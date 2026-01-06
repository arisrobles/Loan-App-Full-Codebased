
import { PrismaClient } from '@prisma/client';
import dotenv from 'dotenv';
import path from 'path';

// Load env from parent directory (backend root)
dotenv.config({ path: path.join(__dirname, '../.env') });

const prisma = new PrismaClient();

async function main() {
    console.log('Testing database connection...');
    console.log('DATABASE_URL:', process.env.DATABASE_URL?.replace(/:([^:@]+)@/, ':****@'));

    try {
        const borrowCount = await prisma.borrower.count();
        console.log(`✅ Success! Connection established. Found ${borrowCount} borrowers.`);
    } catch (e: any) {
        console.error('❌ Connection failed!');
        console.error('Error message:', e.message);

        // Check for common issues
        if (e.message.includes('Can\'t reach database server')) {
            console.log('\nSuggested fix: Ensure the SSH tunnel is running properly.');
        }
    } finally {
        await prisma.$disconnect();
    }
}

main();
