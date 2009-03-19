<?php /* $Id: Show.tpl 3582 2007-11-12 22:58:48Z brian $ */ ?>
<?php TemplateUtility::printHeader(__('Company'). ' - '.$this->data['name'], array( 'js/sorttable.js', 'js/attachment.js')); ?>
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
                        <img src="images/companies.gif" width="24" height="24" border="0" alt="Companies" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php echo __('Companies').': '.__('Company Details');?></h2></td>
               </tr>
            </table>

            <p class="note"><?php _e('Company Details')?></p>

            <table class="detailsOutside" width="925">
                <tr style="vertical-align:top;">
                    <td width="50%" height="100%">
                        <table class="detailsInside" height="100%">
                            <tr>
                                <td class="vertical"><?php _e('Name')?>:</td>
                                <td class="data">
                                    <span class="<?php echo($this->data['titleClass']); ?>"><?php $this->_($this->data['name']); ?></span>
                                    <?php TemplateUtility::printSingleQuickActionMenu(DATA_ITEM_COMPANY, $this->companyID); ?>
                                </td>
                            </tr>

                            <!-- CONTACT INFO -->

                            <tr>
                                <td class="vertical"><?php _e('Primary Phone')?>:</td>
                                <td class="data"><?php $this->_($this->data['phone1']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Secondary Phone')?>:</td>
                                <td class="data"><?php $this->_($this->data['phone2']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Fax Number')?>:</td>
                                <td class="data"><?php $this->_($this->data['faxNumber']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Address')?>:</td>
                                <td class="data"><?php echo(nl2br(htmlspecialchars($this->data['address']))); ?>&nbsp;
                                <?php echo($this->data['googleMaps']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">&nbsp;</td>
                                <td class="data">
                                    <?php $this->_($this->data['cityAndState']); ?>
                                    <?php $this->_($this->data['zip']); ?>
                                </td>
                            </tr>

                            <?php for ($i = 0; $i < intval(count($this->extraFieldRS)/2); $i++): ?>
                               <tr>
                                    <td class="vertical"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</td>
                                    <td class="data"><?php echo($this->extraFieldRS[$i]['display']); ?></td>
                               </tr>
                            <?php endfor; ?>

                            <!-- /CONTACT INFO -->
                       </table>
                    </td>

                    <td width="50%" height="100%">
                        <table class="detailsInside" height="100%">
                        <!-- CONTACT INFO -->

                            <tr>
                                <td class="vertical"><?php _e('Billing Contact')?>:</td>
                                <td class="data">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php echo($this->data['billingContact']); ?>">
                                        <?php $this->_($this->data['billingContactFullName']); ?>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Web Site')?>:</td>
                                <td class="data">
                                    <a href="<?php $this->_($this->data['url']); ?>" target="_blank">
                                        <?php $this->_($this->data['url']); ?>
                                    </a>
                                </td>
                            </tr>

                        <!-- /CONTACT INFO -->

                            <tr>
                                <td class="vertical"><?php _e('Key Technologies')?>:</td>
                                <td class="data"><?php $this->_($this->data['keyTechnologies']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Created')?>:</td>
                                <td class="data"><?php $this->_($this->data['dateCreated']); ?> (<?php $this->_($this->data['enteredByFullName']); ?>)</td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Owner')?>:</td>
                                <td class="data"><?php $this->_($this->data['ownerFullName']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">&nbsp;</td>
                                <td class="data">&nbsp;</td>
                            </tr>

                        <!-- CONTACT INFO -->

                            <?php for ($i = (intval(count($this->extraFieldRS))/2); $i < (count($this->extraFieldRS)); $i++): ?>
                                <tr>
                                    <td class="vertical"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</td>
                                    <td class="data"><?php echo($this->extraFieldRS[$i]['display']); ?></td>                                </tr>
                            <?php endfor; ?>

                        <!-- /CONTACT INFO -->
                        </table>
                    </td>
                </tr>
            </table>

            <!-- CONTACT INFO -->
            <?php if (count($this->departmentsRS) > 0): ?>
                <table class="detailsOutside" width="925">
                    <tr>
                        <td>
                            <table class="detailsInside">
                                <tr>
                                    <td valign="top" class="vertical"><?php _e('Departments')?>:</td>
                                    <td valign="top" class="data">
                                        <?php foreach ($this->departmentsRS as $departmentRecord): ?>
                                            <?php $this->_($departmentRecord['name']); ?>
                                            <br />
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>
            <!-- /CONTACT INFO -->

            <!-- CONTACT INFO -->
            <table class="detailsOutside" width="925">
                <tr>
                    <td>
                        <table class="detailsInside">
                            <tr>
                                <td valign="top" class="vertical"><?php _e('Attachments')?>:</td>
                                <td valign="top" class="data">
                                    <table class="attachmentsTable">
                                        <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData): ?>
                                            <tr>
                                                <td>
                                                    <?php echo $attachmentsData['retrievalLink']; ?>
                                                        <img src="<?php $this->_($attachmentsData['attachmentIcon']) ?>" alt="" width="16" height="16" border="0" />
                                                        &nbsp;
                                                        <?php $this->_($attachmentsData['originalFilename']) ?>
                                                    </a>
                                                </td>
                                                <td><?php $this->_($attachmentsData['dateCreated']) ?></td>
                                                <td>
                                                    <?php if ($this->accessLevel >= ACCESS_LEVEL_DELETE): ?>
                                                        <a href="<?php echo(osatutil::getIndexName()); ?>?m=companies&amp;a=deleteAttachment&amp;companyID=<?php echo($this->companyID); ?>&amp;attachmentID=<?php $this->_($attachmentsData['attachmentID']) ?>"  title="<?php _e('Delete')?>" onclick="javascript:return confirm(<?php _e('Delete this attachment?')?>);">
                                                            <img src="images/actions/delete.gif" alt="" width="16" height="16" border="0" />
                                                        </a>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                    <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>
                                            <?php if (isset($this->attachmentLinkHTML)): ?>
                                                <?php echo($this->attachmentLinkHTML); ?>
                                            <?php else: ?>
                                                <a href="#" onclick="showPopWin('<?php echo(osatutil::getIndexName()); ?>?m=companies&amp;a=createAttachment&amp;companyID=<?php echo($this->companyID); ?>', 400, 125, null); return false;">
                                            <?php endif; ?>
                                            <img src="images/paperclip_add.gif" width="16" height="16" border="0" alt="add attachment" class="absmiddle" />&nbsp;<?php _e('Add Attachment')?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td valign="top" class="vertical"><?php _e('Misc. Notes')?>:</td>
                                <?php if ($this->isShortNotes): ?>
                                    <td id="shortNotes" style="display:block;" class="data">
                                        <?php echo($this->data['shortNotes']); ?><span class="moreText">...</span>&nbsp;
                                        <p><a href="#" class="moreText" onclick="toggleNotes(); return false;">[<?php _e('More')?>]</a></p>
                                    </td>
                                    <td id="fullNotes" style="display:none;" class="data">
                                        <?php echo($this->data['notes']); ?>&nbsp;
                                        <p><a href="#" class="moreText" onclick="toggleNotes(); return false;">[<?php _e('Less')?>]</a></p>
                                    </td>
                                <?php else: ?>
                                    <td id="shortNotes" style="display:block;" class="data">
                                        <?php echo($this->data['notes']); ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <!-- /CONTACT INFO -->

            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>
                <a id="edit_link" href="<?php echo(osatutil::getIndexName()); ?>?m=companies&amp;a=edit&amp;companyID=<?php echo($this->companyID); ?>">
                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />&nbsp;<?php _e('Edit')?>
                </a>
                &nbsp;&nbsp;&nbsp;&nbsp;
            <?php endif; ?>
            <?php if ($this->accessLevel >= ACCESS_LEVEL_DELETE && $this->data['defaultCompany'] != 1): ?>
                <a id="delete_link" href="<?php echo(osatutil::getIndexName()); ?>?m=companies&amp;a=delete&amp;companyID=<?php echo($this->companyID); ?>" onclick="javascript:return confirm('Delete this company?');">
                    <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;<?php _e('Delete')?>
                </a>
                &nbsp;&nbsp;&nbsp;&nbsp;
            <?php endif; ?>
            <?php if ($this->privledgedUser): ?>
                <a id="history_link" href="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=viewItemHistory&amp;dataItemType=200&amp;dataItemID=<?php echo($this->companyID); ?>">
                    <img src="images/icon_clock.gif" width="16" height="16" class="absmiddle"  border="0" />&nbsp;<?php _e('View History')?>
                </a>
                &nbsp;&nbsp;&nbsp;&nbsp;
            <?php endif; ?>
            <br clear="all" />
            <br />

            <p class="note"><?php _e('Job Orders')?></p>
            <table class="sortable" width="925">
                <tr>
                    <th align="left" width="30" nowrap="nowrap"><?php _e('ID')?></th>
                    <th align="left" width="200"><?php _e('Title')?></th>
                    <th align="left" width="15"><?php _e('Type')?></th>
                    <th align="left" width="15"><?php _e('Status')?></th>
                    <th align="left" width="60"><?php _e('Created')?></th>
                    <th align="left" width="60"><?php _e('Modified')?></th>
                    <th align="left" width="60"><?php _e('Start')?></th>
                    <th align="left" width="15"><?php _e('Age')?></th>
                    <th align="left" width="10"><?php _e('S')?></th>
                    <th align="left" width="10"><?php _e('P')?></th>
                    <th align="left" width="65"><?php _e('Recruiter')?></th>
                    <th align="left" width="68"><?php _e('Owner')?></th>
                    <th align="left" width="25"><?php _e('Action')?></th>
                </tr>

                <?php foreach ($this->jobOrdersRS as $rowNumber => $jobOrdersData): ?>
                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['jobOrderID']) ?></td>
                        <td valign="top">
                            <a href="<?php echo(osatutil::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php $this->_($jobOrdersData['jobOrderID']) ?>">
                                <?php $this->_($jobOrdersData['title']) ?>
                            </a>
                        </td>
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['type']) ?></td>
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['status']) ?></td>
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['dateCreated']) ?></td>
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['dateModified']) ?></td>
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['startDate']) ?></td>
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['daysOld']) ?></td>
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['submitted']); ?></td>
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['pipeline']); ?></td>
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['recruiterAbbrName']); ?></td>
                        <td valign="top" align="left"><?php $this->_($jobOrdersData['ownerAbbrName']); ?></td>
                        <td valign="top" align="center">
                            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>
                                <a href="<?php echo(osatutil::getIndexName()); ?>?m=joborders&amp;a=edit&amp;jobOrderID=<?php $this->_($jobOrdersData['jobOrderID']) ?>">
                                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>

            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>
                <a href="<?php echo(osatutil::getIndexName()); ?>?m=joborders&amp;a=add&amp;selected_company_id=<?php echo($this->companyID); ?>" title="Add Job Order">
                    <img src="images/actions/job_order.gif" width="16" height="16" class="absmiddle" alt="New Job Order" border="0" />&nbsp;<?php _e('Add Job Order')?>
                </a>
            <?php endif; ?>
            <br clear="all" />
            <br />

            <!-- CONTACT INFO -->
            <p class="note"><?php _e('Contacts')?></p>
            <table class="sortable" width="925">
                <tr>
                    <th align="left" nowrap="nowrap"><?php _e('First Name')?></th>
                    <th align="left" nowrap="nowrap"><?php _e('Last Name')?></th>
                    <th align="left"><?php _e('Title')?></th>
                    <th align="left"><?php _e('Department')?></th>
                    <th align="left" nowrap="nowrap"><?php _e('Work Phone')?></th>
                    <th align="left" nowrap="nowrap"><?php _e('Cell Phone')?></th>
                    <th align="left"><?php _e('Created')?></th>
                    <th align="left"><?php _e('Owner')?></th>
                    <th align="center"><?php _e('Action')?></th>
                </tr>

                <?php if (count($this->contactsRSWC) != 0): ?>
                 <?php foreach ($this->contactsRSWC as $rowNumber => $contactsData): ?>
                    <tr id="ContactsDefault<?php echo($rowNumber) ?>" class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                        <td valign="top" align="left">
                            <a href="<?php echo(osatutil::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php $this->_($contactsData['contactID']) ?>" class="<?php $this->_($contactsData['linkClass']); ?>">
                                <?php $this->_($contactsData['firstName']) ?>
                            </a>
                        </td>
                        <td valign="top" align="left">
                            <a href="<?php echo(osatutil::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php $this->_($contactsData['contactID']) ?>" class="<?php $this->_($contactsData['linkClass']); ?>">
                                <?php $this->_($contactsData['lastName']) ?>
                            </a>
                        </td>
                        <td valign="top" align="left"><?php $this->_($contactsData['title']) ?></td>
                        <td valign="top" align="left"><?php $this->_($contactsData['department']) ?></td>
                        <td valign="top" align="left"><?php $this->_($contactsData['phoneWork']) ?></td>
                        <td valign="top" align="left"><?php $this->_($contactsData['phoneCell']) ?></td>
                        <td valign="top" align="left"><?php $this->_($contactsData['dateCreated']) ?></td>
                        <td valign="top" align="left"><?php $this->_($contactsData['ownerAbbrName']); ?></td>
                        <td valign="top" align="center">
                            <?php if (!empty($contactsData['email1'])): ?>
                                <a href="mailto:<?php $this->_($contactsData['email1']); ?>" title="Send E-Mail (<?php $this->_($contactsData['email1']); ?>)">
                                    <img src="images/actions/email.gif" width="16" height="16" alt="" class="absmiddle" border="0" />
                                </a>
                            <?php else: ?>
                                <img src="images/actions/email_no.gif" title="No E-Mail Address" width="16" height="16" alt="" class="absmiddle" border="0" />
                            <?php endif; ?>
                            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>
                                <a href="<?php echo(osatutil::getIndexName()); ?>?m=contacts&amp;a=edit&amp;contactID=<?php $this->_($contactsData['contactID']) ?>">
                                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
               <?php endif; ?>

                <?php /* The following are hidden by default */ ?>
                <?php if (count($this->contactsRSWC) != count($this->contactsRS) && count($this->contactsRS) != 0) : ?>
                 <?php foreach ($this->contactsRS as $rowNumber => $contactsData): ?>
                    <tr id="ContactsFull<?php echo($rowNumber) ?>" class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>" style="display:none;">
                        <td valign="top" align="left">
                            <a href="<?php echo(osatutil::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php $this->_($contactsData['contactID']) ?>" class="<?php $this->_($contactsData['linkClass']); ?>">
                                <?php $this->_($contactsData['firstName']) ?>
                            </a>
                        </td>
                        <td valign="top" align="left">
                            <a href="<?php echo(osatutil::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php $this->_($contactsData['contactID']) ?>" class="<?php $this->_($contactsData['linkClass']); ?>">
                                <?php $this->_($contactsData['lastName']) ?>
                            </a>
                        </td>
                        <td valign="top" align="left"><?php $this->_($contactsData['title']) ?></td>
                        <td valign="top" align="left"><?php $this->_($contactsData['department']) ?></td>
                        <td valign="top" align="left"><?php $this->_($contactsData['phoneWork']) ?></td>
                        <td valign="top" align="left"><?php $this->_($contactsData['phoneCell']) ?></td>
                        <td valign="top" align="left"><?php $this->_($contactsData['dateCreated']) ?></td>
                        <td valign="top" align="left"><?php $this->_($contactsData['ownerAbbrName']); ?></td>
                        <td valign="top" align="center">
                            <?php if (!empty($contactsData['email1'])): ?>
                                <a href="mailto:<?php $this->_($contactsData['email1']); ?>">
                                    <img src="images/actions/email.gif" width="16" height="16" alt="" class="absmiddle" border="0" title="Send E-Mail (<?php $this->_($contactsData['email1']); ?>)"/>
                                </a>
                            <?php else: ?>
                                <img src="images/actions/email_no.gif" title="No E-Mail Address" width="16" height="16" alt="" class="absmiddle" border="0" />
                            <?php endif; ?>
                            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>
                                <a href="<?php echo(osatutil::getIndexName()); ?>?m=contacts&amp;a=edit&amp;contactID=<?php $this->_($contactsData['contactID']) ?>">
                                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />
                                </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                 <?php endforeach; ?>
                <?php endif; ?>

            </table>

            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>
                <a href="<?php echo(osatutil::getIndexName()); ?>?m=contacts&amp;a=add&amp;selected_company_id=<?php echo($this->companyID); ?>" title="Add Contact">
                    <img src="images/actions/add_contact.gif" width="16" height="16" class="absmiddle" alt="add contact" border="0" title="Add Contact"/>&nbsp;<?php _e('Add Contact')?>
                </a>
            <?php endif; ?>
            <?php if (count($this->contactsRSWC) != count($this->contactsRS)) : ?>
                &nbsp;
                <a href="javascript:void(0)" id="linkShowAll" onclick="javascript:for (i = 0; i< <?php echo(count($this->contactsRSWC)); ?>; i++) document.getElementById('ContactsDefault'+i).style.display='none'; for (i = 0; i< <?php echo(count($this->contactsRS)); ?>; i++) document.getElementById('ContactsFull'+i).style.display=''; document.getElementById('linkShowAll').style.display='none'; document.getElementById('linkHideSome').style.display='';">
                    <img src="images/actions/add_contact.gif" width="16" height="16" class="absmiddle" alt="add contact" border="0" title="Show All"/>
                    &nbsp;<?php _e('Show contacts who have left')?> (<?php echo(count($this->contactsRS) - count($this->contactsRSWC)); ?>)
                </a>
                <a href="javascript:void(0)" id="linkHideSome" style="display:none;" onclick="javascript:for (i = 0; i< <?php echo(count($this->contactsRSWC)); ?>; i++) document.getElementById('ContactsDefault'+i).style.display=''; for (i = 0; i< <?php echo(count($this->contactsRS)); ?>; i++) document.getElementById('ContactsFull'+i).style.display='none'; document.getElementById('linkShowAll').style.display=''; document.getElementById('linkHideSome').style.display='none';">
                    <img src="images/actions/add_contact.gif" width="16" height="16" class="absmiddle" alt="add contact" border="0" title="Hide Some"/>
                    &nbsp;<?php _e('Hide contacts who have left')?> (<?php echo(count($this->contactsRS) - count($this->contactsRSWC)); ?>)
                </a>
            <?php endif; ?>
            <!-- /CONTACT INFO -->
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
