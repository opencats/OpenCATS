<?php /* $Id: AddActivityScheduleEventModal.tpl 3093 2007-09-24 21:09:45Z brian $ */ ?>

<?php if(!$this->onlyScheduleEvent): ?>
<?php TemplateUtility::printModalHeader('Contacts', array('modules/contacts/activityvalidator.js', 'js/activity.js'), 'Contacts: Log Activity'); ?>
<?php else: ?>
    <?php TemplateUtility::printModalHeader('Contacts', array('modules/contacts/activityvalidator.js', 'js/activity.js'), 'Contacts: Schedule Event'); ?>
<?php endif; ?>

<?php if (!$this->isFinishedMode): ?>

<script type="text/javascript">
</script>

    <form name="logActivityForm" id="logActivityForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=addActivityScheduleEvent<?php if($this->onlyScheduleEvent): ?>&amp;onlyScheduleEvent=true<?php endif; ?>" method="post" autocomplete="off">
        <input type="hidden" name="postback" id="postback" value="postback" />
        <input type="hidden" id="contactID" name="contactID" value="<?php echo($this->contactID); ?>" />

        <table class="editTable" width="560">
            <tr id="visibleTR" <?php if($this->onlyScheduleEvent): ?>style="display:none;"<?php endif; ?>>
                <td class="tdVertical">
                    <label id="regardingIDLabel" for="regardingID">Regarding:</label>
                </td>
                <td class="tdData">
                    <select id="regardingID" name="regardingID" class="inputbox" style="width: 150px;">
                        <option value="-1">General</option>

                        <?php foreach ($this->regardingRS as $rowNumber => $regardingData): ?>
                            <option value="<?php $this->_($regardingData['jobOrderID']) ?>"><?php $this->_($regardingData['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

           <tr id="addActivityTR" <?php if($this->onlyScheduleEvent): ?>style="display:none;"<?php endif; ?>>
                <td class="tdVertical">
                    <label id="addActivityLabel" for="addActivity">Activity:</label>
                </td>
                <td class="tdData">
                    <input type="checkbox" name="addActivity" id="addActivity" style="margin-left: 0px;" <?php if(!$this->onlyScheduleEvent): ?>checked<?php endif; ?> onclick="AS_onAddActivityChange('addActivity', 'activityTypeID', 'activityNote', 'addActivitySpanA', 'addActivitySpanB');" />Log an Activity<br />
                    <div id="activityNoteDiv" style="margin-top: 4px;">
                        <span id="addActivitySpanA">Activity Type</span><br />
                        <select id="activityTypeID" name="activityTypeID" class="inputbox" style="width: 150px; margin-bottom: 4px;">
                            <option selected="selected" value="<?php echo(ACTIVITY_CALL); ?>">Call</option>
                            <option value="<?php echo(ACTIVITY_CALL_TALKED); ?>">Call (Talked)</option>
                            <option value="<?php echo(ACTIVITY_CALL_LVM); ?>">Call (LVM)</option>
                            <option value="<?php echo(ACTIVITY_CALL_MISSED); ?>">Call (Missed)</option>
                            <option value="<?php echo(ACTIVITY_EMAIL); ?>">E-Mail</option>
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
                    <input type="checkbox" name="scheduleEvent" id="scheduleEvent" style="margin-left: 0px; <?php if($this->onlyScheduleEvent): ?>display:none;<?php endif; ?>" onclick="AS_onScheduleEventChange('scheduleEvent', 'scheduleEventDiv');" <?php if($this->onlyScheduleEvent): ?>checked<?php endif; ?>/><?php if(!$this->onlyScheduleEvent): ?>Schedule Event<?php endif; ?>
                    <div id="scheduleEventDiv" style="<?php if(!$this->onlyScheduleEvent): ?>display:none;<?php endif; ?>">
                        <table style="border: none; margin: 0px; padding: 0px;">
                            <tr>
                                <td valign="top">
                                    <div style="margin-bottom: 4px;">
                                        <select id="eventTypeID" name="eventTypeID" class="inputbox" style="width: 150px;">
                                            <?php foreach ($this->calendarEventTypes as $eventType): ?>
                                                <option <?php if ($eventType['typeID'] == CALENDAR_EVENT_INTERVIEW): ?>selected <?php endif; ?>value="<?php echo($eventType['typeID']); ?>"><?php $this->_($eventType['description']); ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>

                                    <div style="margin-bottom: 4px;">
                                        <script type="text/javascript">DateInput('dateAdd', true, 'MM-DD-YY', '', -1);</script>
                                    </div>

                                    <div style="margin-bottom: 4px;">
                                        <input type="radio" name="allDay" id="allDay0" value="0" style="margin-left: 0px" checked onchange="AS_onEventAllDayChange('allDay1');" />
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
                                        <input type="text" class="inputbox" name="title" id="title" style="width: 150px" />
                                        </div>
                                        <div style="margin-bottom: 4px;">
                                        <label id="descriptionLabel" for="description">Description</label><br />
                                        <textarea name="description" id="description" cols="20" class="inputbox" style="width: 150px"></textarea>
                                    </div>
                                    
                                    <div <?php if (!$this->allowEventReminders): ?>style="display:none;"<?php endif; ?>>
                                        <input type="checkbox" name="reminderToggle" onclick="if (this.checked) document.getElementById('reminderArea').style.display = ''; else document.getElementById('reminderArea').style.display = '';">&nbsp;<label>Set Reminder</label><br />
                                    </div>
                                    
                                    <div style="display:none;" id="reminderArea">
                                        <div>
                                            <label>E-Mail To:</label><br />
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
        <input type="button" class="button" name="close" value="Cancel" onclick="parentGoToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php echo($this->contactID); ?>');" />
    </form>

    <script type="text/javascript">
        document.changePipelineStatusForm.activityNote.focus();
    </script>

<?php else: ?>
    <?php if (!$this->changesMade): ?>
        <p>No changes have been made.</p>
    <?php else: ?>
        <?php if(!$this->onlyScheduleEvent): ?>
            <?php if ($this->activityAdded): ?>
                <?php if (!empty($this->activityDescription)): ?>
                    <p>An activity entry of type <span class="bold"><?php $this->_($this->activityType); ?></span> has been added with the following note: &quot;<?php echo($this->activityDescription); ?>&quot;.</p>
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
        <input type="button" name="close" class="button" value="Close" onclick="parentGoToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php echo($this->contactID); ?>');" />
    </form>
<?php endif; ?>

    </body>
</html>
