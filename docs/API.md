# OpenCATS REST API Documentation

## Overview

The OpenCATS REST API provides programmatic access to your applicant tracking data. It's designed to be compatible with Bullhorn API patterns for easy integration with tools like JobPulse.

## Authentication

### API Keys

Create API keys via CLI:

```bash
php lib/ApiKeys.php create 1 "My Integration"
```

### Using API Keys

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

## Endpoints

### Health Check
```
GET ?m=api&a=ping
```
Response: `{"status":"ok","version":"1.0.0","timestamp":"..."}`

### Authentication
```
POST ?m=api&a=auth
Content-Type: application/json

{"api_key": "your-key", "api_secret": "your-secret"}
```

### Job Orders

**List all:** `GET ?m=api&a=joborders`

**Get single:** `GET ?m=api&a=joborders&id={id}`

Response format (Bullhorn-compatible):
```json
{
  "id": 1,
  "title": "Software Engineer",
  "status": "Active",
  "isOpen": true,
  "clientCorporation": {"id": 5, "name": "Acme Corp"},
  "address": {"city": "San Francisco", "state": "CA"}
}
```

### Tearsheets

**List all:** `GET ?m=api&a=tearsheets`

**Get single:** `GET ?m=api&a=tearsheets&id={id}`

**Get jobs in tearsheet:** `GET ?m=api&a=tearsheets&id={id}&sub=joborders`

### Candidates

**Get single:** `GET ?m=api&a=candidates&id={id}`

### Companies

**Get single:** `GET ?m=api&a=companies&id={id}`

## Error Responses

```json
{"error": true, "message": "Unauthorized", "code": 401}
```

## Integration with JobPulse

```env
ATS_TYPE=opencats
ATS_BASE_URL=http://your-server/opencats
ATS_API_KEY=your-api-key
TEARSHEET_IDS=1,2,3
```
