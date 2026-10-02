<?php TemplateUtility::printHeader('Settings'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: Login Activity</h1>
            </header>

            <p class="bg-secondary-subtle rounded p-2 mb-2 fw-semibold">Recent Login Activity</p>

            <form name="loginActivityViewSelectorForm" id="loginActivityViewSelectorForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get">
                <input type="hidden" name="m" value="settings" />
                <input type="hidden" name="a" value="loginActivity" />

                <div class="card card-body p-2 mb-2">
                    <div class="row g-2 mb-2">
                        <div class="col-12 col-sm">
                            <label class="form-label small" for="view">Login result</label>
                            <select name="view" id="view" onchange="document.loginActivityViewSelectorForm.submit();" class="form-select form-select-sm">
                                <?php if ($this->view == 'successful'): ?>
                                    <option value="successful" selected="selected">Successful Logins</option>
                                    <option value="unsuccessful">Unsuccessful Logins</option>
                                <?php elseif ($this->view == 'unsuccessful'): ?>
                                    <option value="successful">Successful Logins</option>
                                    <option value="unsuccessful" selected="selected">Unsuccessful Logins</option>
                                <?php else: ?>
                                    <option value="successful">Successful Logins</option>
                                    <option value="unsuccessful">Unsuccessful Logins</option>
                                <?php endif; ?>
                            </select>
                            <!--&nbsp;&nbsp;&nbsp;&nbsp;
                            Login activity older than 1 month plus 100 entries in the past is automatically cleared from the system.-->
                        </div>
                    </div>
                </div>
            </form>

            <?php if (!empty($this->rs)): ?>
                <div class="table-responsive"><table class="sortable table table-sm table-striped align-middle">
                    <thead>
                        <tr>
                            <th>
                                <?php $this->pager->printSortLink('firstName', 'First Name'); ?>
                            </th>
                            <th>
                                <?php $this->pager->printSortLink('lastName', 'Last Name'); ?>
                            </th>
                            <th>
                                <?php $this->pager->printSortLink('ip', 'IP'); ?>
                            </th>
                            <th>
                                <?php $this->pager->printSortLink('hostname', 'Hostname'); ?>
                            </th>
                            <th>
                                <?php $this->pager->printSortLink('shortUserAgent', 'User Agent'); ?>
                            </th>
                            <th>
                                <?php $this->pager->printSortLink('dateSort', 'Date / Time'); ?>
                            </th>
                        </tr>
                    </thead>

                    <?php foreach ($this->rs as $rowNumber => $data): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=showUser&userID=<?php $this->_($data['userID']); ?>">
                                    <?php $this->_($data['firstName']); ?>
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=showUser&userID=<?php $this->_($data['userID']); ?>">
                                    <?php $this->_($data['lastName']); ?>
                                </a>
                            </td>
                            <td><?php $this->_($data['ip']); ?></td>
                            <td><?php $this->_($data['hostname']); ?></td>
                            <td><?php $this->_($data['shortUserAgent']); ?></td>
                            <td><?php $this->_($data['date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table></div>
                <?php $this->pager->printNavigation('', true, 20); ?>
            <?php endif; ?>
        </div>
    </main>
<?php TemplateUtility::printFooter(); ?>
