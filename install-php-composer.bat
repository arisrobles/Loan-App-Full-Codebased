@echo off
echo ========================================
echo PHP and Composer Auto-Installer
echo ========================================
echo.

REM Check for admin rights
net session >nul 2>&1
if %errorLevel% == 0 (
    echo Running with administrator privileges...
) else (
    echo WARNING: Not running as administrator.
    echo Some steps may require admin rights.
    echo.
)

echo.
echo [1/4] Installing PHP...
echo.

set "PHP_DIR=C:\php"

if exist "%PHP_DIR%\php.exe" (
    echo PHP already installed at %PHP_DIR%
) else (
    echo Downloading PHP 8.2...
    echo Please visit: https://windows.php.net/download/ to get the latest PHP
    echo Or we'll try to download automatically...
    powershell -Command "& {[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; try { $url = 'https://windows.php.net/downloads/releases/php-8.3.12-Win32-vs16-x64.zip'; Invoke-WebRequest -Uri $url -OutFile '%TEMP%\php.zip' -UseBasicParsing -ErrorAction Stop; Write-Host 'Download successful' } catch { Write-Host 'Auto-download failed. Please download PHP manually from https://windows.php.net/download/' } }"
    
    if exist "%TEMP%\php.zip" (
        echo Extracting PHP...
        if not exist "%PHP_DIR%" mkdir "%PHP_DIR%"
        powershell -Command "Expand-Archive -Path '%TEMP%\php.zip' -DestinationPath '%PHP_DIR%' -Force"
        
        echo Configuring PHP...
        if exist "%PHP_DIR%\php.ini-development" (
            copy "%PHP_DIR%\php.ini-development" "%PHP_DIR%\php.ini" >nul
            echo PHP configured successfully
        )
        
        del "%TEMP%\php.zip" >nul 2>&1
        echo PHP installed at %PHP_DIR%
    ) else (
        echo ERROR: Failed to download PHP
        echo Please download manually from: https://windows.php.net/download/
    )
)

echo.
echo [2/4] Adding PHP to PATH...
echo.

REM Add PHP to user PATH
for /f "tokens=2*" %%A in ('reg query "HKCU\Environment" /v PATH 2^>nul') do set "USER_PATH=%%B"
echo %USER_PATH% | findstr /C:"%PHP_DIR%" >nul
if %errorLevel% neq 0 (
    if exist "%PHP_DIR%\php.exe" (
        setx PATH "%USER_PATH%;%PHP_DIR%" >nul
        echo PHP added to PATH
    )
) else (
    echo PHP already in PATH
)

echo.
echo [3/4] Installing Composer...
echo.

set "COMPOSER_INSTALLER=%TEMP%\Composer-Setup.exe"

if exist "%LOCALAPPDATA%\Programs\Composer\composer.bat" (
    echo Composer already installed
) else (
    echo Downloading Composer installer...
    powershell -Command "& {[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; Invoke-WebRequest -Uri 'https://getcomposer.org/Composer-Setup.exe' -OutFile '%COMPOSER_INSTALLER%' -UseBasicParsing}"
    
    if exist "%COMPOSER_INSTALLER%" (
        echo.
        echo IMPORTANT: A Composer installer window will open.
        echo Please follow these steps:
        echo   1. Click Next through the installation
        echo   2. Make sure "Add to PATH" is CHECKED
        echo   3. Complete the installation
        echo.
        pause
        start "" "%COMPOSER_INSTALLER%"
        echo.
        echo Waiting for Composer installation to complete...
        timeout /t 10 /nobreak >nul
    ) else (
        echo ERROR: Failed to download Composer
        echo Please download manually from: https://getcomposer.org/download/
    )
)

echo.
echo [4/4] Verifying installation...
echo.

REM Refresh PATH
call refreshenv >nul 2>&1

if exist "%PHP_DIR%\php.exe" (
    "%PHP_DIR%\php.exe" --version >nul 2>&1
    if %errorLevel% == 0 (
        echo [OK] PHP is working
        "%PHP_DIR%\php.exe" --version | findstr /C:"PHP"
    ) else (
        echo [ERROR] PHP not working properly
    )
) else (
    echo [ERROR] PHP not found
)

echo.
if exist "%LOCALAPPDATA%\Programs\Composer\composer.bat" (
    "%LOCALAPPDATA%\Programs\Composer\composer.bat" --version >nul 2>&1
    if %errorLevel% == 0 (
        echo [OK] Composer is working
        "%LOCALAPPDATA%\Programs\Composer\composer.bat" --version
    ) else (
        echo [ERROR] Composer not working properly
    )
) else (
    composer --version >nul 2>&1
    if %errorLevel% == 0 (
        echo [OK] Composer is working
        composer --version
    ) else (
        echo [WARNING] Composer may need to be added to PATH manually
    )
)

echo.
echo ========================================
echo Installation Complete!
echo ========================================
echo.
echo IMPORTANT: Close and reopen your terminal/PowerShell
echo for PATH changes to take effect.
echo.
echo Then you can run:
echo   cd Admin\Loan-Admin
echo   composer install
echo.
pause

