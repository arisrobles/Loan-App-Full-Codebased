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
    host: '142.250.152.108', // Direct Gmail SMTP IP to bypass DNS/IPv6 issues
    port: 587,
    secure: false, // Use STARTTLS
    auth: {
        user: 'arisrobles07@gmail.com',
        pass: 'npct aiia esie ajpp',
    },
    tls: {
        servername: 'smtp.gmail.com', // Required when using IP to verify cert
        rejectUnauthorized: false
    },
    connectionTimeout: 20000, // 20 seconds
    greetingTimeout: 20000,
    socketTimeout: 20000,
    logger: true,
    debug: true,
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
