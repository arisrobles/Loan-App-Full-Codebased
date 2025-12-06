import express from 'express';
import { createPayment, getPayments } from '../controllers/payment.controller';
import { authenticateToken } from '../middleware/auth.middleware';

const router = express.Router();

router.use(authenticateToken);

router.post('/', createPayment);
router.get('/', getPayments);

export default router;


