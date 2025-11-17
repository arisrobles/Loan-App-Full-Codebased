import { PrismaClient } from '@prisma/client';
import { NextFunction, Response } from 'express';
import { AuthRequest } from '../middleware/auth.middleware';
import { AppError } from '../middleware/errorHandler';

const prisma = new PrismaClient();

export const getCreditScore = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    // Get borrower's loan statistics
    const borrowerId = BigInt(req.borrowerId);
    
    const [totalLoans, approvedLoans, pendingLoans, disbursedLoans] = await Promise.all([
      prisma.loan.count({ 
        where: { 
          borrowerId,
          isActive: true,
        } 
      }),
      prisma.loan.count({ 
        where: { 
          borrowerId, 
          status: 'approved',
          isActive: true,
        } 
      }),
      prisma.loan.count({ 
        where: { 
          borrowerId, 
          status: { in: ['new_application', 'under_review'] },
          isActive: true,
        } 
      }),
      prisma.loan.count({
        where: {
          borrowerId,
          status: 'disbursed',
          isActive: true,
        },
      }),
    ]);

    // Simple credit score calculation based on loan history
    let baseScore = 650; // Starting score
    
    // Adjust based on loan history
    if (totalLoans > 0) {
      const approvalRate = approvedLoans / totalLoans;
      baseScore += approvalRate * 100;
      
      // Bonus for disbursed loans (shows trust)
      const disbursedRate = disbursedLoans / totalLoans;
      baseScore += disbursedRate * 50;
    }

    // Adjust for pending loans (negative impact)
    baseScore -= pendingLoans * 10;

    // Clamp between 300 and 850
    const calculatedScore = Math.max(300, Math.min(850, Math.round(baseScore)));

    // Return score directly to match frontend expectations
    res.json({
      success: true,
      score: calculatedScore,
      maxScore: 850,
      totalLoans,
      approvedLoans,
      disbursedLoans,
      pendingLoans,
      calculatedAt: new Date(),
      // Also include in data for backward compatibility
      data: {
        score: calculatedScore,
        maxScore: 850,
        totalLoans,
        approvedLoans,
        disbursedLoans,
        pendingLoans,
        calculatedAt: new Date(),
      },
    });
  } catch (error) {
    next(error);
  }
};

export const getCreditHistory = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    // Get loan history which serves as credit history
    const loans = await prisma.loan.findMany({
      where: { 
        borrowerId: BigInt(req.borrowerId),
      },
      select: {
        id: true,
        reference: true,
        principalAmount: true,
        status: true,
        applicationDate: true,
        totalPaid: true,
        createdAt: true,
      },
      orderBy: { createdAt: 'desc' },
      take: 12, // Last 12 loans
    });

    const history = loans.map(loan => ({
      id: loan.id.toString(),
      reference: loan.reference,
      amount: loan.principalAmount.toString(),
      status: loan.status,
      applicationDate: loan.applicationDate,
      totalPaid: loan.totalPaid.toString(),
      date: loan.createdAt,
    }));

    res.json({
      success: true,
      data: history,
    });
  } catch (error) {
    next(error);
  }
};
