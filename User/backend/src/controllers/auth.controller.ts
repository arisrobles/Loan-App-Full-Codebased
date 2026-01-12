import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcrypt';
import { NextFunction, Request, Response } from 'express';
import { z } from 'zod';
import { generateToken } from '../utils/jwt.util';
import { sendEmail } from '../utils/email.util';

const prisma = new PrismaClient();

// Validation schemas
const loginSchema = z.object({
  username: z.string().min(1, 'Username/Email is required'),
  password: z.string().min(1, 'Password is required'),
});

const registerSchema = z.object({
  email: z.string().email('Invalid email address'),
  password: z.string().min(6, 'Password must be at least 6 characters'),
  fullName: z.string().min(1, 'Full name is required'),
  phone: z.string().optional(),
  address: z.string().optional(),
  reference: z.string().optional(),
  referenceNo: z.string().optional(), // Accept both reference and referenceNo from frontend
});

// Mobile app users will be stored as Borrowers
// For authentication, we'll use a simple approach: store hashed password in a custom field
// OR link to existing borrower by email and create password mapping
// For now, we'll create borrowers with password (you may need to add password column to borrowers table)

export const login = async (req: Request, res: Response, next: NextFunction) => {
  try {
    const validatedData = loginSchema.parse(req.body);
    const { username, password } = validatedData;

    // Try to find borrower by email (mobile app users)
    const borrower = await prisma.borrower.findFirst({
      where: {
        email: username,
        deletedAt: null, // Not deleted
      },
    });

    if (!borrower) {
      return res.status(401).json({
        success: false,
        message: 'Invalid email or password',
      });
    }

    // Check password if it exists
    if (borrower.password) {
      const isPasswordValid = await bcrypt.compare(password, borrower.password);
      if (!isPasswordValid) {
        return res.status(401).json({
          success: false,
          message: 'Invalid email or password',
        });
      }
    } else {
      // Password column doesn't exist yet - allow login but warn
      console.warn('Password authentication not set up. Add password column to borrowers table.');
    }

    // Generate token with borrower ID
    const token = generateToken({
      userId: borrower.id.toString(),
      username: borrower.email || borrower.fullName,
    });

    return res.json({
      success: true,
      message: 'Login successful',
      accessToken: token,
      user: {
        id: borrower.id.toString(),
        email: borrower.email,
        name: borrower.fullName,
        reference: borrower.referenceNo,
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

export const register = async (req: Request, res: Response, next: NextFunction) => {
  try {
    const validatedData = registerSchema.parse(req.body);
    const { email, password, fullName, phone, address, reference, referenceNo } = validatedData;

    // Use referenceNo if provided, otherwise use reference
    const finalReference = referenceNo || reference;

    // Check if borrower already exists
    const existingBorrower = await prisma.borrower.findFirst({
      where: {
        OR: [
          { email },
          ...(finalReference ? [{ referenceNo: finalReference }] : []),
        ],
        deletedAt: null,
      },
    });

    if (existingBorrower) {
      return res.status(400).json({
        success: false,
        message: 'Email or reference number already exists',
      });
    }

    // Hash password
    const hashedPassword = await bcrypt.hash(password, 12);

    // Create borrower (mobile app user)
    const borrower = await prisma.borrower.create({
      data: {
        email,
        password: hashedPassword,
        fullName,
        phone: phone || null,
        address: address || null,
        referenceNo: finalReference || null,
        status: 'active',
      },
    });

    // Generate token
    const token = generateToken({
      userId: borrower.id.toString(),
      username: borrower.email || borrower.fullName,
    });

    return res.status(201).json({
      success: true,
      message: 'Registration successful',
      accessToken: token,
      user: {
        id: borrower.id.toString(),
        email: borrower.email,
        name: borrower.fullName,
        reference: borrower.referenceNo,
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

export const logout = async (_req: Request, res: Response) => {
  return res.json({
    success: true,
    message: 'Logout successful',
  });
};

const forgotPasswordSchema = z.object({
  email: z.string().email('Invalid email address'),
});

const resetPasswordSchema = z.object({
  email: z.string().email('Invalid email address'),
  otp: z.string().length(6, 'OTP must be 6 digits'),
  newPassword: z.string().min(6, 'Password must be at least 6 characters'),
});

export const forgotPassword = async (req: Request, res: Response, next: NextFunction) => {
  try {
    const { email } = forgotPasswordSchema.parse(req.body);

    const borrower = await prisma.borrower.findFirst({
      where: { email, deletedAt: null },
    });

    if (!borrower) {
      return res.status(404).json({
        success: false,
        message: 'Email not found',
      });
    }

    // Generate 6-digit OTP
    const otp = Math.floor(100000 + Math.random() * 900000).toString();
    const expiry = new Date(Date.now() + 60 * 60 * 1000); // 1 hour

    await prisma.borrower.update({
      where: { id: borrower.id },
      data: {
        resetOTP: otp,
        resetOTPExpiry: expiry,
      },
    });

    // Send email
    await sendEmail(
      email,
      'Password Reset OTP - MasterFunds',
      `<p>Your OTP for password reset is: <b>${otp}</b></p><p>This OTP is valid for 1 hour.</p>`
    );

    return res.json({
      success: true,
      message: 'OTP sent to email',
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ success: false, message: error.errors[0].message });
    }
    return next(error);
  }
};

export const resetPassword = async (req: Request, res: Response, next: NextFunction) => {
  try {
    const { email, otp, newPassword } = resetPasswordSchema.parse(req.body);

    const borrower = await prisma.borrower.findFirst({
      where: {
        email,
        resetOTP: otp,
        resetOTPExpiry: { gt: new Date() },
        deletedAt: null,
      },
    });

    if (!borrower) {
      return res.status(400).json({
        success: false,
        message: 'Invalid or expired OTP',
      });
    }

    const hashedPassword = await bcrypt.hash(newPassword, 12);

    await prisma.borrower.update({
      where: { id: borrower.id },
      data: {
        password: hashedPassword,
        resetOTP: null,
        resetOTPExpiry: null,
      },
    });

    return res.json({
      success: true,
      message: 'Password reset successful',
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ success: false, message: error.errors[0].message });
    }
    return next(error);
  }
};


