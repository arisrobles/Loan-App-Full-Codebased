# Loan-App-Full-Codebased
# Loan-App
A comprehensive loan management system with Admin Panel (Laravel), Mobile Backend API (Node.js/TypeScript), and Mobile App (React Native/Expo).

## 🚀 Prerequisites

Before starting, ensure you have the following installed:

### Required Software:
1. **Node.js** (v18 or higher) - [Download](https://nodejs.org/)
2. **PHP** (v8.2 or higher) - [Download](https://windows.php.net/download/)
3. **Composer** - PHP dependency manager - [Download](https://getcomposer.org/download/)
4. **MySQL** - Database server
5. **Expo CLI** - `npm install -g expo-cli` (optional, included in project)

### Installing PHP & Composer on Windows:

#### Option 1: Using XAMPP (Recommended for beginners)
1. Download [XAMPP](https://www.apachefriends.org/download.html)
2. Install XAMPP (includes PHP and MySQL)
3. Add PHP to PATH:
   - Find PHP in `C:\xampp\php`
   - Add to System Environment Variables → Path
4. Install Composer:
   - Download [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe)
   - Run installer (it will auto-detect PHP)

#### Option 2: Manual Installation
1. **Install PHP:**
   - Download PHP from [windows.php.net](https://windows.php.net/download/)
   - Extract to `C:\php`
   - Add `C:\php` to System PATH
   - Copy `php.ini-development` to `php.ini`
   - Enable extensions: `extension=mysqli`, `extension=pdo_mysql`, `extension=openssl`

2. **Install Composer:**
   - Download [Composer-Setup.exe](https://getcomposer.org/Composer-Setup.exe)
   - Run installer

#### Verify Installation:
```powershell
php --version    # Should show PHP 8.2+
composer --version  # Should show Composer version
```

## 🔗 Quick Connection Setup

**All three components are already designed to work together!** They share the same MySQL database.

### Quick Start:
1. **Configure Database** - See `CONNECTION_SETUP.md` for detailed instructions
2. **Run Setup Script**:
   - Windows: `setup-connection.bat`
   - Linux/Mac: `./setup-connection.sh`
3. **Verify Connection**: `node verify-connection.js`
4. **Start Services**:
   - Admin Panel: `cd Admin/Loan-Admin && composer install && php artisan serve`
   - Mobile Backend: `cd User/backend && npm install && npm run dev`
   - Mobile App: `cd User/frontend && npm install && npm start`

📖 **Full connection guide**: See [CONNECTION_SETUP.md](./CONNECTION_SETUP.md)

## 📁 Project Structure

```
LoanApp/
├── Admin/Loan-Admin/          # Laravel Admin Panel (Port 8000)
├── User/
│   ├── backend/               # Node.js/TypeScript API (Port 8080)
│   └── frontend/              # React Native/Expo Mobile App
├── CONNECTION_SETUP.md        # Connection guide
├── verify-connection.js       # Connection verification script
└── SHARED_CONFIG.example      # Database config template
```

## 🛠️ Setup Instructions

### 1. Admin Panel (Laravel)

```powershell
cd Admin/Loan-Admin

# Install PHP dependencies
composer install

# Copy environment file
copy .env.example .env

# Generate application key
php artisan key:generate

# Run migrations (if database is set up)
php artisan migrate

# Start development server
php artisan serve
# Server runs on http://localhost:8000
```

### 2. Mobile Backend API (Node.js/TypeScript)

```powershell
cd User/backend

# Install dependencies
npm install

# Set up Prisma
npm run prisma:generate

# Create .env file with database connection
# DATABASE_URL="mysql://user:password@localhost:3306/loan_db"

# Run migrations (if needed)
npm run prisma:migrate

# Start development server
npm run dev
# Server runs on http://localhost:8080
```

### 3. Mobile App (React Native/Expo)

```powershell
cd User/frontend

# Install dependencies
npm install

# Start Expo development server
npm start
# Or use: npm run android / npm run ios / npm run web
```

## 🎯 How They Connect

- **Database**: Both Admin Panel and Mobile Backend use the same MySQL database (`loan_db`)
- **Data Flow**: 
  - Mobile App → Backend API → Database
  - Admin Panel → Database
  - Changes are instantly visible in both systems (same database)
- **No API Integration Needed**: They work together through shared database

## ⚠️ Troubleshooting

### "Composer/PHP not recognized" Error

**Problem:** PHP/Composer are installed but not in your system PATH.

**Quick Fix (Temporary - for current session):**
```powershell
# Run the fix script
.\fix-php-composer-path.ps1

# Or manually add to PATH (replace paths with your actual installation paths)
$env:PATH += ";C:\xampp\php"
$env:PATH += ";C:\Users\YourName\AppData\Local\Programs\Composer"
```

**Permanent Fix:**
1. Find where PHP and Composer are installed:
   - **PHP**: Usually in `C:\xampp\php` or `C:\Program Files\PHP`
   - **Composer**: Usually in `C:\Users\YourName\AppData\Local\Programs\Composer`

2. Add to System PATH:
   - Press `Win + X` → Select "System"
   - Click "Advanced system settings"
   - Click "Environment Variables"
   - Under "System variables", find "Path" and click "Edit"
   - Click "New" and add your PHP directory (e.g., `C:\xampp\php`)
   - Click "New" again and add your Composer directory
   - Click "OK" on all dialogs
   - **Restart PowerShell/Terminal** (important!)

3. Verify:
   ```powershell
   php --version
   composer --version
   ```

**Alternative:** Use full paths directly:
```powershell
# Instead of: composer install
C:\xampp\php\php.exe C:\Users\YourName\AppData\Local\Programs\Composer\composer.phar install
```

### Database connection issues
- Check MySQL is running
- Verify database credentials in `.env` files
- Ensure database `loan_db` exists

## 📚 Documentation

- [Connection Setup Guide](./CONNECTION_SETUP.md) - How to connect all components
- [Backend README](./User/backend/README.md) - Mobile backend API documentation
- [Payment History Investigation](./PAYMENT_HISTORY_INVESTIGATION.md) - Payment system analysis
