# 🚀 FileManager Pro API Documentation

## 📋 Table of Contents

- [Overview](#overview)
- [Authentication](#authentication)
- [Response Format](#response-format)
- [Error Handling](#error-handling)
- [Endpoints](#endpoints)
  - [Authentication](#authentication-endpoints)
  - [File Operations](#file-operations)
  - [User Management](#user-management)
- [Rate Limiting](#rate-limiting)
- [Examples](#examples)

## Overview

FileManager Pro provides a RESTful API for programmatic access to file management functionality. The API uses standard HTTP methods and returns JSON responses.

**Base URL**: `http://localhost/ManagementSistemFile/`  
**API Version**: 1.2  
**Content-Type**: `application/json`

## Authentication

### Session-Based Authentication

All API endpoints (except public ones) require authentication via PHP sessions.

```php
// Login first to establish session
POST /login.php
{
    "username": "your_username",
    "password": "your_password"
}
```

### CSRF Protection

All state-changing operations require a CSRF token:

```javascript
// Get CSRF token
const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

// Include in requests
headers: {
    'X-CSRF-Token': token
}
```

## Response Format

All API responses follow a consistent JSON structure:

```json
{
    "success": true|false,
    "message": "Human readable message",
    "data": {
        // Response data (varies by endpoint)
    },
    "errors": [
        // Array of error messages (if any)
    ],
    "meta": {
        "timestamp": "2025-01-24T10:30:00Z",
        "version": "1.2",
        "request_id": "uuid"
    }
}
```

### Success Response Example

```json
{
    "success": true,
    "message": "Files retrieved successfully",
    "data": {
        "files": [...],
        "total": 25,
        "page": 1
    },
    "errors": [],
    "meta": {
        "timestamp": "2025-01-24T10:30:00Z",
        "version": "1.2"
    }
}
```

### Error Response Example

```json
{
    "success": false,
    "message": "Validation failed",
    "data": null,
    "errors": [
        "File size exceeds maximum limit",
        "Invalid file type"
    ],
    "meta": {
        "timestamp": "2025-01-24T10:30:00Z",
        "version": "1.2"
    }
}
```

## Error Handling

### HTTP Status Codes

| Code | Meaning | Description |
|------|---------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid request parameters |
| 401 | Unauthorized | Authentication required |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 413 | Payload Too Large | File size exceeded |
| 422 | Unprocessable Entity | Validation errors |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |

### Error Response Structure

```json
{
    "success": false,
    "message": "Error message",
    "data": null,
    "errors": [
        {
            "field": "filename",
            "code": "INVALID_FORMAT",
            "message": "Filename contains invalid characters"
        }
    ]
}
```

## Endpoints

### Authentication Endpoints

#### Login
```http
POST /login.php
Content-Type: application/x-www-form-urlencoded

username=testuser&password=password123
```

**Response:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "username": "testuser",
            "email": "test@example.com",
            "full_name": "Test User"
        }
    }
}
```

#### Register
```http
POST /register.php
Content-Type: application/x-www-form-urlencoded

username=newuser&email=new@example.com&password=newpassword&full_name=New User
```

#### Logout
```http
GET /logout.php
```

### File Operations

#### Get Files List
```http
GET /includes/get_files.php?action=list
```

**Query Parameters:**
- `type` (optional): Filter by file type (`image`, `document`, `video`, `audio`, `archive`)
- `search` (optional): Search term for filename
- `sort` (optional): Sort by (`name`, `size`, `date`, `type`)
- `order` (optional): Sort order (`asc`, `desc`)
- `page` (optional): Page number (default: 1)
- `limit` (optional): Items per page (default: 20)

**Response:**
```json
{
    "success": true,
    "message": "Files retrieved successfully",
    "data": {
        "files": [
            {
                "id": 1,
                "filename": "1753256821_2bd2488442681c12.jpg",
                "original_name": "photo.jpg",
                "file_size": 1048576,
                "file_size_formatted": "1.0 MB",
                "file_type": "image",
                "mime_type": "image/jpeg",
                "upload_date": "2025-01-24 10:30:00",
                "file_path": "uploads/1753256821_2bd2488442681c12.jpg",
                "file_url": "../uploads/1753256821_2bd2488442681c12.jpg"
            }
        ],
        "pagination": {
            "total": 25,
            "page": 1,
            "limit": 20,
            "pages": 2
        }
    }
}
```

#### Get File Statistics
```http
GET /includes/get_files.php?action=stats
```

**Response:**
```json
{
    "success": true,
    "message": "Statistics retrieved successfully",
    "data": {
        "total_files": 25,
        "total_size": 52428800,
        "total_size_formatted": "50.0 MB",
        "file_types": {
            "image": 15,
            "document": 7,
            "video": 2,
            "archive": 1
        },
        "recent_uploads": 5,
        "storage_used_percent": 5.0
    }
}
```

#### Upload File
```http
POST /includes/upload.php
Content-Type: multipart/form-data

file=@/path/to/file.jpg
csrf_token=abc123...
```

**Response:**
```json
{
    "success": true,
    "message": "File uploaded successfully",
    "data": {
        "file": {
            "id": 26,
            "filename": "1753256999_abc123def456.jpg",
            "original_name": "uploaded_photo.jpg",
            "file_size": 2097152,
            "file_size_formatted": "2.0 MB",
            "file_type": "image",
            "mime_type": "image/jpeg",
            "upload_date": "2025-01-24 10:35:00"
        }
    }
}
```

#### Download File
```http
GET /includes/download.php?file_id=1
```

**Response:** Binary file data with appropriate headers

#### Delete File
```http
POST /includes/delete.php
Content-Type: application/x-www-form-urlencoded

file_id=1&csrf_token=abc123...
```

**Response:**
```json
{
    "success": true,
    "message": "File deleted successfully",
    "data": {
        "deleted_file_id": 1
    }
}
```

#### Rename File
```http
POST /includes/rename.php
Content-Type: application/x-www-form-urlencoded

file_id=1&new_name=renamed_file.jpg&csrf_token=abc123...
```

**Response:**
```json
{
    "success": true,
    "message": "File renamed successfully",
    "data": {
        "file": {
            "id": 1,
            "old_name": "old_file.jpg",
            "new_name": "renamed_file.jpg"
        }
    }
}
```

#### Copy File
```http
POST /includes/copy.php
Content-Type: application/x-www-form-urlencoded

file_id=1&csrf_token=abc123...
```

**Response:**
```json
{
    "success": true,
    "message": "File copied successfully",
    "data": {
        "original_file": {
            "id": 1,
            "filename": "original.jpg"
        },
        "copied_file": {
            "id": 27,
            "filename": "copy_of_original.jpg"
        }
    }
}
```

### User Management

#### Get User Profile
```http
GET /pages/profile.php?action=get_profile
```

**Response:**
```json
{
    "success": true,
    "message": "Profile retrieved successfully",
    "data": {
        "user": {
            "id": 1,
            "username": "testuser",
            "email": "test@example.com",
            "full_name": "Test User",
            "created_at": "2025-01-20 10:00:00",
            "settings": {
                "dark_mode": false,
                "language": "id",
                "notifications": true
            }
        }
    }
}
```

#### Update User Profile
```http
POST /pages/profile.php
Content-Type: application/x-www-form-urlencoded

action=update_profile&full_name=Updated Name&email=updated@example.com&csrf_token=abc123...
```

#### Get Storage Usage
```http
GET /includes/get_storage_used.php
```

**Response:**
```json
{
    "success": true,
    "message": "Storage usage retrieved successfully",
    "data": {
        "used_bytes": 52428800,
        "used_formatted": "50.0 MB",
        "total_bytes": 1073741824,
        "total_formatted": "1.0 GB",
        "usage_percent": 4.88,
        "files_count": 25
    }
}
```

## Rate Limiting

API requests are rate-limited to prevent abuse:

- **File uploads**: 10 requests per minute
- **File operations**: 100 requests per minute  
- **Authentication**: 5 requests per minute
- **General API**: 1000 requests per hour

Rate limit headers are included in responses:

```
X-RateLimit-Limit: 100
X-RateLimit-Remaining: 95
X-RateLimit-Reset: 1642774800
```

## Examples

### JavaScript/Fetch API

```javascript
class FileManagerAPI {
    constructor(baseUrl) {
        this.baseUrl = baseUrl;
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}${endpoint}`;
        const config = {
            headers: {
                'X-CSRF-Token': this.csrfToken,
                ...options.headers
            },
            ...options
        };

        const response = await fetch(url, config);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || 'API request failed');
        }

        return data;
    }

    async getFiles(params = {}) {
        const queryString = new URLSearchParams(params).toString();
        return this.request(`includes/get_files.php?action=list&${queryString}`);
    }

    async uploadFile(file) {
        const formData = new FormData();
        formData.append('file', file);
        formData.append('csrf_token', this.csrfToken);

        return this.request('includes/upload.php', {
            method: 'POST',
            body: formData
        });
    }

    async deleteFile(fileId) {
        const formData = new FormData();
        formData.append('file_id', fileId);
        formData.append('csrf_token', this.csrfToken);

        return this.request('includes/delete.php', {
            method: 'POST',
            body: formData
        });
    }
}

// Usage
const api = new FileManagerAPI('/ManagementSistemFile/');

// Get files
api.getFiles({ type: 'image', limit: 10 })
    .then(response => {
        console.log('Files:', response.data.files);
    })
    .catch(error => {
        console.error('Error:', error.message);
    });

// Upload file
document.getElementById('file-input').addEventListener('change', async (event) => {
    const file = event.target.files[0];
    if (file) {
        try {
            const response = await api.uploadFile(file);
            console.log('Upload successful:', response.data.file);
        } catch (error) {
            console.error('Upload failed:', error.message);
        }
    }
});
```

### PHP/cURL Example

```php
<?php
class FileManagerClient {
    private $baseUrl;
    private $sessionCookie;
    
    public function __construct($baseUrl) {
        $this->baseUrl = rtrim($baseUrl, '/') . '/';
    }
    
    public function login($username, $password) {
        $data = [
            'username' => $username,
            'password' => $password
        ];
        
        $response = $this->request('login.php', 'POST', $data);
        
        if ($response['success']) {
            // Store session cookie for subsequent requests
            $this->sessionCookie = $response['cookie'];
        }
        
        return $response;
    }
    
    public function getFiles($params = []) {
        $queryString = http_build_query(array_merge(['action' => 'list'], $params));
        return $this->request("includes/get_files.php?{$queryString}");
    }
    
    public function uploadFile($filePath, $csrfToken) {
        $file = new CURLFile($filePath);
        $data = [
            'file' => $file,
            'csrf_token' => $csrfToken
        ];
        
        return $this->request('includes/upload.php', 'POST', $data, true);
    }
    
    private function request($endpoint, $method = 'GET', $data = [], $isFileUpload = false) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_COOKIEJAR => '/tmp/filemanager_cookies.txt',
            CURLOPT_COOKIEFILE => '/tmp/filemanager_cookies.txt'
        ]);
        
        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data) {
                if ($isFileUpload) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                }
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return json_decode($response, true);
    }
}

// Usage
$client = new FileManagerClient('http://localhost/ManagementSistemFile/');

// Login
$loginResult = $client->login('testuser', 'password123');
if ($loginResult['success']) {
    // Get files
    $files = $client->getFiles(['type' => 'image', 'limit' => 10]);
    print_r($files['data']['files']);
}
?>
```

## Webhooks (Future Feature)

*Note: Webhooks are planned for a future release.*

FileManager Pro will support webhooks for real-time notifications:

```json
{
    "event": "file.uploaded",
    "data": {
        "file": {
            "id": 26,
            "filename": "document.pdf",
            "user_id": 1
        }
    },
    "timestamp": "2025-01-24T10:30:00Z"
}
```

## SDK Development

We welcome community-developed SDKs for various programming languages:

- JavaScript/TypeScript (Official - Coming Soon)
- Python
- PHP
- Java
- Go
- Ruby

---

For questions about the API or to request new features, please open an issue on [GitHub](https://github.com/whympxx/SystemManagementFile/issues).
