@echo off
REM Quick migration script - assumes database already exists
echo Running Prisma migration...
call npm run prisma:generate
if errorlevel 1 (
    echo ERROR: Failed to generate Prisma Client
    pause
    exit /b 1
)

echo.
echo Creating database schema...
npx prisma migrate dev --name initial_schema
if errorlevel 1 (
    echo ERROR: Migration failed
    pause
    exit /b 1
)

echo.
echo SUCCESS! Database migrated.
echo Run: npm run prisma:studio to view your data
pause

