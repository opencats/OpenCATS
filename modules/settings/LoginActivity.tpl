<?php /* $Id: LoginActivity.tpl 2452 2007-05-11 17:47:55Z brian $ */ ?>
<?php TemplateUtility::printHeader('Settings'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: Login Activity</h2></td>
                </tr>
            </table>

            <p class="note">Recent Login Activity</p>

            <form name="loginActivityViewSelectorForm" id="loginActivityViewSelectorForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get">
                <input type="hidden" name="m" value="settings" />
                <input type="hidden" name="a" value="loginActivity" />

                <table class="viewSelector">
                    <tr>
                        <td>
                            <select name="view" id="view" onchange="document.loginActivityViewSelectorForm.submit();">
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
                        </td>
                    </tr>
                </table>
            </form>

            <?php if (!empty($this->rs)): ?>
                <table class="sortable">
                    <thead>
                        <tr>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('firstName', 'First Name'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('lastName', 'Last Name'); ?>
                            </th>
                            <th align="left">
                                <?php $this->pager->printSortLink('ip', 'IP'); ?>
                            </th>
                            <th align="left">
                                <?php $this->pager->printSortLink('hostname', 'Hostname'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('shortUserAgent', 'User Agent'); ?>
                            </th>
                            <th align="left" nowrap="nowrap">
                                <?php $this->pager->printSortLink('dateSort', 'Date / Time'); ?>
                            </th>
                        </tr>
                    </thead>

                    <?php foreach ($this->rs as $rowNumber => $data): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td valign="top" align="left">
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=showUser&userID=<?php $this->_($data['userID']); ?>">
                                    <?php $this->_($data['firstName']); ?>
                                </a>
                            </td>
                            <td valign="top" align="left">
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&a=showUser&userID=<?php $this->_($data['userID']); ?>">
                                    <?php $this->_($data['lastName']); ?>
                                </a>
                            </td>
                            <td valign="top" align="left"><?php $this->_($data['ip']); ?></td>
                            <td valign="top" align="left"><?php $this->_($data['hostname']); ?></td>
                            <td valign="top" align="left"><?php $this->_($data['shortUserAgent']); ?></td>
                            <td valign="top" align="left" nowrap="nowrap"><?php $this->_($data['date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
                <?php $this->pager->printNavigation('', true, 20); ?>
            <?php endif; ?>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
