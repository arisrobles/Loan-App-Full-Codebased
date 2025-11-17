import cors from 'cors';
import dotenv from 'dotenv';
import express from 'express';
import helmet from 'helmet';
import { errorHandler } from './middleware/errorHandler';
import authRoutes from './routes/auth.routes';
import creditRoutes from './routes/credit.routes';
import documentRoutes from './routes/document.routes';
import loanRoutes from './routes/loan.routes';
import notificationRoutes from './routes/notification.routes';
import paymentRoutes from './routes/payment.routes';
import userRoutes from './routes/user.routes';

dotenv.config();

const app = express();
const PORT = parseInt(process.env.PORT || '8080', 10);

// Security middleware
app.use(helmet());

// CORS configuration
app.use(cors({
  origin: process.env.FRONTEND_URL || '*',
  credentials: true,
}));

// Body parsing middleware
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// Serve uploaded files
app.use('/uploads', express.static('uploads'));

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

// Error handling middleware (must be last)
app.use(errorHandler);

// 404 handler
app.use((_req, res) => {
  res.status(404).json({
    success: false,
    message: 'Route not found',
  });
});

app.listen(PORT, '0.0.0.0', () => {
  console.log(`🚀 Server running on port ${PORT}`);
  console.log(`📡 API available at http://localhost:${PORT}/api/${API_VERSION}`);
  console.log(`🌐 Server accessible on network at http://YOUR_LOCAL_IP:${PORT}/api/${API_VERSION}`);
  console.log(`🏥 Health check: http://localhost:${PORT}/health`);
  console.log(`\n💡 To find your local IP:`);
  console.log(`   Windows: ipconfig | findstr IPv4`);
  console.log(`   Mac/Linux: ifconfig | grep "inet " | grep -v 127.0.0.1`);
});

export default app;


