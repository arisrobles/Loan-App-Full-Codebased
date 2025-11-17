import express from 'express';
import { getDocuments, uploadDocument, uploadMiddleware } from '../controllers/document.controller';
import { authenticateToken } from '../middleware/auth.middleware';

const router = express.Router();

router.use(authenticateToken);

router.post('/upload', uploadMiddleware, uploadDocument);
router.get('/', getDocuments);

export default router;


