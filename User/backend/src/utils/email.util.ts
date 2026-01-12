import dns from 'dns';
import nodemailer from 'nodemailer';

// Force IPv4 to avoid IPv6 timeout issues in some cloud environments
dns.setDefaultResultOrder('ipv4first');

const transporter = nodemailer.createTransport({
    host: 'smtp.gmail.com',
    port: 587,
    secure: false, // Use STARTTLS
    auth: {
        user: 'arisrobles07@gmail.com',
        pass: 'npct aiia esie ajpp',
    },
});

export const sendEmail = async (to: string, subject: string, html: string) => {
    try {
        const info = await transporter.sendMail({
            from: '"MasterFunds Support" <arisrobles07@gmail.com>',
            to,
            subject,
            html,
        });
        console.log('Message sent: %s', info.messageId);
        return info;
    } catch (error) {
        console.error('Error sending email:', error);
        throw error;
    }
};
