<?php /* $Id: Show.tpl 3814 2007-12-06 17:54:28Z brian $ */
include_once('./vendor/autoload.php');
use OpenCATS\UI\QuickActionMenu;
?>
<?php if ($this->isPopup): ?>
    <?php TemplateUtility::printHeader(__('Job Order').' - '.$this->data['title'], array('js/sorttable.js', 'js/match.js', 'js/pipeline.js', 'js/attachment.js')); ?>
<?php else: ?>
    <?php TemplateUtility::printHeader(__('Job Order').' - '.$this->data['title'], array( 'js/sorttable.js', 'js/match.js', 'js/pipeline.js', 'js/attachment.js')); ?>
    <?php TemplateUtility::printHeaderBlock(); ?>
    <?php TemplateUtility::printTabs($this->active); 
    ?>
        <div id="main">
            <?php TemplateUtility::printQuickSearch(); ?>
<?php endif; 


//vd(array('$this->fl'=>$this->fl));    
$showWidth = 1000;   
 
 
function tplAddCandidate($thisTpl){
 ?>
            <?php if (!$thisTpl->isPopup): ?>
            <?php if ($thisTpl->getUserAccessLevel('joborders.considerCandidateSearch') >= ACCESS_LEVEL_EDIT && !isset($thisTpl->frozen)): ?>
                <a href="#" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=considerCandidateSearch&amp;jobOrderID=<?php echo($thisTpl->jobOrderID); ?>', 820, 550, null); return false;">
                    <img src="images/consider.gif" width="16" height="16" class="absmiddle" alt="add candidate" border="0" />&nbsp;<?php echo __("Add Candidate to This Job Order Pipeline");?>
                </a>
            <?php endif; ?>
            <?php endif; ?>
 <?php
 }

 
 function tplAttachmentLink($thisTpl){
 ?>
                                     <?php if (!$thisTpl->isPopup): ?>
                                        <?php if ($thisTpl->getUserAccessLevel('joborders.createAttachment') >= ACCESS_LEVEL_EDIT): ?>
                                            <?php if (isset($thisTpl->attachmentLinkHTML)): ?>
                                                <?php echo($thisTpl->attachmentLinkHTML); ?>
                                            <?php else: ?>
                                                <a href="#" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=createAttachment&amp;jobOrderID=<?php echo($thisTpl->jobOrderID); ?>', 400, 125, null); return false;">
                                            <?php endif; ?>
                                                <img src="images/paperclip_add.gif" width="16" height="16" border="0" alt="<?php echo __("add attachment");?>" class="absmiddle" />&nbsp;<?php echo __("Add Attachment");?>
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
 <?php
 }
 
 function tplActions($thisTpl, $top=false){
 ?>
 <?php if (!$thisTpl->isPopup): ?>
            <div id="actionbar">
                <span style="float:left;">
                    <?php if ($thisTpl->getUserAccessLevel('joborders.edit') >= ACCESS_LEVEL_EDIT): ?>
                        <a id="edit_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=edit&amp;jobOrderID=<?php echo($thisTpl->jobOrderID); ?>">
                            <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="<?php echo __("edit");?>" border="0" />&nbsp;<?php echo __("Edit");?>
                        </a>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    <?php endif; ?>
                    <?php if ($thisTpl->getUserAccessLevel('joborders.delete') >= ACCESS_LEVEL_DELETE): ?>
                        <a id="delete_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=delete&amp;jobOrderID=<?php echo($thisTpl->jobOrderID); ?>" onclick="javascript:return confirm('<?php echo __("Delete this job order?");?>');">
                            <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;<?php echo __("Delete");?>
                        </a>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    <?php endif; ?>
                    <?php if ($thisTpl->getUserAccessLevel('joborders.hidden') >= ACCESS_LEVEL_MULTI_SA): ?>
                        <?php if ($thisTpl->data['isAdminHidden'] == 1): ?>
                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=administrativeHideShow&amp;jobOrderID=<?php echo($thisTpl->jobOrderID); ?>&amp;state=0">
                                <img src="images/resume_preview_inline.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;<?php echo __("Administrative Show");?>
                            </a>
                            <?php else: ?>
                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=administrativeHideShow&amp;jobOrderID=<?php echo($thisTpl->jobOrderID); ?>&amp;state=1">
                                <img src="images/resume_preview_inline.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;<?php echo __("Administrative Hide");?>
                            </a>
                        <?php endif; ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    <?php endif; ?>
                    
                    <?php if ($top) { ?>
                    <?php tplAttachmentLink($thisTpl);?>
                    <?php tplAddCandidate($thisTpl);?>                    
                    <a id="report_link" href="<?php echo E::routeHref('excel/export');?>?i=jobOrder&id=<?php echo($thisTpl->jobOrderID); ?>">
                        <img src="images/reportsSmall.gif" width="16" height="16" class="absmiddle" alt="sheet" border="0" />&nbsp;<?php echo __("Generate Job Order Sheet");?>
                    </a> 
                    <?php } ?>   
                                                        
                </span>
                <span style="float:right;">
                    <?php if (!empty($thisTpl->data['public']) && $thisTpl->careerPortalEnabled): ?>
                        <a id="public_link" href="<?php echo(CATSUtility::getAbsoluteURI()); ?>careers/<?php echo(CATSUtility::getIndexName()); ?>?p=showJob&amp;ID=<?php echo($thisTpl->jobOrderID); ?>">
                            <img src="images/public.gif" width="16" height="16" class="absmiddle" alt="Online Application" border="0" />&nbsp;<?php echo __("Online Application");?>
                        </a>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    <?php endif; ?>
                    <?php /* TODO: Make report available for every site. */ ?>
                    <a id="report_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=reports&amp;a=customizeJobOrderReport&amp;jobOrderID=<?php echo($thisTpl->jobOrderID); ?>">
                        <img src="images/reportsSmall.gif" width="16" height="16" class="absmiddle" alt="report" border="0" />&nbsp;<?php echo __("Generate Report");?>
                    </a>
                    <?php if ($thisTpl->privledgedUser): ?>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                        <a id="history_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=viewItemHistory&amp;dataItemType=400&amp;dataItemID=<?php echo($thisTpl->jobOrderID); ?>">
                            <img src="images/icon_clock.gif" width="16" height="16" class="absmiddle"  border="0" />&nbsp;<?php echo __("View History");?>
                        </a>
                    <?php endif; ?>
                </span>
            </div>
<?php endif; ?>
 
 
 <?php
 } //tplActions



?>

        <div id="contents">

            <table>
            	<tr><td colspan="2">
            	 
            	</td>
                <tr>
                    <td width="3%">
                        <img src="images/job_orders.gif" width="24" height="24" border="0" alt="Job Orders" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php echo __("Job Orders");?>: <?php echo __("Job Order Details");?></h2></td>
                </tr>
            </table>
            
            
<?php tplActions($this,true);?><br/>
            <p class="note"><?php echo __("Job Order Details");?></p>

            <?php if ($this->data['isAdminHidden'] == 1): ?>
                <p class="warning">
                <?php echo sprintf(__("This entry is hidden.  Only CATS Administrators can view it or search for it.  To make it visible by the site users, click %s"),'<a href="'.CATSUtility::getIndexName().'?m=joborders&amp;a=administrativeHideShow&amp;jobOrderID='.$this->jobOrderID.'&amp;state=0" style="font-weight:bold;">'.__('Here.').'</a>');?> 
                </p>
            <?php endif; ?>

            <?php if (isset($this->frozen)): ?>
                <table style="font-weight:bold; border: 1px solid #000; background-color: #ffed1a; padding:5px; margin-bottom:7px;" width="100%" id="candidateAlreadyInSystemTable">
                    <tr>
                        <td class="tdVertical">
                           <?php echo sprintf(__("This Job Order is %s and can not be modified."),$this->_($this->data['status']));?>
                           <?php if ($this->getUserAccessLevel('joborders.edit') >= ACCESS_LEVEL_EDIT): ?>
                               <a id="edit_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=edit&amp;jobOrderID=<?php echo($this->jobOrderID); ?>">
                                   <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />&nbsp;<?php echo __("Edit");?>
                               </a>
                               <?php echo __("the Job Order to make it Active.");?>&nbsp;&nbsp;
                           <?php endif; ?>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>
            
            

            <table class="detailsOutside" width="<?php echo $showWidth;?>">
                <tr style="vertical-align:top;">
                    <td width="50%" height="100%">
                        <table class="detailsInside" height="100%">
                            <tr>
                                <td class="vertical"><?php echo __("Title");?>:</td>
                                <td class="data" width="300">
                                    <span class="<?php echo($this->data['titleClass']); ?>"><?php $this->_($this->data['title']); ?></span>
                                    <?php echo($this->data['public']) ?>
                                    <?php TemplateUtility::printSingleQuickActionMenu(new QuickActionMenu(DATA_ITEM_JOBORDER, $this->data['jobOrderID'], $_SESSION['CATS']->getAccessLevel('joborders.edit'))); ?>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Company Name");?>:</td>
                                <td class="data">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php echo($this->data['companyID']); ?>">
                                        <?php echo($this->data['companyName']); ?>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Department");?>:</td>
                                <td class="data">
                                    <?php echo($this->data['department']); ?>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("CATS Job ID");?>:</td>
                                <td class="data" width="300"><?php $this->_($this->data['jobOrderID']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Company Job ID");?>:</td>
                                <td class="data"><?php echo($this->data['companyJobID']); ?></td>
                            </tr>

                            <!-- CONTACT INFO -->
                            <tr>
                                <td class="vertical"><?php echo __("Contact Name");?>:</td>
                                <td class="data">
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=show&amp;contactID=<?php echo($this->data['contactID']); ?>">
                                        <?php echo($this->data['contactFullName']); ?>
                                    </a>
                                </td>
                            </tr>

                            <!--tr>
                                <td class="vertical"><?php echo __("Contact Phone");?>:</td>
                                <td class="data"><?php echo($this->data['contactWorkPhone']); 
                                //vd($this->fl);
                                ?></td>
                            </tr-->
						<tr><td colspan="2">
                    	<?php 
                    		E::showCustomFields(array(
                    			'dataItem'=>'jobOrder',
                    			'section'=>'col1read',
                    			'template'=>'read',
                    			'fl'=>$this->fl,
                    			)); 
                    	?>
						</td></tr>
                            <tr>
                                <td class="vertical"><?php echo __("Contact Email");?>:</td>
                                <td class="data">
                                    <a href="mailto:<?php $this->_($this->data['contactEmail']); ?>"><?php $this->_($this->data['contactEmail']); ?></a>
                                </td>
                            </tr>
                            <!-- /CONTACT INFO -->

                            <tr>
                                <td class="vertical"><?php echo __("Location");?>:</td>
                                <td class="data"><?php $this->_($this->data['cityAndState']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Max Rate");?>:</td>
                                <td class="data"><?php $this->_($this->data['maxRate']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Salary");?>:</td>
                                <td class="data"><?php $this->_($this->data['salary']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Start Date");?>:</td>
                                <td class="data"><?php $this->_($this->data['startDate']); ?></td>
                            </tr>
                        </table>
                    </td>

                    <td width="50%" height="100%" style="vertical-align:top;" >
                        <table class="detailsInside" height="100%">
                            <tr>
                                <td class="vertical"><?php echo __("Duration");?>:</td>
                                <td class="data"><?php $this->_($this->data['duration']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Openings");?>:</td>
                                <td class="data"><?php $this->_($this->data['openings']); if ($this->data['openingsAvailable'] != $this->data['openings']): ?> (<?php $this->_($this->data['openingsAvailable']); ?> Available)<?php endif; ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Type");?>:</td>
                                <td class="data"><?php $this->_($this->data['typeDescription']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Status");?>:</td>
                                <td class="data"><?php $this->_(EnumTypeEnum::jobOrderStatus()->enumByAttr('dbValue',$this->data['status'])->desc); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Pipeline");?>:</td>
                                <td class="data"><?php $this->_($this->data['pipeline']) ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Submitted");?>:</td>
                                <td class="data"><?php $this->_($this->data['submitted']) ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Days Old");?>:</td>
                                <td class="data"><?php $this->_($this->data['daysOld']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Created");?>:</td>
                                <td class="data"><?php $this->_(evConvertDateDbToDateTime($this->data['dateCreatedDb'])); ?> (<?php $this->_($this->data['enteredByFullName']); ?>)</td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Recruiter");?>:</td>
                                <td class="data"><?php $this->_($this->data['recruiterFullName']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical"><?php echo __("Owner");?>:</td>
                                <td class="data"><?php $this->_($this->data['ownerFullName']); ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
                        	            
            
            <table class="detailsOutside" width="<?php echo $showWidth;?>">
                <tr style="vertical-align:top;">
                    <td width="50%" height="100%">
                        <table class="detailsInside" height="100%">
                            <?php for ($i = 0; $i < intval(count($this->extraFieldRS)/2); $i++): ?>
                               <?php if(($this->extraFieldRS[$i]['extraFieldType']) != EXTRA_FIELD_TEXTAREA): ?>
                                   <tr>
                                        <td class="vertical"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</td>
                                        <td class="data"><?php echo($this->extraFieldRS[$i]['display']); ?></td>
                                   </tr>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <?php eval(Hooks::get('JO_TEMPLATE_SHOW_BOTTOM_OF_LEFT')); ?>
                        </table>
                    </td>
                    <td width="50%" height="100%">
                        <table class="detailsInside" height="100%">                        
                            <?php for ($i = (intval(count($this->extraFieldRS))/2); $i < (count($this->extraFieldRS)); $i++): ?>
                                <?php if(($this->extraFieldRS[$i]['extraFieldType']) != EXTRA_FIELD_TEXTAREA): ?>
                                    <tr>
                                        <td class="vertical"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</td>
                                        <td class="data"><?php echo($this->extraFieldRS[$i]['display']); ?></td>
                                    </tr>
                                <?php endif; ?>
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
                    <?php if ($this->getUserAccessLevel('joborders.careerPortalUrl') >= ACCESS_LEVEL_SA): ?>
                        <a style="font-weight: bold;" href="<?php $this->_($this->careerPortalURL); ?>">Careers Website</a>.
                    <?php else: ?>
                        Careers Website.
                    <?php endif; ?></b>
                <?php endif; ?>

                <?php if ($this->questionnaireID !== false): ?>
                    <br />Applicants must complete the "<i><?php echo $this->questionnaireData['title']; ?></i>" (<a href="<?php echo CATSUtility::getIndexName(); ?>?m=settings&a=careerPortalQuestionnaire&questionnaireID=<?php echo $this->questionnaireID; ?>">edit</a>) questionnaire when applying.
                <?php else: ?>
                    <br />You have not attached any
                    <?php if ($this->getUserAccessLevel('setting.carrerPortalSettings') >= ACCESS_LEVEL_SA): ?>
                        <a href="<?php echo CATSUtility::getIndexName(); ?>?m=settings&a=careerPortalSettings">Questionnaires</a>.
                    <?php else: ?>
                        Questionnaires.
                    <?php endif; ?>
                <?php endif; ?>
            </div>
            <?php endif; ?>

            <table class="detailsOutside" width="<?php echo $showWidth;?>">
                <tr>
                    <td>
                        <table class="detailsInside" width="100%">
                            <tr>
                                <td valign="top" class="vertical"><?php echo __("Attachments");?>:</td>
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
                                                <td><?php $this->_(evConvertDateDbToDateTime($attachmentsData['dateCreatedDb'])) ?></td>
                                                <td>
                                                    <?php if (!$this->isPopup): ?>
                                                        <?php if ($this->getUserAccessLevel('joborders.deleteAttachment') >= ACCESS_LEVEL_DELETE): ?>
                                                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=deleteAttachment&amp;jobOrderID=<?php echo($this->jobOrderID); ?>&amp;attachmentID=<?php $this->_($attachmentsData['attachmentID']) ?>"  title="<?php echo __("Delete");?>" onclick="javascript:return confirm('<?php echo __("Delete this attachment?");?>');">
                                                                <img src="images/actions/delete.gif" alt="" width="16" height="16" border="0" />
                                                            </a>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </table>
                                    <?php tplAttachmentLink($this); ?>
                                </td>
                            </tr>

                            <tr>
                                <td valign="top" class="vertical"><?php echo __("Description");?>:</td>

                                <td class="data" colspan="2">
                                    <?php if($this->data['description'] != ''): ?>
                                    <div id="shortDescription" style="overflow: auto; height:170px; border: #AAA 1px solid; padding:5px;">
                                        <?php echo($this->data['description']); ?>
                                    </div>
                                    <?php endif; ?>
                                </td>

                            </tr>
                
                            <?php for ($i = (intval(count($this->extraFieldRS))/2); $i < (count($this->extraFieldRS)); $i++): ?>
                                <?php if(($this->extraFieldRS[$i]['extraFieldType']) == EXTRA_FIELD_TEXTAREA): ?>
                                    <tr>
                                        <td class="vertical"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</td>
                                        <td class="data"><?php echo($this->extraFieldRS[$i]['display']); ?></td>
                                    </tr>
                                <?php endif; ?>
                            <?php endfor; ?>

                            <tr>
                                <td valign="top" class="vertical"><?php echo __("Internal Notes");?>:</td>

                                <td class="data" style="width:100%;">
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
            
   			<p class="note"><?php echo __("Range and conitions of service");?></p>
            <table class="detailsOutside" width="<?php echo $showWidth;?>">
                <tr style="vertical-align:top;">
                    <td width="50%">                    	
                    	<?php 
                    		E::showCustomFields(array(
                    			'dataItem'=>'jobOrder',
                    			'section'=>'rangeAndConditions1',
                    			'template'=>'read',
                    			'fl'=>$this->fl,
                    			)); 
                    	?>
                    </td>   
                  	<td width="50%">                  	
                    	<?php 
                    		E::showCustomFields(array(
                    			'dataItem'=>'jobOrder',
                    			'section'=>'rangeAndConditions2',
                    			'template'=>'read',
                    			'fl'=>$this->fl,
                    			)); 
                    	?>                	
                  	</td>
                </tr>
            </table>
            
<script>
function showHideReq() {
    var x = document.getElementById('reqTable');
    var h = document.getElementById('reqHeader');
    if (x.style.display === 'none') {
        x.style.display = null;
        h.style.backgroundColor = null;
        x = document.getElementById('reqButton');
        x.innerHTML = 'Ukryj';
    } else {
        x.style.display = 'none';
        h.style.backgroundColor = '#f9f9d9';
        x = document.getElementById('reqButton');
        x.innerHTML = 'Pokaż';        
    }
}
</script>
            
            <p class="note" id="reqHeader" style="background-color:#f9f9d9;"><?php echo __("Requirements");?> <button id = "reqButton" onclick="showHideReq()">Pokaż</button></p> 
            
             <table id="reqTable" class="detailsOutside" width="<?php echo $showWidth;?>" style="display:none;">
                <tr style="vertical-align:top;">
                    <td width="50%">                   	
                    	<?php 
                    		E::showCustomFields(array(
                    			'dataItem'=>'jobOrder',
                    			'section'=>'requirements1',
                    			'template'=>'read',
                    			'fl'=>$this->fl,
                    			)); 
                    	?> 
                    </td>   
                  	<td width="50%">                  	
                    	<?php 
                    		E::showCustomFields(array(
                    			'dataItem'=>'jobOrder',
                    			'section'=>'requirements2',
                    			'template'=>'read',
                    			'fl'=>$this->fl,
                    			)); 
                    	?>                  	
                  	</td>
                </tr>
            </table> 
            
            <?php tplActions($this);?>              
            

            <br clear="all" />
            <br />

            <p class="note"><?php echo __("Candidate Pipeline");?></p>

            <p id="ajaxPipelineControl">
                Number of visible entries:&nbsp;&nbsp;
                <select id="numberOfEntriesSelect" onchange="PipelineJobOrder_changeLimit(<?php $this->_($this->data['jobOrderID']); ?>, this.value, <?php if ($this->isPopup) echo(1); else echo(0); ?>, 'ajaxPipelineTable', '<?php echo($this->sessionCookie); ?>', 'ajaxPipelineTableIndicator', '<?php echo(CATSUtility::getIndexName()); ?>');" class="selectBox">
                    <option value="15" <?php if ($this->pipelineEntriesPerPage == 15): ?>selected<?php endif; ?>><?php echo sprintf(__("%s entries"),'15');?></option>
                    <option value="30" <?php if ($this->pipelineEntriesPerPage == 30): ?>selected<?php endif; ?>><?php echo sprintf(__("%s entries"),'30');?></option>
                    <option value="50" <?php if ($this->pipelineEntriesPerPage == 50): ?>selected<?php endif; ?>><?php echo sprintf(__("%s entries"),'50');?></option>
                    <option value="99999" <?php if ($this->pipelineEntriesPerPage == 99999): ?>selected<?php endif; ?>><?php echo __("All entries");?></option>
                </select>&nbsp;
                <span id="ajaxPipelineNavigation">
                </span>&nbsp;
                <img src="images/indicator.gif" alt="" id="ajaxPipelineTableIndicator" />
            </p>

            <div id="ajaxPipelineTable"></div>
            <input type="checkbox" name="select_all" onclick="selectAll_candidates(this)" title="<?php echo __("Select all candidates");?>" /> <a href="javascript:void(0);" onclick="exportFromPipeline()" title="<?php echo __("Export selected candidates");?>"><?php echo __("Export");?></a>&nbsp;&nbsp;&nbsp;&nbsp;
            <script type="text/javascript">
            	function exportFromPipeline(){
<?php
	$params = array(
			'sortBy' => 'dateModifiedSort',
			'sortDirection' => 'DESC',
	        'filterVisible' => false,
	        'rangeStart' => 0,
	        'maxResults' => 100000000,
	        'exportIDs' => '<dynamic>',
	        'noSaveParameters' => true);

	$instance_name = 'candidates:candidatesListByViewDataGrid';
	$instance_md5 = md5($instance_name);
?>
					var exportArray<?= $instance_md5 ?> = getSelected_candidates();
            		if (exportArray<?= $instance_md5 ?>.length>0) {
                		window.location.href='<?= CATSUtility::getIndexName()?>?m=export&a=exportByDataGrid&i=<?= urlencode($instance_name); ?>&p=<?= urlencode(serialize($params)) ?>&dynamicArgument<?= $instance_md5 ?>=' + urlEncode(serializeArray(exportArray<?= $instance_md5 ?>));
            		} else {
                		alert('<?php echo __("No data selected");?>');
            		}
            	}


            </script>
            <script type="text/javascript">
                PipelineJobOrder_populate(<?php $this->_($this->data['jobOrderID']); ?>, 0, <?php $this->_($this->pipelineEntriesPerPage); ?>, 'dateCreatedInt', 'desc', <?php if ($this->isPopup) echo(1); else echo(0); ?>, 'ajaxPipelineTable', '<?php echo($this->sessionCookie); ?>', 'ajaxPipelineTableIndicator', '<?php echo(CATSUtility::getIndexName()); ?>');
            </script>
			
			<?php tplAddCandidate($this);?>

        </div>
    </div>

<?php TemplateUtility::printFooter(); ?>
