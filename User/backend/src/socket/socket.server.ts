import { Server as HttpServer } from 'http';
import { Server as SocketServer, Socket } from 'socket.io';
import jwt from 'jsonwebtoken';
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

interface AuthenticatedSocket extends Socket {
  userId?: string;
  borrowerId?: string;
  userType?: 'borrower' | 'admin';
}

export const initializeSocket = (httpServer: HttpServer) => {
  const io = new SocketServer(httpServer, {
    cors: {
      origin: '*',
      methods: ['GET', 'POST'],
      credentials: true,
    },
  });

  // Authentication middleware for Socket.io
  io.use(async (socket: AuthenticatedSocket, next) => {
    try {
      const token = socket.handshake.auth.token || socket.handshake.headers.authorization?.replace('Bearer ', '');
      
      if (!token) {
        return next(new Error('Authentication error: No token provided'));
      }

      // Try to decode token (could be JWT or base64 encoded payload from Laravel)
      let decoded: any;
      
      try {
        // First try as JWT
        decoded = jwt.verify(token, process.env.JWT_SECRET || 'your-secret-key') as any;
      } catch (jwtError) {
        // If JWT fails, try as base64 encoded JSON (from Laravel admin)
        try {
          const decodedStr = Buffer.from(token, 'base64').toString('utf-8');
          decoded = JSON.parse(decodedStr);
          
          // Verify expiration
          if (decoded.exp && decoded.exp < Math.floor(Date.now() / 1000)) {
            return next(new Error('Authentication error: Token expired'));
          }
        } catch (base64Error) {
          return next(new Error('Authentication error: Invalid token format'));
        }
      }
      
      // Check if it's an admin token (from Laravel)
      if (decoded.userType === 'admin' || decoded.userId) {
        // Check if it's admin (has userType or username field)
        if (decoded.userType === 'admin' || decoded.username) {
          socket.userId = decoded.userId.toString();
          socket.userType = 'admin';
          console.log(`Admin authenticated: ${decoded.username || decoded.userId}`);
        } else {
          // Verify it's a borrower
          const borrower = await prisma.borrower.findFirst({
            where: {
              id: BigInt(decoded.userId),
              deletedAt: null,
              status: 'active',
            },
          });

          if (borrower) {
            socket.borrowerId = decoded.userId.toString();
            socket.userId = decoded.userId.toString();
            socket.userType = 'borrower';
          } else {
            return next(new Error('Authentication error: Borrower not found or inactive'));
          }
        }
      } else {
        return next(new Error('Authentication error: Invalid token structure'));
      }

      next();
    } catch (error) {
      console.error('Socket authentication error:', error);
      next(new Error('Authentication error: Invalid token'));
    }
  });

  io.on('connection', (socket: AuthenticatedSocket) => {
    console.log(`✅ Socket connected: ${socket.userType} - ${socket.userId}`);

    // Join user-specific room
    if (socket.borrowerId) {
      socket.join(`borrower:${socket.borrowerId}`);
      console.log(`📱 Borrower ${socket.borrowerId} joined their room`);
    }
    if (socket.userId && socket.userType === 'admin') {
      socket.join('admin');
      console.log(`👨‍💼 Admin ${socket.userId} joined admin room`);
    }
    
    // Handle admin room join request
    socket.on('join_admin_room', () => {
      if (socket.userType === 'admin') {
        socket.join('admin');
        console.log(`👨‍💼 Admin ${socket.userId} joined admin room via request`);
      }
    });

    // Join support message room
    socket.on('join_support_message', async (messageId: string) => {
      socket.join(`support_message:${messageId}`);
      console.log(`💬 ${socket.userType} ${socket.userId} joined support message ${messageId}`);
    });

    // Leave support message room
    socket.on('leave_support_message', (messageId: string) => {
      socket.leave(`support_message:${messageId}`);
    });

    // Handle new support message (from user)
    socket.on('new_support_message', async (data: { subject: string; message: string }) => {
      try {
        if (!socket.borrowerId) {
          socket.emit('error', { message: 'Unauthorized' });
          return;
        }

        // Create message in database
        const supportMessage = await (prisma as any).supportMessage.create({
          data: {
            borrowerId: BigInt(socket.borrowerId),
            subject: data.subject,
            message: data.message,
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

        // Notify admins
        io.to('admin').emit('support_message_created', {
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

        // Confirm to sender
        socket.emit('support_message_sent', {
          id: supportMessage.id.toString(),
          subject: supportMessage.subject,
          message: supportMessage.message,
          status: supportMessage.status,
          createdAt: supportMessage.createdAt,
        });

        // Notify borrower in their room
        io.to(`borrower:${socket.borrowerId}`).emit('support_message_updated', {
          id: supportMessage.id.toString(),
          subject: supportMessage.subject,
          message: supportMessage.message,
          status: supportMessage.status,
          createdAt: supportMessage.createdAt,
        });
      } catch (error) {
        console.error('Error creating support message:', error);
        socket.emit('error', { message: 'Failed to send message' });
      }
    });

    // Handle admin response
    socket.on('admin_response', async (data: { messageId: string; response: string; status?: string }) => {
      try {
        if (socket.userType !== 'admin' || !socket.userId) {
          socket.emit('error', { message: 'Unauthorized' });
          return;
        }

        const messageId = BigInt(data.messageId);
        const status = data.status || 'in_progress';

        // Update message in database
        const supportMessage = await (prisma as any).supportMessage.update({
          where: { id: messageId },
          data: {
            adminResponse: data.response,
            status: status as any,
            respondedByUserId: BigInt(socket.userId),
            respondedAt: new Date(),
          },
          include: {
            borrower: {
              select: {
                id: true,
                fullName: true,
                email: true,
              },
            },
            respondedBy: {
              select: {
                id: true,
                username: true,
              },
            },
          },
        });

        // Notify borrower
        io.to(`borrower:${supportMessage.borrowerId.toString()}`).emit('support_message_response', {
          id: supportMessage.id.toString(),
          adminResponse: supportMessage.adminResponse,
          status: supportMessage.status,
          respondedBy: supportMessage.respondedBy ? {
            id: supportMessage.respondedBy.id.toString(),
            username: supportMessage.respondedBy.username,
          } : null,
          respondedAt: supportMessage.respondedAt,
        });

        // Notify all admins
        io.to('admin').emit('support_message_updated', {
          id: supportMessage.id.toString(),
          subject: supportMessage.subject,
          message: supportMessage.message,
          adminResponse: supportMessage.adminResponse,
          status: supportMessage.status,
          borrower: {
            id: supportMessage.borrower.id.toString(),
            fullName: supportMessage.borrower.fullName,
            email: supportMessage.borrower.email,
          },
          respondedBy: supportMessage.respondedBy ? {
            id: supportMessage.respondedBy.id.toString(),
            username: supportMessage.respondedBy.username,
          } : null,
          respondedAt: supportMessage.respondedAt,
          updatedAt: supportMessage.updatedAt,
        });

        // Notify in specific message room
        io.to(`support_message:${data.messageId}`).emit('support_message_response', {
          id: supportMessage.id.toString(),
          adminResponse: supportMessage.adminResponse,
          status: supportMessage.status,
          respondedBy: supportMessage.respondedBy ? {
            id: supportMessage.respondedBy.id.toString(),
            username: supportMessage.respondedBy.username,
          } : null,
          respondedAt: supportMessage.respondedAt,
        });
      } catch (error) {
        console.error('Error sending admin response:', error);
        socket.emit('error', { message: 'Failed to send response' });
      }
    });

    socket.on('disconnect', () => {
      console.log(`❌ Socket disconnected: ${socket.userType} - ${socket.userId}`);
    });
  });

  return io;
};

