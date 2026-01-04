import { PrismaClient } from '@prisma/client';
import { NextFunction, Request, Response } from 'express';
import jwt from 'jsonwebtoken';

const prisma = new PrismaClient();

export interface AuthRequest extends Request {
  borrowerId?: string;
  borrower?: {
    id: string;
    email: string;
    fullName: string;
  };
}

export const authenticateToken = async (
  req: AuthRequest,
  res: Response,
  next: NextFunction
) => {
  try {
    const authHeader = req.headers['authorization'];
    const token = authHeader && authHeader.split(' ')[1]; // Bearer TOKEN

    if (!token) {
      return res.status(401).json({
        success: false,
        message: 'Access token required',
      });
    }

    const jwtSecret = process.env.JWT_SECRET;
    if (!jwtSecret) {
      throw new Error('JWT_SECRET is not defined');
    }

    const decoded = jwt.verify(token, jwtSecret) as { userId: string };
    
    // Verify borrower still exists
    const borrower = await prisma.borrower.findFirst({
      where: { 
        id: BigInt(decoded.userId),
        deletedAt: null,
      },
      select: { 
        id: true, 
        email: true, 
        fullName: true,
        status: true,
      },
    });

    if (!borrower) {
      return res.status(401).json({
        success: false,
        message: 'User not found or account deleted',
      });
    }

    if (borrower.status !== 'active') {
      return res.status(403).json({
        success: false,
        message: 'Account is not active',
      });
    }

    req.borrowerId = borrower.id.toString();
    req.borrower = {
      id: borrower.id.toString(),
      email: borrower.email || '',
      fullName: borrower.fullName,
    };
    return next();
  } catch (error) {
    if (error instanceof jwt.JsonWebTokenError) {
      return res.status(403).json({
        success: false,
        message: 'Invalid or expired token',
      });
    }
    return next(error);
  }
};
