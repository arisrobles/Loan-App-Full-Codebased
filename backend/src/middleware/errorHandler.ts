import { NextFunction, Request, Response } from 'express';

export class AppError extends Error {
  statusCode: number;
  isOperational: boolean;

  constructor(message: string, statusCode: number = 500) {
    super(message);
    this.statusCode = statusCode;
    this.isOperational = true;
    Error.captureStackTrace(this, this.constructor);
  }
}

export const errorHandler = (
  err: Error | AppError,
  req: Request,
  res: Response,
  next: NextFunction
) => {
  if (err instanceof AppError) {
    return res.status(err.statusCode).json({
      success: false,
      message: err.message,
    });
  }

  // Handle Prisma errors
  if (err.name === 'PrismaClientKnownRequestError') {
    return res.status(400).json({
      success: false,
      message: 'Database operation failed',
    });
  }

  // Default error
  console.error('❌ Error:', err);
  console.error('📋 Error name:', err.name);
  console.error('📝 Error message:', err.message);
  if (err.stack) {
    console.error('🔍 Error stack:', err.stack);
  }
  
  // Log full error details in development
  if (process.env.NODE_ENV !== 'production') {
    console.error('💾 Full error:', JSON.stringify(err, Object.getOwnPropertyNames(err), 2));
  }
  
  res.status(500).json({
    success: false,
    message: process.env.NODE_ENV === 'production' 
      ? 'Internal server error' 
      : err.message,
    ...(process.env.NODE_ENV !== 'production' && { 
      stack: err.stack,
      name: err.name,
    }),
  });
};


