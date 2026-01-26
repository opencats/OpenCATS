# OpenCATS REST API Module

This module provides a RESTful API for OpenCATS, designed to be compatible with Bullhorn API patterns.

## Quick Links

- [Full Documentation](../../docs/API_DOCUMENTATION.md)
- [Quick Start Guide](../../docs/API_QUICKSTART.md)
- [Changelog](../../docs/API_CHANGELOG.md)
- [Audit Report](../../test/reports/FINAL_AUDIT_REPORT.md)

## Directory Structure

```
modules/api/
├── ApiUI.php                 # Main API controller
├── README.md                 # This file
├── handlers/                 # Entity-specific handlers
│   ├── AppointmentHandler.php
│   ├── AssociationHandler.php
│   ├── AttachmentHandler.php
│   ├── CandidateHandler.php
│   ├── CompanyHandler.php
│   ├── ContactHandler.php
│   ├── JobOrderHandler.php
│   ├── JobSubmissionHandler.php
│   ├── MassUpdateHandler.php
│   ├── MetaHandler.php
│   ├── NoteHandler.php
│   ├── OAuthHandler.php
│   ├── PlacementHandler.php
│   ├── SubscriptionHandler.php
│   ├── TaskHandler.php
│   └── TearsheetHandler.php
├── traits/                   # Shared functionality
│   ├── ApiHelpers.php        # Response/pagination helpers
│   └── WebhookTrigger.php    # Webhook event dispatching
└── formatters/               # Response formatting
    └── EntityFormatter.php   # Bullhorn-compatible formatting
```

## Supported Libraries

```
lib/
├── ApiKeys.php              # API key management
├── ApiConfig.php            # Configuration helpers
├── ApiRateLimiter.php       # Rate limiting
├── ApiRequestLogger.php     # Request audit logging
├── OAuth2Server.php         # OAuth 2.0 implementation
├── WebhookSubscription.php  # Webhook subscriptions
├── WebhookDispatcher.php    # Webhook event delivery
├── JobSubmissions.php       # Pipeline management
├── Placements.php           # Placement tracking
├── Notes.php                # Activity notes
├── Appointments.php         # Calendar items
├── Tasks.php                # To-do items
└── Tearsheets.php           # Candidate lists
```

## Database Tables

```sql
-- API Core
api_keys                     -- API key storage
api_rate_limits              -- Rate limit tracking
api_request_log              -- Request audit log

-- OAuth 2.0
oauth_clients                -- OAuth applications
oauth_access_tokens          -- Access tokens
oauth_refresh_tokens         -- Refresh tokens
oauth_authorization_codes    -- Auth codes

-- Webhooks
webhook_subscriptions        -- Webhook endpoints
webhook_delivery_log         -- Delivery attempts
webhook_event_queue          -- Pending events

-- Extended Entities
tearsheet                    -- Candidate lists
tearsheet_joborder           -- Tearsheet-job links
tearsheet_candidate          -- Tearsheet-candidate links
```

## API Endpoints

| Endpoint | Handler | Description |
|----------|---------|-------------|
| `ping` | ApiUI | Health check |
| `auth` | ApiUI | API key auth |
| `oauth` | OAuthHandler | OAuth 2.0 flows |
| `candidates` | CandidateHandler | Candidate CRUD |
| `joborders` | JobOrderHandler | Job order CRUD |
| `companies` | CompanyHandler | Company CRUD |
| `contacts` | ContactHandler | Contact CRUD |
| `jobsubmissions` | JobSubmissionHandler | Pipeline CRUD |
| `placements` | PlacementHandler | Placement CRUD |
| `notes` | NoteHandler | Notes CRUD |
| `appointments` | AppointmentHandler | Calendar CRUD |
| `tasks` | TaskHandler | Task CRUD |
| `tearsheets` | TearsheetHandler | Tearsheet CRUD |
| `attachments` | AttachmentHandler | File management |
| `massupdate` | MassUpdateHandler | Bulk operations |
| `associations` | AssociationHandler | Entity linking |
| `subscriptions` | SubscriptionHandler | Webhook management |
| `meta` | MetaHandler | Schema discovery |

## Authentication Methods

1. **X-Api-Key Header** (Recommended)
   ```
   X-Api-Key: your-api-key
   ```

2. **Bearer Token**
   ```
   Authorization: Bearer your-token
   ```

3. **Query Parameter** (Testing only)
   ```
   ?api_key=your-api-key
   ```

## Response Format

**Success:**
```json
{
    "total": 100,
    "page": 1,
    "limit": 25,
    "data": [...]
}
```

**Error:**
```json
{
    "error": true,
    "message": "Error description",
    "code": 400
}
```

## Running Tests

```bash
# Run full audit
cd opencats
bash test/run_full_audit.sh

# Run specific audits
php test/security/sql_injection_audit.php
php test/quality/code_style_audit.php
php test/functional/crud_completeness_audit.php
```

## Contributing

1. Follow existing code style (PSR-2 compatible)
2. Add PHPDoc to all public methods
3. Run audits before submitting PRs
4. Update documentation for new features

## License

CATS Public License Version 1.1a
