import { PrismaClient } from '@prisma/client';
import { NextFunction, Response } from 'express';
import { z } from 'zod';
import * as path from 'path';
import * as fs from 'fs';
import { AuthRequest } from '../middleware/auth.middleware';
import { AppError } from '../middleware/errorHandler';
import {
  generateLoanAgreement as generateAgreementText,
  generateDemandLetter as generateDemandLetterText,
  generateGuarantyAgreement as generateGuarantyAgreementText,
  getDefaultLender,
} from '../utils/documentGenerator.util';
import { calculateEMI } from '../utils/loan.util';
import { generateWordDocumentWithUrl } from '../utils/wordDocumentGenerator.util';
import { convertWordToHtml } from '../utils/wordToHtml.util';

const prisma = new PrismaClient();

const generateAgreementSchema = z.object({
  loanId: z.string(),
  city: z.string().optional().default('Manila'),
  penaltyRate: z.number().optional().default(0.10),
});

// Schema for preview agreement (no loanId required - uses form data)
const generateAgreementPreviewSchema = z.object({
  loanAmount: z.number(),
  tenor: z.number(),
  interestRate: z.number(), // Annual rate as decimal
  borrower: z.object({
    fullName: z.string(),
    address: z.string(),
    civilStatus: z.string(),
    email: z.string().optional(),
    phone: z.string().optional(),
  }),
  city: z.string().optional().default('Manila'),
  penaltyRate: z.number().optional().default(0.10),
  applicationDate: z.string().optional(), // ISO date string, defaults to now
  loanPurpose: z.string().optional().default('personal use'),
  paymentPlace: z.string().optional().default('the Lender\'s office'),
  venueCity: z.string().optional(),
});

const generateDemandLetterPreviewSchema = z.object({
  loanAmount: z.number(),
  monthlyPayment: z.number(),
  borrower: z.object({
    fullName: z.string(),
    address: z.string(),
    civilStatus: z.string(),
    email: z.string().optional(),
    phone: z.string().optional(),
    sex: z.string().optional(), // For title (Ms./Mr.)
    barangay: z.string().optional().default(""), // For demand letter address formatting - default to empty string
    city: z.string().optional().default(""), // For demand letter address formatting - default to empty string
  }),
  loanAgreementDate: z.string().optional(), // ISO date string, defaults to now
  paymentDueDay: z.number().optional().default(1),
  penaltyRate: z.number().optional().default(0.10),
  daysToComply: z.number().optional().default(5),
});

const generateDemandLetterSchema = z.object({
  loanId: z.string(),
  daysToComply: z.number().optional().default(5),
});

const generateGuarantyAgreementSchema = z.object({
  loanId: z.string(),
  guarantor: z.object({
    fullName: z.string(),
    address: z.string(),
    civilStatus: z.string(),
  }),
  city: z.string().optional().default('Manila'),
});

/**
 * Generate Loan Agreement for a specific loan
 */
export const generateLoanAgreement = async (
  req: AuthRequest,
  res: Response,
  next: NextFunction
) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const validatedData = generateAgreementSchema.parse(req.body);
    const { loanId, city, penaltyRate } = validatedData;

    // Fetch loan with borrower details
    const loan = await prisma.loan.findFirst({
      where: {
        id: BigInt(loanId),
        borrowerId: BigInt(req.borrowerId),
      },
      include: {
        borrower: true,
        repayments: {
          orderBy: { dueDate: 'asc' },
          take: 1,
        },
      },
    });

    if (!loan) {
      return res.status(404).json({
        success: false,
        message: 'Loan not found',
      });
    }

    if (!loan.borrower) {
      return res.status(400).json({
        success: false,
        message: 'Borrower information not found',
      });
    }

    // Validate borrower has required information
    if (!loan.borrower.address || !loan.borrower.civilStatus) {
      return res.status(400).json({
        success: false,
        message: 'Borrower information incomplete. Please update your profile with address and civil status.',
        missingFields: {
          address: !loan.borrower.address,
          civilStatus: !loan.borrower.civilStatus,
        },
      });
    }

    // Get lender information
    const lender = getDefaultLender();

    // Calculate monthly payment (EMI)
    const annualRatePercent = Number(loan.interestRate) * 100;
    const tenor = loan.repayments?.length || 6; // Fallback to 6 if no repayments
    const monthlyPayment = calculateEMI(
      Number(loan.principalAmount),
      annualRatePercent,
      tenor
    );

    // Generate agreement text
    const agreementText = generateAgreementText({
      borrower: {
        fullName: loan.borrower.fullName,
        address: loan.borrower.address,
        civilStatus: loan.borrower.civilStatus || 'single',
        email: loan.borrower.email || undefined,
        phone: loan.borrower.phone || undefined,
        birthday: loan.borrower.birthday || undefined,
      },
      lender,
      loanAmount: Number(loan.principalAmount),
      interestRate: Number(loan.interestRate) / 12, // Convert annual to monthly
      monthlyPayment,
      tenor,
      applicationDate: loan.applicationDate,
      city,
      penaltyRate,
      // Optional fields with defaults
      loanPurpose: 'personal use',
      paymentPlace: 'the Lender\'s office',
      venueCity: city,
    });

    // Generate Word document
    const baseUploadDir = process.env.UPLOAD_DIR || './uploads';
    const loanDocumentsDir = path.join(baseUploadDir, 'loan-documents', req.borrowerId.toString());
    const fileName = `Loan_Agreement_${loan.reference}_${Date.now()}.docx`;
    
    const { filePath } = await generateWordDocumentWithUrl(
      {
        title: 'PERSONAL LOAN AGREEMENT',
        content: agreementText,
        fileName,
        outputDir: loanDocumentsDir,
      },
      '' // Base URL will be handled by static file serving
    );

    // Convert Word document to HTML for viewing
    let htmlContent: string | null = null;
    try {
      htmlContent = await convertWordToHtml(filePath);
    } catch (htmlError) {
      console.warn('Failed to convert Word to HTML:', htmlError);
      // Continue without HTML - frontend can fall back to text
    }

    // Save agreement as document in database
    let documentId: bigint | null = null;
    try {
      const document = await prisma.document.create({
        data: {
          borrowerId: BigInt(req.borrowerId),
          loanId: loan.id,
          documentType: 'AGREEMENT',
          fileName,
          fileUrl: `/uploads/loan-documents/${req.borrowerId}/${fileName}`,
          fileSize: BigInt(fs.statSync(filePath).size),
          mimeType: 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        },
      });
      documentId = document.id;
    } catch (docError) {
      // Log but don't fail if document creation fails
      console.warn('Failed to save agreement document:', docError);
    }

    res.json({
      success: true,
      message: 'Loan agreement generated successfully',
      data: {
        agreement: agreementText, // Keep text for backward compatibility
        wordDocumentUrl: `/uploads/loan-documents/${req.borrowerId}/${fileName}`,
        htmlContent, // HTML version for viewing
        loanReference: loan.reference,
        documentId: documentId?.toString(),
        generatedAt: new Date().toISOString(),
      },
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Validation error',
        errors: error.errors,
      });
    }
    return next(error);
  }
};

/**
 * Generate Demand Letter for overdue loan
 */
export const generateDemandLetter = async (
  req: AuthRequest,
  res: Response,
  next: NextFunction
) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const validatedData = generateDemandLetterSchema.parse(req.body);
    const { loanId, daysToComply } = validatedData;

    // Fetch loan with borrower and overdue repayments
    const loan = await prisma.loan.findFirst({
      where: {
        id: BigInt(loanId),
        borrowerId: BigInt(req.borrowerId),
      },
      include: {
        borrower: true,
        repayments: {
          where: {
            dueDate: {
              lte: new Date(), // Past due date
            },
          },
          orderBy: { dueDate: 'asc' },
        },
      },
    });

    if (!loan) {
      return res.status(404).json({
        success: false,
        message: 'Loan not found',
      });
    }

    if (!loan.borrower) {
      return res.status(400).json({
        success: false,
        message: 'Borrower information not found',
      });
    }

    // Calculate missed payments (filter out fully paid)
    // If no overdue repayments, use empty array (for newly created loans)
    const missedPayments = loan.repayments.length > 0
      ? loan.repayments
          .filter((repayment) => {
            const amountDue = Number(repayment.amountDue);
            const amountPaid = Number(repayment.amountPaid || 0);
            return amountPaid < amountDue;
          })
          .map((repayment) => {
            const amountDue = Number(repayment.amountDue);
            const amountPaid = Number(repayment.amountPaid || 0);
            return {
              dueDate: repayment.dueDate,
              amount: amountDue - amountPaid,
            };
          })
      : [];

    // Calculate total due (including penalties)
    // For newly created loans without overdue payments, use 0
    const totalDue = missedPayments.length > 0
      ? missedPayments.reduce((sum: number, payment: { amount: number }) => {
          return sum + payment.amount;
        }, 0) + Number(loan.totalPenalties || 0)
      : 0;

    // Get lender information
    const lender = getDefaultLender();

    // Calculate monthly payment
    const annualRatePercent = Number(loan.interestRate) * 100;
    // Get total repayments count from database (not just overdue ones)
    const allRepayments = await prisma.repayment.findMany({
      where: { loanId: loan.id },
    });
    const tenor = allRepayments.length || 6;
    const monthlyPayment = calculateEMI(
      Number(loan.principalAmount),
      annualRatePercent,
      tenor
    );

    // Get first repayment date to determine payment due day
    const firstRepayment = allRepayments.length > 0 ? allRepayments[0] : null;
    const paymentDueDay = firstRepayment ? firstRepayment.dueDate.getDate() : 1;

    // Get first missed payment date
    const firstMissedPaymentDate = missedPayments.length > 0 
      ? missedPayments[0].dueDate 
      : new Date(); // Fallback to current date if no missed payments

    // Parse barangay and city from address if not available
    // Address format might be: "Street, Barangay, City, Province" or similar
    const borrowerAddress = loan.borrower.address || 'Address not provided';
    let borrowerBarangay = (loan.borrower as any).barangay;
    let borrowerCity = (loan.borrower as any).city;
    
    // If not available, try to parse from address (simple parsing)
    if (!borrowerBarangay || !borrowerCity) {
      const addressParts = borrowerAddress.split(',').map(s => s.trim());
      // Common format: Street, Barangay, City, Province
      if (addressParts.length >= 3) {
        borrowerBarangay = borrowerBarangay || addressParts[addressParts.length - 3] || '';
        borrowerCity = borrowerCity || addressParts[addressParts.length - 2] || '';
      }
    }

    // Generate demand letter text
    const letterText = generateDemandLetterText({
      borrower: {
        fullName: loan.borrower.fullName,
        address: borrowerAddress,
        civilStatus: loan.borrower.civilStatus || 'single',
        email: loan.borrower.email || undefined,
        phone: loan.borrower.phone || undefined,
        sex: (loan.borrower as any).sex || undefined, // Include sex for title (Ms./Mr.)
        barangay: borrowerBarangay || undefined,
        city: borrowerCity || undefined,
      },
      lender,
      loanReference: loan.reference,
      loanAmount: Number(loan.principalAmount),
      monthlyPayment,
      missedPayments,
      totalDue,
      demandDate: new Date(),
      daysToComply,
      loanAgreementDate: loan.applicationDate,
      paymentDueDay,
      penaltyRate: 0.10, // Default penalty rate (10%)
      firstMissedPaymentDate,
    });

    // Generate Word document
    const baseUploadDir = process.env.UPLOAD_DIR || './uploads';
    const loanDocumentsDir = path.join(baseUploadDir, 'loan-documents', req.borrowerId.toString());
    const fileName = `Demand_Letter_${loan.reference}_${Date.now()}.docx`;
    
    const { filePath } = await generateWordDocumentWithUrl(
      {
        title: 'FINAL DEMAND FOR UNPAID PERSONAL LOAN',
        content: letterText,
        fileName,
        outputDir: loanDocumentsDir,
      },
      '' // Base URL will be handled by static file serving
    );

    // Convert Word document to HTML for viewing
    let htmlContent: string | null = null;
    try {
      htmlContent = await convertWordToHtml(filePath);
    } catch (htmlError) {
      console.warn('Failed to convert Word to HTML:', htmlError);
      // Continue without HTML - frontend can fall back to text
    }

    res.json({
      success: true,
      message: 'Demand letter generated successfully',
      data: {
        letter: letterText, // Keep text for backward compatibility
        wordDocumentUrl: `/uploads/loan-documents/${req.borrowerId}/${fileName}`,
        htmlContent, // HTML version for viewing
        loanReference: loan.reference,
        totalDue,
        missedPaymentsCount: missedPayments.length,
        generatedAt: new Date().toISOString(),
      },
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Validation error',
        errors: error.errors,
      });
    }
    return next(error);
  }
};

/**
 * Generate Guaranty Agreement for a loan
 */
export const generateGuarantyAgreement = async (
  req: AuthRequest,
  res: Response,
  next: NextFunction
) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const validatedData = generateGuarantyAgreementSchema.parse(req.body);
    const { loanId, guarantor, city } = validatedData;

    // Fetch loan with borrower details
    const loan = await prisma.loan.findFirst({
      where: {
        id: BigInt(loanId),
        borrowerId: BigInt(req.borrowerId),
      },
      include: {
        borrower: true,
      },
    });

    if (!loan) {
      return res.status(404).json({
        success: false,
        message: 'Loan not found',
      });
    }

    if (!loan.borrower) {
      return res.status(400).json({
        success: false,
        message: 'Borrower information not found',
      });
    }

    // Get lender information
    const lender = getDefaultLender();

    // Generate guaranty agreement text
    const guarantyAgreementText = generateGuarantyAgreementText({
      guarantor: {
        fullName: guarantor.fullName,
        address: guarantor.address,
        civilStatus: guarantor.civilStatus || 'single',
      },
      creditor: lender,
      principalDebtor: {
        fullName: loan.borrower.fullName,
        address: loan.borrower.address || 'Address not provided',
        civilStatus: loan.borrower.civilStatus || 'single',
        email: loan.borrower.email || undefined,
        phone: loan.borrower.phone || undefined,
        birthday: loan.borrower.birthday || undefined,
      },
      loanAmount: Number(loan.principalAmount),
      loanAgreementDate: loan.applicationDate, // Date of the original loan agreement
      city: city || 'Manila',
      venueCity: city || 'Manila',
    });

    // Generate Word document
    const baseUploadDir = process.env.UPLOAD_DIR || './uploads';
    const loanDocumentsDir = path.join(baseUploadDir, 'loan-documents', req.borrowerId.toString());
    const fileName = `Guaranty_Agreement_${loan.reference}_${Date.now()}.docx`;
    
    const { filePath } = await generateWordDocumentWithUrl(
      {
        title: 'GUARANTY AGREEMENT',
        content: guarantyAgreementText,
        fileName,
        outputDir: loanDocumentsDir,
      },
      '' // Base URL will be handled by static file serving
    );

    // Convert Word document to HTML for viewing
    let htmlContent: string | null = null;
    try {
      htmlContent = await convertWordToHtml(filePath);
    } catch (htmlError) {
      console.warn('Failed to convert Word to HTML:', htmlError);
      // Continue without HTML - frontend can fall back to text
    }

    res.json({
      success: true,
      message: 'Guaranty agreement generated successfully',
      data: {
        agreement: guarantyAgreementText, // Keep text for backward compatibility
        wordDocumentUrl: `/uploads/loan-documents/${req.borrowerId}/${fileName}`,
        htmlContent, // HTML version for viewing
        loanReference: loan.reference,
        generatedAt: new Date().toISOString(),
      },
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Validation error',
        errors: error.errors,
      });
    }
    return next(error);
  }
};

/**
 * Generate Loan Agreement Preview (without creating loan)
 * This is used during application process before final submission
 */
export const generateAgreementPreview = async (
  req: AuthRequest,
  res: Response,
  next: NextFunction
) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const validatedData = generateAgreementPreviewSchema.parse(req.body);
    const { loanAmount, tenor, interestRate, borrower, city, penaltyRate, applicationDate } = validatedData;

    // Get lender information
    const lender = getDefaultLender();

    // Calculate monthly payment (EMI)
    const annualRatePercent = interestRate * 100;
    const monthlyPayment = calculateEMI(loanAmount, annualRatePercent, tenor);

    // Use provided date or current date
    const appDate = applicationDate ? new Date(applicationDate) : new Date();

    // Generate agreement text (preview only - not saved to database)
    const agreementText = generateAgreementText({
      borrower: {
        fullName: borrower.fullName,
        address: borrower.address,
        civilStatus: borrower.civilStatus || 'single',
        email: borrower.email,
        phone: borrower.phone,
      },
      lender,
      loanAmount,
      interestRate: interestRate / 12, // Convert annual to monthly
      monthlyPayment,
      tenor,
      applicationDate: appDate,
      city: city || 'Manila',
      penaltyRate: penaltyRate || 0.10,
      // Optional fields with defaults
      loanPurpose: validatedData.loanPurpose || 'personal use',
      paymentPlace: validatedData.paymentPlace || 'the Lender\'s office',
      venueCity: validatedData.venueCity || city || 'Manila',
    });

    // Generate Word document for preview (temporary file)
    const baseUploadDir = process.env.UPLOAD_DIR || './uploads';
    const loanDocumentsDir = path.join(baseUploadDir, 'loan-documents', req.borrowerId.toString());
    const fileName = `Loan_Agreement_Preview_${Date.now()}.docx`;
    
    const { filePath } = await generateWordDocumentWithUrl(
      {
        title: 'PERSONAL LOAN AGREEMENT',
        content: agreementText,
        fileName,
        outputDir: loanDocumentsDir,
      },
      '' // Base URL will be handled by static file serving
    );

    // Convert Word document to HTML for viewing
    let htmlContent: string | null = null;
    try {
      htmlContent = await convertWordToHtml(filePath);
    } catch (htmlError) {
      console.warn('Failed to convert Word to HTML:', htmlError);
      // Continue without HTML - frontend can fall back to text
    }

    res.json({
      success: true,
      message: 'Loan agreement preview generated successfully',
      data: {
        agreement: agreementText, // Keep text for backward compatibility
        wordDocumentUrl: `/uploads/loan-documents/${req.borrowerId}/${fileName}`,
        htmlContent, // HTML version for viewing
        generatedAt: new Date().toISOString(),
      },
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Validation error',
        errors: error.errors,
      });
    }
    return next(error);
  }
};

/**
 * Generate Guaranty Agreement Preview (without creating loan)
 */
export const generateGuarantyAgreementPreview = async (
  req: AuthRequest,
  res: Response,
  next: NextFunction
) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const validatedData = z.object({
      loanAmount: z.number(),
      borrower: z.object({
        fullName: z.string(),
        address: z.string(),
        civilStatus: z.string(),
      }),
      guarantor: z.object({
        fullName: z.string(),
        address: z.string(),
        civilStatus: z.string(),
      }),
      city: z.string().optional().default('Manila'),
      applicationDate: z.string().optional(),
    }).parse(req.body);

    const { loanAmount, borrower, guarantor, city, applicationDate } = validatedData;

    // Get lender information
    const lender = getDefaultLender();

    // Use provided date or current date
    const appDate = applicationDate ? new Date(applicationDate) : new Date();

    // Generate guaranty agreement text (preview only)
    const guarantyAgreementText = generateGuarantyAgreementText({
      guarantor: {
        fullName: guarantor.fullName,
        address: guarantor.address,
        civilStatus: guarantor.civilStatus || 'single',
      },
      creditor: lender,
      principalDebtor: {
        fullName: borrower.fullName,
        address: borrower.address || 'N/A',
        civilStatus: borrower.civilStatus || 'single',
      },
      loanAmount,
      loanAgreementDate: appDate,
      city: city || 'Manila',
      venueCity: city || 'Manila',
    });

    // Generate Word document for preview (temporary file)
    const baseUploadDir = process.env.UPLOAD_DIR || './uploads';
    const loanDocumentsDir = path.join(baseUploadDir, 'loan-documents', req.borrowerId.toString());
    const fileName = `Guaranty_Agreement_Preview_${Date.now()}.docx`;
    
    const { filePath } = await generateWordDocumentWithUrl(
      {
        title: 'GUARANTY AGREEMENT',
        content: guarantyAgreementText,
        fileName,
        outputDir: loanDocumentsDir,
      },
      '' // Base URL will be handled by static file serving
    );

    // Convert Word document to HTML for viewing
    let htmlContent: string | null = null;
    try {
      htmlContent = await convertWordToHtml(filePath);
    } catch (htmlError) {
      console.warn('Failed to convert Word to HTML:', htmlError);
      // Continue without HTML - frontend can fall back to text
    }

    res.json({
      success: true,
      message: 'Guaranty agreement preview generated successfully',
      data: {
        agreement: guarantyAgreementText, // Keep text for backward compatibility
        wordDocumentUrl: `/uploads/loan-documents/${req.borrowerId}/${fileName}`,
        htmlContent, // HTML version for viewing
        generatedAt: new Date().toISOString(),
      },
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Validation error',
        errors: error.errors,
      });
    }
    return next(error);
  }
};

/**
 * Generate Demand Letter Preview (without creating loan)
 */
export const generateDemandLetterPreview = async (
  req: AuthRequest,
  res: Response,
  next: NextFunction
) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    // Debug: Log raw request body BEFORE Zod validation
    console.log("Raw request body received:", JSON.stringify(req.body, null, 2));
    console.log("Raw borrower data:", JSON.stringify(req.body.borrower, null, 2));

    const validatedData = generateDemandLetterPreviewSchema.parse(req.body);
    const { loanAmount, monthlyPayment, borrower, loanAgreementDate, paymentDueDay, penaltyRate, daysToComply } = validatedData;
    
    // Debug: Log validated data AFTER Zod validation
    console.log("Validated borrower data:", JSON.stringify(borrower, null, 2));

    // Get lender information
    const lender = getDefaultLender();

    // Use provided date or current date
    const appDate = loanAgreementDate ? new Date(loanAgreementDate) : new Date();
    const demandDate = new Date();

    // For preview, use empty missed payments and estimate total due
    const missedPayments: Array<{ dueDate: Date; amount: number }> = [];
    const totalDue = loanAmount; // Simple estimate for preview

    // Calculate first missed payment date (for preview, use a date one month after loan agreement)
    const firstMissedDate = new Date(appDate);
    firstMissedDate.setMonth(firstMissedDate.getMonth() + 2); // 2 months after agreement (1 month grace + 1 month missed)
    firstMissedDate.setDate(paymentDueDay || 1);

    // Debug: Log borrower data received
    console.log("Backend received borrower data:", {
      barangay: borrower.barangay,
      city: borrower.city,
      fullName: borrower.fullName,
      barangayType: typeof borrower.barangay,
      cityType: typeof borrower.city,
    });
    
    // Ensure barangay and city are strings (Zod should handle this with .default(""), but double-check)
    const borrowerBarangay = borrower.barangay ?? "";
    const borrowerCity = borrower.city ?? "";

    console.log("After processing:", {
      barangay: borrowerBarangay,
      city: borrowerCity,
    });

    // Generate demand letter text (preview only - not saved to database)
    const letterText = generateDemandLetterText({
      borrower: {
        fullName: borrower.fullName,
        address: borrower.address,
        civilStatus: borrower.civilStatus || 'single',
        email: borrower.email,
        phone: borrower.phone,
        sex: (borrower as any).sex || undefined, // Include sex for title (Ms./Mr.)
        barangay: borrowerBarangay, // Always a string now
        city: borrowerCity, // Always a string now
      },
      lender,
      loanReference: 'PREVIEW',
      loanAmount,
      monthlyPayment,
      missedPayments,
      totalDue,
      demandDate,
      daysToComply: daysToComply || 5,
      loanAgreementDate: appDate,
      paymentDueDay: paymentDueDay || 1,
      penaltyRate: penaltyRate || 0.10,
      firstMissedPaymentDate: firstMissedDate,
    });

    // Generate Word document for preview (temporary file)
    const baseUploadDir = process.env.UPLOAD_DIR || './uploads';
    const loanDocumentsDir = path.join(baseUploadDir, 'loan-documents', req.borrowerId.toString());
    const fileName = `Demand_Letter_Preview_${Date.now()}.docx`;
    
    const { filePath } = await generateWordDocumentWithUrl(
      {
        title: 'FINAL DEMAND FOR UNPAID PERSONAL LOAN',
        content: letterText,
        fileName,
        outputDir: loanDocumentsDir,
      },
      '' // Base URL will be handled by static file serving
    );

    // Convert Word document to HTML for viewing
    let htmlContent: string | null = null;
    try {
      htmlContent = await convertWordToHtml(filePath);
    } catch (htmlError) {
      console.warn('Failed to convert Word to HTML:', htmlError);
      // Continue without HTML - frontend can fall back to text
    }

    res.json({
      success: true,
      message: 'Demand letter preview generated successfully',
      data: {
        letter: letterText, // Keep text for backward compatibility
        wordDocumentUrl: `/uploads/loan-documents/${req.borrowerId}/${fileName}`,
        htmlContent, // HTML version for viewing
        generatedAt: new Date().toISOString(),
      },
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({
        success: false,
        message: 'Validation error',
        errors: error.errors,
      });
    }
    return next(error);
  }
};

