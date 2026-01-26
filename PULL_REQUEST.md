## Description

This PR adds two highly-requested features to OpenCATS:

1. **REST API Module** - Provides programmatic access to OpenCATS data
2. **Tearsheets Feature** - Allows users to create saved lists of job orders
3. **Web-based API Key Management** - Admin UI for managing API keys

### Why These Features?

- REST API enables integration with external tools (job boards, automation, job distribution)
- Tearsheets is a standard staffing industry feature (popularized by Bullhorn)
- Both features maintain backward compatibility with existing installations
- Web UI allows admins to manage API keys without CLI access

### Changes

**New Files:**
- `modules/api/ApiUI.php` - REST API controller (486 lines)
- `lib/ApiKeys.php` - API key management (569 lines)
- `lib/Tearsheets.php` - Tearsheet business logic (391 lines)
- `lib/ApiResponse.php` - JSON response helper (69 lines)
- `db/migrations/001_add_api_and_tearsheets.sql` - Database schema (214 lines)
- `modules/settings/ApiKeys.tpl` - API Keys admin template (204 lines)
- `setup-dev.sh` - Development environment setup script

**Modified Files:**
- `modules/settings/SettingsUI.php` - Added apiKeys() method and handler
- `modules/settings/Administration.tpl` - Added API Keys menu link

**Documentation:**
- `docs/API.md` - Basic API reference
- `docs/API_KEYS_GUIDE.md` - Comprehensive API key documentation (310 lines)
- `docs/TEARSHEETS.md` - Tearsheets feature guide
- `docs/INTEGRATION_ARCHITECTURE.md` - System integration diagrams

### API Endpoints Added

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `?m=api&a=ping` | Health check |
| POST | `?m=api&a=auth` | Authenticate with key+secret |
| GET | `?m=api&a=joborders` | List job orders |
| GET | `?m=api&a=joborders&id={id}` | Get single job order |
| GET | `?m=api&a=tearsheets` | List tearsheets |
| GET | `?m=api&a=tearsheets&id={id}` | Get tearsheet details |
| GET | `?m=api&a=tearsheets&id={id}&sub=joborders` | Get jobs in tearsheet |
| GET | `?m=api&a=candidates` | List candidates |
| GET | `?m=api&a=candidates&id={id}` | Get single candidate |
| GET | `?m=api&a=companies` | List companies |
| GET | `?m=api&a=companies&id={id}` | Get single company |

### Admin Features

Access via: **Settings > API Keys**

- Create new API keys (sandbox accounts)
- View all existing keys with usage stats
- Activate/Deactivate keys
- Regenerate secrets
- Delete keys
- One-time credential display (secure)

### Testing Checklist

- [ ] Database migration runs without errors
- [ ] API authentication works with X-Api-Key header
- [ ] API authentication works with Bearer token
- [ ] Job order endpoints return correct JSON (Bullhorn-compatible)
- [ ] Tearsheet CRUD operations work
- [ ] Settings > API Keys page loads correctly
- [ ] Can create/deactivate/delete API keys via web UI
- [ ] No breaking changes to existing functionality
- [ ] Works with PHP 7.2+ and MariaDB 10.6

### Installation

1. Run database migration:
   ```bash
   mysql -u opencats -p opencats < db/migrations/001_add_api_and_tearsheets.sql
   ```

2. Create first API key (CLI):
   ```bash
   php lib/ApiKeys.php create 1 "My First Integration"
   ```

3. Or use web UI: Settings > API Keys > Create

### Related Issues

Closes #214 (Integration with a jobboard)
Closes #479 (Job board integrations)

---

*This contribution makes OpenCATS compatible with Bullhorn-compatible job distribution tools!*
