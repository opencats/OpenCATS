# Module: attachments

## Overview

The attachments module is a single-controller module whose sole job is to **serve (stream) a stored attachment file back to the browser**. It does not provide UI for uploading, listing, or deleting attachments — those operations are driven by other modules (candidates, companies, joborders, contacts) through `lib/Attachments.php`.

Controller class declaration:

```php
class AttachmentsUI extends UserInterface
```
(modules/attachments/AttachmentsUI.php:33)

It defines a single class constant controlling streaming chunk size:

```php
const ATTACHMENT_BLOCK_SIZE = 80192;
```
(modules/attachments/AttachmentsUI.php:36)

Constructor (modules/attachments/AttachmentsUI.php:39-48):

```php
public function __construct()
{
    parent::__construct();

    $this->_authenticationRequired = true;
    $this->_moduleDirectory = 'attachments';
    $this->_moduleName = 'attachments';
    $this->_moduleTabText = '';
    $this->_subTabs = array();
}
```

- `$this->_authenticationRequired = true;` (modules/attachments/AttachmentsUI.php:43) — a logged-in user session is required to reach any action. `_moduleTabText` is empty (modules/attachments/AttachmentsUI.php:46), so this module renders no top navigation tab.
- The base class default is also `protected $_authenticationRequired = true;` (lib/UserInterface.php:50), and the accessor returns that flag (lib/UserInterface.php:179-181).

What it serves: a binary file stored on the local filesystem under `attachments/<directory_name>/<stored_filename>`, identified by an `attachment_id` plus a hash of the directory name (modules/attachments/AttachmentsUI.php:95-97).

The required include of the data layer:

```php
include_once(LEGACY_ROOT . '/lib/Attachments.php');
```
(modules/attachments/AttachmentsUI.php:31), plus `lib/CommonErrors.php` (modules/attachments/AttachmentsUI.php:30).

## Action catalog

`handleRequest()` dispatches on `getAction()` (modules/attachments/AttachmentsUI.php:51-66). It fires the `ATTACHMENTS_HANDLE_REQUEST` hook before the switch (modules/attachments/AttachmentsUI.php:55).

| Action | Exact ACL guard | Required level | Handler | lib calls | Output |
|--------|-----------------|----------------|---------|-----------|--------|
| `getAttachment` | **(none)** — no `getUserAccessLevel(...)` call anywhere in this file; only `_authenticationRequired = true` (constructor) plus a `directoryNameHash` match | Authenticated session only | `getAttachment()` (modules/attachments/AttachmentsUI.php:69-145) | `new Attachments(-1)` (AttachmentsUI.php:83); `Attachments::get($attachmentID, false)` (AttachmentsUI.php:84); `Attachments::fileMimeType($fileName)` (AttachmentsUI.php:113) | Streams the file bytes with `Content-Disposition: inline`, `Content-Type`, `Content-Length`, `Pragma: no-cache`, `Expires: 0`, then `exit()` |
| _default_ | n/a | n/a | `break;` (no-op) (modules/attachments/AttachmentsUI.php:63-64) | none | nothing |

There is exactly **one** real switch case (`getAttachment`) plus the empty default. **No action in this module calls `getUserAccessLevel(...)`** (verified by reading the file in full). The site-scoping/permission check is delegated to the data layer and a hash check (see next section).

## Attachment retrieval / serving flow

`getAttachment()` (modules/attachments/AttachmentsUI.php:69-145):

1. Raises PHP memory limit: `@ini_set('memory_limit', '128M');` (AttachmentsUI.php:72). (A `FIXME` comment questions whether this is needed, AttachmentsUI.php:71.)
2. Validates the required `id` GET parameter: `if (!$this->isRequiredIDValid('id', $_GET))` → `CommonErrors::fatal(COMMONERROR_BADINDEX, ...)` (AttachmentsUI.php:74-79). Helper is `isRequiredIDValid($key, $request, $allowZero = false)` (lib/UserInterface.php:318).
3. Constructs the data layer with siteID `-1`: `$attachments = new Attachments(-1);` (AttachmentsUI.php:83). Constructor signature `public function __construct($siteID)` (lib/Attachments.php:50-54).
4. Fetches the row with site verification **disabled**: `$rs = $attachments->get($attachmentID, false);` (AttachmentsUI.php:84). In `get($attachmentID, $verifySiteID = true)` (lib/Attachments.php:579), the second arg `false` makes the WHERE clause emit `true` for the site condition: `(site_id = %s || content_type = 'catsbackup' || %s)` with `($verifySiteID ? 'false' : 'true')` (lib/Attachments.php:601-604). So the SQL does **not** restrict by `site_id` here.
5. **Authorization is enforced solely by a directory-name hash**: `if (empty($rs) || md5($rs['directoryName']) != $_GET['directoryNameHash'])` → `CommonErrors::fatal(COMMONERROR_BADFIELDS, ...)` (AttachmentsUI.php:86-93). The caller must already know `md5(directory_name)`; this matches the `retrievalURL` produced by `get()` which embeds `urlencode(md5($rs['directoryName']))` (lib/Attachments.php:617-622).
6. Builds the local path: `$filePath = sprintf('attachments/%s/%s', $directoryName, $fileName);` from `$rs['directoryName']` and `$rs['storedFilename']` (AttachmentsUI.php:95-97).
7. Backup special case: if `$rs['contentType'] == 'catsbackup'` and the file is missing, `CommonErrors::fatal(COMMONERROR_FILENOTFOUND, ...)` (AttachmentsUI.php:100-107).
8. Fires the `ATTACHMENT_RETRIEVAL` hook: `if (!eval(Hooks::get('ATTACHMENT_RETRIEVAL'))) return;` (AttachmentsUI.php:110). A `FIXME` notes the desire to stream rather than redirect "depends on download preparer working" (AttachmentsUI.php:109).
9. MIME type: `$contentType = Attachments::fileMimeType($fileName);` (AttachmentsUI.php:113). `fileMimeType($filename)` (lib/Attachments.php:708-723) gets the extension via `FileUtility::getFileExtension`, scans `lib/mime.types` line-by-line for a matching extension, returns the first column, else `'application/octet-stream'` (lib/Attachments.php:710-722).
10. Opens the file read-only: `$fp = @fopen($filePath, 'r');`; on failure `CommonErrors::fatal(COMMONERROR_BADFIELDS, ...)` with an "offline" message (AttachmentsUI.php:116-124).
11. Emits headers (AttachmentsUI.php:126-131): `Content-Disposition: inline; filename="<fileName>"` (a comment notes `attachment` was the old default that forced download — AttachmentsUI.php:127), `Content-Type`, `Content-Length: filesize($filePath)`, `Pragma: no-cache`, `Expires: 0`.
12. Streams the file in `ATTACHMENT_BLOCK_SIZE` (80192-byte) chunks: `while (!feof($fp)) { print fread($fp, self::ATTACHMENT_BLOCK_SIZE); }` (AttachmentsUI.php:136-139), then `fclose($fp)` (AttachmentsUI.php:141) and `exit()` to prevent trailing output (AttachmentsUI.php:143-144).

**Local vs. remote:** This controller only reads from the **local** filesystem path `attachments/<dir>/<file>` (AttachmentsUI.php:97, 116). It does not itself perform any remote fetch. Remote-storage integration is provided by the `lib/Attachments.php` methods `forceAttachmentLocal`/`forceAttachmentRemote`/`forceRemoteDeleteAttachment` via hooks (see Hooks section); none of those are invoked by this module before streaming. The `ATTACHMENT_RETRIEVAL` hook (AttachmentsUI.php:110) is the extension point where a remote-storage plugin could materialize the file locally before the `fopen`.

## The `attachment` table

Defined in db/cats_schema.sql:84-108:

| Column | Type | Notes (cite) |
|--------|------|-------|
| `attachment_id` | int(11) AUTO_INCREMENT | PRIMARY KEY (cats_schema.sql:85, :102) |
| `data_item_id` | int(11) DEFAULT '0' | ID of the owning record (cats_schema.sql:86) |
| `data_item_type` | int(11) DEFAULT '0' | DATA_ITEM_* discriminator (cats_schema.sql:87) |
| `site_id` | int(11) DEFAULT '0' | owning site (cats_schema.sql:88) |
| `title` | varchar(128) | (cats_schema.sql:89) |
| `original_filename` | varchar(255) NOT NULL | uploaded name (cats_schema.sql:90) |
| `stored_filename` | varchar(255) NOT NULL | on-disk name; used to build `$filePath` (cats_schema.sql:91; AttachmentsUI.php:96) |
| `content_type` | varchar(255) | `'catsbackup'` triggers backup handling (cats_schema.sql:92; AttachmentsUI.php:100) |
| `resume` | int(1) NOT NULL DEFAULT '0' | is-resume flag (cats_schema.sql:93) |
| `text` | text | extracted resume text (cats_schema.sql:94) |
| `date_created` | datetime | (cats_schema.sql:95) |
| `date_modified` | datetime | (cats_schema.sql:96) |
| `profile_image` | int(1) DEFAULT '0' | (cats_schema.sql:97) |
| `directory_name` | varchar(64) | hashed for the retrieval check (cats_schema.sql:98; AttachmentsUI.php:86, :95) |
| `md5_sum` | varchar(40) NOT NULL | file md5 for de-dup (cats_schema.sql:99) |
| `file_size_kb` | int(11) DEFAULT '0' | (cats_schema.sql:100) |
| `md5_sum_text` | varchar(40) NOT NULL | md5 of extracted text (cats_schema.sql:101) |

Indexes: `IDX_type_id (data_item_type, data_item_id)`, `IDX_data_item_id`, `IDX_CANDIDATE_MD5_SUM (md5_sum)`, `IDX_site_file_size`, `IDX_site_file_size_created` (cats_schema.sql:103-107).

**DATA_ITEM_* discriminator** (`data_item_type`) — attachments attach to these record types (constants.php:57-61):

```php
define('DATA_ITEM_CANDIDATE',   100);
define('DATA_ITEM_COMPANY',     200);
define('DATA_ITEM_CONTACT',     300);
define('DATA_ITEM_JOBORDER',    400);
define('DATA_ITEM_BULKRESUME',  500);
```

(`DATA_ITEM_BULKRESUME` = 500 is also used by `Attachments::getBulkAttachmentsInfo`/`getBulkAttachments`, lib/Attachments.php:743, :779.)

## Upload whitelist (FileUtility)

The serving controller does not perform uploads, but stored filenames are sanitized at upload time by `FileUtility::makeSafeFilename($filename)` (lib/FileUtility.php:166). It applies a **whitelist** of extensions; anything not on the list gets `.txt` appended (neutralizing it):

```php
$GoodFileExtensions = array('bak', 'bmp', 'csv', 'doc', 'docx', 'heic', 'html', 'jpeg', 'jpg', 'msg', 'odg', 'odt', 'pages', 'pdf', 'png', 'ppt', 'pptx', 'rtf', 'tiff', 'txt', 'wpd', 'wps', 'xls', 'xlsx', 'xps');
if (!in_array($fileExtension, $GoodFileExtensions))
{
    $filename .= ".txt";
}
```
(lib/FileUtility.php:192-196)

The extension is extracted (lowercased) via `getFileExtension($filename)` (lib/FileUtility.php:328-339), called at lib/FileUtility.php:186. This whitelist is the upstream control referenced by doc 20.

## lib/ dependencies

- **lib/Attachments.php** (`include_once`, AttachmentsUI.php:31)
  - `__construct($siteID)` — lib/Attachments.php:50; called as `new Attachments(-1)` (AttachmentsUI.php:83).
  - `get($attachmentID, $verifySiteID = true)` — lib/Attachments.php:579; called with `false` to bypass site scoping (AttachmentsUI.php:84). Returns aliased columns and a `retrievalURL` (lib/Attachments.php:582-623).
  - `fileMimeType($filename)` (static) — lib/Attachments.php:708; called at AttachmentsUI.php:113.
  - Not called by this module but part of the attachment lifecycle: `add(...)` (lib/Attachments.php:73-76), `delete($attachmentID, $removeFile = true)` (lib/Attachments.php:304), `deleteAll` (lib/Attachments.php:377), `getAll($dataItemType, $dataItemID)` (lib/Attachments.php:513).
- **lib/CommonErrors.php** (`include_once`, AttachmentsUI.php:30) — `CommonErrors::fatal(...)` used at AttachmentsUI.php:76, :88, :102, :119.
- **lib/FileUtility.php** — `getFileExtension` (lib/FileUtility.php:328) used transitively by `fileMimeType`; `makeSafeFilename` whitelist (lib/FileUtility.php:166-203) governs stored extensions.
- **lib/UserInterface.php** (base class) — `getAction()` (lib/UserInterface.php:193), `isRequiredIDValid(...)` (lib/UserInterface.php:318), `_authenticationRequired` (lib/UserInterface.php:50, :179-181).
- **lib/Hooks.php** — `Hooks::get($hookName)` (lib/Hooks.php:52) returns concatenated hook code or `'return true;'` if none registered (lib/Hooks.php:54-71).

## Hooks fired

The hook mechanism: `Hooks::get('NAME')` returns a string of PHP that `eval()` runs; if no hooks are registered in `$_SESSION['hooks']` it returns `'return true;'` (lib/Hooks.php:52-71). The `if (!eval(...)) return;` idiom lets a registered hook short-circuit the handler by returning false.

In this module (modules/attachments/AttachmentsUI.php):
- **`ATTACHMENTS_HANDLE_REQUEST`** — `if (!eval(Hooks::get('ATTACHMENTS_HANDLE_REQUEST'))) return;` at the top of `handleRequest()` (AttachmentsUI.php:55).
- **`ATTACHMENT_RETRIEVAL`** — `if (!eval(Hooks::get('ATTACHMENT_RETRIEVAL'))) return;` immediately before MIME detection / streaming (AttachmentsUI.php:110). This is the documented extension point for remote storage / download preparation.

In the data layer (lib/Attachments.php), related hooks not fired by this module:
- **`FORCE_ATTACHMENT_LOCAL`** — `forceAttachmentLocal($attachmentID)` (lib/Attachments.php:173-176, hook at :175). Also fired in modules/settings/ajax/backup.php:285.
- **`FORCE_ATTACHMENT_REMOTE`** — `forceAttachmentRemote($attachmentID)` (lib/Attachments.php:190-193, hook at :192).
- **`FORCE_ATTACHMENT_DELETE`** — `forceRemoteDeleteAttachment($attachmentID)` (lib/Attachments.php:200-203, hook at :202).
- **`CREATE_ATTACHMENT_FINISHED`** — `if (!eval(Hooks::get('CREATE_ATTACHMENT_FINISHED'))) return;` at the end of the directory-creation step (lib/Attachments.php:1288).

## Source evidence

- modules/attachments/AttachmentsUI.php:30-31 — includes (CommonErrors, Attachments)
- modules/attachments/AttachmentsUI.php:33 — `class AttachmentsUI extends UserInterface`
- modules/attachments/AttachmentsUI.php:36 — `const ATTACHMENT_BLOCK_SIZE = 80192;`
- modules/attachments/AttachmentsUI.php:39-48 — constructor; `_authenticationRequired = true`
- modules/attachments/AttachmentsUI.php:51-66 — `handleRequest()` switch; `ATTACHMENTS_HANDLE_REQUEST` hook
- modules/attachments/AttachmentsUI.php:69-145 — `getAttachment()` full body
- modules/attachments/AttachmentsUI.php:86 — directoryNameHash authorization check
- modules/attachments/AttachmentsUI.php:110 — `ATTACHMENT_RETRIEVAL` hook
- modules/attachments/AttachmentsUI.php:113 — `Attachments::fileMimeType`
- modules/attachments/AttachmentsUI.php:126-144 — headers + chunked streaming + exit
- lib/Attachments.php:44-54 — class + constructor `__construct($siteID)`
- lib/Attachments.php:73-76 — `add(...)` signature
- lib/Attachments.php:173-203 — force-local/remote/delete hooks
- lib/Attachments.php:304 — `delete($attachmentID, $removeFile = true)`
- lib/Attachments.php:579-626 — `get($attachmentID, $verifySiteID = true)` + site condition + retrievalURL
- lib/Attachments.php:708-723 — `fileMimeType($filename)`
- lib/Attachments.php:1288 — `CREATE_ATTACHMENT_FINISHED` hook
- lib/FileUtility.php:166-203 — `makeSafeFilename` + `$GoodFileExtensions` whitelist
- lib/FileUtility.php:328-339 — `getFileExtension($filename)`
- lib/Hooks.php:52-72 — `Hooks::get($hookName)`
- lib/UserInterface.php:50, :179-181, :193, :318 — auth flag and helpers
- db/cats_schema.sql:84-108 — `attachment` table
- constants.php:57-61 — DATA_ITEM_* constants

## Unverified / open questions

- **Access-control gap (observed, not a claim about intent):** `getAttachment()` calls `get($attachmentID, false)` (AttachmentsUI.php:84), which disables `site_id` scoping in the query (lib/Attachments.php:601-604). The only gate is `md5($rs['directoryName']) == $_GET['directoryNameHash']` (AttachmentsUI.php:86). Whether knowing `attachment_id` + the directory-name md5 is intended as sufficient authorization (cross-site reachability) is not asserted by the source; flagged for review.
- The actual content of `$_SESSION['hooks']` (which hook code, if any, is registered for `ATTACHMENT_RETRIEVAL`, `ATTACHMENTS_HANDLE_REQUEST`, `FORCE_ATTACHMENT_*`, `CREATE_ATTACHMENT_FINISHED`) is not present in the open-source tree I read; `Hooks::get` defaults to `'return true;'` (lib/Hooks.php:54-57), i.e. no-op in this distribution.
- `lib/mime.types` contents were not opened; `fileMimeType` behavior depends on that file (lib/Attachments.php:712).
- The relationship between `makeSafeFilename`'s whitelist and "doc 20" is taken from the task brief; the doc-20 artifact itself was not inspected.
