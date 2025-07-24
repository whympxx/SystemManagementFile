#!/bin/bash

# Colors for better output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}========================================"
echo -e "   FILEMANAGER PRO - DOCUMENTATION"
echo -e "     Push to GitHub Repository"
echo -e "========================================${NC}"
echo

# Check if git is available
if ! command -v git &> /dev/null; then
    echo -e "${RED}[ERROR] Git is not installed${NC}"
    echo "Please install Git first:"
    echo "  Ubuntu/Debian: sudo apt install git"
    echo "  CentOS/RHEL: sudo yum install git"
    echo "  macOS: brew install git"
    exit 1
fi

echo -e "${BLUE}[INFO] Checking repository status...${NC}"
git status

echo
echo -e "${BLUE}[INFO] Adding all documentation files...${NC}"
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

echo
echo -e "${BLUE}[INFO] Adding any other new files...${NC}"
git add .

echo
echo -e "${BLUE}[INFO] Creating commit with documentation update...${NC}"
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

if [ $? -ne 0 ]; then
    echo -e "${YELLOW}[WARNING] No changes to commit or commit failed${NC}"
    echo "This might be because there are no changes to commit."
fi

echo
echo -e "${BLUE}[INFO] Pushing to GitHub repository...${NC}"
git push origin main

if [ $? -ne 0 ]; then
    echo -e "${RED}[ERROR] Failed to push to GitHub${NC}"
    echo "Please check your credentials and network connection"
    echo "You might need to set up your GitHub credentials:"
    echo "  git config --global user.name \"Your Name\""
    echo "  git config --global user.email \"your.email@example.com\""
    echo
    echo "Or setup SSH key authentication:"
    echo "  ssh-keygen -t ed25519 -C \"your.email@example.com\""
    echo "  cat ~/.ssh/id_ed25519.pub"
    echo "  # Add the output to GitHub SSH keys"
    exit 1
fi

echo
echo -e "${GREEN}========================================"
echo -e "   DOCUMENTATION PUSH COMPLETED!"
echo -e "========================================${NC}"
echo
echo -e "Your FileManager Pro documentation has been successfully"
echo -e "pushed to GitHub repository:"
echo -e "${BLUE}https://github.com/whympxx/SystemManagementFile${NC}"
echo
echo -e "Documentation includes:"
echo -e "- 📖 Complete README with modern design"
echo -e "- 🚀 Installation and deployment guides"
echo -e "- 👤 User guide for end users"
echo -e "- 🔒 Security documentation"
echo -e "- 🤝 Contributing guidelines"
echo -e "- 📡 API documentation"
echo -e "- 🛠️ Configuration examples"
echo
echo -e "${GREEN}The repository is now ready for production use!${NC}"
echo

# Make the script executable
chmod +x push_docs.sh
