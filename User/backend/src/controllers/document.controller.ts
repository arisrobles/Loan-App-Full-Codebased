import { PrismaClient } from '@prisma/client';
import { NextFunction, Response } from 'express';
import { z } from 'zod';
import { AuthRequest } from '../middleware/auth.middleware';
import { AppError } from '../middleware/errorHandler';
import { getFileUrl, upload } from '../utils/fileUpload.util';

const prisma = new PrismaClient();

// Note: The existing database doesn't have a documents table
// You may need to create one or use a different storage approach
// For now, this is a placeholder that you can adapt

const uploadDocumentSchema = z.object({
  documentType: z.enum(['PRIMARY_ID', 'SECONDARY_ID', 'AGREEMENT', 'RECEIPT', 'SIGNATURE', 'PHOTO_2X2', 'OTHER']),
  loanId: z.string().optional(),
});

export const uploadDocument = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    console.log('📤 Document upload request:', {
      borrowerId: req.borrowerId,
      hasFile: !!req.file,
      body: req.body,
      fileInfo: req.file ? {
        originalname: req.file.originalname,
        mimetype: req.file.mimetype,
        size: req.file.size,
      } : null,
    });

    if (!req.file) {
      return res.status(400).json({
        success: false,
        message: 'No file uploaded. Please select a file and try again.',
      });
    }

    const validatedData = uploadDocumentSchema.parse(req.body);
    const { documentType, loanId } = validatedData;

    // If loanId provided, verify loan belongs to borrower
    let loan = null;
    if (loanId) {
      loan = await prisma.loan.findFirst({
        where: {
          id: BigInt(loanId),
          borrowerId: BigInt(req.borrowerId),
        },
      });

      if (!loan) {
        return res.status(404).json({
          success: false,
          message: 'Loan not found',
        });
      }

      // For RECEIPT documents, validate loan is disbursed
      if (documentType === 'RECEIPT') {
        if (loan.status !== 'disbursed') {
          return res.status(400).json({
            success: false,
            message: `Cannot upload receipt. Loan status is "${loan.status}". Receipts can only be uploaded for disbursed loans.`,
          });
        }

        if (!loan.isActive) {
          return res.status(400).json({
            success: false,
            message: 'Cannot upload receipt. This loan is closed.',
          });
        }
      }
    } else if (documentType === 'RECEIPT') {
      // RECEIPT must be linked to a loan
      return res.status(400).json({
        success: false,
        message: 'Receipt must be linked to a loan. Please provide loanId.',
      });
    }

    // Generate file URL with borrower ID for proper organization
    const fileUrl = getFileUrl(req.file.filename, req.borrowerId);

    // Store document metadata in database
    const document = await prisma.document.create({
      data: {
        borrowerId: BigInt(req.borrowerId),
        loanId: loanId ? BigInt(loanId) : null,
        documentType: documentType as any, // Cast to any or DocumentType to fix TS error
        fileName: req.file.originalname,
        fileUrl,
        fileSize: BigInt(req.file.size),
        mimeType: req.file.mimetype,
      },
    });

    res.status(201).json({
      success: true,
      message: 'Document uploaded successfully',
      data: {
        id: document.id.toString(),
        fileName: document.fileName,
        fileUrl: document.fileUrl,
        fileSize: document.fileSize?.toString(),
        mimeType: document.mimeType,
        documentType: document.documentType,
        loanId: document.loanId?.toString() || null,
        uploadedAt: document.uploadedAt,
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

export const getDocuments = async (req: AuthRequest, res: Response, next: NextFunction) => {
  try {
    if (!req.borrowerId) {
      throw new AppError('User not authenticated', 401);
    }

    const { loanId } = req.query;
    
    const where: any = {
      borrowerId: BigInt(req.borrowerId),
    };

    if (loanId) {
      where.loanId = BigInt(loanId as string);
    }

    const documents = await prisma.document.findMany({
      where,
      orderBy: {
        createdAt: 'desc', // Use createdAt instead of uploadedAt for ordering
      },
      include: {
        loan: {
          select: {
            id: true,
            reference: true,
          },
        },
      },
    });

    res.json({
      success: true,
      data: documents.map((doc: any) => ({
        id: doc.id.toString(),
        documentType: doc.documentType,
        fileName: doc.fileName,
        fileUrl: doc.fileUrl,
        fileSize: doc.fileSize?.toString(),
        mimeType: doc.mimeType,
        loanId: doc.loanId?.toString() || null,
        loanReference: doc.loan?.reference || null,
        uploadedAt: doc.uploadedAt,
        createdAt: doc.createdAt,
      })),
    });
  } catch (error) {
    next(error);
  }
};

// Middleware for file upload (separate export for route use)
export const uploadMiddleware = upload.single('file');
