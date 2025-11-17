import { PrismaClient } from '@prisma/client';
import { NextFunction, Response } from 'express';
import { z } from 'zod';
import { AuthRequest } from '../middleware/auth.middleware';
import { AppError } from '../middleware/errorHandler';

const prisma = new PrismaClient();

const createPaymentSchema = z.object({
  loanId: z.string(),
  amount: z.number().positive(),
  repaymentId: z.string().optional(), // If paying specific repayment schedule
  receiptUrl: z.string().optional(),
});

export const createPayment = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const validatedData = createPaymentSchema.parse(req.body);
    const { loanId, amount, repaymentId, receiptUrl } = validatedData;

    // Verify loan belongs to borrower
    const loan = await prisma.loan.findFirst({
      where: {
        id: BigInt(loanId),
        borrowerId: BigInt(req.borrowerId),
      },
    });

    if (!loan) {
      return res.status(404).json({
        success: false,
        message: 'Loan not found',
      });
    }

    // If repaymentId provided, update the specific repayment
    if (repaymentId) {
      const repayment = await prisma.repayment.findFirst({
        where: {
          id: BigInt(repaymentId),
          loanId: loan.id,
        },
      });

      if (repayment) {
        const newAmountPaid = Number(repayment.amountPaid) + amount;
        await prisma.repayment.update({
          where: { id: repayment.id },
          data: {
            amountPaid: newAmountPaid,
            paidAt: newAmountPaid >= Number(repayment.amountDue) ? new Date() : repayment.paidAt,
          },
        });
      }
    }

    // Update loan total paid
    const newTotalPaid = Number(loan.totalPaid) + amount;
    await prisma.loan.update({
      where: { id: loan.id },
      data: {
        totalPaid: newTotalPaid,
      },
    });

    // Create bank transaction record (optional, depends on your flow)
    // For now, we'll just return success

    res.status(201).json({
      success: true,
      message: 'Payment recorded successfully',
      data: {
        loanId: loan.id.toString(),
        amount: amount.toString(),
        totalPaid: newTotalPaid.toString(),
        receiptUrl: receiptUrl || null,
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
    next(error);
  }
};

export const getPayments = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const { loanId } = req.query;

    // Get loans for this borrower
    const where: any = { 
      borrowerId: BigInt(req.borrowerId),
      isActive: true,
    };
    
    if (loanId) {
      where.id = BigInt(loanId as string);
    }

    const loans = await prisma.loan.findMany({
      where,
      include: {
        repayments: {
          where: {
            amountPaid: {
              gt: 0, // Only show repayments with payments
            },
          },
          orderBy: { paidAt: 'desc' },
        },
      },
      orderBy: { createdAt: 'desc' },
    });

    // Format payments from repayments
    const payments = loans.flatMap(loan => 
      loan.repayments.map(rep => ({
        id: rep.id.toString(),
        loanId: loan.id.toString(),
        loanReference: loan.reference,
        amount: rep.amountPaid.toString(),
        dueDate: rep.dueDate,
        paidAt: rep.paidAt,
        penaltyApplied: rep.penaltyApplied.toString(),
      }))
    );

    // Return payments array in 'payments' property to match frontend expectations
    res.json({
      success: true,
      payments: payments,
      data: payments, // Also include 'data' for backward compatibility
    });
  } catch (error) {
    next(error);
  }
};
