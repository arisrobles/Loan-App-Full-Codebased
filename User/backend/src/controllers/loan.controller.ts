import { PrismaClient } from '@prisma/client';
import { NextFunction, Response } from 'express';
import { z } from 'zod';
import { AuthRequest } from '../middleware/auth.middleware';
import { AppError } from '../middleware/errorHandler';
import { calculateEMI, generatePaymentSchedule } from '../utils/loan.util';
import { LoanStatus, isCancellableStatus, getLoanStatusDisplay } from '../constants/loanStatus';
import { LoanDefaults } from '../constants/loanDefaults';
import { sendErrorResponse, sendValidationError } from '../utils/errorResponse';

const prisma = new PrismaClient();

const createLoanSchema = z.object({
  amount: z.number().min(3500).max(50000).optional(),
  principalAmount: z.number().min(3500).max(50000).optional(),
  tenor: z.preprocess(
    (val) => {
      // Accept both string and number, convert to number
      if (typeof val === 'string') {
        return parseInt(val, 10);
      }
      return val;
    },
    z.number().int().min(1).max(18).refine(
      (val) => val >= 1 && val <= 18,
      { message: 'Tenor must be between 1 and 18 months' }
    )
  ),
  interestRate: z.number().optional(),
  applicationDate: z.string().optional(), // ISO date string (YYYY-MM-DD)
  latitude: z.number().optional(),
  longitude: z.number().optional(),
  locationAddress: z.string().optional(),
  guarantor: z.object({
    fullName: z.string().min(1),
    address: z.string().min(1),
    civilStatus: z.string().optional(),
  }).optional(),
}).refine((data) => data.amount || data.principalAmount, {
  message: 'Either amount or principalAmount is required',
});

// Note: Loan reference generation is now done inside the loan creation transaction
// to ensure atomicity and prevent race conditions

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
          in: [
            LoanStatus.NEW_APPLICATION,
            LoanStatus.UNDER_REVIEW,
            LoanStatus.APPROVED,
            LoanStatus.FOR_RELEASE,
            LoanStatus.DISBURSED,
          ],
        },
        isActive: true,
      },
    });

    if (existingPendingLoan) {
      const statusDisplay = getLoanStatusDisplay(existingPendingLoan.status);
      console.log('⛔ Existing loan found:', existingPendingLoan.reference, existingPendingLoan.status);
      return sendErrorResponse(
        res,
        400,
        `You already have a ${statusDisplay} loan application (${existingPendingLoan.reference}). Please wait for it to be processed or closed before applying for a new loan. You can cancel it from Loan History if needed.`,
        undefined,
        {
        existingLoan: {
          id: existingPendingLoan.id.toString(),
          reference: existingPendingLoan.reference,
          status: existingPendingLoan.status,
          statusDisplay: statusDisplay,
        },
        }
      );
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
      return sendErrorResponse(
        res,
        400,
        'Borrower information incomplete. Please complete your profile with address and civil status before submitting your loan application.',
        undefined,
        {
        missingFields: {
          address: !borrower?.address,
          civilStatus: !borrower?.civilStatus,
        },
        }
      );
    }

    // Validate request data
    console.log('🔍 Validating request data...');
    const validatedData = createLoanSchema.parse(req.body);
    console.log('✅ Validation passed:', validatedData);
    const principalAmount = validatedData.principalAmount || validatedData.amount;
    const tenor = validatedData.tenor;
    const interestRate = validatedData.interestRate || LoanDefaults.INTEREST_RATE;
    
    if (!principalAmount) {
      return sendErrorResponse(res, 400, 'Loan amount is required');
    }

    // Interest rate is stored as decimal (0.24 = 24%)
    // Calculate EMI (monthly payment) - convert to percentage for calculation
    const annualRatePercent = interestRate * 100; // 0.24 * 100 = 24%
    const monthlyEMI = calculateEMI(principalAmount, annualRatePercent, tenor);

    // Use provided application date or current date
    const applicationDate = validatedData.applicationDate 
      ? new Date(validatedData.applicationDate)
      : new Date();
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

    // Create loan with reference generation inside transaction to prevent race conditions
    if (!req.borrower) {
      return sendErrorResponse(res, 400, 'Borrower information not available');
    }

    const borrowerName = req.borrower.fullName;
    const borrowerId = req.borrowerId;

    const loan = await prisma.$transaction(async (tx) => {
      // Generate loan reference inside transaction with locking
      const year = new Date().getFullYear();
      const pattern = `MF-${year}-%`;
      
      const result = await tx.$queryRaw<Array<{ reference: string }>>`
        SELECT reference 
        FROM loans 
        WHERE reference LIKE ${pattern}
        ORDER BY created_at DESC 
        LIMIT 1
        FOR UPDATE
      `;

      let sequence = 1;
      if (result && result.length > 0 && result[0]?.reference) {
        const match = result[0].reference.match(/MF-\d{4}-(\d+)/);
        if (match) {
          sequence = parseInt(match[1]) + 1;
        }
      }

      const reference = `MF-${year}-${sequence.toString().padStart(4, '0')}`;

      // Create loan inside the same transaction
      const createdLoan = await tx.loan.create({
      data: {
        reference,
          borrowerId: BigInt(borrowerId),
          borrowerName: borrowerName,
        principalAmount: principalAmount,
        interestRate: interestRate, // Store as decimal (0.24 = 24%)
        applicationDate,
        maturityDate,
          status: LoanStatus.NEW_APPLICATION,
        totalDisbursed: 0,
        totalPaid: 0,
        totalPenalties: 0,
          penaltyGraceDays: LoanDefaults.PENALTY_GRACE_DAYS,
          penaltyDailyRate: LoanDefaults.PENALTY_DAILY_RATE,
        isActive: true,
          remarks: LoanDefaults.REMARKS_MOBILE_APP,
        applicationLatitude: validatedData.latitude ? validatedData.latitude : null,
        applicationLongitude: validatedData.longitude ? validatedData.longitude : null,
        applicationLocationAddress: validatedData.locationAddress || null,
        } as any,
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

      // Generate payment schedule (repayments) inside transaction
      const scheduleData = generatePaymentSchedule(applicationDate, tenor, monthlyEMI);
      
      if (scheduleData && scheduleData.length > 0) {
        await tx.repayment.createMany({
          data: scheduleData.map((item, index) => ({
            loanId: createdLoan.id,
            dueDate: item.dueDate,
            amountDue: item.amount,
            amountPaid: 0,
            penaltyApplied: 0,
            note: index === 0 ? 'First payment' : `Payment ${index + 1}`,
          })),
        });
      }

      return createdLoan;
    });

    // Create guarantor if provided (outside transaction, but after loan is created)
    if (validatedData.guarantor) {
      await (prisma as any).guarantor.create({
        data: {
          loanId: loan.id,
          fullName: validatedData.guarantor.fullName,
          address: validatedData.guarantor.address,
          civilStatus: validatedData.guarantor.civilStatus || null,
        },
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
          message: `Your loan application ${loan.reference} for ₱${principalAmount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} has been submitted successfully. We will review it and notify you of the status.`,
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
      return sendValidationError(res, error);
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

    // Fetch guarantors separately for all loans
    const loanIds = loans.map(loan => loan.id);
    const guarantors = await (prisma as any).guarantor.findMany({
      where: {
        loanId: { in: loanIds },
      },
    });
    const guarantorMap = new Map(guarantors.map((g: any) => [g.loanId.toString(), g]));

    // Format response
    const formattedLoans = loans.map(loan => {
      const guarantor = guarantorMap.get(loan.id.toString());
      return {
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
        applicationLatitude: (loan as any).applicationLatitude?.toString() || null,
        applicationLongitude: (loan as any).applicationLongitude?.toString() || null,
        applicationLocationAddress: (loan as any).applicationLocationAddress || null,
        guarantor: guarantor ? {
          id: (guarantor as any).id.toString(),
          fullName: (guarantor as any).fullName,
          address: (guarantor as any).address,
          civilStatus: (guarantor as any).civilStatus || null,
        } : null,
        repayments: loan.repayments.map((rep: any) => ({
        id: rep.id.toString(),
        dueDate: rep.dueDate,
        amountDue: rep.amountDue.toString(),
        amountPaid: rep.amountPaid.toString(),
        paidAt: rep.paidAt,
        penaltyApplied: rep.penaltyApplied.toString(),
      })),
      };
    });

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
      return sendErrorResponse(res, 404, 'Loan not found');
    }

    // Fetch guarantor separately
    const guarantor = await (prisma as any).guarantor.findUnique({
      where: {
        loanId: loan.id,
      },
      });

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
        applicationLatitude: (loan as any).applicationLatitude?.toString() || null,
        applicationLongitude: (loan as any).applicationLongitude?.toString() || null,
        applicationLocationAddress: (loan as any).applicationLocationAddress || null,
        guarantor: guarantor ? {
          id: (guarantor as any).id.toString(),
          fullName: (guarantor as any).fullName,
          address: (guarantor as any).address,
          civilStatus: (guarantor as any).civilStatus || null,
        } : null,
        repayments: loan.repayments.map((rep: any) => ({
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
      return sendErrorResponse(res, 404, 'Loan not found or already cancelled');
    }

    // Only allow cancellation for pending applications
    if (!isCancellableStatus(loan.status)) {
      const statusDisplay = getLoanStatusDisplay(loan.status);
      return sendErrorResponse(
        res,
        400,
        `Cannot cancel loan application. Loan is already ${statusDisplay}. Only pending applications (new application or under review) can be cancelled.`
      );
    }

    // Cancel the loan by setting status to cancelled and isActive to false
    const cancelledLoan = await prisma.loan.update({
      where: { id: loan.id },
      data: {
        status: LoanStatus.CANCELLED,
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
      return sendErrorResponse(res, 404, 'Loan not found');
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
