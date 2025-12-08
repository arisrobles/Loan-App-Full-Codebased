import fs from 'fs';
import multer from 'multer';
import path from 'path';
import { AuthRequest } from '../middleware/auth.middleware';

// Base uploads directory
const baseUploadDir = process.env.UPLOAD_DIR || './uploads';
const loanDocumentsDir = path.join(baseUploadDir, 'loan-documents');

// Ensure base uploads directory exists
if (!fs.existsSync(baseUploadDir)) {
  fs.mkdirSync(baseUploadDir, { recursive: true });
}

// Configure storage with borrower ID-based organization
const storage = multer.diskStorage({
  destination: (req: any, _file, cb) => {
    try {
      // Get borrower ID from request (set by auth middleware)
      const borrowerId = (req as AuthRequest).borrowerId;
      
      if (!borrowerId) {
        // Fallback to base directory if no borrower ID
        cb(null, baseUploadDir);
        return;
      }

      // Create directory structure: uploads/loan-documents/{borrowerId}/
      const borrowerDir = path.join(loanDocumentsDir, borrowerId.toString());
      
      // Ensure borrower directory exists
      if (!fs.existsSync(borrowerDir)) {
        fs.mkdirSync(borrowerDir, { recursive: true });
      }

      cb(null, borrowerDir);
    } catch (error) {
      console.error('Error setting upload destination:', error);
      // Fallback to base directory on error
      cb(null, baseUploadDir);
    }
  },
  filename: (_req, file, cb) => {
    const uniqueSuffix = Date.now() + '-' + Math.round(Math.random() * 1e9);
    const ext = path.extname(file.originalname);
    cb(null, `${file.fieldname}-${uniqueSuffix}${ext}`);
  },
});

// File filter
const fileFilter = (_req: any, file: Express.Multer.File, cb: multer.FileFilterCallback) => {
  const allowedTypes = process.env.ALLOWED_FILE_TYPES?.split(',') || [
    'image/jpeg',
    'image/png',
    'image/jpg',
    'application/pdf',
  ];

  // Allow if mimetype matches or if no mimetype (some clients don't send it)
  if (!file.mimetype || allowedTypes.includes(file.mimetype)) {
    cb(null, true);
  } else {
    console.warn(`File type rejected: ${file.mimetype} for file ${file.originalname}`);
    cb(new Error('Invalid file type. Allowed types: ' + allowedTypes.join(', ')));
  }
};

// Configure multer
export const upload = multer({
  storage,
  fileFilter,
  limits: {
    fileSize: parseInt(process.env.MAX_FILE_SIZE || '5242880'), // 5MB default
  },
});

// Helper to get file URL
export const getFileUrl = (filename: string, borrowerId?: string | bigint): string => {
  if (borrowerId) {
    return `/uploads/loan-documents/${borrowerId.toString()}/${filename}`;
  }
  // Fallback for old files or files without borrower ID
  return `/uploads/${filename}`;
};


