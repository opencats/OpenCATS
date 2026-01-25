## Description

This PR adds two highly-requested features to OpenCATS:

1. **REST API Module** - Provides programmatic access to OpenCATS data
2. **Tearsheets Feature** - Allows users to create saved lists of job orders

### Why These Features?

- REST API enables integration with external tools (job boards, automation, JobPulse)
- Tearsheets is a standard staffing industry feature (popularized by Bullhorn)
- Both features maintain backward compatibility with existing installations

### Changes

**New Files:**
- `modules/api/ApiUI.php` - REST API controller (486 lines)
- `lib/ApiKeys.php` - API key management (569 lines)
- `lib/Tearsheets.php` - Tearsheet business logic (391 lines)
- `lib/ApiResponse.php` - JSON response helper (68 lines)
- `db/migrations/001_add_api_and_tearsheets.sql` - Database schema (214 lines)
- `docs/API.md` - API documentation
- `docs/TEARSHEETS.md` - Tearsheets documentation

### API Endpoints Added

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `?m=api&a=ping` | Health check |
| POST | `?m=api&a=auth` | Authenticate |
| GET | `?m=api&a=joborders` | List job orders |
| GET | `?m=api&a=joborders&id={id}` | Get job order |
| GET | `?m=api&a=tearsheets` | List tearsheets |
| GET | `?m=api&a=tearsheets&id={id}` | Get tearsheet |
| GET | `?m=api&a=tearsheets&id={id}&sub=joborders` | Get jobs in tearsheet |
| GET | `?m=api&a=candidates&id={id}` | Get candidate |
| GET | `?m=api&a=companies&id={id}` | Get company |

### Testing Checklist

- [ ] API authentication works with X-Api-Key header
- [ ] API authentication works with Bearer token
- [ ] Job order endpoints return correct JSON
- [ ] Tearsheet CRUD operations work
- [ ] No breaking changes to existing functionality
- [ ] Works with PHP 7.2+ and MariaDB 10.6

### Documentation

- Added `docs/API.md` with full endpoint documentation
- Added `docs/TEARSHEETS.md` with feature guide

### Related Issues

Closes #214 (Integration with a jobboard)
Closes #479 (Job board integrations)

---

*This contribution makes OpenCATS compatible with JobPulse and similar job distribution tools!*
