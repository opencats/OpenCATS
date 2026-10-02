<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: Customization</h1>
            </header>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Calendar Customization</p>
            <div class="card card-body p-2 mb-2">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm">
                        <form name="editCalendarForm" id="editCalendarForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=customizeCalendar" method="post">
                            <input type="hidden" name="postback" value="postback" />
                            <div class="card card-body p-2 mb-2">
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label class="form-label small mb-0" for="noAjax">Disable AJAX dynamic event loading:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="checkbox" name="noAjax" id="noAjax"<?php if ($this->calendarSettingsRS['noAjax'] == '1'): ?> checked<?php endif; ?> class="form-check-input">
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label class="form-label small mb-0" for="defaultPublic">By default, all events are public:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="checkbox" name="defaultPublic" id="defaultPublic"<?php if ($this->calendarSettingsRS['defaultPublic'] == '1'): ?> checked<?php endif; ?> class="form-check-input">
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label class="form-label small mb-0" for="firstDayMonday">First day of the week is Monday:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <input type="checkbox" name="firstDayMonday" id="firstDayMonday"<?php if ($this->calendarSettingsRS['firstDayMonday'] == '1'): ?> checked<?php endif; ?> class="form-check-input">
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label class="form-label small mb-0" for="dayStart">Work day start time:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <select name="dayStart" id="dayStart" class="form-select form-select-sm">
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
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label class="form-label small mb-0" for="dayStop">Work day stop time:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <select name="dayStop" id="dayStop" class="form-select form-select-sm">
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
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-sm-4 col-lg-3">
                                        <label class="form-label small mb-0" for="calendarView">Default calendar view:</label>
                                    </div>
                                    <div class="col-12 col-sm">
                                        <select name="calendarView" id="calendarView" class="form-select form-select-sm">
                                            <option value="DAYVIEW"<?php if ($this->calendarSettingsRS['calendarView'] == 'DAYVIEW'): ?> selected<?php endif; ?>>Day View</option>
                                            <option value="WEEKVIEW"<?php if ($this->calendarSettingsRS['calendarView'] == 'WEEKVIEW'): ?> selected<?php endif; ?>>Week View</option>
                                            <option value="MONTHVIEW"<?php if ($this->calendarSettingsRS['calendarView'] == 'MONTHVIEW'): ?> selected<?php endif; ?>>Month View</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <input type="submit" name="submit" id="submit" value="Save"  class="btn btn-sm btn-primary" />&nbsp;
                            <input type="reset"  name="reset"  id="reset"  value="Reset"  class="btn btn-sm btn-outline-secondary" />&nbsp;
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
