import express from 'express';
import {
    cancelLoan,
    createLoan,
    getLoanById,
    getLoanStatus,
    getLoans,
} from '../controllers/loan.controller';
import { authenticateToken } from '../middleware/auth.middleware';

const router = express.Router();

// All loan routes require authentication
router.use(authenticateToken);

router.post('/', createLoan);
router.get('/', getLoans);
router.get('/:id', getLoanById);
router.get('/:id/status', getLoanStatus);
router.patch('/:id/cancel', cancelLoan);

export default router;


