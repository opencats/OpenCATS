<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: Administration</h1>
            </header>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Localization</p>

            <div class="card card-body p-2 mb-2">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm">
                        <div class="mb-2">These options affect how CATS formats numbers, dates, and time. <span class="fw-semibold">You (and your other site users) will need to log out and log back in for these settings to take effect.</span></div>
                        <br />
                        <form action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration" id="localizationForm" method="post">
                            <input type="hidden" name="postback" value="postback" />
                            <input type="hidden" name="administrationMode" value="localization" />

                            <div class="card card-body p-2 mb-2">
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm"><label class="form-label small mb-0" for="timeZone">Please choose your time zone.</label></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm"><?php TemplateUtility::printTimeZoneSelect('timeZone', '', 'form-select form-select-sm', $this->timeZone); ?></div>
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm"><label class="form-label small mb-0" for="dateFormat">Please choose your preferred date format.</label></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm">
                                        <select id="dateFormat" name="dateFormat" class="form-select form-select-sm">
                                            <option value="mdy"<?php if (!$this->isDateDMY): ?> selected<?php endif; ?>>MM-DD-YYYY (US)</option>
                                            <option value="dmy"<?php if ($this->isDateDMY): ?> selected<?php endif; ?>>DD-MM-YYYY (UK)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm"><label class="form-label small mb-0" for="timeFormat">Please choose your preferred time format.</label></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm">
                                        <select id="timeFormat" name="timeFormat" class="form-select form-select-sm">
                                            <option value="12"<?php if (!$this->isTimeFormat24): ?> selected<?php endif; ?>>12-hour (1:30 PM)</option>
                                            <option value="24"<?php if ($this->isTimeFormat24): ?> selected<?php endif; ?>>24-hour (13:30)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm"><label class="form-label small mb-0" for="defaultPhoneCountryCodeDigits">Please enter your default phone country calling code.</label></div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-12 col-sm">
                                        <span>+</span>
                                        <input
                                            type="text"
                                            name="defaultPhoneCountryCodeDigits"
                                            id="defaultPhoneCountryCodeDigits"
                                            value="<?php echo(htmlspecialchars($this->defaultPhoneCountryCodeDigits, ENT_QUOTES | ENT_SUBSTITUTE, HTML_ENCODING)); ?>"
                                            size="5"
                                            maxlength="5"
                                            oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                                         class="form-control form-control-sm" />
                                    </div>
                                </div>
                            </div>
                        <input type="submit" value="Save (And Logout)"  class="btn btn-sm btn-primary" />&nbsp;
                        <input type="button" name="back" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration';"  class="btn btn-sm btn-outline-secondary" />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
