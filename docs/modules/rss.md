# Module: rss

## Overview

The `rss` module is a small, public-facing feed module that emits an RSS 2.0
XML document of publicly shared job orders.

Controller class declaration:

```php
class RssUI extends UserInterface
```

(modules/rss/RssUI.php:43)

The constructor explicitly disables authentication:

```php
public function __construct()
{
    parent::__construct();

    $this->_authenticationRequired = false;
    $this->_moduleDirectory = 'rss';
    $this->_moduleName = 'rss';
    $this->_moduleTabText = '';
    $this->_subTabs = array();
}
```

(modules/rss/RssUI.php:45-54)

`_authenticationRequired = false` (modules/rss/RssUI.php:49) means this module is
**public / unauthenticated** — it can be reached without a logged-in session.

### Routing via root shim + index.php flag

The root shim `rss/index.php` sets a flag and bootstraps the front controller:

```php
$rssPage = true;

chdir('..');
include_once(LEGACY_ROOT . '/lib/CATSUtility.php');
include_once(CATSUtility::getIndexName());
```

(rss/index.php:34-38)

The front controller dispatches on `$rssPage`:

```php
else if (isset($rssPage) && $rssPage)
{
    ModuleUtility::loadModule('rss');
}
```

(index.php:173-176)

Note that the CSRF-token check earlier in `index.php` is skipped for RSS
requests because the guard condition includes `(!isset($rssPage) || !$rssPage)`
(index.php:149) — i.e. RSS pages bypass the POST CSRF validation block
(index.php:145-163).

## Action catalog

The action comes from `$this->getAction()` (modules/rss/RssUI.php:59) and is
matched in `handleRequest()` (modules/rss/RssUI.php:57-67).

| Action | ACL guard if any | Handler | lib calls | Output |
|--------|------------------|---------|-----------|--------|
| `jobOrders` (and `default`) | None — no `getUserAccessLevel(...)` guard anywhere in the module | `displayPublicJobOrders()` (modules/rss/RssUI.php:99) | `new Site(-1)`, `Site::getFirstSiteID()`, `Hooks::get('RSS_SITEID')`, `new JobOrders()`, `JobOrders::getAll()`, `JobOrders::typeCodeToString()`, `DateUtility::getRSSDate()`, `StringUtility::makeCityStateString()`, `CATSUtility::getAbsoluteURI()`, `CATSUtility::getIndexName()` | `header('Content-type: text/xml')` then an RSS 2.0 XML document echoed to the response |

There is only one switch case; `jobOrders` and `default` collapse to the same
handler (modules/rss/RssUI.php:62-65). There is **no ACL / `getUserAccessLevel`
guard** in `RssUI.php` — access control relies solely on the module being
intended as a public feed.

`outputRSSError($title, $errorMessage)` (modules/rss/RssUI.php:69) exists as a
private helper that emits an RSS document whose single `<item>` describes an
error, but it is **not invoked** from any code path in this file.

## Per-action / feed-generation detail

### `displayPublicJobOrders()` (modules/rss/RssUI.php:99-156)

1. Resolves the career-portal site:

   ```php
   $site = new Site(-1);
   $careerPortalSiteID = $site->getFirstSiteID();
   ```

   (modules/rss/RssUI.php:101-103; `getFirstSiteID()` at lib/Site.php:183)

2. Fires the `RSS_SITEID` hook and short-circuits if it returns falsey:

   ```php
   if (!eval(Hooks::get('RSS_SITEID'))) return;
   ```

   (modules/rss/RssUI.php:105) — see [Hooks fired](#hooks-fired).

3. Loads the shared job orders:

   ```php
   $jobOrders = new JobOrders($careerPortalSiteID);
   $rs = $jobOrders->getAll(JOBORDERS_STATUS_SHARE, -1, -1, -1, false, true);
   ```

   (modules/rss/RssUI.php:107-108)

   The real `getAll` signature is:

   ```php
   public function getAll($status, $userID = -1, $companyID = -1,
       $contactID = -1, $onlyHot = false, $onlyPublic = false, $allowAdministrativeHidden = false)
   ```

   (lib/JobOrders.php:563-564). The call passes `JOBORDERS_STATUS_SHARE`
   (value `100`, lib/JobOrders.php:40) and `$onlyPublic = true`, so the feed is
   restricted to publicly shared job orders.

4. Emits the XML header and builds the RSS channel header inline:

   ```php
   header('Content-type: text/xml');
   ...
   $stream = sprintf(
       "<?xml version=\"1.0\" encoding=\"utf-8\"?>\n"
       . "<rss version=\"2.0\">\n"
       . "<channel>\n"
       . "<title>New Job Orders</title>\n"
       . "<description>CATS RSS Feed</description>\n"
       . "<link>%s</link>\n"
       . "<pubDate>%s</pubDate>\n",
       CATSUtility::getAbsoluteURI(),
       DateUtility::getRSSDate()
   );
   ```

   (modules/rss/RssUI.php:111-125)

5. Iterates each job-order row, building one `<item>` per job. The per-item link
   is built with `CATSUtility::getAbsoluteURI()` and the job order ID, then
   rewritten when served from the `/rss/` path:

   ```php
   $uri = sprintf("%scareers/?p=showJob&amp;ID=%d",
       CATSUtility::getAbsoluteURI(),
       $row['jobOrderID']
   );

   // Fix URL if viewing from /rss without using globals or dirup '../'
   if (strpos($_SERVER['PHP_SELF'], '/rss/') !== false)
   {
       $uri = str_replace('/rss/', '/', $uri);
   }
   ```

   (modules/rss/RssUI.php:127-138)

   Each item interpolates `$row['title']`, `typeCodeToString($row['type'])`,
   `StringUtility::makeCityStateString($row['city'], $row['state'])`, and the URI:

   ```php
   $stream .= sprintf(
       "<item>\n" .
       "<title>%s (%s)</title>\n" .
       "<description>Located in %s.</description>\n" .
       "<link>%s</link>\n" .
       "</item>\n",
       $row['title'],
       $jobOrders->typeCodeToString($row['type']),
       StringUtility::makeCityStateString($row['city'], $row['state']),
       $uri
   );
   ```

   (modules/rss/RssUI.php:140-150)

6. Closes the channel and echoes the stream:

   ```php
   $stream .= "</channel>\n</rss>\n";
   echo $stream;
   ```

   (modules/rss/RssUI.php:153-155)

Unlike the `xml` module, the RSS feed is **not** template-driven — the markup is
hard-coded inline and does not use `XmlTemplate` or the `.xtpl` files. (Note the
shipped `modules/xml/xml_templates/rss.xtpl` mirrors this same markup but is only
consumed by the `xml` module, not by `rss`.)

`$indexName = CATSUtility::getIndexName();` (modules/rss/RssUI.php:113) is
assigned but never used in the feed body.

## lib/ dependencies (cited)

Includes at the top of the module (modules/rss/RssUI.php:37-41):

- `lib/ActivityEntries.php` (modules/rss/RssUI.php:37) — included; not referenced
  in this file's logic.
- `lib/StringUtility.php` (modules/rss/RssUI.php:38) — `StringUtility::makeCityStateString()` (modules/rss/RssUI.php:148).
- `lib/DateUtility.php` (modules/rss/RssUI.php:39) — `DateUtility::getRSSDate()` (modules/rss/RssUI.php:93, 124).
- `lib/JobOrders.php` (modules/rss/RssUI.php:40) — `new JobOrders()` / `getAll()` / `typeCodeToString()` (modules/rss/RssUI.php:107-108, 147); `JOBORDERS_STATUS_SHARE` constant (lib/JobOrders.php:40).
- `lib/Site.php` (modules/rss/RssUI.php:41) — `new Site(-1)` / `getFirstSiteID()` (modules/rss/RssUI.php:101-103; lib/Site.php:183).

`CATSUtility` (e.g. `getAbsoluteURI`, `getIndexName`) and `Hooks` are used
(modules/rss/RssUI.php:74, 105, 113, 123, 130) but loaded by the front-controller
bootstrap, not by an `include_once` in this module.

This module does **not** include or call `lib/XmlJobExport.php`.

## Hooks fired

| Hook key | Where | Cite |
|----------|-------|------|
| `RSS_SITEID` | `displayPublicJobOrders()` — `if (!eval(Hooks::get('RSS_SITEID'))) return;` | modules/rss/RssUI.php:105 |

`Hooks::get($hookName)` returns a string of PHP to `eval()`. When no hooks are
registered in the session it returns `'return true;'` (lib/Hooks.php:54-57), and
otherwise appends registered commands before `' return true;'`
(lib/Hooks.php:59-71). With no registered `RSS_SITEID` hook, the guard therefore
evaluates true and the feed proceeds.

## Source evidence

- modules/rss/RssUI.php (read in full, 1-159) — controller, `__construct`,
  `handleRequest`, `outputRSSError`, `displayPublicJobOrders`.
- rss/index.php (read in full, 1-41) — root shim, sets `$rssPage = true`.
- index.php:145-181 — CSRF-skip condition and `$rssPage` dispatch to
  `ModuleUtility::loadModule('rss')`.
- lib/Hooks.php:38-73 — `Hooks::get()` behaviour.
- lib/JobOrders.php:40, 563-564 — `JOBORDERS_STATUS_SHARE` and `getAll()` signature.
- lib/Site.php:183 — `getFirstSiteID()`.

## Unverified / open questions

- The exact runtime fields of each `$row` (e.g. `jobOrderID`, `title`, `type`,
  `city`, `state`) were not traced into the `getAll()` SQL projection; only the
  consuming code was read (modules/rss/RssUI.php:127-150).
- `outputRSSError()` (modules/rss/RssUI.php:69-97) is defined but no call site
  exists in this file; whether any external code path triggers it was not
  verified.
- The behaviour of `Hooks::get('RSS_SITEID')` depends on what (if anything)
  populates `$_SESSION['hooks']['RSS_SITEID']`; no such registration was located
  in this repo during this review.
