import express from 'express';
import { getCreditHistory, getCreditScore } from '../controllers/credit.controller';
import { authenticateToken } from '../middleware/auth.middleware';

const router = express.Router();

router.use(authenticateToken);

router.get('/score', getCreditScore);
router.get('/history', getCreditHistory);

export default router;


