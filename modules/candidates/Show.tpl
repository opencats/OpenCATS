<?php /* $Id: Show.tpl 3814 2007-12-06 17:54:28Z brian $ */
include_once('./vendor/autoload.php');
use OpenCATS\UI\CandidateQuickActionMenu;
?>
<?php if ($this->isPopup): ?>
    <?php TemplateUtility::printHeader('Candidate - '.$this->data['firstName'].' '.$this->data['lastName'], array( 'js/activity.js', 'js/sorttable.js', 'js/match.js', 'js/lib.js', 'js/pipeline.js', 'js/attachment.js', 'modules/candidates/quickAction-candidates.js')); ?>
<?php else: ?>
    <?php TemplateUtility::printHeader('Candidate - '.$this->data['firstName'].' '.$this->data['lastName'], array( 'js/activity.js', 'js/sorttable.js', 'js/match.js', 'js/lib.js', 'js/pipeline.js', 'js/attachment.js', 'modules/candidates/quickAction-candidates.js')); ?>
    
    <?php TemplateUtility::printHeaderBlock(); ?>
    <?php TemplateUtility::printTabs($this->active); ?>
        <div id="main">
            <?php TemplateUtility::printQuickSearch(); ?>
<?php endif; ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/candidate.gif" width="24" height="24" border="0" alt="Candidates" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Candidates: Candidate Details</h2></td>
               </tr>
            </table>

            <p class="note">Candidate Details</p>

            <?php if ($this->data['isAdminHidden'] == 1): ?>
                <p class="warning">This Candidate is hidden.  Only CATS Administrators can view it or search for it.  To make it visible by the site users, click <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=administrativeHideShow&amp;candidateID=<?php echo($this->candidateID); ?>&amp;state=0" style="font-weight:bold;">Here.</a></p>
            <?php endif; ?>

            <table class="detailsOutside">
                <tr style="vertical-align:top;">
                    <?php $profileImage = false; ?>
                    <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData): ?>
                         <?php if ($attachmentsData['isProfileImage'] == '1'): ?>
                             <?php $profileImage = true; ?>
                         <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($profileImage): ?>
                        <td width="390" height="100%">
                    <?php else: ?>
                        </td><td width="50%" height="100%">
                    <?php endif; ?>
                        <table class="detailsInside" height="100%">
                            <tr>
                                <td class="vertical">Name:</td>
                                <td class="data">
                                    <span style="font-weight: bold;" class="<?php echo($this->data['titleClass']); ?>">
                                        <?php $this->_($this->data['firstName']); ?>
                                        <?php $this->_($this->data['middleName']); ?>
                                        <?php $this->_($this->data['lastName']); ?>
                                        <?php if ($this->data['isActive'] != 1): ?>
                                            &nbsp;<span style="color:orange;">(INACTIVE)</span>
                                        <?php endif; ?>
                                        <?php TemplateUtility::printSingleQuickActionMenu(new CandidateQuickActionMenu(DATA_ITEM_CANDIDATE, $this->data['candidateID'], $_SESSION['CATS']->getAccessLevel('candidates.edit'))); ?>
                                    </span>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical">E-Mail:</td>
                                <td class="data">
                                    <a href="mailto:<?php $this->_($this->data['email1']); ?>">
                                        <?php $this->_($this->data['email1']); ?>
                                    </a>
                                </td>
                            </tr>
                            <tr>
                                <td class="vertical">2nd E-Mail:</td>
                                <td class="data">
                                    <a href="mailto:<?php $this->_($this->data['email2']); ?>">
                                        <?php $this->_($this->data['email2']); ?>
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical">Home Phone:</td>
                                <td class="data"><?php $this->_($this->data['phoneHome']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Cell Phone:</td>
                                <td class="data"><?php $this->_($this->data['phoneCell']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Work Phone:</td>
                                <td class="data"><?php $this->_($this->data['phoneWork']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Best Time To Call:</td>
                                <td class="data"><?php $this->_($this->data['bestTimeToCall']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Address:</td>
                                <td class="data"><?php echo(nl2br(htmlspecialchars($this->data['address']))); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">&nbsp;</td>
                                <td class="data">
                                    <?php $this->_($this->data['cityAndState']); ?>
                                    <?php $this->_($this->data['zip']); ?>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical">Web Site:</td>
                                <td class="data">
                                    <?php if (!empty($this->data['webSite'])): ?>
                                        <a href="<?php $this->_($this->data['webSite']); ?>" target="_blank"><?php $this->_($this->data['webSite']); ?></a>
                                    <?php endif; ?>
                                </td>
                            </tr>

                            <tr>
                                <td class="vertical">Source:</td>
                                <td class="data"><?php $this->_($this->data['source']); ?></td>
                            </tr>

                            <?php for ($i = 0; $i < intval(count($this->extraFieldRS)/2); $i++): ?>
                                <tr>
                                    <td class="vertical"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</td>
                                    <td class="data"><?php echo($this->extraFieldRS[$i]['display']); ?></td>
                                </tr>
                            <?php endfor; ?>

                            <tr>
                                <td class="vertical"></td>
                                <td class="data"></td>
                            </tr>
                        </table>
                    </td>

                    <?php if ($profileImage): ?>
                        <td width="390" height="100%" valign="top">
                    <?php else: ?>
                        </td><td width="50%" height="100%" valign="top">
                    <?php endif; ?>
                        <table class="detailsInside" height="100%">
                            <tr>
                                <td class="vertical">Date Available:</td>
                                <td class="data"><?php $this->_($this->data['dateAvailable']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Current Employer:</td>
                                <td class="data"><?php $this->_($this->data['currentEmployer']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Key Skills:</td>
                                <td class="data"><?php $this->_($this->data['keySkills']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Can Relocate:</td>
                                <td class="data"><?php $this->_($this->data['canRelocate']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Current Pay:</td>
                                <td class="data"><?php $this->_($this->data['currentPay']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Desired Pay:</td>
                                <td class="data"><?php $this->_($this->data['desiredPay']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Pipeline:</td>
                                <td class="data"><?php $this->_($this->data['pipeline']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Submitted:</td>
                                <td class="data"><?php $this->_($this->data['submitted']); ?></td>
                            </tr>

                            <tr>
                                <td class="vertical">Created:</td>
                                <td class="data"><?php $this->_($this->data['dateCreated']); ?> (<?php $this->_($this->data['enteredByFullName']); ?>)</td>
                            </tr>

                            <tr>
                                <td class="vertical">Owner:</td>
                                <td class="data"><?php $this->_($this->data['ownerFullName']); ?></td>
                            </tr>

                            <?php for ($i = (intval(count($this->extraFieldRS))/2); $i < (count($this->extraFieldRS)); $i++): ?>
                                <tr>
                                    <td class="vertical"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</td>
                                    <td class="data"><?php echo($this->extraFieldRS[$i]['display']); ?></td>
                                </tr>
                            <?php endfor; ?>
                        </table>
                    </td>
                    <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData): ?>
                         <?php if ($attachmentsData['isProfileImage'] == '1'): ?>
                            <td width="135" height="100%"  valign="top">
                                <table class="detailsInside">
                                    <tr>
                                        <td style="text-align:center;" class="vertical">
                                            <?php if (!$this->isPopup): ?>
                                                <?php if ($this->getUserAccessLevel('candidates.deleteAttachment') >= ACCESS_LEVEL_DELETE): ?>
                                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=deleteAttachment&amp;candidateID=<?php echo($this->candidateID); ?>&amp;attachmentID=<?php $this->_($attachmentsData['attachmentID']) ?>" onclick="javascript:return confirm('Delete this attachment?');">
                                                        <img src="images/actions/delete.gif" alt="" width="16" height="16" border="0" title="Delete" />
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                            &nbsp;&nbsp;&nbsp;&nbsp;
                                            <?php endif; ?>&nbsp;&nbsp;
                                            Picture:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="data">
                                            <a href="attachments/<?php $this->_($attachmentsData['directoryName']) ?>/<?php $this->_($attachmentsData['storedFilename']) ?>">
                                                <img src="attachments/<?php $this->_($attachmentsData['directoryName']) ?>/<?php $this->_($attachmentsData['storedFilename']) ?>" border="0" alt="" width="125" />
                                            </a>
                                        </td>
                                    </tr>
                                </table>
                            </td>
                         <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
            </table>

            <?php if($this->EEOSettingsRS['enabled'] == 1): ?>
                <table class="detailsOutside">
                    <tr>
                        <td>
                            <table class="detailsInside">
                                <?php for ($i = 0; $i < intval(count($this->EEOValues)/2); $i++): ?>
                                    <tr>
                                        <td class="vertical"><?php $this->_($this->EEOValues[$i]['fieldName']); ?>:</td>
                                        <?php if($this->EEOSettingsRS['canSeeEEOInfo']): ?>
                                            <td class="data"><?php $this->_($this->EEOValues[$i]['fieldValue']); ?></td>
                                        <?php else: ?>
                                            <td class="data"><i><a href="javascript:void(0);" title="Ask an administrator to see the EEO info, or have permission granted to see it.">(Hidden)</a></i></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endfor; ?>
                            </table>
                        </td>
                        <?php if ($profileImage): ?>
                            <td width="390" height="100%" valign="top">
                        <?php else: ?>
                            </td><td width="50%" height="100%" valign="top">
                        <?php endif; ?>
                            <table class="detailsInside">
                                <?php for ($i = (intval(count($this->EEOValues))/2); $i < intval(count($this->EEOValues)); $i++): ?>
                                    <tr>
                                        <td class="vertical"><?php $this->_($this->EEOValues[$i]['fieldName']); ?>:</td>
                                        <?php if($this->EEOSettingsRS['canSeeEEOInfo']): ?>
                                            <td class="data"><?php $this->_($this->EEOValues[$i]['fieldValue']); ?></td>
                                        <?php else: ?>
                                            <td class="data"><i><a href="javascript:void(0);" title="Ask an administrator to see the EEO info, or have permission  granted to see it.">(Hidden)</a></i></td>
                                        <?php endif; ?>
                                    </tr>
                                <?php endfor; ?>
                            </table>
                        </td>
                    </tr>
                </table>
            <?php endif; ?>

            <table class="detailsOutside">
                <tr>
                    <td>
                        <table class="detailsInside">
                            <tr>
                                <td valign="top" class="vertical">Misc. Notes:</td>
                                <?php if ($this->isShortNotes): ?>
                                    <td id="shortNotes" style="display:block;" class="data">
                                        <?php echo($this->data['shortNotes']); ?><span class="moreText">...</span>&nbsp;
                                        <p><a href="#" class="moreText" onclick="toggleNotes(); return false;">[More]</a></p>
                                    </td>
                                    <td id="fullNotes" style="display:none;" class="data">
                                        <?php echo($this->data['notes']); ?>&nbsp;
                                        <p><a href="#" class="moreText" onclick="toggleNotes(); return false;">[Less]</a></p>
                                    </td>
                                <?php else: ?>
                                    <td id="shortNotes" style="display:block;" class="data">
                                        <?php echo($this->data['notes']); ?>
                                    </td>
                                <?php endif; ?>
                            </tr>

                            <tr>
                                <td valign="top" class="vertical">Upcoming Events:</td>
                                <td id="shortNotes" style="display:block;" class="data">
                                <?php foreach ($this->calendarRS as $rowNumber => $calendarData): ?>
                                    <div>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=calendar&amp;view=DAYVIEW&amp;month=<?php echo($calendarData['month']); ?>&amp;year=20<?php echo($calendarData['year']); ?>&amp;day=<?php echo($calendarData['day']); ?>&amp;showEvent=<?php echo($calendarData['eventID']); ?>">
                                            <img src="<?php $this->_($calendarData['typeImage']) ?>" alt="" border="0" />
                                            <?php $this->_($calendarData['dateShow']) ?>:
                                            <?php $this->_($calendarData['title']); ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                                <?php if ($this->getUserAccessLevel('pipelines.addActivityChangeStatus') >= ACCESS_LEVEL_EDIT): ?>
                                    <a href="#" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addActivityChangeStatus&amp;candidateID=<?php echo($this->candidateID); ?>&amp;jobOrderID=-1&amp;onlyScheduleEvent=true', 600, 350, null); return false;">
                                        <img src="images/calendar_add.gif" width="16" height="16" border="0" alt="Schedule Event" class="absmiddle" />&nbsp;Schedule Event
                                    </a>
                                <?php endif; ?>
                                </td>
                            </tr>

                            <?php if (isset($this->questionnaires) && !empty($this->questionnaires)): ?>
                            <tr>
                                <td valign="top" class="vertical" valign="top" align="left">Questionnaires:</td>
                                <td valign="top" class="data" valign="top" align="left">
                                    <table cellpadding="0" cellspacing="0" border="0">
                                    <tr>
                                        <td style="border-bottom: 1px solid #c0c0c0; font-weight: bold; padding-right: 10px;">Title (Internal)</td>
                                        <td style="border-bottom: 1px solid #c0c0c0; font-weight: bold; padding-right: 10px;">Completed</td>
                                        <td style="border-bottom: 1px solid #c0c0c0; font-weight: bold; padding-right: 10px;">Description (Public)</td>
                                    </tr>
                                    <?php foreach ($this->questionnaires as $questionnaire): ?>
                                    <tr>
                                        <td style="padding-right: 10px;" nowrap="nowrap"><a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show_questionnaire&amp;candidateID=<?php echo($this->candidateID); ?>&amp;questionnaireTitle=<?php echo urlencode($questionnaire['questionnaireTitle']); ?>&print=no"><?php echo $questionnaire['questionnaireTitle']; ?></a></td>
                                        <td style="padding-right: 10px;" nowrap="nowrap"><?php echo date('F j. Y', strtotime($questionnaire['questionnaireDate'])); ?></td>
                                        <td style="padding-right: 10px;" nowrap="nowrap"><?php echo $questionnaire['questionnaireDescription']; ?></td>
                                        <td style="padding-right: 10px;" nowrap="nowrap">
                                            <a id="edit_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show_questionnaire&amp;candidateID=<?php echo($this->candidateID); ?>&amp;questionnaireTitle=<?php echo urlencode($questionnaire['questionnaireTitle']); ?>&print=no">
                                                <img src="images/actions/view.gif" width="16" height="16" class="absmiddle" alt="view" border="0" />&nbsp;View
                                            </a>
                                            &nbsp;
                                            <a id="edit_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show_questionnaire&amp;candidateID=<?php echo($this->candidateID); ?>&amp;questionnaireTitle=<?php echo urlencode($questionnaire['questionnaireTitle']); ?>&print=yes">
                                                <img src="images/actions/print.gif" width="16" height="16" class="absmiddle" alt="print" border="0" />&nbsp;Print
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </table>
                                </td>
                            </tr>
                            <?php endif; ?>

                            <tr>
                                <td valign="top" class="vertical">Attachments:</td>
                                <td valign="top" class="data">
                                    <table class="attachmentsTable">
                                        <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData): ?>
                                            <?php if ($attachmentsData['isProfileImage'] != '1'): ?>
                                                <tr>
                                                    <td>
                                                        <?php echo $attachmentsData['retrievalLink']; ?>
                                                            <img src="<?php $this->_($attachmentsData['attachmentIcon']) ?>" alt="" width="16" height="16" border="0" />
                                                            &nbsp;
                                                            <?php $this->_($attachmentsData['originalFilename']) ?>
                                                        </a>
                                                    </td>
                                                    <td><?php echo($attachmentsData['previewLink']); ?></td>
                                                    <td><?php $this->_($attachmentsData['dateCreated']) ?></td>
                                                    <td>
                                                        <?php if (!$this->isPopup): ?>
                                                            <?php if ($this->getUserAccessLevel('candidates.deleteAttachment') >= ACCESS_LEVEL_DELETE): ?>
                                                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=deleteAttachment&amp;candidateID=<?php echo($this->candidateID); ?>&amp;attachmentID=<?php $this->_($attachmentsData['attachmentID']) ?>" onclick="javascript:return confirm('Delete this attachment?');">
                                                                    <img src="images/actions/delete.gif" alt="" width="16" height="16" border="0" title="Delete" />
                                                                </a>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </table>
                                    <?php if (!$this->isPopup): ?>
                                        <?php if ($this->getUserAccessLevel('candidates.createAttachment') >= ACCESS_LEVEL_EDIT): ?>
                                            <?php if (isset($this->attachmentLinkHTML)): ?>
                                                <?php echo($this->attachmentLinkHTML); ?>
                                            <?php else: ?>
                                                <a href="#" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=createAttachment&amp;candidateID=<?php echo($this->candidateID); ?>', 400, 125, null); return false;">
                                            <?php endif; ?>
                                                <img src="images/paperclip_add.gif" width="16" height="16" border="0" alt="Add Attachment" class="absmiddle" />&nbsp;Add Attachment
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <td valign="top" class="vertical">Tags:
                                    <?php if (!$this->isPopup){ ?>
                                        <?php if ($this->getUserAccessLevel('candidates.addCandidateTags') >= ACCESS_LEVEL_EDIT){ ?>
                                                <a href="#" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addCandidateTags&amp;candidateID=<?php echo($this->candidateID); ?>', 400, 125, null); return false;">
                                                Add/Remove
                                            </a>
                                        <?php } ?>
                                    <?php } ?>

                                </td>
                                <td valign="top" class="data"><?= implode(', ',$this->assignedTags) ?>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            <?php if (!$this->isPopup): ?>
            <?php if ($this->getUserAccessLevel('candidates.edit') >= ACCESS_LEVEL_EDIT): ?>
                <a id="edit_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=edit&amp;candidateID=<?php echo($this->candidateID); ?>">
                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit" border="0" />&nbsp;Edit
                </a>
                &nbsp;&nbsp;&nbsp;&nbsp;
            <?php endif; ?>
            <?php if ($this->getUserAccessLevel('candidates.delete') >= ACCESS_LEVEL_DELETE): ?>
                <a id="delete_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=delete&amp;candidateID=<?php echo($this->candidateID); ?>" onclick="javascript:return confirm('Delete this candidate?');">
                    <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;Delete
                </a>
                &nbsp;&nbsp;&nbsp;&nbsp;
            <?php endif; ?>
            <?php if ($this->privledgedUser): ?>
                <a id="history_link" href="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=viewItemHistory&amp;dataItemType=100&amp;dataItemID=<?php echo($this->candidateID); ?>">
                    <img src="images/icon_clock.gif" width="16" height="16" class="absmiddle"  border="0" />&nbsp;View History
                </a>
                &nbsp;&nbsp;&nbsp;&nbsp;
            <?php endif; ?>
            <?php if ($this->getUserAccessLevel('candidates.administrativeHideShow') >= ACCESS_LEVEL_MULTI_SA): ?>
                <?php if ($this->data['isAdminHidden'] == 1): ?>
                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=administrativeHideShow&amp;candidateID=<?php echo($this->candidateID); ?>&amp;state=0">
                        <img src="images/resume_preview_inline.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;Administrative Show
                    </a>
                    <?php else: ?>
                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=administrativeHideShow&amp;candidateID=<?php echo($this->candidateID); ?>&amp;state=1">
                        <img src="images/resume_preview_inline.gif" width="16" height="16" class="absmiddle" alt="delete" border="0" />&nbsp;Administrative Hide
                    </a>
                <?php endif; ?>
                &nbsp;&nbsp;&nbsp;&nbsp;
            <?php endif; ?>
<?php endif; ?>
            <br clear="all" />
            <br />

            <p class="note">Job Order Pipeline</p>
            <table class="sortablepair">
                <tr>
                    <th></th>
                    <th align="left">Match</th>
                    <th align="left">Title</th>
                    <th align="left">Company</th>
                    <th align="left">Owner</th>
                    <th align="left">Added</th>
                    <th align="left">Entered By</th>
                    <th align="left">Status</th>
<?php if (!$this->isPopup): ?>
                    <th align="center">Action</th>
<?php endif; ?>
                </tr>

                <?php foreach ($this->pipelinesRS as $rowNumber => $pipelinesData): ?>
                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>" id="pipelineRow<?php echo($rowNumber); ?>">
                        <td valign="top">
                            <span id="pipelineOpen<?php echo($rowNumber); ?>">
                                <a href="javascript:void(0);" onclick="document.getElementById('pipelineDetails<?php echo($rowNumber); ?>').style.display=''; document.getElementById('pipelineClose<?php echo($rowNumber); ?>').style.display = ''; document.getElementById('pipelineOpen<?php echo($rowNumber); ?>').style.display = 'none'; PipelineDetails_populate(<?php echo($pipelinesData['candidateJobOrderID']); ?>, 'pipelineInner<?php echo($rowNumber); ?>', '<?php echo($this->sessionCookie); ?>');">
                                    <img src="images/arrow_next.png" alt="" border="0" title="Show History" />
                                </a>
                            </span>
                            <span id="pipelineClose<?php echo($rowNumber); ?>" style="display: none;">
                                <a href="javascript:void(0);" onclick="document.getElementById('pipelineDetails<?php echo($rowNumber); ?>').style.display = 'none'; document.getElementById('pipelineClose<?php echo($rowNumber); ?>').style.display = 'none'; document.getElementById('pipelineOpen<?php echo($rowNumber); ?>').style.display = '';">
                                    <img src="images/arrow_down.png" alt="" border="0" title="Hide History" />
                                </a>
                            </span>
                        </td>
                        <td valign="top">
                            <?php echo($pipelinesData['ratingLine']); ?>
                        </td>
                        <td valign="top">
                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php echo($pipelinesData['jobOrderID']); ?>" class="<?php $this->_($pipelinesData['linkClass']) ?>">
                                <?php $this->_($pipelinesData['title']) ?>
                            </a>
                        </td>
                        <td valign="top">
                            <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;companyID=<?php echo($pipelinesData['companyID']); ?>&amp;a=show">
                                <?php $this->_($pipelinesData['companyName']) ?>
                            </a>
                        </td>
                        <td valign="top"><?php $this->_($pipelinesData['ownerAbbrName']) ?></td>
                        <td valign="top"><?php $this->_($pipelinesData['dateCreated']) ?></td>
                        <td valign="top"><?php $this->_($pipelinesData['addedByAbbrName']) ?></td>
                        <td valign="top" nowrap="nowrap"><?php $this->_($pipelinesData['status']) ?></td>
<?php if (!$this->isPopup): ?>
                        <td align="center" nowrap="nowrap">
                            <?php eval(Hooks::get('CANDIDATE_TEMPLATE_SHOW_PIPELINE_ACTION')); ?>
                            <?php if ($this->getUserAccessLevel('pipelines.screening') >= ACCESS_LEVEL_EDIT && !$_SESSION['CATS']->hasUserCategory('sourcer')): ?>
                                <?php if ($pipelinesData['ratingValue'] < 0): ?>
                                    <a href="#" id="screenLink<?php echo($pipelinesData['candidateJobOrderID']); ?>" onclick="moImageValue<?php echo($pipelinesData['candidateJobOrderID']); ?> = 0; setRating(<?php echo($pipelinesData['candidateJobOrderID']); ?>, 0, 'moImage<?php echo($pipelinesData['candidateJobOrderID']); ?>', '<?php echo($_SESSION['CATS']->getCookie()); ?> '); return false;">
                                        <img id="screenImage<?php echo($pipelinesData['candidateJobOrderID']); ?>" src="images/actions/screen.gif" width="16" height="16" class="absmiddle" alt="" border="0" title="Mark as Screened" />
                                    </a>
                                <?php else: ?>
                                    <img src="images/actions/blank.gif" width="16" height="16" class="absmiddle" alt="" border="0" />
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($this->getUserAccessLevel('pipelines.addActivityChangeStatus') >= ACCESS_LEVEL_EDIT): ?>
                                <a href="#" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addActivityChangeStatus&amp;candidateID=<?php echo($this->candidateID); ?>&amp;jobOrderID=<?php echo($pipelinesData['jobOrderID']); ?>', 600, 480, null); return false;" >
                                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="" border="0" title="Log an Activity / Change Status"/>
                                </a>
                            <?php endif; ?>
                            <?php if ($this->getUserAccessLevel('pipelines.removeFromPipeline') >= ACCESS_LEVEL_DELETE): ?>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=removeFromPipeline&amp;candidateID=<?php echo($this->candidateID); ?>&amp;jobOrderID=<?php echo($pipelinesData['jobOrderID']); ?>"  onclick="javascript:return confirm('Delete from <?php $this->_(str_replace('\'', '\\\'', $pipelinesData['title'])); ?> (<?php $this->_(str_replace('\'', '\\\'', $pipelinesData['companyName'])); ?>) pipeline?')">
                                    <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="" border="0" title="Remove from Pipeline"/>
                                </a>
                            <?php endif; ?>
                        </td>
<?php endif; ?>
                    </tr>
                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>" id="pipelineDetails<?php echo($rowNumber); ?>" style="display:none;">
                        <td colspan="11" align="center">
                            <table width="98%" border="1" class="detailsOutside" style="margin: 5px;">
                                <tr>
                                    <td align="left" style="padding: 6px 6px 6px 6px; background-color: white; clear: both;">
                                        <div style="overflow: auto; height: 200px;" id="pipelineInner<?php echo($rowNumber); ?>">
                                            <img src="images/indicator.gif" alt="" />&nbsp;&nbsp;Loading pipeline details...
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                <?php endforeach; ?>
            </table>

<?php if (!$this->isPopup): ?>
            <?php if ($this->getUserAccessLevel('candidates.considerForJobSearch') >= ACCESS_LEVEL_EDIT): ?>
                <a href="#" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=considerForJobSearch&amp;candidateID=<?php echo($this->candidateID); ?>', 750, 390, null); return false;">
                    <img src="images/consider.gif" width="16" height="16" class="absmiddle" alt="Add to Pipeline" border="0" />&nbsp;Add This Candidate to Job Order Pipeline
                </a>
            <?php endif; ?>
<?php endif; ?>
            <br clear="all" />
            <br />

            <p class="note">Activity</p>

            <table id="activityTable" class="sortable">
                <tr>
                    <th align="left" width="125">Date</th>
                    <th align="left" width="90">Type</th>
                    <th align="left" width="90">Entered</th>
                    <th align="left" width="250">Regarding</th>
                    <th align="left">Notes</th>
<?php if (!$this->isPopup): ?>
                    <th align="left" width="40">Action</th>
<?php endif; ?>
                </tr>

                <?php foreach ($this->activityRS as $rowNumber => $activityData): ?>
                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                        <td align="left" valign="top" id="activityDate<?php echo($activityData['activityID']); ?>"><?php $this->_($activityData['dateCreated']) ?></td>
                        <td align="left" valign="top" id="activityType<?php echo($activityData['activityID']); ?>"><?php $this->_($activityData['typeDescription']) ?></td>
                        <td align="left" valign="top"><?php $this->_($activityData['enteredByAbbrName']) ?></td>
                        <td align="left" valign="top" id="activityRegarding<?php echo($activityData['activityID']); ?>"><?php $this->_($activityData['regarding']) ?></td>
                        <td align="left" valign="top" id="activityNotes<?php echo($activityData['activityID']); ?>"><?php echo($activityData['notes']); ?></td>
<?php if (!$this->isPopup): ?>
                        <td align="center" >
                            <?php if ($this->getUserAccessLevel('candidates.edit') >= ACCESS_LEVEL_EDIT): ?>
                                <a href="#" id="editActivity<?php echo($activityData['activityID']); ?>" onclick="Activity_editEntry(<?php echo($activityData['activityID']); ?>, <?php echo($this->candidateID); ?>, <?php echo(DATA_ITEM_CANDIDATE); ?>, '<?php echo($this->sessionCookie); ?>'); return false;">
                                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="" border="0" title="Edit" />
                                </a>
                            <?php endif; ?>
                            <?php if ($this->getUserAccessLevel('candidates.delete') >= ACCESS_LEVEL_DELETE): ?>
                                <a href="#" id="deleteActivity<?php echo($activityData['activityID']); ?>" onclick="Activity_deleteEntry(<?php echo($activityData['activityID']); ?>, '<?php echo($this->sessionCookie); ?>'); return false;">
                                    <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="" border="0" title="Delete" />
                                </a>
                            <?php endif; ?>
                        </td>
<?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </table>
<?php if (!$this->isPopup): ?>
            <div id="addActivityDiv">
                <?php if ($this->getUserAccessLevel('pipelines.addActivityChangeStatus') >= ACCESS_LEVEL_EDIT): ?>
                    <a href="#" id="addActivityLink" onclick="showPopWin('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addActivityChangeStatus&amp;candidateID=<?php echo($this->candidateID); ?>&amp;jobOrderID=-1', 600, 480, null); return false;">
                        <img src="images/new_activity_inline.gif" width="16" height="16" class="absmiddle" title="Log an Activity / Change Status" alt="Log an Activity / Change Status" border="0" />&nbsp;Log an Activity
                    </a>
                <?php endif; ?>
                <img src="images/indicator2.gif" id="addActivityIndicator" alt="" style="visibility: hidden; margin-left: 5px;" height="16" width="16" />
            </div>
        </div>
    </div>

<?php endif; ?>
	
<?php TemplateUtility::printFooter(); ?>
