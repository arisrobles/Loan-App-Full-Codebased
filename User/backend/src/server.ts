import cors from 'cors';
import dotenv from 'dotenv';
import express from 'express';
import helmet from 'helmet';
import { createServer } from 'http';
import { errorHandler } from './middleware/errorHandler';
import authRoutes from './routes/auth.routes';
import creditRoutes from './routes/credit.routes';
import documentRoutes from './routes/document.routes';
import loanRoutes from './routes/loan.routes';
import notificationRoutes from './routes/notification.routes';
import paymentRoutes from './routes/payment.routes';
import userRoutes from './routes/user.routes';
import supportRoutes from './routes/support.routes';
import legalRoutes from './routes/legal.routes';
import { initializeSocket } from './socket/socket.server';

dotenv.config();

const app = express();

// Fix for BigInt serialization
(BigInt.prototype as any).toJSON = function () {
  return this.toString();
};

const httpServer = createServer(app);
const PORT = parseInt(process.env.PORT || '8080', 10);

// Initialize Socket.io
const io = initializeSocket(httpServer);

// Export io instance for use in controllers
import { setSocketIO } from './controllers/support.controller';
setSocketIO(io);

// Security middleware
app.use(helmet());

// CORS configuration
app.use(cors({
  origin: process.env.FRONTEND_URL || '*',
  credentials: true,
}));


// Body parsing middleware
app.use(express.json({ limit: '10mb' }));
app.use((err: any, _req: express.Request, res: express.Response, next: express.NextFunction) => {
  if (err instanceof SyntaxError && 'body' in err) {
    console.error('❌ JSON Parse Error:', err.message);
    console.error('🔍 offending body:', (err as any).body);
    return res.status(400).json({
      success: false,
      message: 'Invalid JSON payload received',
      error: err.message
    });
  }
  return next(err);
});
app.use(express.urlencoded({ extended: true, limit: '10mb' }));


// Serve uploaded files
// Serve base uploads directory
app.use('/uploads', express.static('uploads'));
// Serve loan-documents subdirectory
app.use('/uploads/loan-documents', express.static('uploads/loan-documents'));

// Health check
app.get('/health', (_req, res) => {
  res.json({ status: 'OK', message: 'MasterFunds API is running' });
});

// API Routes
const API_VERSION = process.env.API_VERSION || 'v1';
app.use(`/api/${API_VERSION}/auth`, authRoutes);
app.use(`/api/${API_VERSION}/loans`, loanRoutes);
app.use(`/api/${API_VERSION}/payments`, paymentRoutes);
app.use(`/api/${API_VERSION}/documents`, documentRoutes);
app.use(`/api/${API_VERSION}/credit`, creditRoutes);
app.use(`/api/${API_VERSION}/users`, userRoutes);
app.use(`/api/${API_VERSION}/notifications`, notificationRoutes);
app.use(`/api/${API_VERSION}/support`, supportRoutes);
app.use(`/api/${API_VERSION}/legal`, legalRoutes);

// Error handling middleware (must be last)
app.use(errorHandler);

// 404 handler
app.use((_req, res) => {
  res.status(404).json({
    success: false,
    message: 'Route not found',
  });
});

httpServer.listen(PORT, '0.0.0.0', () => {
  const publicUrl = process.env.RENDER_EXTERNAL_URL || process.env.PUBLIC_URL;
  const localBase = `http://localhost:${PORT}`;
  const apiPath = `/api/${API_VERSION}`;
  const healthPath = `/health`;

  // Database Connection Logging
  const dbUrl = process.env.DATABASE_URL;
  if (!dbUrl) {
    console.error('❌ CRITICAL: DATABASE_URL environment variable is NOT set!');
  } else {
    try {
      // Mask credentials for safe logging: schema://user:password@host:port/db
      const maskedUrl = dbUrl.replace(/(:\/\/)([^:]+):([^@]+)@/, '$1$2:****@');
      console.log(`🗄️ Database Config: ${maskedUrl}`);

      const isLocal = dbUrl.includes('localhost') || dbUrl.includes('127.0.0.1');
      if (isLocal && process.env.NODE_ENV !== 'test') {
        console.warn('⚠️ WARNING: using LOCALHOST database connection. This will fail if deployed to cloud/containers (like Render) unless using a sidecar.');
      }
    } catch (e) {
      console.log('🗄️ Database Config: [Complex/Invalid Format]');
    }
  }

  console.log(`🚀 Server running on port ${PORT}`);
  if (publicUrl) {
    console.log(`🔗 Public URL: ${publicUrl}`);
    console.log(`📡 API available at ${publicUrl}${apiPath}`);
    console.log(`🏥 Health check: ${publicUrl}${healthPath}`);
  } else {
    console.log(`📡 API available at ${localBase}${apiPath}`);
    console.log(`🏥 Health check: ${localBase}${healthPath}`);
    console.log(`🌐 Server accessible on network at http://YOUR_LOCAL_IP:${PORT}${apiPath}`);
    console.log(`\n💡 To find your local IP:`);
    console.log(`   Windows: ipconfig | findstr IPv4`);
    console.log(`   Mac/Linux: ifconfig | grep "inet " | grep -v 127.0.0.1`);
  }
  console.log(`💬 Socket.io server initialized`);
});

export { io };
export default app;

