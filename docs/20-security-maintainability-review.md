# 20 — Security & Maintainability Review

This document is a **code-reading review** of the OpenCATS PHP 7.4 codebase. Every
finding below points at code that was opened and verified in this repository, with a
`file:line` citation and (where load-bearing) the real quoted source. It is **not** a
penetration test: no payloads were fired, no running instance was probed. Confirmed
findings are distinguished from items that need runtime verification, both inline and in
the register at the end.

The headline posture is fair to state up front: this is a legacy app that has been
**partially hardened** and the hardening is real. It has a global CSRF token check
(`hash_equals`-based), per-action ACL guards on the core CRUD modules, parameterised /
escaped SQL throughout the `lib/` data layer, a file-upload extension **whitelist**, and
modern password hashing (`password_hash` / `password_verify` with a lazy MD5 migration).
The genuine gaps are concentrated in (a) the deliberately public surfaces (careers / rss /
xml / graphs / wizard / install), (b) a handful of legacy AJAX endpoints, (c) one
cross-site attachment retrieval path, and (d) the `eval()`-based hook/wizard machinery.

---

## Method & scope

Examined:

- The front controller `index.php` (boot, auth, the global POST CSRF gate).
- The AJAX front controller `ajax.php` (its own CSRF gate, function-name sanitisation,
  installer lockdown).
- `lib/Session.php` CSRF methods, `lib/DatabaseConnection.php` escaping primitives,
  `lib/FileUtility.php` upload whitelist, `lib/Users.php` password handling, `lib/Hooks.php`
  hook runtime (cross-referenced with doc 11).
- The per-action ACL guard pattern in a representative controller
  (`modules/candidates/CandidatesUI.php`) and the ACL inventory in
  `docs/_evidence/acl-summary.md` / doc 14.
- The cross-site attachment retrieval path in `modules/attachments/AttachmentsUI.php` +
  `lib/Attachments.php` (cross-referenced with doc/modules/attachments.md).
- The public surfaces: `modules/careers/CareersUI.php`, `modules/graphs/GraphsUI.php`,
  `modules/wizard/WizardUI.php`, and two un-gated AJAX endpoints
  (`ajax/zipLookup.php`, `ajax/getParsedAddress.php`).
- `config.php` (committed secrets), `composer.json` (autoload scope), and the project's own
  `SECURITY.MD` claims (cross-checked against the code below).

Not examined at runtime: actual exploitability of the unescaped AJAX/careers output (XSS),
the contents of `lib/mime.types`, and the behaviour of `$_SESSION['hooks']` under a
tampered session/cache store.

---

## Findings

### F1 — Global CSRF protection on POST (strength), with documented public exemptions

**Severity:** Info (this is a control, noted for completeness) — with a Low caveat on the
exemptions.

**Evidence.** The front controller validates a CSRF token on **every authenticated POST**,
except the career-portal / rss / xml flows (`index.php:145-163`):

```php
if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] === 'POST' &&
    $_SESSION['CATS']->isLoggedIn() &&
    (!isset($careerPage) || !$careerPage) &&
    (!isset($_GET['showCareerPortal']) || $_GET['showCareerPortal'] != '1') &&
    (!isset($rssPage) || !$rssPage) &&
    (!isset($xmlPage) || !$xmlPage))
{
    $token = null;
    if (isset($_POST['csrfToken'])) { $token = $_POST['csrfToken']; }
    if (!$_SESSION['CATS']->isCSRFTokenValid($token))
    {
        CommonErrors::fatal(COMMONERROR_BADFIELDS, null, 'Invalid request.');
    }
}
```

The token is cryptographically strong and validated in constant time
(`lib/Session.php:1243-1267`):

```php
public function rotateCSRFToken()
{
    $token = bin2hex(random_bytes(32));
    $this->storeValueByName('csrfToken', $token);
    return $token;
}
public function isCSRFTokenValid($token)
{
    $storedToken = $this->retrieveValueByName('csrfToken');
    if (!is_string($storedToken) || $storedToken === '' || !is_string($token))
    {
        return false;
    }
    return hash_equals($storedToken, $token);
}
```

`ajax.php` has its own independent gate for authenticated POSTs (`ajax.php:56-79`), using
the same `isCSRFTokenValid`.

**Why it matters.** This is a real, correctly-built CSRF defence (random 32-byte token,
`hash_equals` comparison, fail-closed on a missing/empty token). The only structural
caveat: the gate keys off `isLoggedIn()` **and** the careers/rss/xml flags, so the
career-portal POST (job application, including resume upload) is **intentionally
CSRF-exempt** (`index.php:147-150`) — acceptable for an anonymous public form, but it means
that surface relies entirely on its own input handling (see F6). The AJAX gate also only
fires for `REQUEST_METHOD === 'POST'` (`ajax.php:50,56`), so any AJAX endpoint reachable on
GET is not CSRF-checked there (see F2/F7).

**Recommendation.** Keep as-is. If any state-changing AJAX endpoint is reachable via GET,
either force POST or add an explicit token check inside the handler.

---

### F2 — Authorization gaps: modules/actions with no per-action ACL guard

**Severity:** Medium.

**Evidence.** The core CRUD controllers implement a consistent per-action guard. The
canonical pattern (`modules/candidates/CandidatesUI.php:88-93`):

```php
case 'show':
    if ($this->getUserAccessLevel('candidates.show') < ACCESS_LEVEL_READ)
    {
        CommonErrors::fatal(COMMONERROR_PERMISSION, $this, 'Invalid user level for action.');
    }
    $this->show();
    break;
```

candidates, joborders, companies, contacts, reports, import and (mostly) settings follow
this pattern per `docs/_evidence/acl-summary.md`. However, several **authenticated** modules
dispatch their actions with **no `getUserAccessLevel(...)` guard at all** (verified against
the acl-summary, which was extracted by reading each controller):

- **activity** (`modules/activity/ActivityUI.php`) — `viewByDate`, `listByViewDataGrid`,
  `default`: no controller guards (auth only). (acl-summary.md:71-75)
- **home** (`modules/home/HomeUI.php`) — all actions auth-only. (acl-summary.md:84-85)
- **lists** (`modules/lists/ListsUI.php`) — web actions un-guarded; only the AJAX
  endpoints check `lists @ EDIT`. (acl-summary.md:87-89)
- **export** (`modules/export/ExportUI.php`) — `export`/`exportByDataGrid` auth-only.
  (acl-summary.md:114-115)
- **queue** (`modules/queue/QueueUI.php`) — no-op controller, auth only
  (`modules/queue/QueueUI.php:35-55`). (acl-summary.md:117-118)

These are gated only by `_authenticationRequired = true` (the `UserInterface` default), so
**any logged-in user regardless of access level** can reach them. Most damaging in
principle is **export** (data egress) being level-agnostic.

**Why it matters.** A low-privilege (e.g. READ-only) user can reach actions that other
modules would gate at EDIT/DELETE. The risk is bounded — these modules are mostly
read/aggregation views and the export path only supports `DATA_ITEM_CANDIDATE`
(acl-summary.md:115) — but the inconsistency is a real authorization gap, not a
hardened design.

**Recommendation.** Add explicit `getUserAccessLevel(...)` guards to export (at minimum) and
the activity/home/lists web actions, matching the candidates pattern.

---

### F3 — Cross-site attachment retrieval (site_id scoping disabled)

**Severity:** Medium (needs runtime confirmation of cross-tenant reach).

**Evidence.** `modules/attachments/AttachmentsUI.php` requires only a logged-in session (no
`getUserAccessLevel` call anywhere in the file) and then fetches the attachment row with
**site verification turned off** (`AttachmentsUI.php:83-93`):

```php
$attachments = new Attachments(-1);
$rs = $attachments->get($attachmentID, false);

if (empty($rs) || md5($rs['directoryName']) != $_GET['directoryNameHash'])
{
    CommonErrors::fatal(
        COMMONERROR_BADFIELDS,
        $this,
        'Invalid id / directory / filename, or you do not have permission to access this attachment.'
    );
}
```

In `lib/Attachments.php:579,601-604`, the `false` second argument makes the SQL `site_id`
condition evaluate to `true` (i.e. it does **not** restrict by site). So the **only**
authorization gate is that the caller knows `md5(directory_name)` — and that hash is
embedded in the `retrievalURL` the app itself hands out (`lib/Attachments.php:617-622`,
per docs/modules/attachments.md:66-67). The `id` is a sequential integer
(`attachment_id AUTO_INCREMENT`).

**Why it matters.** In a multi-site install, knowledge of an `attachment_id` plus the
directory-name md5 is sufficient to stream a file regardless of which site owns it. The
directory name is a guessable/derivable value (md5 of a stored directory name), and the
file is streamed `Content-Disposition: inline` (`AttachmentsUI.php:127`). Whether this is
actually cross-tenant-reachable depends on how directory names are generated — flagged as
needs-verification but the disabled site scoping is confirmed in source.

**Recommendation.** Pass `true` (verify site) for non-backup retrievals, or scope `new
Attachments($_SESSION['CATS']->getSiteID())` and remove the `false` bypass for the normal
download path.

---

### F4 — Unauthenticated public graph tiers

**Severity:** Low.

**Evidence.** `modules/graphs/GraphsUI.php` sets `_authenticationRequired = false`
(`:48`). Its `handleRequest()` dispatches a first tier with **no login check** —
`testGraph`, `wordVerify`, `jobOrderReportGraph`, `generic`, `genericPie` — each
`return`ing before the auth check (`GraphsUI.php:72-99`, comment `//These graphs do not
require a login.` at `:76`). Only the second tier (`activity`, `newCandidates`,
`newJobOrders`, `newSubmissions`, `miniPlacementStatistics`, `miniJobOrderPipeline`) is
wrapped in `if ($_SESSION['CATS']->isLoggedIn())` (`GraphsUI.php:101-...`).

**Why it matters.** `wordVerify` is the CAPTCHA generator (legitimately public).
`jobOrderReportGraph` / `generic` / `genericPie` render charts from request parameters
without auth; height/width come straight from `$_GET` (`GraphsUI.php:62-64`). This is a
low-severity image-rendering surface, but it is unauthenticated by design and worth noting
as attack surface (resource use, any data the generic chart endpoints accept).

**Recommendation.** Confirm `jobOrderReportGraph`/`generic`/`genericPie` cannot leak
site-scoped data when called anonymously; otherwise move them behind the `isLoggedIn()`
tier.

---

### F5 — `eval()`-based hooks and the wizard `ajax_getPage` eval (code-execution trust surface)

**Severity:** Medium (architectural; not directly request-injectable in the stock tree).

**Evidence.** The hook runtime executes PHP source strings read from `$_SESSION['hooks']`
(`lib/Hooks.php:52-72`, full analysis in doc 11). Call sites run them with `eval()`, e.g.
`index.php:199`, `ajax.php:161` (`if (!eval(Hooks::get('AJAX_HOOK'))) return;`),
`lib/Attachments.php:160`. There are ~229 distinct hook keys across ~260 `eval(Hooks::get())`
call sites (doc 11, *Source evidence*). The fragments originate from module files on disk
and transit `$_SESSION` (and `modules.cache` when `CACHE_MODULES` is on) — so a writable
cache file or tamperable session store becomes PHP-code injection into `eval()`.

The wizard is more direct. `modules/wizard/WizardUI.php` is `_authenticationRequired = false`
(`:46`) and `ajax_getPage()` **evals PHP stored in the session** (`WizardUI.php:179-182`):

```php
if (($php = $_SESSION['CATS_WIZARD']['pages'][$requestPage]['php']) != '')
{
    eval($php);
}
```

`ajax.php` also blindly `eval()`s output filters from the included handler
(`ajax.php:168-171`):

```php
foreach ($filters as $filter)
{
    eval($filter);
}
```

**Why it matters.** This is a large `eval()` trust surface. In the stock distribution the
inputs are not user-supplied at request time (hook fragments come from module source;
`$_SESSION['CATS_WIZARD']['php']` is set by server-side wizard setup), so it is not a
turnkey RCE. But the integrity guarantee reduces to *"only trusted code reaches the modules
directory, the cache file, and the session store."* Any path that lets an attacker influence
`$_SESSION['hooks']`, `modules.cache`, or `$_SESSION['CATS_WIZARD']['pages'][...]['php']`
becomes arbitrary PHP execution. This is also a maintainability hazard: a hook registration
bug surfaces as a fatal `eval()` error inside the host method.

**Recommendation.** Treat the session store and `modules.cache` as code (lock down session
storage, ensure `modules.cache` is not web-writable). Long term, replace the `eval()` hook
model with a callable/event mechanism. Confirm nothing user-controlled flows into
`$_SESSION['CATS_WIZARD'][...]['php']`.

---

### F6 — SQL injection posture: escaped data layer (strength), with a `FIXME` caveat

**Severity:** Info (defence) / Low (caveat).

**Evidence.** The `lib/` data layer builds SQL with `sprintf` plus typed escaping helpers.
The string escaper (`lib/DatabaseConnection.php:495-498`):

```php
public function makeQueryString($string)
{
    return "'" . $this->escapeString($string) . "'";
}
```

`escapeString` wraps `mysqli_real_escape_string` (`DatabaseConnection.php:486`), with
`makeQueryStringOrNULL` (`:508-518`) and `makeQueryInteger` for typed values. A grep of
`lib/*.php` for raw `$_GET`/`$_POST`/`$_REQUEST` interpolated into SQL (excluding the escape
helpers / `intval` / `preg_replace`) returned **no hits** — the data layer consistently
routes user input through `makeQuery*`. Example in the credential path
(`lib/Users.php:1315-1326`):

```php
$sql = sprintf(
        "UPDATE user SET password = %s WHERE user.user_id = %s",
        $this->_db->makeQueryString($this->hashPassword($password)),
        $this->_db->makeQueryInteger($userID)
        );
```

**Why it matters.** The dominant posture is sound (escaped + quoted). The caveat is
self-documented at `DatabaseConnection.php:482-485`:

```php
// FIXME: Security issue, this function is not enough for sanitizing
// user input. ... To be replaced with Symfony's stack
return mysqli_real_escape_string($this->_connection, $string);
```

`mysqli_real_escape_string` is safe for **quoted** string contexts (which is how
`makeQueryString` uses it), but provides no protection if a value is interpolated
**unquoted** into SQL or used in an identifier position. No such concrete unquoted-use site
was confirmed in the spot-check, so this is reported as posture + a flagged caveat rather
than a confirmed injection.

**Recommendation.** Keep using `makeQuery*` everywhere; never interpolate request values
unquoted. The `FIXME`'s intended move to a parameterised/ORM layer would close the residual
risk.

---

### F7 — Career portal & legacy AJAX endpoints: public, CSRF-exempt, with a documented escaping gap

**Severity:** Medium.

**Evidence.** The careers portal is public (`CareersUI` is `_authenticationRequired = false`,
all branches un-guarded — acl-summary.md:107-108) and CSRF-exempt (F1). It does use
`htmlspecialchars` heavily (71 occurrences in `modules/careers/CareersUI.php`), typically
`htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, HTML_ENCODING)`
(docs/modules/careers.md:243-244) — so it is **not** unescaped wholesale. But the code
itself documents an **escaping gap** in the apply path (`CareersUI.php:1514-1515`, quoted in
docs/modules/careers.md:260-263):

```php
// NOTE: Careers Portal renders these values into HTML without consistent output escaping.
// TODO (security/xss-hardening): Escape attributes/textarea/title consistently, then switch to getTrimmedInput().
```

Applicant values there flow into activity-note HTML and email templates without per-use
escaping (careers.md:265-267).

Separately, two legacy AJAX endpoints emit request-derived data into XML with **no auth, no
ACL, no escaping**:

- `ajax/getParsedAddress.php` — echoes parsed values from `$_REQUEST['addressBlock']`
  straight into the XML response (`getParsedAddress.php:74-157`), e.g.
  `"<company>" . $parsedAddressArray['company'] . "</company>"`.
- `ajax/zipLookup.php` — echoes `$_REQUEST['zip']`-derived street/city/state into XML
  (`zipLookup.php:17-38`).

Neither file checks authentication or wraps output in `htmlspecialchars`; both are reachable
on GET (so the `ajax.php` POST-only CSRF gate at `:50,56` does not apply).

**Why it matters.** The careers portal is the primary XSS-sensitive surface (public,
unauthenticated, accepts free text + file uploads), and its own authors flag the
inconsistent escaping. The AJAX endpoints reflect request input into a response without
encoding; whether this is exploitable as reflected XSS depends on the XML content-type and
client handling (needs runtime confirmation), but the unescaped reflection is confirmed in
source.

**Recommendation.** Close the documented careers escaping gap (escape attributes/textarea/
title consistently). Wrap the AJAX endpoint output in proper encoding and require a session
for `getParsedAddress`/`zipLookup`.

---

### F8 — File-upload extension whitelist (strength)

**Severity:** Info.

**Evidence.** `lib/FileUtility.php:166-203` sanitises filenames: strips `*nix`/Windows path
separators, replaces non-ASCII/control bytes, then enforces a **whitelist** — anything not
listed gets `.txt` appended (`FileUtility.php:192-196`):

```php
$GoodFileExtensions = array('bak', 'bmp', 'csv', 'doc', 'docx', 'heic', 'html', 'jpeg', 'jpg', 'msg', 'odg', 'odt', 'pages', 'pdf', 'png', 'ppt', 'pptx', 'rtf', 'tiff', 'txt', 'wpd', 'wps', 'xls', 'xlsx', 'xps');
if (!in_array($fileExtension, $GoodFileExtensions))
{
    $filename .= ".txt";
}
```

**Why it matters.** This is a positive control: an uploaded `shell.php` becomes
`shell.php.txt`, neutralising direct PHP execution via the stored file. Two notes: (1)
`html` is on the whitelist, so an `.html` upload served from the attachments path could host
stored XSS if the web server renders it inline (`AttachmentsUI.php:127` sends
`Content-Disposition: inline`); (2) the whitelist is the only filename control —
defence-in-depth still wants the documented `.htaccess`/permissions on the upload folder
(SECURITY.MD:17-20).

**Recommendation.** Keep the whitelist. Consider dropping `html` (or forcing
`Content-Disposition: attachment` for it) and apply the documented upload-folder `.htaccess`.

---

### F9 — Committed credentials & license key in `config.php`

**Severity:** Low (these are placeholder/sample values, but they are committed and
copied into installs).

**Evidence.** `config.php` ships with a hardcoded license key and default DB password
(`config.php:31,40-43`):

```php
define('LICENSE_KEY','3163GQ-54ISGW-14E4SHD-ES9ICL-X02DTG-GYRSQ6');
...
define('DATABASE_USER', 'cats');
define('DATABASE_PASS', 'password');
define('DATABASE_HOST', 'localhost');
define('DATABASE_NAME', 'cats_dev');
```

Plus other placeholder secrets: `MAIL_SMTP_PASS` = `'password'` (`:212`),
`LDAP_BIND_PASSWORD` = `'password'` (`:262`), and demo/tester creds
(`DEMO_LOGIN`/`DEMO_PASSWORD`, `:185-186`).

**Why it matters.** `config.php` is the actual config the app `include_once`s
(`index.php:42`). If an operator deploys without changing `DATABASE_PASS` from `password`,
that is a trivially weak DB credential. The committed `LICENSE_KEY` is a sample. These are
documented as things the operator must change (SECURITY.MD:26-28 mentions strong/unique DB
passwords), but shipping `'password'` as the default invites misconfiguration.

**Recommendation.** Ship a `config.php.dist` with empty/obvious-placeholder secrets and keep
the real `config.php` out of version control; or force the installer to set a DB password.

---

### F10 — Password hashing (strength) with lazy MD5 migration

**Severity:** Info.

**Evidence.** Passwords are hashed with `password_hash`/`PASSWORD_DEFAULT`
(`lib/Users.php:1256-1258`) and verified with `password_verify`
(`lib/Users.php:1296-1311`). Legacy MD5 hashes are detected and migrated **on successful
login** (`lib/Users.php:1291-1311`):

```php
private function isLegacyPasswordHash($hash)
{
    return (bool) preg_match('/^[0-9a-f]{32}$/i', $hash);
}
private function verifyAndMigratePassword($userID, $password, $storedHash)
{
    if ($this->isLegacyPasswordHash($storedHash))
    {
        if (md5($password) !== $storedHash) { return false; }
        $this->updatePasswordHash($userID, $password);   // rehash to password_hash
        return true;
    }
    return password_verify($password, $storedHash);
}
```

There is also `rehashPasswordIfNeeded` (`:1272-1283`) that upgrades hashes via
`password_needs_rehash`. The legacy MD5 comparison uses `!==` (string compare) — acceptable
here because it's MD5-vs-MD5 hex, not a plaintext compare.

**Why it matters.** This is modern, correct password handling with a sensible migration
path. The only residual is that MD5 hashes persist until each user next logs in (a row that
never logs in keeps its MD5). The code is self-documented as temporary (`FIXME` at
`:1285-1289`).

**Recommendation.** None urgent. Remove the MD5 path once all hashes are confirmed migrated.

---

### F11 — Maintainability: no autoloader for `lib/`, include_once coupling, vestigial code

**Severity:** Low–Medium (maintainability, not security).

**Evidence.**

- **No autoloader for `lib/`.** Composer's PSR-4 autoload only covers `OpenCATS\` under
  `src/OpenCATS/` (`composer.json:20-24`). The ~74 classes in `lib/` are wired by hand-ordered
  `include_once` chains with dependency comments, e.g. `index.php:59-70`:
  ```php
  include_once(LEGACY_ROOT . '/lib/Session.php'); /* Depends: MRU, Users, DatabaseConnection. */
  include_once(LEGACY_ROOT . '/lib/UserInterface.php'); /* Depends: Template, Session. */
  ```
  The `vendor/autoload.php` is pulled in ad hoc inside individual lib files
  (`lib/Companies.php:2`, `lib/JobOrders.php:2`, `lib/TemplateUtility.php:38`,
  `lib/Mailer.php:43`, `lib/Search.php:39`) rather than once at boot.
- **`eval()` hook surface** (~229 keys / ~260 call sites — doc 11) is also a maintainability
  cost: failures are fatal `eval()` errors, not catchable exceptions.
- **Vestigial / no-op code.** `modules/queue/QueueUI.php` is a no-op controller (the real
  logic lives in `lib/QueueProcessor.php` / CLI) (`QueueUI.php:35-55`, acl-summary.md:117-118).
  The wizard module (`modules/wizard/`) is reachable but its `ajax_getPage` evals session PHP
  (F5). Commented-out config blocks (`config.php:276-488`) carry a large dead "all possible
  secure object names" reference list.
- **Free-text status columns.** `joborder.status` is `varchar(64) DEFAULT 'Active'` and
  `joborder.type` is `varchar(64) DEFAULT 'C'` (`db/cats_schema.sql`, `CREATE TABLE
  \`joborder\``) — statuses are free strings, not an enum/lookup, so typos/variants are
  possible and the status sets are configured via commented PHP arrays in `config.php`
  (`:282-314`).
- **Spelling/typo debt that is load-bearing.** The maintenance flag is misspelled
  `performMaintenence` and that exact misspelling is the POST key used across the app
  (`index.php:44`, `lib/ModuleUtility.php:207,290`) — renaming it would break callers, so the
  typo is now an API. The ACL key `joborders.createAttachement` is misspelled in the
  reference list (`config.php:420`) vs the correctly-spelled `deleteAttachment`. Deprecated
  `is_writeable` (PHP alias) is used in `lib/CATSUtility.php:145`, `lib/FileUtility.php:493`,
  `lib/InstallationTests.php:814-825`.

**Why it matters.** The include_once coupling means class-load order is manual and fragile;
adding a class to a boot path requires finding the right include slot. The free-text status
and misspelled-but-load-bearing identifiers are classic legacy hazards (data drift, can't
safely rename). None of these are vulnerabilities; they raise the cost and risk of change.

**Recommendation.** Introduce a classmap/PSR-4 autoload for `lib/` (incrementally), include
`vendor/autoload.php` once at boot, and move job-order status/type to a lookup table. Leave
the `performMaintenence` key alone unless doing a coordinated rename.

---

## Security.MD cross-check

`SECURITY.MD` (= `Security.MD`/`security.md`, same file on this case-insensitive FS) makes
five substantive claims. Verified against code:

| # | SECURITY.MD claim | Verdict | Evidence |
|---|---|---|---|
| 1 | "hashes user passwords using PHP's password_hash() and verifies them with password_verify(), using PASSWORD_DEFAULT" (`SECURITY.MD:11`) | **Confirmed** | `lib/Users.php:1256-1258` (`password_hash($password, PASSWORD_DEFAULT)`), `:1296-1311` (`password_verify`). Plus lazy MD5 migration (`:1291-1311`) the doc doesn't mention. |
| 2 | "main vector for XSS … via the career portal (disabled by default). htmlspecialchars is used to protect career portal form submissions" (`SECURITY.MD:15`) | **Partly confirmed** | `htmlspecialchars` is used pervasively (71x in `CareersUI.php`; idiom `ENT_QUOTES \| ENT_SUBSTITUTE` — careers.md:243-244). But the code itself documents an *inconsistent* escaping gap in the apply path (`CareersUI.php:1514-1515`). "Used" is true; "consistently protected" is not — the doc's own caveat ("any custom templates … can reintroduce issues") understates an in-tree gap. |
| 3 | "a whitelist of 'good' filetypes is used during upload" (`SECURITY.MD:20`) | **Confirmed** | `lib/FileUtility.php:192-196` `$GoodFileExtensions` whitelist; non-whitelisted names get `.txt` appended. (Note: `html` is whitelisted — see F8.) |
| 4 | ".htaccess … Since version 0.9.7 this is no longer required" / review htaccess + permissions (`SECURITY.MD:19-20`) | **Partly** | The whitelist (claim 3) does reduce reliance on `.htaccess`. But attachments stream `Content-Disposition: inline` (`AttachmentsUI.php:127`) and `html` is allowed, so folder `.htaccess`/permissions remain meaningful defence-in-depth — consistent with the doc's own "should be reviewed and deployed." |
| 5 | "ensure you use the --no-dev option … dev packages removed from releases since 0.9.7.2" (`SECURITY.MD:24`) | **Confirmed (structurally)** | `composer.json:12-18` lists `require-dev` (behat, phpunit) separately from runtime `require` (`:5-11`), so `--no-dev` does drop them. The claim that release tarballs omit them can't be verified from the repo tree alone (needs the release artifact). |

No claim was outright **refuted**. The one to watch is #2: the careers XSS protection is
real but, by the code's own admission, not consistent.

---

## Prioritized tech-debt & security register

Ranked highest-risk first. "Confirmed?" = whether the issue is proven in source vs needs a
runtime/exploitability check.

| # | Item | Type | Severity | Confirmed? | Effort | Pointer |
|---|------|------|----------|-----------|--------|---------|
| 1 | Cross-site attachment retrieval — `get($id, false)` disables `site_id` scoping; only md5(dir) gate | security | Med | Confirmed (cross-tenant reach needs verify) | S | `AttachmentsUI.php:83-93`; `lib/Attachments.php:601-604` |
| 2 | Careers portal documented escaping gap (public + CSRF-exempt + accepts uploads) | security | Med | Confirmed (gap); exploitability needs verify | M | `CareersUI.php:1514-1515` |
| 3 | `eval()` of session/cache hook code + wizard `ajax_getPage` evals session PHP | security | Med | Confirmed (design); injectability needs verify | L | `lib/Hooks.php:52-72`; `WizardUI.php:179-182`; `ajax.php:161,168-171` |
| 4 | Authz gaps — export/activity/home/lists/queue have no per-action ACL guard | security | Med | Confirmed | M | acl-summary.md:71-118; `QueueUI.php:35-55` |
| 5 | Un-gated AJAX endpoints reflect request input unescaped (no auth/CSRF on GET) | security | Med | Confirmed (reflection); XSS needs verify | S | `ajax/getParsedAddress.php:74-157`; `ajax/zipLookup.php:17-38` |
| 6 | Default DB password `'password'` + committed LICENSE_KEY/SMTP/LDAP placeholders in `config.php` | security | Low | Confirmed | S | `config.php:31,41,212,262` |
| 7 | Public graph tiers (`jobOrderReportGraph`/`generic`/`genericPie`) unauthenticated | security | Low | Confirmed (data-leak potential needs verify) | S | `GraphsUI.php:48,72-99` |
| 8 | `mysqli_real_escape_string`-only escaping (safe for quoted use; FIXME flags it) | security | Low | Confirmed posture; no unquoted-use site found | L | `DatabaseConnection.php:482-498` |
| 9 | `.html` on upload whitelist + inline disposition (stored-XSS via served file) | security | Low | Confirmed (control present); exploitability needs verify | S | `FileUtility.php:192`; `AttachmentsUI.php:127` |
| 10 | No autoloader for `lib/`; hand-ordered `include_once` chains; ad-hoc `vendor/autoload` | maintainability | Med | Confirmed | L | `composer.json:20-24`; `index.php:59-70` |
| 11 | Free-text `joborder.status`/`type` varchar + statuses configured via commented PHP arrays | maintainability | Low | Confirmed | M | `db/cats_schema.sql` `joborder`; `config.php:282-314` |
| 12 | Vestigial/no-op code (queue controller, dead config reference blocks) | maintainability | Low | Confirmed | S | `QueueUI.php:35-55`; `config.php:276-488` |
| 13 | Load-bearing typos: `performMaintenence` POST key, `joborders.createAttachement`; deprecated `is_writeable` | maintainability | Low | Confirmed | S | `index.php:44`; `config.php:420`; `FileUtility.php:493` |
| 14 | Residual MD5 hashes persist until next login (lazy migration) | security | Info | Confirmed | S | `Users.php:1291-1311` |

**Net posture:** the authentication, CSRF, SQL-escaping, upload-whitelist and password
controls are present and correctly implemented. The real, confirmed risks cluster in the
attachment cross-site path (#1), the public/CSRF-exempt careers + AJAX surfaces (#2, #5),
and the `eval()` machinery (#3) — plus the authorization inconsistency (#4). The
maintainability debt (#10–#13) is real but non-exploitable.

---

## Source evidence

- `index.php:42` (config include), `:44` (`performMaintenence`), `:59-70` (lib include_once
  chain), `:145-163` (global POST CSRF gate), `:147-150` (careers/rss/xml exemption),
  `:199` (`eval(Hooks::get('INDEX_LOAD_HOME'))`).
- `ajax.php:50,56-79` (POST-only session + CSRF gate), `:120-135` (function-name
  `preg_replace` sanitisation + filename build), `:161` (`AJAX_HOOK` eval), `:168-171`
  (filter eval).
- `lib/Session.php:1226-1267` (`getCSRFToken`/`rotateCSRFToken`/`isCSRFTokenValid` with
  `random_bytes(32)` + `hash_equals`).
- `lib/DatabaseConnection.php:480-518` (`escapeString` FIXME, `makeQueryString`,
  `makeQueryStringOrNULL`).
- `lib/FileUtility.php:166-203` (`makeSafeFilename` + `$GoodFileExtensions` whitelist),
  `:493` (`is_writeable`).
- `lib/Users.php:649-690` (`changePassword` + MD5-reference comment), `:1256-1259`
  (`hashPassword`), `:1272-1311` (`rehashPasswordIfNeeded`, `isLegacyPasswordHash`,
  `verifyAndMigratePassword`), `:1313-1327` (`updatePasswordHash`).
- `lib/Hooks.php:52-72` (hook runtime — full analysis in doc 11).
- `modules/candidates/CandidatesUI.php:86-97` (per-action guard pattern).
- `modules/attachments/AttachmentsUI.php:43` (auth-only), `:83-93` (`get($id, false)` +
  md5 gate), `:127` (inline disposition); `lib/Attachments.php:579,601-604,617-622`.
- `modules/graphs/GraphsUI.php:48,62-64,72-101` (public tier vs `isLoggedIn` tier).
- `modules/wizard/WizardUI.php:46` (auth false), `:133-184` (`ajax_getPage`), `:179-182`
  (`eval($php)`).
- `modules/queue/QueueUI.php:35-55` (no-op controller).
- `ajax/zipLookup.php:11-38`, `ajax/getParsedAddress.php:37-157` (unescaped reflected
  output, no auth).
- `modules/careers/CareersUI.php:1514-1515` (in-source escaping-gap NOTE/TODO); careers.md
  (htmlspecialchars idiom + gap context).
- `config.php:31,40-43,185-186,212,262,276-488` (license key, DB creds, demo creds,
  placeholder secrets, dead reference blocks).
- `composer.json:5-24` (runtime require, require-dev, PSR-4 autoload scope = `src/OpenCATS/`
  only).
- `db/cats_schema.sql` `CREATE TABLE \`joborder\`` (`status`/`type` varchar(64)),
  attachment table.
- `SECURITY.MD:11,15,19-20,24` (the five cross-checked claims).
- `docs/_evidence/acl-summary.md:71-134` (per-module ACL inventory).

---

## Unverified / open questions

- **Cross-site attachment exploitability (F3/#1).** The disabled `site_id` scoping is
  confirmed in source, but whether `attachment_id` + `md5(directory_name)` is *practically*
  guessable/derivable across sites depends on how `directory_name` is generated at upload
  time — not traced end-to-end here. Needs a runtime check on a multi-site install.
- **Reflected XSS in `getParsedAddress`/`zipLookup` (F7/#5).** Output reflection without
  encoding is confirmed; whether it executes as XSS depends on the response content-type
  (`text/xml`) and how the calling client renders it. Not fired/verified.
- **Careers stored-XSS (F7/#2).** The inconsistent escaping is documented in-source; the
  exact unescaped sinks (activity-note HTML, email templates per careers.md:265-267) were
  read in summary, not exhaustively traced to a proven payload path.
- **`eval()` injectability (F5/#3).** No request-time path was found that injects into
  `$_SESSION['hooks']` or `$_SESSION['CATS_WIZARD'][...]['php']`; this depends on session/
  cache integrity and the wizard's server-side setup, which weren't fully traced.
- **`generic`/`genericPie`/`jobOrderReportGraph` data exposure (F4/#7).** Confirmed
  unauthenticated; whether they can render site-scoped/sensitive data anonymously was not
  verified against their data-fetch code.
- **SQL unquoted-use sites (F6/#8).** Spot-grep of `lib/` found no raw superglobal in SQL,
  but the full codebase (modules, ajax handlers) was not exhaustively audited for an unquoted
  interpolation of a `makeQueryString` value or identifier-position injection.
- **Release-tarball dev-dependency removal (Security.MD #5).** `require-dev` separation is
  confirmed in `composer.json`; the claim that shipped releases omit dev packages can only be
  verified against an actual release artifact, not this source tree.
