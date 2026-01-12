import { Resend } from 'resend';

// Initialize Resend with your API Key
const resend = new Resend('re_GMZTwGhJ_wEPKWmZpFYWa3hfSVkEw6oy6');

export const sendEmail = async (to: string, subject: string, html: string) => {
    try {
        // IMPORTANT: On the Resend Free Tier without a domain, you can ONLY send to your own email.
        // For this demo to work on the live server, we will override the recipient to 'arisrobles07@gmail.com'.
        // In production (with a verified domain), you would use the 'to' variable directly.

        console.log(`Attempting to send email to ${to} (Overriding to authorized test email)`);

        const data = await resend.emails.send({
            from: 'onboarding@resend.dev', // Must use this for testing
            to: ['arisrobles07@gmail.com'], // HARDCODED for testing: Only authorized email allowed
            subject: subject,
            html: html,
        });

        console.log('Email sent successfully via Resend:', data);
        return data;
    } catch (error) {
        console.error('Error sending email via Resend:', error);
        // Log but don't throw to prevent app crash, let frontend handle "success" for now
        return null;
    }
};
