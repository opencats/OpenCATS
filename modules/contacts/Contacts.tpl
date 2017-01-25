<?php /* $Id: Contacts.tpl 3430 2007-11-06 20:44:51Z will $ */ ?>
<?php TemplateUtility::printHeader('Contacts', array('js/highlightrows.js', 'js/export.js', 'js/dataGrid.js', 'js/dataGridFilters.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <style type="text/css">
    div.addContactsButton { background: #4172E3 url(images/nodata/contactsButton.jpg); cursor: pointer; width: 337px; height: 67px; }
    div.addContactsButton:hover { background: #4172E3 url(images/nodata/contactsButton-o.jpg); cursor: pointer; width: 337px; height: 67px; }
    </style>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents"<?php echo !$this->totalContacts ? ' style="background-color: #E6EEFF; padding: 0px;"' : ''; ?>>
            <?php if ($this->totalContacts): ?>
            <table width="100%">
                <tr>
                    <td width="3%">
                        <img src="images/contact.gif" width="24" height="24" border="0" alt="Contacts" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Contacts: Home</h2></td>
                    <td align="right">
                        <form name="contactsViewSelectorForm" id="contactsViewSelectorForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get">
                            <input type="hidden" name="m" value="contacts" />
                            <input type="hidden" name="a" value="listByView" />

                            <table class="viewSelector">
                                <tr>
                                    <td valign="top" align="right" nowrap="nowrap">
                                        <?php $this->dataGrid->printNavigation(false); ?>
                                    </td>
                                    <td valign="top" align="right" nowrap="nowrap">
                                        <input type="checkbox" name="onlyMyCompanies" id="onlyMyContacts" <?php if ($this->dataGrid->getFilterValue('OwnerID') ==  $this->userID): ?>checked<?php endif; ?> onclick="<?php echo $this->dataGrid->getJSAddRemoveFilterFromCheckbox('OwnerID', '==',  $this->userID); ?>" />
                                        <label for="onlyMyContacts">Only My Contacts</label>&nbsp;
                                    </td>
                                    <td valign="top" align="right" nowrap="nowrap">
                                        <input type="checkbox" name="onlyHotCompanies" id="onlyHotContacts" <?php if ($this->dataGrid->getFilterValue('IsHot') == '1'): ?>checked<?php endif; ?> onclick="<?php echo $this->dataGrid->getJSAddRemoveFilterFromCheckbox('IsHot', '==', '\'1\''); ?>" />
                                        <label for="onlyHotContacts">Only Hot Contacts</label>&nbsp;
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </td>
                </tr>
            </table>

            <?php if ($this->errMessage != ''): ?>
            <div id="errorMessage" style="padding: 25px 0px 25px 0px; border-top: 1px solid #800000; border-bottom: 1px solid #800000; background-color: #f7f7f7;margin-bottom: 15px;">
            <table>
                <tr>
                    <td align="left" valign="center" style="padding-right: 5px;">
                        <img src="images/large_error.gif" align="left">
                    </td>
                    <td align="left" valign="center">
                        <span style="font-size: 12pt; font-weight: bold; color: #800000; line-height: 12pt;">There was a problem with your request:</span>
                        <div style="font-size: 10pt; font-weight: bold; padding: 3px 0px 0px 0px;"><?php echo $this->errMessage; ?></div>
                    </td>
                </tr>
            </table>
            </div>
            <?php endif; ?>

            <p class="note">
                <span style="float:left;">
                    Contacts - Page <?php echo($this->dataGrid->getCurrentPageHTML()); ?>
                    (<?php echo($this->dataGrid->getNumberOfRows()); ?> Items)
                    <?php if ($this->dataGrid->getFilterValue('OwnerID') ==  $this->userID): ?>(Only My Contacts)<?php endif; ?>
                    <?php if ($this->dataGrid->getFilterValue('IsHot') == '1'): ?>(Only Hot Contacts)<?php endif; ?>
                </span>
                <span style="float:right;">
                    <?php $this->dataGrid->drawRowsPerPageSelector(); ?>
                    <?php $this->dataGrid->drawShowFilterControl(); ?>
                </span>&nbsp;
            </p>

            <?php $this->dataGrid->drawFilterArea(); ?>
            <?php $this->dataGrid->draw();  ?>

            <div style="display:block;">
                <span style="float:left;">
                    <?php $this->dataGrid->printActionArea(); ?>
                </span>
                <span style="float:right;">
                    <?php $this->dataGrid->printNavigation(true); ?>
                </span>&nbsp;
            </div>

            <?php else: ?>

            <br /><br /><br /><br />
            <div style="height: 95px; background: #E6EEFF url(images/nodata/contactsTop.jpg);">
                &nbsp;
            </div>
            <br /><br />
                <?php if ($this->getUserAccessLevel('contacts.add') >= ACCESS_LEVEL_EDIT): ?>
            <table cellpadding="0" cellspacing="0" border="0" width="956">
                <tr>
                <td style="padding-left: 62px;" align="center" valign="center">

                    <div style="text-align: center; width: 600px; line-height: 22px; font-size: 18px; font-weight: bold; color: #666666; padding-bottom: 20px;">
                    Add contacts to keep track of people you work with.
                    </div>

                    <a href="<?php echo CATSUtility::getIndexName(); ?>?m=contacts&amp;a=add">
                    <div class="addContactsButton">&nbsp;</div>
                    </a>
                </td>

                </tr>
            </table>
                <?php endif; ?>

            <?php endif; ?>

        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
