<?php /* $Id: ShowUser.tpl 2881 2007-08-14 07:47:26Z brian $ */ ?>
<?php TemplateUtility::printHeader('Settings', 'js/sorttable.js'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" alt="Settings" style="border: none; margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: User Details</h2></td>
                </tr>
            </table>

            <p class="note">
                <?php /* Leave these separate; just one span makes the background image display weird. */ ?>
                <?php if ($this->privledged): ?>
                    <span style="float: left;">User Details</span>
                    <span style="float: right;"><a href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=manageUsers'>Back to User Management</a></span>&nbsp;
                <?php else: ?>
                    User Details
                <?php endif; ?>
            </p>

            <?php if (!$this->privledged): ?>
                <p>Contact your site administrator to change these settings.</p>
            <?php endif; ?>

            <table class="detailsOutside">
                <tr>
                    <td width="100%" height="100%">
                        <table class="detailsInside" height="100%">
                            <tr>
                                <td class="vertical" style="width: 135px;">Name:</td>
                                <td class="data">
                                    <span class="bold">
                                        <?php $this->_($this->data['firstName']); ?>
                                        <?php $this->_($this->data['lastName']); ?>
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical">E-Mail:</td>
                                <td class="data">
                                    <a href="mailto:<?php $this->_($this->data['email']); ?>">
                                        <?php $this->_($this->data['email']); ?>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical">Username:</td>
                                <td class="data"><?php $this->_($this->data['username']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Access Level:</td>
                                <td class="data"><?php $this->_($this->data['accessLevelLongDescription']); ?></td>
                            </tr>
                            
                            <?php if($this->EEOSettingsRS['enabled'] == 1): ?> <tr>
                                <td class="vertical">Can See EEO Info:</td>
                                    <td class="data">                                       
                                        This user is <?php if ($this->data['canSeeEEOInfo'] == 0): ?>not <?php endif; ?>allowed to edit and view candidate's EEO information.
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php if (count($this->categories) > 0): ?>
                                <?php foreach ($this->categories as $category): ?>
                                    <?php if ($this->data['categories'] == $category[1]): ?>
                                        <tr>
                                            <td class="vertical">Role:</td>
                                            <td class="data">
                                                <?php $this->_($category[0]); ?> - <?php $this->_($category[2]); ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>

                           <tr>
                                <td class="vertical">Last Successful Login:</td>
                                <td class="data"><?php $this->_($this->data['successfulDate']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Last Failed Login:</td>
                                <td class="data"><?php $this->_($this->data['unsuccessfulDate']); ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <?php if ($this->privledged): ?>
                <a id="edit_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=editUser&amp;userID=<?php $this->_($this->data['userID']); ?>" title="Edit">
                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" style="border: none;" alt="edit user" />&nbsp;Edit
                </a>
            <?php else: ?>
                <input type="button" name="back" class = "button" value="Back" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings';" />
            <?php endif; ?>
            <br clear="all" />
            <br />

            <?php if ($this->privledged): ?>
                <p class="note">Recent Logins Activity</p>
                <table class="sortable">
                    <thead>
                        <tr>
                            <th align="left" nowrap="nowrap">IP</th>
                            <th align="left" nowrap="nowrap">Host Name</th>
                            <th align="left" nowrap="nowrap">User Agent</th>
                            <th align="left">Date</th>
                            <th align="left" nowrap="nowrap">Successful</th>
                        </tr>
                    </thead>

                    <?php foreach ($this->loginAttempts as $rowNumber => $loginAttemptsData): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td><?php $this->_($loginAttemptsData['ip']); ?></td>
                            <td><?php $this->_($loginAttemptsData['hostname']); ?></td>
                            <td><?php $this->_($loginAttemptsData['shortUserAgent']); ?></td>
                            <td><?php $this->_($loginAttemptsData['date']); ?></td>
                            <td><?php $this->_($loginAttemptsData['successful']); ?></td>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
