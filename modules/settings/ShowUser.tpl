<?php /* $Id: ShowUser.tpl 2881 2007-08-14 07:47:26Z brian $ */ ?>
<?php TemplateUtility::printHeader(__('Settings'), 'js/sorttable.js'); ?>
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
                    <td><h2><?php echo __("Settings");?>: <?php echo __("User Details");?></h2></td>
                </tr>
            </table>

            <p class="note">
                <?php /* Leave these separate; just one span makes the background image display weird. */ ?>
                <?php if ($this->privledged): ?>
                    <span style="float: left;"><?php echo __("User Details");?></span>
                    <span style="float: right;"><a href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=manageUsers'><?php echo __("Back to User Management");?></a></span>&nbsp;
                <?php else: ?>
                    <?php echo __("User Details");?>
                <?php endif; ?>
            </p>

            <?php if (!$this->privledged): ?>
                <p><?php echo __("Contact your site administrator to change these settings.");?></p>
            <?php endif; ?>

            <table class="detailsOutside">
                <tr>
                    <td width="100%" height="100%">
                        <table class="detailsInside" height="100%">
                            <tr>
                                <td class="vertical" style="width: 135px;"><?php echo __("Name");?>:</td>
                                <td class="data">
                                    <span class="bold">
                                        <?php $this->_($this->data['firstName']); ?>
                                        <?php $this->_($this->data['lastName']); ?>
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("E-Mail");?>:</td>
                                <td class="data">
                                    <a href="mailto:<?php $this->_($this->data['email']); ?>">
                                        <?php $this->_($this->data['email']); ?>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Username");?>:</td>
                                <td class="data"><?php $this->_($this->data['username']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Access Level");?>:</td>
                                <td class="data"><?php $this->_($this->data['accessLevelLongDescription']); ?></td>
                            </tr>
                            
                            <?php if($this->EEOSettingsRS['enabled'] == 1): ?> <tr>
                                <td class="vertical"><?php echo __("Can See EEO Info");?>:</td>
                                    <td class="data">                                       
                                        <?php echo sprintf(__("This user is %s allowed to edit and view candidate's EEO information."),($this->data['canSeeEEOInfo'] == 0)?'not':'');?>
                                    </td>
                                </tr>
                            <?php endif; ?>

                            <?php if (count($this->categories) > 0): ?>
                                <?php foreach ($this->categories as $category): ?>
                                    <?php if ($this->data['categories'] == $category[1]): ?>
                                        <tr>
                                            <td class="vertical"><?php echo __("Role");?>:</td>
                                            <td class="data">
                                                <?php $this->_($category[0]); ?> - <?php $this->_($category[2]); ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>

                           <tr>
                                <td class="vertical"><?php echo __("Last Successful Login");?>:</td>
                                <td class="data"><?php $this->_($this->data['successfulDate']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Last Failed Login");?>:</td>
                                <td class="data"><?php $this->_($this->data['unsuccessfulDate']); ?></td>
                            </tr>
                            <tr >
                            	<td colspan ="2" width="100%">
                         <?php 
                    		E::showCustomFields(array(
                    			'dataItem'=>'user',
                    			'section'=>'custom1',
                    			'template'=>'read',
                    			'fl'=>$this->fl,
                    			)); 
                    	?>                              	
                            	</td>
                            </tr>
                            
                        </table>
                        
                     

                    </td>
                </tr>
            </table>
            
            <form name="editUserProfileForm" id="editUserProfileForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=showUser&userID=<?php echo  $this->fl['id'];?>&privledged=false" method="post" autocomplete="off"> <!-- onsubmit="return checkEditUserForm(document.editUserForm);" -->                       
            <input type="hidden" name="fs[id]" id="fs_id" value="<?php echo $this->fl['id'];?>" />
            <table class="detailsOutside">
                <tr>

                    <td width="50%" height="100%">            
                        <?php 
                    		E::showCustomFields(array(
                    			'dataItem'=>'user',
                    			'section'=>'editable1',
                    			'template'=>'edit',
                    			'fl'=>$this->fl,
                    			)); 
                    	?>
                    </td> 
                    <td width="30%" height="100%" valign="top">            
						<input type="submit" class="button" name="submit" id="submit" value="Zapisz" />&nbsp;
                    </td>                                       
                </tr>
            </table> 
            </form>                 	
                    	            
            <?php if ($this->privledged): ?>
                <a id="edit_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=editUser&amp;userID=<?php $this->_($this->data['userID']); ?>" title="<?php echo __("Edit");?>">
                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" style="border: none;" alt="edit user" />&nbsp;<?php echo __("Edit");?>
                </a>
            <?php else: ?>
                <input type="button" name="back" class = "button" value="<?php echo __("Back");?>" onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings';" />
            <?php endif; ?>
            <br clear="all" />
            <br />

            <?php if ($this->privledged): ?>
                <p class="note"><?php echo __("Recent Logins Activity");?></p>
                <table class="sortable">
                    <thead>
                        <tr>
                            <th align="left" nowrap="nowrap"><?php echo __("IP");?></th>
                            <th align="left" nowrap="nowrap"><?php echo __("Host Name");?></th>
                            <th align="left" nowrap="nowrap"><?php echo __("User Agent");?></th>
                            <th align="left"><?php echo __("Date");?></th>
                            <th align="left" nowrap="nowrap"><?php echo __("Successful");?></th>
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
