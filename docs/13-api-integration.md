# 13 — APIs & Integrations

This document covers OpenCATS's machine-facing surfaces: the internal AJAX endpoint
"API", the three unauthenticated public web feeds (careers / RSS / XML), the optional
Sphinx full-text search backend, the resume/document text-extraction binaries, the
SMTP/mail layer, and the (largely vestigial) SOAP resume-parsing integration.

All claims below are cited to a real file + line that was opened during research.

---

## AJAX endpoint API

### How `ajax.php` dispatches

`ajax.php` is a single front controller. It is **not** a class-based router — it maps the
request parameter `f` to a PHP file on disk and `include()`s it.

1. Bootstrap: `config.php`, `constants.php`, `DatabaseConnection`, `Session`,
   `AJAXInterface`, `CATSUtility` are included (`ajax.php:38-43`).
2. Session: a session is started **only for POST requests** (`ajax.php:50-54`), using
   `@session_name(CATS_SESSION_NAME)` then `session_start()`.
3. CSRF/auth gate (top-level): for `POST` requests where the session is present and
   `isLoggedIn()` is true, the token in `$_POST['csrfToken']` is validated with
   `$_SESSION['CATS']->isCSRFTokenValid($token)`; on failure it emits an XML error
   `<errorcode>-1</errorcode>` / `Invalid request.` and `die()`s (`ajax.php:56-79`).
4. `f` required: missing/empty `f` → XML error `No function specified.` (`ajax.php:81-93`).
5. Installer lockout: if `INSTALL_BLOCK` does **not** exist, the installer is considered
   active and only `f=install:...` actions are permitted; everything else is rejected
   (`ajax.php:95-118`).
6. Routing (`ajax.php:120-135`):
   - No colon in `f`: sanitized to `[A-Za-z0-9]` and resolved to `ajax/<function>.php`.
   - With a colon (`module:function`): resolved to `modules/<module>/ajax/<function>.php`.
7. Readability check: if the resolved file is not `is_readable`, emit XML error
   `Invalid function name.` (`ajax.php:137-149`).
8. Execution + output filtering: unless `nobuffer` is set, the handler is run inside an
   output buffer, `Hooks::get('AJAX_HOOK')` is `eval`'d, leading whitespace is stripped
   (unless `nospacefilter` is set), any `$filters` are `eval`'d, then the buffer is echoed
   (`ajax.php:151-178`).

### The CSRF / auth gate (per-handler)

The top-level gate in `ajax.php` only fires for already-logged-in POSTs. The real
per-request authentication is enforced **inside each handler** by instantiating
`SecureAJAXInterface` (defined in `lib/AJAXInterface.php:196-277`). Its constructor:
starts the session, requires `isSessionLoggedIn()` (else XML error "You are not logged
in." + `die()`), and **re-validates the CSRF token on POST** (`lib/AJAXInterface.php:202-239`).
Handlers that instead use the plain `AJAXInterface` (base class,
`lib/AJAXInterface.php:38-189`) perform **no** login/CSRF check — they are effectively
public to anyone who can reach `ajax.php`.

Helper methods on the base class used by handlers for input validation:
`isRequiredIDValid()`, `isOptionalIDValid()`, `isChecked()`, `getTrimmedInput()`
(`lib/AJAXInterface.php:97-188`), and output helpers `outputXMLPage()`,
`outputXMLErrorPage()`, `outputXMLSuccessPage()` (`lib/AJAXInterface.php:47-86`).

### The 20 `ajax/` handlers

| File | Purpose | Key inputs | Output | Auth / ACL |
|------|---------|------------|--------|------------|
| `deleteActivity.php` | Delete an activity entry | `activityID` (POST) | XML success | `SecureAJAXInterface`; POST-only; ACL `contacts.deleteActivity` ≥ `ACCESS_LEVEL_EDIT` (`ajax/deleteActivity.php:33-45`) |
| `editActivity.php` | Edit an activity entry (notes/type/date/joborder) | `activityID`, `type`, `jobOrderID`, `notes`, `date`, `hour`, `minute`, `ampm` (POST) | XML w/ updated activity | `SecureAJAXInterface`; POST-only; ACL `contacts.editActivity` ≥ `ACCESS_LEVEL_EDIT` (`ajax/editActivity.php:34-46`) |
| `getCandidateIdByEmail.php` | Look up candidate by email | `email` | XML `<candidate><id>/<name>` (id `-1` if none) | `SecureAJAXInterface`; no extra ACL (`ajax/getCandidateIdByEmail.php:30`) |
| `getCandidateIdByPhone.php` | Look up candidate by phone | `phone` | XML `<candidate><id>/<name>` | `SecureAJAXInterface`; no extra ACL (`ajax/getCandidateIdByPhone.php:30`) |
| `getCompanyContacts.php` | List a company's contacts | `companyID` (required, non-zero) | XML list of `<contact>` | `SecureAJAXInterface`; no extra ACL (`ajax/getCompanyContacts.php:33-39`) |
| `getCompanyLocation.php` | A company's address fields | `companyID` | XML address/city/state/zip | `SecureAJAXInterface`; no extra ACL (`ajax/getCompanyLocation.php:33-39`) |
| `getCompanyLocationAndDepartments.php` | Company address + department list | `companyID` | XML address + `<departments>` | `SecureAJAXInterface`; no extra ACL (`ajax/getCompanyLocationAndDepartments.php:33-39`) |
| `getCompanyNames.php` | Company name autocomplete/search | `dataName`, `maxResults` | XML `<result>` list (id/name) | `SecureAJAXInterface`; uses `SearchCompanies` (`ajax/getCompanyNames.php:34, 53`) |
| `getDataGridPager.php` | Re-render a DataGrid page (HTML) | `i` (grid identifier), `p` (JSON params), optional `dynamicArgument` | HTML grid + nav | `SecureAJAXInterface`; no extra ACL (`ajax/getDataGridPager.php:34-44`) |
| `getDataItemJobOrders.php` | Job orders linked to a candidate/company/contact | `dataItemID`, `dataItemType` | XML `<joborder>` list | `SecureAJAXInterface`; no extra ACL; switches on data item type (`ajax/getDataItemJobOrders.php:30-70`) |
| `getParsedAddress.php` | Parse a free-text address block into fields | `mode` (contact/company/person), `addressBlock` | XML name/address/phones/email | **Plain `AJAXInterface` — NO auth/CSRF** (`ajax/getParsedAddress.php:35`) |
| `getPipelineDetails.php` | Activity history for one pipeline entry (HTML) | `candidateJobOrderID` (non-zero) | HTML `<table>` of activities | `SecureAJAXInterface`; no extra ACL (`ajax/getPipelineDetails.php:33-39`) |
| `getPipelineJobOrder.php` | Full job-order pipeline grid (HTML) | `joborderID`, `page`, `entriesPerPage`, `sortBy`, `sortDirection`, `indexFile`, `isPopup` | HTML pipeline | `SecureAJAXInterface`; no top-level ACL — individual action buttons are ACL-gated in template (`ajax/getPipelineJobOrder.php:37-47`; per-action `getAccessLevel(...)` at lines 293/303/308/313) |
| `getReportHTML.php` | (empty stub) | — | — | **Empty file (0 bytes)** — no code (`ajax/getReportHTML.php`) |
| `replaceTemplateTags.php` | Render an email template w/ candidate variables | `candidateID`, `templateText` | XML `<text>` | `SecureAJAXInterface`; no extra ACL (`ajax/replaceTemplateTags.php:6-12`) |
| `setCandidateJobOrderRating.php` | Set a pipeline rating (-6..5) | `candidateJobOrderID`, `rating` (POST) | XML `<newrating>` | `SecureAJAXInterface`; POST-only; ACL `pipelines.editRating` ≥ `ACCESS_LEVEL_EDIT` (`ajax/setCandidateJobOrderRating.php:33-58`) |
| `setColumnWidth.php` | Persist a datagrid column width to session | `instance`, `columnName`, `columnWidth` (POST) | XML success | `SecureAJAXInterface`; POST-only; no ACL (session prefs only) (`ajax/setColumnWidth.php:30-42`) |
| `showTemplate.php` | Fetch raw email-template text | `templateID` | XML `<text>` | `SecureAJAXInterface`; no extra ACL (`ajax/showTemplate.php:5-11`) |
| `testEmailSettings.php` | Send a test email via current SMTP settings | `testEmailAddress`, `fromAddress` (POST) | XML success / mailer error | `SecureAJAXInterface`; POST-only; ACL `settings.emailSettings.POST` ≥ `ACCESS_LEVEL_SA` (`ajax/testEmailSettings.php:33-45`) |
| `zipLookup.php` | Street/city/state from a ZIP | `zip` | XML address/city/state | **Plain `AJAXInterface` — NO auth/CSRF** (`ajax/zipLookup.php:9`) |

> Note the two unauthenticated handlers (`getParsedAddress.php`, `zipLookup.php`) use the
> base `AJAXInterface`, not `SecureAJAXInterface`. Most handlers authenticate via
> `SecureAJAXInterface` but apply **no additional per-resource ACL** — only the
> mutating handlers (`deleteActivity`, `editActivity`, `setCandidateJobOrderRating`,
> `testEmailSettings`) check `getAccessLevel(...)`.

There are also module-scoped AJAX handlers reachable via `f=module:function` →
`modules/<module>/ajax/<function>.php` (`ajax.php:134`); enumerating those per-module is
out of scope for this doc.

---

## Public web endpoints (careers / RSS / XML)

Three directories contain thin shim `index.php` files that set a flag, `chdir('..')`, and
hand off to the main front controller via `CATSUtility::getIndexName()`:

- **Careers portal** — `careers/index.php` sets `$careerPage = true` (`careers/index.php:34-39`).
- **RSS feed** — `rss/index.php` sets `$rssPage = true` (`rss/index.php:34-38`).
- **XML feed** — `xml/index.php` sets `$xmlPage = true` (`xml/index.php:34-39`).

In `index.php`, these flags do two things:

1. **CSRF-exempt:** the POST CSRF check is explicitly skipped when any of `$careerPage`,
   `$_GET['showCareerPortal']=='1'`, `$rssPage`, or `$xmlPage` is set (`index.php:145-163`).
   So the public careers application form and the feeds do not require a CSRF token.
2. **Unauthenticated routing:** the flags route directly to `ModuleUtility::loadModule('careers' | 'rss' | 'xml')` (`index.php:165-181`), bypassing the normal
   `moduleRequiresAuthentication()` login gate (`lib/ModuleUtility.php:109-134`). The
   modules themselves declare they are public by setting
   `$this->_authenticationRequired = false` in their constructors:
   `modules/careers/CareersUI.php:53`, `modules/rss/RssUI.php:49`,
   `modules/xml/XmlUI.php:54` (the base `UserInterface::requiresAuthentication()` defaults
   to `true` — `lib/UserInterface.php:177-185`).

The careers portal is also reachable on the main `index.php` via `?showCareerPortal=1`
(`index.php:166-167`). Behaviour and templates for these modules belong to their own
module docs (careers: `modules/careers/`, rss: `modules/rss/`, xml: `modules/xml/`).

---

## Full-text search (Sphinx)

Sphinx is an **optional** acceleration layer for resume/document searching; OpenCATS works
without it by falling back to MySQL fulltext `WHERE` clauses.

Config constants (`config.php`):

| Constant | Default value | Line |
|----------|---------------|------|
| `ENABLE_SPHINX` | `false` | `config.php:97` |
| `SPHINX_HOST` | `'localhost'` | `config.php:98` |
| `SPHINX_PORT` | `3312` | `config.php:99` |
| `SPHINX_INDEX` | `'cats catsdelta'` | `config.php:100` |

When `ENABLE_SPHINX` is true, `lib/Search.php` loads the Sphinx PHP client via
`vendor/autoload.php` at include time (`lib/Search.php:37-39`). In
`SearchByResumePager` (`lib/Search.php`), the search branches on `ENABLE_SPHINX`:

- **Enabled** (`lib/Search.php:1926-1992`): builds a `SphinxClient`, `SetServer(SPHINX_HOST, SPHINX_PORT)`, `SetMatchMode(SPH_MATCH_EXTENDED)`, filters on `site_id`, converts the
  query with `DatabaseSearch::humanToSphinxBoolean()`, runs `Query($wildCardString, SPHINX_INDEX)` (retrying up to 5× on "server maxed out"), then turns the matched
  attachment IDs into `attachment.attachment_id IN(...)` (`lib/Search.php:1986-1992`).
  Sphinx errors/warnings raise a fatal (`lib/Search.php:1969-1980`).
- **Disabled / fallback** (`lib/Search.php:1997-2004`): builds the WHERE with
  `DatabaseSearch::makeBooleanSQLWhere(DatabaseSearch::fulltextEncode(...), $db, 'attachment.text')` — i.e. a plain MySQL fulltext search, no Sphinx required.

`lib/DatabaseSearch.php` provides the Sphinx-compatible query translator
`humanToSphinxBoolean()` (`lib/DatabaseSearch.php:50-55`), used by the Sphinx branch.

### Optional-updates Sphinx override

`optional-updates/latest-sphinx-search/` ships a newer Sphinx integration intended to be
copied over a normal install (per `optional-updates/latest-sphinx-search/README.MD`,
which links the project wiki "How_to_install_Sphinx"). It contains a replacement
`Search.php`, a `config.php`, and a sample `sphinx.conf`. Its `config.php` enables Sphinx
by default and uses a **different port**: `ENABLE_SPHINX = true`, `SPHINX_HOST = 'localhost'`,
`SPHINX_PORT = 9312`, `SPHINX_INDEX = 'cats catsdelta'`
(`optional-updates/latest-sphinx-search/config.php:89-92`). Note the port differs from the
shipped default `3312` (`config.php:99`); 9312 is the modern Sphinx/Manticore default.

### Sphinx maintenance scripts (`scripts/`)

These shell scripts source `~/ENVIRONMENT.conf` for `SPHINX_BIN` / `SPHINX_CONFIG` (with
hard-coded fallbacks like `SPHINX_BIN="/usr/local/bin"`,
`SPHINX_CONFIG="${CATS_PATH}/config/sphinx/sphinx.conf"`):

- `scripts/sphinx_reindex.sh` — full reindex: `${SPHINX_BIN}/indexer --all --config "${SPHINX_CONFIG}"` (`scripts/sphinx_reindex.sh`).
- `scripts/sphinx_update_delta.sh` — delta index: `${SPHINX_BIN}/indexer --rotate --config "${SPHINX_CONFIG}" catsdelta` (`scripts/sphinx_update_delta.sh`).
- `scripts/sphinx_restart.sh`, `scripts/sphinx_rotate.sh` — restart/rotate helpers.
- `scripts/sphinxtest.php` — a CLI test harness (`define('TEST_QUERY','java')`, `TEST_SITE_ID`)
  that includes `config.php` and exercises a query (`scripts/sphinxtest.php:1-20`).

---

## Resume / document text extraction

Text extraction from uploaded resumes is handled by `lib/DocumentToText.php::convert($fileName, $documentType)` (`lib/DocumentToText.php:72`). It shells out to **external
binaries** for some formats and uses **pure-PHP** routines for others.

External binary path constants (`config.php`):

| Constant | Default value | Line | Used for |
|----------|---------------|------|----------|
| `ANTIWORD_PATH` | `"\\path\\to\\antiword"` | `config.php:62` | `.doc` |
| `ANTIWORD_MAP` | `'8859-1.txt'` | `config.php:63` | antiword char map |
| `PDFTOTEXT_PATH` | `"\\path\\to\\pdftotext"` | `config.php:69` | `.pdf` (xpdf) |
| `HTML2TEXT_PATH` | `"\\path\\to\\html2text"` | `config.php:75` | `.html` |
| `UNRTF_PATH` | `"\\path\\to\unrtf"` | `config.php:81` | **defined but never referenced** in `DocumentToText.php` |

How `convert()` dispatches (`lib/DocumentToText.php:104-194`):

- **DOC** → if `ANTIWORD_PATH == ''` it errors out; otherwise builds
  `'"<ANTIWORD_PATH>" -m <ANTIWORD_MAP> <escapedFilename>'` (`:107-116`). (A DOC whose
  first 5 bytes are `{\rtf` is re-classified as RTF — `:83-95`.)
- **PDF** → `'"<PDFTOTEXT_PATH>" -layout <escapedFilename> -'` (`:118-128`).
- **HTML** → on Windows `TYPE <file> | "<HTML2TEXT_PATH>" -nobs`, else
  `'"<HTML2TEXT_PATH>" -nobs <escapedFilename>'` (`:130-148`).
- **TEXT** → read directly via `_readTextFile()` (`:150-151`).
- **RTF** → pure-PHP `rtf2text()` (the internal parser, `:154-163`, `:433-516`) — **not**
  UnRTF, despite `UNRTF_PATH` existing in config.
- **ODT / DOCX** → pure-PHP via `ZipArchive` + `DOMDocument` (`readZippedXML()`,
  `odt2text()`/`docx2text()`, `:165-187`, `:388-431`).
- **UNKNOWN/default** → error (`:189-193`).

The binary commands are run by the private `_executeCommand()` (`lib/DocumentToText.php:349-386`): on Windows it uses a `COM('WScript.Shell')` redirecting to a temp file in
`CATS_TEMP_DIR`; on other platforms it uses `@exec($command, $output, $returnCode)`.
Filenames are passed through `escapeshellarg(realpath(...))` (`:101`). Output from
`ISO-8859-1` binaries is re-encoded to UTF-8 with `iconv` (`:210-215`).

### `PARSING_ENABLED`

`PARSING_ENABLED` (default `false`, `config.php:51`) is a **separate** feature from text
extraction — it controls the legacy SOAP resume *parser* (see SOAP section), not the
local binaries. `DocumentToText` ignores it entirely; text extraction works regardless of
`PARSING_ENABLED`. The import/candidate flows wrap the SOAP parse call behind
`LicenseUtility::isParsingEnabled()` (`modules/import/ImportUI.php:1391`,
`modules/careers/CareersUI.php:569`), and `License::getParsingStatus()` returns `true`
immediately when `PARSING_ENABLED` is not defined or false (`lib/License.php:712-716`).

---

## Email / SMTP

Mail is sent through PHPMailer wrapped by `lib/Mailer.php` (deep detail is in doc 08).
`Mailer` imports PHPMailer (`use PHPMailer\PHPMailer\PHPMailer;`, `lib/Mailer.php:39`),
requires `vendor/autoload.php` (`:43`), and constructs `new PHPMailer(true)`
(`lib/Mailer.php:76`).

Config constants (`config.php`):

| Constant | Default value | Line |
|----------|---------------|------|
| `MAIL_MAILER` | `3` (0=Disabled, 1=PHP mail, 2=Sendmail, 3=SMTP) | `config.php:197` |
| `MAIL_SENDMAIL_PATH` | `"/usr/sbin/sendmail"` | `config.php:202` |
| `MAIL_SMTP_HOST` | `"localhost"` | `config.php:208` |
| `MAIL_SMTP_PORT` | `587` | `config.php:209` |
| `MAIL_SMTP_AUTH` | `true` | `config.php:210` |
| `MAIL_SMTP_USER` | `"user"` | `config.php:211` |
| `MAIL_SMTP_PASS` | `"password"` | `config.php:212` |
| `MAIL_SMTP_SECURE` | `"tls"` (`''`/`ssl`/`tls`) | `config.php:214` |

`Mailer` maps these onto PHPMailer in a `switch (MAIL_MAILER)` (`lib/Mailer.php:314`):
`Sendmail = MAIL_SENDMAIL_PATH` (`:321`); for SMTP it calls `isSMTP()` then sets
`Host`/`Port`/`SMTPSecure` and, when `MAIL_SMTP_AUTH`, `SMTPAuth`/`Username`/`Password`
(`lib/Mailer.php:325-341`). The `testEmailSettings.php` AJAX handler exercises this path
live (see AJAX table).

---

## SOAP / WSDL

The `wsdl/` directory contains three WSDL descriptors for a Cognizo/Resfly-hosted resume
service. **Two are wired into PHP code; one is dead.**

| WSDL file | Service / operation | Endpoint (`soap:address`) | Referenced in code? |
|-----------|---------------------|---------------------------|---------------------|
| `wsdl/parse.wsdl` | `CATSDocumentParseService` / `DocumentParse` | `http://soap.resfly.com/parse.php` (`wsdl/parse.wsdl:78`) | **Yes** — `lib/ParseUtility.php:53,60` |
| `wsdl/status.wsdl` | `StatusService` / `Status` | `http://soap.resfly.com/status.php` (`wsdl/status.wsdl:69`) | **Yes** — `lib/ParseUtility.php:135` |
| `wsdl/keyCheck.wsdl` | `KeyCheckService` / `KeyCheck` | `http://catsone.com/keyCheck.php` (`wsdl/keyCheck.wsdl:66`) | **No** — no PHP file references `keyCheck` (grep `--include=*.php` returned nothing) → **vestigial** |

`lib/ParseUtility.php` is the only SOAP **client**:

- Constructor hard-codes `$this->_wsdl = 'wsdl/parse.wsdl'` (`lib/ParseUtility.php:53`);
  `startClient()` does `new SoapClient($this->_wsdl)` (`:60`).
- `documentParse(name,size,mimeType,contents)` calls the remote `DocumentParse(LICENSE_KEY, ...)` and maps the response to candidate fields (`lib/ParseUtility.php:85-130`).
- `status($key)` builds `new SoapClient('wsdl/status.wsdl')` and calls `Status($key)`,
  but first short-circuits on `if (!CATSUtility::isSOAPEnabled()) return false;`
  (`lib/ParseUtility.php:132-173`). `isSOAPEnabled()` checks `extension_loaded('soap') && class_exists('SoapClient')` (`lib/CATSUtility.php:647-657`).

Call sites (all gated by the parsing toggle): `lib/License.php:718-719`,
`modules/candidates/CandidatesUI.php:1126-1127`, `modules/careers/CareersUI.php:571-573`,
`modules/import/ImportUI.php:1390,1431`.

**Honest assessment of wired vs dead:**
- There is **no SOAP server** in this repo — OpenCATS is purely a *client* of an external
  service. `InstallationTests.php:326-341` only checks that the PHP `soap` extension is
  installed; it does not host any endpoint.
- The two referenced endpoints point at `soap.resfly.com`, a third-party
  Cognizo-era service that is **defunct**. With the shipped default `PARSING_ENABLED = false`
  (`config.php:51`), `documentParse()`/`status()` are never reached through the normal
  flows, so this integration is effectively dead in a default install. The code path
  remains intact and would attempt the remote SOAP call only if `PARSING_ENABLED` is set
  to true and a live parse server is reachable.
- `keyCheck.wsdl` is entirely orphaned (no code reference) and is dead/vestigial.

---

## Source evidence

- AJAX dispatch + gates: `ajax.php:38-178`
- AJAX interfaces: `lib/AJAXInterface.php:38-189` (base), `:196-277` (`SecureAJAXInterface`)
- 20 handlers: `ajax/*.php` (each cited inline in the table; `getReportHTML.php` is 0 bytes)
- Public feeds: `careers/index.php:34-39`, `rss/index.php:34-38`, `xml/index.php:34-39`;
  routing/CSRF-exempt `index.php:145-181`; module auth flags
  `modules/careers/CareersUI.php:53`, `modules/rss/RssUI.php:49`,
  `modules/xml/XmlUI.php:54`; default `lib/UserInterface.php:177-185`;
  `lib/ModuleUtility.php:109-134`
- Sphinx: `config.php:97-100`; `lib/Search.php:37-39, 1926-2004`;
  `lib/DatabaseSearch.php:50-55`;
  `optional-updates/latest-sphinx-search/{config.php:89-92,Search.php,sphinx.conf,README.MD}`;
  `scripts/sphinx_reindex.sh`, `scripts/sphinx_update_delta.sh`,
  `scripts/sphinx_restart.sh`, `scripts/sphinx_rotate.sh`, `scripts/sphinxtest.php`
- Text extraction: `config.php:51,62,63,69,75,81,87`; `lib/DocumentToText.php:72-431`;
  `lib/License.php:687-730`
- Mail: `config.php:197,202,208-214`; `lib/Mailer.php:39,43,76,314-341`
- SOAP: `wsdl/parse.wsdl:78`, `wsdl/status.wsdl:69`, `wsdl/keyCheck.wsdl:66`;
  `lib/ParseUtility.php:53,60,85-173`; `lib/CATSUtility.php:647-657`;
  `lib/InstallationTests.php:326-341`

---

## Unverified / open questions

- **Module-scoped AJAX (`modules/<m>/ajax/*.php`):** the `f=module:function` route exists
  (`ajax.php:134`) but the full set of module AJAX handlers was not enumerated here (only
  the 20 top-level `ajax/` handlers were in scope).
- **Resfly/Cognizo SOAP service reachability:** the endpoints `soap.resfly.com` and
  `catsone.com/keyCheck.php` were read from the WSDLs but not network-tested; they are
  assumed defunct based on age and the disabled-by-default `PARSING_ENABLED`. Not verified
  by an actual request.
- **`UNRTF_PATH`:** defined in `config.php:81` but I found no reference in
  `lib/DocumentToText.php`; RTF is handled by the internal `rtf2text()`. Whether any other
  file consumes `UNRTF_PATH` was not exhaustively grepped beyond `DocumentToText.php`.
- **`getReportHTML.php`:** the file is 0 bytes (no handler logic); whether any client JS
  still calls `f=getReportHTML` was not checked.
- **`License::isParsingEnabled()` logic:** every branch returns `true`
  (`lib/License.php:687-705`), which looks like a stub/bug, but confirming the intended
  behaviour is outside this doc's scope (belongs to a licensing/parsing doc).
- **PHPMailer version/details:** only the `Mailer.php` wiring was inspected here; the
  vendored PHPMailer version and full send semantics are deferred to doc 08.
