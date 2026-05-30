# Module: install

## Overview

The install module lives in `modules/install/` but its controller class is **not** named `Install` — it is declared as:

```php
class CATSUI extends UserInterface
```

(`modules/install/CATSUI.php:30`). The class name (`CATSUI`) differs from the module name (`install`); the framework loads the module by directory, so the class identity is decoupled from the directory name.

The constructor is minimal (`modules/install/CATSUI.php:32-40`):

```php
public function __construct()
{
    parent::__construct();

    $this->_authenticationRequired = false;
    $this->_moduleDirectory = 'install';
    $this->_moduleName = 'install';
    $this->_schema = CATSSchema::get();
}
```

Key facts:
- `_authenticationRequired = false` (`modules/install/CATSUI.php:36`). The base class default is `true` (`lib/UserInterface.php:50`), so this module explicitly opts out of login. `getModuleRequiresAuthentication()` reads this flag (`lib/UserInterface.php:179-181`).
- `_moduleDirectory` and `_moduleName` are both `'install'` (`modules/install/CATSUI.php:37-38`).
- `$this->_schema = CATSSchema::get();` (`modules/install/CATSUI.php:39`) — `CATSSchema` is the class defined in `modules/install/Schema.php:29`, with a static `get()` at `Schema.php:31`. `Schema.php` is included at `modules/install/CATSUI.php:28`.
- **`handleRequest()` is empty** (`modules/install/CATSUI.php:42-44`). The controller does no request handling. All actual installer logic is in the AJAX endpoints under `modules/install/ajax/` (primarily `ui.php`), driven from the root-level `installwizard.php` page. The `CATSUI` controller is effectively a vestigial shell.

## How the not-installed gate works

The presence/absence of a file literally named `INSTALL_BLOCK` in the application root is the install gate. There are three independent enforcement points:

1. **Front controller gate (`index.php:44-48`):**
   ```php
   if (!file_exists('INSTALL_BLOCK') && !isset($_POST['performMaintenence']))
   {
       include(LEGACY_ROOT . '/modules/install/notinstalled.php');
       die();
   }
   ```
   When `INSTALL_BLOCK` is absent (i.e. not yet installed) and this is not a maintenance postback, `index.php` includes `modules/install/notinstalled.php` and dies before bootstrapping the rest of the app. `notinstalled.php` is a static HTML page that tells the user OpenCATS has not been installed and links to the Installation Wizard:
   ```php
   <p>OpenCATS has not yet been installed, or a previous installation was not completed.</p>
   <p>Please visit the <a href="installwizard.php">Installation Wizard</a> to continue.</p>
   ```
   (`modules/install/notinstalled.php:22-23`). It pulls in `config.php`, `constants.php`, `lib/TemplateUtility.php` and references `js/lib.js`, `js/install.js`, and `modules/install/install.css` (`notinstalled.php:3-11`).

   Note the `performMaintenence` POST escape hatch: `modules/install/ajax/maint.php` sets `$maintPage = true;` and `include_once('index.php')` (`maint.php:53-55`), and the installer JS posts `performMaintenence=yes` (`js/install.js:134`) so that `index.php` continues past this gate during maintenance.

2. **AJAX router gate (`ajax.php:95-118`):** When the installer is active (`$installerActive = (!file_exists('INSTALL_BLOCK'));`, `ajax.php:95`), the router only permits AJAX functions whose module prefix is `install`; any other module is rejected with `Installer is active. Only installer AJAX actions are allowed.` (`ajax.php:96-118`). The AJAX function string `install:ui` is split on `:` into module + function and dispatched to `modules/install/ajax/ui.php` (`ajax.php:128-134`).

3. **Installer self-lock (`modules/install/ajax/ui.php:62-70`):** When `INSTALL_BLOCK` *does* exist, `ui.php` refuses every action and renders the `installLocked` block instead:
   ```php
   if (file_exists('INSTALL_BLOCK'))
   {
       echo '<script ...>setActiveStep(1); showTextBlock(\'installLocked\');</script>';
       die();
   }
   ```

So: no `INSTALL_BLOCK` => not installed => wizard runnable; `INSTALL_BLOCK` present => installed => wizard locked. The file is written at the end of a successful install (`ui.php:1010-1013`).

## Action catalog

The installer's action dispatch is **not** in `CATSUI.php` (whose `handleRequest()` is empty). It is the `switch ($action)` in `modules/install/ajax/ui.php:72`, where `$action = $_REQUEST['a']` (`ui.php:59`) and `$_REQUEST = $_POST` (`ui.php:50`; POST-only, enforced at `ui.php:45-48`). None of these `ui.php` cases call `getUserAccessLevel(...)`/`getAccessLevel(...)` — there are **no ACL guards inside `ui.php`**; access is gated only by the `INSTALL_BLOCK` checks above (`ui.php:62`, `ajax.php:95-118`). The two reindex/migration endpoints in the same `ajax/` dir *do* have ACL guards (last two rows).

| Action (`a=`) | ACL guard | Handler (file:line) | lib / helper calls | Template / UI block shown |
|---|---|---|---|---|
| `startInstall` | none | `ui.php:74-81` | — | `startInstall`; appends `a=installTest` |
| `installTest` | none | `ui.php:83-117` | `InstallationTests::runInstallerTests()` | `testPassed` / `testWarning` / `testFailed` / `testFailedWarning` |
| `databaseConnectivity` | none | `ui.php:119-172` | `CATSUtility::changeConfigSetting()` | `databaseConnectivity`; appends `a=testDatabaseConnectivity` |
| `testDatabaseConnectivity` | none | `ui.php:266-287` | `InstallationTests::checkMySQL()` | `MySQLTestPassed` / `MySQLTestFailed` |
| `detectRevision` | none | `ui.php:599-675` | `MySQLConnect()`, `MySQLQuery()` | `emptyDatabase` / `catsUpToDate` / `unknownDataInDatabase` / `databaseUpgrade` |
| `queryResetDatabase` | none | `ui.php:677-679` | — | `queryResetDatabase` |
| `resetDatabase` | none | `ui.php:681-697` | `MySQLConnect()`, `MySQLQuery('DROP TABLE ...')` | re-populates `a=detectRevision` or `a=selectDBType` |
| `selectDBType` | none | `ui.php:699-725` | — | `installingComponents` / `queryInstallDemo` / `queryInstallBackup` |
| `doInstallEmptyDatabase` | none | `ui.php:791-815` | `MySQLConnect()`, `changeConfigSetting('ENABLE_DEMO_MODE','false')`, `MySQLQueryMultiple()` over `db/cats_schema.sql` | proceeds to `a=resumeParsing` |
| `onLoadDemoData` | none | `ui.php:817-879` | `lib/FileCompressor.php` `ZipFileExtractor`, `MySQLQueryMultiple()` over `db/cats_testdata.bak` | proceeds to `a=upgradeCats` |
| `restoreFromBackup` | none | `ui.php:727-785` | `lib/FileCompressor.php` `ZipFileExtractor` over `./restore/catsbackup.bak`, `MySQLQueryMultiple()` | proceeds to `a=upgradeCats` |
| `doDeleteBackup` | none | `ui.php:787-789` | — | re-populates `a=detectRevision` |
| `upgradeCats` | none | `ui.php:881-968` | `MySQLConnect()`, `MySQLQueryMultiple()` over staged `db/upgrade-*.sql` files | proceeds to `a=resumeParsing` |
| `resumeParsing` | none | `ui.php:289-413` | reads `ANTIWORD_PATH`/`PDFTOTEXT_PATH`/`HTML2TEXT_PATH`/`UNRTF_PATH`; `lib/SystemUtility.php` `SystemUtility::isWindows()` | `resumeParsing` (step 4) |
| `testResumeParsing` | none | `ui.php:415-434` | `CATSUtility::changeConfigSetting()` for the 4 binary paths | `resumeParsing`; appends `a=testResumeParsing2` |
| `testResumeParsing2` | none | `ui.php:436-467` | `InstallationTests::checkAntiword()/checkPdftotext()/checkHtml2text()/checkUnrtf()` | `testFailed` / `testPassedParsing` |
| `mailSettings` | none | `ui.php:174-203` | `MySQLConnect()`, `MySQLQuery()` settings lookup | `mailSettings` (step 5) |
| `setMailSettings` | none | `ui.php:205-264` | `CATSUtility::changeConfigSetting()` for MAIL_* ; stores `$_SESSION['fromAddressInstaller']` | `detectingOptional`; schedules `a=optionalComponents` |
| `optionalComponents` | none | `ui.php:469-525` | `MySQLConnect()`, `initializeOptionalComponents()` | `pickOptionalComponents` (step 6) |
| `setupOptional` | none | `ui.php:527-597` | `initializeOptionalComponents()`, `eval()` of component install/remove code, `changeConfigSetting('OFFSET_GMT', ...)`; stores tz/dateFormat/phone in `$_SESSION` | `installingComponentsMaint`; schedules `a=maint` |
| `maint` | none | `ui.php:970-988` | clears `$_SESSION['CATS']`/`['modules']` | `installingComponentsMaint`; calls `Installpage_maint()` (`js/install.js:102`) which posts to `install:maint` |
| `reindexResumes` | none | `ui.php:990-995` | — | `installingComponentsMaintResume`; populates `a=onReindexResumes` |
| `onReindexResumes` | none | `ui.php:997-1004` | `include modules/install/ajax/attachmentsReindex.php` | proceeds to `a=maintComplete` |
| `maintComplete` | none | `ui.php:1006-1090` | `MySQLConnect()`, **writes `INSTALL_BLOCK`**, settings/site UPDATE/INSERT queries | `installCompleteDemo` / `installCompleteProd` (step 7) |
| `loginCATS` | none | `ui.php:1092-1106` | `MySQLConnect()`, `MySQLQuery()` admin lookup | redirects browser to `index.php` |
| *(default)* | none | `ui.php:1108-1110` | — | `die('Invalid action.')` |
| `install:maint` (separate endpoint) | none (delegates to `index.php` via `performMaintenence`) | `modules/install/ajax/maint.php` | sets `$maintPage=true; include_once('index.php')` (`maint.php:53-55`) | — |
| `install:attachmentsReindex` (separate endpoint, also included by `onReindexResumes`) | `getAccessLevel(ACL::SECOBJ_ROOT) < ACCESS_LEVEL_SA` **only when `INSTALL_BLOCK` exists** | `modules/install/ajax/attachmentsReindex.php:70-73` | `lib/Attachments.php`, `DatabaseConnection`, `DocumentToText` | — |
| `install:attachmentsToThreeDirectory` (separate endpoint) | `getAccessLevel(ACL::SECOBJ_ROOT) < ACCESS_LEVEL_ROOT` | `modules/install/ajax/attachmentsToThreeDirectory.php:53-56` | `SecureAJAXInterface`, `DatabaseConnection` | — |

## Per-action / step detail

The wizard UI is the static page `installwizard.php`. It declares 7 steps (`installwizard.php:67` `maxSteps = 7`; step labels `installwizard.php:70-90`) and bootstraps with `Installpage_populate('a=startInstall')` (`installwizard.php:568`). Every step is an AJAX round-trip to `install:ui`; the response is HTML containing `<script>` that calls `setActiveStep()` / `showTextBlock()` to swap the visible block (`js/install.js:33-62`). `Installpage_populate` replaces the content of `subFormBlock` and executes embedded JS (`js/install.js:64-100`); `Installpage_append` appends (`js/install.js:144-180`).

- **Step 1 — System Check.** `startInstall` (`ui.php:74-81`) shows the welcome block and appends `a=installTest`. `installTest` (`ui.php:83-117`) runs `InstallationTests::runInstallerTests()` (`lib/InstallationTests.php:67-140`), which emits `<tr>` rows and toggles `$result`/`$warningsOccurred` globals. Based on those it shows `testPassed`/`testWarning`/`testFailed`/`testFailedWarning`. The "Next" button in `testPassed` moves to `a=databaseConnectivity` (`installwizard.php:519`).
  - `runInstallerTests` checks: PHP version, register_globals, MySQLi, session, PCRE, CType, GD2, LDAP, SOAP, Zip extensions, attachments dir, config writable, directory writable (`InstallationTests.php:76-139`). Several are warnings (GD/LDAP/SOAP/Zip/register_globals set `$GLOBALS['warningsOccurred']`).

- **Step 2 — Database Connectivity.** `databaseConnectivity` (`ui.php:119-172`): if posted `user`/`pass`/`host`/`name`, persists them via `CATSUtility::changeConfigSetting('DATABASE_*', var_export(...))` (`ui.php:127-143`) then appends `a=testDatabaseConnectivity`; otherwise pre-fills the form from current `DATABASE_*` constants (`ui.php:162-171`). `testDatabaseConnectivity` (`ui.php:266-287`) runs `InstallationTests::checkMySQL(DATABASE_HOST, DATABASE_USER, DATABASE_PASS, DATABASE_NAME)` (`InstallationTests.php:367-488`, which connects, checks version >= 4.1.0, selects DB, and exercises CREATE/INSERT/UPDATE/DELETE/DROP on a `testtable`). On success shows `MySQLTestPassed` whose Next goes to `a=detectRevision` (`installwizard.php:552`).

- **Step 3 — Loading Data.** `detectRevision` (`ui.php:599-675`) connects (`MySQLConnect()`) and branches on the existing table set:
  - 0 tables => `emptyDatabase` block (`ui.php:604-611`).
  - `history` table present => `catsUpToDate` block (`ui.php:642-652`).
  - Recognized old CATS layouts (0.5.0 / 0.5.1-0.5.2 / 0.5.5 / 0.6.x) => `databaseUpgrade` block with the detected version (`ui.php:626-641,667-674`).
  - Otherwise (unknown tables) => `unknownDataInDatabase`, listing table names (`ui.php:654-665`).
  - `selectDBType` (`ui.php:699-725`) routes `empty` -> `a=doInstallEmptyDatabase`, `demo` -> `queryInstallDemo`, `restore` -> `queryInstallBackup`.
  - `resetDatabase` (`ui.php:681-697`) `DROP TABLE`s every table then re-enters `detectRevision` or `selectDBType`.
  - `doInstallEmptyDatabase` (`ui.php:791-815`) loads `db/cats_schema.sql` via `MySQLQueryMultiple($schema, ";\n")` and, if `history` still missing, applies `db/upgrade-0.6.x-0.7.0.sql`, then -> `a=resumeParsing`.
  - `onLoadDemoData` (`ui.php:817-879`) sets `ENABLE_DEMO_MODE=true`, extracts `db/cats_testdata.bak` via `ZipFileExtractor` (executing embedded `db/catsbackup.sql.*` with delimiter `((ENDOFQUERY))`), then -> `a=upgradeCats`.
  - `restoreFromBackup` (`ui.php:727-785`) extracts `./restore/catsbackup.bak` the same way (sets `ENABLE_DEMO_MODE=false`), then -> `a=upgradeCats`.
  - `upgradeCats` (`ui.php:881-968`) determines revision from table/column presence and applies the chain of `db/upgrade-*.sql` files (and `db/upgrade-zipcodes.sql`), then -> `a=resumeParsing`.

- **Step 4 — Setup Resume Indexing.** `resumeParsing` (`ui.php:289-413`) pre-fills the four converter paths from `ANTIWORD_PATH`/`PDFTOTEXT_PATH`/`HTML2TEXT_PATH`/`UNRTF_PATH`, applying a Windows->UNIX default-path swap using `SystemUtility::isWindows()` (`lib/SystemUtility.php`, included at `ui.php:306` etc.). `testResumeParsing` (`ui.php:415-434`) saves the four paths via `changeConfigSetting()` and appends `a=testResumeParsing2`. `testResumeParsing2` (`ui.php:436-467`) calls `InstallationTests::checkAntiword/checkPdftotext/checkHtml2text/checkUnrtf` (each converts a file under `modules/install/testdocs/` and checks for `"This is a test document."` — `InstallationTests.php:501-698`), shows `testFailed` or `testPassedParsing`. This step is skippable directly to `a=mailSettings` (`installwizard.php:227`).

- **Step 5 — Mail Settings.** `mailSettings` (`ui.php:174-203`) pre-fills the mail form from `MAIL_*` constants and the `settings.fromAddress` row. `setMailSettings` (`ui.php:205-264`) validates the from-address (>= 4 chars), persists `MAIL_MAILER`, `MAIL_SMTP_AUTH`, `MAIL_SENDMAIL_PATH`, `MAIL_SMTP_HOST/PORT/USER/PASS` via `changeConfigSetting()`, stores `$_SESSION['fromAddressInstaller']`, then shows `detectingOptional` and schedules `a=optionalComponents` after 5s.

- **Step 6 — Loading Extras.** `optionalComponents` (`ui.php:469-525`) connects, runs `initializeOptionalComponents()` (`ui.php:1199-1217`, which includes `modules/install/OptionalComponents.php` and `eval`s each component's `detectCode`), detects current date format from `site.date_format_ddmmyy`, and renders the `pickOptionalComponents` block (timezone via `TemplateUtility::printTimeZoneSelect`, date format, phone country code, and a per-component install/do-not-install table). `setupOptional` (`ui.php:527-597`) saves `OFFSET_GMT`, stores timezone/dateFormat/phone-code in session, and for each listed component `eval`s its `installCode` or `removeCode` (`ui.php:580,587`), then shows `installingComponentsMaint` and schedules `a=maint`.
  - Optional components are defined in `modules/install/OptionalComponents.php`; the only shipped one is `usZipCodes` (`OptionalComponents.php:30-52`), whose `installCode` loads `db/upgrade-zipcodes.sql` and sets `US_ZIPS_ENABLED=true`.

- **Step 7 — Finishing.** `maint` (`ui.php:970-988`) clears the CATS session objects and triggers `Installpage_maint()` (`js/install.js:102-142`), which POSTs to `install:maint` with `performMaintenence=yes`. `maint.php` then sets `$maintPage=true` and includes `index.php` (`maint.php:53-55`); the front-controller `performMaintenence` exception (`index.php:44`) lets the migration logic run. When the maint response contains no `setProgressUpdating` (i.e. migrations done — `setProgressUpdating` is defined at `js/install.js:220`), the JS calls `a=reindexResumes` (`js/install.js:122-124`). `reindexResumes` -> `onReindexResumes` (`ui.php:990-1004`) includes `attachmentsReindex.php` then -> `a=maintComplete`. `maintComplete` (`ui.php:1006-1090`) **writes the `INSTALL_BLOCK` file** (`ui.php:1010-1013`), writes `fromAddress`/`configured` settings, site date-format/time-zone/phone-country-code, clears session, and shows `installCompleteProd` or `installCompleteDemo`. `loginCATS` (`ui.php:1092-1106`) is the post-install redirect helper.

## Relationship to installwizard.php / installtest.php (root)

- **`installwizard.php`** (repo root) is the actual installer front-end / "view." It does a PHP-major-version check (`installwizard.php:7-20`), starts a session for >= PHP5, conditionally emits the CSRF token if a user is logged in (`installwizard.php:40-44`), loads `js/lib.js`, `js/install.js`, `js/submodal/subModal.js`, `modules/install/install.css`, and renders all the hidden step `<div>`s the AJAX layer toggles. It bootstraps the flow with `Installpage_populate('a=startInstall')` (`installwizard.php:568`). This is the page `notinstalled.php` links to (`notinstalled.php:23`). Note `CATSUI::handleRequest()` is empty (`CATSUI.php:42-44`) — the wizard does **not** route through the `CATSUI` controller; it talks directly to `ajax.php` -> `modules/install/ajax/ui.php`.

- **`installtest.php`** (repo root) is a standalone diagnostic page (not part of the AJAX wizard). It includes `lib/InstallationTests.php` (`installtest.php:34`), defines `REQUIRED_SCHEMA_VERSION` = `'1200'` (`installtest.php:55`), and runs `InstallationTests::runCoreTests()`, `checkMySQL(...)`, `checkAttachmentsDir()`, `checkAntiword()` (`installtest.php:174-179`), printing pass/fail/warning rows and a footer verdict. It shares the same `InstallationTests` class the wizard's `installTest`/`testDatabaseConnectivity` actions use, but is invoked by visiting the URL directly rather than via AJAX.

- **`modules/install/phpVersion.php`** is a standalone fallback page shown when PHP is too old (text: "Your PHP version is ... OpenCATS Requires PHP 5 or better." — `phpVersion.php:22-23`). The wizard's PHP4 branch instead shows the inline `phpVersion`/`testFailed` blocks (`installwizard.php:567-573`).

## lib/ dependencies (cited)

- `lib/InstallationTests.php` — the `InstallationTests` class (`InstallationTests.php:42`); used by `ui.php` (included `ui.php:31`) and `installtest.php` (`installtest.php:34`). Provides `runInstallerTests()` (`:67`), `runCoreTests()` (`:47`), `checkMySQL()` (`:367`), `checkAntiword/checkPdftotext/checkHtml2text/checkUnrtf()` (`:501/:551/:601/:651`), `checkAttachmentsDir()` (`:490`), `checkConfigWritable()` (`:700`), `checkDirectoryWritable()` (`:720`). It itself includes `lib/FileUtility.php` (`InstallationTests.php:35`) and pulls in `lib/DocumentToText.php` for the binary checks (`InstallationTests.php:524` etc.).
- `lib/CATSUtility.php` — included `ui.php:32`; `CATSUtility::changeConfigSetting()` is the workhorse that rewrites `config.php` constants throughout `ui.php`.
- `lib/FileCompressor.php` — included for `ZipFileExtractor` in `restoreFromBackup` (`ui.php:728`) and `onLoadDemoData` (`ui.php:820`).
- `lib/SystemUtility.php` — `SystemUtility::isWindows()` in `resumeParsing` path normalization (`ui.php:306,336,366,396`).
- `lib/Attachments.php` + `lib/DocumentToText.php` + `lib/DatabaseConnection.php` — used by `attachmentsReindex.php` (`:68`, `:75`).
- `modules/install/Schema.php` — `CATSSchema` class (`Schema.php:29`), `CATSSchema::get()` (`Schema.php:31`); included by the controller (`CATSUI.php:28`) and stored as `$this->_schema` (`CATSUI.php:39`).
- `modules/install/OptionalComponents.php` — defines the `$optionalComponents` array (`OptionalComponents.php:30+`); included by `initializeOptionalComponents()` (`ui.php:1204`).
- Local helper functions defined inside `ui.php`: `MySQLConnect()` (`ui.php:1113`), `MySQLQuery()` (`ui.php:1159`), `MySQLQueryMultiple()` (`ui.php:1182`), `initializeOptionalComponents()` (`ui.php:1199`). These are raw `mysqli_*` wrappers used by the installer, not the app's normal `DatabaseConnection` abstraction.

## Hooks fired

- The install module's own files fire **no** hooks directly (`grep "Hooks::" modules/install/` returns nothing).
- Hooks relevant to the install flow fire in the dispatchers it routes through:
  - `ajax.php:161` — `eval(Hooks::get('AJAX_HOOK'))` wraps every `install:ui` / `install:maint` AJAX response.
  - `index.php:199` — `eval(Hooks::get('INDEX_LOAD_HOME'))` (reached during the `maint` postback path through `index.php`).

## Source evidence

- `modules/install/CATSUI.php` — controller class `CATSUI extends UserInterface` (`:30`), constructor (`:32-40`), `_authenticationRequired=false` (`:36`), empty `handleRequest()` (`:42-44`), Schema include (`:28`).
- `modules/install/ajax/ui.php` — POST-only guard (`:45-48`), `$action` (`:59`), `INSTALL_BLOCK` self-lock (`:62-70`), action switch (`:72-1111`), `INSTALL_BLOCK` write (`:1010-1013`), helper functions (`:1113-1217`).
- `modules/install/ajax/maint.php` — POST-only guard + `$maintPage=true; include_once('index.php')` (`:30-55`).
- `modules/install/ajax/attachmentsReindex.php` — ACL guard (`:70-73`).
- `modules/install/ajax/attachmentsToThreeDirectory.php` — ACL guard (`:53-56`).
- `modules/install/notinstalled.php` — not-installed page (`:22-23`).
- `modules/install/phpVersion.php` — PHP-too-old page (`:22-23`).
- `modules/install/OptionalComponents.php` — `usZipCodes` definition (`:30-52`).
- `index.php` — front-controller install gate (`:44-48`).
- `ajax.php` — installer-active routing restriction (`:95-118`), function->file dispatch (`:120-134`).
- `installwizard.php` — wizard view, 7 steps (`:67-90`), bootstrap (`:568`).
- `installtest.php` — diagnostic page (`:34,:55,:174-179`).
- `js/install.js` — `setActiveStep` (`:33`), `Installpage_populate` (`:64`), `Installpage_maint` (`:102`), `Installpage_append` (`:144`), `setProgressUpdating` (`:220`).
- `lib/InstallationTests.php` — test class and methods as cited above.
- `lib/UserInterface.php` — base default `_authenticationRequired=true` (`:50`), `getModuleRequiresAuthentication` (`:179-181`).

## Unverified / open questions

- `_schema` (`CATSSchema::get()`, `CATSUI.php:39`) is assigned in the constructor but no read of `$this->_schema` was found within the install flow (the empty `handleRequest()` never uses it). I did not trace `Schema.php` (`CATSSchema`) beyond confirming the class/`get()` declaration (`Schema.php:29-31`); its broader purpose is out of scope here.
- `installtest.php` defines `REQUIRED_SCHEMA_VERSION` = `'1200'` (`installtest.php:55`) but I did not find it consumed within `installtest.php` itself; its effect (if any) is unverified.
- The `eval()` of `installCode`/`removeCode`/`detectCode` for optional components (`ui.php:580,587,1210`) and the `eval(Hooks::get(...))` calls execute strings defined in `OptionalComponents.php` / hook config; I verified the only shipped component is `usZipCodes` but did not audit any custom/hook-injected component code.
- `mailSettings` reads `$tables['settings']` (`ui.php:183`) but `$tables` is only populated by `MySQLConnect()` called at `ui.php:175`; on a brand-new empty DB the `settings` table may be absent — behavior in that branch is plausibly a no-op but not explicitly verified here.
- I did not exhaustively read `modules/install/Schema.php` (94 KB), `backupDB.php`, or the `scripts/` migration files (`114.php`, `150.php`, `359.sql`, `372.php`); they are not referenced by the wizard's `ui.php` switch.

---

ACL-SUMMARY

```
install.startInstall => (none)
install.installTest => (none)
install.databaseConnectivity => (none)
install.testDatabaseConnectivity => (none)
install.detectRevision => (none)
install.queryResetDatabase => (none)
install.resetDatabase => (none)
install.selectDBType => (none)
install.doInstallEmptyDatabase => (none)
install.onLoadDemoData => (none)
install.restoreFromBackup => (none)
install.doDeleteBackup => (none)
install.upgradeCats => (none)
install.resumeParsing => (none)
install.testResumeParsing => (none)
install.testResumeParsing2 => (none)
install.mailSettings => (none)
install.setMailSettings => (none)
install.optionalComponents => (none)
install.setupOptional => (none)
install.maint => (none)
install.reindexResumes => (none)
install.onReindexResumes => (none)
install.maintComplete => (none)
install.loginCATS => (none)
install.attachmentsReindex => ACCESS_LEVEL_SA (only enforced when INSTALL_BLOCK exists)
install.attachmentsToThreeDirectory => ACCESS_LEVEL_ROOT
```
