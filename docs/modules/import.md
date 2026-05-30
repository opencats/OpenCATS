# Module: import

Source-derived design doc for the OpenCATS **import** module (bulk/CSV/resume import). Every claim is cited to a file+line that was opened. Anything not verifiable from source is listed under *Unverified / open questions*.

## Overview

The module is rooted at `modules/import/` (20 files: `Import.php`, `ImportUI.php`, `import.js`, `MassImport.css`, 14 `.tpl`, one `ajax/processMassImportItem.php`). It supports two distinct flows:

1. **Delimited (CSV / tab) import** of Candidates, Job Orders, Companies, Contacts — column-mapped, row-by-row INSERT, revertable for 7 days.
2. **Resume / bulk-resume import** — files dropped into an `upload/` dir are converted to text, optionally parsed into candidate fields, and either added as candidates or stored as searchable "bulk resume" attachments.

### Classes declared

`ImportUI` — the front controller, extends `UserInterface` (modules/import/ImportUI.php:48). Constructor (modules/import/ImportUI.php:53-61) sets `$this->_authenticationRequired = true`, `$this->_moduleDirectory = 'import'`, `$this->_moduleName = 'import'`, `$this->_subTabs = array()`. Class constant `const MAX_ERRORS = 100;` (modules/import/ImportUI.php:50) caps the number of per-row errors rendered.

`Import` — the import-record/data-access class (modules/import/Import.php:30). Constructor `public function __construct($siteID)` (modules/import/Import.php:36-40) stores `$_siteID` and gets the singleton `DatabaseConnection::getInstance()`. It owns the `import` bookkeeping table plus the `extra_field` / `extra_field_settings` (foreign / "extra" field) tables. Key methods (all cited below in *lib dependencies*): `add($table)` (modules/import/Import.php:49), `updateErrors($importID, $importErrors, $addedLines)` (86), `delete($importID)` (116), `get($importID)` (144), `getAll()` (171), `revert($tableName, $importID)` (203), `addForeignSettingUnique($type, $field, $importID)` (267), `addForeign($type, $data, $assocID, $importID)` (325).

`JobOrdersImport` — declared in the same file (modules/import/Import.php:368). Constructor at modules/import/Import.php:374-378; `public function add($dataNamed, $userID, $importID)` (389) resolves `company_id` by company name then INSERTs into `joborder` with `status='Draft'` (modules/import/Import.php:469) and `import_id` set.

`ImportUI.php` includes the other import-entity libs at the top (modules/import/ImportUI.php:42-45): `ImportUtility`, `CandidatesImport`, `CompaniesImport`, `ContactsImport`. Note: the contact entity class is named `ContactImport` (singular) — see lib/ContactsImport.php:5 and the instantiation at modules/import/ImportUI.php:1189.

### CSV vs resume bulk, briefly

- **CSV/tab** path: `import()` → `importSelectType()` → `importUploadFile()` → `onImport()` → `onImportDelimited()` (column-map screen) → POST back → `onImportFieldsDelimited()` (the actual INSERT loop, modules/import/ImportUI.php:757). Each successful run creates an `import` row so it can be reverted.
- **Resume bulk** path A (mass import / parser): `importSelectType` with `typeOfImport == 'resume'` calls `massImport()` (modules/import/ImportUI.php:392-396), a 4-step wizard that scans the per-site `massimport` upload dir, AJAX-converts each file to text (`massImportDocument()`), optionally parses, lets the user edit (`massImportEdit()`), then imports candidates / bulk-resume documents (step 4 → `getMassImportCandidates()`).
- **Resume bulk** path B (legacy `/upload/` scan): `showMassImport()` (modules/import/ImportUI.php:1307) walks `./upload/`, stores the file list in the session, and the JS progress bar (import.js:161 `importFile()`) repeatedly POSTs to the AJAX endpoint `import:processMassImportItem` which creates `DATA_ITEM_BULKRESUME` attachments.

## Action catalog

`getAction()` dispatch is in `handleRequest()` (modules/import/ImportUI.php:64-150). One row per switch case. "ACL guard" is the literal `getUserAccessLevel(...)` check inside the handler (UserInterface::getUserAccessLevel signature at lib/UserInterface.php:429); `(none)` means the handler performs no `getUserAccessLevel` check of its own.

| Action (`a=`) | ACL guard (literal) | Required level | Handler | lib calls | Template |
|---|---|---|---|---|---|
| `revert` (POST only) | `getUserAccessLevel('import.import') < ACCESS_LEVEL_EDIT` (ImportUI.php:157) | EDIT | `revert()` (155) | `Import::get`,`Import::revert`,`Import::delete` (170-181) | `ImportRecent.tpl` via `viewPending()` |
| `viewerrors` | (none) | (none) | `viewErrors()` (196) | `Import::get` (207) | `ImportRecent.tpl` via `viewPending()` |
| `viewpending` | (none) | (none) | `viewPending()` (230) | `Import::getAll` (233) | `ImportRecent.tpl` (245) or `Import1.tpl` if empty |
| `importSelectType` | (none) | (none) | `importSelectType()` (383) | — | `Import2.tpl` (404); or delegates to `massImport()`/`import()` |
| `importUploadFile` | (via `onImport`) | EDIT (in `onImport`) | `importUploadFile()` (411) → `onImport()` | — | (delegates) |
| `whatIsBulkResumes` | (none) | (none) | `whatIsBulkResumes()` (1297) | — | `BulkResumesHelp.tpl` (1300) |
| `showMassImport` | (none) | (none) | `showMassImport()` (1307) | `ImportUtility` not used here; raw `opendir` | `ImportResumesBulk.tpl` (1348) |
| `massImport` | `getUserAccessLevel('import.massImport') < ACCESS_LEVEL_EDIT` (ImportUI.php:1524) | EDIT | `massImport()` (1513) | `FileUtility::getUploadPath`, `ImportUtility::getDirectoryFiles` (1539-1540) | `MassImportStep{1..4}.tpl` wrapped in `MassImport.tpl` (1664-1670) |
| `massImportDocument` (AJAX) | (none) | (none) | `massImportDocument()` (1357) | `DocumentToText::convert/getString`, `ParseUtility::documentParse`, `DatabaseSearch::fulltextDecode` (1389-1434) | echoes `Ok`/`Fail` |
| `massImportEdit` | (none directly; calls `massImport(3)`) | (none here) | `massImportEdit()` (1444) | — | `MassImportEdit.tpl` (1510) |
| `importBulkResumes` (POST only) | `getUserAccessLevel('import.bulkResumes') < ACCESS_LEVEL_SA` (ImportUI.php:2119) | SA | `importBulkResumes()` (2113) | `Attachments::getBulkAttachments`, `DatabaseSearch::fulltextDecode` (2127-2152) | redirect to `m=import&a=massImport&step=2` (2159) |
| `deleteBulkResumes` (POST only) | `getUserAccessLevel('import.bulkResumes') < ACCESS_LEVEL_SA` (ImportUI.php:2086) | SA | `deleteBulkResumes()` (2080) | `Attachments::getBulkAttachments`,`Attachments::delete` (2094-2107) | `import()` → `Import1.tpl` |
| `import` / default | POST→`getUserAccessLevel('import.import') < ACCESS_LEVEL_EDIT` (in `onImport`, ImportUI.php:437); GET→(none) | EDIT (on POST) | POST→`onImport()` (435); GET→`import()` (360) | `Import::getAll`, `Attachments::getBulkAttachmentsInfo` (363-366) | `Import1.tpl` (377) |

Non-`switch` handlers reachable internally and their guards:
- `onImportFieldsDelimited()` (public, ImportUI.php:757): guard `getUserAccessLevel('import.import') < ACCESS_LEVEL_EDIT` (759). The "Add field to Extra Fields and Import" branch additionally requires `getUserAccessLevel('import.import') >= ACCESS_LEVEL_SA` (920).
- `onImportDelimited()` computes `$isSA = ($this->getUserAccessLevel('import.import') >= ACCESS_LEVEL_SA)` (733) purely to toggle the "foreign field" option in the map screen.
- `importUploadResume()` (424) sets `dataType='Resume'` then calls `onImport()`; it is referenced by `ImportResumesBulk.tpl` form action `a=importUploadResume` (ImportResumesBulk.tpl:60) but is **not** a case in `handleRequest()`.

Note: `ImportCommits.tpl` references `a=commit` (ImportCommits.tpl:46,75) but there is **no** `commit` case in `handleRequest()` (modules/import/ImportUI.php:64-150) — the template is orphaned.

## Per-action detail

### Upload (CSV: select type → upload → map)
- `import()` (ImportUI.php:360-378) loads existing imports via `Import::getAll()` and bulk-attachment counts via `Attachments::getBulkAttachmentsInfo()`; sets `pendingCommits` if any imports exist; fires `IMPORT2_SHOW` (373); displays `Import1.tpl`.
- `importSelectType()` (383-406): reads `typeOfImport`; empty → back to `import()`; `'resume'` → `massImport()`; otherwise fires `IMPORT_UPLOAD` (402) and shows `Import2.tpl` (the file-upload form).
- `importUploadFile()` (411-419) rewrites POST: `dataType='Text File'`, `importInto=typeOfImport`, `delimitedType=typeOfFile`, then calls `onImport()`.
- `onImport()` (435-602): guard EDIT (437); `set_time_limit(500)` unless safe_mode (442-450); `setImportTypes()` (452). If `$_POST['fileName']` is set the user already mapped columns → demo block (`isDemo()` fatal, 474-477), fires `IMPORT_ON_IMPORT_1`, dispatches `Text File` → `onImportFieldsDelimited()` (483-485). Otherwise validates `$_FILES['file']` (501-534), ensures `CATS_TEMP_DIR` (535-552), copies upload to a random `.tmp` name (`FileUtility::makeRandomFilename`, 557) inside `CATS_TEMP_DIR`, records the name in `$_SESSION['CATS']->validImportFileIDs[]` (584) to prevent file-id injection, fires `IMPORT_ON_IMPORT_3`, then `Text File` → `onImportDelimited($randomFile)` (591).

### Map columns
- `onImportDelimited($fileID)` (608-751): reads `delimitedType`/`importInto`/`dataType`; opens the temp file; reads header row with `fgetcsv` using `"\t"` for `tab` or `,`/`"` for `csv` (636-650); selects the per-destination field map (`candidatesTypes`/`jobOrdersTypes`/`contactsTypes`/`companiesTypes` from `setImportTypes()`); auto-matches header names (685-700); reads up to 20 sample rows (704-729); computes `$isSA` (733); displays `Import.tpl` (750). For Contacts it sets `contactsUploadNotice` (666) which drives the "generate companies" UI in `Import.tpl`.

### View pending / View errors
- `viewPending()` (230-249): `Import::getAll()`; if zero → `import()`, else fire `IMPORT_VIEW_PENDING` and display `ImportRecent.tpl`.
- `viewErrors()` (196-225): reads `$_GET['importID']`; `Import::get()`; fires `IMPORT_VIEW_ERRORS`; HTML-escapes stored `importErrors` (213) and re-displays via `viewPending()`.

### Revert
- `revert()` (155-190): guard EDIT (157); validates `importID`; `Import::get()` to fetch `moduleName`; `Import::revert(moduleName, importID)` (177) deletes the rows that carry that `import_id` from `extra_field_settings`, the target table (`company`/`contact`/`candidate`/`joborder`), and `extra_field`; if the table was `contact` it also deletes generated `company` rows by `import_id` (Import.php:242-256); then `Import::delete()` removes the bookkeeping row; fires `IMPORT_REVERT` (183); re-displays `viewPending()`.

### Add candidate / contact / client / joborder (the INSERT loop)
`onImportFieldsDelimited()` (757-1058) is the real importer:
- Guard EDIT (759); opens temp file; detects encoding via `mb_detect_encoding` honoring `IMPORT_FILE_ENCODING` if defined (795-802).
- Creates the bookkeeping row per destination: `Import::add('candidate'|'joborder'|'company'|'contact')` returning `$importID` (826-846).
- Builds per-column user preference from `$_POST['importType'.$fieldID]` (857-860).
- For each data row: `iconv` to UTF-8 (886-890); routes each field to either a CATS column, or an extra/"foreign" field (prefix `#` or preference `foreign`/`foreignAdded`). The foreign branch requires SA (920) and calls `Import::addForeignSettingUnique(DATA_ITEM_*, ...)` (930-942).
- Dispatches the row to one of:
  - `addToCandidates($dataFields,$dataNamed,$dataForeign,$importID)` (1074): splits `name` into first/last (1082-1085); requires first/last/company_id present else returns error (1088-1092); fires `IMPORT_ADD_CANDIDATE`; `CandidatesImport::add($dataNamed,$userID,$importID)` (1097-1098); then `addForeign(DATA_ITEM_CANDIDATE,...)`; fires `IMPORT_ADD_CANDIDATE_POST`.
  - `addToJobOrders(...)` (1115): requires `title` (1122-1125); fires `IMPORT_ADD_JOBORDER`; `JobOrdersImport::add(...)` (1129-1130); `addForeign(DATA_ITEM_JOBORDER,...)`; fires `IMPORT_ADD_JOBORDER_POST`.
  - `addToCompanies(...)` (1148): requires `name` (1155-1158); duplicate check via `Companies::companyByName` returns `'Duplicate entry.'` (1162-1166); fires `IMPORT_ADD_CLIENT`; `CompaniesImport::add(...)` (1170); `addForeign(DATA_ITEM_COMPANY,...)`; fires `IMPORT_ADD_CLIENT_POST`.
  - `addToContacts(...)` (1187): resolves company by name; if missing and `$_POST['generateCompanies']=='yes'` builds and inserts a company via `CompaniesImport::add` (fires `IMPORT_ADD_CONTACT_CLIENT`/`_POST`, 1224-1234) and returns sentinel `'newCompany'`; otherwise `'Invalid company name.'` (1239). Splits `name`; if no name and `unnamedContacts=='yes'` + generated company, sets first name `'nobody'` (1257-1259); fires `IMPORT_ADD_CONTACT`; `ContactImport::add(...)` (1275); `addForeign(DATA_ITEM_CONTACT,...)`; fires `IMPORT_ADD_CONTACT_POST`.
- A return of `''` or `'newCompany'` counts as success (989-997); other strings become collapsible error HTML, capped at `MAX_ERRORS=100` rows (998-1013).
- `Import::updateErrors($importID, $errorHtml, $totalImported)` (1026) persists the error blob + count. Builds a success message including "1 week to review … before changes become permanent" and Revert/View-Errors buttons (1042-1051); fires `IMPORT_ON_IMPORT_DELIMITED_10`; re-renders via `import()`.

`addForeign($dataTable,$data,$assocID,$importID)` (1063-1069) fires `IMPORT_ADD_FOREIGN` then delegates to `Import::addForeign()`.

### Mass-resume wizard internals
- `massImport($step=1)` (1513): requires login; guard `import.massImport < ACCESS_LEVEL_EDIT` (1524). Step 1 lists files in `FileUtility::getUploadPath($siteID,'massimport')` and computes the on-disk upload path. Step 2 builds JS `addDocument(...)` calls per file and fires `MASS_IMPORT_SPACE_CHECK` (1585). Step 3 reviews `getMassImportDocuments()`. Step 4 calls `getMassImportCandidates()` and clears `CATS_PARSE_TEMP`. Step 99 deletes all upload files (1642-1656). Renders `MassImportStep{step}.tpl` into `MassImport.tpl` (1663-1670).
- `massImportDocument()` (1357, AJAX): per-file convert+parse; on `DocumentToText::convert` failure records `success=false` and echoes `Fail` (1400-1406); else stores decoded contents and (if `LicenseUtility::isParsingEnabled()`) `ParseUtility::documentParse` results into `$_SESSION['CATS_PARSE_TEMP']`, echoes `Ok`.
- `getMassImportDocuments()` (1981-2052) normalizes parsed fields (`first_name`,`last_name`,`us_address`,`city`,`state`,`zip_code`,`email_address`,`phone_number`,…) into the `firstName`/`lastName`/… shape.
- `getMassImportCandidates()` (1673-1979) dedups (email match; lastName+phone; lastName+zip), adds unique candidates via `Candidates::add(...)` (1789), backdates `date_created`/`date_modified` to file ctime, attaches the resume via `AttachmentCreator::createFromFile(DATA_ITEM_CANDIDATE,...)` (1834); otherwise stores as `DATA_ITEM_BULKRESUME` (1903). Handles `_BulkResume_*.txt` rescans (1877, 1939). `splitAddressForImport()` helper at 2054.
- `importBulkResumes()` (2113) writes `_BulkResume_<name>.txt` files (fulltext-decoded) into the massimport upload dir for each existing bulk attachment, then redirects to step 2. `deleteBulkResumes()` (2080) deletes all bulk attachments.

## Resume-to-text pipeline (DocumentToText + external binaries)

`DocumentToText::convert($fileName, $documentType)` (lib/DocumentToText.php:72) dispatches by document-type flag and shells out to external binaries via `escapeshellarg(realpath($fileName))` (lib/DocumentToText.php:101) and `_executeCommand()` (which uses `@exec`, lib/DocumentToText.php:378). Mapping:
- `DOCUMENT_TYPE_DOC` → `ANTIWORD_PATH` with `-m ANTIWORD_MAP` (lib/DocumentToText.php:107-116); empty path → error "The DOC format has not been configured." A leading `{\rtf` header reclassifies DOC as RTF (lib/DocumentToText.php:83-96).
- `DOCUMENT_TYPE_PDF` → `PDFTOTEXT_PATH -layout … -` (lib/DocumentToText.php:118-128).
- `DOCUMENT_TYPE_HTML` → `HTML2TEXT_PATH -nobs …` (piped via `TYPE` on Windows) (lib/DocumentToText.php:130-148).
- `DOCUMENT_TYPE_TEXT` → `_readTextFile()` (lib/DocumentToText.php:150-151).
- `DOCUMENT_TYPE_RTF` → internal `rtf2text()` (154-163); `DOCUMENT_TYPE_ODT` → `odt2text()` (165-175); `DOCUMENT_TYPE_DOCX` → `docx2text()` (177-187, ZIP+XML).
- Unknown → error (189-193).

Binary output is `rtrim`-ed, joined with `\n`, and `iconv`-ed from `ISO-8859-1` to UTF-8 (lib/DocumentToText.php:204-215). Non-zero return code → `convert` returns false (220-224). Results are read back with `getString()` (240) / `getArray()` (252); errors via `isError()`/`getError()` (284/294).

`ImportUtility::getDirectoryFiles($dirName)` (lib/ImportUtility.php:41) recursively stats a directory, skips files ≤ 50 bytes (67), skips files whose `FileUtility::getDocumentType` is `DOCUMENT_TYPE_UNKNOWN` (72), and returns `realName/name/size/ext/type/cTime/parsed` records (74-82).

## Templates

| Template | Rendered by | Notes |
|---|---|---|
| `Import1.tpl` | `import()` (377) | Step-1 type picker; shows bulk-resume rescan/delete panel only if `numBulkAttachments>0 && import.import >= ACCESS_LEVEL_SA` (Import1.tpl:95) |
| `Import2.tpl` | `importSelectType()` (404) | File upload + CSV/tab choice; posts to `a=importUploadFile` |
| `Import.tpl` | `onImportDelimited()` (750) | Column-mapping grid; "Add field to Extra Fields" option gated on `$this->isSA` (Import.tpl:206); contacts "generate companies" block (138-176) |
| `ImportRecent.tpl` | `viewPending()` (245) | Lists recent imports w/ Revert + View Errors |
| `ImportCommits.tpl` | (none — orphaned) | References non-existent `a=commit` (lines 46,75) |
| `BulkResumesHelp.tpl` | `whatIsBulkResumes()` (1300) | Modal help |
| `ImportResumesBulk.tpl` | `showMassImport()` (1348) | `/upload/` scan + progress bar; posts to `a=importUploadResume` |
| `MassImport.tpl` | `massImport()` (1670) | 4-step wrapper, includes `MassImport.css` |
| `MassImportStep1.tpl` | step 1 | Upload-queue + instructions; `LicenseUtility::isParsingEnabled/isProfessional` branches |
| `MassImportStep2.tpl` | step 2 | Progress bar; emits `this->js` `addDocument()` calls + `startDocumentParsing()` |
| `MassImportStep3.tpl` | step 3 | Review grid; `goStep4()` button; local `strlimit()` helper |
| `MassImportStep4.tpl` | step 4 | Counts of imported candidates/documents/failed/duplicates |
| `MassImportEdit.tpl` | `massImportEdit()` (1510) | Per-document edit form; loads external `http://resfly.com/js/resumeParserValidation.js` (line 5) |
| `Error.tpl` / `ErrorModal.tpl` | (not referenced in ImportUI.php) | Generic fatal-error templates |
| `MassImport.css` | linked by `MassImport.tpl`/`MassImportEdit.tpl` | Styling only |

## JavaScript

`modules/import/import.js`:
- `evauluateImportDataType()` (36), `registerImportDataType()` (76) — show/hide data-type rows (legacy ACT-style importer).
- `evaluateFieldSelection(theID)` (53), `checkField(...)` (81) — toggle the "import into" sub-select and validate that a `company_id` column exists for contacts (called from `Import.tpl:90`).
- `showSampleData`/`hideSampleData` (65/71), `showErrorId`/`hideErrorId` (116/123) — sample-data and collapsible error toggles (error IDs emitted by `onImportFieldsDelimited`, ImportUI.php:1001-1004).
- `evaluateUnnamedContacts()` (130) — toggles the "name unnamed contacts" select.
- `startMassImport()` (145) → `importFile()` (161): the `/upload/` bulk-resume progress loop. `importFile()` does `AJAX_callCATSFunction(http, "import:processMassImportItem", ...)` (222-230) and parses a CSV `dups,success,processed` response (185-203). `finishImportNotice()` (233) and `setProgress()` (254) drive the UI.

The step-2 parser progress bar uses `addDocument(...)` / `startDocumentParsing()` / `setProgressBar(...)` from `js/massImport.js` (referenced by `MassImport.tpl:2`), populated by the server-generated `$this->js` (MassImportStep2.tpl:38). `addDocument` ultimately drives AJAX GETs to `a=massImportDocument` (handled at ImportUI.php:1357). *(massImport.js lives outside modules/import/ and was not opened — see Unverified.)*

## lib/ dependencies (cited)

- `Import` (modules/import/Import.php:30) — `add` (49, INSERT into `import`), `updateErrors` (86), `delete` (116), `get` (144), `getAll` (171, 7-day window: `TO_DAYS(date_created) > TO_DAYS(DATE_SUB(NOW(), INTERVAL 7 DAY))`, lines 187-188), `revert` (203), `addForeignSettingUnique` (267, INSERTs into `extra_field_settings`), `addForeign` (325, batch INSERT into `extra_field`).
- `JobOrdersImport` (modules/import/Import.php:368) — `add` (389): looks up `company_id` by name (391-413), forces `status='Draft'`, sets `import_id`.
- `ImportableEntity` (abstract) (lib/ImportableEntity.php:3) — `abstract protected function add($dataNamed,$userID,$importID)` (8); `public function __construct($siteID)` (10); `public function prepareData($dataNamed)` (16) builds `dataColumns` + `makeQueryStringOrNULL`-escaped `data`.
- `CandidatesImport extends ImportableEntity` (lib/CandidatesImport.php:5) — `add` (20): INSERT into `candidate` with `can_relocate=0`, `entered_by=owner=$userID`, `import_id`.
- `CompaniesImport extends ImportableEntity` (lib/CompaniesImport.php:5) — `add` (22): INSERT into `company` with `import_id`.
- `ContactImport extends ImportableEntity` (lib/ContactsImport.php:5) — `add` (20): INSERT into `contact` with `import_id`. (Class is singular `ContactImport`; instantiated at ImportUI.php:1189.)
- `ImportUtility` (lib/ImportUtility.php:38) — static `getDirectoryFiles($dirName)` (41).
- `DocumentToText` (lib/DocumentToText.php:41) — `getDocumentType` (60), `convert` (72), `getString` (240), `getArray` (252), `getReturnCode` (263), `getRawOutput` (274), `isError` (284), `getError` (294); private `_readTextFile` (323), `_executeCommand` (349), `odt2text` (388), `docx2text` (393), `rtf2text` (433).
- Other libs included at ImportUI.php:30-45 and used: `Companies` (`companyByName`, ImportUI.php:1162/1198), `Attachments` (`getBulkAttachmentsInfo`/`getBulkAttachments`/`delete`, ImportUI.php:366/1880/1951), `AttachmentCreator` (`createFromFile`, ImportUI.php:1834/1902 and processMassImportItem.php:68), `FileUtility` (`makeRandomFilename`/`getUploadPath`/`getErrorMessage`/`isUploadFileSafe`/`getDocumentType`), `ParseUtility` (`documentParse`, ImportUI.php:1431), `DatabaseSearch` (`fulltextDecode`, ImportUI.php:1410/2152), `LicenseUtility` (`isParsingEnabled`, ImportUI.php:1391), `Candidates` (`add`, ImportUI.php:1789).
- AJAX endpoint `modules/import/ajax/processMassImportItem.php` — `new SecureAJAXInterface()` (33), POST-only (35), pops up to 50 files per call from `$_SESSION['CATS']->massImportFiles` (56-63), `AttachmentCreator::createFromFile(DATA_ITEM_BULKRESUME,...)` (68), echoes `dups,success,processed` (92).

## Hooks fired (keys + cites)

All via `eval(Hooks::get('<KEY>'))` in ImportUI.php:
- `IMPORT_REVERT` (183)
- `IMPORT_VIEW_ERRORS` (209)
- `IMPORT_VIEW_PENDING` (241)
- `IMPORT_TYPES_2` (321)
- `IMPORT2_SHOW` (373)
- `IMPORT_UPLOAD` (402)
- `IMPORT_ON_IMPORT_1` (479), `IMPORT_ON_IMPORT_2` (499), `IMPORT_ON_IMPORT_3` (586)
- `IMPORT_ON_IMPORT_DELIMITED_1` (632) … `_10` (1053) — at 632, 652, 711, 735, 804, 822, 868, 925, 962, 1053
- `IMPORT_ADD_FOREIGN` (1065)
- `IMPORT_ADD_CANDIDATE` (1095), `IMPORT_ADD_CANDIDATE_POST` (1107)
- `IMPORT_ADD_JOBORDER` (1127), `IMPORT_ADD_JOBORDER_POST` (1139)
- `IMPORT_ADD_CLIENT` (1168), `IMPORT_ADD_CLIENT_POST` (1179)
- `IMPORT_ADD_CONTACT_CLIENT` (1224), `IMPORT_ADD_CONTACT_CLIENT_POST` (1234)
- `IMPORT_ADD_CONTACT` (1273), `IMPORT_ADD_CONTACT_POST` (1284)
- `MASS_IMPORT_SPACE_CHECK` (1585)
- `IMPORT_NOTIFY_DEV` — present but **commented out** (1633), so never fires.

## Source evidence

Files opened in full: `modules/import/ImportUI.php` (2164 lines), `modules/import/Import.php`, all `modules/import/*.tpl` (Import1, Import2, Import, ImportRecent, ImportCommits, BulkResumesHelp, ImportResumesBulk, MassImport, MassImportStep1-4, MassImportEdit head, Error, ErrorModal), `modules/import/import.js`, `modules/import/ajax/processMassImportItem.php`, `lib/ImportUtility.php`, `lib/ImportableEntity.php`, `lib/CandidatesImport.php`, `lib/CompaniesImport.php`, `lib/ContactsImport.php`, and `lib/DocumentToText.php` lines 41-300. ACL signature verified at `lib/UserInterface.php:429`.

## Unverified / open questions

- **ACL constant numeric values** (`ACCESS_LEVEL_EDIT`, `ACCESS_LEVEL_SA`) and the resolution of secured-object names `import.import` / `import.massImport` / `import.bulkResumes` were not opened (no definition found in `lib/Authorization.php`/`lib/ModuleAccess.php` via grep). Required levels above are taken verbatim from the literal comparison operators in the guards, not from a permissions table.
- `js/massImport.js` (the parser progress-bar / `addDocument` / `goStep4` / `gridBrowse` functions referenced by `MassImport.tpl` and `MassImportStep{2,3}.tpl`) lives outside `modules/import/` and was not opened.
- `MassImportEdit.tpl` was read only through line 40 (header + start of form); the body field list is inferred from the corresponding POST keys handled in `massImportEdit()` (ImportUI.php:1470-1491).
- The AJAX route string `import:processMassImportItem` (import.js:224) maps to `modules/import/ajax/processMassImportItem.php` by convention; the dispatcher (`AJAX_callCATSFunction` / `SecureAJAXInterface` routing) was not opened.
- `Error.tpl` / `ErrorModal.tpl` are present in the module but no `display()` call referencing them was found in `ImportUI.php`; they are presumed generic fallbacks.
- The `addToJobOrders` validation requires only `title` (ImportUI.php:1122), yet `JobOrdersImport::add` dereferences `$dataNamed['openings']` and `$dataNamed['company']` (Import.php:401,470) — behavior when those columns are unmapped was not exercised/verified.
