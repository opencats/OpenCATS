# OpenCATS + External Integration Architecture

## How It All Connects

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                        YOUR LOCAL SERVER                                     │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌──────────────────────────────────────────────────────────────────────┐   │
│  │                         OPENCATS (ATS)                                │   │
│  │                     http://localhost/opencats                        │   │
│  │                                                                       │   │
│  │   ┌─────────────┐    ┌─────────────┐    ┌─────────────────────────┐ │   │
│  │   │   Web UI    │    │  REST API   │    │   Admin: API Keys       │ │   │
│  │   │ (Recruiters)│    │ /api module │    │ Settings → API Keys     │ │   │
│  │   └─────────────┘    └──────┬──────┘    └───────────┬─────────────┘ │   │
│  │                              │                       │               │   │
│  │                              │                       │               │   │
│  │   ┌──────────────────────────┴───────────────────────┴─────────────┐ │   │
│  │   │                        MariaDB                                  │ │   │
│  │   │  ┌───────────┐  ┌───────────┐  ┌───────────┐  ┌─────────────┐ │ │   │
│  │   │  │ joborder  │  │ tearsheet │  │ api_keys  │  │ candidates  │ │ │   │
│  │   │  │ (jobs)    │  │ (lists)   │  │ (sandbox) │  │             │ │ │   │
│  │   │  └───────────┘  └───────────┘  └───────────┘  └─────────────┘ │ │   │
│  │   └────────────────────────────────────────────────────────────────┘ │   │
│  │                                                                       │   │
│  └───────────────────────────────────────────────────────────────────────┘   │
│                                        │                                      │
│                                        │ REST API                             │
│                                        │ (API Key Auth)                       │
│                                        ▼                                      │
│  ┌───────────────────────────────────────────────────────────────────────┐   │
│  │                         JOBPULSE                                       │   │
│  │                     http://localhost:5000                             │   │
│  │                                                                        │   │
│  │   ┌─────────────┐    ┌─────────────┐    ┌─────────────────────────┐  │   │
│  │   │  Freshness  │    │    XML      │    │      SFTP Upload        │  │   │
│  │   │   Engine    │───▶│  Generator  │───▶│    (Job Boards)         │  │   │
│  │   │ (30 min)    │    │             │    │                         │  │   │
│  │   └─────────────┘    └─────────────┘    └───────────┬─────────────┘  │   │
│  │                                                      │                │   │
│  └──────────────────────────────────────────────────────┼────────────────┘   │
│                                                          │                    │
└──────────────────────────────────────────────────────────┼────────────────────┘
                                                           │
                                                           ▼
                                              ┌─────────────────────────┐
                                              │       JOB BOARDS        │
                                              │  Indeed, LinkedIn, etc  │
                                              │                         │
                                              │   (Fresh XML every      │
                                              │    30 minutes)          │
                                              └─────────────────────────┘
```

---

## Step-by-Step: Creating a Sandbox Account

### 1. Create API Key (via CLI)

```bash
cd /var/www/opencats
php lib/ApiKeys.php create 1 "External Integration"
```

**Output:**
```
========================================
  NEW API KEY CREATED (Sandbox Account)
========================================

  API Key:       abc123def456...
  API Secret:    xyz789ghi012...

  ⚠️  SAVE THESE CREDENTIALS NOW!
========================================
```

### 2. Configure Your Application

```env
# In your application .env file
OPENCATS_URL=http://localhost/opencats
OPENCATS_API_KEY=abc123def456...
OPENCATS_API_SECRET=xyz789ghi012...
TEARSHEET_IDS=1,2,3
```

### 3. Test the Connection

```bash
# Test API access
curl -H "X-Api-Key: abc123def456..." \
  "http://localhost/opencats/index.php?m=api&a=ping"

# Expected: {"status":"ok","version":"1.0.0"}

# Get jobs from tearsheet
curl -H "X-Api-Key: abc123def456..." \
  "http://localhost/opencats/index.php?m=api&a=tearsheets&id=1&sub=joborders"

# Expected: {"total":10,"data":[...jobs...]}
```

---

## Data Flow

```
1. ADMIN creates API key (sandbox account)
        │
        ▼
2. API KEY stored in api_keys table
        │
        ▼
3. JOBPULSE configured with API key
        │
        ▼
4. Every 30 minutes:
   ├── Application calls: GET /api/tearsheets/{id}/joborders
   │       │
   │       ▼
   ├── OpenCATS returns job data (JSON)
   │       │
   │       ▼
   ├── Application generates fresh XML
   │       │
   │       ▼
   └── Upload to Job Boards via SFTP
```

---

## Comparison: Bullhorn vs OpenCATS

| Step | Bullhorn | OpenCATS |
|------|----------|----------|
| 1. Get Sandbox | Request from Bullhorn ($12K/yr) | `php lib/ApiKeys.php create` (FREE) |
| 2. API Endpoint | `rest.bullhornstaffing.com` | `localhost/opencats/index.php?m=api` |
| 3. Auth Method | OAuth 2.0 (complex) | API Key header (simple) |
| 4. Get Tearsheet Jobs | `GET /entity/Tearsheet/{id}` | `GET ?m=api&a=tearsheets&id={id}&sub=joborders` |
| 5. Job Response | Bullhorn JSON format | Same structure (compatible) |

---

## Multiple Environments

```bash
# Create separate keys for each environment

# Development
php lib/ApiKeys.php create 1 "DEV - Local Testing"

# Staging
php lib/ApiKeys.php create 1 "STAGING - QA Environment"

# Production
php lib/ApiKeys.php create 1 "PROD - Live Integration"

# CI/CD
php lib/ApiKeys.php create 1 "CI/CD - Automated Tests"
```

---

## Security Note

```
⚠️  The API Secret is shown ONLY ONCE when created.
    If lost, use: php lib/ApiKeys.php regenerate {id}
    Or via Web UI: Settings → API Keys → "New Secret"
```
