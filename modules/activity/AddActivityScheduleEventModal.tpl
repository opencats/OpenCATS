<?php /* $Id: AddActivityScheduleEventModal.tpl $ */ ?>
<?php if ($this->onlyScheduleEvent): ?>
    <?php TemplateUtility::printModalHeader($this->activityModalTitle, array($this->activityValidatorPath, 'js/activity.js'), $this->activityModalTitle . ': Schedule Event'); ?>
<?php else: ?>
    <?php TemplateUtility::printModalHeader($this->activityModalTitle, array($this->activityValidatorPath, 'js/activity.js'), $this->activityModalTitle . ': Log Activity'); ?>
<?php endif; ?>

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

        <table class="editTable" width="560">
<?php if (!$this->onlyScheduleEvent): ?>
            <tr id="activityDateTR">
                <td class="tdVertical">
                    <label id="activityDateLabel" for="activityDate_Month_ID">Date:</label>
                </td>
                <td class="tdData">
                    <script type="text/javascript">DateInput('activityDate', true, (typeof window.CATSUserDateFormat !== 'undefined' ? window.CATSUserDateFormat : 'MM-DD-YY'), '', -1);</script>
                </td>
            </tr>

            <tr id="activityTimeTR">
                <td class="tdVertical">
                    <label id="activityTimeLabel" for="activityHour">Time:</label>
                </td>
                <td class="tdData">
                    <select id="activityHour" name="activityHour" class="inputbox" style="width: 40px;">
                        <?php for ($i = 1; $i <= 12; ++$i): ?>
                            <option value="<?php echo($i); ?>"><?php echo(sprintf('%02d', $i)); ?></option>
                        <?php endfor; ?>
                    </select>&nbsp;
                    <select id="activityMinute" name="activityMinute" class="inputbox" style="width: 40px;">
                        <?php for ($i = 0; $i <= 59; ++$i): ?>
                            <option value="<?php echo(sprintf('%02d', $i)); ?>">
                                <?php echo(sprintf('%02d', $i)); ?>
                            </option>
                        <?php endfor; ?>
                    </select>&nbsp;
                    <select id="activityMeridiem" name="activityMeridiem" class="inputbox" style="width: 45px;">
                        <option value="AM">AM</option>
                        <option value="PM">PM</option>
                    </select>
                </td>
            </tr>
<?php endif; ?>

            <tr id="visibleTR" <?php if ($this->onlyScheduleEvent): ?>style="display:none;"<?php endif; ?>>
                <td class="tdVertical">
                    <label id="regardingIDLabel" for="regardingID">Regarding:</label>
                </td>
                <td class="tdData">
<?php if ($this->activityRegardingIDHidden): ?>
                    <span><?php $this->_($this->activityRegardingTitle); ?></span>
<?php else: ?>
                    <select id="regardingID" name="regardingID" class="inputbox" style="width: 150px;">
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
                </td>
            </tr>

            <tr id="addActivityTR" <?php if ($this->onlyScheduleEvent): ?>style="display:none;"<?php endif; ?>>
                <td class="tdVertical">
                    <label id="addActivityLabel" for="addActivity">Activity:</label>
                </td>
                <td class="tdData">
                    <input type="checkbox" name="addActivity" id="addActivity" style="margin-left: 0px;"<?php if (!$this->onlyScheduleEvent): ?> checked="checked"<?php endif; ?> onclick="AS_onAddActivityChange('addActivity', 'activityTypeID', 'activityNote', 'addActivitySpanA', 'addActivitySpanB');" />Log an Activity<br />
                    <div id="activityNoteDiv" style="margin-top: 4px;">
                        <span id="addActivitySpanA">Activity Type</span><br />
                        <select id="activityTypeID" name="activityTypeID" class="inputbox" style="width: 150px; margin-bottom: 4px;">
                            <option selected="selected" value="">-- Select --</option>
                            <option value="<?php echo(ACTIVITY_CALL); ?>">Not reached</option>
                            <option value="<?php echo(ACTIVITY_CALL_TALKED); ?>">Call (Talked)</option>
                            <option value="<?php echo(ACTIVITY_CALL_LVM); ?>">Call (LVM)</option>
                            <option value="<?php echo(ACTIVITY_CALL_MISSED); ?>">Call (Missed)</option>
                            <option value="<?php echo(ACTIVITY_EMAIL); ?>">Email</option>
                            <option value="<?php echo(ACTIVITY_MEETING); ?>">Meeting</option>
                            <option value="<?php echo(ACTIVITY_OTHER); ?>">Other</option>
                        </select><br />
                        <span id="addActivitySpanB">Activity Notes</span><br />
                        <textarea name="activityNote" id="activityNote" cols="50" style="margin-bottom: 4px;" class="inputbox"></textarea>
                    </div>
                </td>
            </tr>

            <tr id="scheduleEventTR">
                <td class="tdVertical">
                    <label id="scheduleEventLabel" for="scheduleEvent">Schedule Event:</label>
                </td>
                <td class="tdData">
                    <input type="checkbox" name="scheduleEvent" id="scheduleEvent" style="margin-left: 0px; <?php if ($this->onlyScheduleEvent): ?>display:none;<?php endif; ?>" onclick="AS_onScheduleEventChange('scheduleEvent', 'scheduleEventDiv');"<?php if ($this->onlyScheduleEvent): ?> checked="checked"<?php endif; ?> /><?php if (!$this->onlyScheduleEvent): ?>Schedule Event<?php endif; ?>
                    <div id="scheduleEventDiv" <?php if (!$this->onlyScheduleEvent): ?>style="display:none;"<?php endif; ?>>
                        <table style="border: none; margin: 0px; padding: 0px;">
                            <tr>
                                <td valign="top">
                                    <div style="margin-bottom: 4px;">
                                        <select id="eventTypeID" name="eventTypeID" class="inputbox" style="width: 150px;">
                                            <?php foreach ($this->calendarEventTypes as $eventType): ?>
                                                <option <?php if ($eventType['typeID'] == CALENDAR_EVENT_INTERVIEW): ?>selected="selected" <?php endif; ?>value="<?php echo($eventType['typeID']); ?>"><?php $this->_($eventType['description']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div style="margin-bottom: 4px;">
                                        <script type="text/javascript">DateInput('dateAdd', true, (typeof window.CATSUserDateFormat !== 'undefined' ? window.CATSUserDateFormat : 'MM-DD-YY'), '', -1);</script>
                                    </div>

                                    <div style="margin-bottom: 4px;">
                                        <input type="radio" name="allDay" id="allDay0" value="0" style="margin-left: 0px" checked="checked" onchange="AS_onEventAllDayChange('allDay1');" />
                                        <select id="hour" name="hour" class="inputbox" style="width: 40px;">
                                            <?php for ($i = 1; $i <= 12; ++$i): ?>
                                                <option value="<?php echo($i); ?>"><?php echo(sprintf('%02d', $i)); ?></option>
                                            <?php endfor; ?>
                                        </select>&nbsp;
                                        <select id="minute" name="minute" class="inputbox" style="width: 40px;">
                                            <?php for ($i = 0; $i <= 45; $i = $i + 15): ?>
                                                <option value="<?php echo(sprintf('%02d', $i)); ?>">
                                                    <?php echo(sprintf('%02d', $i)); ?>
                                                </option>
                                            <?php endfor; ?>
                                        </select>&nbsp;
                                        <select id="meridiem" name="meridiem" class="inputbox" style="width: 45px;">
                                            <option value="AM">AM</option>
                                            <option value="PM">PM</option>
                                        </select>
                                    </div>

                                    <div style="margin-bottom: 4px;">
                                        <input type="radio" name="allDay" id="allDay1" value="1" style="margin-left: 0px" onchange="AS_onEventAllDayChange('allDay1');" />All Day / No Specific Time<br />
                                    </div>

                                    <div style="margin-bottom: 4px;">
                                        <input type="checkBox" name="publicEntry" id="publicEntry" style="margin-left: 0px" />Public Entry
                                    </div>
                                </td>

                                <td valign="top">
                                    <div style="margin-bottom: 4px;">
                                        <label id="titleLabel" for="title">Title&nbsp;*</label><br />
                                        <input type="text" class="inputbox" name="title" id="title" style="width: <?php echo($this->activityTitleWidth); ?>px" />
                                    </div>

<?php if ($this->activityShowEventDuration): ?>
                                    <div style="margin-bottom: 4px;">
                                        <label id="durationLabel" for="duration">Length:</label>
                                        <br />
                                        <select id="duration" name="duration" class="inputbox" style="width: <?php echo($this->activityTitleWidth); ?>px;">
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

                                    <div style="margin-bottom: 4px;">
                                        <label id="descriptionLabel" for="description">Description</label><br />
                                        <textarea name="description" id="description" cols="20" class="inputbox" style="width: <?php echo($this->activityDescriptionWidth); ?>px;<?php if ($this->activityDescriptionHeight > 0): ?> height:<?php echo($this->activityDescriptionHeight); ?>px;<?php endif; ?>"></textarea>
                                    </div>

                                    <div <?php if (!$this->allowEventReminders): ?>style="display:none;"<?php endif; ?>>
                                        <input type="checkbox" name="reminderToggle" onclick="if (this.checked) document.getElementById('reminderArea').style.display = ''; else document.getElementById('reminderArea').style.display = '';">&nbsp;<label>Set Reminder</label><br />
                                    </div>

                                    <div style="display:none;" id="reminderArea">
                                        <div>
                                            <label><?php echo($this->activityReminderEmailLabel); ?></label><br />
                                            <input type="text" id="sendEmail" name="sendEmail" class="inputbox" style="width: 150px" value="<?php $this->_($this->userEmail); ?>" />
                                        </div>
                                        <div>
                                            <label>Time:</label><br />
                                            <select id="reminderTime" name="reminderTime" style="width: 150px">
                                                <option value="15">15 min early</option>
                                                <option value="30">30 min early</option>
                                                <option value="45">45 min early</option>
                                                <option value="60">1 hour early</option>
                                                <option value="120">2 hours early</option>
                                                <option value="1440">1 day early</option>
                                            </select>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>

        </table>
        <input type="submit" class="button" name="submit" id="submit" value="Save" />&nbsp;
        <input type="button" class="button" name="close" value="Cancel" onclick="parentGoToURL(<?php echo Template::escapeJsAttr($this->activityCancelURL); ?>);" />
    </form>

    <script type="text/javascript">
        if (!<?php echo($this->onlyScheduleEvent ? 'true' : 'false'); ?>)
        {
            var now = new Date();
            var currentHour = now.getHours() % 12;
            if (currentHour == 0)
            {
                currentHour = 12;
            }
            document.getElementById('activityHour').value = currentHour.toString();
            document.getElementById('activityMinute').value = (now.getMinutes() < 10 ? '0' : '') + now.getMinutes();
            document.getElementById('activityMeridiem').value = (now.getHours() >= 12 ? 'PM' : 'AM');
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
                    <p>An activity entry of type <span class="bold"><?php $this->_($this->activityType); ?></span> has been added with the following note: &quot;<?php $this->_($this->activityDescription); ?>&quot;.</p>
                <?php else: ?>
                    <p>An activity entry of type <span class="bold"><?php $this->_($this->activityType); ?></span> has been added with no notes.</p>
                <?php endif; ?>
            <?php else: ?>
                <p>No activity entries have been added.</p>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php echo($this->eventHTML); ?>

    <form>
        <input type="button" name="close" class="button" value="Close" onclick="parentGoToURL(<?php echo Template::escapeJsAttr($this->activityCloseURL); ?>);" />
    </form>
<?php endif; ?>

    </body>
</html>
