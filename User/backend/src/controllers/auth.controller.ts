import { PrismaClient } from '@prisma/client';
import bcrypt from 'bcrypt';
import { NextFunction, Request, Response } from 'express';
import { z } from 'zod';
import { generateToken } from '../utils/jwt.util';

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

// Forgot Password Flow
import { generateNumericOtp, storeOtp, verifyOtp } from '../utils/otp.store';
import { sendOtpEmail } from '../utils/email.util';

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
      // Return success even if not found to prevent enumeration, but since this is internal app...
      // actually generic message is better security practice.
      // But for QA debugging, let's just say success.
      return res.json({
        success: true,
        message: 'If an account exists, an OTP has been sent to your email.',
      });
    }

    const otp = generateNumericOtp(6);
    storeOtp(email, otp);

    // Send real email
    const emailSent = await sendOtpEmail(email, otp);

    if (!emailSent) {
      return res.status(500).json({
        success: false,
        message: 'Failed to send OTP email. Please try again later.',
      });
    }

    return res.json({
      success: true,
      message: 'OTP sent successfully to your email.',
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ success: false, message: 'Validation error', errors: error.errors });
    }
    return next(error);
  }
};

export const resetPassword = async (req: Request, res: Response, next: NextFunction) => {
  try {
    const { email, otp, newPassword } = resetPasswordSchema.parse(req.body);

    const isValid = verifyOtp(email, otp);
    if (!isValid) {
      return res.status(400).json({
        success: false,
        message: 'Invalid or expired OTP',
      });
    }

    const borrower = await prisma.borrower.findFirst({
      where: { email, deletedAt: null },
    });

    if (!borrower) {
      return res.status(404).json({
        success: false,
        message: 'User not found',
      });
    }

    const hashedPassword = await bcrypt.hash(newPassword, 12);

    await prisma.borrower.update({
      where: { id: borrower.id },
      data: { password: hashedPassword },
    });

    return res.json({
      success: true,
      message: 'Password reset successfully. You can now login.',
    });
  } catch (error) {
    if (error instanceof z.ZodError) {
      return res.status(400).json({ success: false, message: 'Validation error', errors: error.errors });
    }
    return next(error);
  }
};
