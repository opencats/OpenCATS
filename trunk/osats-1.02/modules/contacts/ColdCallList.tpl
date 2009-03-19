<?php /* $Id: ColdCallList.tpl 1948 2007-02-23 09:49:27Z will $ */ ?>
<?php TemplateUtility::printHeader(__('Contacts'), array('js/sorttable.js', 'js/highlightrows.js')); ?>
<?php 
if (MYTABPOS == 'top') {
	osatutil::TabsAtTop();
	TemplateUtility::printTabs($this->active);
}
?>
    <table>
        <tr>
            <td width="3%">
                <img src="images/contact.gif" width="24" height="24" border="0" alt="Contacts" style="margin-top: 3px;" />&nbsp;
            </td>
            <td><h2><?php _e('Contacts')?>: <?php _e('Cold Call List')?></h2></td>
        </tr>
    </table>

    <p class="note"><?php _e('Cold Call List')?> (<?php _e('Only Contacts with Phone Numbers')?>)</p>

    <?php if (!empty($this->rs)): ?>
        <table class="sortable" width="925" rules="all" onmouseover="javascript:trackTableHighlight(event)">
            <tr>
                <th align="left"><?php _e('Company')?></th>
                <th align="left" nowrap="nowrap"><?php _e('First Name')?></th>
                <th align="left" nowrap="nowrap"><?php _e('Last Name')?></th>
                <th align="left"><?php _e('Title')?></th>
                <th align="left" nowrap="nowrap"><?php _e('Work Phone')?></th>
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