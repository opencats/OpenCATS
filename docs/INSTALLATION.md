# OpenCATS REST API & Tearsheets - Installation Guide

## Prerequisites

Before installing, ensure you have:

- OpenCATS 0.9.7+ installed and running
- PHP 7.2 or higher
- MariaDB 10.6+ or MySQL 5.7+
- Admin access to OpenCATS (access level 500+)
- Command-line access to your server

---

## Installation Steps

### Step 1: Backup Your Database

Always backup before making changes:

```bash
mysqldump -u opencats -p opencats > opencats_backup_$(date +%Y%m%d).sql
```

### Step 2: Run Database Migration

Navigate to your OpenCATS installation directory and run the migration:

```bash
cd /var/www/opencats

# Run the migration
mysql -u opencats -p opencats < db/migrations/001_add_api_and_tearsheets.sql
```

This creates the following tables:
- `api_keys` - Stores API credentials (sandbox accounts)
- `api_sessions` - Stores temporary session tokens
- `tearsheet` - Stores saved job order lists
- `tearsheet_joborder` - Links tearsheets to job orders
- `api_request_log` - Logs API requests for debugging

### Step 3: Verify File Placement

Ensure all files are in the correct locations:

```
opencats/
├── modules/
│   └── api/
│       └── ApiUI.php              # REST API controller
├── lib/
│   ├── ApiKeys.php                # API key management
│   ├── ApiResponse.php            # JSON response helper
│   └── Tearsheets.php             # Tearsheet operations
├── modules/settings/
│   ├── SettingsUI.php             # (modified - includes apiKeys method)
│   ├── Administration.tpl         # (modified - includes API Keys link)
│   └── ApiKeys.tpl                # API Keys admin template
├── db/migrations/
│   └── 001_add_api_and_tearsheets.sql
└── docs/
    ├── API.md
    ├── API_KEYS_GUIDE.md
    ├── INSTALLATION.md            # This file
    ├── INTEGRATION_ARCHITECTURE.md
    └── TEARSHEETS.md
```

### Step 4: Set File Permissions

Ensure proper permissions:

```bash
# Make files readable by web server
chmod 644 modules/api/ApiUI.php
chmod 644 lib/ApiKeys.php
chmod 644 lib/ApiResponse.php
chmod 644 lib/Tearsheets.php
chmod 644 modules/settings/ApiKeys.tpl
```

### Step 5: Verify Installation

Test that the API is working:

```bash
# Health check (no authentication required)
curl "http://localhost/opencats/index.php?m=api&a=ping"

# Expected response:
# {"status":"ok","version":"1.0.0","timestamp":"2026-01-25T12:00:00+00:00"}
```

---

## Configuration

### Create Your First API Key

**Option 1: Command Line (Recommended for initial setup)**

```bash
cd /var/www/opencats
php lib/ApiKeys.php create 1 "My First API Key"
```

Save the displayed API Key and Secret immediately - the secret is shown only once.

**Option 2: Web Admin Interface**

1. Log in to OpenCATS as an administrator
2. Go to **Settings** > **API Keys**
3. Enter a description and click **Create API Key**
4. Copy and save the credentials immediately

### Test Authentication

```bash
# Test with your new API key
curl -H "X-Api-Key: YOUR_API_KEY_HERE" \
  "http://localhost/opencats/index.php?m=api&a=joborders"
```

---

## Troubleshooting

### "Table doesn't exist" Error

The migration didn't run. Execute:

```bash
mysql -u opencats -p opencats < db/migrations/001_add_api_and_tearsheets.sql
```

### "Class not found" Error

Files are missing or in wrong location. Verify file placement (Step 3).

### "Unauthorized" (401) Error

1. Check if API key exists and is active:
   ```bash
   php lib/ApiKeys.php list
   ```

2. Verify header format:
   ```bash
   # Correct
   curl -H "X-Api-Key: abc123..." "http://..."

   # Also correct
   curl -H "Authorization: Bearer abc123..." "http://..."
   ```

### API Keys Menu Not Showing

1. Verify `modules/settings/Administration.tpl` includes the API Keys link
2. Verify `modules/settings/SettingsUI.php` has the `apiKeys` case in handleRequest()
3. Clear browser cache and re-login

### Migration Errors

If foreign key errors occur:

```bash
# Check if user table exists
mysql -u opencats -p -e "DESCRIBE opencats.user;"

# Check if joborder table exists
mysql -u opencats -p -e "DESCRIBE opencats.joborder;"
```

---

## Verifying the Complete Installation

Run these tests to confirm everything works:

```bash
# 1. Health check
curl "http://localhost/opencats/index.php?m=api&a=ping"

# 2. Create API key
php lib/ApiKeys.php create 1 "Installation Test"
# (Save the key and secret)

# 3. Test authentication
curl -H "X-Api-Key: YOUR_KEY" \
  "http://localhost/opencats/index.php?m=api&a=joborders"

# 4. Test tearsheets endpoint
curl -H "X-Api-Key: YOUR_KEY" \
  "http://localhost/opencats/index.php?m=api&a=tearsheets"

# 5. Verify web UI
# Log in to OpenCATS → Settings → API Keys
# Should see the key you just created
```

---

## Uninstallation

To remove the REST API feature (if needed):

```sql
-- Remove tables (WARNING: Deletes all API keys and tearsheets!)
DROP TABLE IF EXISTS tearsheet_joborder;
DROP TABLE IF EXISTS tearsheet;
DROP TABLE IF EXISTS api_sessions;
DROP TABLE IF EXISTS api_request_log;
DROP TABLE IF EXISTS api_keys;

-- Remove views
DROP VIEW IF EXISTS v_tearsheets_summary;
DROP VIEW IF EXISTS v_api_keys_summary;
```

Then remove the files listed in Step 3.

---

## Next Steps

1. Read the [API Keys Guide](./API_KEYS_GUIDE.md) for detailed usage
2. Review [API Documentation](./API.md) for endpoint reference
3. See [Tearsheets Guide](./TEARSHEETS.md) for tearsheet feature usage
4. Check [Integration Architecture](./INTEGRATION_ARCHITECTURE.md) for system diagrams

---

*For support, see the OpenCATS GitHub repository or community forums.*
