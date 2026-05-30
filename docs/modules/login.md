# Module: login

Source-derived design document for the OpenCATS **login** (authentication) module. Every claim below cites a file and line that was opened directly in this repository. No behavior is described that was not read in source.

## Overview

The module controller is declared as:

```php
class LoginUI extends UserInterface
```

(`modules/login/LoginUI.php:37`).

The constructor sets the module reachable while logged out by overriding the base authentication flag:

```php
public function __construct()
{
    parent::__construct();

    $this->_authenticationRequired = false;
    $this->_moduleName = 'login';
    $this->_moduleDirectory = 'login';
}
```

(`modules/login/LoginUI.php:39-46`).

**`_authenticationRequired = false` is confirmed** at `modules/login/LoginUI.php:43`. The base class defaults this to `true` (`lib/UserInterface.php:50`), and `UserInterface::requiresAuthentication()` returns the property when set (`lib/UserInterface.php:177-185`). The front controller consults this via `ModuleUtility::moduleRequiresAuthentication($_GET['m'])` (`lib/ModuleUtility.php:109`) so that, when not logged in, only modules with `_authenticationRequired = false` load directly; otherwise the user is redirected to the login module (`index.php:259-269`). Thus the login module is reachable without a session, as required.

Request dispatch is a single `switch` on the action keyword (`modules/login/LoginUI.php:48-77`):

```php
public function handleRequest()
{
    $action = $this->getAction();
    switch ($action)
    ...
}
```

## Action catalog

The login module performs **no `getAccessLevel(...)` / ACL guards in any handler** — that is by design, since the module is unauthenticated (`_authenticationRequired = false`). The only access-level reads in the file are *post-login* routing decisions (e.g. `$accessLevel = $_SESSION['CATS']->getAccessLevel(ACL::SECOBJ_ROOT)` at `modules/login/LoginUI.php:288`), not entry guards.

| Action | ACL guard | Required level | Handler | lib calls | Template |
|--------|-----------|----------------|---------|-----------|----------|
| `attemptLogin` | (none) | (none — unauthenticated) | `attemptLogin()` `modules/login/LoginUI.php:179` | `Site::getSiteByUnixName()`; `CATSSession::processLogin()` `:247`; `isLoggedIn()` `:250`; `getLoginError()` `:252`; `SystemInfo` `:286`; `getAccessLevel(ACL::SECOBJ_ROOT)` `:288`; `MailerSettings::getAll()` `:290-291`; `getEmail()`/`getSiteName()`; `NewVersionCheck::checkForUpdate()` `:414`; `CATSUtility::transferRelativeURI()` | `./modules/login/Login.tpl` (on failure, `:223`/`:281`) or redirect on success |
| `forgotPassword` (GET, not postback) | (none) | (none) | `forgotPassword()` `modules/login/LoginUI.php:442` | none | `./modules/login/ForgotPassword.tpl` `:446` |
| `forgotPassword` (POST / postback) | (none) | (none) | `onForgotPassword()` `modules/login/LoginUI.php:453` | `Users::getPassword()` `:460` (see Unverified); `Mailer::sendToOne()` `:463-468` | `./modules/login/ForgotPassword.tpl` `:487` |
| `noCookiesModal` | (none) | (none) | `noCookiesModal()` `modules/login/LoginUI.php:169` | none | `./modules/login/NoCookiesModal.tpl` `:173` |
| `showLoginForm` / *default* | (none) | (none) | `showLoginForm()` `modules/login/LoginUI.php:83` | `CATSSession::isLoggedIn()` `:124`; `checkForceLogout()` `:125`; `getUnixName()` `:127`; `Site::getSiteByUnixName()` `:138` | `./modules/login/Login.tpl` `:166` |

Postback selection for `forgotPassword` is driven by `$this->isPostBack()` (`modules/login/LoginUI.php:58-66`).

## Authentication flow

Traced from the `attemptLogin` action through the session and user layers.

1. **Input gather.** `attemptLogin()` reads `siteName` directly from `$_POST` (`modules/login/LoginUI.php:182-189`; note the in-source `//FIXME: getTrimmedInput()!` at `:181`). If either `username` or `password` is missing from `$_POST`, it sets `message = 'Invalid username or password.'`, fires `LOGIN_NO_CREDENTIALS`, and re-renders `Login.tpl` (`modules/login/LoginUI.php:191-226`).
2. **Username normalization.** Username/password are trimmed via `getTrimmedInput()` (`modules/login/LoginUI.php:228-229`). If the username contains `@`, `siteName` is cleared (`:231-234`); otherwise, if a `siteName` was supplied, the site's ID is appended as `username@siteID` after a `Site::getSiteByUnixName()` lookup (`:236-244`).
3. **Blind login attempt.** The controller delegates entirely to the session:
   ```php
   $_SESSION['CATS']->processLogin($username, $password);
   ```
   (`modules/login/LoginUI.php:247`).
4. **`CATSSession::processLogin`** is declared:
   ```php
   public function processLogin($username, $password, $addToHistory = true)
   ```
   (`lib/Session.php:666`). It first calls `Users::isCorrectLogin($username, $password)` to get a status flag (`lib/Session.php:671-672`).
5. **`Users::isCorrectLogin`** is declared:
   ```php
   public function isCorrectLogin($username, $password)
   ```
   (`lib/Users.php:796`). Returns `LOGIN_INVALID_USER` for empty username (`:801-804`), `LOGIN_INVALID_PASSWORD` for empty password (`:806-809`). It loads the user row by `user_name` (`:811-823`). For SQL auth, the password is verified with:
   ```php
   if (!$this->verifyAndMigratePassword((int) $rs['userID'], $password, $rs['password']))
   {
       return LOGIN_INVALID_PASSWORD;
   }
   $this->rehashPasswordIfNeeded((int) $rs['userID'], $rs['password'], $password);
   ```
   (`lib/Users.php:853-861`). It then maps account state to flags: `LOGIN_DISABLED` if `accessLevel <= ACCESS_LEVEL_DISABLED` (`:871-874`), `LOGIN_ROOT_ONLY` under `CATS_SLAVE` for non-root (`:877-880`), else `LOGIN_SUCCESS` (`:882`). Status constants are defined at `lib/Users.php:41-47`.
6. **Password verification (the actual crypto).** `verifyAndMigratePassword($userID, $password, $storedHash)` (`lib/Users.php:1296`): if the stored hash matches the legacy MD5 pattern `^[0-9a-f]{32}$` (`isLegacyPasswordHash`, `lib/Users.php:1291-1294`), it compares `md5($password) !== $storedHash` and lazily re-hashes on success (`:1298-1308`); otherwise it returns:
   ```php
   return password_verify($password, $storedHash);
   ```
   (`lib/Users.php:1310`). Hashing uses `password_hash($password, PASSWORD_DEFAULT)` (`hashPassword`, `lib/Users.php:1256-1259`). `rehashPasswordIfNeeded()` upgrades hashes via `password_needs_rehash()` when not legacy/LDAP (`lib/Users.php:1272-1283`).
7. **Back in `processLogin`.** It re-queries the full user+site row (`lib/Session.php:682-721`). On `LOGIN_INVALID_PASSWORD`, `LOGIN_ROOT_ONLY`, `LOGIN_DISABLED`, `LOGIN_PENDING_APPROVAL` it sets `_isLoggedIn = false` and an appropriate `_loginError`, logging failures via `Users::addLoginHistory(...)` (`lib/Session.php:749-809`). On `LOGIN_SUCCESS` (`:811`) it populates session state — `_username`, `_userID`, `_siteID`, `_accessLevel`, `_realAccessLevel`, `_email`, etc. (`:812-841`) — forces EEO visibility for SA+ (`:844-848`), demotes inactive accounts to `ACCESS_LEVEL_READ` (`:862-866`) and deleted to `ACCESS_LEVEL_DISABLED` (`:868-872`), records a successful login history row (`:883-899`), sets `_isLoggedIn = true` (`:900`), and issues the session cookie via `setcookie('session_cookie', $cookieValue, ...)` with `secure`/`httponly`/`samesite=Lax` (`lib/Session.php:917-944`).
8. **Post-login routing (controller).** After `processLogin`, the controller checks `isLoggedIn()`. On failure it pulls `getLoginError()` (`lib/Session.php:1075-1078`), fires `LOGIN_UNSUCCESSFUL`, and re-renders `Login.tpl` (`modules/login/LoginUI.php:250-283`). On success it reads `getAccessLevel(ACL::SECOBJ_ROOT)` (`modules/login/LoginUI.php:288`; `getAccessLevel` delegates to `ACL::getAccessLevel(...)` at `lib/Session.php:404-407`) and routes:
   - fires `LOGGED_IN` hook (`:385`); honors `reloginVars` redirect (`:387-390`);
   - fires `LOGGED_IN_MESSAGES` (`:393`);
   - if `accessLevel >= ACCESS_LEVEL_SA` and `getSiteName() === 'default_site'` → `m=settings&a=upgradeSiteName` (`:404-408`);
   - if SA+ and mailer `configured == '0'` → renders the "E-Mail Disabled" `NewInstallWizard.tpl` (`:411-422`);
   - if `getEmail()` empty → `m=settings&a=forceEmail` (`:425-428`);
   - otherwise fires `LOGGED_IN_HOME_PAGE` and redirects to `m=home` (`:431-435`).

   The first-login wizard block (welcome/license/password/site/users pages) is present but **commented out / disabled**; the admin-default-password redirect is gated by a literal `false &&` (`modules/login/LoginUI.php:293-401`).

**Logout** is not a handler in this module. `CATSSession::logout()` simply sets `_isLoggedIn = false` (`lib/Session.php:254-257`); the actual logout flow lives in the front controller under `m=logout`, which requires POST, calls `logout()`, unsets `$_SESSION['CATS']` and `$_SESSION['modules']`, and redirects to `m=login` (`index.php:210-257`).

## Forgot password flow

- **Display (GET):** `forgotPassword()` fires the `FORGOT_PASSWORD` hook and displays `ForgotPassword.tpl` (`modules/login/LoginUI.php:442-447`). The template posts back to `?m=login&a=forgotPassword` with a hidden `postback=true` field (`modules/login/ForgotPassword.tpl:34-43`).
- **Process (POST):** `onForgotPassword()` reads `username` via `getTrimmedInput('username', $_POST)` (`modules/login/LoginUI.php:455`), fires `ON_FORGOT_PASSWORD` (`:457`), then:
  ```php
  $user = new Users($this->_siteID);
  if ($password = $user->getPassword($username))
  {
      $mailer = new Mailer($this->_siteID);
      $mailerStatus = $mailer->sendToOne(
          array($username, $username),
          PASSWORD_RESET_SUBJECT,
          sprintf(PASSWORD_RESET_BODY, $password),
          true
      );
      ...
  ```
  (`modules/login/LoginUI.php:459-468`). On mail success it assigns `complete = true`; on mail failure or unknown user it assigns a failure `message`; either way it re-displays `ForgotPassword.tpl` (`:470-487`). The success branch of the template tells the user an email "containing your password" was sent (`modules/login/ForgotPassword.tpl:44-49`).

This flow has two source-level defects (see Unverified): `Users::getPassword()` and the constants `PASSWORD_RESET_SUBJECT` / `PASSWORD_RESET_BODY` do not exist in the repository.

## LDAP

LDAP is present (`lib/LDAP.php`) and wired into authentication via `Users::isCorrectLogin`. The directory mode is selected by the `AUTH_MODE` config constant, defaulting to `'sql'` (`config.php:48`). LDAP is engaged when `AUTH_MODE == 'ldap' || AUTH_MODE == 'sql+ldap'` and either the DB record's password equals the LDAP sentinel or the user is absent from the DB:

```php
if((AUTH_MODE == 'ldap' || AUTH_MODE == 'sql+ldap')
    && (($existsInDB && $rs['password'] == LDAPUSER_PASSWORD) || !$existsInDB) ) {
    $this->_ldap = LDAP::getInstance($username, $password);
    if($this->_ldap == NULL)            { return LOGIN_INVALID_USER; }
    if(!$this->_ldap->authenticate($username, $password)) { return LOGIN_INVALID_PASSWORD; }
    $existsInLDAP = true;
}
```

(`lib/Users.php:838-849`). The LDAP sentinel password is `define('LDAPUSER_PASSWORD', '_LDAPUSER_')` (`lib/Users.php:56`), and LDAP rows store that sentinel rather than a real hash; `verifyAndMigratePassword`/`rehashPasswordIfNeeded` explicitly skip it (`lib/Users.php:1274`). A first-time LDAP user not yet in the DB is auto-created **disabled** via `Users::add(... LDAPUSER_PASSWORD, '0', false, LDAP_SITEID)` and returns `LOGIN_PENDING_APPROVAL` (`lib/Users.php:863-867`). `isUserLDAP($userID)` reports whether a user's stored password is the sentinel (`lib/Users.php:1331-1344`), and LDAP users are barred from changing/resetting their password (`lib/Users.php:651-653`, `:736-738`).

`lib/LDAP.php` is a singleton (`getInstance()`, `lib/LDAP.php:15-28`, private constructor `:34`). `connect()` uses `ldap_connect(LDAP_HOST, LDAP_PORT)` with protocol version and, when `LDAP_AD`, disables referrals (`lib/LDAP.php:38-52`). `authenticate($username, $password)` (`lib/LDAP.php:54`): if `LDAP_BIND_DN` is set it binds with the service account, searches `LDAP_BASEDN` on `LDAP_ATTRIBUTE_UID`, then re-binds as the found entry DN with the user's password (`:58-92`); otherwise it binds directly using the `LDAP_ACCOUNT` template with `{$username}` substituted (`:93-105`). `getUserInfo($username)` returns `[lastName, firstName, email, uid]` from directory attributes (`lib/LDAP.php:127-140`), consumed by the auto-provisioning path above. Default LDAP config constants live at `config.php:255` (`LDAP_HOST`), `:261` (`LDAP_BIND_DN`), `:272` (`LDAP_SITEID`).

## Templates

- `modules/login/Login.tpl` — main login page. Posts to `?m=login&a=attemptLogin` (appending `&reloginVars=...` when set) with `onsubmit="return checkLoginForm(...)"` (`modules/login/Login.tpl:45`). Renders username/password inputs unless `siteNameFull == 'error'` (`:59-73`), shows version (`:78`), and conditionally shows demo-login link gated by `ENABLE_DEMO_MODE` (`:36-39`). Inline JS defines `demoLogin()` injecting `DEMO_LOGIN`/`DEMO_PASSWORD` (`:88-93`) and `defaultLogin()` hard-coding `admin`/`cats` and auto-submitting when `?defaultlogin` is present (`:94-103`). Calls `TemplateUtility::printCookieTester()` (`:133`).
- `modules/login/ForgotPassword.tpl` — forgot-password form posting to `?m=login&a=forgotPassword` with hidden `postback=true` (`:34-35`); shows the success confirmation when `complete` is set (`:44-49`).
- `modules/login/NoCookiesModal.tpl` — modal warning shown by the `noCookiesModal` action when cookies are disabled; offers a Retry button (`modules/login/NoCookiesModal.tpl:2-18`).

There is also a `modules/login/wizard/` directory of first-login wizard templates, but that flow is disabled in the controller (see Authentication flow step 8).

## JavaScript

- `modules/login/validator.js` — client-side form validation loaded by `Login.tpl` (`:10`) and `ForgotPassword.tpl` (`:10`). `checkLoginForm(form)` concatenates `checkUsername()` + `checkPassword()` and `alert()`s on any error, returning `false` to block submit (`modules/login/validator.js:30-44`). `checkUsername()`/`checkPassword()` flag empty fields and turn the label red (`:46-84`). Note: both helpers test the DOM *element* (`document.getElementById(...)`) against `""` rather than its `.value`, so the empty-field check never triggers as written (see Unverified).
- The templates also load `js/lib.js` and `js/submodal/subModal.js` (`Login.tpl:9-11`) and inline scripts for focus and demo/default login (`Login.tpl:84-104`).

## lib/ dependencies

Includes at the top of the module (`modules/login/LoginUI.php:30-35`): `lib/SystemInfo.php`, `lib/Mailer.php`, `lib/Site.php`, `lib/NewVersionCheck.php`, `lib/Wizard.php`, `lib/License.php`.

Runtime dependencies actually invoked:
- **`lib/Session.php` (CATSSession):** `processLogin($username, $password, $addToHistory = true)` (`:666`), `isLoggedIn()` (`:244-247`), `logout()` (`:254-257`), `getLoginError()` (`:1075-1078`), `getAccessLevel($securedObjectName)` (`:404-407`), `getSiteName()` (`:449`), `getEmail()` (`:504`), `getUnixName()` (`:460`), `getSiteID()` (`:321`), `checkForceLogout()` (`:172`), `isFirstTimeSetup()` (`:282`). CSRF helpers: `getCSRFToken()` (`:1226`), `rotateCSRFToken()` returning `bin2hex(random_bytes(32))` (`:1243-1250`), `isCSRFTokenValid($token)` using `hash_equals(...)` (`:1258-1268`). **Note:** the login templates and `attemptLogin()` do not themselves read or validate the CSRF token; these methods exist on the session but are not exercised by this module's code paths as read.
- **`lib/Users.php`:** `isCorrectLogin($username, $password)` (`:796`), `addLoginHistory(...)` (`:973`, called from `Session::processLogin`), `verifyAndMigratePassword($userID, $password, $storedHash)` (`:1296`), `isUserLDAP($userID)` (`:1331`). Status constants `LOGIN_SUCCESS`/`LOGIN_INVALID_USER`/`LOGIN_INVALID_PASSWORD`/`LOGIN_DISABLED`/`LOGIN_ROOT_ONLY`/`LOGIN_PENDING_APPROVAL` (`:41-47`).
- **`lib/LoginActivity.php`:** constructor `__construct($rowsPerPage, $currentPage, $siteID, $successful = true)` (`:48`), `updateHostName($userLoginID, $hostName)` (`:94`), `getPage()` (`:119`). This class is *not* referenced by `LoginUI.php`; login history is recorded by `Users::addLoginHistory` from within `Session::processLogin` (`lib/Session.php:758`, `:776`, `:794`, `:886`).
- **`lib/LDAP.php`:** singleton used by `Users::isCorrectLogin` (see LDAP section).
- **`lib/Site.php`:** `getSiteByUnixName()` for site-scoped login (`modules/login/LoginUI.php:138`, `:205`, `:239`, `:264`).
- **`lib/Mailer.php`:** `Mailer::sendToOne(...)` for the forgot-password email (`modules/login/LoginUI.php:462-468`).

## Hooks fired

All hooks are invoked through the repo's `eval(Hooks::get('KEY'))` convention and abort the handler when the eval'd code returns false.

| Hook key | Location |
|----------|----------|
| `SHOW_LOGIN_FORM_PRE` | `modules/login/LoginUI.php:130` |
| `SHOW_LOGIN_FORM_POST` | `modules/login/LoginUI.php:154` |
| `SHOW_LOGIN_FORM_POST_2` | `modules/login/LoginUI.php:164` |
| `NO_COOKIES_MODAL` | `modules/login/LoginUI.php:171` |
| `LOGIN_NO_CREDENTIALS` | `modules/login/LoginUI.php:215` |
| `LOGIN_UNSUCCESSFUL` | `modules/login/LoginUI.php:274` |
| `LICENSE_TERMS` | `modules/login/LoginUI.php:311` (inside the disabled wizard block) |
| `ASP_WIZARD_PAGES` | `modules/login/LoginUI.php:353` (disabled wizard block) |
| `ASP_WIZARD_IMPORT` | `modules/login/LoginUI.php:368` (disabled wizard block) |
| `LOGGED_IN` | `modules/login/LoginUI.php:385` |
| `LOGGED_IN_MESSAGES` | `modules/login/LoginUI.php:393` |
| `LOGGED_IN_HOME_PAGE` | `modules/login/LoginUI.php:433` |
| `FORGOT_PASSWORD` | `modules/login/LoginUI.php:444` |
| `ON_FORGOT_PASSWORD` | `modules/login/LoginUI.php:457` |
| `TRANSPARENT_LOGIN_POST` | `lib/Session.php:1050` (fired from `CATSSession::transparentLogin()`, not from this module) |

The `LOGIN_*` keys present are `LOGIN_NO_CREDENTIALS` and `LOGIN_UNSUCCESSFUL`. The `SHOW_LOGIN_FORM_*` keys are `SHOW_LOGIN_FORM_PRE`, `SHOW_LOGIN_FORM_POST`, and `SHOW_LOGIN_FORM_POST_2`. `TRANSPARENT_LOGIN_POST` and `FORGOT_PASSWORD`/`ON_FORGOT_PASSWORD` are present as cited.

## Source evidence

- `modules/login/LoginUI.php` — read in full (1-516).
- `modules/login/Login.tpl` — read in full (1-135).
- `modules/login/ForgotPassword.tpl` — read in full (1-70).
- `modules/login/NoCookiesModal.tpl` — read in full (1-20).
- `modules/login/validator.js` — read in full (1-84).
- `lib/Session.php` — `checkForceLogout` (160-179), `isLoggedIn`/`logout` (244-257), `getAccessLevel` (404-407), `processLogin` (666-968), `transparentLogin`/`TRANSPARENT_LOGIN_POST` (980-1067), `getLoginError` (1075-1078), CSRF methods (1226-1268).
- `lib/Users.php` — status/LDAP constants (37-56), `isCorrectLogin` (796-883), password hashing/verification/migration (1256-1327), `isUserLDAP` (1331-1344).
- `lib/LDAP.php` — read in full (1-142).
- `lib/LoginActivity.php` — method signatures (48, 94, 119).
- `lib/UserInterface.php` — `_authenticationRequired` default (50) and `requiresAuthentication()` (177-185).
- `index.php` — auth dispatch and logout flow (180-269).
- `config.php` — `AUTH_MODE` (48), demo/single-session (166,172,185), LDAP (255,261,272).

## Unverified / open questions

- **`Users::getPassword()` does not exist.** `onForgotPassword()` calls `$user->getPassword($username)` (`modules/login/LoginUI.php:460`), but a repo-wide search found no `function getPassword` in `lib/Users.php` (only `CATSSession::getPassword()` at `lib/Session.php:367`, which is the logged-in user's stored hash and is unrelated). As read, the forgot-password POST path would fatal on an undefined method. Not runtime-confirmed here.
- **`PASSWORD_RESET_SUBJECT` / `PASSWORD_RESET_BODY` are undefined.** These are used at `modules/login/LoginUI.php:465-466`, but `grep -rln "PASSWORD_RESET_SUBJECT"` matched only `modules/login/LoginUI.php` — no `define()` exists anywhere in the repo. Combined with the point above, the forgot-password flow appears non-functional in this tree.
- **Plaintext password email implied.** The success template states an email "containing your password" was sent (`modules/login/ForgotPassword.tpl:46`) and the handler `sprintf(PASSWORD_RESET_BODY, $password)` formats the value from `getPassword()` into the mail body — but since passwords are stored as `password_hash()` output (`lib/Users.php:1258`), no plaintext password is recoverable. The intended behavior of this flow is unclear/legacy; not verified.
- **`validator.js` empty-field check is ineffective.** `checkUsername`/`checkPassword` compare the DOM element (`document.getElementById(...)`) to `""` instead of its `.value` (`modules/login/validator.js:50-52`, `:70-72`), so the comparison is always false and validation never blocks an empty field. Behavior not runtime-confirmed.
- **CSRF not enforced on the login POST.** `CATSSession` provides `getCSRFToken`/`isCSRFTokenValid` (`lib/Session.php:1226-1268`), but neither `Login.tpl` nor `attemptLogin()` emits or checks a token as read here. Whether CSRF protection is intentionally omitted for the unauthenticated login form is an open question.
- The first-login **wizard** block and the admin-default-password redirect are present but disabled (commented out / gated by `false &&`) per `modules/login/LoginUI.php:293-401`; whether they are ever exercised was not verified.

---

ACL-SUMMARY
```
login.attemptLogin    => (none)  # unauthenticated module (_authenticationRequired = false)
login.forgotPassword  => (none)  # unauthenticated
login.noCookiesModal  => (none)  # unauthenticated
login.showLoginForm   => (none)  # unauthenticated (default action)
```
Note: the login module sets `_authenticationRequired = false` (`modules/login/LoginUI.php:43`); none of its actions carry a `getAccessLevel(...)` ACL guard, because they must be reachable while logged out.
