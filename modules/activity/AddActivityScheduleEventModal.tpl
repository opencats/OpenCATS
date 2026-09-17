<?php if ($this->onlyScheduleEvent): ?>
    <?php TemplateUtility::printModalHeader($this->activityModalTitle, array($this->activityValidatorPath, 'js/activity.js'), $this->activityModalTitle . ': Schedule Event'); ?>
<?php else: ?>
    <?php TemplateUtility::printModalHeader($this->activityModalTitle, array($this->activityValidatorPath, 'js/activity.js'), $this->activityModalTitle . ': Log Activity'); ?>
<?php endif; ?>

<main class="container-fluid p-2">
<?php if (!$this->isFinishedMode): ?>

<script type="text/javascript">
    window.CATSUserDateFormat = '<?php echo($_SESSION['CATS']->isDateDMY() ? 'DD-MM-YY' : 'MM-DD-YY'); ?>';
</script>

    <form name="logActivityForm" id="logActivityForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=<?php echo($this->activityParentModule); ?>&amp;a=<?php echo($this->activitySubmitAction); ?><?php if ($this->onlyScheduleEvent): ?>&amp;onlyScheduleEvent=true<?php endif; ?>" method="post" onsubmit="return checkActivityForm(document.logActivityForm);" autocomplete="off">
        <input type="hidden" name="postback" id="postback" value="postback" />
        <input type="hidden" id="<?php echo($this->activityParentIDName); ?>" name="<?php echo($this->activityParentIDName); ?>" value="<?php echo($this->activityParentID); ?>" />
<?php if ($this->activityRegardingIDHidden): ?>
        <input type="hidden" id="regardingID" name="regardingID" value="<?php echo($this->regardingID); ?>" />
<?php endif; ?>

        <div class="card card-body p-2">
<?php if (!$this->onlyScheduleEvent): ?>
            <div class="row g-2 mb-3" id="activityDateTR">
                <div class="col-sm-3">
                    <label class="form-label mb-1" id="activityDateLabel" for="activityDate_Month_ID">Date:</label>
                </div>
                <div class="col-sm-9">
                    <script type="text/javascript">DateInput('activityDate', true, (typeof window.CATSUserDateFormat !== 'undefined' ? window.CATSUserDateFormat : 'MM-DD-YY'), '', -1);</script>
                </div>
            </div>

            <div class="row g-2 mb-3" id="activityTimeTR">
                <div class="col-sm-3">
                    <label class="form-label mb-1" id="activityTimeLabel" for="activityHour">Time:</label>
                </div>
                <div class="col-sm-9">
                    <select aria-label="Activity hour" id="activityHour" name="activityHour" class="form-select form-select-sm w-auto d-inline-block">
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
                    <select aria-label="Activity minute" id="activityMinute" name="activityMinute" class="form-select form-select-sm w-auto d-inline-block">
                        <?php for ($i = 0; $i <= 59; ++$i): ?>
                            <option value="<?php echo(sprintf('%02d', $i)); ?>">
                                <?php echo(sprintf('%02d', $i)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                    <?php if (!$_SESSION['CATS']->isTimeFormat24()): ?>
                    <select aria-label="Activity AM or PM" id="activityMeridiem" name="activityMeridiem" class="form-select form-select-sm w-auto d-inline-block">
                        <option value="AM">AM</option>
                        <option value="PM">PM</option>
                    </select>
                    <?php endif; ?>
                </div>
            </div>
<?php endif; ?>

            <div class="row g-2 mb-3" id="visibleTR" <?php if ($this->onlyScheduleEvent): ?>style="display:none;"<?php endif; ?>>
                <div class="col-sm-3">
                    <label class="form-label mb-1" id="regardingIDLabel" for="regardingID">Regarding:</label>
                </div>
                <div class="col-sm-9">
<?php if ($this->activityRegardingIDHidden): ?>
                    <span><?php $this->_($this->activityRegardingTitle); ?></span>
<?php else: ?>
                    <select id="regardingID" name="regardingID" class="form-select form-select-sm">
                        <option value="-1">General</option>
                        <?php foreach ($this->jobOrdersRS as $jobOrderData): ?>
                            <?php if ($this->regardingID == $jobOrderData['jobOrderID']): ?>
                                <option selected="selected" value="<?php $this->_($jobOrderData['jobOrderID']) ?>"><?php $this->_($jobOrderData['activityLabel']) ?></option>
                            <?php else: ?>
                                <option value="<?php $this->_($jobOrderData['jobOrderID']) ?>"><?php $this->_($jobOrderData['activityLabel']) ?></option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
<?php endif; ?>
                </div>
            </div>

            <div class="row g-2 mb-3" id="addActivityTR" <?php if ($this->onlyScheduleEvent): ?>style="display:none;"<?php endif; ?>>
                <div class="col-sm-3">
                    <label class="form-label mb-1" id="addActivityLabel" for="addActivity">Activity:</label>
                </div>
                <div class="col-sm-9">
                    <input type="checkbox" class="form-check-input" name="addActivity" id="addActivity" <?php if (!$this->onlyScheduleEvent): ?> checked="checked"<?php endif; ?> onclick="AS_onAddActivityChange('addActivity', 'activityTypeID', 'activityNote', 'addActivitySpanA', 'addActivitySpanB');" /><label class="form-check-label ms-1" for="addActivity">Log an Activity</label>
                    <div id="activityNoteDiv" class="mt-2">
                        <label class="form-label mb-1" id="addActivitySpanA" for="activityTypeID">Activity Type</label>
                        <select id="activityTypeID" name="activityTypeID" class="form-select form-select-sm">
                            <option selected="selected" value="">-- Select --</option>
                            <option value="<?php echo(ACTIVITY_CALL); ?>">Not reached</option>
                            <option value="<?php echo(ACTIVITY_CALL_TALKED); ?>">Call (Talked)</option>
                            <option value="<?php echo(ACTIVITY_CALL_LVM); ?>">Call (LVM)</option>
                            <option value="<?php echo(ACTIVITY_CALL_MISSED); ?>">Call (Missed)</option>
                            <option value="<?php echo(ACTIVITY_EMAIL); ?>">Email</option>
                            <option value="<?php echo(ACTIVITY_MEETING); ?>">Meeting</option>
                            <option value="<?php echo(ACTIVITY_OTHER); ?>">Other</option>
                        </select>
                        <label class="form-label mt-2 mb-1" id="addActivitySpanB" for="activityNote">Activity Notes</label>
                        <textarea name="activityNote" id="activityNote" rows="3" class="form-control form-control-sm"></textarea>
                    </div>
                </div>
            </div>

            <div class="row g-2 mb-3" id="scheduleEventTR">
                <div class="col-sm-3">
                    <label class="form-label mb-1" id="scheduleEventLabel" for="scheduleEvent">Schedule Event:</label>
                </div>
                <div class="col-sm-9">
                    <input type="checkbox" class="form-check-input" name="scheduleEvent" id="scheduleEvent" <?php if ($this->onlyScheduleEvent): ?>style="display:none;"<?php endif; ?> onclick="AS_onScheduleEventChange('scheduleEvent', 'scheduleEventDiv');"<?php if ($this->onlyScheduleEvent): ?> checked="checked"<?php endif; ?> /><?php if (!$this->onlyScheduleEvent): ?><label class="form-check-label ms-1" for="scheduleEvent">Schedule Event</label><?php endif; ?>
                    <div id="scheduleEventDiv" <?php if (!$this->onlyScheduleEvent): ?>style="display:none;"<?php endif; ?>>
                        <div class="row g-3 mt-1">

                                <div class="col-sm-6">
                                    <div class="mb-2">
                                        <label class="form-label mb-1" for="eventTypeID">Event Type</label>
                                        <select id="eventTypeID" name="eventTypeID" class="form-select form-select-sm">
                                            <?php foreach ($this->calendarEventTypes as $eventType): ?>
                                                <option <?php if ($eventType['typeID'] == CALENDAR_EVENT_INTERVIEW): ?>selected="selected" <?php endif; ?>value="<?php echo($eventType['typeID']); ?>"><?php $this->_($eventType['description']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label mb-1" for="dateAdd_Month_ID">Event Date</label>
                                        <script type="text/javascript">DateInput('dateAdd', true, (typeof window.CATSUserDateFormat !== 'undefined' ? window.CATSUserDateFormat : 'MM-DD-YY'), '', -1);</script>
                                    </div>

                                    <div class="mb-2">
                                        <input type="radio" class="form-check-input" name="allDay" aria-label="Specific time" id="allDay0" value="0"  checked="checked" onchange="AS_onEventAllDayChange('allDay1');" />
                                        <select aria-label="Event hour" id="hour" name="hour" class="form-select form-select-sm w-auto d-inline-block">
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
                                        <select aria-label="Event minute" id="minute" name="minute" class="form-select form-select-sm w-auto d-inline-block">
                                            <?php for ($i = 0; $i <= 45; $i = $i + 15): ?>
                                                <option value="<?php echo(sprintf('%02d', $i)); ?>">
                                                    <?php echo(sprintf('%02d', $i)); ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>
                                        <?php if (!$_SESSION['CATS']->isTimeFormat24()): ?>
                                        <select aria-label="Event AM or PM" id="meridiem" name="meridiem" class="form-select form-select-sm w-auto d-inline-block">
                                            <option value="AM">AM</option>
                                            <option value="PM">PM</option>
                                        </select>
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input type="radio" class="form-check-input" name="allDay" id="allDay1" value="1"  onchange="AS_onEventAllDayChange('allDay1');" /><label class="form-check-label ms-1" for="allDay1">All Day / No Specific Time</label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" name="publicEntry" id="publicEntry"  /><label class="form-check-label ms-1" for="publicEntry">Public Entry</label>
                                    </div>
                                </div>

                                <div class="col-sm-6">
                                    <div class="mb-2">
                                        <label class="form-label mb-1" id="titleLabel" for="title">Title&nbsp;*</label>
                                        <input type="text" class="form-control form-control-sm" name="title" id="title" />
                                    </div>

<?php if ($this->activityShowEventDuration): ?>
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
<?php endif; ?>

                                    <div class="mb-2">
                                        <label class="form-label mb-1" id="descriptionLabel" for="description">Description</label>
                                        <textarea name="description" id="description" rows="3" class="form-control form-control-sm"></textarea>
                                    </div>

                                    <div <?php if (!$this->allowEventReminders): ?>style="display:none;"<?php endif; ?>>
                                        <input type="checkbox" class="form-check-input" name="reminderToggle" id="reminderToggle" onclick="if (this.checked) document.getElementById('reminderArea').style.display = ''; else document.getElementById('reminderArea').style.display = '';"><label class="form-check-label ms-1" for="reminderToggle">Set Reminder</label>
                                    </div>

                                    <div style="display:none;" id="reminderArea">
                                        <div class="mb-2">
                                            <label class="form-label mb-1" for="sendEmail"><?php echo($this->activityReminderEmailLabel); ?></label>
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
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="d-flex justify-content-end gap-2 mt-3">
        <input type="submit" class="btn btn-sm btn-primary" name="submit" id="submit" value="Save" />
        <input type="button" class="btn btn-sm btn-secondary" name="close" value="Cancel" onclick="parentGoToURL(<?php echo Template::escapeJsAttr($this->activityCancelURL); ?>);" />
        </div>
    </form>

    <script type="text/javascript">
        if (!<?php echo($this->onlyScheduleEvent ? 'true' : 'false'); ?>)
        {
            var now = new Date();
            <?php if ($_SESSION['CATS']->isTimeFormat24()): ?>
            document.getElementById('activityHour').value = now.getHours().toString();
            <?php else: ?>
            var currentHour = now.getHours() % 12;
            if (currentHour == 0) { currentHour = 12; }
            document.getElementById('activityHour').value = currentHour.toString();
            document.getElementById('activityMeridiem').value = (now.getHours() >= 12 ? 'PM' : 'AM');
            <?php endif; ?>
            document.getElementById('activityMinute').value = (now.getMinutes() < 10 ? '0' : '') + now.getMinutes();
            document.logActivityForm.activityNote.focus();
        }
<?php if ($this->onlyScheduleEvent && $this->activityFocusEventTitle): ?>
        else
        {
            document.getElementById('title').focus();
        }
<?php endif; ?>
    </script>

<?php else: ?>
    <?php if (!$this->changesMade): ?>
        <p>No changes have been made.</p>
    <?php else: ?>
        <?php if (!$this->onlyScheduleEvent): ?>
            <?php if ($this->activityAdded): ?>
                <?php if (!empty($this->activityDescription)): ?>
                    <p>An activity entry of type <span class="fw-semibold"><?php $this->_($this->activityType); ?></span> has been added with the following note: &quot;<?php $this->_($this->activityDescription); ?>&quot;.</p>
                <?php else: ?>
                    <p>An activity entry of type <span class="fw-semibold"><?php $this->_($this->activityType); ?></span> has been added with no notes.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>No activity entries have been added.</p>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php echo($this->eventHTML); ?>

    <form>
        <input type="button" name="close" class="btn btn-sm btn-primary" value="Close" onclick="parentGoToURL(<?php echo Template::escapeJsAttr($this->activityCloseURL); ?>);" />
    </form>
<?php endif; ?>

</main>
    </body>
</html>
