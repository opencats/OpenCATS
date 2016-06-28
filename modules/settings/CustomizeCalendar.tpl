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
                                        <?php // FIXME: Generate this more automatically? ?>
                                        <select name="dayStart">
                                            <option value="1"<?php if ($this->calendarSettingsRS['dayStart'] == '1'): ?> selected<?php endif; ?>>1 AM</option>
                                            <option value="2"<?php if ($this->calendarSettingsRS['dayStart'] == '2'): ?> selected<?php endif; ?>>2 AM</option>
                                            <option value="3"<?php if ($this->calendarSettingsRS['dayStart'] == '3'): ?> selected<?php endif; ?>>3 AM</option>
                                            <option value="4"<?php if ($this->calendarSettingsRS['dayStart'] == '4'): ?> selected<?php endif; ?>>4 AM</option>
                                            <option value="5"<?php if ($this->calendarSettingsRS['dayStart'] == '5'): ?> selected<?php endif; ?>>5 AM</option>
                                            <option value="6"<?php if ($this->calendarSettingsRS['dayStart'] == '6'): ?> selected<?php endif; ?>>6 AM</option>
                                            <option value="7"<?php if ($this->calendarSettingsRS['dayStart'] == '7'): ?> selected<?php endif; ?>>7 AM</option>
                                            <option value="8"<?php if ($this->calendarSettingsRS['dayStart'] == '8'): ?> selected<?php endif; ?>>8 AM</option>
                                            <option value="9"<?php if ($this->calendarSettingsRS['dayStart'] == '9'): ?> selected<?php endif; ?>>9 AM</option>
                                            <option value="10"<?php if ($this->calendarSettingsRS['dayStart'] == '10'): ?> selected<?php endif; ?>>10 AM</option>
                                            <option value="11"<?php if ($this->calendarSettingsRS['dayStart'] == '11'): ?> selected<?php endif; ?>>11 AM</option>
                                            <option value="12"<?php if ($this->calendarSettingsRS['dayStart'] == '12'): ?> selected<?php endif; ?>>12 PM</option>
                                            <option value="13"<?php if ($this->calendarSettingsRS['dayStart'] == '13'): ?> selected<?php endif; ?>>1 PM</option>
                                            <option value="14"<?php if ($this->calendarSettingsRS['dayStart'] == '14'): ?> selected<?php endif; ?>>2 PM</option>
                                            <option value="15"<?php if ($this->calendarSettingsRS['dayStart'] == '15'): ?> selected<?php endif; ?>>3 PM</option>
                                            <option value="16"<?php if ($this->calendarSettingsRS['dayStart'] == '16'): ?> selected<?php endif; ?>>4 PM</option>
                                            <option value="17"<?php if ($this->calendarSettingsRS['dayStart'] == '17'): ?> selected<?php endif; ?>>5 PM</option>
                                            <option value="18"<?php if ($this->calendarSettingsRS['dayStart'] == '18'): ?> selected<?php endif; ?>>6 PM</option>
                                            <option value="19"<?php if ($this->calendarSettingsRS['dayStart'] == '19'): ?> selected<?php endif; ?>>7 PM</option>
                                            <option value="20"<?php if ($this->calendarSettingsRS['dayStart'] == '20'): ?> selected<?php endif; ?>>8 PM</option>
                                            <option value="21"<?php if ($this->calendarSettingsRS['dayStart'] == '21'): ?> selected<?php endif; ?>>9 PM</option>
                                            <option value="22"<?php if ($this->calendarSettingsRS['dayStart'] == '22'): ?> selected<?php endif; ?>>10 PM</option>
                                            <option value="23"<?php if ($this->calendarSettingsRS['dayStart'] == '23'): ?> selected<?php endif; ?>>11 PM</option>
                                            <option value="0"<?php if ($this->calendarSettingsRS['dayStart'] == '0'): ?> selected<?php endif; ?>>12 AM</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="tdVertical" style="width:250px;">
                                        Work day stop time:
                                    </td>
                                    <td class="tdData">
                                        <?php // FIXME: Generate this more automatically? ?>
                                        <select name="dayStop">
                                            <option value="1"<?php if ($this->calendarSettingsRS['dayStop'] == '1'): ?> selected<?php endif; ?>>1 AM</option>
                                            <option value="2"<?php if ($this->calendarSettingsRS['dayStop'] == '2'): ?> selected<?php endif; ?>>2 AM</option>
                                            <option value="3"<?php if ($this->calendarSettingsRS['dayStop'] == '3'): ?> selected<?php endif; ?>>3 AM</option>
                                            <option value="4"<?php if ($this->calendarSettingsRS['dayStop'] == '4'): ?> selected<?php endif; ?>>4 AM</option>
                                            <option value="5"<?php if ($this->calendarSettingsRS['dayStop'] == '5'): ?> selected<?php endif; ?>>5 AM</option>
                                            <option value="6"<?php if ($this->calendarSettingsRS['dayStop'] == '6'): ?> selected<?php endif; ?>>6 AM</option>
                                            <option value="7"<?php if ($this->calendarSettingsRS['dayStop'] == '7'): ?> selected<?php endif; ?>>7 AM</option>
                                            <option value="8"<?php if ($this->calendarSettingsRS['dayStop'] == '8'): ?> selected<?php endif; ?>>8 AM</option>
                                            <option value="9"<?php if ($this->calendarSettingsRS['dayStop'] == '9'): ?> selected<?php endif; ?>>9 AM</option>
                                            <option value="10"<?php if ($this->calendarSettingsRS['dayStop'] == '10'): ?> selected<?php endif; ?>>10 AM</option>
                                            <option value="11"<?php if ($this->calendarSettingsRS['dayStop'] == '11'): ?> selected<?php endif; ?>>11 AM</option>
                                            <option value="12"<?php if ($this->calendarSettingsRS['dayStop'] == '12'): ?> selected<?php endif; ?>>12 PM</option>
                                            <option value="13"<?php if ($this->calendarSettingsRS['dayStop'] == '13'): ?> selected<?php endif; ?>>1 PM</option>
                                            <option value="14"<?php if ($this->calendarSettingsRS['dayStop'] == '14'): ?> selected<?php endif; ?>>2 PM</option>
                                            <option value="15"<?php if ($this->calendarSettingsRS['dayStop'] == '15'): ?> selected<?php endif; ?>>3 PM</option>
                                            <option value="16"<?php if ($this->calendarSettingsRS['dayStop'] == '16'): ?> selected<?php endif; ?>>4 PM</option>
                                            <option value="17"<?php if ($this->calendarSettingsRS['dayStop'] == '17'): ?> selected<?php endif; ?>>5 PM</option>
                                            <option value="18"<?php if ($this->calendarSettingsRS['dayStop'] == '18'): ?> selected<?php endif; ?>>6 PM</option>
                                            <option value="19"<?php if ($this->calendarSettingsRS['dayStop'] == '19'): ?> selected<?php endif; ?>>7 PM</option>
                                            <option value="20"<?php if ($this->calendarSettingsRS['dayStop'] == '20'): ?> selected<?php endif; ?>>8 PM</option>
                                            <option value="21"<?php if ($this->calendarSettingsRS['dayStop'] == '21'): ?> selected<?php endif; ?>>9 PM</option>
                                            <option value="22"<?php if ($this->calendarSettingsRS['dayStop'] == '22'): ?> selected<?php endif; ?>>10 PM</option>
                                            <option value="23"<?php if ($this->calendarSettingsRS['dayStop'] == '23'): ?> selected<?php endif; ?>>11 PM</option>
                                            <option value="0"<?php if ($this->calendarSettingsRS['dayStop'] == '0'): ?> selected<?php endif; ?>>12 AM</option>
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
