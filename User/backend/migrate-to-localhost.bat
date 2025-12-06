@echo off
echo ========================================
echo MasterFunds Prisma Migration to Localhost
echo ========================================
echo.

REM Check if .env exists
if not exist .env (
    echo [ERROR] .env file not found!
    echo Please create .env file first with your DATABASE_URL
    echo Example: DATABASE_URL="mysql://root:password@localhost:3306/loan_db"
    pause
    exit /b 1
)

echo [1/4] Checking Node.js...
node --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Node.js is not installed or not in PATH
    pause
    exit /b 1
)
echo [OK] Node.js found

echo.
echo [2/4] Installing dependencies (if needed)...
call npm install >nul 2>&1

echo.
echo [3/4] Generating Prisma Client...
call npm run prisma:generate
if errorlevel 1 (
    echo [ERROR] Failed to generate Prisma Client
    pause
    exit /b 1
)

echo.
echo [4/4] Creating database schema...
echo IMPORTANT: Make sure MySQL is running and loan_db database exists!
echo.
echo Options:
echo  A. If database is EMPTY, run: npx prisma migrate dev --name initial_schema
echo  B. If database has tables, run: npx prisma db push
echo.
set /p choice="Choose option (A or B): "

if /i "%choice%"=="A" (
    echo.
    echo Running: npx prisma migrate dev --name initial_schema
    npx prisma migrate dev --name initial_schema
) else if /i "%choice%"=="B" (
    echo.
    echo Running: npx prisma db push
    npx prisma db push
) else (
    echo Invalid choice. Exiting.
    pause
    exit /b 1
)

if errorlevel 1 (
    echo.
    echo [ERROR] Migration failed!
    echo.
    echo Troubleshooting:
    echo 1. Check MySQL is running
    echo 2. Verify DATABASE_URL in .env file
    echo 3. Make sure loan_db database exists (run CREATE_DATABASE.sql in MySQL Workbench)
    pause
    exit /b 1
)

echo.
echo ========================================
echo [SUCCESS] Migration completed!
echo ========================================
echo.
echo Next steps:
echo  1. Open MySQL Workbench
echo  2. Connect to localhost
echo  3. Check loan_db database - all tables should be there!
echo.
echo You can also run: npm run prisma:studio
echo to view your data in a GUI.
echo.
pause

