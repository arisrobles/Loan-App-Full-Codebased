import express from 'express';
import {
  generateLoanAgreement,
  generateDemandLetter,
  generateGuarantyAgreement,
  generateAgreementPreview,
  generateGuarantyAgreementPreview,
  generateDemandLetterPreview,
} from '../controllers/legal.controller';
import { authenticateToken } from '../middleware/auth.middleware';

const router = express.Router();

// All legal document routes require authentication
router.use(authenticateToken);

// Preview endpoints (for application process - no loan created)
router.post('/agreement/preview', generateAgreementPreview);
router.post('/guaranty-agreement/preview', generateGuarantyAgreementPreview);
router.post('/demand-letter/preview', generateDemandLetterPreview);

// Actual generation endpoints (require existing loan)
router.post('/agreement', generateLoanAgreement);
router.post('/demand-letter', generateDemandLetter);
router.post('/guaranty-agreement', generateGuarantyAgreement);

export default router;

