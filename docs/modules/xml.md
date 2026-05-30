# Module: xml

## Overview

The `xml` module is a small, public-facing feed module that builds an XML
document of publicly shared job postings, intended for bulk submission to job
bulletin sites (e.g. Indeed.com). Unlike the `rss` module, its output is
**template-driven** via `.xtpl` files and the `XmlTemplate` class.

Controller class declaration:

```php
class XmlUI extends UserInterface
```

(modules/xml/XmlUI.php:48)

The constructor explicitly disables authentication:

```php
public function __construct()
{
    parent::__construct();

    $this->_authenticationRequired = false;
    $this->_moduleDirectory = 'xml';
    $this->_moduleName = 'xml';
    $this->_moduleTabText = '';
    $this->_subTabs = array();
}
```

(modules/xml/XmlUI.php:50-59)

`_authenticationRequired = false` (modules/xml/XmlUI.php:54) means this module is
**public / unauthenticated**.

Three section-name constants are defined at file scope:

```php
define('XTPL_HEADER_STRING',    'header');
define('XTPL_FOOTER_STRING',    'footer');
define('XTPL_JOB_STRING',       'job');
```

(modules/xml/XmlUI.php:44-46)

### Routing via root shim + index.php flag

The root shim `xml/index.php` sets a flag and bootstraps the front controller
(note it also pulls in `config.php` directly, unlike the rss shim):

```php
$xmlPage = true;

chdir('..');
include_once('config.php');
include_once(LEGACY_ROOT . '/lib/CATSUtility.php');
include_once(CATSUtility::getIndexName());
```

(xml/index.php:34-39)

The front controller dispatches on `$xmlPage`:

```php
else if (isset($xmlPage) && $xmlPage)
{
    ModuleUtility::loadModule('xml');
}
```

(index.php:178-181)

As with rss, the POST CSRF-validation block is skipped for XML requests because
the guard condition includes `(!isset($xmlPage) || !$xmlPage)` (index.php:150).

## Action catalog

The action comes from `$this->getAction()` (modules/xml/XmlUI.php:64) and is
matched in `handleRequest()` (modules/xml/XmlUI.php:62-72).

| Action | ACL guard if any | Handler | lib calls | Output |
|--------|------------------|---------|-----------|--------|
| `jobOrders` (and `default`) | None — no `getUserAccessLevel(...)` guard anywhere in the module | `displayPublicJobOrders()` (modules/xml/XmlUI.php:103) | `new Site(-1)`, `Site::getFirstSiteID()`, `Hooks::get('RSS_SITEID')`, `new JobOrders()`, `JobOrders::getAll()`, `HTTPLogger::getHTTPLogTypeIDByName()`, `HTTPLogger::addHTTPLog()`, `XmlTemplate::getTemplates()`, `XmlTemplate::loadTemplate()`, `XmlTemplate::loadTemplateTags()`, `XmlTemplate::replaceTemplateTags()`, `new CareerPortalSettings()`, `CareerPortalSettings::getAll()`, `DateUtility::getRSSDate()`, `CATSUtility::getAbsoluteURI()`, `CATSUtility::getIndexName()` | `header('Content-type: text/xml')` then a template-rendered XML document echoed to the response |

Only one switch case exists; `jobOrders` and `default` collapse to the same
handler (modules/xml/XmlUI.php:67-70). There is **no ACL / `getUserAccessLevel`
guard** in `XmlUI.php`.

`outputXMLError($title, $errorMessage)` (modules/xml/XmlUI.php:74) is a private
helper that emits an error document (curiously wrapped in an `<rss version="2.0">`
shell, modules/xml/XmlUI.php:80-100), but it is **not invoked** from any code path
in this file.

## Per-action / feed-generation detail

### `displayPublicJobOrders()` (modules/xml/XmlUI.php:103-328)

1. Resolves the career-portal site and fires the site hook (same pattern as rss):

   ```php
   $site = new Site(-1);
   $careerPortalSiteID = $site->getFirstSiteID();

   if (!eval(Hooks::get('RSS_SITEID'))) return;
   ```

   (modules/xml/XmlUI.php:105-109; `getFirstSiteID()` at lib/Site.php:183) — note
   the hook key is `RSS_SITEID`, the same key the rss module uses.

2. Loads shared job orders with the same call shape as rss:

   ```php
   $jobOrders = new JobOrders($careerPortalSiteID);
   $rs = $jobOrders->getAll(JOBORDERS_STATUS_SHARE, -1, -1, -1, false, true);
   ```

   (modules/xml/XmlUI.php:111-112). Signature:
   `getAll($status, $userID = -1, $companyID = -1, $contactID = -1, $onlyHot = false, $onlyPublic = false, $allowAdministrativeHidden = false)`
   (lib/JobOrders.php:563-564); `JOBORDERS_STATUS_SHARE` = `100`
   (lib/JobOrders.php:40); `$onlyPublic = true`.

3. Logs the access via HTTPLogger (two queries — name→ID then insert):

   ```php
   HTTPLogger::addHTTPLog(
       HTTPLogger::getHTTPLogTypeIDByName('xml'),
       $careerPortalSiteID
   );
   ```

   (modules/xml/XmlUI.php:117-120; `getHTTPLogTypeIDByName` at
   lib/HttpLogger.php:103, `addHTTPLog` at lib/HttpLogger.php:53). This logging
   step is present in `xml` but **absent** in `rss`.

4. Emits the XML content-type header (modules/xml/XmlUI.php:123).

5. Selects a template. The set of available templates is read from the
   `xml_feeds` table; the `t` GET parameter chooses one by name (case-insensitive),
   else the first row's template is the default:

   ```php
   $availTemplates = XmlTemplate::getTemplates();

   if (isset($_GET['t']))
   {
       $templateName = $_GET['t'];
       foreach ($availTemplates as $template)
       {
           if (!strcasecmp($template['xml_template_name'], $templateName))
           {
               $templateSections = XmlTemplate::loadTemplate($templateName);
           }
       }
   }

   if (!isset($templateSections))
   {
       $templateSections = XmlTemplate::loadTemplate(
           $templateName = $availTemplates[0]["xml_template_name"]
       );
   }
   ```

   (modules/xml/XmlUI.php:127-148)

6. Splits the loaded template into header / job / footer sections using the
   `XTPL_*` constants:

   ```php
   $templateHeader = $templateSections[XTPL_HEADER_STRING];
   $templateJob = $templateSections[XTPL_JOB_STRING];
   $templateFooter = $templateSections[XTPL_FOOTER_STRING];
   ```

   (modules/xml/XmlUI.php:151-153)

7. Header-section tag replacement. Tags are discovered with
   `XmlTemplate::loadTemplateTags()` and replaced with `replaceTemplateTags()`;
   only `date` and `siteURL` are handled:

   ```php
   $tags = XmlTemplate::loadTemplateTags($templateHeader);
   foreach ($tags as $tag)
   {
       switch ($tag)
       {
           case 'date':    // -> DateUtility::getRSSDate()
           case 'siteURL': // -> CATSUtility::getAbsoluteURI('')
       }
   }
   $stream = $templateHeader;
   ```

   (modules/xml/XmlUI.php:155-177)

8. Career-portal gating + URL fixup. The job loop only runs when the portal's
   `allowBrowse` setting is `1`:

   ```php
   $careerPortalSettings = new CareerPortalSettings($careerPortalSiteID);
   $settings = $careerPortalSettings->getAll();

   $url = CATSUtility::getAbsoluteURI();
   if(strrpos($url, 'xml') == (strlen($url) - 4))
   {
       $url = substr($url, 0, -4);
   }

   if ($settings['allowBrowse'] == 1)
   {
       foreach ($rs as $rowIndex => $row) { ... }
   }
   ```

   (modules/xml/XmlUI.php:181-323; `CareerPortalSettings` at lib/CareerPortal.php:39,
   `getAll()` at lib/CareerPortal.php:73)

9. Per-job tag replacement. The job-section tags are resolved once
   (modules/xml/XmlUI.php:179) and applied for every job. Supported tags
   (modules/xml/XmlUI.php:197-318):

   | Tag | Source value | Cite |
   |-----|--------------|------|
   | `siteURL` | `$url` | modules/xml/XmlUI.php:201-207 |
   | `jobTitle` | `$row['title']` | modules/xml/XmlUI.php:209-215 |
   | `jobPostDate` | `$row['dateCreatedSort']` | modules/xml/XmlUI.php:217-223 |
   | `jobURL` | `sprintf("%scareers/?p=showJob&ID=%d&ref=%s", $url, $row['jobOrderID'], $templateName)` | modules/xml/XmlUI.php:225-237 |
   | `jobOrderID` | `$row['jobOrderID']` | modules/xml/XmlUI.php:239-245 |
   | `jobID` | `$row['jobID']` | modules/xml/XmlUI.php:247-253 |
   | `hiringCompany` | hard-coded `'CATS (www.catsone.com)'` | modules/xml/XmlUI.php:255-261 |
   | `jobCity` | `$row['city']` | modules/xml/XmlUI.php:263-269 |
   | `jobState` | `$row['state']` | modules/xml/XmlUI.php:271-277 |
   | `jobCountry` | hard-coded `"US"` (FIXME: non-US) | modules/xml/XmlUI.php:280-286 |
   | `jobZipCode` | hard-coded `''` | modules/xml/XmlUI.php:288-294 |
   | `jobDescription` | `$row['jobDescription']` | modules/xml/XmlUI.php:296-302 |
   | `notes` | `$row['notes']` | modules/xml/XmlUI.php:304-310 |
   | `type` | `$row['type']` | modules/xml/XmlUI.php:311-317 |

   Each rendered job string is appended to `$stream` (modules/xml/XmlUI.php:321).

10. Appends the footer section and echoes:

    ```php
    $stream .= $templateFooter;
    echo $stream;
    ```

    (modules/xml/XmlUI.php:325-327)

`$indexName = CATSUtility::getIndexName();` (modules/xml/XmlUI.php:125) is assigned
but unused in the feed body.

## lib/ dependencies (cited)

Includes at the top of the module (modules/xml/XmlUI.php:35-42):

- `lib/ActivityEntries.php` (modules/xml/XmlUI.php:35) — included; not referenced.
- `lib/StringUtility.php` (modules/xml/XmlUI.php:36) — included; not referenced in
  this file's logic.
- `lib/DateUtility.php` (modules/xml/XmlUI.php:37) — `DateUtility::getRSSDate()`
  (modules/xml/XmlUI.php:97, 163).
- `lib/JobOrders.php` (modules/xml/XmlUI.php:38) — `new JobOrders()` / `getAll()`
  (modules/xml/XmlUI.php:111-112); `JOBORDERS_STATUS_SHARE` (lib/JobOrders.php:40).
- `lib/Site.php` (modules/xml/XmlUI.php:39) — `new Site(-1)` / `getFirstSiteID()`
  (modules/xml/XmlUI.php:105-107; lib/Site.php:183).
- `lib/XmlJobExport.php` (modules/xml/XmlUI.php:40) — defines `class XmlTemplate`
  (lib/XmlJobExport.php:38). Methods used:
  - `XmlTemplate::getTemplates()` (lib/XmlJobExport.php:51) — `SELECT ... FROM xml_feeds`
    (lib/XmlJobExport.php:56-69); called at modules/xml/XmlUI.php:127.
  - `XmlTemplate::loadTemplate($templateName)` (lib/XmlJobExport.php:125) — reads
    `XML_EXPORT_TEMPLATES_DIR/<name>.xtpl` and parses `>>section`/`<<section`
    blocks (lib/XmlJobExport.php:130-174); called at modules/xml/XmlUI.php:137, 145.
  - `XmlTemplate::loadTemplateTags($template)` (lib/XmlJobExport.php:184) — scans for
    `$[...]` tags (lib/XmlJobExport.php:187-202); called at modules/xml/XmlUI.php:155, 179.
  - `XmlTemplate::replaceTemplateTags($tag, $replace, $template)`
    (lib/XmlJobExport.php:214) — `str_replace('$[tag]', htmlspecialchars($replace), ...)`
    (lib/XmlJobExport.php:216-220); called throughout the tag switches.
  - Also defined but **not** called by this module: `XmlTemplate::getTemplate()`
    (lib/XmlJobExport.php:80) and `XmlTemplate::submitXMLFeeds()`
    (lib/XmlJobExport.php:108).
- `lib/HttpLogger.php` (modules/xml/XmlUI.php:41) — `HTTPLogger::getHTTPLogTypeIDByName()`
  (lib/HttpLogger.php:103) and `HTTPLogger::addHTTPLog()` (lib/HttpLogger.php:53);
  called at modules/xml/XmlUI.php:117-120.
- `lib/CareerPortal.php` (modules/xml/XmlUI.php:42) — `class CareerPortalSettings`
  (lib/CareerPortal.php:39), `getAll()` (lib/CareerPortal.php:73); used at
  modules/xml/XmlUI.php:181-182.

Template files / constant:

- `XML_EXPORT_TEMPLATES_DIR` = `'./modules/xml/xml_templates'`
  (constants.php:190), consumed by `loadTemplate()` (lib/XmlJobExport.php:131).
- The "other file(s)" alongside `XmlUI.php` in `modules/xml/` are the template
  files in `modules/xml/xml_templates/`: `indeed.xtpl`, `rss.xtpl`,
  `simplyhired.xtpl`. Example — `rss.xtpl` defines `>>header / >>job / >>footer`
  sections with `$[siteURL]`, `$[date]`, `$[jobTitle]`, `$[jobDescription]`,
  `$[jobURL]` tags (modules/xml/xml_templates/rss.xtpl:3-24).

## Hooks fired

| Hook key | Where | Cite |
|----------|-------|------|
| `RSS_SITEID` | `displayPublicJobOrders()` — `if (!eval(Hooks::get('RSS_SITEID'))) return;` | modules/xml/XmlUI.php:109 |
| `XML_SUBMIT_FEEDS_TO_QUEUE` | `XmlTemplate::submitXMLFeeds()` — `if (!eval(Hooks::get('XML_SUBMIT_FEEDS_TO_QUEUE'))) return;` (lib dependency; method not called by this module) | lib/XmlJobExport.php:110 |

`Hooks::get()` returns `'return true;'` when `$_SESSION['hooks']` is unset
(lib/Hooks.php:54-57), so with no registered hook the `RSS_SITEID` guard
evaluates true and the feed proceeds.

## Source evidence

- modules/xml/XmlUI.php (read in full, 1-331) — controller, constructor,
  `handleRequest`, `outputXMLError`, `displayPublicJobOrders`, `XTPL_*` constants.
- xml/index.php (read in full, 1-41) — root shim, sets `$xmlPage = true`,
  includes `config.php`.
- index.php:145-181 — CSRF-skip condition and `$xmlPage` dispatch.
- lib/XmlJobExport.php (read in full, 1-224) — `class XmlTemplate` and all methods.
- modules/xml/xml_templates/ — `indeed.xtpl`, `rss.xtpl`, `simplyhired.xtpl`;
  rss.xtpl read in full (1-24).
- constants.php:190 — `XML_EXPORT_TEMPLATES_DIR`.
- lib/Hooks.php:38-73 — `Hooks::get()`.
- lib/JobOrders.php:40, 563-564 — `JOBORDERS_STATUS_SHARE`, `getAll()` signature.
- lib/Site.php:183; lib/HttpLogger.php:53, 103; lib/CareerPortal.php:39, 73 —
  referenced method/class locations.

## Unverified / open questions

- The runtime fields of each `$row` (e.g. `jobOrderID`, `jobID`,
  `dateCreatedSort`, `jobDescription`, `notes`) were confirmed only at their
  consuming sites, not traced into the `getAll()` SQL projection.
- `loadTemplate()` calls `file_get_contents(...)` with no existence check
  (lib/XmlJobExport.php:130-132); behaviour when `$templateName` resolves to a
  missing `.xtpl` file was not exercised.
- `outputXMLError()` (modules/xml/XmlUI.php:74-101) is defined but has no call
  site in this file.
- `XmlTemplate::submitXMLFeeds()` (lib/XmlJobExport.php:108) and `getTemplate()`
  (lib/XmlJobExport.php:80) are defined in the included lib but not invoked by
  this module; their callers (if any) were not traced.
- Whether anything registers `$_SESSION['hooks']['RSS_SITEID']` /
  `['XML_SUBMIT_FEEDS_TO_QUEUE']` was not located in this repo during review.
