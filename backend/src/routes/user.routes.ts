import express from 'express';
import { getProfile, updateProfile } from '../controllers/user.controller';
import { authenticateToken } from '../middleware/auth.middleware';

const router = express.Router();

router.use(authenticateToken);

router.get('/profile', getProfile);
router.put('/profile', updateProfile);

export default router;


