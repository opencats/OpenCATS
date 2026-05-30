# Module: wizard

## Overview

The `wizard` module is the **rendering/runtime engine** for OpenCATS's generic multi-step
"Wizard" UI. It does **not** define the wizard's pages itself; it reads page definitions that
another module (the login flow) has stashed in the session and renders them as an
AJAX-driven, multi-page form with section tabs, Next/Previous/Skip/Finish navigation, and a
loading bar.

Controller class declaration:

```php
class WizardUI extends UserInterface
```
(modules/wizard/WizardUI.php:40)

Constructor:

```php
public function __construct()
{
    parent::__construct();

    $this->_authenticationRequired = false;
    $this->_moduleDirectory = 'wizard';
    $this->_moduleName = 'wizard';
    $this->_moduleTabText = '';
    $this->_subTabs = array();
    ...
}
```
(modules/wizard/WizardUI.php:42-74)

Key facts derived from the constructor:

- The module **disables authentication** by overriding the base default. `UserInterface`
  defaults to `protected $_authenticationRequired = true;` (lib/UserInterface.php:50), and the
  wizard explicitly sets it to `false` (modules/wizard/WizardUI.php:46). This is reported back
  to the framework via `isAuthenticationRequired()` which returns `$this->_authenticationRequired`
  (lib/UserInterface.php:179-181).
- It registers no module tab text and no sub-tabs (modules/wizard/WizardUI.php:49-50).
- The bulk of the constructor is a **large commented-out block** (modules/wizard/WizardUI.php:52-73)
  that previously added an intro/license/register/setup-users/localization wizard via
  `$this->addPage(...)`, `$this->addJsInclude(...)` and `$this->setFinishURL(...)`. None of
  this runs — it is inert.

What the wizard actually does at runtime: the page list, current page index, finish URL, and
optional JS include all come from `$_SESSION['CATS_WIZARD']`, which is populated by the
**`Wizard` library** (`lib/Wizard.php`), not by this module. The session shape is created in
the `Wizard` constructor as:

```php
$_SESSION['CATS_WIZARD'] = array(
    'pages' => array(),
    'curPage' => 1,
    'js' => $jsInclude,
    'finishURL' => $finishURL
);
```
(lib/Wizard.php:56-61)

`WizardUI::show()` renders the chrome (`Show.tpl`) and emits a script block that registers
each page client-side, and `ajax_getPage()` serves the body of an individual page on demand.
The only producer of `CATS_WIZARD` pages found in the repo is in `LoginUI.php`, and that
producer is itself **commented out** (see Hooks section below) — so in the current code the
session is normally empty and `show()` redirects away.

## Action catalog

The dispatcher is `handleRequest()` (modules/wizard/WizardUI.php:77-90). There are **no
`getUserAccessLevel(...)` ACL guards anywhere in this module** (verified: the only access-control-
related line in the whole module is the `$_authenticationRequired = false;` override at
modules/wizard/WizardUI.php:46). The module is reachable unauthenticated.

| Action (`a=`) | Exact ACL guard | Required level | Handler | lib calls | Template |
|---|---|---|---|---|---|
| `ajax_getPage` | (none) | (none — auth disabled, no ACL) | `ajax_getPage()` (WizardUI.php:133-185) | reads `$_SESSION['CATS_WIZARD']`; reads `$_SESSION['CATS']` session object getters: `getUserID()`, `getUserName()`, `getSiteID()`, `getSiteName()` (WizardUI.php:168-171) | the per-page template stored in `$_SESSION['CATS_WIZARD']['pages'][n]['template']` (WizardUI.php:176,184) |
| `default` → `show` | (none) | (none — auth disabled, no ACL) | `show()` (WizardUI.php:92-131) | reads `$_SESSION['CATS_WIZARD']`; on missing session calls `CATSUtility::transferRelativeURI(...)` and `CATSUtility::getIndexName()` (WizardUI.php:99) | `./modules/wizard/Show.tpl` (WizardUI.php:130) |

## Per-action / step detail

### `default` → `show()` (modules/wizard/WizardUI.php:92-131)

1. Guards on session presence:
   ```php
   if (!isset($_SESSION['CATS_WIZARD']) || empty($_SESSION['CATS_WIZARD']) ||
       !is_array($_SESSION['CATS_WIZARD']))
   {
       CATSUtility::transferRelativeURI(CATSUtility::getIndexName() . 'm=home');
       return;
   }
   ```
   (WizardUI.php:94-101) — if the wizard session was never built or was lost, the user is
   redirected to `m=home`.
2. Builds a JavaScript string that calls `addWizardPage("title", disableNext, disableSkip)`
   once per page (WizardUI.php:104-113). The page title is passed through `addslashes()`
   (WizardUI.php:108) and the two booleans render as literal `true`/`false`.
3. Appends `var finishURL = '...';` from `$_SESSION['CATS_WIZARD']['finishURL']` and
   `var currentPage = <curPage>;` (WizardUI.php:114-115).
4. Assigns to the template: `js` (the script above), `jsInclude` (optional extra JS file from
   `$_SESSION['CATS_WIZARD']['js']`, defaulting to `''`), `pages`, `currentPage` (the current
   page array, 1-based index resolved via `curPage-1`), `currentPageIndex`, `active` (`$this`),
   and the nav flags `enableSkip=true`, `enablePrevious` (false only on page 1), `enableNext=true`
   (WizardUI.php:116-128).
5. Renders `./modules/wizard/Show.tpl` (WizardUI.php:130).

The `addWizardPage`, `finishURL`, and `currentPage` values are consumed by
`modules/wizard/wizard.js` (`addWizardPage` at wizard.js:69-74, `finishURL` used in
`next()`/`skip()` at wizard.js:87,119, `currentPage` driving navigation throughout).

### `ajax_getPage()` (modules/wizard/WizardUI.php:133-185)

Serves the inner HTML of a single wizard page (the body injected into `#wizardContainerBody`
by `wizard.js` `loadPage()` at wizard.js:235).

1. Session guard: if `CATS_WIZARD` is unset / not an array / empty, echoes the literal string
   `'This wizard has no pages.'` and returns (WizardUI.php:135-140).
2. Reads/clamps `$_GET['currentPage']` via `intval()`, defaulting to `1` and clamping out-of-range
   values back to `1` (WizardUI.php:143-144).
3. Reads `$_GET['requestAction']` and computes the requested page index (WizardUI.php:146-162):
   - `next` → `currentPage + 1`
   - `previous` → `currentPage - 1`
   - `skip` → `count($_SESSION['CATS_WIZARD'])` *(note: this counts the **top-level** wizard
     session array, not the `['pages']` array — see Unverified)*
   - `current` / default → `currentPage`
4. If a `$_SESSION['CATS']` session object exists, assigns `userID`, `userName`, `siteID`,
   `siteName` to the template from its getters (WizardUI.php:165-172).
5. Resolves the target page, falling back to index `0` if the requested page does not exist,
   then updates `$_SESSION['CATS_WIZARD']['curPage']` (WizardUI.php:175-177).
6. **`eval()` of stored PHP:** if the page definition carries a non-empty `php` string, it is
   executed:
   ```php
   if (($php = $_SESSION['CATS_WIZARD']['pages'][$requestPage]['php']) != '')
   {
       eval($php);
   }
   ```
   (WizardUI.php:179-182). The `php` payload originates from the `addPage(... $phpEval ...)`
   call (lib/Wizard.php:75-85). This is server-side PHP stored in the session and evaluated to
   prep template variables for the page.
7. Displays the page's template (WizardUI.php:184).

### Client navigation (modules/wizard/wizard.js)

- `addWizardPage(title, nonext, noskip)` pushes into `wizardPages` / `optionDisableNext` /
  `optionDisableSkip` (wizard.js:69-74).
- `next()` (wizard.js:76-93): if on the last page, navigates to `finishURL`; otherwise
  `loadPage("next")`. Honors a `nextDisabled` flag and an optional `extendedNext()` hook
  function if the active page template defines one.
- `previous()` (wizard.js:107-115) → `loadPage("previous")` when `currentPage > 1`.
- `skip()` (wizard.js:117-120) → navigates straight to `finishURL`.
- `current()` (wizard.js:122-125) → `loadPage("current")`; invoked on `<body onload="current();">`
  (Show.tpl:18) to load the first/current page body on initial render.
- `loadPage(requestAction)` (wizard.js:127-243): issues an XHR GET to
  `?m=wizard&a=ajax_getPage&currentPage=<n>&requestAction=<action>`, shows a loading bar after
  1.5s, updates the page title, Next/Previous/Skip button states, the "Next"→"Finish" relabel
  on the last page, the section-tab highlight, and finally injects the response HTML into
  `#wizardContainerBody`.
- Loading-bar animation helpers: `loadingBarDotDance()`, `showLoadingBar()`, `hideLoadingBar()`
  (wizard.js:245-299).

### Templates / assets

- `Show.tpl` (modules/wizard/Show.tpl) — the full standalone HTML page (its own `<html>`, not a
  fragment). Includes `modules/wizard/style.css` and `modules/wizard/wizard.js` via
  `TemplateUtility::getVersionedAssetURL(...)` (Show.tpl:9-10), optionally includes
  `$this->jsInclude` (Show.tpl:11-13), emits `$this->js` inline (Show.tpl:15), renders the
  section-title tabs from `$this->pages` (Show.tpl:29-31), the page title from
  `$this->currentPage['title']` (Show.tpl:54), the empty body container `#wizardContainerBody`
  (Show.tpl:62), and the navigator buttons gated by `$this->enableSkip` / `$this->enableNext`
  (Show.tpl:80-87).
- `style.css` (modules/wizard/style.css) — styling for the wizard chrome, section tabs,
  loading bar, and a user table (`.userTable`, `.userColumn1` etc., style.css:19-32) that was
  used by the now-disabled "Setup Users" page.

## lib/ dependencies (cited)

`WizardUI.php` includes these libraries at the top (modules/wizard/WizardUI.php:33-38):

- `lib/ActivityEntries.php`
- `lib/StringUtility.php`
- `lib/DateUtility.php`
- `lib/JobOrders.php`
- `lib/Site.php`
- `lib/CareerPortal.php`

None of these is referenced by name in the live code paths of `WizardUI.php` (they were
presumably needed by the commented-out pages / `eval`'d page PHP). The classes actually used at
runtime:

- **`CATSUtility`** — `transferRelativeURI()` and `getIndexName()` in `show()`'s redirect
  (WizardUI.php:99). Also the redirect target used by the library's `doModal()`.
- **`UserInterface`** — base class; provides `getAction()` (lib/UserInterface.php:193),
  `isAuthenticationRequired()` (lib/UserInterface.php:179-181), the `$_template` object, etc.

**`lib/Wizard.php`** is the companion library (not included by `WizardUI.php`; included by the
producer, `modules/login/LoginUI.php:34`). Its methods:

- `public function __construct($finishURL = '', $jsInclude = '')` (lib/Wizard.php:51-62) —
  resets and initializes `$_SESSION['CATS_WIZARD']`.
- `public function addPage($pageTitle, $templateFile, $phpEval = '', $disableNext = false, $disableSkip = false)`
  (lib/Wizard.php:75-85) — appends a page definition (`title`, `php`, `template`, `disableNext`,
  `disableSkip`) to the session. This is the source of the `php` payload later `eval()`'d by
  `ajax_getPage()`.
- `public function doModal()` (lib/Wizard.php:94-99) — if pages exist, redirects to `m=wizard`
  via `CATSUtility::transferRelativeURI('m=wizard')`, handing control to this module.

## Hooks fired

**This module (`modules/wizard/`) fires no `Hooks::get(...)` / `ASP_WIZARD_*` hooks** — none
appear in any wizard-module file (verified by grep over the repo: the only `ASP_WIZARD` matches
are in `modules/login/LoginUI.php` and the login docs).

The `ASP_WIZARD_*` hooks live in the **producer** code in `LoginUI.php`, inside the
commented-out "NEW WIZARD" block, so they do not currently execute:

- `ASP_WIZARD_PAGES` — `if (!eval(Hooks::get('ASP_WIZARD_PAGES'))) return;` (modules/login/LoginUI.php:353)
- `ASP_WIZARD_IMPORT` — `if (!eval(Hooks::get('ASP_WIZARD_IMPORT'))) return;` (modules/login/LoginUI.php:368)

Both are within the `/* ... */` block spanning modules/login/LoginUI.php:301-373, alongside the
`new Wizard(...)` instantiation (modules/login/LoginUI.php:302) and the `addPage`/`doModal`
calls. (Also cross-referenced in docs/modules/login.md:181-182.)

## Source evidence

- modules/wizard/WizardUI.php:40 — `class WizardUI extends UserInterface`
- modules/wizard/WizardUI.php:42-74 — constructor; `_authenticationRequired = false` (line 46); commented-out legacy pages (lines 52-73)
- modules/wizard/WizardUI.php:77-90 — `handleRequest()` switch: `ajax_getPage` and default→`show`
- modules/wizard/WizardUI.php:92-131 — `show()`; session guard + redirect (94-101); nav JS build (104-115); template assigns (116-128); renders `Show.tpl` (130)
- modules/wizard/WizardUI.php:133-185 — `ajax_getPage()`; "This wizard has no pages." (138); requestAction switch (147-162); session-object assigns (165-172); `eval($php)` (179-182)
- lib/Wizard.php:51-62 — `Wizard::__construct()` builds `$_SESSION['CATS_WIZARD']`
- lib/Wizard.php:75-85 — `Wizard::addPage(...)`
- lib/Wizard.php:94-99 — `Wizard::doModal()` redirects to `m=wizard`
- lib/UserInterface.php:50 — base default `$_authenticationRequired = true`
- lib/UserInterface.php:179-181 — `isAuthenticationRequired()`
- lib/UserInterface.php:193 — `getAction()`
- lib/UserInterface.php:429 — `getUserAccessLevel()` (exists in base; never called by this module)
- modules/wizard/Show.tpl:9-15, 18, 29-31, 54, 62, 80-87 — chrome, asset includes, inline JS, tabs, body, nav buttons
- modules/wizard/wizard.js:69-74 (`addWizardPage`), 76-125 (nav fns), 127-243 (`loadPage` XHR to `?m=wizard&a=ajax_getPage`)
- modules/login/LoginUI.php:34 (include lib/Wizard.php), 302 (`new Wizard(...)`), 353/368 (`ASP_WIZARD_*`), 301-373 (entire block commented out)

## Unverified / open questions

- **`skip` math bug (unverified intent):** in `ajax_getPage()`, the `skip` case sets
  `$requestPage = count($_SESSION['CATS_WIZARD']);` (WizardUI.php:156), counting the top-level
  session array (which has 4 keys: `pages`, `curPage`, `js`, `finishURL`) rather than
  `count($_SESSION['CATS_WIZARD']['pages'])`. Whether this is intentional is unclear; the
  client-side `skip()` (wizard.js:117-120) navigates directly to `finishURL` and never sends
  `requestAction=skip`, so this server branch may simply be dead.
- **Auth disabled + `eval()`:** the module sets `_authenticationRequired = false`
  (WizardUI.php:46) and `eval()`s session-stored PHP (WizardUI.php:181). The PHP payload is only
  ever written by `Wizard::addPage()` from server-side code (login flow), so it is not directly
  attacker-controlled; an independent security assessment of this path is out of scope here and
  not verified.
- **Live reachability:** the only producer of `CATS_WIZARD` pages found in the repo is the
  commented-out block in `LoginUI.php` (lines 301-373). I did not find any other code path that
  calls `new Wizard(...)`/`addPage(...)`/`doModal()`. Therefore, in the current codebase the
  `m=wizard` page is reachable but normally finds an empty/absent session and redirects to
  `m=home` (WizardUI.php:99). Whether any plugin/hook re-enables it at runtime was not verified.
- The six `include_once` libraries at WizardUI.php:33-38 appear unused by the current live code
  paths; they were likely required by the disabled pages/`eval` payloads. Not independently
  confirmed to be fully dead.
```
