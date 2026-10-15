<?php TemplateUtility::printHeader('Settings', 'js/sorttable.js'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: User Details</h1>
            </header>

            <p class="d-flex flex-wrap justify-content-between gap-2 bg-secondary-subtle rounded p-2 mb-2 fw-semibold">
                <?php /* Leave these separate; just one span makes the background image display weird. */ ?>
                <?php if ($this->privledged): ?>
                    <span >User Details</span>
                    <span class="ms-sm-auto"><a href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=manageUsers'>Back to User Management</a></span>&nbsp;
                <?php else: ?>
                    User Details
                <?php endif; ?>
            </p>

            <?php if (!$this->privledged): ?>
                <p>Contact your site administrator to change these settings.</p>
            <?php endif; ?>

            <div class="card card-body p-2 mb-2">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-sm">
                        <div class="card card-body p-2 mb-2">
                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 col-lg-3">Name:</div>
                                <div class="col-12 col-sm">
                                    <span class="bold">
                                        <?php $this->_($this->data['firstName']); ?>
                                        <?php $this->_($this->data['lastName']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 col-lg-3">E-Mail:</div>
                                <div class="col-12 col-sm">
                                    <a href="mailto:<?php $this->_($this->data['email']); ?>">
                                        <?php $this->_($this->data['email']); ?>
                                    </a>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 col-lg-3">Username:</div>
                                <div class="col-12 col-sm"><?php $this->_($this->data['username']); ?></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 col-lg-3">Access Level:</div>
                                <div class="col-12 col-sm"><?php $this->_($this->data['accessLevelLongDescription']); ?></div>
                            </div>

                            <?php if($this->EEOSettingsRS['enabled'] == 1): ?> <div class="row g-2 mb-2">
                                <div class="col-sm-4 col-lg-3">Can See EEO Info:</div>
                                    <div class="col-12 col-sm">
                                        This user is <?php if ($this->data['canSeeEEOInfo'] == 0): ?>not <?php endif; ?>allowed to edit and view candidate's EEO information.
                                    </div>
                                </div>
                            <?php endif; ?>

                            <?php if (count($this->categories) > 0): ?>
                                <?php foreach ($this->categories as $category): ?>
                                    <?php if ($this->data['categories'] == $category[1]): ?>
                                        <div class="row g-2 mb-2">
                                            <div class="col-sm-4 col-lg-3">Role:</div>
                                            <div class="col-12 col-sm">
                                                <?php $this->_($category[0]); ?> - <?php $this->_($category[2]); ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>

                           <div class="row g-2 mb-2">
                                <div class="col-sm-4 col-lg-3">Last Successful Login:</div>
                                <div class="col-12 col-sm"><?php $this->_($this->data['successfulDate']); ?></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 col-lg-3">Last Failed Login:</div>
                                <div class="col-12 col-sm"><?php $this->_($this->data['unsuccessfulDate']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php if ($this->privledged): ?>
                <a class="btn btn-primary btn-sm" id="edit_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=editUser&amp;userID=<?php $this->_($this->data['userID']); ?>" title="Edit">
                    <img src="images/actions/edit.gif" class="absmiddle" style="border: none;" alt="edit user" />&nbsp;Edit
                </a>
            <?php else: ?>
                <input type="button" name="back" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings';"  class="btn btn-sm btn-outline-secondary" />
            <?php endif; ?>
            <br clear="all" />
            <br />

            <?php if ($this->privledged): ?>
                <p class="d-flex flex-wrap justify-content-between gap-2 bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Recent Logins Activity</p>
                <div class="table-responsive mb-2"><table class="sortable table table-sm table-striped align-middle">
                    <thead>
                        <tr>
                            <th>IP</th>
                            <th>Host Name</th>
                            <th>User Agent</th>
                            <th>Date</th>
                            <th>Successful</th>
                        </tr>
                    </thead>

                    <?php foreach ($this->loginAttempts as $rowNumber => $loginAttemptsData): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td><?php $this->_($loginAttemptsData['ip']); ?></td>
                            <td><?php $this->_($loginAttemptsData['hostname']); ?></td>
                            <td><?php $this->_($loginAttemptsData['shortUserAgent']); ?></td>
                            <td><?php $this->_($loginAttemptsData['date']); ?></td>
                            <td><?php $this->_($loginAttemptsData['successful']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table></div>
            <?php endif; ?>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
