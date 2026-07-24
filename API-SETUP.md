# Adding a REST API to your CandidATS/OpenCATS fork

This adds a token-authenticated JSON API module (`modules/api/ApiUI.php`)
verified against the actual OpenCATS codebase — I cloned `opencats/OpenCATS`
and confirmed the module-loading convention, the `Candidates::add()`
signature, and the `AttachmentCreator::createFromUpload()` call (the exact
method the web UI itself uses for resume upload + text extraction) before
writing this. If your CandidATS fork has diverged from upstream OpenCATS,
double check these against your actual `lib/Candidates.php` and
`lib/Attachments.php` — line numbers/signatures can differ by version.

## 1. Fork the repo (you do this part — needs your GitHub login)

Go to whichever repo you're actually running and click **Fork**:
- https://github.com/opencats/OpenCATS, or
- the specific CandidATS repo you deployed from

This creates `github.com/<your-username>/OpenCATS` (or CandidATS) under
your account.

## 2. Clone your fork locally

```bash
git clone https://github.com/<your-username>/OpenCATS.git
cd OpenCATS
git checkout -b feature/rest-api
```

## 3. Add the new module

Copy the files from this bundle into your cloned fork, preserving paths:

```
modules/api/ApiUI.php
config/api.php.example
```

```bash
mkdir -p modules/api
cp /path/to/bundle/modules/api/ApiUI.php modules/api/
cp /path/to/bundle/config/api.php.example config/
```

Because OpenCATS auto-discovers modules by scanning the `modules/`
directory and matching folder name to `<Name>UI.php` (confirmed in
`lib/ModuleUtility.php`), **no other file needs to change** — the new
`api` module will show up automatically once you restart/redeploy.

## 4. Set your API key

```bash
cp config/api.php.example config/api.php
php -r "echo bin2hex(random_bytes(32));"   # generate a real key
```
Paste the generated key into `config/api.php` as `$API_KEY`.
Add `config/api.php` to `.gitignore` — never commit the real key.

## 5. Set the fallback "owner" user ID

Open `modules/api/ApiUI.php`, find:

```php
$currentUserID = 1; // TODO: replace with your automation user's actual userID
```

Look up the real `userID` of an account in your `user` table (Admin →
Users in the UI, or `SELECT user_id FROM user;` in the DB) and hardcode
it there, or read it from `config/api.php` instead if you'd rather keep
it out of the PHP source.

## 6. Commit and push to your fork

```bash
git add modules/api/ApiUI.php config/api.php.example .gitignore
git commit -m "Add token-authenticated REST API module"
git push origin feature/rest-api
```

## 7. Rebuild/restart your Docker container

If you're running CandidATS via Docker, rebuild the image (or bind-mount
the repo) so the container picks up the new `modules/api/` directory, then
restart:

```bash
docker compose down
docker compose up -d --build
```

## Using the API

```bash
# Add a candidate with a resume, parsed the same way the UI parses it
curl -X POST "http://localhost:8080/index.php?m=api&a=addCandidate" \
  -H "Authorization: Bearer <your API_KEY>" \
  -F "firstName=Supreme" \
  -F "lastName=Test" \
  -F "email1=test@example.com" \
  -F "resume=@/path/to/tailored_resume.pdf"

# Fetch a candidate back
curl "http://localhost:8080/index.php?m=api&a=getCandidate&candidateID=1" \
  -H "Authorization: Bearer <your API_KEY>"
```

The `addCandidate` response includes `resume.extractedText` — the exact
text CandidATS's own parser (pdftotext/antiword/etc. under the hood)
pulled from your file. That's your ground truth for "did this resume
parse cleanly," no screenshot-scraping required.

## What this replaces from the earlier Playwright-based bridge

You can now drop the browser-automation step from `bridge.py` entirely
and call this API directly with `requests` — faster, less brittle, and
it returns the actual extracted text rather than just a handful of form
field values. I can update `bridge.py` to call this instead if you want
the full pipeline wired up end-to-end.

## Security notes

- This endpoint bypasses normal session login by design (it's meant for
  scripts, not browsers) — the API key is the *only* thing standing
  between the internet and `candidates.add`-level access. Don't expose
  port 8080 (or whatever you map it to) outside your local machine /
  trusted network unless you put this behind HTTPS and a reverse proxy.
- Treat `config/api.php` like a secrets file: `.gitignore` it, never
  commit the real key, rotate it if you ever suspect it leaked.
