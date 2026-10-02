<?php TemplateUtility::printHeader('Calendar', array('modules/calendar/Calendar.css', 'js/highlightrows.js', 'modules/calendar/Calendar.js', 'modules/calendar/CalendarUI.js', 'modules/calendar/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <script>
        window.CATSUserDateFormat = '<?php echo($_SESSION['CATS']->isDateDMY() ? 'DD-MM-YY' : 'MM-DD-YY'); ?>';
        window.CATSTimeFormat24 = <?php echo($_SESSION['CATS']->isTimeFormat24() ? 'true' : 'false'); ?>;
    </script>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2 oc-calendar-page">
    <div id="contents">
        <header class="d-flex flex-wrap align-items-center gap-2 mb-2">
            <h1 class="h5 fw-semibold mb-0" id="calendarTitle">Calendar</h1>
            <?php if ($this->userIsSuperUser == 1): ?>
                <div class="form-check ms-auto">
                    <input class="form-check-input" type="checkbox" name="hideNonPublic" id="hideNonPublic" onclick="refreshView();" <?php if ($this->superUserActive): ?>checked<?php endif; ?>>
                    <label class="form-check-label" for="hideNonPublic">Show Entries from Other Users</label>
                </div>
            <?php else: ?>
                <input type="checkbox" style="display:none;" name="hideNonPublic" id="hideNonPublic">
            <?php endif; ?>
        </header>
        <div class="row g-2">
            <aside id="tableNav" class="col-12 col-xl-4" aria-label="Calendar events">
                <section id="upcomingEventsTD" class="card card-body p-2 text-break oc-calendar-upcoming">
                    <?php echo($this->summaryHTML); ?>
                </section>
                <section id="addEventTD" class="card" style="display:none;" aria-labelledby="addEventHeading">
                    <h2 id="addEventHeading" class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold">Add Event</h2>
                    <div class="card-body p-2">
                        <form name="addEventForm" id="addEventForm" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName()); ?>?m=calendar&amp;a=addEvent" method="post" onsubmit="return checkAddForm(document.addEventForm);" autocomplete="off" class="oc-calendar-event-form">
                            <input type="hidden" name="postback" id="postbackA" value="postback" />
                            <div>
                                <div class="mb-2">
                                    <label class="form-label mb-1" id="titleLabel" for="title">Title:</label>
                                    <input type="text" class="form-control form-control-sm" name="title" id="title" aria-required="true" /><span class="small text-body-secondary">Required</span>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label mb-1" id="eventTypeLabel" for="type">Type:</label>
                                    <select id="type" aria-required="true" name="type" class="form-select form-select-sm">
                                        <option value="">(Select a Type)</option>
                                        <?php foreach ($this->calendarEventTypes as $type): ?>
                                            <option value="<?php echo($type['typeID']); ?>"><?php $this->_($type['description']); ?></option>
                                        <?php endforeach; ?>
                                    </select><span class="small text-body-secondary">Required</span>
                                </div>
                                <div class="mb-2">
                                    <input class="form-check-input" type="checkbox" name="publicEntry" id="publicEntry" <?php if ($this->defaultPublic == 'true'): ?>checked<?php endif; ?> /><label class="form-check-label ms-1" for="publicEntry">Public Entry</label>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label mb-1" id="dateLabel" for="dateAdd_Month_ID">Date:</label>
                                    <script>DateInput('dateAdd', true, (typeof window.CATSUserDateFormat !== 'undefined' ? window.CATSUserDateFormat : 'MM-DD-YY'), '<?php echo($_SESSION['CATS']->isDateDMY() ? DateUtility::getAdjustedDate('d-m-y') : $this->currentDateMDY); ?>', -1);</script>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label mb-1" id="timeLabel" for="hour">Time:</label>
                                    <div class="d-flex flex-wrap align-items-center gap-1 mb-2"><input aria-label="Specific time" class="form-check-input" type="radio" name="allDay" id="allDay0" value="0" checked onchange="setAddAllDayEnabled();" />
                                        <select aria-label="Hour" id="hour" name="hour" class="form-select form-select-sm w-auto">
                                            <?php if ($_SESSION['CATS']->isTimeFormat24()): ?>
                                                <?php for ($i = 0; $i <= 23; ++$i): ?>
                                                    <option value="<?php echo($i); ?>"><?php echo(sprintf('%02d', $i)); ?></option>
                                                <?php endfor; ?>
                                            <?php else: ?>
                                                <?php for ($i = 1; $i <= 12; ++$i): ?>
                                                    <option value="<?php echo($i); ?>"><?php echo(sprintf('%02d', $i)); ?></option>
                                                <?php endfor; ?>
                                            <?php endif; ?>
                                        </select>
                                        <select aria-label="Minute" id="minute" name="minute" class="form-select form-select-sm w-auto">
                                            <?php for ($i = 0; $i <= 45; $i = $i + 15): ?>
                                                <option value="<?php echo(sprintf('%02d', $i)); ?>">
                                                <?php echo(sprintf('%02d', $i)); ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                        <?php if (!$_SESSION['CATS']->isTimeFormat24()): ?>
                                            <select aria-label="AM or PM" id="meridiem" name="meridiem" class="form-select form-select-sm w-auto">
                                                <option value="AM">AM</option>
                                                <option value="PM">PM</option>
                                            </select>
                                        <?php endif; ?>
                                    </div>
                                    <input class="form-check-input" type="radio" name="allDay" id="allDay1" value="1" onchange="setAddAllDayEnabled();" /><label class="form-check-label ms-1" for="allDay1">All Day / No Specific Time</label>
                                    <div class="mt-2" style="<?php if(!$this->allowEventReminders): ?>display:none;<?php endif; ?>">
                                        <input class="form-check-input" type="checkbox" name="reminderToggle" id="reminderToggle" onclick="considerCheckBox('reminderToggle', 'sendEmailTD');"><label class="form-check-label ms-1" for="reminderToggle">Send e-mail reminder</label>
                                    </div>
                                </div>
                                <div class="mb-2" id="sendEmailTD" style="display:none;">
                                    <div class="fw-semibold small mb-1">E-mail reminder</div>
                                    <div>
                                        <div class="mb-2">
                                            <label class="form-label mb-1" for="sendEmail">To:</label>
                                            <input type="text" id="sendEmail" name="sendEmail" class="form-control form-control-sm" value="<?php $this->_($this->userEmail); ?>" />
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label mb-1" for="reminderTime">Time:</label>
                                            <select class="form-select form-select-sm" id="reminderTime" name="reminderTime">
                                                <option value="15">15 min early</option>
                                                <option value="30">30 min early</option>
                                                <option value="45">45 min early</option>
                                                <option value="60">1 hour early</option>
                                                <option value="120">2 hours early</option>
                                                <option value="1440">1 day early</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label mb-1" id="durationLabel" for="duration">Length:</label>
                                    <select id="duration" name="duration" class="form-select form-select-sm">
                                        <option value="15">15 minutes</option>
                                        <option value="30">30 minutes</option>
                                        <option value="45">45 minutes</option>
                                        <option value="60" selected="selected">1 hour</option>
                                        <option value="90">1.5 hours</option>
                                        <option value="120">2 hours</option>
                                        <option value="180">3 hours</option>
                                        <option value="240">4 hours</option>
                                        <option value="300">More than 4 hours</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label mb-1" id="descriptionLabel" for="description">Description:</label>
                                    <textarea class="form-control form-control-sm" rows="4" id="description" name="description"></textarea>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <input type="submit" class="btn btn-sm btn-primary" name="submit" value="Add Event" />
                            </div>
                        </form>
                    </div>
                </section>
                <section id="editEventTD" class="card" style="display:none;" aria-labelledby="editEventHeading">
                    <h2 id="editEventHeading" class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold">Edit Event</h2>
                    <div class="card-body p-2">
                        <form name="editEventForm" id="editEventForm" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName()); ?>?m=calendar&amp;a=editEvent" method="post" onsubmit="return checkEditForm(document.editEventForm);" autocomplete="off" class="oc-calendar-event-form">
                            <input type="hidden" name="postback" id="postbackB" value="postback" />
                            <input type="hidden" name="eventID" id="eventIDEdit" />
                            <input type="hidden" name="dataItemType" id="dataItemTypeEdit" />
                            <input type="hidden" name="dataItemID" id="dataItemIDEdit" />
                            <input type="hidden" name="jobOrderID" id="jobOrderIDEdit" />
                            <div>
                                <div class="mb-2">
                                    <label class="form-label mb-1" id="titleLabelEdit" for="titleEdit">Title:</label>
                                    <input type="text" class="form-control form-control-sm" name="title" id="titleEdit" aria-required="true" /><span class="small text-body-secondary">Required</span>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label mb-1" id="eventTypeLabelEdit" for="typeEdit">Type:</label>
                                    <select id="typeEdit" aria-required="true" name="type" class="form-select form-select-sm">
                                        <option value="">(Select a Type)</option>
                                        <?php foreach ($this->calendarEventTypes as $type): ?>
                                            <option value="<?php echo($type['typeID']); ?>"><?php $this->_($type['description']); ?></option>
                                        <?php endforeach; ?>
                                    </select><span class="small text-body-secondary">Required</span>
                                </div>
                                <div class="mb-2">
                                    <input class="form-check-input" type="checkbox" name="publicEntry" id="publicEntryEdit" /><label class="form-check-label ms-1" for="publicEntryEdit">Public Entry</label>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label mb-1" id="dateLabelEdit" for="dateEdit_Month_ID">Date:</label>
                                    <script>DateInput('dateEdit', true, (typeof window.CATSUserDateFormat !== 'undefined' ? window.CATSUserDateFormat : 'MM-DD-YY'), '<?php echo($_SESSION['CATS']->isDateDMY() ? DateUtility::getAdjustedDate('d-m-y') : $this->currentDateMDY); ?>', -1);</script>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label mb-1" id="timeLabelEdit" for="hourEdit">Time:</label>
                                    <div class="d-flex flex-wrap align-items-center gap-1 mb-2"><input aria-label="Specific time" class="form-check-input" type="radio" name="allDay" id="allDayEdit0" value="0" checked onchange="setEditAllDayEnabled();" />
                                        <select aria-label="Hour" id="hourEdit" name="hour" class="form-select form-select-sm w-auto">
                                            <?php if ($_SESSION['CATS']->isTimeFormat24()): ?>
                                                <?php for ($i = 0; $i <= 23; ++$i): ?>
                                                    <option value="<?php echo($i); ?>"><?php echo(sprintf('%02d', $i)); ?></option>
                                                <?php endfor; ?>
                                            <?php else: ?>
                                                <?php for ($i = 1; $i <= 12; ++$i): ?>
                                                    <option value="<?php echo($i); ?>"><?php echo(sprintf('%02d', $i)); ?></option>
                                                <?php endfor; ?>
                                            <?php endif; ?>
                                        </select>
                                        <select aria-label="Minute" id="minuteEdit" name="minute" class="form-select form-select-sm w-auto">
                                            <?php for ($i = 0; $i <= 45; $i = $i + 15): ?>
                                                <option value="<?php echo(sprintf('%02d', $i)); ?>">
                                                <?php echo(sprintf('%02d', $i)); ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                        <?php if (!$_SESSION['CATS']->isTimeFormat24()): ?>
                                            <select aria-label="AM or PM" id="meridiemEdit" name="meridiem" class="form-select form-select-sm w-auto">
                                                <option value="AM">AM</option>
                                                <option value="PM">PM</option>
                                            </select>
                                        <?php endif; ?>
                                    </div>
                                    <input class="form-check-input" type="radio" name="allDay" id="allDayEdit1" value="1" onchange="setEditAllDayEnabled();" /><label class="form-check-label ms-1" for="allDayEdit1">All Day / No Specific Time</label>
                                    <div class="mt-2" style="<?php if(!$this->allowEventReminders): ?>display:none;<?php endif; ?>">
                                        <input class="form-check-input" type="checkbox" name="reminderToggle" id="reminderToggleEdit" onclick="considerCheckBox('reminderToggleEdit', 'sendEmailTDEdit');"><label class="form-check-label ms-1" for="reminderToggleEdit">Send e-mail reminder</label>
                                    </div>
                                </div>
                                <div class="mb-2" id="sendEmailTDEdit" style="display: none;">
                                    <div class="fw-semibold small mb-1">E-mail reminder</div>
                                    <div>
                                        <div class="mb-2">
                                            <label class="form-label mb-1" for="sendEmailEdit">To:</label>
                                            <input type="text" id="sendEmailEdit" name="sendEmail" class="form-control form-control-sm" value="<?php $this->_($this->userEmail); ?>" />
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label mb-1" for="reminderTimeEdit">Time:</label>
                                            <select class="form-select form-select-sm" id="reminderTimeEdit" name="reminderTime">
                                                <option value="15">15 min early</option>
                                                <option value="30">30 min early</option>
                                                <option value="45">45 min early</option>
                                                <option value="60">1 hour early</option>
                                                <option value="120">2 hours early</option>
                                                <option value="1440">1 day early</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label mb-1" id="durationLabelEdit" for="durationEdit">Length:</label>
                                    <select id="durationEdit" name="duration" class="form-select form-select-sm">
                                        <option value="15">15 minutes</option>
                                        <option value="30">30 minutes</option>
                                        <option value="45">45 minutes</option>
                                        <option value="60" selected="selected">1 hour</option>
                                        <option value="90">1.5 hours</option>
                                        <option value="120">2 hours</option>
                                        <option value="180">3 hours</option>
                                        <option value="240">4 hours</option>
                                        <option value="300">More than 4 hours</option>
                                    </select>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label mb-1" id="descriptionLabelEdit" for="descriptionEdit">Description:</label>
                                    <textarea class="form-control form-control-sm" rows="4" id="descriptionEdit" name="description"></textarea>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <input type="submit" class="btn btn-sm btn-primary" name="submit" value="Save" />
                                <?php if ($this->getUserAccessLevel('calendar.deleteEvent') >= ACCESS_LEVEL_DELETE): ?>
                                    <input type="button" class="btn btn-sm btn-outline-danger" name="delete" value="Delete" onclick="confirmDeleteEntry();" />
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </section>
                <section id="viewEventTD" class="card oc-calendar-event-details" style="display:none;" aria-labelledby="viewEventHeading">
                    <h2 id="viewEventHeading" class="card-header bg-secondary-subtle h6 py-1 px-2 fw-semibold">View Event</h2>
                    <div class="card-body p-2 text-break">
                        <h3 id="viewEventTitle" class="h6 fw-semibold"></h3>
                        <dl class="mb-2">
                            <dt>Entered By</dt><dd id="viewEventOwner"></dd>
                            <dt>Event Type</dt><dd id="viewEventType"></dd>
                            <dt>Related Record</dt><dd id="viewEventLink"></dd>
                            <dt>Date</dt><dd id="viewEventDate"></dd>
                            <dt>Time</dt><dd id="viewEventTime"></dd>
                            <dt>Duration</dt><dd id="viewEventDuration"></dd>
                            <dt>Reminder</dt><dd id="viewEventReminder"></dd>
                            <dt>Description</dt><dd id="viewEventDescription"></dd>
                        </dl>
                        <?php if ($this->getUserAccessLevel('calendar.editEvent') >= ACCESS_LEVEL_EDIT): ?>
                            <button type="button" class="btn btn-sm btn-primary" name="Edit" onclick="calendarEditEvent(currentViewedEntry);">Edit Event</button>
                        <?php endif; ?>
                    </div>
                </section>
            </aside>
            <div class="col-12 col-xl-8 oc-calendar-view">
                <section id="calendarMonthParent" class="card" style="display:none;" aria-label="Month calendar">
                    <div class="card-body p-2">
                        <nav class="d-flex flex-wrap align-items-center gap-2 mb-2 oc-calendar-toolbar" aria-label="Month view navigation">
                            <div class="btn-group" role="group" aria-label="Calendar view">
                                <button type="button" class="btn btn-sm btn-outline-secondary" aria-pressed="false" data-calendar-view="day" onclick="userCalendarViewDay()">Day</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" aria-pressed="false" data-calendar-view="week" onclick="userCalendarViewWeek()">Week</button>
                                <button type="button" class="btn btn-sm btn-primary" aria-pressed="true" data-calendar-view="month" onclick="userCalendarViewMonth()">Month</button>
                            </div>
                            <span id="linkMonthBack"></span>
                            <span id="monthNotice" class="small flex-grow-1 text-center" aria-live="polite"></span>
                            <span id="linkMonthForeward"></span>
                        </nav>
                        <div class="table-responsive" tabindex="0" role="region" aria-label="Month calendar grid">
                            <table id="calendarMonth" class="oc-calendar-grid" onmouseup="trackTableSelect(event);">
                                <tr >
                                    <?php if ($this->firstDayMonday != '1'): ?><th>Sunday</th><?php endif; ?>
                                    <th>Monday</th>
                                    <th>Tuesday</th>
                                    <th>Wednesday</th>
                                    <th>Thursday</th>
                                    <th>Friday</th>
                                    <th>Saturday</th>
                                    <?php if ($this->firstDayMonday == '1'): ?><th>Sunday</th><?php endif; ?>
                                </tr>

                                <?php $calendarPosition = 0; ?>
                                <?php for ($calendarRow = 1; $calendarRow <= 6; ++$calendarRow): ?>
                                    <tr id="calendarRow<?php echo($calendarRow); ?>">
                                        <?php $weekPosition = 1; ?>
                                        <?php for ($weekday = 1; $weekday <= 7; ++$weekday): ?>
                                            <td class="empty" id="calendarMonthCell<?php echo($calendarPosition++); ?>">&nbsp;</td>
                                        <?php endfor; ?>
                                    </tr>
                                <?php endfor; ?>
                            </table>
                        </div>
                    </div>
                </section>
                <section id="calendarWeekParent" class="card" style="display:none;" aria-label="Week calendar">
                    <div class="card-body p-2">
                        <nav class="d-flex flex-wrap align-items-center gap-2 mb-2 oc-calendar-toolbar" aria-label="Week view navigation">
                            <div class="btn-group" role="group" aria-label="Calendar view">
                                <button type="button" class="btn btn-sm btn-outline-secondary" aria-pressed="false" data-calendar-view="day" onclick="userCalendarViewDay()">Day</button>
                                <button type="button" class="btn btn-sm btn-primary" aria-pressed="true" data-calendar-view="week" onclick="userCalendarViewWeek()">Week</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" aria-pressed="false" data-calendar-view="month" onclick="userCalendarViewMonth()">Month</button>
                            </div>
                            <span id="linkWeekBack"></span>
                            <span id="weekNotice" class="small flex-grow-1 text-center" aria-live="polite"></span>
                            <span id="linkWeekForeward"></span>
                        </nav>
                        <div class="table-responsive" tabindex="0" role="region" aria-label="Week calendar grid">
                            <table id="calendarWeek" class="oc-calendar-grid" onmouseup="trackTableSelect(event, '#e9e9e9');">
                                <?php if ($this->firstDayMonday != '1'): ?>
                                    <tr>
                                        <th>Sunday <br /><span id="weekDay0"></span></th>
                                        <td class="empty" id="calendarWeekCell0"></td>
                                    </tr>

                                    <tr>
                                        <th>Monday <br /><span id="weekDay1"></span></th>
                                        <td class="empty" id="calendarWeekCell1"></td>
                                    </tr>
                                    <tr>
                                        <th>Tuesday <br /><span id="weekDay2"></span></th>
                                        <td class="empty" id="calendarWeekCell2"></td>
                                    </tr>
                                    <tr>
                                        <th>Wednesday <br /><span id="weekDay3"></span></th>
                                        <td class="empty" id="calendarWeekCell3"></td>
                                    </tr>
                                    <tr>
                                        <th>Thursday <br /><span id="weekDay4"></span></th>
                                        <td class="empty" id="calendarWeekCell4"></td>
                                    </tr>
                                    <tr>
                                        <th>Friday <br /><span id="weekDay5"></span></th>
                                        <td class="empty" id="calendarWeekCell5"></td>
                                    </tr>
                                    <tr>
                                        <th>Saturday <br /><span id="weekDay6"></span></th>
                                        <td class="empty" id="calendarWeekCell6"></td>
                                    </tr>
                                <?php else: ?>
                                    <tr>
                                        <th>Monday <br /><span id="weekDay0"></span></th>
                                        <td class="empty" id="calendarWeekCell0"></td>
                                    </tr>

                                    <tr>
                                        <th>Tuesday <br /><span id="weekDay1"></span></th>
                                        <td class="empty" id="calendarWeekCell1"></td>
                                    </tr>
                                    <tr>
                                        <th>Wednesday <br /><span id="weekDay2"></span></th>
                                        <td class="empty" id="calendarWeekCell2"></td>
                                    </tr>
                                    <tr>
                                        <th>Thursday <br /><span id="weekDay3"></span></th>
                                        <td class="empty" id="calendarWeekCell3"></td>
                                    </tr>
                                    <tr>
                                        <th>Friday <br /><span id="weekDay4"></span></th>
                                        <td class="empty" id="calendarWeekCell4"></td>
                                    </tr>
                                    <tr>
                                        <th>Saturday <br /><span id="weekDay5"></span></th>
                                        <td class="empty" id="calendarWeekCell5"></td>
                                    </tr>
                                    <tr>
                                        <th>Sunday <br /><span id="weekDay6"></span></th>
                                        <td class="empty" id="calendarWeekCell6"></td>
                                    </tr>
                                <?php endif; ?>
                            </table>
                        </div>
                    </div>
                </section>
                <section id="calendarDayParent" class="card" style="display:none;" aria-label="Day calendar">
                    <div class="card-body p-2">
                        <nav class="d-flex flex-wrap align-items-center gap-2 mb-2 oc-calendar-toolbar" aria-label="Day view navigation">
                            <div class="btn-group" role="group" aria-label="Calendar view">
                                <button type="button" class="btn btn-sm btn-primary" aria-pressed="true" data-calendar-view="day" onclick="userCalendarViewDay()">Day</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" aria-pressed="false" data-calendar-view="week" onclick="userCalendarViewWeek()">Week</button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" aria-pressed="false" data-calendar-view="month" onclick="userCalendarViewMonth()">Month</button>
                            </div>
                            <span id="linkDayBack"></span>
                            <span id="dayNotice" class="small flex-grow-1 text-center" aria-live="polite"></span>
                            <span id="linkDayForeward"></span>
                        </nav>
                        <div class="table-responsive" tabindex="0" role="region" aria-label="Day calendar grid">
                            <table id="calendarDay" class="oc-calendar-grid" onmouseup="trackTableSelect(event, '#e9e9e9');">
                                <tr>
                                    <th>Morning</th>
                                    <td class="empty" id="calendarDayCell0"></td>
                                </tr>
                                    <?php for ($i = $this->dayHourStart; $i <= $this->dayHourEnd; $i++): ?>
                                    <tr>
                                        <th><?php if (!$this->militaryTime && $i>12):?><?php echo($i - 12); ?><?php else: ?><?php echo($i); ?><?php endif; ?>:00</th>
                                        <td class="empty" id="calendarDayCell<?php echo($i - $this->dayHourStart + 1); ?>"></td>
                                    </tr>
                                    <?php endfor; ?>
                                <tr>
                                    <th>Evening</th>
                                    <td class="empty" id="calendarDayCell<?php echo($this->dayHourEnd - $this->dayHourStart + 2); ?>"></td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</main>
    <script>
        initializeCalendarUI();

        /* Settings */
        indexName = '<?php echo(CATSUtility::getIndexName()); ?>';
        todayDay = <?php echo($this->currentDay); ?>;
        todayMonth = <?php echo($this->currentMonth); ?>;
        todayYear = <?php echo($this->currentYear); ?>;
        todayHour = <?php echo($this->currentHour); ?>;
        dayHourStart = <?php echo($this->dayHourStart); ?>;
        dayHourEnd = <?php echo($this->dayHourEnd); ?>;
        dayTotalCells = <?php echo($this->dayHourEnd - $this->dayHourStart + 3); ?>;
        userEmail = '<?php echo($this->userEmail); ?>';
        allowAjax = <?php echo($this->allowAjax ? 'true' : 'false'); ?>;
        defaultPublic = <?php echo($this->defaultPublic); ?>;
        userID = <?php echo($this->userID); ?>;
        userIsSuperUser = <?php echo($this->userIsSuperUser); ?>;
        firstDayMonday =  <?php if ($this->firstDayMonday == 1) echo('1'); else echo('0'); ?>;
        accessLevel =  <?php echo($this->getUserAccessLevel('calendar')); ?>;

        /* Constants */
        <?php
            $typesArray = array();
            foreach ($this->calendarEventTypes as $type):
                $typesArray[] = "new Array(" . $type['typeID'] . ", '" . $type['description'] . "', '" . $type['iconImage'] . "')";
            endforeach;
        ?>
        entryTypesArray = new Array(<?php echo(implode(",\n", $typesArray)); ?>);

        var ACCESS_LEVEL_DISABLED  = <?php echo(ACCESS_LEVEL_DISABLED); ?>;
        var ACCESS_LEVEL_READ      = <?php echo(ACCESS_LEVEL_READ); ?>;
        var ACCESS_LEVEL_EDIT      = <?php echo(ACCESS_LEVEL_EDIT); ?>;
        var ACCESS_LEVEL_DELETE    = <?php echo(ACCESS_LEVEL_DELETE); ?>;
        var ACCESS_LEVEL_DEMO      = <?php echo(ACCESS_LEVEL_DEMO); ?>;
        var ACCESS_LEVEL_SA        = <?php echo(ACCESS_LEVEL_SA); ?>;
        var ACCESS_LEVEL_ROOT      = <?php echo(ACCESS_LEVEL_ROOT); ?>;

        /* Data */
        calendarDataPopulateString('<?php echo($this->eventsString) ?>');

        /* Action */
        <?php if ($this->view == 'WEEKVIEW'): ?>
            setCalendarViewWeek(<?php echo($this->year) ?>, <?php echo($this->month) ?>, <?php echo($this->week) ?>);
        <?php elseif ($this->view == 'DAYVIEW'): ?>
            setCalendarViewDay(<?php echo($this->year) ?>, <?php echo($this->month) ?>, <?php echo($this->day) ?>);
        <?php else: ?>
            setCalendarViewMonth(<?php echo($this->year) ?>, <?php echo($this->month) ?>);
        <?php endif; ?>

        <?php if ($this->showEvent != null): ?>
            handleClickEntryByID(<?php echo($this->showEvent); ?>);
        <?php endif; ?>
    </script>
<?php TemplateUtility::printFooter(); ?>
