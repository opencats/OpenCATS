# OpenCATS REST API - Quick Start Guide

Get up and running with the OpenCATS REST API in 5 minutes.

---

## Step 1: Run Database Migrations

```bash
cd /path/to/opencats

# Run all migrations in order
mysql -u YOUR_USER -p YOUR_DATABASE < db/migrations/001_add_api_and_tearsheets.sql
mysql -u YOUR_USER -p YOUR_DATABASE < db/migrations/002_oauth2_tables.sql
mysql -u YOUR_USER -p YOUR_DATABASE < db/migrations/003_job_submission_placement.sql
mysql -u YOUR_USER -p YOUR_DATABASE < db/migrations/004_extended_entities.sql
mysql -u YOUR_USER -p YOUR_DATABASE < db/migrations/005_tearsheet_candidates.sql
mysql -u YOUR_USER -p YOUR_DATABASE < db/migrations/006_webhooks.sql
```

---

## Step 2: Create an API Key

```sql
-- Connect to your database
mysql -u YOUR_USER -p YOUR_DATABASE

-- Create an API key (replace values as needed)
INSERT INTO api_keys (
    site_id,
    user_id,
    api_key,
    api_secret,
    name,
    access_level,
    is_active,
    date_created
) VALUES (
    1,                          -- site_id (use 1 for single-site)
    1,                          -- user_id (admin user)
    'my-api-key-12345',         -- your API key
    'my-secret-67890',          -- your API secret
    'My Integration',           -- descriptive name
    500,                        -- access level (500 = full admin)
    1,                          -- is_active
    NOW()                       -- date_created
);
```

---

## Step 3: Test the API

### Health Check (No Auth Required)

```bash
curl "http://YOUR_DOMAIN/index.php?m=api&a=ping"
```

**Expected Response:**
```json
{
    "status": "ok",
    "version": "1.0.0",
    "timestamp": "2026-01-25T12:00:00+00:00"
}
```

### Authenticated Request

```bash
curl -H "X-Api-Key: my-api-key-12345" \
     "http://YOUR_DOMAIN/index.php?m=api&a=candidates"
```

**Expected Response:**
```json
{
    "total": 150,
    "page": 1,
    "limit": 25,
    "data": [
        {
            "id": 1,
            "firstName": "John",
            "lastName": "Doe",
            "email": "john@example.com",
            ...
        }
    ]
}
```

---

## Step 4: Basic Operations

### List Job Orders

```bash
curl -H "X-Api-Key: my-api-key-12345" \
     "http://YOUR_DOMAIN/index.php?m=api&a=joborders"
```

### Create a Candidate

```bash
curl -X POST \
     -H "X-Api-Key: my-api-key-12345" \
     -H "Content-Type: application/json" \
     -d '{
         "firstName": "Jane",
         "lastName": "Smith",
         "email": "jane@example.com",
         "phone": "555-123-4567",
         "city": "Austin",
         "state": "TX"
     }' \
     "http://YOUR_DOMAIN/index.php?m=api&a=candidates"
```

### Update a Candidate

```bash
curl -X PUT \
     -H "X-Api-Key: my-api-key-12345" \
     -H "Content-Type: application/json" \
     -d '{
         "status": "Active",
         "currentEmployer": "New Company Inc"
     }' \
     "http://YOUR_DOMAIN/index.php?m=api&a=candidates&id=42"
```

### Delete a Candidate

```bash
curl -X DELETE \
     -H "X-Api-Key: my-api-key-12345" \
     "http://YOUR_DOMAIN/index.php?m=api&a=candidates&id=42"
```

---

## Available Endpoints

| Endpoint | Description |
|----------|-------------|
| `a=ping` | Health check |
| `a=auth` | Authenticate with key/secret |
| `a=candidates` | Manage candidates |
| `a=joborders` | Manage job orders |
| `a=companies` | Manage companies |
| `a=contacts` | Manage contacts |
| `a=jobsubmissions` | Manage pipeline/submissions |
| `a=placements` | Manage placements |
| `a=notes` | Manage activity notes |
| `a=appointments` | Manage appointments |
| `a=tasks` | Manage tasks |
| `a=tearsheets` | Manage candidate lists |
| `a=attachments` | Manage file attachments |
| `a=subscriptions` | Manage webhooks |
| `a=meta` | Get entity schemas |

---

## Common Parameters

| Parameter | Example | Description |
|-----------|---------|-------------|
| `page` | `page=2` | Page number |
| `limit` | `limit=50` | Items per page (max 100) |
| `fields` | `fields=id,firstName,email` | Select specific fields |
| `sort` | `sort=dateAdded` | Sort by field |
| `order` | `order=DESC` | Sort direction |
| `query` | `query=city=Austin,status=Active` | Filter results |

---

## Next Steps

1. Read the full [API Documentation](API_DOCUMENTATION.md)
2. Set up [Webhooks](API_DOCUMENTATION.md#8-webhooks) for real-time notifications
3. Implement [OAuth 2.0](API_DOCUMENTATION.md#9-oauth-20) for third-party apps
4. Review [Edge Cases & Best Practices](API_DOCUMENTATION.md#10-edge-cases--best-practices)

---

## Need Help?

- Full Documentation: [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- Troubleshooting: [API_DOCUMENTATION.md#12-troubleshooting](API_DOCUMENTATION.md#12-troubleshooting)
- GitHub Issues: https://github.com/opencats/OpenCATS/issues
