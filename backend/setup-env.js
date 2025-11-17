// Setup script to create .env file
const fs = require('fs');
const path = require('path');

const envPath = path.join(__dirname, '.env');
const envExamplePath = path.join(__dirname, '.env.example');

// Default .env content
const envContent = `# Database Connection
# Format: mysql://USERNAME:PASSWORD@HOST:PORT/DATABASE_NAME
# For localhost MySQL Workbench:
DATABASE_URL="mysql://root:@localhost:3306/loan_db"

# JWT Secret (change this in production)
JWT_SECRET=your-super-secret-jwt-key-change-in-production-make-it-long-and-random

# API Configuration
PORT=8080
API_VERSION=v1

# Frontend URL (for CORS)
FRONTEND_URL=*

# Environment
NODE_ENV=development
`;

// Check if .env already exists
if (fs.existsSync(envPath)) {
  console.log('⚠️  .env file already exists!');
  console.log('📝 Please manually update DATABASE_URL in .env with your MySQL credentials:');
  console.log('   DATABASE_URL="mysql://USERNAME:PASSWORD@localhost:3306/loan_db"');
  console.log('\n💡 Example: DATABASE_URL="mysql://root:mypassword@localhost:3306/loan_db"');
} else {
  // Create .env file
  fs.writeFileSync(envPath, envContent);
  console.log('✅ .env file created successfully!');
  console.log('\n📝 IMPORTANT: Please edit .env and update DATABASE_URL with your MySQL credentials:');
  console.log('   DATABASE_URL="mysql://USERNAME:PASSWORD@localhost:3306/loan_db"');
  console.log('\n💡 Examples:');
  console.log('   - No password: DATABASE_URL="mysql://root:@localhost:3306/loan_db"');
  console.log('   - With password: DATABASE_URL="mysql://root:mypassword@localhost:3306/loan_db"');
}

// Also create .env.example
if (!fs.existsSync(envExamplePath)) {
  fs.writeFileSync(envExamplePath, envContent);
  console.log('\n✅ .env.example file created!');
}

console.log('\n📚 Next steps:');
console.log('   1. Edit .env with your MySQL credentials');
console.log('   2. Create database: mysql -u root -p < CREATE_DATABASE.sql');
console.log('   3. Run: npm run prisma:generate');
console.log('   4. Run: npx prisma migrate dev --name initial_schema');

