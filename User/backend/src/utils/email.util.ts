import dns from 'dns';
import nodemailer from 'nodemailer';

// Force IPv4 to avoid IPv6 timeout issues in some cloud environments
try {
    if (dns.setDefaultResultOrder) {
        dns.setDefaultResultOrder('ipv4first');
    }
} catch (e) {
    console.warn('Could not set default result order', e);
}

const transporter = nodemailer.createTransport({
    host: 'smtp.gmail.com',
    port: 465,
    secure: true, // Use SSL
    auth: {
        user: 'arisrobles07@gmail.com',
        pass: 'npct aiia esie ajpp',
    },
    tls: {
        rejectUnauthorized: false, // Bypass strict SSL checks
    },
    connectionTimeout: 20000,
    greetingTimeout: 20000,
    socketTimeout: 20000,
    debug: true,
    logger: true,
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
