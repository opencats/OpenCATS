# Module: calendar

## Overview

The calendar module is implemented by a single controller class:

```php
class CalendarUI extends UserInterface
```

declared at `modules/calendar/CalendarUI.php:35`. It includes `lib/Calendar.php`, `lib/DateUtility.php`, and `lib/SystemUtility.php` at the top of the file (`modules/calendar/CalendarUI.php:30-32`).

The constructor (`modules/calendar/CalendarUI.php:37-50`) sets:

- `$this->_authenticationRequired = true;` (`:41`)
- `$this->_moduleDirectory = 'calendar';` (`:42`)
- `$this->_moduleName = 'calendar';` (`:43`)
- `$this->_moduleTabText = 'Calendar*al=' . ACCESS_LEVEL_READ . '@calendar';` (`:44`) — the module tab requires `ACCESS_LEVEL_READ` on the `calendar` module.
- `$this->_subTabs` (`:45-49`) with three entries:
  - `'My Upcoming Events'` → `javascript:void(0);*js=calendarUpcomingEvents();*al=ACCESS_LEVEL_READ@calendar` (`:46`)
  - `'Add Event'` → `javascript:void(0);*js=userCalendarAddEvent();*al=ACCESS_LEVEL_EDIT@calendar` (`:47`)
  - `'Goto Today'` → `javascript:void(0);*js=goToToday();*al=ACCESS_LEVEL_READ@calendar` (`:48`)

`handleRequest()` (`modules/calendar/CalendarUI.php:53-95`) reads the action via `$this->getAction()` (`:55`), fires the `CALENDAR_HANDLE_REQUEST` hook (`:57`), then dispatches on a `switch ($action)` (`:59`). Note: `handleRequest()` itself performs no access-level check; per-action authorization is enforced inside the individual handler methods (and the framework enforces the tab-level `ACCESS_LEVEL_READ@calendar` from the constructor before reaching the controller).

## Action catalog

One row per `switch` case in `handleRequest()` (`modules/calendar/CalendarUI.php:59-94`).

| Action (`a=`) | Exact ACL guard | Required level | Handler method | lib calls | Template |
|---|---|---|---|---|---|
| `addEvent` | `if ($this->getUserAccessLevel('calendar.addEvent') < ACCESS_LEVEL_EDIT)` (`:352`) — only runs on postback (`:62`) | `ACCESS_LEVEL_EDIT` | `onAddEvent()` (`:350`) | `Calendar::addEvent(...)` (`:474`) | none (redirects via `CATSUtility::transferRelativeURI`, `:505`) |
| `editEvent` | `if ($this->getUserAccessLevel('calendar.editEvent') < ACCESS_LEVEL_EDIT)` (`:514`); plus owner-or-SA check `if ($eventRS['enteredBy'] != $this->_userID && $this->getUserAccessLevel('calendar.show') < ACCESS_LEVEL_SA)` (`:593-594`) — only runs on postback (`:69`) | `ACCESS_LEVEL_EDIT` (own events); `ACCESS_LEVEL_SA` to edit others' | `onEditEvent()` (`:512`) | `Calendar::get(...)` (`:586`), `Calendar::updateEvent(...)` (`:678`) | none (redirects, `:706`) |
| `dynamicData` | none (no `getUserAccessLevel` call in `dynamicData()`) | none beyond the module tab's `ACCESS_LEVEL_READ@calendar` | `dynamicData()` (`:312`) | `Calendar::makeEventString(...)`, `Calendar::getEventArray(...)` (`:336-340`) | none (echoes raw event string, `:344`) |
| `deleteEvent` | `if ($this->getUserAccessLevel('calendar.deleteEvent') < ACCESS_LEVEL_DELETE)` (`:714`); plus owner-or-SA check `if ($eventRS['enteredBy'] != $this->_userID && $this->getUserAccessLevel('calendar.show') < ACCESS_LEVEL_SA)` (`:734-735`) — requires postback else `CommonErrors::fatal` (`:84-87`) | `ACCESS_LEVEL_DELETE` (own events); `ACCESS_LEVEL_SA` to delete others' | `onDeleteEvent()` (`:712`) | `Calendar::get(...)` (`:727`), `Calendar::deleteEvent(...)` (`:742`) | none (redirects, `:758`) |
| `showCalendar` / default | `getUserAccessLevel('calendar.show')` used to compute super-user flag: `$userIsSuperUser = ($this->getUserAccessLevel('calendar.show') < ACCESS_LEVEL_SA ? 0 : 1);` (`:180`) — no hard gate beyond the tab's `ACCESS_LEVEL_READ@calendar` | `ACCESS_LEVEL_READ` (tab) | `showCalendar()` (`:100`) | `Calendar::getEventArray`, `Calendar::makeEventString`, `Calendar::getAllEventTypes`, `Calendar::getUpcomingEventsHTML`, `CalendarSettings::getAll` (`:212-259`) | `./modules/calendar/Calendar.tpl` (`:306`) |

## Per-action detail

### showCalendar() — month/week/day view (`modules/calendar/CalendarUI.php:100-307`)

- Computes "now" values via `DateUtility::getAdjustedDate(...)` for hour/day/month/year/unixtime and `m-d-y` (`:102-107`), and `currentWeek` via `DateUtility::getWeekNumber(...)` minus the week number of the 1st of the month (`:109-111`).
- Date argument validation: if `month` and `year` are both valid IDs (`$this->isRequiredIDValid('month', $_GET)` / `'year'`, `:117-118`), it uses them and validates with `checkdate($month, 1, $year)` (`:123`), `CommonErrors::fatal(COMMONERROR_BADFIELDS, ...)` on failure (`:125`). Otherwise it defaults to the current month (`:139-141`).
- Reads optional `view`, `week`, `day`, `showEvent` from `$_GET` (`:144-178`); `view` defaults to the sentinel `'DEFAULT_VIEW'` (`:150`).
- Super-user toggle: `$userIsSuperUser` is `1` only when `getUserAccessLevel('calendar.show') >= ACCESS_LEVEL_SA` (`:180`); `$superUserActive` is true only when super-user AND `$_GET['superuser'] == 1` (`:181-188`).
- Builds a `Calendar($this->_siteID)` (`:193`) and assembles event strings for the previous, current, and next month using `getEventArray()` wrapped by `makeEventString()` (`:212-228`), then joins them with the super-user flag into `$eventsString` via `implode('@', ...)` (`:230-233`).
- Loads event types `getAllEventTypes()` (`:249`) and settings through `new CalendarSettings($this->_siteID)` → `getAll()` (`:251-252`); when `view == 'DEFAULT_VIEW'` it falls back to `$calendarSettingsRS['calendarView']` (`:254-257`).
- Builds the sidebar HTML via `getUpcomingEventsHTML(12, UPCOMING_FOR_CALENDAR)` (`:259`).
- Fires the `CALENDAR_SHOW` hook (`:261`).
- Reminder availability: `$allowEventReminders` is true only when `SystemUtility::isSchedulerEnabled() && !$_SESSION['CATS']->isDemo()` (`:263-270`).
- Assigns ~30 template vars (`:273-305`) including `dayHourStart`/`dayHourEnd`/`firstDayMonday` from settings, `allowAjax` (true when `noAjax == 0`, `:276`), `defaultPublic` (`:277`), `militaryTime => false` (`:278`), and `eventsString` (`:304`), then `display('./modules/calendar/Calendar.tpl')` (`:306`).

### dynamicData() — AJAX event feed (`modules/calendar/CalendarUI.php:312-345`)

- Requires valid `month`+`year` in `$_GET` else `CommonErrors::fatal(COMMONERROR_BADFIELDS, ...)` (`:318-332`); also runs `checkdate` (`:324`).
- Builds the event string for the requested month via `makeEventString(getEventArray($month,$year), ...)` (`:336-340`), fires the `CALENDAR_DATA` hook (`:342`), and `echo`es the raw string (`:344`). This is the endpoint the front-end calls at `?m=calendar&a=dynamicData&month=…&year=…` (`modules/calendar/Calendar.js:537-540`). No `getUserAccessLevel` guard exists in this method.

### onAddEvent() — add event (`modules/calendar/CalendarUI.php:350-506`)

- Guard: `if ($this->getUserAccessLevel('calendar.addEvent') < ACCESS_LEVEL_EDIT) { CommonErrors::fatal(COMMONERROR_PERMISSION, ...); }` (`:352-355`).
- Validates date `dateAdd` against `DATE_FORMAT_DDMMYY`/`DATE_FORMAT_MMDDYY` based on `$_SESSION['CATS']->isDateDMY()` (`:358-366`); requires a valid `type` (`:370-373`); duration defaults to 30 if not a valid optional ID (`:376-383`); requires `allDay` be `'0'` or `'1'` (`:386-390`).
- For non-all-day events, requires `hour`, `minute`, and `meridiem` ('AM'/'PM') (`:432-452`) and builds an `YYYY-MM-DD HH:MM:SS` string (`:455-466`); all-day events fix time to 12:00 AM (`:419-428`).
- Reads `publicEntry`, `reminderToggle`, `description`, `title`, `sendEmail`, `reminderTime` (`:403-408`); requires non-empty `title` (`:413-416`).
- Fires `CALENDAR_ADD_PRE` (`:471`), calls `Calendar::addEvent($type, $date, $description, $allDay, $this->_userID, -1, -1, null, $title, $duration, $reminderEnabled, $reminderEmail, $reminderTime, $publicEntry, $timeZoneOffset)` (`:474-478`) — note `dataItemID`/`dataItemType` passed as `-1` and `jobOrderID` as `null`. On `$eventID <= 0`, `CommonErrors::fatal(COMMONERROR_RECORDERROR, ...)` (`:480-483`).
- Rebuilds the query string without `a` and with `showEvent=$eventID` (`:492-501`), fires `CALENDAR_ADD_POST` (`:503`), and redirects via `CATSUtility::transferRelativeURI(...)` (`:505`).

### onEditEvent() — edit event (`modules/calendar/CalendarUI.php:512-707`)

- Guard: `if ($this->getUserAccessLevel('calendar.editEvent') < ACCESS_LEVEL_EDIT)` → fatal (`:514-517`).
- Requires valid `eventID` (`:520-523`) and `type` (`:527-530`); duration defaults to 30 (`:533-540`); optionally associates `dataItemID`+`dataItemType` (else `'NULL'`, `:543-553`) and `jobOrderID` (else `null`, `:556-563`).
- Loads the event with `Calendar::get($eventID)` (`:586`); empty result → fatal (`:588-591`).
- Ownership gate: `if ($eventRS['enteredBy'] != $this->_userID && $this->getUserAccessLevel('calendar.show') < ACCESS_LEVEL_SA)` → fatal (`:593-597`). Only the owner, or a user with `ACCESS_LEVEL_SA` on `calendar.show`, may edit.
- Same date/time parsing as add (`:566-673`).
- Fires `CALENDAR_EDIT_PRE` (`:675`), calls `Calendar::updateEvent($eventID, $type, $date, $description, $allDay, $dataItemID, $dataItemType, $jobOrderID, $title, $duration, $reminderEnabled, $reminderEmail, $reminderTime, $publicEntry, $_SESSION['CATS']->getTimeZoneOffset())` (`:678-681`); failure → `CommonErrors::fatal(COMMONERROR_RECORDERROR, ...)` (`:683`).
- Fires `CALENDAR_EDIT_POST` (`:686`); rebuilds the query string with `showEvent=$eventID` and redirects (`:694-706`).

### onDeleteEvent() — delete event (`modules/calendar/CalendarUI.php:712-759`)

- Reached only on postback; a non-postback `deleteEvent` request hits `CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid request.')` in `handleRequest()` (`:86`).
- Guard: `if ($this->getUserAccessLevel('calendar.deleteEvent') < ACCESS_LEVEL_DELETE)` → fatal (`:714-717`).
- Requires valid `eventID` (`:720-723`); loads via `Calendar::get()` (`:727`), empty → fatal (`:729-732`).
- Ownership gate identical to edit: owner or `ACCESS_LEVEL_SA` on `calendar.show` (`:734-738`).
- Fires `CALENDAR_DELETE_PRE` (`:740`), calls `Calendar::deleteEvent($eventID)` (`:742`), fires `CALENDAR_DELETE_POST` (`:744`), rebuilds the query string without `a`/`eventID` and redirects (`:747-758`).

### _getReminderTimeString() (`modules/calendar/CalendarUI.php:762-794`)

Private helper mapping a reminder offset in minutes to a phrase ("immediately", "in 1 minute", "in N minutes", "in 1 hour", … "in N days"). It is defined but not referenced by any handler in `CalendarUI.php`; an equivalent copy lives in the reminder task (`modules/calendar/tasks/Reminders.php:114-146`).

## Templates

- **`modules/calendar/Calendar.tpl`** — the only `display()`ed template (`CalendarUI.php:306`). Header loads CSS/JS bundle `Calendar.css`, `js/highlightrows.js`, `Calendar.js`, `CalendarUI.js`, `validator.js` (`Calendar.tpl:2`). Sets `window.CATSUserDateFormat` from `isDateDMY()` (`:6`). Notable structure:
  - Super-user "Show Entries from Other Users" checkbox shown only when `$this->userIsSuperUser == 1` (`:19-23`), wired to `refreshView()`.
  - Sidebar panels toggled by JS: upcoming events (`upcomingEventsTD`, prints `$this->summaryHTML`, `:44-45`), Add Event form (`addEventTD`, posts to `?m=calendar&a=addEvent`, `:47-191`), Edit Event form (`editEventTD`, posts to `?m=calendar&a=editEvent`, `:193-345`), and read-only View Event panel (`viewEventTD`, `:346-370`).
  - Add/Edit forms use `onsubmit="return checkAddForm(...)"` / `checkEditForm(...)` (`:49`, `:195`) from `validator.js`; type `<select>` is populated from `$this->calendarEventTypes` (`:69-71`, `:219-221`); date widgets via `DateInput(...)` (`:90`, `:240`).
  - Reminder UI is hidden unless `$this->allowEventReminders` (`:120`, `:270`).
  - Edit panel shows a Delete button only `if ($this->getUserAccessLevel('calendar.deleteEvent') >= ACCESS_LEVEL_DELETE)` (`:340-342`); View panel shows an Edit button only `if ($this->getUserAccessLevel('calendar.editEvent') >= ACCESS_LEVEL_EDIT)` (`:364-366`).
  - Three hidden grid containers — `calendarMonthParent` (6×7 month grid built in nested PHP loops, `:375-425`), `calendarWeekParent` (`:427-518`), `calendarDayParent` (hours from `$this->dayHourStart`..`dayHourEnd`, `:520-564`). Sunday-first vs Monday-first ordering is driven by `$this->firstDayMonday` (`:403`, `:410`, `:454`, `:484`).
  - Trailing `<script>` block (`:571-621`) exports PHP settings into JS globals (`indexName`, `todayDay/Month/Year/Hour`, `dayHourStart/End`, `userEmail`, `allowAjax`, `defaultPublic`, `userID`, `userIsSuperUser`, `firstDayMonday`, `accessLevel = getUserAccessLevel('calendar')` at `:587`), builds `entryTypesArray` from event types (`:590-596`), defines the `ACCESS_LEVEL_*` JS constants (`:598-604`), calls `calendarDataPopulateString($this->eventsString)` (`:607`), then selects the initial view (`setCalendarViewWeek/Day/Month`, `:610-616`) and optionally `handleClickEntryByID($this->showEvent)` (`:618-620`).
- **`modules/calendar/Error.tpl`** — a generic fatal-error page (`Error.tpl:18-22`). It is not `display()`ed anywhere in `CalendarUI.php`; the controller uses `CommonErrors::fatal(...)` instead.

## JavaScript

- **`modules/calendar/CalendarUI.js`** — sidebar/UI behaviors:
  - `calendarUpcomingEvents()` (`:155`), `userCalendarAddEvent()` (`:417`) — the two sub-tab handlers; `resetSideBar()` (`:487`) hides all four sidebar panels.
  - `calendarEditEvent(entry)` (`:161`) — populates the edit form; returns early `if (accessLevel < ACCESS_LEVEL_EDIT)` (`:163-166`), a GUI-level mirror of the server guard.
  - `calendarViewEvent(entry)` / `handleClickEntry` / `handleClickEntryByID` (`:255`, `:307`, `:312`) — populate the read-only view panel.
  - `addEventByDay(year, month, day, hour)` (`:333`) — opens the add form from a grid click; returns early `if (accessLevel < ACCESS_LEVEL_EDIT)` (`:342-345`).
  - `confirmDeleteEntry()` (`:426`) — builds and submits a POST form to `getCurrentCalendarUrl() + "&a=deleteEvent"` (`:435`) with `eventID` from `eventIDEdit`, and appends a `csrfToken` hidden input when `CATSCsrfToken` is defined (`:449-457`).
  - `setAddAllDayEnabled()` / `setEditAllDayEnabled()` (`:506`, `:521`) enable/disable time selects; `getDurationString` (`:536`) and `getReminderTimeString` (`:568`) format labels.
- **`modules/calendar/Calendar.js`** — grid engine and data model (~35KB). Key entry points: `calendarDataPopulateString()` (`:588`), `calendarDataPopulateServer(year, month)` which, when `allowAjax && totalRecordsInMemory < 500`, GETs `?m=calendar&a=dynamicData&month=…&year=…` (`:532-543`) and otherwise full-page navigates via `getCurrentCalendarUrl()` (`:546`); `handleResponse()` consumes the AJAX response (`:550-561`). `getCurrentCalendarUrl()` (`:502-529`) builds `?m=calendar&view=…&month=…&year=…&week=…&day=…` plus `&superuser=1` when the hide-non-public box is checked. View setters `setCalendarViewMonth/Week/Day` (`:765`, `:784`, `:805`) and the toolbar handlers `userCalendarViewMonth/Week/Day` (`:1260`, `:1265`, `:1311`) and `goToToday()` (`:1381`) drive navigation. Event-cell rendering helpers `generateCalendarEntrySmall` / `generateCalendarEntryDayView` / `generateCalendarEntryGrouped` live in `CalendarUI.js` (`:35`, `:85`, `:149`).
- **`modules/calendar/validator.js`** — `checkAddForm`/`checkEditForm` (`:14`, `:29`) call `checkEvent` (`:45`, requires an event type) and `checkDescription` (`:73`, requires a non-empty title). Despite the file header reading "Job Orders Form Validation" (`:2`) it is the calendar form validator.

## lib/ dependencies (cited)

All in `lib/Calendar.php`, instantiated as `new Calendar($this->_siteID)`:

- `class Calendar` — `lib/Calendar.php:56`; `__construct($siteID)` (`:63`), depends on `$_SESSION['CATS']->getUserID()` and `DatabaseConnection::getInstance()` (`:66-69`).
- `getEventArray($month, $year)` — `lib/Calendar.php:83`; returns a per-day array of events from `calendar_event` joined to `calendar_event_type` and `user` (`:87-150`). Called at `CalendarUI.php:213,219,225,337`.
- `makeEventString($eventArray, $month, $year, $showAllUsersEvents = true)` — `lib/Calendar.php:545`; serializes events for `Calendar.js`. Called at `CalendarUI.php:212,218,224,336`.
- `getAllEventTypes()` — `lib/Calendar.php:257`; selects `typeID`, `description`, `iconImage` from `calendar_event_type` (`:259-270`). Called at `CalendarUI.php:249`.
- `get($eventID)` — `lib/Calendar.php:280`; returns `eventID`, `enteredBy` for the event in the current site (`:282-296`). Called at `CalendarUI.php:586,727`.
- `addEvent($type, $date, $description, $allDay, $enteredBy, $dataItemID, $dataItemType, $jobOrderID, $title, $duration, $reminderEnabled, $reminderEmail, $reminderTime, $isPublic, $timeZoneOffset)` — `lib/Calendar.php:323`; INSERTs into `calendar_event`, returns new ID or `-1` (`:337-401`). Called at `CalendarUI.php:474`.
- `updateEvent($eventID, $type, $date, $description, $allDay, $dataItemID, $dataItemType, $jobOrderID, $title, $duration, $reminderEnabled, $reminderEmail, $reminderTime, $isPublic, $timeZoneOffset)` — `lib/Calendar.php:427`. Called at `CalendarUI.php:678`.
- `deleteEvent($eventID)` — `lib/Calendar.php:515`; DELETEs scoped by `calendar_event_id` AND `site_id` (`:517-528`). Called at `CalendarUI.php:742`.
- `getUpcomingEventsHTML($limit, $flag = UPCOMING_FOR_CALENDAR)` — `lib/Calendar.php:685`. Called at `CalendarUI.php:259`.
- `getAllDueReminders()` — `lib/Calendar.php:209`; used by the reminder task. Called at `modules/calendar/tasks/Reminders.php:58`.
- `updateEventDisableReminder($eventID)` — `lib/Calendar.php:494`; sets `reminder_enabled = 0`. Called at `Reminders.php:102`.
- `sendEmail($siteID, $userID, $destination, $subject, $body)` — `lib/Calendar.php:988`. Called at `Reminders.php:93`.
- `class CalendarSettings` — `lib/Calendar.php:1018`; `__construct($siteID)` (`:1025`), `getAll()` (`:1039`) returns settings with defaults `noAjax=0, defaultPublic=0, dayStart=8, dayStop=18, firstDayMonday=1, calendarView=MONTHVIEW` (`:1042-1049`), queried from `settings` where `settings_type = SETTINGS_CALENDAR` (`:1051-1063`). Instantiated at `CalendarUI.php:251`, `getAll()` at `:252`.

Also used: `DateUtility::getAdjustedDate / getWeekNumber / getStartingWeekday / getDaysInMonth / convert / validate` (`lib/DateUtility.php`, called throughout `CalendarUI.php:102-191,358-672`) and `SystemUtility::isSchedulerEnabled()` (`lib/SystemUtility.php`, called at `CalendarUI.php:263`).

Relevant constants: `UPCOMING_FOR_CALENDAR` = 0 (`constants.php:157`), `SETTINGS_CALENDAR` = 2 (`constants.php:69`), and calendar event type IDs `CALENDAR_EVENT_CALL/EMAIL/MEETING/INTERVIEW/PERSONAL/OTHER` = 100/200/300/400/500/600 (`lib/Calendar.php:34-39`).

## Hooks fired (keys + cites)

All via `if (!eval(Hooks::get('<KEY>'))) return;`:

- `CALENDAR_HANDLE_REQUEST` — top of `handleRequest()` (`modules/calendar/CalendarUI.php:57`).
- `CALENDAR_SHOW` — in `showCalendar()`, after data assembly, before reminder/template logic (`:261`).
- `CALENDAR_DATA` — in `dynamicData()`, before echoing the event string (`:342`).
- `CALENDAR_ADD_PRE` — in `onAddEvent()`, before `Calendar::addEvent()` (`:471`).
- `CALENDAR_ADD_POST` — in `onAddEvent()`, after add, before the redirect (`:503`).
- `CALENDAR_EDIT_PRE` — in `onEditEvent()`, before `Calendar::updateEvent()` (`:675`).
- `CALENDAR_EDIT_POST` — in `onEditEvent()`, after update (`:686`).
- `CALENDAR_DELETE_PRE` — in `onDeleteEvent()`, before `Calendar::deleteEvent()` (`:740`).
- `CALENDAR_DELETE_POST` — in `onDeleteEvent()`, after delete (`:744`).

## Scheduled task

`modules/calendar/tasks/tasks.php:39` registers `./modules/calendar/tasks/Reminders.php` as a recurring queue task. `class Reminders extends Task` (`Reminders.php:33`) runs every minute (`getSchedule()` returns `'* * * * *'`, `:47`); `run()` (`:50`) calls `getAllDueReminders()`, e-mails each due event using the `%FULLNAME%/%NOTES%/%EVENTNAME%/%DUETIME%` template substitution against `$GLOBALS['eventReminderEmail']` (`:70-99`), then calls `updateEventDisableReminder()` per event (`:102`).

## Source evidence

- `modules/calendar/CalendarUI.php` (read in full, 1-797).
- `modules/calendar/Calendar.tpl` (read in full, 1-622).
- `modules/calendar/Error.tpl` (read in full, 1-25).
- `modules/calendar/CalendarUI.js` (read in full, 1-600).
- `modules/calendar/validator.js` (read in full, 1-99).
- `modules/calendar/Calendar.js` (function index grep + read 502-561).
- `modules/calendar/tasks/tasks.php` (1-41), `modules/calendar/tasks/Reminders.php` (1-147).
- `lib/Calendar.php` (method index + read of signatures at lines 56-155, 209, 257-297, 323-401, 427-529, 685-689, 988-992, 1018-1072).
- `constants.php` (lines 69, 157 for `SETTINGS_CALENDAR`, `UPCOMING_FOR_CALENDAR`).

## Unverified / open questions

- The exact body/return semantics of `Calendar::makeEventString` (event-string wire format), `getUpcomingEventsHTML`, `getAllDueReminders`, `updateEvent` (full SQL), and `sendEmail` were not read past their signatures; only signatures and call sites are cited here.
- `getUserAccessLevel(...)` itself (in the `UserInterface` base class) was not opened; the privilege keys used are `calendar.show`, `calendar.addEvent`, `calendar.editEvent`, `calendar.deleteEvent`, and `calendar` (string passed in the template at `Calendar.tpl:587`). How these dotted keys resolve to per-module access levels is in the framework and not verified in this pass.
- `dynamicData` performs no `getUserAccessLevel` check inside the handler; whether the framework gates the `dynamicData` action separately before `handleRequest()` was not verified (only the constructor's tab-level `ACCESS_LEVEL_READ@calendar` is confirmed in this module).
- `Calendar.js` is large (~35KB); only its function index and the AJAX/URL helpers (`calendarDataPopulateServer`, `getCurrentCalendarUrl`, `handleResponse`) were read in detail. The grid-rendering internals (`updateCalendarViewMonth/Week/Day*`) were not read line-by-line.
- `$GLOBALS['eventReminderEmail']` (the reminder e-mail body template used in `Reminders.php:70`) is defined outside the calendar module and was not located.
