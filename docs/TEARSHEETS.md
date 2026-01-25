# OpenCATS Tearsheets

## What are Tearsheets?

Tearsheets are saved lists of job orders - like playlists for your job postings. This feature is inspired by Bullhorn's tearsheet functionality.

## Use Cases

- **Job Board Distribution**: Create a tearsheet of jobs to send to job boards
- **Client Presentations**: Group jobs for a specific client
- **Recruiter Assignments**: Organize jobs by recruiter territory
- **Priority Jobs**: Mark hot/urgent positions

## API Usage

```bash
# List tearsheets
curl -H "X-Api-Key: key" "?m=api&a=tearsheets"

# Get jobs in tearsheet
curl -H "X-Api-Key: key" "?m=api&a=tearsheets&id=1&sub=joborders"
```

## Database Schema

```sql
tearsheet (tearsheet_id, site_id, user_id, name, description, is_public, date_created, date_modified)
tearsheet_joborder (tearsheet_id, joborder_id, date_added, added_by)
```

## Integration

Tearsheets integrate with the REST API to provide Bullhorn-compatible job list functionality for tools like JobPulse.
