# 🎨 FileManager Pro - Visual System Diagrams

<div align="center">
  <img src="https://img.shields.io/badge/Visual-Diagrams-28a745?style=for-the-badge" alt="Visual Diagrams">
  <img src="https://img.shields.io/badge/ASCII-Art-blue?style=for-the-badge" alt="ASCII Art">
  <img src="https://img.shields.io/badge/PlantUML-Supported-purple?style=for-the-badge" alt="PlantUML Supported">
</div>

## 📋 Overview

This document contains additional visual representations of the FileManager Pro system using ASCII art, PlantUML, and other visual formats that complement the Mermaid diagrams.

## 🏗️ System Architecture Overview

### High-Level System Architecture (ASCII)

```
╔══════════════════════════════════════════════════════════════════════════════╗
║                            FILEMANAGER PRO ARCHITECTURE                      ║
╠══════════════════════════════════════════════════════════════════════════════╣
║                                                                              ║
║  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐                     ║
║  │   Browser   │    │   Mobile    │    │ API Client  │  CLIENT LAYER        ║
║  │    (Web)    │    │   Device    │    │ (Third-party│                     ║
║  └─────────────┘    └─────────────┘    └─────────────┘                     ║
║           │                 │                 │                             ║
║           └─────────────────┼─────────────────┘                             ║
║                             │                                               ║
║  ╔═══════════════════════════▼═══════════════════════════╗                  ║
║  ║              LOAD BALANCER (Nginx/HAProxy)            ║  NETWORK LAYER   ║
║  ║  • SSL Termination  • Rate Limiting  • Health Checks ║                  ║
║  ╚═══════════════════════════╤═══════════════════════════╝                  ║
║                              │                                              ║
║  ┌─────────────┬─────────────┼─────────────┬─────────────┐                 ║
║  │             │             │             │             │                 ║
║  ▼             ▼             ▼             ▼             ▼                 ║
║ ┌───┐         ┌───┐         ┌───┐         ┌───┐         ┌───┐              ║
║ │WEB│         │WEB│         │WEB│         │WEB│         │WEB│ APPLICATION ║
║ │ 1 │         │ 2 │         │ 3 │         │ N │         │...│    LAYER    ║
║ └─┬─┘         └─┬─┘         └─┬─┘         └─┬─┘         └─┬─┘              ║
║   │             │             │             │             │                ║
║   └─────────────┼─────────────┼─────────────┼─────────────┘                ║
║                 │             │             │                              ║
║  ╔══════════════▼═════════════▼═════════════▼══════════════╗               ║
║  ║                  CACHING LAYER                         ║  CACHE LAYER  ║
║  ║  ┌─────────────┐           ┌─────────────┐            ║               ║
║  ║  │Redis Master │◄─────────►│Redis Replica│            ║               ║
║  ║  │ (Sessions)  │           │ (Read-only) │            ║               ║
║  ║  └─────────────┘           └─────────────┘            ║               ║
║  ╚════════════════════════════════════════════════════════╝               ║
║                              │                                             ║
║  ╔══════════════════════════════════════════════════════╗                  ║
║  ║                DATABASE LAYER                        ║  DATABASE LAYER ║
║  ║  ┌─────────────┐           ┌─────────────┐          ║                  ║
║  ║  │MySQL Master │──────────►│MySQL Replica│          ║                  ║
║  ║  │(Read/Write) │           │(Read-only)  │          ║                  ║
║  ║  └─────────────┘           └─────────────┘          ║                  ║
║  ╚══════════════════════════════════════════════════════╝                  ║
║                              │                                             ║
║  ╔══════════════════════════════════════════════════════╗                  ║
║  ║                STORAGE LAYER                         ║  STORAGE LAYER  ║
║  ║  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ║                  ║
║  ║  │Local Storage│  │Cloud Storage│  │Backup Storage│  ║                  ║
║  ║  │    (NFS)    │  │  (AWS S3)   │  │  (Glacier)  │  ║                  ║
║  ║  └─────────────┘  └─────────────┘  └─────────────┘  ║                  ║
║  ╚══════════════════════════════════════════════════════╝                  ║
║                                                                             ║
╚═════════════════════════════════════════════════════════════════════════════╝
```

### Application Component Diagram (ASCII)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        FILEMANAGER PRO COMPONENTS                           │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  FRONTEND LAYER                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐ │   │
│  │  │    User     │  │   Upload    │  │ File Grid   │  │   Preview   │ │   │
│  │  │ Interface   │  │ Component   │  │ Component   │  │ Component   │ │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘ │   │
│  │          │               │               │               │          │   │
│  └──────────┼───────────────┼───────────────┼───────────────┼──────────┘   │
│             │               │               │               │              │
│  ═══════════▼═══════════════▼═══════════════▼═══════════════▼══════════     │
│                                                                             │
│  BACKEND LAYER                                                              │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐ │   │
│  │  │   Router    │  │   Upload    │  │    File     │  │    User     │ │   │
│  │  │ Controller  │  │  Handler    │  │  Manager    │  │  Manager    │ │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘ │   │
│  │          │               │               │               │          │   │
│  └──────────┼───────────────┼───────────────┼───────────────┼──────────┘   │
│             │               │               │               │              │
│  ═══════════▼═══════════════▼═══════════════▼═══════════════▼══════════     │
│                                                                             │
│  SERVICE LAYER                                                              │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐ │   │
│  │  │    File     │  │    Auth     │  │ Validation  │  │   Logging   │ │   │
│  │  │  Service    │  │  Service    │  │  Service    │  │  Service    │ │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘ │   │
│  │          │               │               │               │          │   │
│  └──────────┼───────────────┼───────────────┼───────────────┼──────────┘   │
│             │               │               │               │              │
│  ═══════════▼═══════════════▼═══════════════▼═══════════════▼══════════     │
│                                                                             │
│  DATA LAYER                                                                 │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐ │   │
│  │  │  Database   │  │    Cache    │  │    File     │  │    Logs     │ │   │
│  │  │   (MySQL)   │  │  (Redis)    │  │  System     │  │  Storage    │ │   │
│  │  └─────────────┘  └─────────────┘  └─────────────┘  └─────────────┘ │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

## 🗄️ Database Schema Visualization

### Entity Relationship Diagram (ASCII)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        DATABASE SCHEMA OVERVIEW                             │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐       │
│  │     USERS       │     │     FILES       │     │    FOLDERS      │       │
│  ├─────────────────┤     ├─────────────────┤     ├─────────────────┤       │
│  │ id (PK)         │────►│ id (PK)         │     │ id (PK)         │       │
│  │ username (UK)   │     │ filename        │     │ folder_name     │       │
│  │ email (UK)      │     │ original_name   │     │ folder_path     │       │
│  │ password_hash   │     │ file_path       │     │ parent_id (FK)  │───┐   │
│  │ full_name       │     │ file_size       │     │ user_id (FK)    │◄──┼─┐ │
│  │ profile_picture │     │ file_type       │     │ created_at      │   │ │ │
│  │ status          │     │ mime_type       │     │ updated_at      │   │ │ │
│  │ created_at      │     │ file_hash       │     └─────────────────┘   │ │ │
│  │ updated_at      │     │ user_id (FK)    │◄────────────────────────────┘ │ │
│  │ last_login      │     │ status          │                           │ │
│  └─────────────────┘     │ upload_date     │                           │ │
│           │               │ updated_at      │                           │ │
│           │               │ download_count  │                           │ │
│           │               └─────────────────┘                           │ │
│           │                        │                                    │ │
│           │                        │                                    │ │
│           ▼                        ▼                                    │ │
│  ┌─────────────────┐     ┌─────────────────┐                           │ │
│  │  USER_SESSIONS  │     │  FILE_SHARES    │                           │ │
│  ├─────────────────┤     ├─────────────────┤                           │ │
│  │ session_id (PK) │     │ id (PK)         │                           │ │
│  │ user_id (FK)    │◄────│ file_id (FK)    │                           │ │
│  │ session_data    │     │ share_token(UK) │                           │ │
│  │ ip_address      │     │ password_hash   │                           │ │
│  │ user_agent      │     │ expires_at      │                           │ │
│  │ created_at      │     │ download_limit  │                           │ │
│  │ expires_at      │     │ download_count  │                           │ │
│  └─────────────────┘     │ permissions     │                           │ │
│           │               │ created_at      │                           │ │
│           │               │ created_by (FK) │◄──────────────────────────┘ │
│           │               └─────────────────┘                             │
│           │                        │                                      │
│           ▼                        │                                      │
│  ┌─────────────────┐               │                                      │
│  │ ACTIVITY_LOGS   │               │                                      │
│  ├─────────────────┤               │                                      │
│  │ id (PK)         │               │                                      │
│  │ user_id (FK)    │◄──────────────┘                                      │
│  │ action          │                                                      │
│  │ resource_type   │               ┌─────────────────┐                    │
│  │ resource_id     │               │ FILE_VERSIONS   │                    │
│  │ ip_address      │               ├─────────────────┤                    │
│  │ user_agent      │               │ id (PK)         │                    │
│  │ metadata        │               │ file_id (FK)    │◄───────────────────┘
│  │ created_at      │               │ version_number  │
│  └─────────────────┘               │ file_path       │
│                                    │ file_size       │
│                                    │ file_hash       │
│                                    │ created_at      │
│                                    │ created_by (FK) │
│                                    └─────────────────┘
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

## 🔄 File Upload Process Flow

### File Upload Workflow (ASCII)

```
                            FILE UPLOAD PROCESS FLOW
                            
    ┌─────────────┐
    │    USER     │
    │ Selects File│
    └─────┬───────┘
          │
          ▼
    ┌─────────────┐         ┌─────────────┐         ┌─────────────┐
    │   BROWSER   │        │  FRONTEND   │        │   BACKEND   │
    │ Validation  │───────►│ JavaScript  │───────►│ PHP Server  │
    │ • File Size │        │ • Progress  │        │ • Auth Check│
    │ • File Type │        │ • Drag&Drop │        │ • CSRF Token│
    └─────┬───────┘        └─────────────┘        └─────┬───────┘
          │                                             │
          │ ┌─────────────────────────────────────────────┘
          │ │
          ▼ ▼
    ┌─────────────┐         ┌─────────────┐         ┌─────────────┐
    │FILE SECURITY│        │   STORAGE   │        │  DATABASE   │
    │• Virus Scan │───────►│• Save File  │───────►│• Save Meta  │
    │• Type Check │        │• Generate   │        │• Update     │
    │• Size Limit │        │  Thumbnail  │        │  Stats      │
    └─────┬───────┘        └─────────────┘        └─────┬───────┘
          │                                             │
          │  ┌────────────────────────────────────────────┘
          │  │
          ▼  ▼
    ┌─────────────┐         ┌─────────────┐         ┌─────────────┐
    │   LOGGING   │        │    CACHE    │        │  RESPONSE   │
    │• Activity   │◄───────│• Update     │◄───────│• Success/   │
    │  Log        │        │  User Data  │        │  Error      │
    │• Security   │        │• Invalidate │        │• File Info  │
    │  Audit      │        │  Cache      │        │• Redirect   │
    └─────────────┘        └─────────────┘        └─────────────┘
```

### File Security Scanning Process

```
                        FILE SECURITY SCANNING PROCESS
                        
    ┌─────────────┐
    │   UPLOADED  │
    │    FILE     │
    └─────┬───────┘
          │
          ▼
    ╔═════════════╗
    ║   PHASE 1   ║     ┌─────────────────────────────────────────────┐
    ║  BASIC      ║────►│ • File Extension Check                      │
    ║ VALIDATION  ║     │ • MIME Type Validation                      │
    ╚═════════════╝     │ • File Size Verification                    │
          │             │ • Filename Sanitization                     │
          │             └─────────────────────────────────────────────┘
          ▼                                  │
    ╔═════════════╗                         │ PASS
    ║   PHASE 2   ║     ┌─────────────────────────────────────────────┐
    ║  CONTENT    ║────►│ • Magic Number Check                        │
    ║ ANALYSIS    ║     │ • File Structure Analysis                   │
    ╚═════════════╝     │ • Embedded Content Scan                     │
          │             │ • Metadata Extraction                       │
          │             └─────────────────────────────────────────────┘
          ▼                                  │
    ╔═════════════╗                         │ PASS
    ║   PHASE 3   ║     ┌─────────────────────────────────────────────┐
    ║  SECURITY   ║────►│ • Virus/Malware Scanning                    │
    ║  SCANNING   ║     │ • Threat Signature Detection                │
    ╚═════════════╝     │ • Behavioral Analysis                       │
          │             │ • Reputation Check                          │
          │             └─────────────────────────────────────────────┘
          ▼                                  │
    ╔═════════════╗                         │ PASS
    ║   PHASE 4   ║     ┌─────────────────────────────────────────────┐
    ║    RISK     ║────►│ • Risk Score Calculation                    │
    ║ ASSESSMENT  ║     │ • Policy Compliance Check                   │
    ╚═════════════╝     │ • Whitelist/Blacklist Verification         │
          │             │ • Final Decision Making                     │
          │             └─────────────────────────────────────────────┘
          ▼
    ┌─────────────┐     ┌─────────────┐     ┌─────────────┐
    │   ALLOW     │     │ QUARANTINE  │     │   REJECT    │
    │  • Store    │     │ • Isolate   │     │ • Delete    │
    │  • Process  │     │ • Review    │     │ • Log       │
    │  • Notify   │     │ • Alert     │     │ • Notify    │
    └─────────────┘     └─────────────┘     └─────────────┘
```

## 🚀 Deployment Architecture

### Docker Container Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         DOCKER DEPLOYMENT ARCHITECTURE                      │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─────────────────────────────────────────────────────────────────────┐   │
│  │                        DOCKER HOST                                  │   │
│  │                                                                     │   │
│  │  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐     │   │
│  │  │  NGINX PROXY    │  │  FILEMANAGER    │  │  FILEMANAGER    │     │   │
│  │  │   CONTAINER     │  │   CONTAINER 1   │  │   CONTAINER 2   │     │   │
│  │  │                 │  │                 │  │                 │     │   │
│  │  │ ┌─────────────┐ │  │ ┌─────────────┐ │  │ ┌─────────────┐ │     │   │
│  │  │ │Load Balancer│ │  │ │    Apache   │ │  │ │    Apache   │ │     │   │
│  │  │ │    :80      │◄┼──┼─┤    :80     │ │  │ │    :80     │ │     │   │
│  │  │ │    :443     │ │  │ │             │ │  │ │             │ │     │   │
│  │  │ └─────────────┘ │  │ │   PHP 8.1   │ │  │ │   PHP 8.1   │ │     │   │
│  │  └─────────────────┘  │ │             │ │  │ │             │ │     │   │
│  │                       │ └─────────────┘ │  │ └─────────────┘ │     │   │
│  │                       └─────────────────┘  └─────────────────┘     │   │
│  │                                   │                 │              │   │
│  │  ┌─────────────────┐              │                 │              │   │
│  │  │    DATABASE     │              │                 │              │   │
│  │  │   CONTAINERS    │◄─────────────┼─────────────────┘              │   │
│  │  │                 │              │                                │   │
│  │  │ ┌─────────────┐ │              │                                │   │
│  │  │ │   MySQL     │ │              │                                │   │
│  │  │ │    :3306    │ │              │                                │   │
│  │  │ └─────────────┘ │              │                                │   │
│  │  │                 │              ▼                                │   │
│  │  │ ┌─────────────┐ │  ┌─────────────────────────────────┐          │   │
│  │  │ │    Redis    │ │  │         SHARED VOLUMES          │          │   │
│  │  │ │    :6379    │ │  │                                 │          │   │
│  │  │ └─────────────┘ │  │ ┌─────────────┐ ┌─────────────┐ │          │   │
│  │  │                 │  │ │   uploads   │ │    logs     │ │          │   │
│  │  │ ┌─────────────┐ │  │ │    data     │ │    data     │ │          │   │
│  │  │ │ phpMyAdmin  │ │  │ └─────────────┘ └─────────────┘ │          │   │
│  │  │ │    :8081    │ │  │                                 │          │   │
│  │  │ └─────────────┘ │  │ ┌─────────────┐ ┌─────────────┐ │          │   │
│  │  └─────────────────┘  │ │   mysql     │ │    redis    │ │          │   │
│  │                       │ │    data     │ │    data     │ │          │   │
│  │                       │ └─────────────┘ └─────────────┘ │          │   │
│  │                       └─────────────────────────────────┘          │   │
│  │                                                                     │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
└─────────────────────────────────────────────────────────────────────────────┘
```

## 🔌 API Architecture Overview

### REST API Structure (Visual)

```
                            API ARCHITECTURE OVERVIEW
                            
    ┌─────────────────────────────────────────────────────────────────────┐
    │                        CLIENT REQUESTS                              │
    └─────────────────────────┬───────────────────────────────────────────┘
                              │
                              ▼
    ┌─────────────────────────────────────────────────────────────────────┐
    │                    NGINX REVERSE PROXY                              │
    │                 • SSL Termination                                   │
    │                 • Load Balancing                                    │
    │                 • Rate Limiting                                     │
    └─────────────────────────┬───────────────────────────────────────────┘
                              │
                              ▼
    ╔═════════════════════════════════════════════════════════════════════╗
    ║                      MIDDLEWARE CHAIN                               ║
    ╠═════════════════════════════════════════════════════════════════════╣
    ║  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐   ║
    ║  │    CORS     │→│    AUTH     │→│ RATE LIMIT  │→│   LOGGING   │   ║
    ║  │ Middleware  │ │ Middleware  │ │ Middleware  │ │ Middleware  │   ║
    ║  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘   ║
    ╚═════════════════════════════════════════════════════════════════════╝
                              │
                              ▼
    ┌─────────────────────────────────────────────────────────────────────┐
    │                       API ROUTES                                    │
    ├─────────────────────────────────────────────────────────────────────┤
    │  /api/auth/*      │  /api/files/*     │  /api/users/*     │        │
    │  • POST /login    │  • GET /list      │  • GET /profile   │        │
    │  • POST /register │  • POST /upload   │  • PUT /profile   │        │
    │  • POST /logout   │  • DELETE /{id}   │  • GET /stats     │        │
    │  • POST /refresh  │  • PUT /{id}      │  • POST /avatar   │        │
    └─────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
    ┌─────────────────────────────────────────────────────────────────────┐
    │                      CONTROLLERS                                    │
    ├─────────────────────────────────────────────────────────────────────┤
    │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐   │
    │  │    Auth     │ │    File     │ │    User     │ │   Admin     │   │
    │  │ Controller  │ │ Controller  │ │ Controller  │ │ Controller  │   │
    │  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘   │
    └─────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
    ┌─────────────────────────────────────────────────────────────────────┐
    │                        SERVICES                                     │
    ├─────────────────────────────────────────────────────────────────────┤
    │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐   │
    │  │    Auth     │ │    File     │ │    User     │ │ Validation  │   │
    │  │  Service    │ │  Service    │ │  Service    │ │  Service    │   │
    │  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘   │
    └─────────────────────────────────────────────────────────────────────┘
                              │
                              ▼
    ┌─────────────────────────────────────────────────────────────────────┐
    │                      DATA LAYER                                     │
    ├─────────────────────────────────────────────────────────────────────┤
    │  ┌─────────────┐ ┌─────────────┐ ┌─────────────┐ ┌─────────────┐   │
    │  │   MySQL     │ │    Redis    │ │    File     │ │    Logs     │   │
    │  │  Database   │ │    Cache    │ │   System    │ │  Storage    │   │
    │  └─────────────┘ └─────────────┘ └─────────────┘ └─────────────┘   │
    └─────────────────────────────────────────────────────────────────────┘
```

## 👤 User Journey Visualization

### User Registration to File Management

```
USER JOURNEY: FROM REGISTRATION TO FILE MANAGEMENT

    👤 NEW USER
         │
         ▼
    ┌─────────────┐    Step 1: Discovery
    │   LANDING   │◄─── • Google Search
    │    PAGE     │     • Social Media
    │             │     • Word of Mouth
    └─────┬───────┘     • Direct URL
          │
          ▼
    ┌─────────────┐    Step 2: Registration
    │ REGISTRATION│◄─── • Fill Form
    │    FORM     │     • Email Verification  
    │             │     • Account Activation
    └─────┬───────┘     • Welcome Email
          │
          ▼
    ┌─────────────┐    Step 3: First Login
    │    LOGIN    │◄─── • Enter Credentials
    │    PAGE     │     • Remember Device
    │             │     • Security Check
    └─────┬───────┘     • Session Creation
          │
          ▼
    ┌─────────────┐    Step 4: Dashboard
    │  DASHBOARD  │◄─── • Tour/Tutorial
    │    PAGE     │     • Feature Overview
    │             │     • Upload Prompt
    └─────┬───────┘     • Storage Info
          │
          ▼
    ┌─────────────┐    Step 5: First Upload
    │   UPLOAD    │◄─── • Drag & Drop
    │ INTERFACE   │     • Progress Tracking
    │             │     • Success Message
    └─────┬───────┘     • File Preview
          │
          ▼
    ┌─────────────┐    Step 6: File Management
    │    FILE     │◄─── • Organize Files
    │ MANAGEMENT  │     • Create Folders
    │             │     • Share Files
    └─────┬───────┘     • Download Files
          │
          ▼
    ┌─────────────┐    Step 7: Advanced Features
    │  ADVANCED   │◄─── • File Sharing
    │  FEATURES   │     • Collaboration
    │             │     • API Usage
    └─────────────┘     • Admin Functions

    📊 SUCCESS METRICS:
    • Registration Completion: 85%
    • First Upload within 24h: 70%
    • Weekly Active Users: 60%
    • Feature Adoption: 45%
```

## 🔒 Security Architecture Visualization

### Multi-Layer Security Model

```
                        SECURITY ARCHITECTURE LAYERS
                        
    ┌─────────────────────────────────────────────────────────────────────┐
    │                      EXTERNAL THREATS                               │
    │     🌐 Internet    🦠 Malware    🔓 Hackers    📧 Phishing         │
    └─────────────────────────────┬───────────────────────────────────────┘
                                  │
                                  ▼
    ╔═════════════════════════════════════════════════════════════════════╗
    ║                    LAYER 1: NETWORK SECURITY                       ║
    ╠═════════════════════════════════════════════════════════════════════╣
    ║  🔥 Firewall    🛡️ DDoS Protection    🔍 IDS/IPS    📊 Monitoring ║
    ╚═════════════════════════════════════════════════════════════════════╝
                                  │
                                  ▼
    ╔═════════════════════════════════════════════════════════════════════╗
    ║                  LAYER 2: APPLICATION SECURITY                     ║
    ╠═════════════════════════════════════════════════════════════════════╣
    ║  🔒 HTTPS/TLS    🛡️ WAF    🔐 CSRF Protection    🧹 Input Sanitize║
    ╚═════════════════════════════════════════════════════════════════════╝
                                  │
                                  ▼
    ╔═════════════════════════════════════════════════════════════════════╗
    ║               LAYER 3: AUTHENTICATION & AUTHORIZATION              ║
    ╠═════════════════════════════════════════════════════════════════════╣
    ║  👤 User Auth    🔑 2FA    📱 Sessions    🎭 Role-Based Access     ║
    ╚═════════════════════════════════════════════════════════════════════╝
                                  │
                                  ▼
    ╔═════════════════════════════════════════════════════════════════════╗
    ║                    LAYER 4: FILE SECURITY                          ║
    ╠═════════════════════════════════════════════════════════════════════╣
    ║  🦠 Virus Scan    📝 Type Check    🔒 Encryption    🚫 Quarantine  ║
    ╚═════════════════════════════════════════════════════════════════════╝
                                  │
                                  ▼
    ╔═════════════════════════════════════════════════════════════════════╗
    ║                     LAYER 5: DATA SECURITY                         ║
    ╠═════════════════════════════════════════════════════════════════════╣
    ║  🗄️ DB Encryption    💾 Backup Security    🔑 Key Management      ║
    ╚═════════════════════════════════════════════════════════════════════╝
                                  │
                                  ▼
    ┌─────────────────────────────────────────────────────────────────────┐
    │                    🔒 SECURE DATA STORAGE                           │
    │          💾 Database    📁 Files    📋 Logs    🔐 Backups           │
    └─────────────────────────────────────────────────────────────────────┘
```

## 📊 System Performance Monitoring

### Performance Dashboard Layout

```
                        SYSTEM PERFORMANCE DASHBOARD
                        
    ┌─────────────────────────────────────────────────────────────────────┐
    │                        FILEMANAGER PRO                              │
    │                     Performance Dashboard                           │
    ├─────────────────────────────────────────────────────────────────────┤
    │                                                                     │
    │  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐     │
    │  │   SYSTEM HEALTH │  │  RESPONSE TIME  │  │   THROUGHPUT    │     │
    │  │                 │  │                 │  │                 │     │
    │  │  CPU: ████░░ 80%│  │  Avg: 245ms     │  │  1,234 req/min  │     │
    │  │  RAM: ██████ 60%│  │  P95: 1.2s      │  │  89% Success    │     │
    │  │  DISK:████░░ 75%│  │  P99: 2.1s      │  │  11% Errors     │     │
    │  │                 │  │                 │  │                 │     │
    │  └─────────────────┘  └─────────────────┘  └─────────────────┘     │
    │                                                                     │
    │  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐     │
    │  │   FILE STORAGE  │  │   USER ACTIVITY │  │   ERROR RATES   │     │
    │  │                 │  │                 │  │                 │     │
    │  │  Used: 2.3TB    │  │  Online: 1,547  │  │  HTTP 4xx: 2.1% │     │
    │  │  Free: 780GB    │  │  Uploads: 89    │  │  HTTP 5xx: 0.3% │     │
    │  │  Files: 45,678  │  │  Downloads: 156 │  │  DB Errors: 0.1%│     │
    │  │                 │  │                 │  │                 │     │
    │  └─────────────────┘  └─────────────────┘  └─────────────────┘     │
    │                                                                     │
    │  ┌─────────────────────────────────────────────────────────────┐   │
    │  │                    REAL-TIME METRICS                        │   │
    │  │                                                             │   │
    │  │  Request Rate:  ████████████████████████████░░░░  85 req/s  │   │
    │  │  Error Rate:    ██░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░   2.1%     │   │
    │  │  Response Time: ████████████████████░░░░░░░░░░░░  245ms     │   │
    │  │  Active Users:  ████████████████████████████████  1,547    │   │
    │  │                                                             │   │
    │  └─────────────────────────────────────────────────────────────┘   │
    │                                                                     │
    │  ┌─────────────────────────────────────────────────────────────┐   │
    │  │                      ALERT STATUS                           │   │
    │  │                                                             │   │
    │  │  🟢 All Systems Operational                                 │   │
    │  │  🟡 High CPU Usage on Server-02                            │   │
    │  │  🔴 Database Connection Pool Warning                       │   │
    │  │  🟢 Storage Systems Healthy                                │   │
    │  │                                                             │   │
    │  └─────────────────────────────────────────────────────────────┘   │
    │                                                                     │
    └─────────────────────────────────────────────────────────────────────┘
```

## 🔄 CI/CD Pipeline Visualization

### Development to Production Pipeline

```
                            CI/CD PIPELINE FLOW
                            
    👨‍💻 DEVELOPER                   ☁️ GITHUB                    🔧 CI/CD
         │                           │                          │
         │ git push                  │                          │
         ▼                           ▼                          ▼
    ┌─────────────┐           ┌─────────────┐           ┌─────────────┐
    │   LOCAL     │          │  GITHUB     │          │   GITHUB    │
    │ DEVELOPMENT │─────────►│ REPOSITORY  │─────────►│   ACTIONS   │
    │             │          │             │          │             │
    │ • Code      │          │ • Source    │          │ • Build     │
    │ • Test      │          │   Control   │          │ • Test      │
    │ • Debug     │          │ • Branches  │          │ • Deploy    │
    └─────────────┘          └─────────────┘          └─────────────┘
                                                              │
                                                              ▼
                             🧪 TESTING                🐳 CONTAINERIZATION
                                  │                          │
                                  ▼                          ▼
                            ┌─────────────┐           ┌─────────────┐
                            │   TESTING   │          │   DOCKER    │
                            │ ENVIRONMENT │◄─────────│    BUILD    │
                            │             │          │             │
                            │ • Unit Test │          │ • Image     │
                            │ • Integration│          │   Creation  │
                            │ • Security  │          │ • Registry  │
                            └─────────────┘          └─────────────┘
                                    │                        │
                                    ▼                        ▼
                            🚀 DEPLOYMENT               📊 MONITORING
                                    │                        │
                                    ▼                        ▼
                            ┌─────────────┐           ┌─────────────┐
                            │ PRODUCTION  │          │  MONITORING │
                            │ DEPLOYMENT  │◄─────────│ & ALERTING  │
                            │             │          │             │
                            │ • Rolling   │          │ • Health    │
                            │   Update    │          │   Checks    │
                            │ • Health    │          │ • Metrics   │
                            │   Check     │          │ • Alerts    │
                            └─────────────┘          └─────────────┘
```

---

## 📝 Usage Instructions

### ASCII Diagrams
- Copy and paste ASCII diagrams into documentation
- Use monospace fonts for proper alignment
- Best viewed in text editors or markdown viewers

### PlantUML Integration
To use PlantUML diagrams:

```bash
# Install PlantUML
npm install -g plantuml

# Generate images from PlantUML code
plantuml diagram.puml
```

### Mermaid Integration
For interactive Mermaid diagrams, see `ARCHITECTURE_DIAGRAMS.md`

### Visual Tools
Recommended tools for viewing/editing:
- **ASCII**: VS Code with ASCII Tree Generator
- **PlantUML**: PlantUML Online Editor
- **Mermaid**: Mermaid Live Editor
- **Diagrams**: Draw.io, Lucidchart

---

**Built with ❤️ by [whympxx](https://github.com/whympxx)**

*Comprehensive visual documentation for modern file management*
