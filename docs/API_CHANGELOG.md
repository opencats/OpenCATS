# OpenCATS REST API - Changelog

All notable changes to the OpenCATS REST API.

---

## [1.0.0] - 2026-01-25

### Added

#### Core API
- RESTful API module (`modules/api/`) with full CRUD operations
- Bullhorn API compatibility for easy migration
- JSON response format with pagination support
- Field selection (`?fields=id,name,email`)
- Sorting (`?sort=dateAdded&order=DESC`)
- Advanced query syntax (`?query=city=Austin,status=Active`)

#### Authentication
- API Key authentication via header (`X-Api-Key`)
- API Key authentication via Bearer token
- API Key + Secret exchange for access tokens
- OAuth 2.0 authorization code flow
- OAuth 2.0 refresh token support
- Token revocation endpoint

#### Entities Supported
- **Candidates** - Full CRUD with search
- **Job Orders** - Full CRUD with status management
- **Companies** (ClientCorporation) - Full CRUD
- **Contacts** (ClientContact) - Full CRUD with company linking
- **Job Submissions** - Pipeline management with status workflow
- **Placements** - Hire tracking with salary/fee management
- **Notes** - Activity logging for any entity
- **Appointments** - Calendar/scheduling integration
- **Tasks** - To-do management with priorities
- **Tearsheets** - Candidate list management
- **Attachments** - File upload/download for resumes and documents

#### Advanced Features
- **Webhooks** - Real-time event notifications
  - Create, update, delete events
  - HMAC signature verification
  - Automatic retry with exponential backoff
  - Delivery logging and monitoring
- **Mass Update** - Bulk operations on multiple entities
- **Associations** - Link entities together
- **Meta Endpoint** - Entity schema discovery

#### Security
- SQL injection prevention (parameterized queries)
- XSS prevention (JSON-encoded output)
- Rate limiting (per-minute and per-hour)
- CORS configuration support
- Input validation on all endpoints
- Secure token generation (random_bytes)
- Timing-safe token comparison

#### Infrastructure
- Request logging for audit compliance
- Rate limit headers in responses
- Configurable CORS origins
- Database migrations for all new tables

### Database Migrations

```
001_add_api_and_tearsheets.sql  - API keys, rate limits, logging, tearsheets
002_oauth2_tables.sql           - OAuth clients, tokens, codes
003_job_submission_placement.sql - Enhanced pipeline and placements
004_extended_entities.sql       - Notes, appointments, tasks
005_tearsheet_candidates.sql    - Tearsheet-candidate associations
006_webhooks.sql                - Webhook subscriptions and delivery
```

### Configuration Options

```php
API_ENABLED                  - Enable/disable API (default: true)
API_VERSION                  - API version string
API_RATE_LIMIT_ENABLED       - Enable rate limiting (default: true)
API_RATE_LIMIT_PER_MINUTE    - Requests per minute (default: 60)
API_RATE_LIMIT_PER_HOUR      - Requests per hour (default: 1000)
API_CORS_ALLOWED_ORIGINS     - CORS allowed origins (default: *)
API_LOG_ENABLED              - Enable request logging (default: true)
```

---

## Migration from Bullhorn

### Endpoint Mapping

| Bullhorn | OpenCATS |
|----------|----------|
| `GET /entity/Candidate` | `GET ?m=api&a=candidates` |
| `POST /entity/Candidate` | `POST ?m=api&a=candidates` |
| `GET /entity/JobOrder` | `GET ?m=api&a=joborders` |
| `GET /entity/ClientCorporation` | `GET ?m=api&a=companies` |
| `GET /entity/ClientContact` | `GET ?m=api&a=contacts` |
| `GET /entity/JobSubmission` | `GET ?m=api&a=jobsubmissions` |
| `GET /entity/Placement` | `GET ?m=api&a=placements` |
| `GET /meta` | `GET ?m=api&a=meta` |

### Authentication Migration

Bullhorn uses OAuth. OpenCATS supports:
1. Simple API Key (recommended for internal use)
2. OAuth 2.0 (for third-party integrations)

---

## Known Limitations

1. **File Size**: Attachments limited to 10MB by default
2. **Pagination**: Maximum 100 items per page
3. **Rate Limits**: Default 60/min, 1000/hour (configurable)
4. **Legacy Tables**: Some tables use MyISAM for compatibility

---

## Future Roadmap

- [ ] GraphQL endpoint support
- [ ] Batch operations endpoint
- [ ] Real-time WebSocket updates
- [ ] API key scopes/permissions
- [ ] Request signing for enhanced security
