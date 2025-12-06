@echo off
echo Fixing Prisma EPERM Error...
echo.
echo Step 1: Closing Node processes...
taskkill /F /IM node.exe 2>nul
echo Waiting 3 seconds...
timeout /t 3 /nobreak >nul
echo.
echo Step 2: Removing .prisma folder...
if exist "node_modules\.prisma" (
    rmdir /s /q "node_modules\.prisma"
    echo .prisma folder removed
) else (
    echo .prisma folder not found
)
echo.
echo Step 3: Generating Prisma Client...
call npm run prisma:generate
echo.
echo Done!
pause

