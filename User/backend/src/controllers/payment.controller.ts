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
  receiptDocumentId: z.string().optional(), // Link to uploaded receipt document
});

export const createPayment = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const validatedData = createPaymentSchema.parse(req.body);
    const { loanId, amount, repaymentId, receiptDocumentId } = validatedData;

    // Verify loan belongs to borrower and is disbursed
    const loan = await prisma.loan.findFirst({
      where: {
        id: BigInt(loanId),
        borrowerId: BigInt(req.borrowerId),
      },
      include: {
        repayments: {
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

    // Validate loan is disbursed (only disbursed loans can receive payments)
    if (loan.status !== 'disbursed') {
      return res.status(400).json({
        success: false,
        message: `Cannot make payment. Loan status is "${loan.status}". Only disbursed loans can receive payments.`,
      });
    }

    // Validate loan is not closed
    if (!loan.isActive) {
      return res.status(400).json({
        success: false,
        message: 'Cannot make payment. This loan is closed.',
      });
    }

    // Calculate penalty if payment is overdue (for display, not applied yet)
    let penaltyAmount = 0;
    const today = new Date();
    today.setHours(0, 0, 0, 0);

    let targetRepaymentId: bigint | null = null;

    // Determine which repayment this payment is for
    if (repaymentId) {
      const repayment = await prisma.repayment.findFirst({
        where: {
          id: BigInt(repaymentId),
          loanId: loan.id,
        },
      });

      if (!repayment) {
        return res.status(404).json({
          success: false,
          message: 'Repayment not found',
        });
      }

      // Check if repayment is already fully paid
      const totalDue = Number(repayment.amountDue) + Number(repayment.penaltyApplied);
      const totalPaid = Number(repayment.amountPaid);
      
      if (totalPaid >= totalDue) {
        return res.status(400).json({
          success: false,
          message: 'This repayment period is already fully paid. You cannot submit additional payments for it.',
        });
      }

      // Check if there's already a pending payment for this repayment
      const existingPendingPayment = await prisma.payment.findFirst({
        where: {
          repaymentId: repayment.id,
          borrowerId: BigInt(req.borrowerId),
          status: 'pending',
        },
      });

      if (existingPendingPayment) {
        return res.status(400).json({
          success: false,
          message: 'You already have a pending payment for this repayment period. Please wait for admin approval before submitting another payment.',
          existingPayment: {
            id: existingPendingPayment.id.toString(),
            amount: existingPendingPayment.amount.toString(),
            submittedAt: existingPendingPayment.createdAt,
          },
        });
      }

      // Check if there's already an approved payment for this repayment (that fully covers it)
      const existingApprovedPayments = await prisma.payment.findMany({
        where: {
          repaymentId: repayment.id,
          borrowerId: BigInt(req.borrowerId),
          status: 'approved',
        },
      });

      // Calculate total approved amount for this repayment
      const totalApprovedAmount = existingApprovedPayments.reduce((sum, p) => {
        return sum + Number(p.amount);
      }, 0);

      // If approved payments already cover the remaining balance, prevent new submission
      const remainingBalance = totalDue - totalPaid;
      if (totalApprovedAmount >= remainingBalance) {
        return res.status(400).json({
          success: false,
          message: 'This repayment period already has approved payments that cover the remaining balance. You cannot submit additional payments.',
        });
      }

      targetRepaymentId = repayment.id;

      // Calculate penalty if overdue (matching PHP implementation)
      const dueDate = new Date(repayment.dueDate);
      dueDate.setHours(0, 0, 0, 0);
      // Formula: max(0, floor((today - dueDate) / 86400000) - graceDays)
      const daysDiff = Math.floor((today.getTime() - dueDate.getTime()) / (1000 * 60 * 60 * 24)); // Positive if dueDate is in past
      const daysOverdue = Math.max(0, daysDiff - (loan.penaltyGraceDays || 0));
      
      if (daysOverdue > 0 && Number(repayment.amountPaid) < Number(repayment.amountDue)) {
        const outstanding = Number(repayment.amountDue) - Number(repayment.amountPaid);
        penaltyAmount = outstanding * Number(loan.penaltyDailyRate || 0.001) * daysOverdue;
        penaltyAmount = Math.round(penaltyAmount * 100) / 100; // Round to 2 decimal places
      }
    } else {
      // If no repaymentId, apply payment to oldest unpaid repayment
      const unpaidRepayment = loan.repayments.find(rep => {
        const totalDue = Number(rep.amountDue) + Number(rep.penaltyApplied);
        const totalPaid = Number(rep.amountPaid);
        return totalPaid < totalDue;
      });

      if (unpaidRepayment) {
        // Check if there's already a pending payment for this repayment
        const existingPendingPayment = await prisma.payment.findFirst({
          where: {
            repaymentId: unpaidRepayment.id,
            borrowerId: BigInt(req.borrowerId),
            status: 'pending',
          },
        });

        if (existingPendingPayment) {
          return res.status(400).json({
            success: false,
            message: 'You already have a pending payment for this repayment period. Please wait for admin approval before submitting another payment.',
            existingPayment: {
              id: existingPendingPayment.id.toString(),
              amount: existingPendingPayment.amount.toString(),
              submittedAt: existingPendingPayment.createdAt,
            },
          });
        }

        // Check if repayment is already fully paid (including approved pending payments)
        const totalDue = Number(unpaidRepayment.amountDue) + Number(unpaidRepayment.penaltyApplied);
        const totalPaid = Number(unpaidRepayment.amountPaid);
        
        // Get approved payments for this repayment
        const approvedPayments = await prisma.payment.findMany({
          where: {
            repaymentId: unpaidRepayment.id,
            borrowerId: BigInt(req.borrowerId),
            status: 'approved',
          },
        });

        const totalApprovedAmount = approvedPayments.reduce((sum, p) => sum + Number(p.amount), 0);
        const remainingBalance = totalDue - totalPaid;

        if (totalApprovedAmount >= remainingBalance) {
          return res.status(400).json({
            success: false,
            message: 'This repayment period already has approved payments that cover the remaining balance. You cannot submit additional payments.',
          });
        }

        targetRepaymentId = unpaidRepayment.id;

        const dueDate = new Date(unpaidRepayment.dueDate);
        dueDate.setHours(0, 0, 0, 0);
        // Calculate days overdue (matching PHP implementation)
        // Formula: max(0, floor((today - dueDate) / 86400000) - graceDays)
        const daysDiff = Math.floor((today.getTime() - dueDate.getTime()) / (1000 * 60 * 60 * 24)); // Positive if dueDate is in past
        const daysOverdue = Math.max(0, daysDiff - (loan.penaltyGraceDays || 0));
        
        if (daysOverdue > 0) {
          const outstanding = Number(unpaidRepayment.amountDue) - Number(unpaidRepayment.amountPaid);
          penaltyAmount = outstanding * Number(loan.penaltyDailyRate || 0.001) * daysOverdue;
          penaltyAmount = Math.round(penaltyAmount * 100) / 100; // Round to 2 decimal places
        }
      }
    }

    // Verify receipt document if provided
    if (receiptDocumentId) {
      const receiptDoc = await prisma.document.findFirst({
        where: {
          id: BigInt(receiptDocumentId),
          borrowerId: BigInt(req.borrowerId),
          loanId: loan.id,
          documentType: 'RECEIPT',
        },
      });

      if (!receiptDoc) {
        return res.status(404).json({
          success: false,
          message: 'Receipt document not found or does not belong to this loan',
        });
      }
    }

    // Create pending payment (DO NOT update repayments yet - wait for admin approval)
    const payment = await prisma.payment.create({
      data: {
        loanId: loan.id,
        borrowerId: BigInt(req.borrowerId),
        repaymentId: targetRepaymentId,
        receiptDocumentId: receiptDocumentId ? BigInt(receiptDocumentId) : null,
        amount,
        penaltyAmount,
        status: 'pending',
        paidAt: new Date(),
      },
    });

    // Create notification for payment pending approval
    try {
      await prisma.notification.create({
        data: {
          borrowerId: BigInt(req.borrowerId),
          loanId: loan.id,
          type: 'info',
          title: 'Payment Submitted',
          message: `Your payment of ₱${amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} for loan ${loan.reference} has been submitted and is pending admin approval.${penaltyAmount > 0 ? ` Estimated penalty: ₱${penaltyAmount.toFixed(2)}` : ''}`,
          isRead: false,
        },
      });
    } catch (notificationError) {
      console.error('⚠️ Failed to create payment notification:', notificationError);
    }

    return res.status(201).json({
      success: true,
      message: 'Payment submitted successfully. It is pending admin approval.',
      data: {
        paymentId: payment.id.toString(),
        loanId: loan.id.toString(),
        amount: amount.toString(),
        penaltyAmount: penaltyAmount.toString(),
        status: 'pending',
        repaymentId: targetRepaymentId?.toString() || null,
        receiptDocumentId: receiptDocumentId || null,
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

export const getPayments = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const { loanId, status } = req.query;

    // Get payments for this borrower
    const where: any = { 
      borrowerId: BigInt(req.borrowerId),
    };
    
    if (loanId) {
      where.loanId = BigInt(loanId as string);
    }

    if (status) {
      where.status = status as string;
    }

    const payments = await prisma.payment.findMany({
      where,
      include: {
        loan: {
          select: {
            id: true,
            reference: true,
          },
        },
        repayment: {
          select: {
            id: true,
            dueDate: true,
          },
        },
        receiptDocument: {
          select: {
            id: true,
            fileUrl: true,
            fileName: true,
          },
        },
      },
      orderBy: { paidAt: 'desc' },
    });

    // Format payments
    const formattedPayments = payments.map((payment: any) => ({
      id: payment.id.toString(),
      loanId: payment.loanId.toString(),
      loanReference: payment.loan?.reference || null,
      repaymentId: payment.repaymentId?.toString() || null,
      dueDate: payment.repayment?.dueDate || null,
      amount: payment.amount.toString(),
      penaltyAmount: payment.penaltyAmount.toString(),
      status: payment.status,
      receiptUrl: payment.receiptDocument?.fileUrl || null,
      receiptFileName: payment.receiptDocument?.fileName || null,
      paidAt: payment.paidAt,
      approvedAt: payment.approvedAt,
      rejectionReason: payment.rejectionReason,
      createdAt: payment.createdAt,
    }));

    // Return payments array in 'payments' property to match frontend expectations
    res.json({
      success: true,
      payments: formattedPayments,
      data: formattedPayments, // Also include 'data' for backward compatibility
    });
  } catch (error) {
    next(error);
  }
};
