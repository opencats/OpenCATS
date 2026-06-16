<?php /* $Id: CustomizeCalendar.tpl 1535 2007-01-22 17:55:29Z will $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table width="100%">
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td align="left"><h2>Settings: Customization</h2></td>
                </tr>
            </table>

            <p class="note">Calendar Customization</p>
            <table>
                <tr>
                    <td>
                        <form name="editCalendarForm" id="editCalendarForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=customizeCalendar" method="post">
                            <input type="hidden" name="postback" value="postback" />
                            <table class="editTable" width="700">
                                <tr>
                                    <td class="tdVertical" style="width:250px;">
                                        Disable AJAX dynamic event loading:
                                    </td>
                                    <td class="tdData">
                                        <input type="checkbox" name="noAjax"<?php if ($this->calendarSettingsRS['noAjax'] == '1'): ?> checked<?php endif; ?>>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="tdVertical" style="width:250px;">
                                        By default, all events are public:
                                    </td>
                                    <td class="tdData">
                                        <input type="checkbox" name="defaultPublic"<?php if ($this->calendarSettingsRS['defaultPublic'] == '1'): ?> checked<?php endif; ?>>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="tdVertical" style="width:250px;">
                                        First day of the week is Monday:
                                    </td>
                                    <td class="tdData">
                                        <input type="checkbox" name="firstDayMonday"<?php if ($this->calendarSettingsRS['firstDayMonday'] == '1'): ?> checked<?php endif; ?>>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="tdVertical" style="width:250px;">
                                        Work day start time:
                                    </td>
                                    <td class="tdData">
                                        <select name="dayStart">
                                            <?php foreach ($this->isTimeFormat24 ? range(0, 23) : array_merge(range(1, 23), array(0)) as $_h): ?>
                                            <option value="<?php echo $_h; ?>"<?php if ($this->calendarSettingsRS['dayStart'] == $_h): ?> selected<?php endif; ?>><?php
                                                if ($this->isTimeFormat24) { echo sprintf('%02d:00', $_h); }
                                                elseif ($_h == 0)  { echo '12 AM'; }
                                                elseif ($_h < 12)  { echo $_h . ' AM'; }
                                                elseif ($_h == 12) { echo '12 PM'; }
                                                else               { echo ($_h - 12) . ' PM'; }
                                            ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="tdVertical" style="width:250px;">
                                        Work day stop time:
                                    </td>
                                    <td class="tdData">
                                        <select name="dayStop">
                                            <?php foreach ($this->isTimeFormat24 ? range(0, 23) : array_merge(range(1, 23), array(0)) as $_h): ?>
                                            <option value="<?php echo $_h; ?>"<?php if ($this->calendarSettingsRS['dayStop'] == $_h): ?> selected<?php endif; ?>><?php
                                                if ($this->isTimeFormat24) { echo sprintf('%02d:00', $_h); }
                                                elseif ($_h == 0)  { echo '12 AM'; }
                                                elseif ($_h < 12)  { echo $_h . ' AM'; }
                                                elseif ($_h == 12) { echo '12 PM'; }
                                                else               { echo ($_h - 12) . ' PM'; }
                                            ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="tdVertical" style="width:250px;">
                                        Default calendar view:
                                    </td>
                                    <td class="tdData">
                                        <select name="calendarView">
                                            <option value="DAYVIEW"<?php if ($this->calendarSettingsRS['calendarView'] == 'DAYVIEW'): ?> selected<?php endif; ?>>Day View</option>
                                            <option value="WEEKVIEW"<?php if ($this->calendarSettingsRS['calendarView'] == 'WEEKVIEW'): ?> selected<?php endif; ?>>Week View</option>
                                            <option value="MONTHVIEW"<?php if ($this->calendarSettingsRS['calendarView'] == 'MONTHVIEW'): ?> selected<?php endif; ?>>Month View</option>
                                        </select>
                                    </td>
                                </tr>
                            </table>
                            <input type="submit" class="button" name="submit" id="submit" value="Save" />&nbsp;
                            <input type="reset"  class="button" name="reset"  id="reset"  value="Reset" />&nbsp;
                        </form>
                    </td>
                </tr>
            </table>

        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
