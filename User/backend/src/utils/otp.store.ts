type OtpData = {
    otp: string;
    expiresAt: number;
};

// In-memory store for OTPs (Production should use Redis)
const otpStore = new Map<string, OtpData>();

export const storeOtp = (email: string, otp: string, ttlSeconds: number = 300) => {
    const expiresAt = Date.now() + ttlSeconds * 1000;
    otpStore.set(email, { otp, expiresAt });
};

export const verifyOtp = (email: string, otp: string): boolean => {
    const data = otpStore.get(email);
    if (!data) return false;

    if (Date.now() > data.expiresAt) {
        otpStore.delete(email);
        return false;
    }

    if (data.otp === otp) {
        otpStore.delete(email); // One-time use
        return true;
    }

    return false;
};

export const generateNumericOtp = (length: number = 6): string => {
    let otp = '';
    for (let i = 0; i < length; i++) {
        otp += Math.floor(Math.random() * 10).toString();
    }
    return otp;
};
