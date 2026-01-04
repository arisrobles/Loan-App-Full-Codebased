import nodemailer from 'nodemailer';
import dotenv from 'dotenv';
dotenv.config();

const transporter = nodemailer.createTransport({
    service: 'gmail',
    auth: {
        user: process.env.EMAIL_USER,
        pass: process.env.EMAIL_PASS,
    },
});

export const sendOtpEmail = async (to: string, otp: string): Promise<boolean> => {
    try {
        const info = await transporter.sendMail({
            from: `"MasterFunds Support" <${process.env.EMAIL_USER}>`,
            to,
            subject: 'Password Reset OTP - MasterFunds',
            text: `Your One-Time Password (OTP) for password reset is: ${otp}. It expires in 5 minutes.`,
            html: `
        <div style="font-family: Arial, sans-serif; padding: 20px; color: #333; max-width: 600px; margin: 0 auto; border: 1px solid #e0e0e0; border-radius: 8px;">
          <h2 style="color: #302b63; text-align: center;">Password Reset Request</h2>
          <p>Hello,</p>
          <p>You requested to reset your password for your MasterFunds account.</p>
          <div style="background-color: #f8f9fa; padding: 15px; text-align: center; border-radius: 6px; margin: 20px 0;">
            <p style="margin: 0; font-size: 14px; color: #666;">Your One-Time Password (OTP)</p>
            <h1 style="color: #302b63; letter-spacing: 5px; margin: 10px 0; font-size: 32px;">${otp}</h1>
          </div>
          <p>This code will expire in 5 minutes.</p>
          <p style="font-size: 12px; color: #888;">If you did not request this, please ignore this email.</p>
          <hr style="border: none; border-top: 1px solid #eee; margin: 20px 0;" />
          <p style="font-size: 12px; color: #aaa; text-align: center;">© ${new Date().getFullYear()} MasterFunds. All rights reserved.</p>
        </div>
      `,
        });
        console.log('📧 Email sent: %s', info.messageId);
        return true;
    } catch (error) {
        console.error('❌ Error sending email:', error);
        return false;
    }
};
