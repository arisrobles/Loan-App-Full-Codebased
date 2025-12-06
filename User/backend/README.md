# MasterFunds Backend API

Backend API for MasterFunds Loan Management Application built with Node.js, Express, TypeScript, and MySQL.

## 🚀 Features

- ✅ RESTful API architecture
- ✅ JWT-based authentication  
- ✅ User registration and login (Borrowers)
- ✅ Loan management (create, view, track status)
- ✅ Payment processing via repayments
- ✅ Document upload handling
- ✅ Credit score calculation
- ✅ Integrated with existing MySQL database structure

## 📋 Prerequisites

- Node.js (v18 or higher)
- MySQL/MariaDB (v10.4 or higher)
- npm or yarn

## 🛠️ Installation

1. **Navigate to backend directory:**
   ```bash
   cd backend
   ```

2. **Install dependencies:**
   ```bash
   npm install
   ```

3. **Set up environment variables:**
   ```bash
   cp .env.example .env
   ```

4. **Edit `.env` file with your MySQL configuration:**
   ```env
   DATABASE_URL="mysql://root:yourpassword@localhost:3306/loan_db"
   JWT_SECRET=your-super-secret-jwt-key-change-in-production
   PORT=8080
   ```

5. **Import your existing database (if not already done):**
   ```bash
   mysql -u root -p loan_db < /path/to/loan_db-4.sql
   ```

6. **Database Migration (REQUIRED):**
   
   **Option A - Via Terminal (Quick):**
   ```bash
   mysql -u root -p loan_db < MIGRATE_TO_MYSQL.sql
   ```
   
   **Option B - Via MySQL Workbench:**
   1. Open MySQL Workbench
   2. Open `MIGRATE_TO_MYSQL.sql`
   3. Execute the script (or copy-paste into Query tab)
   
   **Option C - Direct SQL:**
   ```sql
   USE loan_db;
   ALTER TABLE `borrowers` ADD COLUMN `password` VARCHAR(255) NULL AFTER `email`;
   ```
   
   **Verify:** Run `DESCRIBE borrowers;` and confirm `password` column exists.

7. **Generate Prisma Client:**
   ```bash
   npm run prisma:generate
   ```

   **Note:** Do NOT run `prisma migrate` - your database already exists! Use `MIGRATE_TO_MYSQL.sql` instead.

## 🏃 Running the Server

### Development mode:
```bash
npm run dev
```

### Production mode:
```bash
npm run build
npm start
```

The server will start on `http://localhost:8080`

## 📡 API Endpoints

### Authentication
- `POST /api/v1/auth/login` - User login (email-based)
- `POST /api/v1/auth/register` - User registration (creates borrower)
- `POST /api/v1/auth/logout` - User logout

### Loans
- `POST /api/v1/loans` - Create loan application (Auth required)
- `GET /api/v1/loans` - Get borrower's loans (Auth required)
- `GET /api/v1/loans/:id` - Get loan details (Auth required)
- `GET /api/v1/loans/:id/status` - Get loan status (Auth required)

### Payments
- `POST /api/v1/payments` - Record payment (Auth required)
- `GET /api/v1/payments` - Get payment history (Auth required)

### Documents
- `POST /api/v1/documents/upload` - Upload document (Auth required)
- `GET /api/v1/documents` - Get user documents (Auth required)

### Credit Score
- `GET /api/v1/credit/score` - Get credit score (Auth required)
- `GET /api/v1/credit/history` - Get credit history (Auth required)

### User Profile
- `GET /api/v1/users/profile` - Get borrower profile (Auth required)
- `PUT /api/v1/users/profile` - Update profile (Auth required)

## 🔐 Authentication

All protected routes require a JWT token in the Authorization header:

```
Authorization: Bearer <your-token>
```

The token is received after successful login or registration.

## ⚠️ Required: Add Password Column to Borrowers Table

**You must add a password field for authentication to work properly:**

```sql
ALTER TABLE `borrowers` 
ADD COLUMN `password` VARCHAR(255) NULL AFTER `email`;
```

Without this, authentication will use email-only matching (not secure for production).

## 📝 Example Requests

### Register User (Creates Borrower)
```bash
curl -X POST http://localhost:8080/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "email": "carl@example.com",
    "password": "password123",
    "fullName": "Carl Kelvin",
    "phone": "+639123456789",
    "reference": "REF123"
  }'
```

### Login
```bash
curl -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "carl@example.com",
    "password": "password123"
  }'
```

### Create Loan (with auth token)
```bash
curl -X POST http://localhost:8080/api/v1/loans \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <your-token>" \
  -d '{
    "amount": 15000,
    "tenor": "12"
  }'
```

**Note:** Frontend can send either `amount` or `principalAmount` - both are accepted.

## 🗄️ Database Structure

The backend uses your existing MySQL database structure:

- **borrowers** - Mobile app users (customers) - requires `password` column
- **loans** - Loan applications and records (linked via `borrower_id`)
- **repayments** - Payment schedule and history
- **bank_transactions** - Financial transactions
- **users** - Admin users (separate from borrowers, for admin panel)

### Key Field Mappings:
- Loan amounts: `principal_amount` in database
- Interest rates: Stored as decimal (0.24 = 24%)
- Loan references: Auto-generated as `MF-YYYY-XXXX` format
- Status values: Match existing enum values exactly

## 📁 Project Structure

```
backend/
├── src/
│   ├── controllers/      # Route handlers
│   ├── middleware/       # Auth, error handling
│   ├── routes/          # API routes
│   ├── utils/           # Helper functions
│   └── server.ts        # Express app setup
├── prisma/
│   └── schema.prisma    # Database schema (matches MySQL)
├── uploads/             # File storage
├── .env                 # Environment variables
└── package.json
```

## 🔧 Development

### Prisma Client
```bash
# Generate Prisma Client (after schema changes)
npm run prisma:generate

# Open Prisma Studio to view/edit data
npm run prisma:studio
```

## 🐛 Troubleshooting

### Database connection error
- Check MySQL is running
- Verify DATABASE_URL in `.env`
- Ensure database `loan_db` exists
- Verify tables exist (import SQL dump if needed)

### Prisma errors (EPERM on Windows)
If you get "operation not permitted" errors:
```bash
# Close all Node processes, then:
rmdir /s node_modules\.prisma
npm run prisma:generate
```
Or use `fix-prisma.bat` script.

### BigInt conversion errors
- IDs are BigInt in MySQL, converted to strings in API responses
- This is handled automatically in controllers

### Frontend Connection
- Android Emulator: Use `http://10.0.2.2:8080`
- iOS Simulator: Use `http://localhost:8080`
- Physical Device: Use your computer's local IP (e.g., `http://192.168.1.100:8080`)

### Migration Failed
- Make sure MySQL is running: `mysql -u root -p -e "SELECT 1;"`
- Verify database exists: `mysql -u root -p -e "SHOW DATABASES LIKE 'loan_db';"`
- Check file path is correct in terminal

## 📄 License

ISC
