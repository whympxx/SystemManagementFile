# 📊 FileManager Pro - Architecture Diagrams

<div align="center">
  <img src="https://img.shields.io/badge/Architecture-Documented-28a745?style=for-the-badge" alt="Architecture Documented">
  <img src="https://img.shields.io/badge/Diagrams-Professional-blue?style=for-the-badge" alt="Professional Diagrams">
  <img src="https://img.shields.io/badge/Mermaid-Interactive-purple?style=for-the-badge" alt="Mermaid Interactive">
</div>

## 📋 Table of Contents

- [System Architecture](#system-architecture)
- [Database Schema](#database-schema)
- [Application Flow](#application-flow)
- [Security Architecture](#security-architecture)
- [Deployment Architecture](#deployment-architecture)
- [API Architecture](#api-architecture)
- [File Processing Flow](#file-processing-flow)
- [User Journey](#user-journey)

## 🏗️ System Architecture

### Overall System Architecture
```mermaid
graph TB
    subgraph "Client Layer"
        WEB[Web Browser]
        MOB[Mobile Browser]
        API_CLIENT[API Client]
    end
    
    subgraph "Load Balancer"
        LB[Nginx Load Balancer]
    end
    
    subgraph "Application Layer"
        APP1[FileManager Instance 1]
        APP2[FileManager Instance 2]
        APP3[FileManager Instance 3]
    end
    
    subgraph "Session & Cache"
        REDIS[(Redis Cache)]
        SESSION[Session Storage]
    end
    
    subgraph "Database Layer"
        DB_MASTER[(MySQL Master)]
        DB_SLAVE[(MySQL Slave)]
    end
    
    subgraph "Storage Layer"
        LOCAL_STORAGE[Local File Storage]
        CDN[CDN/Cloud Storage]
        BACKUP[Backup Storage]
    end
    
    subgraph "External Services"
        SMTP[SMTP Server]
        MONITORING[Monitoring Service]
        LOGS[Log Management]
    end
    
    WEB --> LB
    MOB --> LB
    API_CLIENT --> LB
    
    LB --> APP1
    LB --> APP2
    LB --> APP3
    
    APP1 --> REDIS
    APP2 --> REDIS
    APP3 --> REDIS
    
    APP1 --> DB_MASTER
    APP2 --> DB_MASTER
    APP3 --> DB_MASTER
    
    DB_MASTER --> DB_SLAVE
    
    APP1 --> LOCAL_STORAGE
    APP2 --> LOCAL_STORAGE
    APP3 --> LOCAL_STORAGE
    
    LOCAL_STORAGE --> CDN
    LOCAL_STORAGE --> BACKUP
    
    APP1 --> SMTP
    APP1 --> MONITORING
    APP1 --> LOGS
    
    style WEB fill:#e1f5fe
    style MOB fill:#e1f5fe
    style API_CLIENT fill:#e1f5fe
    style LB fill:#fff3e0
    style APP1 fill:#e8f5e8
    style APP2 fill:#e8f5e8
    style APP3 fill:#e8f5e8
    style REDIS fill:#ffebee
    style DB_MASTER fill:#f3e5f5
    style DB_SLAVE fill:#f3e5f5
```

### Component Architecture
```mermaid
graph LR
    subgraph "Frontend Components"
        UI[User Interface]
        UPLOAD[Upload Component]
        FILEGRID[File Grid]
        PREVIEW[File Preview]
        AUTH[Authentication]
    end
    
    subgraph "Backend Components"
        ROUTER[Router/Controller]
        UPLOAD_HANDLER[Upload Handler]
        FILE_MANAGER[File Manager]
        USER_MANAGER[User Manager]
        SECURITY[Security Manager]
    end
    
    subgraph "Core Services"
        FILE_SERVICE[File Service]
        AUTH_SERVICE[Auth Service]
        VALIDATION[Validation Service]
        LOGGING[Logging Service]
    end
    
    subgraph "Data Layer"
        PDO[PDO Database]
        FILE_SYSTEM[File System]
        CACHE[Cache Layer]
    end
    
    UI --> ROUTER
    UPLOAD --> UPLOAD_HANDLER
    FILEGRID --> FILE_MANAGER
    PREVIEW --> FILE_SERVICE
    AUTH --> USER_MANAGER
    
    ROUTER --> FILE_SERVICE
    UPLOAD_HANDLER --> FILE_SERVICE
    FILE_MANAGER --> FILE_SERVICE
    USER_MANAGER --> AUTH_SERVICE
    SECURITY --> VALIDATION
    
    FILE_SERVICE --> PDO
    FILE_SERVICE --> FILE_SYSTEM
    AUTH_SERVICE --> PDO
    AUTH_SERVICE --> CACHE
    VALIDATION --> LOGGING
    
    style UI fill:#e3f2fd
    style UPLOAD fill:#e3f2fd
    style FILEGRID fill:#e3f2fd
    style ROUTER fill:#e8f5e8
    style FILE_SERVICE fill:#fff8e1
    style PDO fill:#f3e5f5
```

## 🗄️ Database Schema

### Entity Relationship Diagram
```mermaid
erDiagram
    USERS {
        int id PK
        varchar username UK
        varchar email UK
        varchar password_hash
        varchar full_name
        varchar profile_picture
        enum status
        timestamp created_at
        timestamp updated_at
        timestamp last_login
    }
    
    FILES {
        int id PK
        varchar filename
        varchar original_name
        varchar file_path
        bigint file_size
        varchar file_type
        varchar mime_type
        varchar file_hash
        int user_id FK
        enum status
        timestamp upload_date
        timestamp updated_at
        int download_count
    }
    
    FOLDERS {
        int id PK
        varchar folder_name
        varchar folder_path
        int parent_id FK
        int user_id FK
        timestamp created_at
        timestamp updated_at
    }
    
    FILE_SHARES {
        int id PK
        int file_id FK
        varchar share_token UK
        varchar password_hash
        timestamp expires_at
        int download_limit
        int download_count
        enum permissions
        timestamp created_at
        int created_by FK
    }
    
    USER_SESSIONS {
        varchar session_id PK
        int user_id FK
        text session_data
        varchar ip_address
        varchar user_agent
        timestamp created_at
        timestamp expires_at
    }
    
    ACTIVITY_LOGS {
        int id PK
        int user_id FK
        varchar action
        varchar resource_type
        int resource_id
        varchar ip_address
        varchar user_agent
        json metadata
        timestamp created_at
    }
    
    FILE_VERSIONS {
        int id PK
        int file_id FK
        varchar version_number
        varchar file_path
        bigint file_size
        varchar file_hash
        timestamp created_at
        int created_by FK
    }
    
    USERS ||--o{ FILES : owns
    USERS ||--o{ FOLDERS : creates
    USERS ||--o{ FILE_SHARES : creates
    USERS ||--o{ USER_SESSIONS : has
    USERS ||--o{ ACTIVITY_LOGS : generates
    FILES ||--o{ FILE_SHARES : shared
    FILES ||--o{ FILE_VERSIONS : has_versions
    FILES }o--|| FOLDERS : stored_in
    FOLDERS ||--o{ FOLDERS : contains
```

### Database Indexes and Performance
```mermaid
graph TD
    subgraph "Primary Tables"
        USERS_TABLE[Users Table]
        FILES_TABLE[Files Table]
        FOLDERS_TABLE[Folders Table]
    end
    
    subgraph "Indexes"
        IDX_USER_EMAIL[idx_user_email]
        IDX_USER_USERNAME[idx_user_username]
        IDX_FILE_USER[idx_file_user_id]
        IDX_FILE_TYPE[idx_file_type]
        IDX_FILE_DATE[idx_file_upload_date]
        IDX_FILE_HASH[idx_file_hash]
        IDX_FOLDER_USER[idx_folder_user_id]
        IDX_FOLDER_PARENT[idx_folder_parent_id]
    end
    
    subgraph "Performance Features"
        PARTITIONING[Date Partitioning]
        CACHING[Query Caching]
        REPLICATION[Master-Slave Replication]
    end
    
    USERS_TABLE --> IDX_USER_EMAIL
    USERS_TABLE --> IDX_USER_USERNAME
    FILES_TABLE --> IDX_FILE_USER
    FILES_TABLE --> IDX_FILE_TYPE
    FILES_TABLE --> IDX_FILE_DATE
    FILES_TABLE --> IDX_FILE_HASH
    FOLDERS_TABLE --> IDX_FOLDER_USER
    FOLDERS_TABLE --> IDX_FOLDER_PARENT
    
    FILES_TABLE --> PARTITIONING
    IDX_FILE_USER --> CACHING
    USERS_TABLE --> REPLICATION
    
    style USERS_TABLE fill:#e3f2fd
    style FILES_TABLE fill:#e8f5e8
    style FOLDERS_TABLE fill:#fff3e0
    style PARTITIONING fill:#ffebee
    style CACHING fill:#f3e5f5
    style REPLICATION fill:#e0f2f1
```

## 🔄 Application Flow

### File Upload Flow
```mermaid
sequenceDiagram
    participant User
    participant Browser
    participant LoadBalancer
    participant App
    participant Validation
    participant Storage
    participant Database
    participant Cache
    
    User->>Browser: Select Files
    Browser->>Browser: Client-side Validation
    Browser->>LoadBalancer: POST /upload
    LoadBalancer->>App: Route Request
    
    App->>App: Check Authentication
    App->>App: Generate CSRF Token
    App->>Validation: Validate File Type/Size
    
    alt Validation Passes
        App->>Storage: Save File
        Storage-->>App: File Path
        App->>Database: Store File Metadata
        Database-->>App: File ID
        App->>Cache: Update User Stats
        App-->>Browser: Success Response
        Browser-->>User: Upload Complete
    else Validation Fails
        App-->>Browser: Error Response
        Browser-->>User: Show Error
    end
```

### User Authentication Flow
```mermaid
sequenceDiagram
    participant User
    participant Browser
    participant App
    participant Database
    participant Cache
    participant Session
    
    User->>Browser: Enter Credentials
    Browser->>App: POST /login
    App->>App: Validate Input
    App->>Database: Check User Credentials
    Database-->>App: User Data
    
    alt Credentials Valid
        App->>Session: Create Session
        App->>Cache: Store Session Data
        App->>Database: Log Login Activity
        App-->>Browser: Set Session Cookie
        Browser-->>User: Redirect to Dashboard
    else Credentials Invalid
        App->>Database: Log Failed Attempt
        App-->>Browser: Error Response
        Browser-->>User: Show Error
    end
```

### File Management Operations
```mermaid
flowchart TD
    START([User Action]) --> AUTH{Authenticated?}
    AUTH -->|No| LOGIN[Redirect to Login]
    AUTH -->|Yes| PERM{Check Permissions}
    PERM -->|Denied| ERROR[Access Denied]
    PERM -->|Allowed| ACTION{Action Type}
    
    ACTION -->|Upload| UPLOAD_FLOW[Upload Process]
    ACTION -->|Download| DOWNLOAD_FLOW[Download Process]
    ACTION -->|Delete| DELETE_FLOW[Delete Process]
    ACTION -->|Rename| RENAME_FLOW[Rename Process]
    ACTION -->|Share| SHARE_FLOW[Share Process]
    
    UPLOAD_FLOW --> VALIDATE[Validate File]
    VALIDATE --> VIRUS_SCAN[Virus Scan]
    VIRUS_SCAN --> STORE[Store File]
    STORE --> DB_UPDATE[Update Database]
    DB_UPDATE --> THUMB[Generate Thumbnail]
    THUMB --> SUCCESS[Success Response]
    
    DOWNLOAD_FLOW --> FILE_CHECK[Check File Exists]
    FILE_CHECK --> SERVE[Serve File]
    SERVE --> LOG[Log Download]
    LOG --> SUCCESS
    
    DELETE_FLOW --> CONFIRM[Confirm Delete]
    CONFIRM --> REMOVE[Remove File]
    REMOVE --> DB_UPDATE
    
    RENAME_FLOW --> VALIDATE_NAME[Validate Name]
    VALIDATE_NAME --> UPDATE_FS[Update File System]
    UPDATE_FS --> DB_UPDATE
    
    SHARE_FLOW --> GEN_TOKEN[Generate Share Token]
    GEN_TOKEN --> DB_UPDATE
    DB_UPDATE --> SUCCESS
    
    style START fill:#e3f2fd
    style SUCCESS fill:#e8f5e8
    style ERROR fill:#ffebee
    style AUTH fill:#fff3e0
    style PERM fill:#f3e5f5
```

## 🔒 Security Architecture

### Security Layers
```mermaid
graph TB
    subgraph "Network Security"
        FIREWALL[Firewall]
        DDOS[DDoS Protection]
        WAF[Web Application Firewall]
    end
    
    subgraph "Application Security"
        HTTPS[HTTPS/TLS]
        CSRF[CSRF Protection]
        XSS[XSS Prevention]
        SQL_INJ[SQL Injection Prevention]
        INPUT_VAL[Input Validation]
    end
    
    subgraph "Authentication & Authorization"
        LOGIN[Login System]
        SESSION[Session Management]
        RBAC[Role-Based Access Control]
        MFA[Multi-Factor Auth]
        PASSWORD[Password Policy]
    end
    
    subgraph "File Security"
        FILE_VAL[File Validation]
        VIRUS_SCAN[Virus Scanning]
        QUARANTINE[File Quarantine]
        ENCRYPTION[File Encryption]
        SECURE_UPLOAD[Secure Upload]
    end
    
    subgraph "Data Security"
        DB_ENCRYPT[Database Encryption]
        BACKUP_ENCRYPT[Backup Encryption]
        KEY_MGMT[Key Management]
        AUDIT_LOG[Audit Logging]
    end
    
    subgraph "Monitoring & Response"
        INTRUSION[Intrusion Detection]
        ANOMALY[Anomaly Detection]
        ALERTS[Security Alerts]
        INCIDENT[Incident Response]
    end
    
    FIREWALL --> HTTPS
    DDOS --> HTTPS
    WAF --> HTTPS
    
    HTTPS --> CSRF
    CSRF --> LOGIN
    XSS --> SESSION
    SQL_INJ --> RBAC
    INPUT_VAL --> FILE_VAL
    
    LOGIN --> FILE_VAL
    SESSION --> VIRUS_SCAN
    RBAC --> QUARANTINE
    MFA --> ENCRYPTION
    PASSWORD --> SECURE_UPLOAD
    
    FILE_VAL --> DB_ENCRYPT
    VIRUS_SCAN --> BACKUP_ENCRYPT
    QUARANTINE --> KEY_MGMT
    ENCRYPTION --> AUDIT_LOG
    
    DB_ENCRYPT --> INTRUSION
    BACKUP_ENCRYPT --> ANOMALY
    KEY_MGMT --> ALERTS
    AUDIT_LOG --> INCIDENT
    
    style FIREWALL fill:#ffcdd2
    style HTTPS fill:#e8eaf6
    style LOGIN fill:#e0f2f1
    style FILE_VAL fill:#fff3e0
    style DB_ENCRYPT fill:#f3e5f5
    style INTRUSION fill:#fce4ec
```

### Authentication Flow
```mermaid
stateDiagram-v2
    [*] --> Anonymous
    Anonymous --> LoginPage : Access Restricted Resource
    LoginPage --> Authenticating : Submit Credentials
    Authenticating --> Authenticated : Valid Credentials
    Authenticating --> LoginPage : Invalid Credentials
    Authenticated --> SessionActive : Create Session
    SessionActive --> SessionExpired : Timeout/Logout
    SessionExpired --> LoginPage : Session Expired
    SessionActive --> Authenticated : Activity
    
    state Authenticating {
        [*] --> ValidateInput
        ValidateInput --> CheckDatabase
        CheckDatabase --> VerifyPassword
        VerifyPassword --> CheckMFA
        CheckMFA --> CreateSession
        CreateSession --> [*]
    }
    
    state SessionActive {
        [*] --> CheckPermissions
        CheckPermissions --> AllowAccess
        CheckPermissions --> DenyAccess
        AllowAccess --> LogActivity
        DenyAccess --> LogSecurityEvent
    }
```

## 🚀 Deployment Architecture

### Production Deployment
```mermaid
graph TB
    subgraph "Internet"
        USERS[Users]
        CDN[Content Delivery Network]
    end
    
    subgraph "DMZ"
        LB[Load Balancer<br/>Nginx/HAProxy]
        WAF[Web Application Firewall]
    end
    
    subgraph "Web Tier"
        WEB1[Web Server 1<br/>Apache/PHP]
        WEB2[Web Server 2<br/>Apache/PHP]
        WEB3[Web Server 3<br/>Apache/PHP]
    end
    
    subgraph "Application Tier"
        APP1[FileManager App 1]
        APP2[FileManager App 2]
        APP3[FileManager App 3]
    end
    
    subgraph "Cache Tier"
        REDIS_MASTER[Redis Master]
        REDIS_SLAVE[Redis Slave]
    end
    
    subgraph "Database Tier"
        DB_MASTER[MySQL Master]
        DB_SLAVE1[MySQL Slave 1]
        DB_SLAVE2[MySQL Slave 2]
    end
    
    subgraph "Storage Tier"
        NFS[NFS/Shared Storage]
        BACKUP[Backup Storage]
        S3[Cloud Storage<br/>AWS S3/GCS]
    end
    
    subgraph "Monitoring"
        MONITOR[Monitoring<br/>Grafana/Prometheus]
        LOGS[Log Aggregation<br/>ELK Stack]
        ALERTS[Alert Manager]
    end
    
    USERS --> CDN
    CDN --> WAF
    USERS --> WAF
    WAF --> LB
    
    LB --> WEB1
    LB --> WEB2
    LB --> WEB3
    
    WEB1 --> APP1
    WEB2 --> APP2
    WEB3 --> APP3
    
    APP1 --> REDIS_MASTER
    APP2 --> REDIS_MASTER
    APP3 --> REDIS_MASTER
    REDIS_MASTER --> REDIS_SLAVE
    
    APP1 --> DB_MASTER
    APP2 --> DB_MASTER
    APP3 --> DB_MASTER
    DB_MASTER --> DB_SLAVE1
    DB_MASTER --> DB_SLAVE2
    
    APP1 --> NFS
    APP2 --> NFS
    APP3 --> NFS
    NFS --> BACKUP
    NFS --> S3
    
    WEB1 --> MONITOR
    WEB2 --> MONITOR
    WEB3 --> MONITOR
    APP1 --> LOGS
    DB_MASTER --> LOGS
    MONITOR --> ALERTS
    
    style USERS fill:#e3f2fd
    style CDN fill:#e0f2f1
    style LB fill:#fff3e0
    style WEB1 fill:#e8f5e8
    style WEB2 fill:#e8f5e8
    style WEB3 fill:#e8f5e8
    style DB_MASTER fill:#f3e5f5
    style NFS fill:#fce4ec
```

### Docker Deployment Architecture
```mermaid
graph TB
    subgraph "Docker Host"
        subgraph "Frontend Network"
            NGINX[Nginx Container<br/>Load Balancer]
        end
        
        subgraph "Application Network"
            APP1[FileManager Container 1]
            APP2[FileManager Container 2]
            APP3[FileManager Container 3]
        end
        
        subgraph "Database Network"
            MYSQL[MySQL Container]
            REDIS[Redis Container]
            PHPMYADMIN[phpMyAdmin Container]
        end
        
        subgraph "Storage Volumes"
            UPLOADS[uploads_data]
            MYSQL_DATA[mysql_data]
            LOGS[logs_data]
            REDIS_DATA[redis_data]
        end
        
        subgraph "Configuration"
            SECRETS[Docker Secrets]
            CONFIGS[Docker Configs]
        end
    end
    
    NGINX --> APP1
    NGINX --> APP2
    NGINX --> APP3
    
    APP1 --> MYSQL
    APP2 --> MYSQL
    APP3 --> MYSQL
    
    APP1 --> REDIS
    APP2 --> REDIS
    APP3 --> REDIS
    
    PHPMYADMIN --> MYSQL
    
    APP1 --> UPLOADS
    APP2 --> UPLOADS
    APP3 --> UPLOADS
    
    MYSQL --> MYSQL_DATA
    REDIS --> REDIS_DATA
    APP1 --> LOGS
    
    APP1 --> SECRETS
    APP1 --> CONFIGS
    MYSQL --> SECRETS
    
    style NGINX fill:#e0f2f1
    style APP1 fill:#e8f5e8
    style APP2 fill:#e8f5e8
    style APP3 fill:#e8f5e8
    style MYSQL fill:#f3e5f5
    style REDIS fill:#ffebee
    style UPLOADS fill:#fff3e0
```

## 🔌 API Architecture

### REST API Structure
```mermaid
graph LR
    subgraph "API Endpoints"
        AUTH_API[Authentication API<br/>/api/auth]
        FILES_API[Files API<br/>/api/files]
        USERS_API[Users API<br/>/api/users]
        SHARE_API[Sharing API<br/>/api/shares]
        ADMIN_API[Admin API<br/>/api/admin]
    end
    
    subgraph "Middleware"
        AUTH_MW[Authentication<br/>Middleware]
        RATE_MW[Rate Limiting<br/>Middleware]
        CORS_MW[CORS<br/>Middleware]
        LOG_MW[Logging<br/>Middleware]
    end
    
    subgraph "Controllers"
        AUTH_CTRL[Auth Controller]
        FILE_CTRL[File Controller]
        USER_CTRL[User Controller]
        SHARE_CTRL[Share Controller]
        ADMIN_CTRL[Admin Controller]
    end
    
    subgraph "Services"
        AUTH_SVC[Auth Service]
        FILE_SVC[File Service]
        USER_SVC[User Service]
        SHARE_SVC[Share Service]
        ADMIN_SVC[Admin Service]
    end
    
    subgraph "Data Layer"
        DATABASE[(Database)]
        CACHE[(Cache)]
        STORAGE[(File Storage)]
    end
    
    AUTH_API --> AUTH_MW
    FILES_API --> AUTH_MW
    USERS_API --> AUTH_MW
    SHARE_API --> AUTH_MW
    ADMIN_API --> AUTH_MW
    
    AUTH_MW --> RATE_MW
    RATE_MW --> CORS_MW
    CORS_MW --> LOG_MW
    
    LOG_MW --> AUTH_CTRL
    LOG_MW --> FILE_CTRL
    LOG_MW --> USER_CTRL
    LOG_MW --> SHARE_CTRL
    LOG_MW --> ADMIN_CTRL
    
    AUTH_CTRL --> AUTH_SVC
    FILE_CTRL --> FILE_SVC
    USER_CTRL --> USER_SVC
    SHARE_CTRL --> SHARE_SVC
    ADMIN_CTRL --> ADMIN_SVC
    
    AUTH_SVC --> DATABASE
    FILE_SVC --> DATABASE
    FILE_SVC --> STORAGE
    USER_SVC --> DATABASE
    USER_SVC --> CACHE
    SHARE_SVC --> DATABASE
    ADMIN_SVC --> DATABASE
    
    style AUTH_API fill:#e3f2fd
    style FILES_API fill:#e8f5e8
    style AUTH_MW fill:#fff3e0
    style AUTH_CTRL fill:#ffebee
    style AUTH_SVC fill:#f3e5f5
    style DATABASE fill:#e0f2f1
```

### API Request/Response Flow
```mermaid
sequenceDiagram
    participant Client
    participant Gateway
    participant Auth
    participant Controller
    participant Service
    participant Database
    participant Storage
    
    Client->>Gateway: API Request
    Gateway->>Gateway: Rate Limiting Check
    Gateway->>Auth: Validate Token
    Auth-->>Gateway: Token Valid
    Gateway->>Controller: Route Request
    
    Controller->>Controller: Input Validation
    Controller->>Service: Business Logic
    Service->>Database: Query Data
    Database-->>Service: Return Data
    
    alt File Operation
        Service->>Storage: File Operation
        Storage-->>Service: Operation Result
    end
    
    Service-->>Controller: Processed Data
    Controller->>Controller: Format Response
    Controller-->>Gateway: API Response
    Gateway-->>Client: JSON Response
```

## 📁 File Processing Flow

### File Upload Processing
```mermaid
flowchart TD
    START([File Upload Request]) --> VALIDATE_AUTH{Authenticated?}
    VALIDATE_AUTH -->|No| RETURN_401[Return 401 Unauthorized]
    VALIDATE_AUTH -->|Yes| CHECK_CSRF{Valid CSRF Token?}
    CHECK_CSRF -->|No| RETURN_403[Return 403 Forbidden]
    CHECK_CSRF -->|Yes| VALIDATE_FILE{Valid File?}
    
    VALIDATE_FILE -->|No| RETURN_400[Return 400 Bad Request]
    VALIDATE_FILE -->|Yes| CHECK_SIZE{Size OK?}
    CHECK_SIZE -->|No| RETURN_413[Return 413 Too Large]
    CHECK_SIZE -->|Yes| CHECK_TYPE{Type Allowed?}
    CHECK_TYPE -->|No| RETURN_415[Return 415 Unsupported]
    CHECK_TYPE -->|Yes| VIRUS_SCAN{Virus Free?}
    
    VIRUS_SCAN -->|No| QUARANTINE[Quarantine File]
    QUARANTINE --> RETURN_400
    VIRUS_SCAN -->|Yes| GENERATE_HASH[Generate File Hash]
    GENERATE_HASH --> CHECK_DUPLICATE{Duplicate?}
    CHECK_DUPLICATE -->|Yes| LINK_EXISTING[Link to Existing]
    CHECK_DUPLICATE -->|No| SAVE_FILE[Save File to Storage]
    
    SAVE_FILE --> GENERATE_THUMB{Image File?}
    GENERATE_THUMB -->|Yes| CREATE_THUMB[Create Thumbnail]
    GENERATE_THUMB -->|No| SAVE_METADATA[Save to Database]
    CREATE_THUMB --> SAVE_METADATA
    LINK_EXISTING --> SAVE_METADATA
    
    SAVE_METADATA --> UPDATE_STATS[Update User Stats]
    UPDATE_STATS --> LOG_ACTIVITY[Log Upload Activity]
    LOG_ACTIVITY --> RETURN_SUCCESS[Return 200 Success]
    
    style START fill:#e3f2fd
    style RETURN_SUCCESS fill:#e8f5e8
    style RETURN_401 fill:#ffcdd2
    style RETURN_403 fill:#ffcdd2
    style RETURN_400 fill:#ffcdd2
    style RETURN_413 fill:#ffcdd2
    style RETURN_415 fill:#ffcdd2
    style QUARANTINE fill:#fff3e0
    style SAVE_FILE fill:#e0f2f1
```

### File Security Scanning
```mermaid
graph TB
    subgraph "File Input"
        UPLOAD[Uploaded File]
        METADATA[File Metadata]
    end
    
    subgraph "Security Checks"
        EXTENSION[Extension Check]
        MIME[MIME Type Check]
        MAGIC[Magic Number Check]
        SIZE[Size Validation]
        NAME[Filename Validation]
    end
    
    subgraph "Content Analysis"
        VIRUS[Virus Scanning]
        MALWARE[Malware Detection]
        CONTENT[Content Analysis]
        STRUCTURE[File Structure Check]
    end
    
    subgraph "Risk Assessment"
        RISK_CALC[Risk Calculation]
        POLICY[Policy Check]
        WHITELIST[Whitelist Check]
        BLACKLIST[Blacklist Check]
    end
    
    subgraph "Action Decision"
        ALLOW[Allow Upload]
        QUARANTINE[Quarantine]
        REJECT[Reject Upload]
        SANITIZE[Sanitize File]
    end
    
    UPLOAD --> EXTENSION
    UPLOAD --> MIME
    UPLOAD --> MAGIC
    METADATA --> SIZE
    METADATA --> NAME
    
    EXTENSION --> VIRUS
    MIME --> MALWARE
    MAGIC --> CONTENT
    SIZE --> STRUCTURE
    NAME --> STRUCTURE
    
    VIRUS --> RISK_CALC
    MALWARE --> RISK_CALC
    CONTENT --> POLICY
    STRUCTURE --> WHITELIST
    
    RISK_CALC --> BLACKLIST
    POLICY --> ALLOW
    WHITELIST --> ALLOW
    BLACKLIST --> QUARANTINE
    
    QUARANTINE --> REJECT
    ALLOW --> SANITIZE
    
    style UPLOAD fill:#e3f2fd
    style EXTENSION fill:#fff3e0
    style VIRUS fill:#ffebee
    style RISK_CALC fill:#f3e5f5
    style ALLOW fill:#e8f5e8
    style QUARANTINE fill:#ffcdd2
    style REJECT fill:#ffcdd2
```

## 👤 User Journey

### User Registration to File Upload
```mermaid
journey
    title User Journey: Registration to First Upload
    
    section Registration
      Visit Website: 5: User
      Click Register: 3: User
      Fill Form: 2: User
      Submit Registration: 4: User
      Email Verification: 3: User
      
    section First Login
      Enter Credentials: 4: User
      Two-Factor Auth: 3: User
      Access Dashboard: 5: User
      View Tutorial: 4: User
      
    section File Upload
      Click Upload: 5: User
      Select Files: 4: User
      Drag and Drop: 5: User
      Upload Progress: 4: User
      Upload Complete: 5: User
      
    section File Management
      View Files: 5: User
      Organize Folders: 4: User
      Share Files: 4: User
      Download Files: 5: User
```

### Admin User Management Flow
```mermaid
stateDiagram-v2
    [*] --> AdminLogin
    AdminLogin --> Dashboard : Valid Credentials
    Dashboard --> UserManagement : Manage Users
    Dashboard --> SystemSettings : Configure System
    Dashboard --> Reports : View Reports
    
    UserManagement --> ViewUsers
    UserManagement --> CreateUser
    UserManagement --> EditUser
    UserManagement --> DeleteUser
    
    ViewUsers --> UserDetails
    CreateUser --> UserCreated
    EditUser --> UserUpdated
    DeleteUser --> UserDeleted
    
    UserCreated --> UserManagement
    UserUpdated --> UserManagement
    UserDeleted --> UserManagement
    UserDetails --> UserManagement
    
    SystemSettings --> SecuritySettings
    SystemSettings --> StorageSettings
    SystemSettings --> EmailSettings
    
    Reports --> UserReports
    Reports --> FileReports
    Reports --> SecurityReports
    Reports --> SystemReports
    
    state UserManagement {
        [*] --> ListUsers
        ListUsers --> FilterUsers
        FilterUsers --> SearchUsers
        SearchUsers --> ListUsers
    }
```

## 📊 Performance Monitoring

### System Performance Dashboard
```mermaid
graph TB
    subgraph "Performance Metrics"
        CPU[CPU Usage]
        MEMORY[Memory Usage]
        DISK[Disk Usage]
        NETWORK[Network I/O]
    end
    
    subgraph "Application Metrics"
        RESPONSE[Response Time]
        THROUGHPUT[Throughput]
        ERROR_RATE[Error Rate]
        UPTIME[Uptime]
    end
    
    subgraph "Database Metrics"
        DB_CONN[DB Connections]
        QUERY_TIME[Query Time]
        SLOW_QUERIES[Slow Queries]
        DEADLOCKS[Deadlocks]
    end
    
    subgraph "File System Metrics"
        UPLOAD_SPEED[Upload Speed]
        DOWNLOAD_SPEED[Download Speed]
        STORAGE_USED[Storage Used]
        FILE_COUNT[File Count]
    end
    
    subgraph "Security Metrics"
        FAILED_LOGINS[Failed Logins]
        BLOCKED_IPS[Blocked IPs]
        VIRUS_DETECTED[Virus Detected]
        SUSPICIOUS_ACTIVITY[Suspicious Activity]
    end
    
    subgraph "Alerting System"
        THRESHOLD[Threshold Monitoring]
        ALERTS[Alert Generation]
        NOTIFICATIONS[Notifications]
        ESCALATION[Escalation Rules]
    end
    
    CPU --> THRESHOLD
    MEMORY --> THRESHOLD
    RESPONSE --> THRESHOLD
    ERROR_RATE --> THRESHOLD
    DB_CONN --> THRESHOLD
    FAILED_LOGINS --> THRESHOLD
    
    THRESHOLD --> ALERTS
    ALERTS --> NOTIFICATIONS
    NOTIFICATIONS --> ESCALATION
    
    style CPU fill:#e3f2fd
    style RESPONSE fill:#e8f5e8
    style DB_CONN fill:#fff3e0
    style UPLOAD_SPEED fill:#f3e5f5
    style FAILED_LOGINS fill:#ffebee
    style ALERTS fill:#ffcdd2
```

---

## 📝 Diagram Usage Instructions

### Viewing Diagrams
These diagrams use **Mermaid** syntax and can be viewed in:
- GitHub (native support)
- GitLab (native support)
- VS Code (with Mermaid extension)
- Mermaid Live Editor (https://mermaid.live)
- Documentation platforms (GitBook, Notion, etc.)

### Editing Diagrams
To modify any diagram:
1. Copy the Mermaid code
2. Paste into Mermaid Live Editor
3. Make your changes
4. Export or copy the updated code

### Integration
These diagrams can be integrated into:
- Technical documentation
- System design reviews
- Architecture presentations
- Developer onboarding materials
- Stakeholder communications

---

**Built with ❤️ by [whympxx](https://github.com/whympxx)**

*Professional architecture diagrams for modern file management*
