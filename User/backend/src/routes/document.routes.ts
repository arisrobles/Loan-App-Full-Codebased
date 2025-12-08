import express from 'express';
import { getDocuments, uploadDocument, uploadMiddleware } from '../controllers/document.controller';
import { authenticateToken } from '../middleware/auth.middleware';

const router = express.Router();

router.use(authenticateToken);

// Handle multer errors
const handleMulterError = (err: any, req: any, res: any, next: any) => {
  if (err) {
    console.error('❌ Multer error:', err);
    if (err.code === 'LIMIT_FILE_SIZE') {
      return res.status(400).json({
        success: false,
        message: 'File too large. Maximum size is 5MB.',
      });
    }
    if (err.message?.includes('Invalid file type')) {
      return res.status(400).json({
        success: false,
        message: err.message,
      });
    }
    return res.status(400).json({
      success: false,
      message: err.message || 'File upload error',
    });
  }
  next();
};

router.post('/upload', uploadMiddleware, handleMulterError, uploadDocument);
router.get('/', getDocuments);

export default router;


