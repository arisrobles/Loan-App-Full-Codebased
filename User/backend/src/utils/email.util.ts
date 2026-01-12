import dns from 'dns';
import nodemailer from 'nodemailer';

// Force IPv4 to avoid IPv6 timeout issues (Common in Render/AWS)
try {
    if (dns.setDefaultResultOrder) {
        dns.setDefaultResultOrder('ipv4first');
    }
} catch (error) {
    console.warn('Could not set default result order:', error);
}

const transporter = nodemailer.createTransport({
    service: 'gmail',
    auth: {
        user: 'arisrobles07@gmail.com',
        pass: 'npct aiia esie ajpp',
    },
    tls: {
        rejectUnauthorized: false // Bypass SSL verification if needed (Force Fix)
    },
    logger: true, // Log SMTP traffic
    debug: true,  // Include debug info
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
