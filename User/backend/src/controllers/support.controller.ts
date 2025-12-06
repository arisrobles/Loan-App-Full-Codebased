import { Request, Response, NextFunction } from 'express';
import { z } from 'zod';
import { PrismaClient } from '@prisma/client';
import { AppError } from '../middleware/errorHandler';

const prisma = new PrismaClient();

// Lazy import to avoid circular dependency
let ioInstance: any = null;
export const setSocketIO = (io: any) => {
  ioInstance = io;
};

interface AuthRequest extends Request {
  borrowerId?: string;
}

const createSupportMessageSchema = z.object({
  subject: z.string().min(1, 'Subject is required').max(255, 'Subject must be less than 255 characters'),
  message: z.string().min(1, 'Message is required').max(5000, 'Message must be less than 5000 characters'),
});

export const createSupportMessage = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const validated = createSupportMessageSchema.parse(req.body);

    // Create support message
    const supportMessage = await (prisma as any).supportMessage.create({
      data: {
        borrowerId: BigInt(req.borrowerId),
        subject: validated.subject,
        message: validated.message,
        status: 'pending',
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

    // Emit Socket.io event for real-time updates
    if (ioInstance) {
      // Notify admins
      ioInstance.to('admin').emit('support_message_created', {
        id: supportMessage.id.toString(),
        subject: supportMessage.subject,
        message: supportMessage.message,
        status: supportMessage.status,
        borrower: {
          id: supportMessage.borrower.id.toString(),
          fullName: supportMessage.borrower.fullName,
          email: supportMessage.borrower.email,
        },
        createdAt: supportMessage.createdAt,
      });

      // Notify borrower
      ioInstance.to(`borrower:${req.borrowerId}`).emit('support_message_updated', {
        id: supportMessage.id.toString(),
        subject: supportMessage.subject,
        message: supportMessage.message,
        status: supportMessage.status,
        createdAt: supportMessage.createdAt,
      });
    }

    return res.status(201).json({
      success: true,
      message: 'Support message submitted successfully. We will get back to you within 24-48 hours.',
      data: {
        id: supportMessage.id.toString(),
        subject: supportMessage.subject,
        message: supportMessage.message,
        status: supportMessage.status,
        createdAt: supportMessage.createdAt,
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

export const getSupportMessages = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const supportMessages = await (prisma as any).supportMessage.findMany({
      where: {
        borrowerId: BigInt(req.borrowerId),
      },
      orderBy: {
        createdAt: 'desc',
      },
      include: {
        respondedBy: {
          select: {
            id: true,
            username: true,
          },
        },
      },
    });

    const formattedMessages = supportMessages.map((msg: any) => ({
      id: msg.id.toString(),
      subject: msg.subject,
      message: msg.message,
      status: msg.status,
      adminResponse: msg.adminResponse,
      respondedAt: msg.respondedAt,
      respondedBy: msg.respondedBy ? {
        id: msg.respondedBy.id.toString(),
        username: msg.respondedBy.username,
      } : null,
      createdAt: msg.createdAt,
      updatedAt: msg.updatedAt,
    }));

    return res.json({
      success: true,
      messages: formattedMessages,
      data: formattedMessages,
    });
  } catch (error) {
    return next(error);
  }
};

