# Contributing to FileManager Pro

First off, thank you for considering contributing to FileManager Pro! 🎉

It's people like you that make FileManager Pro such a great tool. This document provides guidelines and information for contributing to this project.

## 📋 Table of Contents

- [Code of Conduct](#code-of-conduct)
- [Getting Started](#getting-started)
- [How Can I Contribute?](#how-can-i-contribute)
- [Development Setup](#development-setup)
- [Coding Standards](#coding-standards)
- [Commit Guidelines](#commit-guidelines)
- [Pull Request Process](#pull-request-process)
- [Bug Reports](#bug-reports)
- [Feature Requests](#feature-requests)

## Code of Conduct

This project and everyone participating in it is governed by our Code of Conduct. By participating, you are expected to uphold this code. Please report unacceptable behavior to the project maintainers.

## Getting Started

### Prerequisites

Before you begin contributing, make sure you have:

- PHP 8.0+ installed
- MySQL/MariaDB database server
- Web server (Apache/Nginx)
- Git for version control
- Text editor or IDE (VS Code recommended)

### Setting up the Development Environment

1. **Fork the repository**
   ```bash
   # Fork the repo on GitHub, then clone your fork
   git clone https://github.com/your-username/SystemManagementFile.git
   cd SystemManagementFile
   ```

2. **Set up the database**
   ```bash
   # Import the database schema
   mysql -u root -p < config/filemanager_pro.sql
   ```

3. **Configure the application**
   ```bash
   # Copy the example config file
   cp config/database.php.example config/database.php
   # Edit the database credentials
   ```

4. **Create a new branch for your feature**
   ```bash
   git checkout -b feature/your-feature-name
   ```

## How Can I Contribute?

### 🐛 Reporting Bugs

Before creating bug reports, please check the existing issues to avoid duplicates. When creating a bug report, include:

- **Clear title** describing the issue
- **Detailed description** of the problem
- **Steps to reproduce** the bug
- **Expected vs actual behavior**
- **Screenshots** if applicable
- **Environment details** (OS, PHP version, browser, etc.)

### 💡 Suggesting Features

Feature suggestions are welcome! Please provide:

- **Clear title** for the feature
- **Detailed description** of the functionality
- **Use cases** explaining why this feature would be useful
- **Mockups or examples** if applicable

### 🔧 Code Contributions

We welcome code contributions! Here are areas where you can help:

- **Bug fixes**
- **New features**
- **Performance improvements**
- **Security enhancements**
- **Documentation improvements**
- **Test coverage**

## Development Setup

### Local Development

1. **Database Setup**
   ```sql
   CREATE DATABASE filemanager_pro_dev;
   -- Run the initialization script
   ```

2. **Enable Debug Mode**
   ```php
   // In config/database.php
   define('DEBUG_MODE', true);
   define('LOG_ERRORS', true);
   ```

3. **Test Your Changes**
   ```bash
   # Run PHP syntax check
   find . -name "*.php" -exec php -l {} \;
   
   # Test file uploads
   # Test database operations
   # Test security features
   ```

## Coding Standards

### PHP Standards

Follow **PSR-12** coding standards:

```php
<?php

namespace App\Services;

use App\Models\File;
use App\Exceptions\ValidationException;

class FileService
{
    private $uploadPath;
    
    public function __construct(string $uploadPath)
    {
        $this->uploadPath = $uploadPath;
    }
    
    public function uploadFile(array $fileData): File
    {
        if (!$this->validateFile($fileData)) {
            throw new ValidationException('Invalid file data');
        }
        
        // Process upload
        return $this->processUpload($fileData);
    }
}
```

### JavaScript Standards

Use **ES6+** features with clear, readable code:

```javascript
// Use const/let instead of var
const fileManager = {
    init() {
        this.bindEvents();
        this.loadFiles();
    },
    
    async uploadFile(file) {
        try {
            const response = await fetch('/api/upload', {
                method: 'POST',
                body: this.createFormData(file)
            });
            
            if (!response.ok) {
                throw new Error('Upload failed');
            }
            
            return await response.json();
        } catch (error) {
            this.handleError(error);
        }
    }
};
```

### CSS Standards

Use **BEM methodology** for CSS classes:

```css
/* Block */
.file-manager {}

/* Element */
.file-manager__item {}
.file-manager__button {}

/* Modifier */
.file-manager__item--selected {}
.file-manager__button--primary {}
```

### Database Standards

- Use descriptive table and column names
- Always use prepared statements
- Include proper indexes
- Use appropriate data types

```sql
-- Good
CREATE TABLE file_shares (
    id INT AUTO_INCREMENT PRIMARY KEY,
    file_id INT NOT NULL,
    share_token VARCHAR(64) UNIQUE NOT NULL,
    expires_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (file_id) REFERENCES files(id) ON DELETE CASCADE,
    INDEX idx_share_token (share_token),
    INDEX idx_file_id (file_id)
);
```

## Commit Guidelines

### Commit Message Format

Use [Conventional Commits](https://www.conventionalcommits.org/) format:

```
<type>[optional scope]: <description>

[optional body]

[optional footer(s)]
```

### Types

- **feat**: A new feature
- **fix**: A bug fix
- **docs**: Documentation only changes
- **style**: Changes that don't affect code meaning (formatting, etc.)
- **refactor**: Code change that neither fixes a bug nor adds a feature
- **perf**: Performance improvements
- **test**: Adding missing tests
- **chore**: Changes to build process or auxiliary tools

### Examples

```bash
# Good commit messages
git commit -m "feat: add file sharing functionality"
git commit -m "fix: resolve file upload timeout issue"
git commit -m "docs: update installation instructions"
git commit -m "style: format code according to PSR-12"
git commit -m "refactor: extract file validation logic"
```

## Pull Request Process

### Before Submitting

1. **Test thoroughly** - Ensure your changes work as expected
2. **Check for conflicts** - Rebase your branch if needed
3. **Update documentation** - Include relevant documentation updates
4. **Add tests** - Write tests for new functionality
5. **Follow coding standards** - Ensure code follows project standards

### Pull Request Template

```markdown
## Description
Brief description of changes made.

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
- [ ] Unit tests pass
- [ ] Integration tests pass
- [ ] Manual testing completed

## Screenshots
If applicable, add screenshots to help explain your changes.

## Checklist
- [ ] Code follows project style guidelines
- [ ] Self-review completed
- [ ] Code is commented where necessary
- [ ] Documentation updated
- [ ] No breaking changes (or marked as such)
```

### Review Process

1. **Automated checks** must pass
2. **At least one maintainer** must review
3. **All feedback** must be addressed
4. **Final approval** before merging

## Security Considerations

When contributing security-related changes:

- **Never commit secrets** or credentials
- **Validate all inputs** thoroughly
- **Use prepared statements** for database queries
- **Implement proper authentication** checks
- **Follow OWASP guidelines**
- **Report security issues privately** first

## Testing

### Manual Testing

Before submitting, test these scenarios:

1. **File Upload**
   - Various file types
   - Large files
   - Invalid files
   - Multiple files

2. **File Management**
   - Rename operations
   - Delete operations
   - Download functionality
   - Search and filtering

3. **Security**
   - Authentication flows
   - Authorization checks
   - Input validation
   - CSRF protection

### Automated Testing

We encourage adding automated tests:

```php
<?php
// Example test structure
class FileUploadTest extends TestCase
{
    public function testValidFileUpload()
    {
        // Test implementation
    }
    
    public function testInvalidFileRejection()
    {
        // Test implementation
    }
}
```

## Questions or Need Help?

Don't hesitate to ask for help:

- 📋 Open an [issue](https://github.com/whympxx/SystemManagementFile/issues) for bugs
- 💬 Start a [discussion](https://github.com/whympxx/SystemManagementFile/discussions) for questions
- 📧 Contact maintainers directly for security issues

## Recognition

Contributors will be recognized in:

- Project README
- Release notes
- Contributor documentation

Thank you for contributing to FileManager Pro! 🚀
