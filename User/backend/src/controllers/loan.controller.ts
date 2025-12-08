import { PrismaClient } from '@prisma/client';
import { NextFunction, Response } from 'express';
import { z } from 'zod';
import { AuthRequest } from '../middleware/auth.middleware';
import { AppError } from '../middleware/errorHandler';
import { calculateEMI, generatePaymentSchedule } from '../utils/loan.util';

const prisma = new PrismaClient();

const createLoanSchema = z.object({
  amount: z.number().min(3500).max(50000).optional(),
  principalAmount: z.number().min(3500).max(50000).optional(),
  tenor: z.preprocess(
    (val) => {
      // Accept both string and number, convert to string first
      if (typeof val === 'number') {
        return val.toString();
      }
      return val;
    },
    z.enum(['6', '12', '36'], {
      errorMap: () => ({ message: 'Tenor must be 6, 12, or 36 months' }),
    }).transform(Number)
  ),
  interestRate: z.number().optional(),
}).refine((data) => data.amount || data.principalAmount, {
  message: 'Either amount or principalAmount is required',
});

// Generate loan reference (MF-YYYY-XXXX format)
const generateLoanReference = async (): Promise<string> => {
  const year = new Date().getFullYear();
  const lastLoan = await prisma.loan.findFirst({
    where: {
      reference: {
        startsWith: `MF-${year}-`,
      },
    },
    orderBy: {
      createdAt: 'desc',
    },
  });

  let sequence = 1;
  if (lastLoan) {
    const match = lastLoan.reference.match(/MF-\d{4}-(\d+)/);
    if (match) {
      sequence = parseInt(match[1]) + 1;
    }
  }

  return `MF-${year}-${sequence.toString().padStart(4, '0')}`;
};

export const createLoan = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId || !req.borrower) {
      throw new AppError('User not authenticated', 401);
    }

    // Log incoming request for debugging
    console.log('📥 Loan application request:', {
      borrowerId: req.borrowerId,
      body: req.body,
    });

    // Check for existing pending/active loan applications (exclude cancelled and rejected)
    const existingPendingLoan = await prisma.loan.findFirst({
      where: {
        borrowerId: BigInt(req.borrowerId),
        status: {
          in: ['new_application', 'under_review', 'approved', 'for_release', 'disbursed'],
        },
        isActive: true,
      },
    });

    if (existingPendingLoan) {
      const statusDisplay = existingPendingLoan.status.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase());
      console.log('⛔ Existing loan found:', existingPendingLoan.reference, existingPendingLoan.status);
      return res.status(400).json({
        success: false,
        message: `You already have a ${statusDisplay} loan application (${existingPendingLoan.reference}). Please wait for it to be processed or closed before applying for a new loan. You can cancel it from Loan History if needed.`,
        existingLoan: {
          id: existingPendingLoan.id.toString(),
          reference: existingPendingLoan.reference,
          status: existingPendingLoan.status,
          statusDisplay: statusDisplay,
        },
      });
    }

    // Note: Documents are not required at loan creation time
    // Documents will be uploaded during final application submission
    // This allows multi-step application flow where documents are uploaded at the end

    // Validate borrower has required information
    const borrower = await prisma.borrower.findUnique({
      where: { id: BigInt(req.borrowerId) },
      select: {
        address: true,
        civilStatus: true,
      },
    });

    if (!borrower?.address || !borrower?.civilStatus) {
      return res.status(400).json({
        success: false,
        message: 'Borrower information incomplete. Please complete your profile with address and civil status before submitting your loan application.',
        missingFields: {
          address: !borrower?.address,
          civilStatus: !borrower?.civilStatus,
        },
      });
    }

    // Validate request data
    console.log('🔍 Validating request data...');
    const validatedData = createLoanSchema.parse(req.body);
    console.log('✅ Validation passed:', validatedData);
    const principalAmount = validatedData.principalAmount || validatedData.amount;
    const tenor = validatedData.tenor;
    const interestRate = validatedData.interestRate || 0.24; // 24% default as decimal (0.24)
    
    if (!principalAmount) {
      return res.status(400).json({
        success: false,
        message: 'Loan amount is required',
      });
    }

    // Interest rate is stored as decimal (0.24 = 24%)
    // Calculate EMI (monthly payment) - convert to percentage for calculation
    const annualRatePercent = interestRate * 100; // 0.24 * 100 = 24%
    const monthlyEMI = calculateEMI(principalAmount, annualRatePercent, tenor);

    // Calculate maturity date (same day of month, or last day if month doesn't have that day)
    const applicationDate = new Date();
    // Reset time to midnight for date-only storage
    applicationDate.setHours(0, 0, 0, 0);
    
    const targetDay = applicationDate.getDate();
    // Create maturity date by adding months properly
    const maturityDate = new Date(applicationDate.getFullYear(), applicationDate.getMonth() + tenor, 1);
    // Handle month overflow - if target day doesn't exist, use last day of month
    // e.g., Jan 31 + 6 months = July 31, but Feb 31 doesn't exist, so use Feb 28/29
    const lastDayOfMonth = new Date(maturityDate.getFullYear(), maturityDate.getMonth() + 1, 0).getDate();
    maturityDate.setDate(Math.min(targetDay, lastDayOfMonth));
    maturityDate.setHours(0, 0, 0, 0);

    // Generate loan reference
    const reference = await generateLoanReference();

    // Create loan
    const loan = await prisma.loan.create({
      data: {
        reference,
        borrowerId: BigInt(req.borrowerId),
        borrowerName: req.borrower.fullName,
        principalAmount: principalAmount,
        interestRate: interestRate, // Store as decimal (0.24 = 24%)
        applicationDate,
        maturityDate,
        status: 'new_application',
        totalDisbursed: 0,
        totalPaid: 0,
        totalPenalties: 0,
        isActive: true,
        remarks: 'Mobile app application',
      },
      include: {
        borrower: {
          select: {
            id: true,
            fullName: true,
            email: true,
          },
        },
      },
    });

    // Generate payment schedule (repayments)
    const scheduleData = generatePaymentSchedule(applicationDate, tenor, monthlyEMI);
    
    // Only create repayments if schedule data is valid
    if (scheduleData && scheduleData.length > 0) {
      await prisma.repayment.createMany({
        data: scheduleData.map((item, index) => ({
          loanId: loan.id,
          dueDate: item.dueDate,
          amountDue: item.amount,
          amountPaid: 0,
          penaltyApplied: 0,
          note: index === 0 ? 'First payment' : `Payment ${index + 1}`,
        })),
      });
    }

    // Create notification for borrower
    try {
      await prisma.notification.create({
        data: {
          borrowerId: BigInt(req.borrowerId),
          loanId: loan.id,
          type: 'info',
          title: 'Loan Application Submitted',
          message: `Your loan application ${reference} for ₱${principalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} has been submitted successfully. We will review it and notify you of the status.`,
          isRead: false,
        },
      });
    } catch (notificationError) {
      // Log but don't fail the loan creation if notification fails
      console.error('⚠️ Failed to create notification:', notificationError);
    }

    return res.status(201).json({
      success: true,
      message: 'Loan application submitted successfully',
      data: {
        id: loan.id.toString(),
        reference: loan.reference,
        principalAmount: loan.principalAmount.toString(),
        interestRate: loan.interestRate.toString(),
        status: loan.status,
        applicationDate: loan.applicationDate,
        maturityDate: loan.maturityDate,
        borrower: loan.borrower ? {
          id: loan.borrower.id.toString(),
          fullName: loan.borrower.fullName,
          email: loan.borrower.email,
        } : null,
      },
    });
  } catch (error) {
    console.error('❌ Loan creation error:', error);
    if (error instanceof z.ZodError) {
      console.error('📋 Validation errors:', error.errors);
      return res.status(400).json({
        success: false,
        message: 'Validation error',
        errors: error.errors.map((e) => ({
          path: e.path.join('.'),
          message: e.message,
          code: e.code,
        })),
      });
    }
    return next(error);
  }
};

export const getLoans = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const loans = await prisma.loan.findMany({
      where: { 
        borrowerId: BigInt(req.borrowerId),
        isActive: true,
      },
      include: {
        repayments: {
          orderBy: { dueDate: 'asc' },
        },
      },
      orderBy: { createdAt: 'desc' },
    });

    // Format response
    const formattedLoans = loans.map(loan => ({
      id: loan.id.toString(),
      reference: loan.reference,
      borrowerName: loan.borrowerName,
      principalAmount: loan.principalAmount.toString(),
      interestRate: loan.interestRate.toString(),
      applicationDate: loan.applicationDate,
      maturityDate: loan.maturityDate,
      releaseDate: loan.releaseDate,
      status: loan.status,
      totalDisbursed: loan.totalDisbursed.toString(),
      totalPaid: loan.totalPaid.toString(),
      totalPenalties: loan.totalPenalties.toString(),
      repayments: loan.repayments.map(rep => ({
        id: rep.id.toString(),
        dueDate: rep.dueDate,
        amountDue: rep.amountDue.toString(),
        amountPaid: rep.amountPaid.toString(),
        paidAt: rep.paidAt,
        penaltyApplied: rep.penaltyApplied.toString(),
      })),
    }));

    // Return loans array directly in 'loans' property to match frontend expectations
    res.json({
      success: true,
      loans: formattedLoans,
      data: formattedLoans, // Also include 'data' for backward compatibility
    });
  } catch (error) {
    next(error);
  }
};

export const getLoanById = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const { id } = req.params;
    const loan = await prisma.loan.findFirst({
      where: {
        id: BigInt(id),
        borrowerId: BigInt(req.borrowerId),
      },
      include: {
        repayments: {
          orderBy: { dueDate: 'asc' },
        },
        borrower: {
          select: {
            id: true,
            fullName: true,
            email: true,
          },
        },
      },
    });

    if (!loan) {
      return res.status(404).json({
        success: false,
        message: 'Loan not found',
      });
    }

    return res.json({
      success: true,
      data: {
        id: loan.id.toString(),
        reference: loan.reference,
        borrowerName: loan.borrowerName,
        principalAmount: loan.principalAmount.toString(),
        interestRate: loan.interestRate.toString(),
        applicationDate: loan.applicationDate,
        maturityDate: loan.maturityDate,
        releaseDate: loan.releaseDate,
        status: loan.status,
        totalDisbursed: loan.totalDisbursed.toString(),
        totalPaid: loan.totalPaid.toString(),
        totalPenalties: loan.totalPenalties.toString(),
        repayments: loan.repayments.map(rep => ({
          id: rep.id.toString(),
          dueDate: rep.dueDate,
          amountDue: rep.amountDue.toString(),
          amountPaid: rep.amountPaid.toString(),
          paidAt: rep.paidAt,
          penaltyApplied: rep.penaltyApplied.toString(),
          note: rep.note,
        })),
      },
    });
  } catch (error) {
    return next(error);
  }
};

export const cancelLoan = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const { id } = req.params;

    // Find the loan and verify it belongs to the borrower
    const loan = await prisma.loan.findFirst({
      where: {
        id: BigInt(id),
        borrowerId: BigInt(req.borrowerId),
        isActive: true,
      },
    });

    if (!loan) {
      return res.status(404).json({
        success: false,
        message: 'Loan not found or already cancelled',
      });
    }

    // Only allow cancellation for pending applications
    const cancellableStatuses = ['new_application', 'under_review'];
    if (!cancellableStatuses.includes(loan.status)) {
      return res.status(400).json({
        success: false,
        message: `Cannot cancel loan application. Loan is already ${loan.status.replace('_', ' ')}. Only pending applications (new application or under review) can be cancelled.`,
      });
    }

    // Cancel the loan by setting status to cancelled and isActive to false
    const cancelledLoan = await prisma.loan.update({
      where: { id: loan.id },
      data: {
        status: 'cancelled',
        isActive: false,
      },
    });

    return res.json({
      success: true,
      message: 'Loan application cancelled successfully',
      data: {
        id: cancelledLoan.id.toString(),
        reference: cancelledLoan.reference,
        status: cancelledLoan.status,
      },
    });
  } catch (error) {
    return next(error);
  }
};

export const getLoanStatus = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const { id } = req.params;
    const loan = await prisma.loan.findFirst({
      where: {
        id: BigInt(id),
        borrowerId: BigInt(req.borrowerId),
      },
      select: {
        id: true,
        reference: true,
        status: true,
        principalAmount: true,
        totalDisbursed: true,
        totalPaid: true,
        updatedAt: true,
      },
    });

    if (!loan) {
      return res.status(404).json({
        success: false,
        message: 'Loan not found',
      });
    }

    return res.json({
      success: true,
      data: {
        id: loan.id.toString(),
        reference: loan.reference,
        status: loan.status,
        principalAmount: loan.principalAmount.toString(),
        totalDisbursed: loan.totalDisbursed.toString(),
        totalPaid: loan.totalPaid.toString(),
        lastUpdated: loan.updatedAt,
      },
    });
  } catch (error) {
    return next(error);
  }
};
