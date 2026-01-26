# OpenCATS API Keys & Sandbox Accounts

## Overview

This guide explains how to create and manage API keys (sandbox accounts) for the OpenCATS REST API. API keys are required for any external application (like job distribution tools) to access OpenCATS data programmatically.

---

## Quick Start

### Method 1: Command Line (Fastest)

```bash
# Navigate to OpenCATS directory
cd /var/www/opencats

# Create a new API key
php lib/ApiKeys.php create 1 "API Development"
```

Output:
```
========================================
  NEW API KEY CREATED (Sandbox Account)
========================================

  API Key ID:    1
  API Key:       a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
  API Secret:    x9y8z7w6v5u4t3s2r1q0p9o8n7m6l5k4j3i2h1g0f9e8d7c6

  ⚠️  SAVE THESE CREDENTIALS NOW!
  The secret cannot be retrieved later.

========================================
```

### Method 2: Web Admin Interface

1. Log in to OpenCATS as an administrator
2. Go to **Settings** → **API Keys** 
3. Enter a description (e.g., "API Development")
4. Click **Create API Key**
5. **IMMEDIATELY** copy and save the displayed credentials

---

## CLI Reference

The `ApiKeys.php` library includes a built-in CLI tool:

```bash
# Create new API key
php lib/ApiKeys.php create [user_id] [description]

# List all API keys
php lib/ApiKeys.php list

# Deactivate an API key
php lib/ApiKeys.php deactivate [api_key_id]

# Activate an API key
php lib/ApiKeys.php activate [api_key_id]

# Delete an API key permanently
php lib/ApiKeys.php delete [api_key_id]

# Show help
php lib/ApiKeys.php help
```

### Examples:

```bash
# Create key for user ID 1 (usually admin)
php lib/ApiKeys.php create 1 "API Production"

# Create multiple sandbox accounts
php lib/ApiKeys.php create 1 "Development Environment"
php lib/ApiKeys.php create 1 "Testing Environment"
php lib/ApiKeys.php create 1 "CI/CD Pipeline"

# View all keys
php lib/ApiKeys.php list

# Deactivate compromised key
php lib/ApiKeys.php deactivate 3
```

---

## Using API Keys

### Authentication Methods

**Option 1: X-Api-Key Header (Recommended)**
```bash
curl -X GET "http://localhost/opencats/index.php?m=api&a=joborders" \
  -H "X-Api-Key: your-api-key-here"
```

**Option 2: Bearer Token Header**
```bash
curl -X GET "http://localhost/opencats/index.php?m=api&a=joborders" \
  -H "Authorization: Bearer your-api-key-here"
```

**Option 3: Query Parameter (Less Secure)**
```bash
curl "http://localhost/opencats/index.php?m=api&a=joborders&api_key=your-api-key-here"
```

### Full Authentication Flow

```bash
# Step 1: Authenticate and get token
curl -X POST "http://localhost/opencats/index.php?m=api&a=auth" \
  -H "Content-Type: application/json" \
  -d '{
    "api_key": "your-api-key",
    "api_secret": "your-api-secret"
  }'

# Response:
# {
#   "access_token": "session-token-here",
#   "token_type": "Bearer",
#   "expires_in": 3600
# }

# Step 2: Use the token for subsequent requests
curl "http://localhost/opencats/index.php?m=api&a=joborders" \
  -H "Authorization: Bearer session-token-here"
```

---

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `?m=api&a=auth` | Authenticate, get session token |
| GET | `?m=api&a=ping` | Health check (no auth required) |
| GET | `?m=api&a=joborders` | List all job orders |
| GET | `?m=api&a=joborders&id={id}` | Get single job order |
| GET | `?m=api&a=tearsheets` | List all tearsheets |
| GET | `?m=api&a=tearsheets&id={id}` | Get tearsheet details |
| GET | `?m=api&a=tearsheets&id={id}&sub=joborders` | Get jobs in tearsheet |
| GET | `?m=api&a=candidates` | List candidates |
| GET | `?m=api&a=candidates&id={id}` | Get single candidate |

---

## Integration with External Tools

### Configuration Example

In your application `.env` or configuration:

```env
# OpenCATS API Configuration
ATS_TYPE=opencats
ATS_BASE_URL=http://your-server/opencats
ATS_API_KEY=a1b2c3d4e5f6g7h8i9j0k1l2m3n4o5p6
ATS_API_SECRET=x9y8z7w6v5u4t3s2r1q0p9o8n7m6l5k4j3i2h1g0f9e8d7c6

# Tearsheet IDs to monitor (comma-separated)
TEARSHEET_IDS=1,2,3
```

### Python Example

```python
import requests

class OpenCATSClient:
    def __init__(self, base_url, api_key, api_secret=None):
        self.base_url = base_url.rstrip('/')
        self.api_key = api_key
        self.api_secret = api_secret
        self.session = requests.Session()
        self.session.headers['X-Api-Key'] = api_key
    
    def get_tearsheet_jobs(self, tearsheet_id):
        """Get all jobs from a tearsheet (like Bullhorn)"""
        url = f"{self.base_url}/index.php"
        params = {
            'm': 'api',
            'a': 'tearsheets',
            'id': tearsheet_id,
            'sub': 'joborders'
        }
        response = self.session.get(url, params=params)
        return response.json()
    
    def get_job(self, job_id):
        """Get single job order details"""
        url = f"{self.base_url}/index.php"
        params = {
            'm': 'api',
            'a': 'joborders',
            'id': job_id
        }
        response = self.session.get(url, params=params)
        return response.json()

# Usage
client = OpenCATSClient(
    base_url='http://localhost/opencats',
    api_key='your-api-key'
)

# Get jobs from tearsheet (similar to Bullhorn tearsheet)
jobs = client.get_tearsheet_jobs(tearsheet_id=1)
print(f"Found {jobs['total']} jobs")

for job in jobs['data']:
    print(f"- {job['title']} at {job['clientCorporation']['name']}")
```

---

## Security Best Practices

1. **Never commit API keys to version control**
   - Use environment variables
   - Add `.env` to `.gitignore`

2. **Use different keys for different environments**
   - Development: `"Development Environment"`
   - Staging: `"Staging Environment"`
   - Production: `"Production - Read Only"`

3. **Rotate keys periodically**
   ```bash
   # Regenerate secret for key ID 1
   # (Do this through the web UI to see the new secret)
   ```

4. **Deactivate unused keys**
   ```bash
   php lib/ApiKeys.php deactivate 5
   ```

5. **Monitor last_used timestamps**
   - Check the API Keys admin page for usage patterns
   - Investigate keys that haven't been used

---

## Troubleshooting

### "Unauthorized" Error

1. Check if API key is active:
   ```bash
   php lib/ApiKeys.php list
   ```

2. Verify the key is correct (no extra spaces/characters)

3. Check if using correct header format

### "Endpoint not found" Error

- Ensure you're using the correct URL format: `index.php?m=api&a=ACTION`
- Valid actions: `auth`, `ping`, `joborders`, `tearsheets`, `candidates`

### Database Connection Error

- Ensure OpenCATS is properly configured
- Check `config.php` database settings

---

## Database Schema

The API uses these tables (created by migration):

```sql
-- API Keys (sandbox accounts)
api_keys (
  api_key_id, site_id, user_id, 
  api_key, api_secret, description,
  is_active, created_date, last_used
)

-- Session tokens
api_sessions (
  session_id, api_key_id, session_token,
  created_date, expires_date
)
```

---

## Comparison with Bullhorn

| Feature | Bullhorn | OpenCATS (with this update) |
|---------|----------|----------------------------|
| Sandbox Cost | $12,000/year | **FREE** |
| API Keys | Via support request | Self-service (CLI or Web UI) |
| Tearsheets | Native | Added ✓ |
| REST API | Full | Basic (expandable) |
| OAuth 2.0 | Required | Simple API key (OAuth optional) |
| Job Orders | Full entity | Supported ✓ |
| Candidates | Full entity | Supported ✓ |

---

*This documentation is part of the OpenCATS REST API contribution.*
