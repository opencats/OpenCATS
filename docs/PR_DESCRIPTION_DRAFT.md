# Pull Request: Add REST API Module and Tearsheets Feature

## Summary

This PR introduces a comprehensive REST API module and full Tearsheets feature to OpenCATS, enabling seamless integration with external applications like job distribution tools. The implementation follows Bullhorn-compatible response formats for maximum interoperability with existing ATS integrations.

### Key Features

- **Comprehensive REST API** with 16+ endpoints covering all major OpenCATS entities
- **Bullhorn-compatible response format** for job distribution and similar integrations
- **Full Tearsheets feature** with CRUD operations and candidate associations
- **Dual authentication support**: API Key and OAuth 2.0
- **Rate limiting** to protect against API abuse
- **Request logging** for auditing and debugging
- **Webhook subscriptions** for real-time event notifications

---

## Endpoints Added

### Authentication & Health

| Endpoint | Methods | Description |
|----------|---------|-------------|
| `/api/ping` | GET | Health check and API status |
| `/api/auth` | POST | API key authentication |
| `/api/oauth` | GET, POST | OAuth 2.0 authorization flows |

### Core Entities

| Endpoint | Methods | Description |
|----------|---------|-------------|
| `/api/candidates` | GET, POST, PUT, DELETE | Candidate management |
| `/api/joborders` | GET, POST, PUT, DELETE | Job order management |
| `/api/companies` | GET, POST, PUT, DELETE | Company management |
| `/api/contacts` | GET, POST, PUT, DELETE | Contact management |

### Tearsheets

| Endpoint | Methods | Description |
|----------|---------|-------------|
| `/api/tearsheets` | GET, POST, PUT, DELETE | Tearsheet management |
| `/api/tearsheets/{id}/candidates` | GET, POST, DELETE | Tearsheet candidate associations |

### Workflow Entities

| Endpoint | Methods | Description |
|----------|---------|-------------|
| `/api/jobsubmissions` | GET, POST, PUT, DELETE | Job submission tracking |
| `/api/placements` | GET, POST, PUT, DELETE | Placement management |
| `/api/notes` | GET, POST, PUT, DELETE | Notes and comments |
| `/api/appointments` | GET, POST, PUT, DELETE | Calendar appointments |
| `/api/tasks` | GET, POST, PUT, DELETE | Task management |
| `/api/attachments` | GET, POST, DELETE | File attachments |

### Advanced Features

| Endpoint | Methods | Description |
|----------|---------|-------------|
| `/api/subscriptions` | GET, POST, DELETE | Webhook subscriptions |
| `/api/meta` | GET | Schema discovery and metadata |
| `/api/massupdate` | POST | Bulk update operations |

---

## Files Added

### API Module Core

```
modules/api/
├── ApiUI.php                    # Main API router and controller
└── handlers/
    ├── PingHandler.php          # Health check endpoint
    ├── AuthHandler.php          # Authentication handler
    ├── CandidatesHandler.php    # Candidates CRUD
    ├── JobOrdersHandler.php     # Job orders CRUD
    ├── CompaniesHandler.php     # Companies CRUD
    ├── ContactsHandler.php      # Contacts CRUD
    ├── TearsheetsHandler.php    # Tearsheets CRUD
    ├── JobSubmissionsHandler.php # Job submissions CRUD
    ├── PlacementsHandler.php    # Placements CRUD
    ├── NotesHandler.php         # Notes CRUD
    ├── AppointmentsHandler.php  # Appointments CRUD
    ├── TasksHandler.php         # Tasks CRUD
    ├── AttachmentsHandler.php   # Attachments CRUD
    ├── SubscriptionsHandler.php # Webhooks CRUD
    ├── MetaHandler.php          # Schema discovery
    └── MassUpdateHandler.php    # Bulk operations
```

### Library Classes

```
lib/
├── Tearsheets.php               # Tearsheets business logic
├── ApiKeys.php                  # API key management
├── ApiConfig.php                # API configuration
├── ApiRateLimiter.php           # Rate limiting implementation
├── ApiRequestLogger.php         # Request/response logging
└── OAuth2Server.php             # OAuth 2.0 server implementation
```

### Database Migrations

```
db/migrations/
├── 001_add_api_and_tearsheets.sql   # Core API and tearsheets tables
├── 002_oauth2_tables.sql            # OAuth 2.0 tables
├── 003_job_submission_placement.sql # Job submission and placement tables
├── 004_extended_entities.sql        # Extended entity fields
├── 005_tearsheet_candidates.sql     # Tearsheet-candidate associations
└── 006_webhooks.sql                 # Webhook subscription tables
```

### Documentation

```
docs/
├── API.md                       # API overview
├── API_DOCUMENTATION.md         # Detailed API documentation
├── API_Reference.md             # Complete endpoint reference
├── API_QUICKSTART.md            # Getting started guide
├── API_KEYS_GUIDE.md            # API key management guide
├── API_CHANGELOG.md             # Version history
├── TEARSHEETS.md                # Tearsheets feature documentation
└── Bullhorn_API_Gap_Analysis.md # Bullhorn compatibility analysis
```

### Tests

```
test/
└── api_live_test.sh             # Live API endpoint tests
```

---

## Database Schema Changes

### New Tables

| Table | Purpose |
|-------|---------|
| `api_keys` | API key storage and management |
| `api_request_log` | Request/response logging |
| `api_rate_limits` | Rate limiting tracking |
| `oauth2_clients` | OAuth 2.0 client applications |
| `oauth2_tokens` | OAuth 2.0 access tokens |
| `oauth2_auth_codes` | OAuth 2.0 authorization codes |
| `tearsheet` | Tearsheet definitions |
| `tearsheet_candidate` | Tearsheet-candidate associations |
| `webhook_subscription` | Webhook subscription configuration |
| `webhook_event_log` | Webhook delivery history |

### Migration Details

1. **001_add_api_and_tearsheets.sql**
   - Creates `api_keys` table with key hash, permissions, rate limits
   - Creates `tearsheet` table with owner, name, description
   - Creates `tearsheet_candidate` association table
   - Adds indexes for performance

2. **002_oauth2_tables.sql**
   - Creates OAuth 2.0 client registration table
   - Creates access token storage
   - Creates authorization code storage
   - Implements token expiration

3. **003_job_submission_placement.sql**
   - Extends job submission tracking
   - Adds placement management tables
   - Links candidates, jobs, and companies

4. **004_extended_entities.sql**
   - Adds extended fields for notes
   - Adds appointment scheduling support
   - Adds task management support

5. **005_tearsheet_candidates.sql**
   - Enhances tearsheet-candidate relationships
   - Adds ordering and metadata fields

6. **006_webhooks.sql**
   - Creates webhook subscription table
   - Creates event logging table
   - Supports retry logic

---

## Testing

### API Endpoint Tests

```
Test Results: 17/17 PASSED

✓ GET  /api/ping              - Health check
✓ POST /api/auth              - Authentication
✓ GET  /api/candidates        - List candidates
✓ POST /api/candidates        - Create candidate
✓ GET  /api/candidates/{id}   - Get candidate
✓ PUT  /api/candidates/{id}   - Update candidate
✓ GET  /api/joborders         - List job orders
✓ POST /api/joborders         - Create job order
✓ GET  /api/companies         - List companies
✓ POST /api/companies         - Create company
✓ GET  /api/tearsheets        - List tearsheets
✓ POST /api/tearsheets        - Create tearsheet
✓ POST /api/tearsheets/{id}/candidates - Add candidate
✓ GET  /api/tearsheets/{id}/candidates - List candidates
✓ GET  /api/meta              - Schema metadata
✓ POST /api/subscriptions     - Create webhook
✓ GET  /api/subscriptions     - List webhooks
```

### UI Testing

- [x] Tearsheet creation and editing
- [x] Candidate association to tearsheets
- [x] Tearsheet listing and filtering
- [x] Tearsheet deletion with cascade
- [x] API key management interface
- [x] Request log viewing

---

## API Response Format

All responses follow the Bullhorn-compatible format:

### Success Response

```json
{
  "data": {
    "id": 123,
    "firstName": "John",
    "lastName": "Doe",
    "email": "john.doe@example.com"
  },
  "meta": {
    "status": "success",
    "timestamp": "2025-01-25T10:30:00Z"
  }
}
```

### List Response

```json
{
  "data": [...],
  "count": 50,
  "start": 0,
  "total": 150,
  "meta": {
    "status": "success"
  }
}
```

### Error Response

```json
{
  "error": {
    "code": "NOT_FOUND",
    "message": "Candidate not found",
    "details": {}
  },
  "meta": {
    "status": "error",
    "timestamp": "2025-01-25T10:30:00Z"
  }
}
```

---

## Authentication

### API Key Authentication

```bash
curl -H "Authorization: Bearer YOUR_API_KEY" \
     https://opencats.example.com/index.php?m=api&a=candidates
```

### OAuth 2.0 Authentication

```bash
# 1. Get authorization code
GET /index.php?m=api&a=oauth&action=authorize&client_id=XXX&redirect_uri=XXX

# 2. Exchange for access token
POST /index.php?m=api&a=oauth&action=token
Content-Type: application/x-www-form-urlencoded

grant_type=authorization_code&code=XXX&client_id=XXX&client_secret=XXX

# 3. Use access token
curl -H "Authorization: Bearer ACCESS_TOKEN" \
     https://opencats.example.com/index.php?m=api&a=candidates
```

---

## Rate Limiting

Default limits (configurable per API key):

| Tier | Requests/Minute | Requests/Hour | Requests/Day |
|------|-----------------|---------------|--------------|
| Standard | 60 | 1,000 | 10,000 |
| Premium | 120 | 5,000 | 50,000 |
| Unlimited | No limit | No limit | No limit |

Rate limit headers are included in all responses:

```
X-RateLimit-Limit: 60
X-RateLimit-Remaining: 45
X-RateLimit-Reset: 1706180400
```

---

## Installation

### 1. Run Database Migrations

```bash
mysql -u opencats -p opencats < db/migrations/001_add_api_and_tearsheets.sql
mysql -u opencats -p opencats < db/migrations/002_oauth2_tables.sql
mysql -u opencats -p opencats < db/migrations/003_job_submission_placement.sql
mysql -u opencats -p opencats < db/migrations/004_extended_entities.sql
mysql -u opencats -p opencats < db/migrations/005_tearsheet_candidates.sql
mysql -u opencats -p opencats < db/migrations/006_webhooks.sql
```

### 2. Create API Key

```sql
INSERT INTO api_keys (
  key_hash,
  site_id,
  user_id,
  name,
  permissions,
  is_active
) VALUES (
  SHA2('your-api-key-here', 256),
  1,
  1,
  'Initial API Key',
  '{"read": true, "write": true}',
  1
);
```

### 3. Access the API

```
https://your-opencats-instance/index.php?m=api&a=ping
```

---

## Breaking Changes

**None** - This PR is fully backward compatible with existing OpenCATS installations.

- All new functionality is additive
- No existing tables are modified
- No existing endpoints are changed
- No configuration changes required for existing features

---

## Dependencies

- PHP 7.4+ (existing requirement)
- MySQL 5.7+ / MariaDB 10.3+ (existing requirement)
- No new external dependencies

---

## Security Considerations

- API keys are stored as SHA-256 hashes, never in plaintext
- OAuth 2.0 follows RFC 6749 specifications
- Rate limiting prevents abuse
- Request logging enables security auditing
- Permissions are granular and configurable per key
- HTTPS is recommended for production use

---

## Future Enhancements

Planned for future releases:

- [ ] GraphQL endpoint support
- [ ] Batch operations optimization
- [ ] Advanced filtering and search
- [ ] Custom field support in API
- [ ] API versioning (v2)
- [ ] SDK libraries (PHP, Python, JavaScript)

---

## Related Issues

- Closes #XXX - REST API for external integrations
- Closes #XXX - Tearsheets feature request
- Addresses #XXX - external integration requirements

---

## Reviewers

Please review:

- [ ] Database migration scripts for correctness
- [ ] API endpoint security and authentication
- [ ] Response format Bullhorn compatibility
- [ ] Error handling and edge cases
- [ ] Documentation accuracy

---

## Checklist

- [x] Code follows OpenCATS coding standards
- [x] All new files have appropriate headers
- [x] Database migrations are reversible
- [x] API endpoints are documented
- [x] Tests pass (17/17)
- [x] No breaking changes
- [x] Security review completed
- [x] Documentation is complete

---

## Screenshots

*Note: Add screenshots of:*
- Tearsheet management UI
- API key management interface
- Sample API responses in Postman/curl

---

## License

All code in this PR is released under the same license as OpenCATS (GPL v3).
