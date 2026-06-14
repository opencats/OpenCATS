# OpenCATS Career Portal — Spanish (es_MX) Translation

This adds a Spanish (Mexico) translation of the OpenCATS Career Portal.

## What's included

- `es_MX_career_portal_template.sql` — Database template "CATS 2.0 ES" with full Spanish translation
- Modified `modules/careers/CareersUI.php` — i18n detection based on active template name

## How to install

1. Import the Spanish template into your database:
```bash
mysql opencats < install/es_MX_career_portal_template.sql
```

2. Activate the Spanish template from the admin panel:
   - Go to Settings → Career Portal Settings
   - Select "CATS 2.0 ES" as the active template
   - Save

Or via SQL:
```sql
UPDATE settings SET value = 'CATS 2.0 ES' WHERE setting = 'activeBoard';
```

## Translated sections

| Section | Status |
|---------|--------|
| Main page | ✅ |
| Search Results | ✅ |
| Job Details | ✅ |
| Candidate Registration | ✅ |
| Apply for Position | ✅ |
| Candidate Profile | ✅ |
| Thanks for Submission | ✅ |
| Header (navigation buttons) | ✅ |
| Table headers (Company, Department, Position, Location) | ✅ |
| Action buttons (Apply, Continue, Submit, Login) | ✅ |
| Welcome back message | ✅ |

## How it works

The translation is detected automatically based on the active template name.
Any template containing "ES" in its name activates Spanish strings in CareersUI.php.

To switch back to English:
```sql
UPDATE settings SET value = 'CATS 2.0' WHERE setting = 'activeBoard';
```

## Tested on
- PHP 8.4.21
- MariaDB 10.11
- Debian 12 (Bookworm)
