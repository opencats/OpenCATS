<?php /* $Id: Show.tpl 3814 2007-12-06 17:54:28Z brian $ */ ?>
<?php if ($this->isPopup): ?>
    <?php TemplateUtility::printHeader(__('Job Order'). ' - '.$this->data['title'], array('js/sorttable.js', 'js/match.js', 'js/pipeline.js', 'js/attachment.js')); ?>
<?php else: ?>
    <?php TemplateUtility::printHeader(__('Job Order'). ' - '.$this->data['title'], array( 'js/sorttable.js', 'js/match.js', 'js/pipeline.js', 'js/attachment.js')); ?>
<?php 
if (MYTABPOS == 'top') {
	osatutil::TabsAtTop();
	TemplateUtility::printTabs($this->active);
}
?>
        <div id="main">
            <?php TemplateUtility::printQuickSearch(); ?>
<?php endif; ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/job_orders.gif" width="24" height="24" border="0" alt="Job Orders" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php echo __('Job Orders').': '.__('Job Order Details')?></h2></td>
                </tr>
            </table>

            <p class="note"><?php _e('Job Order Details') ?></p>

            <?php if ($this->data['isAdminHidden'] == 1): ?>
                <p class="warning"><?php _e('This Job Order is hidden. Only OSATS Administrators can view it or search for it. To make it visible by the site users') ?> click <a href="<?php echo(osatutil::getIndexName()); ?>?m=joborders&amp;a=administrativeHideShow&amp;jobOrderID=<?php echo($this->jobOrderID); ?>&amp;state=0" style="font-weight:bold;"><?php _e('Click here.') ?></a></p>
            <?php endif; ?>

            <?php if (isset($this->frozen)): ?>
                <table style="font-weight:bold; border: 1px solid #000; background-color: #ffed1a; padding:5px; margin-bottom:7px;" width="<?php /* if ($this->isModal): */ if(false): ?>100%<?php else: ?>925<?php endif; ?>" id="candidateAlreadyInSystemTable">
                    <tr>
                        <td class="tdVertical" style="width:925px;">
                            <?php _e('This Job Order is %s and can not be modified.', $this->_($this->data['status'])) ?>
                           <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>
                               <a id="edit_link" href="<?php echo(osatutil::getIndexName()); ?>?m=joborders&amp;a=edit&amp;jobOrderID=<?php echo($this->jobOrderID); ?>">
                                   <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />&nbsp;<?php _e('Edit the Job Order to make it Active.') ?>
                               </a>
                               &nbsp;&nbsp;
                           <?php endif; ?>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>

            <table class="detailsOutside" width="925" height="<?php echo((count($this->extraFieldRS)/2 + 12) * 22); ?>">
                <tr style="vertical-align:top;">
                    <td width="50%" height="100%">
                        <table class="detailsInside" height="100%">
                            <tr>
                                <td class="vertical"><?php _e('Title') ?>:</td>
                                <td class="data" width="300">
                                    <span class="<?php echo($this->data['titleClass']); ?>"><?php $this->_($this->data['title']); ?></span>
                                    <?php echo($this->data['public']) ?>
                                    <?php TemplateUtility::printSingleQuickActionMenu(DATA_ITEM_JOBORDER, $this->data['jobOrderID']); ?>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Company Name') ?>:</td>
                                <td class="data">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php echo($this->data['companyID']); ?>">
                                        <?php echo($this->data['companyName']); ?>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Department') ?>:</td>
                                <td class="data">
                                    <?php echo($this->data['department']); ?>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('OSATS Job ID') ?>:</td>
                                <td class="data" width="300"><?php $this->_($this->data['jobOrderID']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Company Job ID') ?>:</td>
                                <td class="data"><?php echo($this->data['companyJobID']); ?></td>
                            </tr>

                            <!-- CONTACT INFO -->
                            <tr>
                                <td class="vertical"><?php _e('Contact Name') ?>:</td>
                                <td class="data">
                                    <a href="<?php echo(osatutil::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php echo($this->data['contactID']); ?>">
                                        <?php echo($this->data['contactFullName']); ?>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Contact Phone') ?>:</td>
                                <td class="data"><?php echo($this->data['contactWorkPhone']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Contact Email') ?>:</td>
                                <td class="data">
                                    <a href="mailto:<?php $this->_($this->data['contactEmail']); ?>"><?php $this->_($this->data['contactEmail']); ?></a>
                                </td>
                            </tr>
                            <!-- /CONTACT INFO -->

                            <tr>
                                <td class="vertical"><?php _e('Location') ?>:</td>
                                <td class="data"><?php $this->_($this->data['cityAndState']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Max Rate') ?>:</td>
                                <td class="data"><?php $this->_($this->data['maxRate']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Salary') ?>:</td>
                                <td class="data"><?php $this->_($this->data['salary']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Start Date') ?>:</td>
                                <td class="data"><?php $this->_($this->data['startDate']); ?></td>
                            </tr>

                            <?php for ($i = 0; $i < intval(count($this->extraFieldRS)/2); $i++): ?>
                               <tr>
                                    <td class="vertical"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</td>
                                    <td class="data"><?php echo($this->extraFieldRS[$i]['display']); ?></td>
                              </tr>
                            <?php endfor; ?>

                            <?php eval(Hooks::get('JO_TEMPLATE_SHOW_BOTTOM_OF_LEFT')); ?>

                        </table>
                    </td>

                    <td width="50%" height="100%" style="vertical-align:top;" >
                        <table class="detailsInside" height="100%">
                            <tr>
                                <td class="vertical"><?php _e('Duration') ?>:</td>
                                <td class="data"><?php $this->_($this->data['duration']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Openings') ?>:</td>
                                <td class="data"><?php $this->_($this->data['openings']); if ($this->data['openingsAvailable'] != $this->data['openings']): ?> (<?php $this->_($this->data['openingsAvailable']); _e('Available'); endif; ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Type') ?>:</td>
                                <td class="data"><?php $this->_($this->data['typeDescription']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Status') ?>:</td>
                                <td class="data"><?php $this->_($this->data['status']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Pipeline') ?>:</td>
                                <td class="data"><?php $this->_($this->data['pipeline']) ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Submitted') ?>:</td>
                                <td class="data"><?php $this->_($this->data['submitted']) ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Days Old') ?>:</td>
                                <td class="data"><?php $this->_($this->data['daysOld']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Created') ?>:</td>
                                <td class="data"><?php $this->_($this->data['dateCreated']); ?> (<?php $this->_($this->data['enteredByFullName']); ?>)</td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Recruiter') ?>:</td>
                                <td class="data"><?php $this->_($this->data['recruiterFullName']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php _e('Owner') ?>:</td>
                                <td class="data"><?php $this->_($this->data['ownerFullName']); ?></td>
                            </tr>

                            <?php for ($i = (intval(count($this->extraFieldRS))/2); $i < (count($this->extraFieldRS)); $i++): ?>
                                <tr>
                                    <td class="vertical"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</td>
                                    <td class="data"><?php echo($this->extraFieldRS[$i]['display']); ?></td>
                                </tr>
                            <?php endfor; ?>

                            <?php eval(Hooks::get('JO_TEMPLATE_SHOW_BOTTOM_OF_RIGHT')); ?>
                        </table>
                    </td>
                </tr>
            </table>

            <?php if ($this->isPublic): ?>
            <div style="background-color: #E6EEFE; padding: 10px; margin: 5px 0 12px 0; border: 1px solid #728CC8;">
                <b>This job order is public<?php if ($this->careerPortalURL === false): ?>.</b><?php else: ?>
                    and will be shown on your
                    <?php if ($_SESSION['OSATS']->getAccessLevel() >= ACCESS_LEVEL_SA): ?>
                        <a style="font-weight: bold;" href="<?php $this->_($this->careerPortalURL); ?>">Careers Website</a>.
                    <?php else: ?>
                        Careers Website.
                    <?php endif; ?></b>
                <?php endif; ?>

                <?php if ($this->questionnaireID !== false): ?>
                    <br />Applicants must complete the "<i><?php echo $this->questionnaireData['title']; ?></i>" (<a href="<?php echo osatutil::getIndexName(); ?>?m=settings&a=careerPortalQuestionnaire&questionnaireID=<?php echo $this->questionnaireID; ?>">edit</a>) questionnaire when applying.
                <?php else: ?>
                    <br />You have not attached any
                    <?php if ($_SESSION['OSATS']->getAccessLevel() >= ACCESS_LEVEL_SA): ?>
                        <a href="<?php echo osatutil::getIndexName(); ?>?m=settings&a=careerPortalSettings">Questionnaires</a>.
                    <?php else: ?>
                        Questionnaires.
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <table class="detailsOutside" width="925">
                <tr>
                    <td>
                        <table class="detailsInside">
                            <tr>
                                <td valign="top" class="vertical"><?php _e('Attachments') ?>:</td>
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
                                                    <?php if (!$this->isPopup): ?>
                                                        <?php if ($this->accessLevel >= ACCESS_LEVEL_DELETE): ?>
                                                            <a href="<?php echo(osatutil::getIndexName()); ?>?m=joborders&amp;a=deleteAttachment&amp;jobOrderID=<?php echo($this->jobOrderID); ?>&amp;attachmentID=<?php $this->_($attachmentsData['attachmentID']) ?>"  title="Delete" onclick="javascript:return confirm('Delete this attachment?');">
                                                                <img src="images/actions/delete.gif" alt="" width="16" height="16" border="0" />
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                    <?php if (!$this->isPopup): ?>
                                        <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>
                                            <?php if (isset($this->attachmentLinkHTML)): ?>
                                                <?php echo($this->attachmentLinkHTML); ?>
                                            <?php else: ?>
                                                <a href="#" onclick="showPopWin('<?php echo(osatutil::getIndexName()); ?>?m=joborders&amp;a=createAttachment&amp;jobOrderID=<?php echo($this->jobOrderID); ?>', 400, 125, null); return false;">
                                            <?php endif; ?>
                                                <img src="images/paperclip_add.gif" width="16" height="16" border="0" alt="add attachment" class="absmiddle" />&nbsp;<?php _e('Add Attachment') ?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <tr>
                                <td valign="top" class="vertical"><?php _e('Description') ?>:</td>

                                <td class="data" colspan="2">
                                    <?php if($this->data['description'] != ''): ?>
                                    <div id="shortDescription" style="overflow: auto; height:170px; border: #AAA 1px solid; padding:5px;">
                                        <?php echo($this->data['description']); ?>
                                    </div>
                                    <?php endif; ?>
                                </td>

                            </tr>

                            <tr>
                                <td valign="top" class="vertical"><?php _e('Internal Notes') ?>:</td>

                                <td class="data" style="width:320px;">
                                    <?php if($this->data['notes'] != ''): ?>
                                        <div id="shortDescription" style="overflow: auto; height:240px; border: #AAA 1px solid; padding:5px;">
                                            <?php echo($this->data['notes']); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                
                                <td style="vertical-align:top;">
                                    <?php echo($this->pipelineGraph);  ?>
                                </td>
                                
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
<?php if (!$this->isPopup): ?>
            <div id="actionbar">
                <span style="float:left;">
                    <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT): ?>
                        <a id="edit_link" href="<?php echo(osatutil::getIndexName()); ?>?m=joborders&amp;a=edit&amp;jobOrderID=<?php echo($this->jobOrderID); ?>">
                            <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />&nbsp;<?php _e('Edit')?>
                        </a>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    <?php endif; ?>
                    <?php if ($this->accessLevel >= ACCESS_LEVEL_DELETE): ?>
                        <a id="delete_link" href="<?php echo(osatutil::getIndexName()); ?>?m=joborders&amp;a=delete&amp;jobOrderID=<?php echo($this->jobOrderID); ?>" onclick="javascript:return confirm('Delete this job order?');">
                            <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;<?php _e('Delete')?>
                        </a>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    <?php endif; ?>
                    <?php if ($this->accessLevel >= ACCESS_LEVEL_MULTI_SA): ?>
                        <?php if ($this->data['isAdminHidden'] == 1): ?>
                            <a href="<?php echo(osatutil::getIndexName()); ?>?m=joborders&amp;a=administrativeHideShow&amp;jobOrderID=<?php echo($this->jobOrderID); ?>&amp;state=0">
                                <img src="images/resume_preview_inline.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;<?php _e('Administrative Show') ?>
                            </a>
                            <?php else: ?>
                            <a href="<?php echo(osatutil::getIndexName()); ?>?m=joborders&amp;a=administrativeHideShow&amp;jobOrderID=<?php echo($this->jobOrderID); ?>&amp;state=1">
                                <img src="images/resume_preview_inline.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;<?php _e('Administrative Hide') ?>
                            </a>
                        <?php endif; ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    <?php endif; ?>
                </span>
                <span style="float:right;">
                    <?php if (!empty($this->data['public']) && $this->careerPortalEnabled): ?>
                        <a id="public_link" href="<?php echo(osatutil::getAbsoluteURI()); ?>careers/<?php echo(osatutil::getIndexName()); ?>?p=showJob&amp;ID=<?php echo($this->jobOrderID); ?>">
                            <img src="images/public.gif" width="16" height="16" class="absmiddle" alt="Online Application" border="0" />&nbsp;<?php _e('Online Application') ?>
                        </a>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    <?php endif; ?>
                    <?php /* TODO: Make report available for every site. */ ?>
                    <a id="report_link" href="<?php echo(osatutil::getIndexName()); ?>?m=reports&amp;a=customizeJobOrderReport&amp;jobOrderID=<?php echo($this->jobOrderID); ?>">
                        <img src="images/reportsSmall.gif" width="16" height="16" class="absmiddle" alt="report" border="0" />&nbsp;<?php _e('Generate Report') ?>
                    </a>
                    <?php if ($this->privledgedUser): ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <a id="history_link" href="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=viewItemHistory&amp;dataItemType=400&amp;dataItemID=<?php echo($this->jobOrderID); ?>">
                            <img src="images/icon_clock.gif" width="16" height="16" class="absmiddle"  border="0" />&nbsp;<?php _e('View History') ?>
                        </a>
                    <?php endif; ?>
                </span>
            </div>
<?php endif; ?>
            <br clear="all" />
            <br />

            <p class="note"><?php _e('Candidate Pipeline') ?></p>

            <p id="ajaxPipelineControl">
                <?php _e('Number of visible entries') ?>:&nbsp;&nbsp;
                <select id="numberOfEntriesSelect" onchange="PipelineJobOrder_changeLimit(<?php $this->_($this->data['jobOrderID']); ?>, this.value, <?php if ($this->isPopup) echo(1); else echo(0); ?>, 'ajaxPipelineTable', '<?php echo($this->sessionCookie); ?>', 'ajaxPipelineTableIndicator', '<?php echo(osatutil::getIndexName()); ?>');" class="selectBox">
                    <option value="15" <?php if ($this->pipelineEntriesPerPage == 15): ?>selected<?php endif; ?>>15 <?php _e('entries') ?></option>
                    <option value="30" <?php if ($this->pipelineEntriesPerPage == 30): ?>selected<?php endif; ?>>30 <?php _e('entries') ?></option>
                    <option value="50" <?php if ($this->pipelineEntriesPerPage == 50): ?>selected<?php endif; ?>>50 <?php _e('entries') ?></option>
                    <option value="99999" <?php if ($this->pipelineEntriesPerPage == 99999): ?>selected<?php endif; ?>><?php _e('All') ?> <?php _e('entries') ?></option>
                </select>&nbsp;
                <span id="ajaxPipelineNavigation">
                </span>&nbsp;
                <img src="images/indicator.gif" alt="" id="ajaxPipelineTableIndicator" />
            </p>

            <div id="ajaxPipelineTable">
            </div>
            <script type="text/javascript">
                PipelineJobOrder_populate(<?php $this->_($this->data['jobOrderID']); ?>, 0, <?php $this->_($this->pipelineEntriesPerPage); ?>, 'dateCreatedInt', 'desc', <?php if ($this->isPopup) echo(1); else echo(0); ?>, 'ajaxPipelineTable', '<?php echo($this->sessionCookie); ?>', 'ajaxPipelineTableIndicator', '<?php echo(osatutil::getIndexName()); ?>');
            </script>

<?php if (!$this->isPopup): ?>
            <?php if ($this->accessLevel >= ACCESS_LEVEL_EDIT && !isset($this->frozen)): ?>
                <a href="#" onclick="showPopWin('<?php echo(osatutil::getIndexName()); ?>?m=joborders&amp;a=considerCandidateSearch&amp;jobOrderID=<?php echo($this->jobOrderID); ?>', 820, 550, null); return false;">
                    <img src="images/consider.gif" width="16" height="16" class="absmiddle" alt="add candidate" border="0" />&nbsp;<?php _e('Add Candidate to This Job Order Pipeline') ?>
                </a>
            <?php endif; ?>
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

<?php endif; ?>
