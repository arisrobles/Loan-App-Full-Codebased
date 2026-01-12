import { Resend } from 'resend';

const resend = new Resend('re_GMZTwGhJ_wEPKWmZpFYWa3hfSVkEw6oy6');

export const sendEmail = async (to: string, subject: string, html: string) => {
    try {
        const data = await resend.emails.send({
            from: 'onboarding@resend.dev', // Default testing domain
            to: [to], // Resend requires 'to' be an array or string, but for testing strictly to verified email or your own
            subject: subject,
            html: html,
        });

        console.log('Email sent successfully:', data);
        return data;
    } catch (error) {
        console.error('Error sending email via Resend:', error);
        throw error;
    }
};
