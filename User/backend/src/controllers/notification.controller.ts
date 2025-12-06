import { PrismaClient } from '@prisma/client';
import { NextFunction, Response } from 'express';
import { AuthRequest } from '../middleware/auth.middleware';
import { AppError } from '../middleware/errorHandler';

const prisma = new PrismaClient();

export const getNotifications = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const { unreadOnly } = req.query;
    
    const where: any = {
      borrowerId: BigInt(req.borrowerId),
    };

    if (unreadOnly === 'true') {
      where.isRead = false;
    }

    // @ts-expect-error - Prisma Client needs regeneration (run: npx prisma generate)
    const notifications = await prisma.notification.findMany({
      where,
      include: {
        loan: {
          select: {
            id: true,
            reference: true,
          },
        },
      },
      orderBy: {
        createdAt: 'desc',
      },
      take: 50, // Limit to last 50 notifications
    });

    // Count unread notifications
    // @ts-expect-error - Prisma Client needs regeneration
    const unreadCount = await prisma.notification.count({
      where: {
        borrowerId: BigInt(req.borrowerId),
        isRead: false,
      },
    });

    return res.json({
      success: true,
      data: notifications.map((notif: any) => ({
        id: notif.id.toString(),
        type: notif.type,
        title: notif.title,
        message: notif.message,
        isRead: notif.isRead,
        readAt: notif.readAt,
        createdAt: notif.createdAt,
        loanId: notif.loanId?.toString() || null,
        loanReference: notif.loan?.reference || null,
      })),
      unreadCount,
    });
  } catch (error) {
    return next(error);
  }
};

export const markAsRead = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const { id } = req.params;

    // Verify notification belongs to borrower
    // @ts-expect-error - Prisma Client needs regeneration
    const notification = await prisma.notification.findFirst({
      where: {
        id: BigInt(id),
        borrowerId: BigInt(req.borrowerId),
      },
    });

    if (!notification) {
      return res.status(404).json({
        success: false,
        message: 'Notification not found',
      });
    }

    // @ts-expect-error - Prisma Client needs regeneration
    await prisma.notification.update({
      where: { id: BigInt(id) },
      data: {
        isRead: true,
        readAt: new Date(),
      },
    });

    return res.json({
      success: true,
      message: 'Notification marked as read',
    });
  } catch (error) {
    return next(error);
  }
};

export const markAllAsRead = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    // @ts-expect-error - Prisma Client needs regeneration
    await prisma.notification.updateMany({
      where: {
        borrowerId: BigInt(req.borrowerId),
        isRead: false,
      },
      data: {
        isRead: true,
        readAt: new Date(),
      },
    });

    return res.json({
      success: true,
      message: 'All notifications marked as read',
    });
  } catch (error) {
    return next(error);
  }
};

