# OpenCATS REST API Documentation

**Version:** 1.0.0
**Compatibility:** Bullhorn API Compatible
**Last Updated:** 2026-01-25

---

## Table of Contents

1. [Introduction](#1-introduction)
2. [Getting Started](#2-getting-started)
3. [Authentication](#3-authentication)
4. [API Endpoints](#4-api-endpoints)
5. [Common Parameters](#5-common-parameters)
6. [Error Handling](#6-error-handling)
7. [Rate Limiting](#7-rate-limiting)
8. [Webhooks](#8-webhooks)
9. [OAuth 2.0](#9-oauth-20)
10. [Edge Cases & Best Practices](#10-edge-cases--best-practices)
11. [Migration Guide](#11-migration-guide)
12. [Troubleshooting](#12-troubleshooting)

---

## 1. Introduction

The OpenCATS REST API provides programmatic access to your recruitment data. It follows RESTful conventions and is designed to be compatible with Bullhorn API patterns, making migration straightforward.

### Key Features

- **Full CRUD Operations** on all major entities
- **Bullhorn-Compatible** field names and response formats
- **OAuth 2.0 Support** for secure third-party integrations
- **Webhooks** for real-time event notifications
- **Rate Limiting** to ensure fair usage
- **Comprehensive Audit Logging** for compliance

### Base URL

```
https://your-opencats-domain.com/index.php?m=api&a={endpoint}
```

### Response Format

All responses are JSON:

```json
{
  "total": 100,
  "page": 1,
  "limit": 25,
  "data": [...]
}
```

Error responses:

```json
{
  "error": true,
  "message": "Error description",
  "code": 400
}
```

---

## 2. Getting Started

### 2.1 Installation

1. **Apply Database Migrations**

```bash
cd opencats
mysql -u username -p database_name < modules/install/Schema.php 001_add_api_and_tearsheets.sql
mysql -u username -p database_name < modules/install/Schema.php 002_oauth2_tables.sql
mysql -u username -p database_name < modules/install/Schema.php 003_job_submission_placement.sql
mysql -u username -p database_name < modules/install/Schema.php 004_extended_entities.sql
mysql -u username -p database_name < modules/install/Schema.php 005_tearsheet_candidates.sql
mysql -u username -p database_name < modules/install/Schema.php 006_webhooks.sql
```

2. **Configure API Settings** (optional - in `config.php`)

```php
// API Configuration
define('API_ENABLED', true);
define('API_VERSION', '1.0.0');
define('API_RATE_LIMIT_ENABLED', true);
define('API_RATE_LIMIT_PER_MINUTE', 60);
define('API_RATE_LIMIT_PER_HOUR', 1000);
define('API_CORS_ALLOWED_ORIGINS', 'https://your-app.com');
define('API_LOG_ENABLED', true);
```

3. **Create an API Key**

```sql
INSERT INTO api_keys (
  site_id, user_id, api_key, api_secret,
  name, access_level, is_active, date_created
) VALUES (
  1, 1, 'your-api-key-here', 'your-api-secret-here',
  'My Integration', 500, 1, NOW()
);
```

### 2.2 Quick Test

```bash
# Health check (no auth required)
curl https://your-domain.com/index.php?m=api&a=ping

# Expected response:
{
  "status": "ok",
  "version": "1.0.0",
  "timestamp": "2026-01-25T12:00:00+00:00"
}
```

---

## 3. Authentication

### 3.1 API Key Authentication

The simplest authentication method. Include your API key in every request.

**Option 1: X-Api-Key Header (Recommended)**

```bash
curl -H "X-Api-Key: your-api-key-here" \
     https://your-domain.com/index.php?m=api&a=candidates
```

**Option 2: Authorization Bearer Header**

```bash
curl -H "Authorization: Bearer your-api-key-here" \
     https://your-domain.com/index.php?m=api&a=candidates
```

**Option 3: Query Parameter (Not recommended for production)**

```bash
curl "https://your-domain.com/index.php?m=api&a=candidates&api_key=your-api-key-here"
```

### 3.2 API Key + Secret Authentication

For enhanced security, authenticate with both key and secret to receive a time-limited access token.

**Request:**

```bash
curl -X POST \
     -H "Content-Type: application/json" \
     -d '{"api_key": "your-key", "api_secret": "your-secret"}' \
     https://your-domain.com/index.php?m=api&a=auth
```

**Response:**

```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "refresh_token": "dGhpcyBpcyBhIHJlZnJlc2ggdG9rZW4..."
}
```

**Use the token:**

```bash
curl -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..." \
     https://your-domain.com/index.php?m=api&a=candidates
```

### 3.3 Access Levels

| Level | Name | Permissions |
|-------|------|-------------|
| 100 | Read Only | GET requests only |
| 200 | Limited | GET, limited POST |
| 300 | Standard | GET, POST, PUT |
| 400 | Full | GET, POST, PUT, DELETE |
| 500 | Admin | Full access + admin functions |

---

## 4. API Endpoints

### 4.1 Job Orders

**List Job Orders**

```
GET /api&a=joborders
```

Parameters:
| Parameter | Type | Description |
|-----------|------|-------------|
| page | int | Page number (default: 1) |
| limit | int | Items per page (default: 25, max: 100) |
| status | string | Filter by status (Active, Closed, etc.) |
| fields | string | Comma-separated fields to return |
| sort | string | Field to sort by |
| order | string | ASC or DESC |

**Example:**

```bash
curl -H "X-Api-Key: your-key" \
     "https://domain.com/index.php?m=api&a=joborders&status=Active&limit=10"
```

**Response:**

```json
{
  "total": 45,
  "page": 1,
  "limit": 10,
  "data": [
    {
      "id": 1,
      "title": "Senior Software Engineer",
      "clientCorporation": {
        "id": 5,
        "name": "Tech Corp Inc"
      },
      "status": "Active",
      "type": "Full-time",
      "city": "Austin",
      "state": "TX",
      "salary": "120000-150000",
      "openings": 2,
      "dateAdded": "2026-01-15T10:30:00+00:00",
      "owner": {
        "id": 1,
        "firstName": "John",
        "lastName": "Recruiter"
      }
    }
  ]
}
```

**Get Single Job Order**

```
GET /api&a=joborders&id={id}
```

**Create Job Order**

```
POST /api&a=joborders
Content-Type: application/json

{
  "title": "DevOps Engineer",
  "companyID": 5,
  "contactID": 12,
  "type": "Full-time",
  "city": "San Francisco",
  "state": "CA",
  "salary": "130000-160000",
  "description": "We are looking for...",
  "openings": 1,
  "status": "Active"
}
```

**Update Job Order**

```
PUT /api&a=joborders&id={id}
Content-Type: application/json

{
  "status": "Closed",
  "openings": 0
}
```

**Delete Job Order**

```
DELETE /api&a=joborders&id={id}
```

---

### 4.2 Candidates

**List Candidates**

```
GET /api&a=candidates
```

Parameters:
| Parameter | Type | Description |
|-----------|------|-------------|
| page | int | Page number |
| limit | int | Items per page |
| status | string | Active, Passive, etc. |
| query | string | Search query (see Query Syntax) |
| fields | string | Fields to return |

**Example:**

```bash
curl -H "X-Api-Key: your-key" \
     "https://domain.com/index.php?m=api&a=candidates&query=skills:Python,city=Austin"
```

**Response:**

```json
{
  "total": 156,
  "page": 1,
  "limit": 25,
  "data": [
    {
      "id": 42,
      "firstName": "Jane",
      "lastName": "Developer",
      "email": "jane@example.com",
      "phone": "555-123-4567",
      "city": "Austin",
      "state": "TX",
      "status": "Active",
      "source": "LinkedIn",
      "currentEmployer": "Previous Corp",
      "currentTitle": "Software Engineer",
      "dateAdded": "2026-01-10T09:15:00+00:00",
      "owner": {
        "id": 1,
        "firstName": "John",
        "lastName": "Recruiter"
      }
    }
  ]
}
```

**Create Candidate**

```
POST /api&a=candidates
Content-Type: application/json

{
  "firstName": "John",
  "lastName": "Smith",
  "email": "john.smith@example.com",
  "phone": "555-987-6543",
  "city": "Denver",
  "state": "CO",
  "source": "Career Fair",
  "skills": "Python, JavaScript, AWS",
  "currentEmployer": "Current Corp",
  "currentTitle": "Developer"
}
```

---

### 4.3 Companies (ClientCorporation)

**List Companies**

```
GET /api&a=companies
```

**Response:**

```json
{
  "total": 89,
  "page": 1,
  "limit": 25,
  "data": [
    {
      "id": 5,
      "name": "Tech Corp Inc",
      "address": "123 Tech Blvd",
      "city": "Austin",
      "state": "TX",
      "zip": "78701",
      "phone": "555-TECH",
      "url": "https://techcorp.com",
      "status": "Active",
      "dateAdded": "2025-06-15T00:00:00+00:00"
    }
  ]
}
```

**Create Company**

```
POST /api&a=companies
Content-Type: application/json

{
  "name": "New Client Inc",
  "address": "456 Business Ave",
  "city": "Seattle",
  "state": "WA",
  "zip": "98101",
  "phone": "555-NEW-BIZ",
  "url": "https://newclient.com"
}
```

---

### 4.4 Contacts (ClientContact)

**List Contacts**

```
GET /api&a=contacts
GET /api&a=contacts&company={companyID}
```

**Response:**

```json
{
  "total": 234,
  "page": 1,
  "limit": 25,
  "data": [
    {
      "id": 12,
      "firstName": "Sarah",
      "lastName": "Manager",
      "title": "HR Director",
      "email": "sarah@techcorp.com",
      "phone": "555-HR-DEPT",
      "clientCorporation": {
        "id": 5,
        "name": "Tech Corp Inc"
      },
      "isHiringManager": true,
      "dateAdded": "2025-07-20T00:00:00+00:00"
    }
  ]
}
```

---

### 4.5 Job Submissions (Candidate Pipeline)

**List Submissions**

```
GET /api&a=jobsubmissions
GET /api&a=jobsubmissions&jobOrder={jobOrderID}
GET /api&a=jobsubmissions&candidate={candidateID}
GET /api&a=jobsubmissions&status=Submitted
```

**Response:**

```json
{
  "total": 12,
  "page": 1,
  "limit": 25,
  "data": [
    {
      "id": 101,
      "candidate": {
        "id": 42,
        "firstName": "Jane",
        "lastName": "Developer",
        "email": "jane@example.com"
      },
      "jobOrder": {
        "id": 1,
        "title": "Senior Software Engineer"
      },
      "clientCorporation": {
        "id": 5,
        "name": "Tech Corp Inc"
      },
      "status": "Interview Scheduled",
      "source": "Recruiter Sourced",
      "dateSubmitted": "2026-01-18T14:30:00+00:00",
      "dateInterview": "2026-01-22T10:00:00+00:00",
      "sendingUser": {
        "id": 1,
        "firstName": "John",
        "lastName": "Recruiter"
      }
    }
  ]
}
```

**Pipeline Statuses:**

| Status | Description |
|--------|-------------|
| Submitted | Initial submission |
| Reviewed | Client reviewed |
| Interview Scheduled | Interview set up |
| Interviewed | Interview completed |
| Offer Extended | Offer made |
| Offer Accepted | Candidate accepted |
| Placed | Candidate started |
| Rejected | Not selected |
| Withdrawn | Candidate withdrew |

**Create Submission**

```
POST /api&a=jobsubmissions
Content-Type: application/json

{
  "candidateID": 42,
  "jobOrderID": 1,
  "status": "Submitted",
  "source": "Database Search"
}
```

**Update Status**

```
PUT /api&a=jobsubmissions&id=101
Content-Type: application/json

{
  "status": "Interview Scheduled"
}
```

---

### 4.6 Placements

**List Placements**

```
GET /api&a=placements
GET /api&a=placements&status=Active
```

**Response:**

```json
{
  "total": 28,
  "page": 1,
  "limit": 25,
  "data": [
    {
      "id": 15,
      "candidate": {
        "id": 42,
        "firstName": "Jane",
        "lastName": "Developer"
      },
      "jobOrder": {
        "id": 1,
        "title": "Senior Software Engineer"
      },
      "clientCorporation": {
        "id": 5,
        "name": "Tech Corp Inc"
      },
      "status": "Active",
      "employmentType": "Direct Hire",
      "salary": 135000.00,
      "fee": 27000.00,
      "feePercent": 20.0,
      "startDate": "2026-02-01",
      "dateAdded": "2026-01-20T16:45:00+00:00"
    }
  ]
}
```

**Create Placement**

```
POST /api&a=placements
Content-Type: application/json

{
  "candidateID": 42,
  "jobOrderID": 1,
  "salary": 135000,
  "feePercent": 20,
  "startDate": "2026-02-01",
  "employmentType": "Direct Hire"
}
```

---

### 4.7 Notes

**List Notes**

```
GET /api&a=notes
GET /api&a=notes&entityType=candidate&entityID=42
```

**Response:**

```json
{
  "total": 5,
  "page": 1,
  "limit": 25,
  "data": [
    {
      "id": 234,
      "entityType": "candidate",
      "entityID": 42,
      "title": "Phone Screen",
      "text": "Spoke with candidate. Very interested in the role...",
      "action": "Phone Call",
      "dateAdded": "2026-01-19T11:30:00+00:00",
      "addedBy": {
        "id": 1,
        "firstName": "John",
        "lastName": "Recruiter"
      }
    }
  ]
}
```

**Create Note**

```
POST /api&a=notes
Content-Type: application/json

{
  "entityType": "candidate",
  "entityID": 42,
  "title": "Interview Feedback",
  "text": "Client feedback was positive. Moving to final round.",
  "action": "Note"
}
```

---

### 4.8 Appointments

**List Appointments**

```
GET /api&a=appointments
GET /api&a=appointments&startDate=2026-01-20&endDate=2026-01-27
```

**Response:**

```json
{
  "total": 8,
  "page": 1,
  "limit": 25,
  "data": [
    {
      "id": 56,
      "title": "Interview - Jane Developer",
      "type": "Interview",
      "description": "Final round interview with CTO",
      "startDate": "2026-01-22T10:00:00+00:00",
      "endDate": "2026-01-22T11:00:00+00:00",
      "allDay": false,
      "location": "Tech Corp HQ",
      "candidate": {
        "id": 42,
        "firstName": "Jane",
        "lastName": "Developer"
      },
      "jobOrder": {
        "id": 1,
        "title": "Senior Software Engineer"
      }
    }
  ]
}
```

**Create Appointment**

```
POST /api&a=appointments
Content-Type: application/json

{
  "title": "Phone Screen - John Smith",
  "type": "Phone Screen",
  "startDate": "2026-01-25T14:00:00",
  "endDate": "2026-01-25T14:30:00",
  "candidateID": 99,
  "jobOrderID": 1,
  "description": "Initial phone screen"
}
```

---

### 4.9 Tasks

**List Tasks**

```
GET /api&a=tasks
GET /api&a=tasks&status=Open
GET /api&a=tasks&assignedTo=1
```

**Response:**

```json
{
  "total": 15,
  "page": 1,
  "limit": 25,
  "data": [
    {
      "id": 78,
      "title": "Follow up with candidate",
      "description": "Send offer letter details",
      "priority": "High",
      "status": "Open",
      "dueDate": "2026-01-26T17:00:00+00:00",
      "assignedTo": {
        "id": 1,
        "firstName": "John",
        "lastName": "Recruiter"
      },
      "relatedEntity": {
        "type": "candidate",
        "id": 42
      }
    }
  ]
}
```

**Create Task**

```
POST /api&a=tasks
Content-Type: application/json

{
  "title": "Schedule final interview",
  "description": "Coordinate with hiring manager",
  "priority": "High",
  "dueDate": "2026-01-25",
  "entityType": "candidate",
  "entityID": 42
}
```

---

### 4.10 Tearsheets (Candidate Lists)

**List Tearsheets**

```
GET /api&a=tearsheets
```

**Response:**

```json
{
  "total": 5,
  "page": 1,
  "limit": 25,
  "data": [
    {
      "id": 3,
      "name": "Python Developers",
      "description": "Candidates with Python experience",
      "candidateCount": 45,
      "jobOrderCount": 3,
      "isPublic": false,
      "dateCreated": "2026-01-10T00:00:00+00:00",
      "owner": {
        "id": 1,
        "firstName": "John",
        "lastName": "Recruiter"
      }
    }
  ]
}
```

**Create Tearsheet**

```
POST /api&a=tearsheets
Content-Type: application/json

{
  "name": "AWS Specialists",
  "description": "Candidates with AWS certifications"
}
```

**Add Candidates to Tearsheet**

```
POST /api&a=tearsheets&id=3&sub=addcandidates
Content-Type: application/json

{
  "candidateIDs": [42, 55, 67, 89]
}
```

**Add Job Orders to Tearsheet**

```
POST /api&a=tearsheets&id=3&sub=addjobs
Content-Type: application/json

{
  "jobOrderIDs": [1, 5, 12]
}
```

---

### 4.11 Attachments

**List Attachments**

```
GET /api&a=attachments&entityType=candidate&entityID=42
```

**Response:**

```json
{
  "total": 3,
  "page": 1,
  "limit": 25,
  "data": [
    {
      "id": 156,
      "entityType": "candidate",
      "entityID": 42,
      "title": "Resume",
      "originalFilename": "jane_developer_resume.pdf",
      "contentType": "application/pdf",
      "fileSize": 245678,
      "isResume": true,
      "dateAdded": "2026-01-10T09:15:00+00:00"
    }
  ]
}
```

**Upload Attachment**

```
POST /api&a=attachments
Content-Type: multipart/form-data

entityType: candidate
entityID: 42
title: Updated Resume
file: @/path/to/resume.pdf
```

**Download Attachment**

```
GET /api&a=attachments&id=156&sub=download
```

---

### 4.12 Mass Update

**Bulk Update Entities**

```
POST /api&a=massupdate
Content-Type: application/json

{
  "entityType": "candidate",
  "ids": [42, 55, 67, 89, 101],
  "updates": {
    "status": "Active",
    "source": "Database Cleanup"
  }
}
```

**Response:**

```json
{
  "success": true,
  "updated": 5,
  "failed": 0,
  "results": [
    {"id": 42, "status": "updated"},
    {"id": 55, "status": "updated"},
    {"id": 67, "status": "updated"},
    {"id": 89, "status": "updated"},
    {"id": 101, "status": "updated"}
  ]
}
```

---

### 4.13 Associations

**Create Association (Link Entities)**

```
POST /api&a=associations
Content-Type: application/json

{
  "entityType": "candidate",
  "entityID": 42,
  "associatedType": "jobOrder",
  "associatedID": 1
}
```

**List Associations**

```
GET /api&a=associations&entityType=candidate&entityID=42
```

**Delete Association**

```
DELETE /api&a=associations&id=789
```

---

### 4.14 Meta (Entity Schema)

**List Available Entities**

```
GET /api&a=meta
```

**Response:**

```json
{
  "entities": [
    "Candidate",
    "ClientContact",
    "ClientCorporation",
    "JobOrder",
    "JobSubmission",
    "Placement",
    "Note",
    "Appointment",
    "Task",
    "Tearsheet"
  ]
}
```

**Get Entity Schema**

```
GET /api&a=meta&entity=Candidate
```

**Response:**

```json
{
  "entity": "Candidate",
  "label": "Candidate",
  "fields": [
    {
      "name": "id",
      "type": "integer",
      "label": "ID",
      "required": false,
      "readonly": true
    },
    {
      "name": "firstName",
      "type": "string",
      "label": "First Name",
      "required": true,
      "maxLength": 255
    },
    {
      "name": "lastName",
      "type": "string",
      "label": "Last Name",
      "required": true,
      "maxLength": 255
    },
    {
      "name": "email",
      "type": "string",
      "label": "Email",
      "required": false,
      "format": "email"
    }
  ]
}
```

---

## 5. Common Parameters

### 5.1 Pagination

All list endpoints support pagination:

| Parameter | Type | Default | Max | Description |
|-----------|------|---------|-----|-------------|
| page | int | 1 | - | Page number |
| limit | int | 25 | 100 | Items per page |

**Example:**

```
GET /api&a=candidates&page=3&limit=50
```

### 5.2 Field Selection

Request only specific fields to reduce response size:

```
GET /api&a=candidates&fields=id,firstName,lastName,email
```

**Nested fields:**

```
GET /api&a=jobsubmissions&fields=id,status,candidate.firstName,candidate.lastName
```

### 5.3 Sorting

| Parameter | Description |
|-----------|-------------|
| sort | Field name to sort by |
| order | ASC or DESC |

**Example:**

```
GET /api&a=candidates&sort=dateAdded&order=DESC
```

### 5.4 Query Syntax

The `query` parameter supports advanced filtering:

| Operator | Example | Description |
|----------|---------|-------------|
| = | `city=Austin` | Exact match |
| : | `skills:Python` | Contains (LIKE) |
| > | `salary>100000` | Greater than |
| < | `salary<150000` | Less than |
| >= | `experience>=5` | Greater or equal |
| <= | `experience<=10` | Less or equal |
| != | `status!=Closed` | Not equal |

**Multiple conditions (AND):**

```
GET /api&a=candidates&query=city=Austin,skills:Python,status=Active
```

---

## 6. Error Handling

### 6.1 HTTP Status Codes

| Code | Meaning | Description |
|------|---------|-------------|
| 200 | OK | Request successful |
| 201 | Created | Resource created successfully |
| 400 | Bad Request | Invalid request parameters |
| 401 | Unauthorized | Missing or invalid authentication |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource not found |
| 405 | Method Not Allowed | HTTP method not supported |
| 409 | Conflict | Resource already exists |
| 429 | Too Many Requests | Rate limit exceeded |
| 500 | Internal Server Error | Server error |
| 501 | Not Implemented | Feature not available |

### 6.2 Error Response Format

```json
{
  "error": true,
  "message": "Detailed error description",
  "code": 400
}
```

### 6.3 Common Errors

**Missing Required Field:**

```json
{
  "error": true,
  "message": "Missing required field: firstName",
  "code": 400
}
```

**Resource Not Found:**

```json
{
  "error": true,
  "message": "Candidate not found",
  "code": 404
}
```

**Duplicate Resource:**

```json
{
  "error": true,
  "message": "Submission already exists for this candidate and job order",
  "code": 409
}
```

---

## 7. Rate Limiting

### 7.1 Default Limits

| Limit | Default Value |
|-------|---------------|
| Per Minute | 60 requests |
| Per Hour | 1,000 requests |

### 7.2 Rate Limit Headers

Every response includes rate limit information:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1706234567
```

### 7.3 Rate Limit Exceeded

When limit is exceeded:

```
HTTP/1.1 429 Too Many Requests
Retry-After: 45
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 0
X-RateLimit-Reset: 1706234567

{
  "error": true,
  "message": "Rate limit exceeded: 60 requests per minute",
  "code": 429
}
```

### 7.4 Configuring Limits

In `config.php`:

```php
define('API_RATE_LIMIT_PER_MINUTE', 120);  // Increase for high-volume apps
define('API_RATE_LIMIT_PER_HOUR', 5000);
```

---

## 8. Webhooks

### 8.1 Overview

Webhooks notify your application when events occur in OpenCATS. Instead of polling the API, receive real-time HTTP POST notifications.

### 8.2 Supported Events

| Event Type | Entities | Description |
|------------|----------|-------------|
| create | All | New record created |
| update | All | Record updated |
| delete | All | Record deleted |
| statusChange | JobSubmission, Placement | Status changed |

### 8.3 Create Subscription

```
POST /api&a=subscriptions
Content-Type: application/json

{
  "name": "Candidate Updates",
  "entityType": "candidate",
  "eventTypes": ["create", "update"],
  "targetUrl": "https://your-app.com/webhooks/opencats",
  "secretKey": "your-webhook-secret"
}
```

**Response:**

```json
{
  "id": 12,
  "name": "Candidate Updates",
  "entityType": "candidate",
  "eventTypes": ["create", "update"],
  "targetUrl": "https://your-app.com/webhooks/opencats",
  "isActive": true,
  "dateCreated": "2026-01-25T12:00:00+00:00"
}
```

### 8.4 Webhook Payload

When an event occurs, OpenCATS sends:

```
POST https://your-app.com/webhooks/opencats
Content-Type: application/json
X-OpenCATS-Signature: sha256=abc123...
X-OpenCATS-Event: candidate.update
X-OpenCATS-Delivery: uuid-here

{
  "event": "update",
  "entityType": "candidate",
  "entityID": 42,
  "timestamp": "2026-01-25T12:30:00+00:00",
  "data": {
    "id": 42,
    "firstName": "Jane",
    "lastName": "Developer",
    "status": "Active"
  },
  "changes": {
    "status": {
      "old": "Passive",
      "new": "Active"
    }
  }
}
```

### 8.5 Verifying Signatures

Verify webhook authenticity using HMAC:

```php
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_OPENCATS_SIGNATURE'];
$secret = 'your-webhook-secret';

$expected = 'sha256=' . hash_hmac('sha256', $payload, $secret);

if (hash_equals($expected, $signature)) {
    // Webhook is authentic
} else {
    http_response_code(401);
    exit('Invalid signature');
}
```

### 8.6 Retry Policy

Failed deliveries are retried:

| Attempt | Delay |
|---------|-------|
| 1 | Immediate |
| 2 | 1 minute |
| 3 | 5 minutes |
| 4 | 30 minutes |
| 5 | 2 hours |

After 5 failures, the delivery is marked as failed.

---

## 9. OAuth 2.0

### 9.1 Overview

OAuth 2.0 allows third-party applications to access OpenCATS on behalf of users without sharing credentials.

### 9.2 Register Application

```sql
INSERT INTO oauth_clients (
  client_id, client_secret, name, redirect_uri, site_id
) VALUES (
  'your-client-id',
  'your-client-secret',
  'My Application',
  'https://your-app.com/callback',
  1
);
```

### 9.3 Authorization Flow

**Step 1: Redirect to Authorization**

```
GET /index.php?m=api&a=oauth&sub=authorize
    &client_id=your-client-id
    &redirect_uri=https://your-app.com/callback
    &response_type=code
    &state=random-state-string
```

**Step 2: User Authorizes**

User logs in and approves access. OpenCATS redirects:

```
https://your-app.com/callback?code=AUTH_CODE&state=random-state-string
```

**Step 3: Exchange Code for Token**

```
POST /index.php?m=api&a=oauth&sub=token
Content-Type: application/x-www-form-urlencoded

grant_type=authorization_code
&code=AUTH_CODE
&client_id=your-client-id
&client_secret=your-client-secret
&redirect_uri=https://your-app.com/callback
```

**Response:**

```json
{
  "access_token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "refresh_token": "dGhpcyBpcyBhIHJlZnJlc2ggdG9rZW4..."
}
```

### 9.4 Refresh Token

```
POST /index.php?m=api&a=oauth&sub=token
Content-Type: application/x-www-form-urlencoded

grant_type=refresh_token
&refresh_token=dGhpcyBpcyBhIHJlZnJlc2ggdG9rZW4...
&client_id=your-client-id
&client_secret=your-client-secret
```

### 9.5 Revoke Token

```
POST /index.php?m=api&a=oauth&sub=revoke
Content-Type: application/x-www-form-urlencoded

token=eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
&client_id=your-client-id
&client_secret=your-client-secret
```

---

## 10. Edge Cases & Best Practices

### 10.1 Handling Large Datasets

**Pagination:**

Always use pagination for list requests:

```bash
# Good - paginated request
curl "...&page=1&limit=100"

# Bad - attempting to get all records
curl "...&limit=10000"  # Will be capped at 100
```

**Incremental Sync:**

For syncing data, use timestamps:

```bash
curl "...&query=dateModified>2026-01-25T00:00:00"
```

### 10.2 Duplicate Prevention

**Job Submissions:**

The API prevents duplicate submissions:

```json
{
  "error": true,
  "message": "Submission already exists for this candidate and job order",
  "code": 409
}
```

**Check before creating:**

```bash
# Check if submission exists
curl "...&a=jobsubmissions&candidate=42&jobOrder=1"

# Then create if not found
```

### 10.3 Concurrent Updates

Use optimistic locking when updating:

```json
{
  "id": 42,
  "status": "Active",
  "dateModified": "2026-01-25T12:00:00+00:00"
}
```

If another update occurred, you'll receive a conflict error.

### 10.4 File Upload Best Practices

**Size Limits:**

- Default max: 10MB per file
- Resume uploads: PDF, DOC, DOCX recommended

**MIME Type Validation:**

Only allowed file types are accepted:
- Documents: PDF, DOC, DOCX, RTF, TXT
- Images: JPG, PNG, GIF

### 10.5 Search Performance

**Use Specific Queries:**

```bash
# Good - specific field query
curl "...&query=city=Austin,status=Active"

# Slower - broad text search
curl "...&query=skills:developer"
```

**Index-Friendly Fields:**

- status
- city/state
- dateAdded
- owner

### 10.6 Webhook Best Practices

**Respond Quickly:**

- Return 200 within 5 seconds
- Process asynchronously if needed

**Handle Duplicates:**

- Use delivery ID for deduplication
- Events may be delivered more than once

**Verify Signatures:**

- Always verify HMAC signatures
- Reject unsigned requests

### 10.7 Error Recovery

**Retry Logic:**

```javascript
async function apiRequest(url, options, maxRetries = 3) {
  for (let i = 0; i < maxRetries; i++) {
    try {
      const response = await fetch(url, options);

      if (response.status === 429) {
        // Rate limited - wait and retry
        const retryAfter = response.headers.get('Retry-After') || 60;
        await sleep(retryAfter * 1000);
        continue;
      }

      return response;
    } catch (error) {
      if (i === maxRetries - 1) throw error;
      await sleep(Math.pow(2, i) * 1000); // Exponential backoff
    }
  }
}
```

---

## 11. Migration Guide

### 11.1 From Bullhorn API

The OpenCATS API is designed for Bullhorn compatibility:

| Bullhorn Endpoint | OpenCATS Endpoint |
|-------------------|-------------------|
| /entity/Candidate | /api&a=candidates |
| /entity/ClientCorporation | /api&a=companies |
| /entity/ClientContact | /api&a=contacts |
| /entity/JobOrder | /api&a=joborders |
| /entity/JobSubmission | /api&a=jobsubmissions |
| /entity/Placement | /api&a=placements |
| /entity/Note | /api&a=notes |
| /entity/Appointment | /api&a=appointments |
| /entity/Task | /api&a=tasks |
| /entity/Tearsheet | /api&a=tearsheets |
| /meta | /api&a=meta |

**Field Mapping:**

Most fields use identical names. Key differences:

| Bullhorn | OpenCATS |
|----------|----------|
| clientCorporation | company/companyID |
| clientContact | contact/contactID |
| sendingUser | owner/addedBy |

### 11.2 Database Migration

Run migrations in order:

```bash
mysql -u user -p db < modules/install/Schema.php 001_add_api_and_tearsheets.sql
mysql -u user -p db < modules/install/Schema.php 002_oauth2_tables.sql
mysql -u user -p db < modules/install/Schema.php 003_job_submission_placement.sql
mysql -u user -p db < modules/install/Schema.php 004_extended_entities.sql
mysql -u user -p db < modules/install/Schema.php 005_tearsheet_candidates.sql
mysql -u user -p db < modules/install/Schema.php 006_webhooks.sql
```

### 11.3 API Key Migration

If migrating from another system:

```sql
-- Import API keys
INSERT INTO api_keys (site_id, user_id, api_key, api_secret, name, access_level, is_active)
SELECT 1, user_id, old_api_key, old_api_secret, key_name, 400, 1
FROM old_system_keys;
```

---

## 12. Troubleshooting

### 12.1 Authentication Issues

**Error: "Unauthorized. Provide valid API key."**

- Verify API key is correct
- Check key is active in database
- Ensure proper header format

```bash
# Debug: Check if key exists
mysql> SELECT * FROM api_keys WHERE api_key = 'your-key';
```

**Error: "Access token expired"**

- Refresh the token using refresh_token
- Request a new access token

### 12.2 Rate Limiting

**Error: "Rate limit exceeded"**

- Wait for Retry-After seconds
- Implement exponential backoff
- Request limit increase if needed

```bash
# Check current usage
mysql> SELECT COUNT(*) FROM api_request_log
       WHERE api_key_id = 1
       AND request_time > DATE_SUB(NOW(), INTERVAL 1 HOUR);
```

### 12.3 Database Errors

**Error: "Table doesn't exist"**

- Run missing migrations
- Check migration order

```bash
# Verify tables exist
mysql> SHOW TABLES LIKE 'api_%';
mysql> SHOW TABLES LIKE 'oauth_%';
mysql> SHOW TABLES LIKE 'webhook_%';
```

### 12.4 Webhook Issues

**Webhooks not received:**

1. Check subscription is active
2. Verify target URL is accessible
3. Check webhook_delivery_log for errors

```sql
SELECT * FROM webhook_delivery_log
WHERE subscription_id = 12
ORDER BY date_created DESC
LIMIT 10;
```

**Signature verification failing:**

- Ensure secret key matches
- Check for encoding issues
- Verify payload is raw (not parsed)

### 12.5 Performance Issues

**Slow API responses:**

1. Add database indexes
2. Use field selection
3. Reduce page size
4. Enable caching

```sql
-- Check for missing indexes
EXPLAIN SELECT * FROM candidate WHERE city = 'Austin';
```

### 12.6 CORS Issues

**Error: "Access-Control-Allow-Origin"**

Configure in `config.php`:

```php
define('API_CORS_ALLOWED_ORIGINS', 'https://your-app.com');
```

For multiple origins:

```php
define('API_CORS_ALLOWED_ORIGINS', 'https://app1.com,https://app2.com');
```

### 12.7 Debug Mode

Enable detailed logging:

```php
define('API_DEBUG_MODE', true);
define('API_LOG_LEVEL', 'debug');
```

Check logs:

```bash
tail -f /var/log/opencats/api.log
```

---

## Appendix A: Complete Field Reference

### Candidate Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| id | int | auto | Unique identifier |
| firstName | string | yes | First name |
| lastName | string | yes | Last name |
| email | string | no | Email address |
| email2 | string | no | Secondary email |
| phone | string | no | Primary phone |
| phoneCell | string | no | Cell phone |
| address | string | no | Street address |
| city | string | no | City |
| state | string | no | State/Province |
| zip | string | no | Postal code |
| source | string | no | Candidate source |
| status | string | no | Active, Passive, etc. |
| currentEmployer | string | no | Current company |
| currentTitle | string | no | Current job title |
| skills | text | no | Skills and keywords |
| notes | text | no | General notes |
| dateAvailable | date | no | Available start date |
| desiredPay | string | no | Desired salary |
| dateAdded | datetime | auto | Date created |
| dateModified | datetime | auto | Last modified |
| ownerID | int | auto | Owner user ID |

### Job Order Fields

| Field | Type | Required | Description |
|-------|------|----------|-------------|
| id | int | auto | Unique identifier |
| title | string | yes | Job title |
| companyID | int | yes | Company ID |
| contactID | int | no | Primary contact ID |
| type | string | no | Full-time, Contract, etc. |
| status | string | no | Active, Closed, etc. |
| city | string | no | Job location city |
| state | string | no | Job location state |
| salary | string | no | Salary range |
| description | text | no | Full job description |
| requirements | text | no | Job requirements |
| openings | int | no | Number of openings |
| startDate | date | no | Expected start date |
| duration | string | no | Contract duration |
| dateAdded | datetime | auto | Date created |
| dateModified | datetime | auto | Last modified |
| ownerID | int | auto | Owner user ID |

---

## Appendix B: Status Values

### Candidate Status

- Active
- Passive
- Do Not Contact
- Placed
- Not Qualified

### Job Order Status

- Active
- On Hold
- Closed - Filled
- Closed - Cancelled
- Draft

### Job Submission Status

- Submitted
- Reviewed
- Interview Scheduled
- Interviewed
- Offer Extended
- Offer Accepted
- Placed
- Rejected
- Withdrawn

### Placement Status

- Active
- Completed
- Terminated
- Fell Through

---

## Appendix C: Code Examples

### PHP Example

```php
<?php
class OpenCATSClient {
    private $baseUrl;
    private $apiKey;

    public function __construct($baseUrl, $apiKey) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->apiKey = $apiKey;
    }

    public function getCandidates($params = []) {
        return $this->request('GET', 'candidates', $params);
    }

    public function createCandidate($data) {
        return $this->request('POST', 'candidates', [], $data);
    }

    private function request($method, $endpoint, $params = [], $data = null) {
        $url = $this->baseUrl . '/index.php?m=api&a=' . $endpoint;

        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Api-Key: ' . $this->apiKey,
            'Content-Type: application/json'
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'PUT') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($method === 'DELETE') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        }

        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}

// Usage
$client = new OpenCATSClient('https://your-domain.com', 'your-api-key');
$candidates = $client->getCandidates(['status' => 'Active', 'limit' => 50]);
```

### JavaScript Example

```javascript
class OpenCATSClient {
  constructor(baseUrl, apiKey) {
    this.baseUrl = baseUrl.replace(/\/$/, '');
    this.apiKey = apiKey;
  }

  async getCandidates(params = {}) {
    return this.request('GET', 'candidates', params);
  }

  async createCandidate(data) {
    return this.request('POST', 'candidates', {}, data);
  }

  async request(method, endpoint, params = {}, data = null) {
    let url = `${this.baseUrl}/index.php?m=api&a=${endpoint}`;

    if (Object.keys(params).length > 0) {
      url += '&' + new URLSearchParams(params).toString();
    }

    const options = {
      method,
      headers: {
        'X-Api-Key': this.apiKey,
        'Content-Type': 'application/json'
      }
    };

    if (data && (method === 'POST' || method === 'PUT')) {
      options.body = JSON.stringify(data);
    }

    const response = await fetch(url, options);
    return response.json();
  }
}

// Usage
const client = new OpenCATSClient('https://your-domain.com', 'your-api-key');
const candidates = await client.getCandidates({ status: 'Active', limit: 50 });
```

### Python Example

```python
import requests

class OpenCATSClient:
    def __init__(self, base_url, api_key):
        self.base_url = base_url.rstrip('/')
        self.api_key = api_key
        self.headers = {
            'X-Api-Key': api_key,
            'Content-Type': 'application/json'
        }

    def get_candidates(self, **params):
        return self._request('GET', 'candidates', params=params)

    def create_candidate(self, data):
        return self._request('POST', 'candidates', json=data)

    def _request(self, method, endpoint, params=None, json=None):
        url = f"{self.base_url}/index.php"
        params = params or {}
        params['m'] = 'api'
        params['a'] = endpoint

        response = requests.request(
            method,
            url,
            params=params,
            json=json,
            headers=self.headers
        )
        return response.json()

# Usage
client = OpenCATSClient('https://your-domain.com', 'your-api-key')
candidates = client.get_candidates(status='Active', limit=50)
```

---

*Documentation generated for OpenCATS REST API v1.0.0*
*For support, visit: https://github.com/opencats/OpenCATS*
