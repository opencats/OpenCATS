# Module: queue

## Overview

The web-facing controller is `QueueUI`, declared as:

```php
class QueueUI extends UserInterface
```

(modules/queue/QueueUI.php:35). It extends the standard `UserInterface` base class (lib/UserInterface.php), like every other in-app module.

**Constructor** (modules/queue/QueueUI.php:37-46):

```php
public function __construct()
{
    parent::__construct();

    $this->_authenticationRequired = true;
    $this->_moduleDirectory = 'queue';
    $this->_moduleName = 'queue';
    $this->_moduleTabText = '';
    $this->_subTabs = array();
}
```

Key facts derived directly from the constructor:

- `$this->_authenticationRequired = true` (modules/queue/QueueUI.php:41) — a logged-in session is required to dispatch to this module, but note there is no per-action ACL guard anywhere in the controller (see Action catalog).
- `$this->_moduleTabText = ''` (modules/queue/QueueUI.php:44) — empty tab text, so the module renders no top-level navigation tab.
- `$this->_subTabs = array()` (modules/queue/QueueUI.php:45) — no sub-tabs.

**Request dispatch** (modules/queue/QueueUI.php:49-57):

```php
public function handleRequest()
{
    $action = $this->getAction();
    switch ($action)
    {
        default:
            break;
    }
}
```

The `switch` has **only a `default` case that breaks** — i.e. the controller accepts no actions and renders nothing. `getAction()` (inherited, lib/UserInterface.php:193-201) returns `$_GET['a']` if set, otherwise `''`; whatever value arrives, control falls straight through `default` and `handleRequest()` returns without output. The module therefore has no usable web UI: it exists only as a registered module slot.

**Relationship to QueueCLI.php and QueueProcessor.** The file header comment in `QueueUI.php` (modules/queue/QueueUI.php:27-29) is stale/misleading — it describes an XML job-posting export, which the code does not implement. The real async-queue engine lives entirely outside this controller:

- `QueueCLI.php` (repo root) is the cron entry point. It bootstraps config + libs (QueueCLI.php:38-52), starts a session, calls `ModuleUtility::registerModuleTasks()` to load each module's `tasks/tasks.php` (QueueCLI.php:64; lib/ModuleUtility.php:86-101), then runs `QueueProcessor::startNextTask()` (QueueCLI.php:69), touches `QUEUE_STATUS_FILE` (QueueCLI.php:72), and periodically runs `cleanUpErroredTasks()` / `cleanUpOldQueues()` (QueueCLI.php:82-87). It is documented elsewhere.
- `lib/QueueProcessor.php` is the static-method engine that manipulates the `queue` table (insert/lock/error/complete/cleanup) and instantiates/executes task classes from `modules/queue/tasks/`.
- This `QueueUI` module is the in-app "view/trigger" slot for the queue — but as shipped it triggers and views nothing. There is no call from `QueueUI` into `QueueProcessor`.

## Action catalog

The `switch` in `handleRequest()` contains exactly one case: `default`. There are no named action cases, hence no per-action ACL guards.

| Action | Exact ACL guard | Required level | Handler | lib calls | Template |
|--------|-----------------|----------------|---------|-----------|----------|
| `default` (any/empty `a`) | _(none — no `getUserAccessLevel(...)` call anywhere in QueueUI.php)_ | Authenticated session only (`$this->_authenticationRequired = true`, modules/queue/QueueUI.php:41); no access-level check | `break;` — no-op (modules/queue/QueueUI.php:54-55) | _(none)_ | _(none — no `Template`/`display` call)_ |

## Per-action detail with cites

### `default` (and every other value of `$_GET['a']`)

There is only one branch in the dispatch switch (modules/queue/QueueUI.php:52-56):

```php
switch ($action)
{
    default:
        break;
}
```

- **Guard:** The controller never calls `getUserAccessLevel(...)` (the base implementation is at lib/UserInterface.php:429). The sole access control is module-level authentication via `$this->_authenticationRequired = true` (modules/queue/QueueUI.php:41), enforced by the framework before dispatch, not inside this module.
- **Behavior:** `$action = $this->getAction();` (modules/queue/QueueUI.php:51) reads `$_GET['a']`; regardless of value, execution hits `default: break;` and `handleRequest()` returns. No `QueueProcessor` calls, no template render, no data mutation.
- **Net effect:** Hitting `index.php?m=queue` (with or without `&a=...`) while logged in produces no module output.

## The queue table

Defined in db/cats_schema.sql:884-897:

```sql
CREATE TABLE `queue` (
  `queue_id` int(11) NOT NULL AUTO_INCREMENT,
  `site_id` int(11) NOT NULL,
  `task` varchar(125) NOT NULL,
  `args` text,
  `priority` tinyint(2) NOT NULL DEFAULT '5' COMMENT '1-5, 1 is highest priority',
  `date_created` datetime NOT NULL,
  `date_timeout` datetime NOT NULL,
  `date_completed` datetime DEFAULT NULL,
  `locked` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `error` tinyint(1) unsigned DEFAULT '0',
  `response` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`queue_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
```

Column-by-column (cited from db/cats_schema.sql):

- `queue_id` (line 885) — auto-increment PK; used as `$taskID` throughout QueueProcessor.
- `site_id` (line 886) — owning site; `startNextTask()` reads it and passes to `startTask()` (lib/QueueProcessor.php:194); recurring tasks use `CATS_ADMIN_SITE` (lib/QueueProcessor.php:156-157).
- `task` (line 887, varchar 125) — task name/path. Inserted by `addAsynchronousTask()` (lib/QueueProcessor.php:287-288) and resolved via `getTaskNameFromPath()` (lib/QueueProcessor.php:219-226).
- `args` (line 888, text) — `serialize($args)` is stored on insert (lib/QueueProcessor.php:291); passed back to the task's `run()` (lib/QueueProcessor.php:247).
- `priority` (line 889, tinyint, default 5, comment "1-5, 1 is highest priority") — `startNextTask()` orders by `priority DESC` (lib/QueueProcessor.php:180). Note the comment says 1 is highest but the selection orders descending; see Unverified.
- `date_created` (line 890) — set to `date('c')` on insert (lib/QueueProcessor.php:293).
- `date_timeout` (line 891) — `now + DEFAULT_QUEUE_TIMEOUT_MINUTES*60` on insert (lib/QueueProcessor.php:282, 294); `cleanUpErroredTasks()` flips locked rows past timeout to error (lib/QueueProcessor.php:474-482).
- `date_completed` (line 892, nullable) — set by `setTaskCompleted()` (lib/QueueProcessor.php:99-117). `startNextTask()` only selects rows where `ISNULL(date_completed)` (lib/QueueProcessor.php:179).
- `locked` (line 893, default 0) — toggled by `setTaskLock()` (lib/QueueProcessor.php:54-70); set to 1 before running, 0 after (lib/QueueProcessor.php:231, 249).
- `error` (line 894, default 0) — set by `setTaskError()` (lib/QueueProcessor.php:73-96).
- `response` (line 895, varchar 255, nullable) — written by `setTaskResponse()` (lib/QueueProcessor.php:322-340), read by `getTaskResponse()` (lib/QueueProcessor.php:343-367).

## lib/QueueProcessor

`class QueueProcessor` (lib/QueueProcessor.php:47) is a static utility class; both `__construct()` and `__clone()` are private to prevent instantiation (lib/QueueProcessor.php:50-51). It includes `modules/queue/constants.php` (lib/QueueProcessor.php:40), which defines `TASKRET_*`, `QUEUE_EXPIRATION_DAYS`, `DEFAULT_QUEUE_TIMEOUT_MINUTES`, etc. (modules/queue/constants.php:33-47).

Methods (real signatures + lines):

- `public static function setTaskLock($taskID, $lockCode = 1)` (lib/QueueProcessor.php:54) — `UPDATE queue SET locked = ...`.
- `public static function setTaskError($taskID, $errorCode = 1)` (lib/QueueProcessor.php:73) — `UPDATE queue SET error = ...`; when `$errorCode == 1`, evals hook `QUEUEERROR_NOTIFY_DEV` (lib/QueueProcessor.php:90-93).
- `public static function setTaskCompleted($taskID, $completedTime = 0)` (lib/QueueProcessor.php:99) — sets `date_completed`.
- `public static function registerRecurringTask($taskPath)` (lib/QueueProcessor.php:126) — checks `isTaskReady($task->getSchedule())`, guards against an already-`locked` instance, then `addAsynchronousTask(CATS_ADMIN_SITE, ...)` + `startTask(...)` (lib/QueueProcessor.php:130-157). This is the method invoked from module `tasks/tasks.php` files (e.g. modules/queue/tasks.php:41).
- `public static function startNextTask()` (lib/QueueProcessor.php:166) — selects the next `locked=0 AND error=0 AND ISNULL(date_completed)` row ordered `priority DESC LIMIT 1`, returns `TASKRET_NO_TASKS` if none, else `startTask(...)` (lib/QueueProcessor.php:170-197). Called by QueueCLI.php:69.
- `public static function getInstantiatedTask($taskPath)` (lib/QueueProcessor.php:201) — `include_once` + `eval('new <Name>()')` (lib/QueueProcessor.php:209-210).
- `public static function getTaskNameFromPath($taskPath)` (lib/QueueProcessor.php:219) — regex extracts the class name from a `.php` path.
- `public static function startTask($siteID, $taskPath, $args, $priority, $taskID)` (lib/QueueProcessor.php:229) — locks, instantiates, calls `$curTask->run($siteID, $args)`, unlocks, then on return value marks completed/error and (for `TASKRET_SUCCESS_NOLOG`) removes the row (lib/QueueProcessor.php:231-274).
- `public static function addAsynchronousTask($siteID, $taskPath, $args, $priority = 5)` (lib/QueueProcessor.php:278) — `INSERT INTO queue (...)` with `serialize($args)`, returns `getLastInsertID()` (lib/QueueProcessor.php:284-299).
- `public static function removeTask($taskID)` (lib/QueueProcessor.php:303) — `DELETE FROM queue`.
- `public static function setTaskResponse($taskID, $response)` (lib/QueueProcessor.php:322) / `public static function getTaskResponse($taskID)` (lib/QueueProcessor.php:343) — write/read the `response` column.
- `public static function getActiveTasksCount()` (lib/QueueProcessor.php:370), `getLockedTasksCount()` (lib/QueueProcessor.php:398), `getErrorTasksCount()` (lib/QueueProcessor.php:422) — count queries by state.
- `public static function cleanUpOldQueues()` (lib/QueueProcessor.php:446) — deletes completed rows older than `QUEUE_EXPIRATION_DAYS`.
- `public static function cleanUpErroredTasks()` (lib/QueueProcessor.php:469) — sets `error=1, locked=0` for locked rows past `date_timeout`.
- `public static function getLastRunTime()` (lib/QueueProcessor.php:493) — `filemtime(QUEUE_STATUS_FILE)`.
- `public static function isActive()` (lib/QueueProcessor.php:513) — true if last run < 5 minutes ago.
- `public static function isTaskReady($schedule)` (lib/QueueProcessor.php:528) — crontab-string matcher.
- Time helpers: `getDayOfMonth()` (lib/QueueProcessor.php:620), `getDayOfWeek()` (lib/QueueProcessor.php:626), `getMonth()` (lib/QueueProcessor.php:632), `getYear()` (lib/QueueProcessor.php:638), `getHour()` (lib/QueueProcessor.php:644), `getMinute()` (lib/QueueProcessor.php:650).

Supporting class: `class Task` (modules/queue/lib/Task.php:29) is the base class each task extends; it exposes `setTaskID()`, `setResponse()` (delegates to `QueueProcessor::setTaskResponse()`, modules/queue/lib/Task.php:42), name/description getters/setters, and the same time helpers (modules/queue/lib/Task.php:35-93).

Registered task (this module): `QueueProcessor::registerRecurringTask('CleanExceptions');` in modules/queue/tasks.php:41. `SampleTask.php` and `SampleRecurring.php` are templates only.

## Hooks fired

- `QUEUEERROR_NOTIFY_DEV` — fired only inside `QueueProcessor::setTaskError()` via `eval(Hooks::get('QUEUEERROR_NOTIFY_DEV'))` when `$errorCode == 1` (lib/QueueProcessor.php:90-93). This is in the lib, not in `QueueUI`. **No hook is fired by the `QueueUI` controller itself.** A repo-wide grep finds this string only at lib/QueueProcessor.php:92 — there is no `setHook('QUEUEERROR_NOTIFY_DEV', ...)` definition anywhere, so `Hooks::get()` returns its default (see Unverified).

## Source evidence

- modules/queue/QueueUI.php (read in full, lines 1-60): controller class, constructor, empty-switch `handleRequest()`.
- modules/queue/constants.php (lines 33-47): `TASKRET_*`, `QUEUE_EXPIRATION_DAYS=7`, `DEFAULT_QUEUE_TIMEOUT_MINUTES=60`, `QUEUE_TASK_DIR`.
- modules/queue/tasks.php (line 39-41): `registerRecurringTask('CleanExceptions')`.
- modules/queue/lib/Task.php (lines 29-94): base `Task` class.
- modules/queue/tasks/: `CleanExceptions.php` (active), `SampleTask.php`, `SampleRecurring.php` (templates), plus a nested `lib/` and `tasks.php`.
- lib/QueueProcessor.php (read in full, lines 1-659): the engine.
- QueueCLI.php (read in full, lines 1-111): cron entry point.
- db/cats_schema.sql:858 (module_schema row for `queue`, version 0), :884-897 (`queue` table DDL).
- lib/UserInterface.php:193-201 (`getAction()`), :429 (`getUserAccessLevel()` base), :50/179-181 (`_authenticationRequired`).
- lib/ModuleUtility.php:86-101 (`registerModuleTasks()`).
- No `.tpl` or `.js` files exist for this module (verified by find under modules/queue and templates/*queue*).

## Unverified / open questions

- **No `.tpl`/`.js`:** `find` returned no templates or JavaScript for the `queue` module. Confirmed absent, but worth noting the framework expects modules to have templates; this one renders nothing.
- **Stale header comment:** modules/queue/QueueUI.php:27-29 claims the module "builds an XML file containing public job postings." No such code exists in the file; the comment is inherited boilerplate and is misleading. Not verified against any other module.
- **`QUEUEERROR_NOTIFY_DEV` hook default:** The hook is consumed (`Hooks::get(...)` then `eval`) at lib/QueueProcessor.php:92 but I found no registration of it in the repo. The runtime default returned by `Hooks::get()` for an unregistered key (and whether `eval` of that default returns truthy) was not verified — I did not open lib/Hooks.php's `get()` implementation.
- **`priority` ordering semantics:** the column comment says "1-5, 1 is highest priority" (db/cats_schema.sql:889) but `startNextTask()` orders `priority DESC` (lib/QueueProcessor.php:180), which would pick 5 first. This apparent inconsistency is noted from source but its intended resolution is not verified.
- **`startTask()` `$priority` param:** `startTask()` accepts `$priority` (lib/QueueProcessor.php:229) but does not use it in its body. Observed, not explained.
- **`QueueCLI.php` switch duplicate:** the status `switch` has two `case TASKRET_SUCCESS:` arms (QueueCLI.php:101 and :104), so the "(NO LOG)" branch is dead and `TASKRET_SUCCESS_NOLOG` is never printed. Noted from source; CLI is documented elsewhere.
