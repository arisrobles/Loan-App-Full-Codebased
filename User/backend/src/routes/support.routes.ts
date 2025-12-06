import { Router } from 'express';
import { createSupportMessage, getSupportMessages } from '../controllers/support.controller';
import { authenticateToken } from '../middleware/auth.middleware';

const router = Router();

// All routes require authentication
router.use(authenticateToken);

// Create a new support message
router.post('/', createSupportMessage);

// Get user's support messages
router.get('/', getSupportMessages);

export default router;

