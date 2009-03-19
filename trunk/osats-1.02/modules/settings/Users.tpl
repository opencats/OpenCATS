<?php /* $Id: Users.tpl 2452 2007-05-11 17:47:55Z brian $ */ ?>
<?php TemplateUtility::printHeader('Settings', 'js/sorttable.js'); ?>
<?php 
if (MYTABPOS == 'top') {
	osatutil::TabsAtTop();
	TemplateUtility::printTabs($this->active);
}
?>
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

            <table class="sortable" width="925">
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
                                <a href="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=showUser&amp;userID=<?php $this->_($data['userID']); ?>">
                                    <?php $this->_($data['firstName']); ?>
                                </a>
                            </td>
                            <td valign="top" align="left">
                                <a href="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=showUser&amp;userID=<?php $this->_($data['userID']); ?>">
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
            <a id="add_link" href="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=addUser" title="You have <?php $this->_($this->license['diff']); ?> user accounts remaining.">
                <img src="images/candidate_inline.gif" width="16" height="16" class="absmiddle" alt="add" style="border: none;" />&nbsp;Add User
            </a>
            <br /><br />&nbsp;<input type="button" name="back" class = "button" value="Back" onclick="document.location.href='<?php echo(osatutil::getIndexName()); ?>?m=settings';" />
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