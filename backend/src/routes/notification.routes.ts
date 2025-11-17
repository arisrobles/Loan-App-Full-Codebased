import express from 'express';
import { getNotifications, markAsRead, markAllAsRead } from '../controllers/notification.controller';
import { authenticateToken } from '../middleware/auth.middleware';

const router = express.Router();

router.use(authenticateToken);

router.get('/', getNotifications);
router.patch('/:id/read', markAsRead);
router.patch('/read-all', markAllAsRead);

export default router;

