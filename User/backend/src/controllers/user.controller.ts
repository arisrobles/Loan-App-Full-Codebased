import { PrismaClient } from '@prisma/client';
import { NextFunction, Response } from 'express';
import { z } from 'zod';
import { AuthRequest } from '../middleware/auth.middleware';
import { AppError } from '../middleware/errorHandler';

const prisma = new PrismaClient();

const updateProfileSchema = z.object({
  fullName: z.string().min(1).optional(),
  email: z.string().email().optional(),
  phone: z.string().optional(),
  address: z.string().optional(),
  reference: z.string().optional(),
  sex: z.string().optional(),
  birthday: z.string().optional(), // Will be converted to Date
  occupation: z.string().optional(),
  monthlyIncome: z.number().optional(),
  civilStatus: z.string().optional(),
});

export const getProfile = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const borrower = await prisma.borrower.findUnique({
      where: { id: BigInt(req.borrowerId) },
      select: {
        id: true,
        fullName: true,
        email: true,
        phone: true,
        address: true,
        referenceNo: true,
        sex: true,
        occupation: true,
        birthday: true,
        monthlyIncome: true,
        civilStatus: true,
        status: true,
        createdAt: true,
      },
    });

    if (!borrower) {
      return res.status(404).json({
        success: false,
        message: 'User not found',
      });
    }

    // Return data directly to match frontend expectations
    return res.json({
      id: borrower.id.toString(),
      email: borrower.email,
      fullName: borrower.fullName,
      name: borrower.fullName,
      phone: borrower.phone,
      address: borrower.address,
      referenceNo: borrower.referenceNo,
      reference: borrower.referenceNo,
      sex: borrower.sex,
      occupation: borrower.occupation,
      birthday: borrower.birthday,
      monthlyIncome: borrower.monthlyIncome?.toString(),
      civilStatus: borrower.civilStatus,
      status: borrower.status,
      createdAt: borrower.createdAt,
    });
  } catch (error) {
    return next(error);
  }
};

export const updateProfile = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const validatedData = updateProfileSchema.parse(req.body);

    // Build update data object with all fields
    const updateData: any = {};

    // Required/important fields
    if (validatedData.fullName !== undefined) updateData.fullName = validatedData.fullName;
    if (validatedData.email !== undefined) updateData.email = validatedData.email;

    // Optional string fields - allow clearing by sending empty string
    if (validatedData.phone !== undefined) updateData.phone = validatedData.phone || null;
    if (validatedData.address !== undefined) updateData.address = validatedData.address || null;
    if (validatedData.reference !== undefined) updateData.referenceNo = validatedData.reference || null;
    if (validatedData.sex !== undefined) updateData.sex = validatedData.sex || null;
    if (validatedData.occupation !== undefined) updateData.occupation = validatedData.occupation || null;
    if (validatedData.civilStatus !== undefined) updateData.civilStatus = validatedData.civilStatus || null;

    // Birthday - convert string to Date safely
    if (validatedData.birthday !== undefined) {
      if (!validatedData.birthday) {
        updateData.birthday = null;
      } else {
        const date = new Date(validatedData.birthday);
        if (!isNaN(date.getTime())) {
          updateData.birthday = date;
        }
      }
    }

    // Monthly income - convert to Decimal
    if (validatedData.monthlyIncome !== undefined) {
      // Handle 0 correctly (don't convert to null)
      updateData.monthlyIncome = (validatedData.monthlyIncome === null || isNaN(validatedData.monthlyIncome))
        ? null
        : validatedData.monthlyIncome;
    }

    // Check if email already exists (if being updated)
    if (validatedData.email) {
      const existingBorrower = await prisma.borrower.findFirst({
        where: {
          email: validatedData.email,
          id: { not: BigInt(req.borrowerId) },
          deletedAt: null,
        },
      });

      if (existingBorrower) {
        return res.status(400).json({
          success: false,
          message: 'Email already exists',
        });
      }
    }

    const borrower = await prisma.borrower.update({
      where: { id: BigInt(req.borrowerId) },
      data: updateData,
      select: {
        id: true,
        fullName: true,
        email: true,
        phone: true,
        address: true,
        referenceNo: true,
        sex: true,
        occupation: true,
        birthday: true,
        monthlyIncome: true,
        civilStatus: true,
        updatedAt: true,
      },
    });

    return res.json({
      success: true,
      message: 'Profile updated successfully',
      data: {
        id: borrower.id.toString(),
        email: borrower.email,
        fullName: borrower.fullName,
        name: borrower.fullName,
        phone: borrower.phone,
        address: borrower.address,
        reference: borrower.referenceNo,
        referenceNo: borrower.referenceNo,
        sex: borrower.sex,
        occupation: borrower.occupation,
        birthday: borrower.birthday,
        monthlyIncome: borrower.monthlyIncome?.toString(),
        civilStatus: borrower.civilStatus,
        updatedAt: borrower.updatedAt,
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
