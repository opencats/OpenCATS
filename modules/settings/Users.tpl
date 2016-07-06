<?php /* $Id: Users.tpl 2452 2007-05-11 17:47:55Z brian $ */ ?>
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
                    <td><h2>Settings: User Management</h2></td>
                </tr>
            </table>

            <p class="note">User Management</p>

            <table class="sortable">
                <thead>
                    <tr>
                        <th align="left" nowrap="nowrap">First Name</th>
                        <th align="left" nowrap="nowrap">Last Name</th>
                        <th align="left">Username</th>
                        <th align="left" nowrap="nowrap">Access Level</th>
                        <th align="left" nowrap="nowrap">Last Success</th>
                        <th align="left" nowrap="nowrap">Last Fail</th>
                    </tr>
                </thead>

                <?php if (!empty($this->rs)): ?>
                    <?php foreach ($this->rs as $rowNumber => $data): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td valign="top" align="left">
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=showUser&amp;userID=<?php $this->_($data['userID']); ?>">
                                    <?php $this->_($data['firstName']); ?>
                                </a>
                            </td>
                            <td valign="top" align="left">
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=showUser&amp;userID=<?php $this->_($data['userID']); ?>">
                                    <?php $this->_($data['lastName']); ?>
                                </a>
                            </td>
                            <td valign="top" align="left"><?php $this->_($data['username']); ?></td>
                            <td valign="top" align="left"><?php $this->_($data['accessLevelDescription']); ?></td>
                            <td valign="top" align="left"><?php $this->_($data['successfulDate']); ?></td>
                            <td valign="top" align="left"><?php $this->_($data['unsuccessfulDate']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </table>
            <?php if (AUTH_MODE != "ldap"): ?>
                <a id="add_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=addUser" title="You have <?php $this->_($this->license['diff']); ?> user accounts remaining.">
                    <img src="images/candidate_inline.gif" width="16" height="16" class="absmiddle" alt="add" style="border: none;" />&nbsp;Add User
                </a>
            <?php endif; ?>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
