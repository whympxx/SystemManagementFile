@echo off
echo ========================================
echo    FILEMANAGER PRO - DOCUMENTATION
echo      Push to GitHub Repository
echo ========================================
echo.

REM Check if git is available
git --version >nul 2>&1
if errorlevel 1 (
    echo [ERROR] Git is not installed or not in PATH
    echo Please install Git first: https://git-scm.com/
    pause
    exit /b 1
)

echo [INFO] Checking repository status...
git status

echo.
echo [INFO] Adding all documentation files...
git add README.md
git add API.md
git add CONTRIBUTING.md
git add SECURITY.md
git add INSTALLATION.md
git add USER_GUIDE.md
git add DEPLOYMENT.md
git add CHANGELOG.md
git add FEATURE_STATUS.md
git add LICENSE
git add .htaccess

echo.
echo [INFO] Adding any other new files...
git add .

echo.
echo [INFO] Creating commit with documentation update...
git commit -m "docs: comprehensive documentation update

- ✨ Modern and professional README.md
- 📚 Complete user guide (USER_GUIDE.md)
- 🛠️ Detailed installation guide (INSTALLATION.md)
- 🚀 Production deployment guide (DEPLOYMENT.md)
- 🔒 Enhanced security documentation (SECURITY.md)
- 🤝 Contributing guidelines (CONTRIBUTING.md)
- 📖 Comprehensive API documentation (API.md)
- 📋 Feature status and changelog
- 🔧 Configuration files and examples

All documentation is now modern, professional, and up-to-date for 2025."

if errorlevel 1 (
    echo [WARNING] No changes to commit or commit failed
    echo This might be because there are no changes to commit.
)

echo.
echo [INFO] Pushing to GitHub repository...
git push origin main

if errorlevel 1 (
    echo [ERROR] Failed to push to GitHub
    echo Please check your credentials and network connection
    echo You might need to set up your GitHub credentials:
    echo   git config --global user.name "Your Name"
    echo   git config --global user.email "your.email@example.com"
    pause
    exit /b 1
)

echo.
echo ========================================
echo    DOCUMENTATION PUSH COMPLETED!
echo ========================================
echo.
echo Your FileManager Pro documentation has been successfully
echo pushed to GitHub repository:
echo https://github.com/whympxx/SystemManagementFile
echo.
echo Documentation includes:
echo - 📖 Complete README with modern design
echo - 🚀 Installation and deployment guides  
echo - 👤 User guide for end users
echo - 🔒 Security documentation
echo - 🤝 Contributing guidelines
echo - 📡 API documentation
echo - 🛠️ Configuration examples
echo.
echo The repository is now ready for production use!
echo.
pause
