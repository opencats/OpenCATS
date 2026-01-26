# OpenCATS REST API Reference

## Overview

The OpenCATS REST API provides programmatic access to your applicant tracking system data. It's designed to be compatible with Bullhorn API patterns for easy integration with tools like job distribution tools.

**Base URL:** `http://your-server/opencats/index.php?m=api`

**API Version:** 1.0.0

---

## Table of Contents

1. [Authentication](#authentication)
   - [API Keys](#api-keys)
   - [OAuth 2.0](#oauth-20)
2. [Common Features](#common-features)
   - [Pagination](#pagination)
   - [Field Selection](#field-selection)
   - [Sorting](#sorting)
   - [Query Parameters](#query-parameters-jpql-like)
3. [Entities](#entities)
   - [Job Orders](#job-orders)
   - [Candidates](#candidates)
   - [Companies](#companies)
   - [Contacts](#contacts)
   - [Tearsheets](#tearsheets)
   - [JobSubmissions](#jobsubmissions)
   - [Placements](#placements)
   - [Notes](#notes)
   - [Appointments](#appointments)
   - [Tasks](#tasks)
4. [File Operations](#file-operations)
   - [Attachments](#attachments)
5. [Bulk Operations](#bulk-operations)
   - [Mass Update](#mass-update)
   - [Associations](#associations)
6. [Webhooks](#webhooks)
   - [Subscriptions](#webhook-subscriptions)
   - [Event Types](#webhook-events)
   - [Payload Format](#webhook-payload-format)
7. [Meta & Discovery](#meta--discovery)
8. [Error Responses](#error-responses)
9. [Bullhorn Compatibility](#bullhorn-compatibility)

---

## Authentication

OpenCATS supports two authentication methods: API Keys (simple) and OAuth 2.0 (standard).

### API Keys

The simplest way to authenticate. Create API keys via CLI or web admin.

**Creating an API Key:**
```bash
php lib/ApiKeys.php create 1 "My Integration"
```

**Using API Keys:**

**Option 1: X-Api-Key Header (Recommended)**
```bash
curl -H "X-Api-Key: your-api-key" \
  "http://localhost/opencats/index.php?m=api&a=joborders"
```

**Option 2: Bearer Token**
```bash
curl -H "Authorization: Bearer your-api-key" \
  "http://localhost/opencats/index.php?m=api&a=joborders"
```

**Option 3: POST Authentication**
```bash
curl -X POST "http://localhost/opencats/index.php?m=api&a=auth" \
  -H "Content-Type: application/json" \
  -d '{"api_key": "your-key", "api_secret": "your-secret"}'
```

Response:
```json
{
  "access_token": "session-token-here",
  "token_type": "Bearer",
  "expires_in": 3600
}
```

---

### OAuth 2.0

OAuth 2.0 provides industry-standard authentication with support for multiple grant types.

#### OAuth Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `?m=api&a=oauth&oauth=authorize` | GET | Authorization endpoint |
| `?m=api&a=oauth&oauth=token` | POST | Token exchange endpoint |
| `?m=api&a=oauth&oauth=revoke` | POST | Token revocation endpoint |
| `?m=api&a=oauth&oauth=clients` | POST | Client registration endpoint |

#### Authorization Code Flow

**Step 1: Authorize**
```bash
GET ?m=api&a=oauth&oauth=authorize
  &client_id=your-client-id
  &redirect_uri=https://your-app.com/callback
  &response_type=code
  &scope=read write
  &state=random-state-string
```

**Step 2: Exchange Code for Token**
```bash
curl -X POST "http://localhost/opencats/index.php?m=api&a=oauth&oauth=token" \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "authorization_code",
    "code": "authorization-code-here",
    "client_id": "your-client-id",
    "client_secret": "your-client-secret",
    "redirect_uri": "https://your-app.com/callback"
  }'
```

Response:
```json
{
  "access_token": "eyJ0eXAiOiJKV1...",
  "token_type": "Bearer",
  "expires_in": 3600,
  "refresh_token": "dGhpcyBpcyBh..."
}
```

#### Client Credentials Flow

For server-to-server authentication without user context:

```bash
curl -X POST "http://localhost/opencats/index.php?m=api&a=oauth&oauth=token" \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "client_credentials",
    "client_id": "your-client-id",
    "client_secret": "your-client-secret",
    "scope": "read write"
  }'
```

Alternative using Basic Auth:
```bash
curl -X POST "http://localhost/opencats/index.php?m=api&a=oauth&oauth=token" \
  -H "Authorization: Basic base64(client_id:client_secret)" \
  -H "Content-Type: application/x-www-form-urlencoded" \
  -d "grant_type=client_credentials&scope=read"
```

#### Refresh Token

```bash
curl -X POST "http://localhost/opencats/index.php?m=api&a=oauth&oauth=token" \
  -H "Content-Type: application/json" \
  -d '{
    "grant_type": "refresh_token",
    "refresh_token": "your-refresh-token",
    "client_id": "your-client-id",
    "client_secret": "your-client-secret"
  }'
```

#### Token Revocation

```bash
curl -X POST "http://localhost/opencats/index.php?m=api&a=oauth&oauth=revoke" \
  -H "Content-Type: application/json" \
  -d '{
    "token": "token-to-revoke",
    "token_type_hint": "access_token",
    "client_id": "your-client-id",
    "client_secret": "your-client-secret"
  }'
```

#### Client Registration

```bash
curl -X POST "http://localhost/opencats/index.php?m=api&a=oauth&oauth=clients" \
  -H "Content-Type: application/json" \
  -d '{
    "client_name": "My Application",
    "redirect_uri": "https://my-app.com/callback",
    "is_confidential": true
  }'
```

Response:
```json
{
  "client_id": "abc123...",
  "client_secret": "xyz789...",
  "client_name": "My Application",
  "redirect_uri": "https://my-app.com/callback",
  "is_confidential": true,
  "created_at": "2026-01-25T10:30:00+00:00",
  "message": "OAuth client created successfully. Store the client_secret securely - it cannot be retrieved again."
}
```

#### Available Scopes

| Scope | Description |
|-------|-------------|
| `read` | Read access to all entities |
| `write` | Write access to all entities |
| `admin` | Administrative access |

---

## Common Features

### Pagination

All list endpoints support pagination:

| Parameter | Default | Max | Description |
|-----------|---------|-----|-------------|
| `page` | 1 | - | Page number (1-indexed) |
| `limit` | 25 | 100 | Items per page |

**Example:**
```bash
GET ?m=api&a=joborders&page=2&limit=50
```

**Response:**
```json
{
  "total": 150,
  "page": 2,
  "limit": 50,
  "data": [...]
}
```

### Field Selection

Request only specific fields using the `fields` parameter:

```bash
GET ?m=api&a=joborders&fields=id,title,status
```

**Nested fields:**
```bash
GET ?m=api&a=joborders&fields=id,title,clientCorporation.name
```

**Response:**
```json
{
  "total": 10,
  "data": [
    {
      "id": 1,
      "title": "Software Engineer",
      "clientCorporation": {
        "name": "Acme Corp"
      }
    }
  ]
}
```

### Sorting

Sort results using `sort` and `order` parameters:

| Parameter | Default | Description |
|-----------|---------|-------------|
| `sort` | `date_created` | Field to sort by |
| `order` | `DESC` | Sort order (ASC or DESC) |

**Example:**
```bash
GET ?m=api&a=joborders&sort=dateAdded&order=DESC
```

### Query Parameters (JPQL-like)

Filter results using the `query` parameter with a JPQL-like syntax:

**Operators:**
| Operator | Description | Example |
|----------|-------------|---------|
| `=` | Equals | `status=Active` |
| `>` | Greater than | `salary>50000` |
| `<` | Less than | `salary<100000` |
| `>=` | Greater or equal | `openings>=2` |
| `<=` | Less or equal | `openings<=5` |
| `!=` | Not equal | `status!=Closed` |
| `:` | Contains (LIKE) | `title:Engineer` |

**Multiple conditions (AND):**
```bash
GET ?m=api&a=joborders&query=status=Active,city=Austin,salary>50000
```

**Examples:**
```bash
# Find active jobs with "Engineer" in title
GET ?m=api&a=joborders&query=status=Active,title:Engineer

# Find candidates in Texas
GET ?m=api&a=candidates&query=state=TX,isActive=1

# Find placements starting after a date
GET ?m=api&a=placements&query=startDate>2026-01-01
```

---

## Entities

### Job Orders

Manage job postings and requisitions.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `?m=api&a=joborders` | List all job orders |
| GET | `?m=api&a=joborders&id={id}` | Get single job order |
| POST | `?m=api&a=joborders` | Create job order |
| PUT | `?m=api&a=joborders&id={id}` | Update job order |
| DELETE | `?m=api&a=joborders&id={id}` | Delete job order |

**Response Format (Bullhorn-compatible):**
```json
{
  "id": 1,
  "title": "Software Engineer",
  "description": "We are looking for...",
  "publicDescription": "Public job description",
  "status": "Active",
  "isOpen": true,
  "isPublic": true,
  "dateAdded": "2026-01-15 10:30:00",
  "dateLastModified": "2026-01-20 14:00:00",
  "address": {
    "city": "San Francisco",
    "state": "CA",
    "zip": "94102",
    "country": "USA"
  },
  "salary": "120000",
  "type": "Full-Time",
  "duration": "Permanent",
  "clientCorporation": {
    "id": 5,
    "name": "Acme Corp"
  },
  "owner": {
    "id": 1,
    "firstName": "John",
    "lastName": "Recruiter"
  },
  "openings": 2,
  "startDate": "2026-02-01"
}
```

---

### Candidates

Manage candidate/applicant records.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `?m=api&a=candidates` | List candidates |
| GET | `?m=api&a=candidates&id={id}` | Get single candidate |
| POST | `?m=api&a=candidates` | Create candidate |
| PUT | `?m=api&a=candidates&id={id}` | Update candidate |
| DELETE | `?m=api&a=candidates&id={id}` | Delete candidate |

**Response Format:**
```json
{
  "id": 1,
  "firstName": "Jane",
  "lastName": "Doe",
  "email": "jane.doe@email.com",
  "phone": "555-1234",
  "address": {
    "city": "Austin",
    "state": "TX",
    "zip": "78701"
  },
  "status": "Active",
  "source": "LinkedIn",
  "keySkills": "Python, JavaScript, AWS",
  "currentEmployer": "Tech Corp",
  "dateAdded": "2026-01-10 09:00:00"
}
```

---

### Companies

Manage client company records.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `?m=api&a=companies` | List companies |
| GET | `?m=api&a=companies&id={id}` | Get single company |
| POST | `?m=api&a=companies` | Create company |
| PUT | `?m=api&a=companies&id={id}` | Update company |
| DELETE | `?m=api&a=companies&id={id}` | Delete company |

**Response Format:**
```json
{
  "id": 5,
  "name": "Acme Corporation",
  "address": {
    "address1": "123 Main St",
    "city": "San Francisco",
    "state": "CA",
    "zip": "94102"
  },
  "phone": "555-5000",
  "fax": "555-5001",
  "url": "https://acme.com",
  "isHot": true,
  "dateAdded": "2026-01-05 08:00:00"
}
```

---

### Contacts

Manage contacts at client companies.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `?m=api&a=contacts` | List contacts |
| GET | `?m=api&a=contacts&id={id}` | Get single contact |
| POST | `?m=api&a=contacts` | Create contact |
| PUT | `?m=api&a=contacts&id={id}` | Update contact |
| DELETE | `?m=api&a=contacts&id={id}` | Delete contact |

**Response Format:**
```json
{
  "id": 10,
  "firstName": "Bob",
  "lastName": "Manager",
  "title": "Hiring Manager",
  "email": "bob@acme.com",
  "phone": "555-5010",
  "clientCorporation": {
    "id": 5,
    "name": "Acme Corporation"
  },
  "isHot": false,
  "dateAdded": "2026-01-06 10:00:00"
}
```

---

### Tearsheets

Manage saved job lists (Bullhorn Tearsheet equivalent).

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `?m=api&a=tearsheets` | List tearsheets |
| GET | `?m=api&a=tearsheets&id={id}` | Get single tearsheet |
| GET | `?m=api&a=tearsheets&id={id}&sub=joborders` | Get jobs in tearsheet |
| GET | `?m=api&a=tearsheets&id={id}&sub=candidates` | Get candidates in tearsheet |
| POST | `?m=api&a=tearsheets` | Create tearsheet |
| PUT | `?m=api&a=tearsheets&id={id}` | Update tearsheet |
| DELETE | `?m=api&a=tearsheets&id={id}` | Delete tearsheet |

**Response Format:**
```json
{
  "id": 1,
  "name": "Hot Jobs Q1 2026",
  "description": "Priority jobs for Q1",
  "isPublic": true,
  "dateCreated": "2026-01-01 08:00:00",
  "jobOrders": {
    "total": 15
  },
  "owner": {
    "id": 1
  }
}
```

**Get Jobs in Tearsheet:**
```bash
GET ?m=api&a=tearsheets&id=1&sub=joborders
```

---

### JobSubmissions

Track candidate submissions to job orders (pipeline management).

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `?m=api&a=jobsubmissions` | List submissions |
| GET | `?m=api&a=jobsubmissions&id={id}` | Get single submission |
| POST | `?m=api&a=jobsubmissions` | Create submission |
| PUT | `?m=api&a=jobsubmissions&id={id}` | Update submission |
| DELETE | `?m=api&a=jobsubmissions&id={id}` | Delete submission |

**Query Parameters:**
| Parameter | Description |
|-----------|-------------|
| `status` | Filter by status |
| `jobOrder` | Filter by job order ID |
| `candidate` | Filter by candidate ID |

**Create Submission:**
```bash
curl -X POST "http://localhost/opencats/index.php?m=api&a=jobsubmissions" \
  -H "X-Api-Key: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "candidateID": 1,
    "jobOrderID": 5,
    "status": "Submitted",
    "source": "LinkedIn"
  }'
```

**Response Format:**
```json
{
  "id": 100,
  "candidate": {
    "id": 1,
    "firstName": "Jane",
    "lastName": "Doe",
    "email": "jane.doe@email.com"
  },
  "jobOrder": {
    "id": 5,
    "title": "Software Engineer"
  },
  "clientCorporation": {
    "id": 3,
    "name": "Acme Corp"
  },
  "status": "Submitted",
  "source": "LinkedIn",
  "dateSubmitted": "2026-01-20 14:30:00",
  "dateInterview": null,
  "dateOffer": null,
  "dateAdded": "2026-01-20 14:30:00",
  "sendingUser": {
    "id": 1,
    "firstName": "John",
    "lastName": "Recruiter"
  }
}
```

**Status Values:**
- `Submitted` - Initial submission
- `Interview` - Interview scheduled
- `Offered` - Offer extended
- `Placed` - Candidate placed
- `Rejected` - Submission rejected

---

### Placements

Track hired candidates with salary, fees, and billing information.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `?m=api&a=placements` | List placements |
| GET | `?m=api&a=placements&id={id}` | Get single placement |
| POST | `?m=api&a=placements` | Create placement |
| PUT | `?m=api&a=placements&id={id}` | Update placement |
| DELETE | `?m=api&a=placements&id={id}` | Delete placement |

**Query Parameters:**
| Parameter | Description |
|-----------|-------------|
| `status` | Filter by status |
| `candidate` | Filter by candidate ID |
| `clientCorporation` | Filter by company ID |

**Create Placement:**
```bash
curl -X POST "http://localhost/opencats/index.php?m=api&a=placements" \
  -H "X-Api-Key: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "candidateID": 1,
    "jobOrderID": 5,
    "clientCorporationID": 3,
    "startDate": "2026-02-01",
    "salary": 120000,
    "salaryType": "Yearly",
    "fee": 15,
    "feeType": "Percentage",
    "status": "Active"
  }'
```

**Response Format:**
```json
{
  "id": 50,
  "candidate": {
    "id": 1,
    "firstName": "Jane",
    "lastName": "Doe",
    "email": "jane.doe@email.com"
  },
  "jobOrder": {
    "id": 5,
    "title": "Software Engineer"
  },
  "clientCorporation": {
    "id": 3,
    "name": "Acme Corp"
  },
  "clientContact": {
    "id": 10,
    "firstName": "Bob",
    "lastName": "Manager"
  },
  "status": "Active",
  "startDate": "2026-02-01",
  "endDate": null,
  "salary": 120000.00,
  "salaryType": "Yearly",
  "fee": 15.00,
  "feeType": "Percentage",
  "billRate": null,
  "payRate": null,
  "referralFee": null,
  "notes": "",
  "owner": {
    "id": 1,
    "firstName": "John",
    "lastName": "Recruiter"
  },
  "dateAdded": "2026-01-25 10:00:00",
  "dateLastModified": "2026-01-25 10:00:00"
}
```

**Status Values:**
- `Active` - Active placement
- `Terminated` - Employment ended
- `Pending` - Pending start

---

### Notes

Manage activity notes attached to entities.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `?m=api&a=notes` | List notes |
| GET | `?m=api&a=notes&id={id}` | Get single note |
| POST | `?m=api&a=notes` | Create note |
| PUT | `?m=api&a=notes&id={id}` | Update note |
| DELETE | `?m=api&a=notes&id={id}` | Delete note |

**Query Parameters:**
| Parameter | Description |
|-----------|-------------|
| `personType` | Entity type (candidate, contact) |
| `personId` | Entity ID |
| `jobOrderId` | Associated job order ID |

**Create Note:**
```bash
curl -X POST "http://localhost/opencats/index.php?m=api&a=notes" \
  -H "X-Api-Key: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "personType": "candidate",
    "personId": 1,
    "action": "Phone Screen",
    "comments": "Discussed background and interests. Strong candidate."
  }'
```

**Response Format:**
```json
{
  "id": 200,
  "action": "Phone Screen",
  "comments": "Discussed background and interests. Strong candidate.",
  "personType": "candidate",
  "personId": 1,
  "jobOrder": {
    "id": 5,
    "title": "Software Engineer"
  },
  "enteredBy": {
    "id": 1,
    "firstName": "John",
    "lastName": "Recruiter"
  },
  "dateAdded": "2026-01-25 11:00:00"
}
```

---

### Appointments

Manage calendar appointments and interviews.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `?m=api&a=appointments` | List appointments |
| GET | `?m=api&a=appointments&id={id}` | Get single appointment |
| POST | `?m=api&a=appointments` | Create appointment |
| PUT | `?m=api&a=appointments&id={id}` | Update appointment |
| DELETE | `?m=api&a=appointments&id={id}` | Delete appointment |

**Query Parameters:**
| Parameter | Description |
|-----------|-------------|
| `type` | Filter by appointment type |
| `startDate` | Filter by start date (YYYY-MM-DD) |
| `endDate` | Filter by end date (YYYY-MM-DD) |

**Create Appointment:**
```bash
curl -X POST "http://localhost/opencats/index.php?m=api&a=appointments" \
  -H "X-Api-Key: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Interview: Jane Doe - Software Engineer",
    "description": "Technical interview round 1",
    "startDate": "2026-01-30 10:00:00",
    "endDate": "2026-01-30 11:00:00",
    "type": "Interview",
    "isPublic": false,
    "reminderEnabled": true,
    "reminderTime": 30
  }'
```

**Response Format:**
```json
{
  "id": 75,
  "title": "Interview: Jane Doe - Software Engineer",
  "description": "Technical interview round 1",
  "startDate": "2026-01-30 10:00:00",
  "endDate": "2026-01-30 11:00:00",
  "allDay": false,
  "type": "Interview",
  "isPublic": false,
  "reminderEnabled": true,
  "reminderTime": 30,
  "owner": {
    "id": 1,
    "firstName": "John",
    "lastName": "Recruiter"
  },
  "dateAdded": "2026-01-25 09:00:00"
}
```

---

### Tasks

Manage to-do items and follow-ups.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `?m=api&a=tasks` | List tasks |
| GET | `?m=api&a=tasks&id={id}` | Get single task |
| POST | `?m=api&a=tasks` | Create task |
| PUT | `?m=api&a=tasks&id={id}` | Update task |
| DELETE | `?m=api&a=tasks&id={id}` | Delete task |

**Query Parameters:**
| Parameter | Description |
|-----------|-------------|
| `status` | Filter by status |
| `priority` | Filter by priority |
| `assignedTo` | Filter by assigned user ID |
| `completed` | Filter by completion status (0/1) |

**Create Task:**
```bash
curl -X POST "http://localhost/opencats/index.php?m=api&a=tasks" \
  -H "X-Api-Key: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "description": "Follow up with Jane Doe about offer",
    "priority": "High",
    "dueDate": "2026-01-28",
    "assignedTo": 1
  }'
```

**Response Format:**
```json
{
  "id": 150,
  "description": "Follow up with Jane Doe about offer",
  "priority": "High",
  "dueDate": "2026-01-28",
  "status": "Open",
  "completed": false,
  "assignedTo": {
    "id": 1,
    "firstName": "John",
    "lastName": "Recruiter"
  },
  "owner": {
    "id": 1,
    "firstName": "John",
    "lastName": "Recruiter"
  },
  "dateAdded": "2026-01-25 12:00:00"
}
```

---

## File Operations

### Attachments

Upload, download, and manage file attachments.

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `?m=api&a=attachments&dataItemType={type}&dataItemID={id}` | List attachments |
| GET | `?m=api&a=attachments&id={id}` | Get attachment metadata |
| GET | `?m=api&a=attachments&id={id}&download=1` | Download attachment file |
| POST | `?m=api&a=attachments` | Upload attachment |
| DELETE | `?m=api&a=attachments&id={id}` | Delete attachment |

**Data Item Types:**
| Type | Code | Description |
|------|------|-------------|
| `candidate` | 100 | Candidate records |
| `company` | 200 | Company records |
| `contact` | 300 | Contact records |
| `joborder` | 400 | Job order records |
| `placement` | 1000 | Placement records |
| `jobsubmission` | 1100 | JobSubmission records |

**Upload Attachment:**
```bash
curl -X POST "http://localhost/opencats/index.php?m=api&a=attachments" \
  -H "X-Api-Key: your-api-key" \
  -F "file=@resume.pdf" \
  -F "dataItemType=candidate" \
  -F "dataItemID=1" \
  -F "title=Resume" \
  -F "isResume=true"
```

**Download Attachment:**
```bash
curl -H "X-Api-Key: your-api-key" \
  "http://localhost/opencats/index.php?m=api&a=attachments&id=25&download=1" \
  -o downloaded_file.pdf
```

**Response Format (Metadata):**
```json
{
  "id": 25,
  "title": "Resume",
  "originalFilename": "jane_doe_resume.pdf",
  "contentType": "application/pdf",
  "fileSize": 245760,
  "fileSizeKB": 240,
  "dataItemType": 100,
  "dataItemTypeName": "Candidate",
  "dataItemId": 1,
  "isResume": true,
  "isProfileImage": false,
  "md5sum": "abc123...",
  "dateCreated": "2026-01-10 09:30:00",
  "downloadUrl": "/api/v1/attachments?id=25&download=1"
}
```

**Supported MIME Types:**
- Documents: PDF, DOC, DOCX, XLS, XLSX, PPT, PPTX, RTF, TXT, HTML, CSV
- Images: JPEG, PNG, GIF, BMP, WebP
- Archives: ZIP, RAR, 7Z

**Max File Size:** 10 MB

---

## Bulk Operations

### Mass Update

Update multiple records of the same entity type in a single request.

**Endpoint:** `POST ?m=api&a=massupdate`

**Request Body:**
```json
{
  "entityType": "joborder",
  "ids": [1, 2, 3, 4, 5],
  "updates": {
    "status": "Closed",
    "isHot": false
  }
}
```

**Response:**
```json
{
  "entityType": "joborder",
  "requested": 5,
  "success": 4,
  "failed": 0,
  "skipped": 1,
  "errors": [],
  "fieldsUpdated": ["status", "is_hot"]
}
```

**Supported Entity Types and Fields:**

| Entity | Allowed Fields |
|--------|----------------|
| `joborder` | status, title, description, notes, city, state, salary, duration, type, is_hot, public, openings, rate_max, recruiter, owner |
| `candidate` | is_active, first_name, last_name, email1, phone_home, phone_cell, address, city, state, zip, source, key_skills, notes, owner |
| `company` | name, address, city, state, zip, phone1, phone2, url, key_technologies, is_hot, notes, owner |
| `contact` | first_name, last_name, title, email1, phone_work, phone_cell, address, city, state, zip, is_hot, notes, owner |
| `jobsubmission` | status, rating_value, source, send_to_client |
| `placement` | start_date, salary, bonus, fee_percent, referral_fee, status, comments |
| `task` | description, priority, due_date, status, completed, assigned_to |
| `note` | action, comments, person_type, person_id, joborder_id |
| `appointment` | title, description, start_date, end_date, all_day, is_public, type |
| `tearsheet` | name, description, is_public |

**Batch Limit:** Maximum 100 records per request

---

### Associations

Manage entity-to-entity relationships (many-to-many).

**Endpoint:** `?m=api&a=associations`

**Required Parameters:**
| Parameter | Description |
|-----------|-------------|
| `parentType` | Parent entity type |
| `parentId` | Parent entity ID |
| `childType` | Child entity type |

**Supported Associations:**

| Parent Type | Child Types |
|-------------|-------------|
| `tearsheet` | joborder, candidate |
| `joborder` | candidate, contact |
| `company` | contact, joborder |
| `candidate` | joborder, attachment |

**Get Associations:**
```bash
GET ?m=api&a=associations&parentType=tearsheet&parentId=1&childType=joborder
```

**Add Associations:**
```bash
curl -X PUT "http://localhost/opencats/index.php?m=api&a=associations&parentType=tearsheet&parentId=1&childType=joborder" \
  -H "X-Api-Key: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{"ids": [5, 10, 15]}'
```

**Response:**
```json
{
  "parentType": "tearsheet",
  "parentId": 1,
  "childType": "joborder",
  "requested": 3,
  "added": 2,
  "skipped": 1,
  "failed": 0,
  "errors": []
}
```

**Remove Associations:**
```bash
curl -X DELETE "http://localhost/opencats/index.php?m=api&a=associations&parentType=tearsheet&parentId=1&childType=joborder" \
  -H "X-Api-Key: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{"ids": [5, 10]}'
```

---

## Webhooks

Receive real-time notifications when entities are created, updated, or deleted.

### Webhook Subscriptions

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `?m=api&a=subscriptions` | List subscriptions |
| GET | `?m=api&a=subscriptions&id={id}` | Get single subscription |
| POST | `?m=api&a=subscriptions` | Create subscription |
| PUT | `?m=api&a=subscriptions&id={id}` | Update subscription |
| DELETE | `?m=api&a=subscriptions&id={id}` | Delete subscription |
| GET | `?m=api&a=subscriptions&id={id}&action=test` | Send test webhook |
| GET | `?m=api&a=subscriptions&id={id}&action=logs` | Get delivery logs |

**Create Subscription:**
```bash
curl -X POST "http://localhost/opencats/index.php?m=api&a=subscriptions" \
  -H "X-Api-Key: your-api-key" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Job Order Changes",
    "entityType": "joborder",
    "eventTypes": ["create", "update", "delete"],
    "callbackUrl": "https://my-app.com/webhooks/opencats",
    "secret": "my-webhook-secret"
  }'
```

**Response:**
```json
{
  "id": 10,
  "name": "Job Order Changes",
  "entityType": "joborder",
  "eventTypes": ["create", "update", "delete"],
  "callbackUrl": "https://my-app.com/webhooks/opencats",
  "isActive": true,
  "dateAdded": "2026-01-25 10:00:00",
  "dateLastModified": "2026-01-25 10:00:00",
  "createdBy": {
    "id": 1
  }
}
```

### Webhook Events

**Entity Types:**
- `candidate` - Candidate records
- `joborder` - Job order records
- `company` - Company records
- `contact` - Contact records
- `placement` - Placement records
- `jobsubmission` - JobSubmission records
- `note` - Note records
- `appointment` - Appointment records
- `task` - Task records
- `tearsheet` - Tearsheet records

**Event Types:**
- `create` - Entity created
- `update` - Entity updated
- `delete` - Entity deleted

### Webhook Payload Format

When an event occurs, OpenCATS sends a POST request to your callback URL:

```json
{
  "event": "update",
  "entityType": "joborder",
  "entityId": 5,
  "timestamp": "2026-01-25T15:30:00Z",
  "subscriptionId": 10,
  "data": {
    "id": 5,
    "title": "Senior Software Engineer",
    "status": "Active",
    ...
  }
}
```

**Request Headers:**
| Header | Description |
|--------|-------------|
| `Content-Type` | `application/json` |
| `User-Agent` | `OpenCATS-Webhook/1.0` |
| `X-OpenCATS-Event` | Event type (create, update, delete) |
| `X-OpenCATS-Entity` | Entity type |
| `X-OpenCATS-Signature` | HMAC signature (if secret configured) |

### HMAC Signature Verification

If you configured a `secret` when creating the subscription, OpenCATS signs the payload:

```
X-OpenCATS-Signature: sha256=<hmac-sha256-hex>
```

**Verification Example (PHP):**
```php
$payload = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_OPENCATS_SIGNATURE'];
$expected = 'sha256=' . hash_hmac('sha256', $payload, $yourSecret);

if (hash_equals($expected, $signature)) {
    // Signature valid
}
```

**Verification Example (Node.js):**
```javascript
const crypto = require('crypto');

function verifySignature(payload, signature, secret) {
  const expected = 'sha256=' + crypto
    .createHmac('sha256', secret)
    .update(payload)
    .digest('hex');
  return crypto.timingSafeEqual(
    Buffer.from(expected),
    Buffer.from(signature)
  );
}
```

### Test Webhook

Send a test payload to verify your endpoint:

```bash
GET ?m=api&a=subscriptions&id=10&action=test
```

**Test Payload:**
```json
{
  "test": true,
  "subscriptionId": 10,
  "subscriptionName": "Job Order Changes",
  "entityType": "joborder",
  "eventTypes": ["create", "update", "delete"],
  "timestamp": "2026-01-25T15:30:00Z",
  "message": "This is a test webhook from OpenCATS"
}
```

---

## Meta & Discovery

### API Meta Endpoint

Get information about available entities and their schemas.

```bash
GET ?m=api&a=meta
```

**Response:**
```json
{
  "version": "1.0.0",
  "entities": {
    "joborder": {
      "endpoint": "?m=api&a=joborders",
      "methods": ["GET", "POST", "PUT", "DELETE"],
      "searchableFields": ["status", "title", "city", "state", "salary", "date_created"]
    },
    "candidate": {
      "endpoint": "?m=api&a=candidates",
      "methods": ["GET", "POST", "PUT", "DELETE"],
      "searchableFields": ["first_name", "last_name", "email1", "city", "state", "is_active"]
    },
    ...
  },
  "features": {
    "oauth": true,
    "webhooks": true,
    "attachments": true,
    "massUpdate": true
  }
}
```

### Health Check

```bash
GET ?m=api&a=ping
```

**Response:**
```json
{
  "status": "ok",
  "version": "1.0.0",
  "timestamp": "2026-01-25T15:30:00Z"
}
```

---

## Error Responses

All errors follow a consistent format:

```json
{
  "error": true,
  "message": "Error description",
  "code": 400
}
```

**HTTP Status Codes:**

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request - Invalid parameters |
| 401 | Unauthorized - Authentication required |
| 403 | Forbidden - Insufficient permissions |
| 404 | Not Found - Resource doesn't exist |
| 405 | Method Not Allowed |
| 409 | Conflict - Resource already exists |
| 500 | Internal Server Error |
| 501 | Not Implemented - Feature not available |
| 503 | Service Unavailable |

**OAuth 2.0 Errors:**
```json
{
  "error": "invalid_request",
  "error_description": "Missing required parameter: grant_type"
}
```

---

## Bullhorn Compatibility

OpenCATS API is designed to be compatible with Bullhorn REST API patterns:

| Feature | Bullhorn | OpenCATS |
|---------|----------|----------|
| Sandbox Cost | $12,000/year | **FREE** |
| Authentication | OAuth 2.0 required | API Key or OAuth 2.0 |
| Tearsheets | Native | Supported |
| REST API | Full | Full (Bullhorn-compatible) |
| Job Orders | Full entity | Supported |
| Candidates | Full entity | Supported |
| JobSubmissions | Full entity | Supported |
| Placements | Full entity | Supported |
| Webhooks | Supported | Supported |
| Attachments | Supported | Supported |
| Mass Update | Supported | Supported |

**Response Format Compatibility:**

OpenCATS uses the same field names and nested object structure as Bullhorn:

```json
{
  "id": 1,
  "title": "Software Engineer",
  "clientCorporation": {
    "id": 5,
    "name": "Acme Corp"
  }
}
```

---

## Integration Examples

### external applications Configuration

```env
ATS_TYPE=opencats
ATS_BASE_URL=http://your-server/opencats
ATS_API_KEY=your-api-key
TEARSHEET_IDS=1,2,3
```

### Python Example

```python
import requests

class OpenCATSClient:
    def __init__(self, base_url, api_key):
        self.base_url = base_url.rstrip('/')
        self.session = requests.Session()
        self.session.headers['X-Api-Key'] = api_key

    def get_tearsheet_jobs(self, tearsheet_id):
        url = f"{self.base_url}/index.php"
        params = {'m': 'api', 'a': 'tearsheets', 'id': tearsheet_id, 'sub': 'joborders'}
        return self.session.get(url, params=params).json()

    def create_submission(self, candidate_id, job_order_id):
        url = f"{self.base_url}/index.php"
        params = {'m': 'api', 'a': 'jobsubmissions'}
        data = {'candidateID': candidate_id, 'jobOrderID': job_order_id, 'status': 'Submitted'}
        return self.session.post(url, params=params, json=data).json()

# Usage
client = OpenCATSClient('http://localhost/opencats', 'your-api-key')
jobs = client.get_tearsheet_jobs(1)
print(f"Found {jobs['total']} jobs")
```

---

*This documentation is part of the OpenCATS REST API extension for Bullhorn API parity.*
