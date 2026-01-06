
import axios from 'axios';
import dotenv from 'dotenv';
import path from 'path';

// Load env
const envPath = path.resolve(__dirname, '../.env');
dotenv.config({ path: envPath });

const API_URL = 'http://localhost:8080/api/v1';
const TEST_EMAIL = process.env.EMAIL_USER; // Use the sender email as the test user too

async function testForgotPassword() {
    if (!TEST_EMAIL) {
        console.error('❌ EMAIL_USER not found in .env');
        return;
    }

    console.log(`Testing Forgot Password for: ${TEST_EMAIL}`);
    console.log(`Target URL: ${API_URL}/auth/forgot-password`);

    try {
        const response = await axios.post(`${API_URL}/auth/forgot-password`, {
            email: TEST_EMAIL
        });

        console.log('✅ Response Status:', response.status);
        console.log('✅ Response Data:', response.data);
    } catch (error: any) {
        console.error('❌ Request Failed!');
        if (error.response) {
            console.error('Status:', error.response.status);
            console.error('Data:', error.response.data);
        } else {
            console.error('Error:', error.message);
        }
    }
}

testForgotPassword();
