<?php /* $Id: Calendar.tpl 3221 2007-10-17 17:13:22Z will $ */ ?>
<?php TemplateUtility::printHeader(__('Calendar'), array('modules/calendar/Calendar.css', 'js/highlightrows.js', 'modules/calendar/Calendar.js', 'modules/calendar/CalendarUI.js', 'modules/calendar/validator.js')); ?>
<?php 
if (MYTABPOS == 'top') {
	osatutil::TabsAtTop();
	TemplateUtility::printTabs($this->active);
}
?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table width="100%">
                <tr>
                    <td width="3%">
                        <img src="images/calendar.gif" width="24" height="24"alt="Calendar" style="border: none; margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php _e('Calendar');?></h2></td>
                    <td align="right" nowrap="nowrap">
                        <?php if ($this->userIsSuperUser == 1): ?>
                            <input type="checkbox" name="hideNonPublic" id="hideNonPublic" onclick="refreshView();" <?php if ($this->superUserActive): ?>checked<?php endif; ?>/><?php _e('Show Entries from Other Users');?>
                        <?php else: ?>
                            <input type="checkbox" style="display:none;" name="hideNonPublic" id="hideNonPublic" onclick="" />
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

            <p class="note" id="calendarTitle"><?php _e('Calendar');?></p>

            <table style="border-collapse: collapse;">
                <tr style="vertical-align: top;">
                    <td style="padding: 0px;">
                        <table style="width: 240px; border: none; vertical-align:top; border-collapse: collapse;" id="tableNav">
                            <tr style="vertical-align:top;">
                            </tr>

                            <tr style="vertical-align:top;">
                                <td style="text-align: center;">
                                    <!-- FIXME: Mini calendar -->
                                </td>
                            </tr>

                            <tr style="vertical-align: top;">
                                <td id="upcomingEventsTD" style="padding: 0px;">
                                    <?php echo($this->summaryHTML); ?>
                                </td>
                                <td id="addEventTD" style="display:none;">
                                    <p class="noteUnsized"><?php _e('Add Event');?></p>
                                    <form name="addEventForm" id="addEventForm" action="<?php echo(osatutil::getIndexName()); ?>?m=calendar&amp;a=addEvent" method="post" onsubmit="return checkAddForm(document.addEventForm);" autocomplete="off">
                                        <input type="hidden" name="postback" id="postbackA" value="postback" />

                                        <table class="editTableMini" width="235">
                                            <tr>
                                                <td class="tdVertical">
                                                    <label id="titleLabel" for="title"><?php _e('Title');?>:</label>
                                                </td>
                                                <td class="tdData">
                                                    <input type="text" class="inputbox" name="title" id="title" style="width: 150px" />&nbsp;*
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="tdVertical">
                                                    <label id="eventTypeLabel" for="type"><?php _e('Type');?>:</label>
                                                </td>
                                                <td class="tdData">
                                                    <select id="type" name="type" class="inputbox" style="width: 150px;">
                                                        <option value="">(<?php _e('Select a Type');?>)</option>
                                                        <?php foreach ($this->calendarEventTypes as $type): ?>
                                                            <option value="<?php echo($type['typeID']); ?>"><?php echo($type['description']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>&nbsp;*
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="tdVertical">
                                                    <label id="dateLabel" for="date"><?php _e('Public');?>:</label>
                                                </td>
                                                <td class="tdData">
                                                    <input type="checkBox" name="publicEntry" id="publicEntry" <?php if ($this->defaultPublic == 'true'): ?>checked<?php endif; ?> /><?php _e('Public Entry');?>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="tdVertical">
                                                    <label id="dateLabel" for="date"><?php _e('Date');?>:</label>
                                                </td>
                                                <td nowrap="nowrap" class="tdData">
                                                    <script type="text/javascript">DateInput('dateAdd', true, 'MM-DD-YY', '<?php echo($this->currentDateMDY); ?>', -1);</script>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="tdVertical">
                                                    <label id="timeLabel" for="time"><?php _e('Time');?>:</label>
                                                </td>
                                                <td class="tdData">
                                                    <input type="radio" name="allDay" id="allDay0" value="0" checked onchange="setAddAllDayEnabled();" />
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
                                                    <br />

                                                    <input type="radio" name="allDay" id="allDay1" value="1" onchange="setAddAllDayEnabled();" /><?php _e('All Day / No Specific Time');?><br />
                                                    <!-- FIXME: Remove hide style. -->
                                                    <span style="<?php if(!$this->allowEventReminders): ?>display:none;<?php endif; ?>">
                                                        <input type="checkBox" name="reminderToggle" id="reminderToggle" onclick="considerCheckBox('reminderToggle', 'sendEmailTD');"><?php _e('Send e-mail reminder');?>
                                                    </span>
                                                </td>
                                            </tr>

                                             <tr id="sendEmailTD" style="display:none;">
                                                <td class="tdVertical">
                                                    <?php _e('E-Mail');?>:
                                                </td>
                                                <td class="tdData">
                                                    <table style="border-collapse: collapse;">
                                                        <tr>
                                                            <td>
                                                                <?php _e('To');?>:
                                                            </td>
                                                            <td>
                                                                <input type="text" id="sendEmail" name="sendEmail" class="inputbox" style="width:115px;" value="<?php $this->_($this->userEmail); ?>" />
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <?php _e('Time');?>:
                                                            </td>
                                                            <td>
                                                                <select id="reminderTime" name="reminderTime" style="width:115px;">
                                                                    <option value="15">15 <?php _e('min early');?></option>
                                                                    <option value="30">30 <?php _e('min early');?></option>
                                                                    <option value="45">45 <?php _e('min early');?></option>
                                                                    <option value="60">1 <?php _e('hour early');?></option>
                                                                    <option value="120">2 <?php _e('hours early');?></option>
                                                                    <option value="1440">1 <?php _e('day early');?></option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="tdVertical">
                                                    <label id="durationLabel" for="duration"><?php _e('Length');?>:</label>
                                                </td>
                                                <td class="tdData">
                                                    <select id="duration" name="duration" class="inputbox" style="width: 150px;">
                                                        <option value="15">15 <?php _e('minutes');?></option>
                                                        <option value="30">30 <?php _e('minutes');?></option>
                                                        <option value="45">45 <?php _e('minutes');?></option>
                                                        <option value="60" selected="selected">1 <?php _e('hour');?></option>
                                                        <option value="90">1.5 <?php _e('hours');?></option>
                                                        <option value="120">2 <?php _e('hours');?></option>
                                                        <option value="180">3 <?php _e('hours');?></option>
                                                        <option value="240">4 <?php _e('hours');?></option>
                                                        <option value="300"><?php _e('More than 4 hours');?></option>
                                                    </select>
                                                </td>
                                             </tr>

                                            <tr>
                                                <td class="tdVertical">
                                                    <label id="descriptionLabel" for="description"><?php _e('Desc');?>:</label>
                                                </td>
                                                <td class="tdData">
                                                    <textarea id="description" name="description" style="width:150px; height:180px;"></textarea>
                                                </td>
                                             </tr>

                                        </table>
                                        <div style="text-align: center;">
                                            <input type="submit" class="button" name="submit" value="Add Event" />
                                        </div>
                                    </form>
                                </td>
                                <td style="display:none" id="editEventTD">
                                    <p class="noteUnsized"><?php _e('Edit Event');?></p>
                                    <form name="editEventForm" id="editEventForm" action="<?php echo(osatutil::getIndexName()); ?>?m=calendar&amp;a=editEvent" method="post" onsubmit="return checkEditForm(document.editEventForm);" autocomplete="off">
                                        <input type="hidden" name="postback" id="postbackB" value="postback" />
                                        <input type="hidden" name="eventID" id="eventIDEdit" />
                                        <input type="hidden" name="dataItemType" id="dataItemTypeEdit" />
                                        <input type="hidden" name="dataItemID" id="dataItemIDEdit" />
                                        <input type="hidden" name="jobOrderID" id="jobOrderIDEdit" />

                                        <table class="editTableMini" width="235">
                                            <tr>
                                                <td class="tdVertical">
                                                    <label id="titleLabelEdit" for="title"><?php _e('Title');?>:</label>
                                                </td>
                                                <td class="tdData">
                                                    <input type="text" class="inputbox" name="title" id="titleEdit" style="width: 150px" />&nbsp;*
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="tdVertical">
                                                    <label id="eventTypeLabelEdit" for="type"><?php _e('Type');?>:</label>
                                                </td>
                                                <td class="tdData">
                                                    <select id="typeEdit" name="type" class="inputbox" style="width: 150px;">
                                                        <option value="">(<?php _e('Select a Type');?>)</option>
                                                        <?php foreach ($this->calendarEventTypes as $type): ?>
                                                            <option value="<?php echo($type['typeID']); ?>"><?php echo($type['description']); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>&nbsp;*
                                                </td>
                                            </tr>

                                             <tr>
                                                <td class="tdVertical">
                                                    <label id="dateLabel" for="date"><?php _e('Public');?>:</label>
                                                </td>
                                                <td class="tdData">
                                                    <input type="checkBox" name="publicEntry" id="publicEntryEdit" /><?php _e('Public Entry');?>
                                                </td>
                                            </tr>

                                           <tr>
                                                <td class="tdVertical">
                                                    <label id="dateLabel" for="date"><?php _e('Date');?>:</label>
                                                </td>
                                                <td nowrap="nowrap" class="tdData">
                                                    <script type="text/javascript">DateInput('dateEdit', true, 'MM-DD-YY', '<?php echo($this->currentDateMDY); ?>', -1);</script>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="tdVertical">
                                                    <label id="timeLabel" for="time"><?php _e('Time');?>:</label>
                                                </td>
                                                <td class="tdData">
                                                    <input type="radio" name="allDay" id="allDayEdit0" value="0" checked onchange="setEditAllDayEnabled();" />
                                                    <select id="hourEdit" name="hour" class="inputbox" style="width: 40px;">
                                                        <?php for ($i = 1; $i <= 12; ++$i): ?>
                                                            <option value="<?php echo($i); ?>"><?php echo(sprintf('%02d', $i)); ?></option>
                                                        <?php endfor; ?>
                                                    </select>&nbsp;
                                                    <select id="minuteEdit" name="minute" class="inputbox" style="width: 40px;">
                                                        <?php for ($i = 0; $i <= 45; $i = $i + 15): ?>
                                                            <option value="<?php echo(sprintf('%02d', $i)); ?>">
                                                                <?php echo(sprintf('%02d', $i)); ?>
                                                            </option>
                                                        <?php endfor; ?>
                                                    </select>&nbsp;
                                                    <select id="meridiemEdit" name="meridiem" class="inputbox" style="width: 45px;">
                                                        <option value="AM">AM</option>
                                                        <option value="PM">PM</option>
                                                    </select>
                                                    <br />

                                                    <input type="radio" name="allDay" id="allDayEdit1" value="1" onchange="setEditAllDayEnabled();" /><?php _e('All Day / No Specific Time');?><br />
                                                     <!-- FIXME: Remove hide style. -->
                                                    <span style="<?php if(!$this->allowEventReminders): ?>display:none;<?php endif; ?>">
                                                       <input type="checkBox" name="reminderToggle" id="reminderToggleEdit" onclick="considerCheckBox('reminderToggleEdit', 'sendEmailTDEdit');"><?php _e('Send e-mail reminder');?>
                                                    </span>
                                                </td>
                                            </tr>

                                            <tr id="sendEmailTDEdit" style="display: none;">
                                                <td class="tdVertical">
                                                    <?php _e('E-Mail');?>:
                                                </td>
                                                <td class="tdData">
                                                    <table style="border-collapse: collapse;">
                                                        <tr>
                                                            <td>
                                                                <?php _e('To');?>:
                                                            </td>
                                                            <td>
                                                                <input type="text" id="sendEmailEdit" name="sendEmail" class="inputbox" style="width:115px;" value="<?php $this->_($this->userEmail); ?>" />
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td>
                                                                <?php _e('Time');?>:
                                                            </td>
                                                            <td>
                                                                <select id="reminderTimeEdit" name="reminderTime" style="width:115px;">
                                                                    <option value="15">15 <?php _e('min early');?></option>
                                                                    <option value="30">30 <?php _e('min early');?></option>
                                                                    <option value="45">45 <?php _e('min early');?></option>
                                                                    <option value="60">1 <?php _e('hour early');?></option>
                                                                    <option value="120">2 <?php _e('hours early');?></option>
                                                                    <option value="1440">1 <?php _e('day early');?></option>
                                                                </select>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td class="tdVertical">
                                                    <label id="durationLabel" for="durationEdit"><?php _e('Length');?>:</label>
                                                </td>
                                                <td class="tdData">
                                                    <select id="durationEdit" name="duration" class="inputbox" style="width: 150px;">
                                                        <option value="15">15 <?php _e('minutes');?></option>
                                                        <option value="30">30 <?php _e('minutes');?></option>
                                                        <option value="45">45 <?php _e('minutes');?></option>
                                                        <option value="60" selected="selected">1 <?php _e('hour');?></option>
                                                        <option value="90">1.5 <?php _e('hours');?></option>
                                                        <option value="120">2 <?php _e('hours');?></option>
                                                        <option value="180">3 <?php _e('hours');?></option>
                                                        <option value="240">4 <?php _e('hours');?></option>
                                                        <option value="300"><?php _e('More than 4 hours');?></option>
                                                    </select>
                                                </td>
                                             </tr>

                                            <tr>
                                                <td class="tdVertical">
                                                    <label id="descriptionLabel" for="descriptionEdit"><?php _e('Desc');?>:</label>
                                                </td>
                                                <td class="tdData">
                                                    <textarea id="descriptionEdit" name="description" style="width: 150px; height: 180px;"></textarea>
                                                </td>
                                             </tr>

                                        </table>
                                        <div style="text-align: center;">
                                            <input type="submit" class="button" name="submit" value="Save" />
                                            <?php if ($this->accessLevel >= ACCESS_LEVEL_DELETE): ?>
                                                <input type="button" class="button" name="delete" value="Delete" onclick="confirmDeleteEntry();" />
                                            <?php endif; ?>
                                        </div>
                                    </form>
                                </td>
                                <td style="display:none" id="viewEventTD">
                                    <table width="235">
                                    <tr>
                                    <td>
                                    <p class="noteUnsized"><?php _e('View Event');?></p>
                                    <span id="viewEventTitle" style="font-weight:bold"></span><br />
                                    <?php _e('Entered By');?>: <span id="viewEventOwner"></span><br />
                                    <?php _e('Event Type');?>: <span id="viewEventType"></span><br />
                                    <span id="viewEventLink"></span><br />
                                    <br />
                                    <?php _e('Date');?>: <span id="viewEventDate"></span><br />
                                    <?php _e('Time');?>: <span id="viewEventTime"></span><br />
                                    <?php _e('Duration');?>: <span id="viewEventDuration"></span><br />
                                    <?php _e('Reminder');?>: <span id="viewEventReminder"></span><br />
                                    <br />
                                    <?php _e('Description');?>:<br />
                                    <span id="viewEventDescription"></span><br />
                                    <br />
                                    <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>
                                        <input type="button" class="button" name="Edit" value="Edit Event" onclick="calendarEditEvent(currentViewedEntry);" />
                                    <?php endif; ?>
                                    </td>
                                    </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="padding-left: 8px;">
                        <table id="calendarMonthParent" style="display:none;border-collapse: collapse;">
                            <tr>
                                <td>
                                    <table style="border-collapse: collapse;">
                                        <tr>
                                            <td>
                                                <input type="button" class="buttonCalendar" value="Day" onclick="userCalendarViewDay()" />&nbsp;
                                                <input type="button" class="buttonCalendar" value="Week" onclick="userCalendarViewWeek()" />&nbsp;
                                                <input type="button" class="buttonDownCalendar" value="Month" onclick="userCalendarViewMonth()" />
                                                &nbsp;&nbsp;&nbsp;&nbsp;
                                            </td>
                                            <td style="width:30px;">
                                                <span id="linkMonthBack"></span>
                                            </td>
                                            <td style="width:300px;text-align:center;">
                                                <span id="monthNotice"></span>
                                            </td>
                                            <td>
                                                <span id="linkMonthForeward"></span>
                                            </td>
                                        </tr>
                                    </table>
                                 </td>
                            </tr>
                            <tr>
                                <td>
                                    <table id="calendarMonth" style="width:675px;" onmouseup="javascript:trackTableSelect(event);">
                                        <tr >
                                            <?php if ($this->firstDayMonday != '1'): ?><th height="1%"><?php _e('Sunday');?></th><?php endif; ?>
                                            <th height="1%"><?php _e('Monday');?></th>
                                            <th height="1%"><?php _e('Tuesday');?></th>
                                            <th height="1%"><?php _e('Wednesday');?></th>
                                            <th height="1%"><?php _e('Thursday');?></th>
                                            <th height="1%"><?php _e('Friday');?></th>
                                            <th height="1%"><?php _e('Saturday');?></th>
                                            <?php if ($this->firstDayMonday == '1'): ?><th height="1%"><?php _e('Sunday');?></th><?php endif; ?>
                                        </tr>

                                        <?php $calendarPosition = 0; ?>
                                        <?php for ($calendarRow = 1; $calendarRow <= 6; ++$calendarRow): ?>
                                            <tr id="calendarRow<?php echo($calendarRow); ?>">
                                                <?php $weekPosition = 1; ?>
                                                <?php for ($weekday = 1; $weekday <= 7; ++$weekday): ?>
                                                    <td class="empty" id="calendarMonthCell<?php echo($calendarPosition++); ?>" height="17%">&nbsp;</td>
                                                <?php endfor; ?>
                                            </tr>
                                        <?php endfor; ?>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table id="calendarWeekParent" style="display:none;">
                            <tr>
                                <td>
                                     <table style="border-collapse: collapse;">
                                        <tr>
                                            <td>
                                                <input type="button" class="buttonCalendar" value="Day" onclick="userCalendarViewDay()" />&nbsp;
                                                <input type="button" class="buttonDownCalendar" value="Week" onclick="userCalendarViewWeek()" />&nbsp;
                                                <input type="button" class="buttonCalendar" value="Month" onclick="userCalendarViewMonth()" />
                                                &nbsp;&nbsp;&nbsp;&nbsp;
                                            </td>
                                            <td style="width:30px;">
                                                <span id="linkWeekBack"></span>
                                            </td>
                                            <td style="width:300px;text-align:center;">
                                                <span id="weekNotice"></span>
                                            </td>
                                            <td>
                                                <span id="linkWeekForeward"></span>
                                            </td>
                                        </tr>
                                    </table>
                                 </td>
                            </tr>
                            <tr>
                                <td>
                                    <table id="calendarWeek" style="width:675px;" onmouseup="javascript:trackTableSelect(event, '#e9e9e9');">
                                        <?php if ($this->firstDayMonday != '1'): ?>
                                            <tr>
                                                <th><?php _e('Sunday');?> <br /><span id="weekDay0"></span></th>
                                                <td class="empty" id="calendarWeekCell0"></td>
                                            </tr>
                                            <?php if ($this->firstDayMonday == '1'): ?></span><?php endif; ?>
                                            <tr>
                                                <th><?php _e('Monday');?> <br /><span id="weekDay1"></span></th>
                                                <td class="empty" id="calendarWeekCell1"></td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Tuesday');?> <br /><span id="weekDay2"></span></th>
                                                <td class="empty" id="calendarWeekCell2"></td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Wednesday');?> <br /><span id="weekDay3"></span></th>
                                                <td class="empty" id="calendarWeekCell3"></td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Thursday');?> <br /><span id="weekDay4"></span></th>
                                                <td class="empty" id="calendarWeekCell4"></td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Friday');?> <br /><span id="weekDay5"></span></th>
                                                <td class="empty" id="calendarWeekCell5"></td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Saturday');?> <br /><span id="weekDay6"></span></th>
                                                <td class="empty" id="calendarWeekCell6"></td>
                                            </tr>
                                        <?php else: ?>
                                            <tr>
                                                <th><?php _e('Monday');?> <br /><span id="weekDay0"></span></th>
                                                <td class="empty" id="calendarWeekCell0"></td>
                                            </tr>
                                            <?php if ($this->firstDayMonday == '1'): ?></span><?php endif; ?>
                                            <tr>
                                                <th><?php _e('Tuesday');?> <br /><span id="weekDay1"></span></th>
                                                <td class="empty" id="calendarWeekCell1"></td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Wednesday');?> <br /><span id="weekDay2"></span></th>
                                                <td class="empty" id="calendarWeekCell2"></td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Thursday');?> <br /><span id="weekDay3"></span></th>
                                                <td class="empty" id="calendarWeekCell3"></td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Friday');?> <br /><span id="weekDay4"></span></th>
                                                <td class="empty" id="calendarWeekCell4"></td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Saturday');?> <br /><span id="weekDay5"></span></th>
                                                <td class="empty" id="calendarWeekCell5"></td>
                                            </tr>
                                            <tr>
                                                <th><?php _e('Sunday');?> <br /><span id="weekDay6"></span></th>
                                                <td class="empty" id="calendarWeekCell6"></td>
                                            </tr>
                                        <?php endif; ?>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table id="calendarDayParent" style="display:none;">
                            <tr>
                                <td>
                                    <table style="border-collapse: collapse;">
                                        <tr>
                                            <td>
                                                <input type="button" class="buttonDownCalendar" value="Day" onclick="userCalendarViewDay()" />&nbsp;
                                                <input type="button" class="buttonCalendar" value="Week" onclick="userCalendarViewWeek()" />&nbsp;
                                                <input type="button" class="buttonCalendar" value="Month" onclick="userCalendarViewMonth()" />
                                                &nbsp;&nbsp;&nbsp;&nbsp;
                                            </td>
                                            <td style="width:30px;">
                                                <span id="linkDayBack"></span>
                                            </td>
                                            <td style="width:300px;text-align:center;">
                                                <span id="dayNotice"></span>
                                            </td>
                                            <td>
                                                <span id="linkDayForeward"></span>
                                            </td>
                                        </tr>
                                    </table>
                                 </td>
                            </tr>
                            <tr>
                                <td>
                                    <table id="calendarDay" style="width:675px;" onmouseup="javascript:trackTableSelect(event, '#e9e9e9');">
                                        <tr>
                                            <th><?php _e('Morning');?></th>
                                            <td class="empty" id="calendarDayCell0"></td>
                                        </tr>
                                            <?php for ($i = $this->dayHourStart; $i <= $this->dayHourEnd; $i++): ?>
                                            <tr>
                                                <th><?php if (!$this->militaryTime && $i>12):?><?php echo($i - 12); ?><?php else: ?><?php echo($i); ?><?php endif; ?>:00<br /><span id="weekDay1"></span></th>
                                                <td class="empty" id="calendarDayCell<?php echo($i - $this->dayHourStart + 1); ?>"></td>
                                            </tr>
                                            <?php endfor; ?>
                                        <tr>
                                            <th><?php _e('Evening');?><br /><span id="weekDay2"></span></th>
                                            <td class="empty" id="calendarDayCell<?php echo($this->dayHourEnd - $this->dayHourStart + 2); ?>"></td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>
    <?php
if (MYTABPOS == 'bottom') 
{
    
	TemplateUtility::printTabs($this->active);
	?>
	</div>
    <div id="bottomShadow"></div>
    
    <?php 
	osatutil::TabsAtBottom();
}else{
	?>
	</div>
    <div id="bottomShadow"></div>
    <?php 
}
?>
<?php TemplateUtility::printFooter(); 
		
?>

<script type="text/javascript">
        /* Settings */
        indexName = '<?php echo(osatutil::getIndexName()); ?>';
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
        accessLevel =  <?php echo($this->accessLevel); ?>;

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
