<?php /* $Id: ColdCallList.tpl 1948 2007-02-23 09:49:27Z will $ */ ?>
<?php TemplateUtility::printHeader(__('Contacts'), array('js/sorttable.js', 'js/highlightrows.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>
        
        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/contact.gif" width="24" height="24" border="0" alt="Contacts" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php echo __("Contacts");?>: <?php echo __("Cold Call List");?></h2></td>
                </tr>
            </table>
        
            <p class="note"><?php echo __("Cold Call List");?> (<?php echo __("Only Contacts with Phone Numbers");?>)</p>
        
            <?php if (!empty($this->rs)): ?>
                <table class="sortable" rules="all" onmouseover="javascript:trackTableHighlight(event)">
                    <tr>
                        <th align="left"><?php echo __("Company");?></th>
                        <th align="left" nowrap="nowrap"><?php echo __("First Name");?></th>
                        <th align="left" nowrap="nowrap"><?php echo __("Last Name");?></th>
                        <th align="left"><?php echo __("Title");?></th>
                        <th align="left" nowrap="nowrap"><?php echo __("Work Phone");?></th>
                    </tr>
        
                    <?php foreach ($this->rs as $rowNumber => $data): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td valign="top" align="left"><?php $this->_($data['companyName']); ?></td>
                            <td valign="top" align="left"><?php $this->_($data['firstName']); ?></td>
                            <td valign="top" align="left"><?php $this->_($data['lastName']); ?></td>
                            <td valign="top" align="left"><?php $this->_($data['title']); ?></td>
                            <td valign="top" align="left"><?php $this->_($data['phoneWork']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
