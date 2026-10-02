<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */
?>
<?php
include_once(LEGACY_ROOT . '/vendor/autoload.php');
use OpenCATS\UI\CandidateQuickActionMenu;
use OpenCATS\UI\CandidateDuplicateQuickActionMenu;
?>
<?php if ($this->isPopup): ?>
    <?php TemplateUtility::printHeader('Candidate - ' . $this->data['firstName'] . ' ' . $this->data['lastName'], array( 'js/activity.js', 'js/sorttable.js', 'js/match.js', 'js/lib.js', 'js/pipeline.js', 'js/attachment.js', 'modules/candidates/quickAction-candidates.js')); ?>
<?php else: ?>
    <?php TemplateUtility::printHeader('Candidate - ' . $this->data['firstName'] . ' ' . $this->data['lastName'], array( 'js/activity.js', 'js/sorttable.js', 'js/match.js', 'js/lib.js', 'js/pipeline.js', 'js/attachment.js', 'modules/candidates/quickAction-candidates.js', 'modules/candidates/quickAction-duplicates.js')); ?>

    <?php TemplateUtility::printHeaderBlock(); ?>
    <?php TemplateUtility::printTabs($this->active); ?>
            <?php TemplateUtility::printQuickSearch(); ?>
<?php endif; ?>

        <script>
            window.CATSUserDateFormat  = <?php echo Template::escapeJs($_SESSION['CATS']->isDateDMY() ? 'DD-MM-YY' : 'MM-DD-YY'); ?>;
            window.CATSTimeFormat24    = <?php echo $_SESSION['CATS']->isTimeFormat24() ? 'true' : 'false'; ?>;
        </script>

<main id="main" class="container-fluid py-2 oc-candidate-show-page">
        <div id="contents">
            <header class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Candidates: Candidate Details
                        <?php if($_SESSION['CATS']->getAccessLevel('candidates.duplicates') >= ACCESS_LEVEL_SA): ?>
                            <?php if(!empty($this->data['isDuplicate'])): ?>
                                <img src="images/wf_error.gif" alt="duplicate_warning" width="20" height="20" title="Possible duplicate">
                                <?php foreach($this->data['isDuplicate'] as $item): ?>
                                    <a href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=candidates&a=show&candidateID=' . $item['duplicateTo']); ?>" target="_blank">Duplicate</a>
                                    <?php TemplateUtility::printSingleQuickActionMenu(new CandidateDuplicateQuickActionMenu(
                                        DATA_ITEM_DUPLICATE,
                                        $this->data['candidateID'],
                                        $_SESSION['CATS']->getAccessLevel('candidates.duplicates'),
                                        urlencode(CATSUtility::getIndexName().'?m=candidates&a=merge&oldCandidateID='.$item['duplicateTo'].'&newCandidateID='.$this->data['candidateID']),
                                        urlencode(CATSUtility::getIndexName().'?m=candidates&a=removeDuplicity&oldCandidateID='.$item['duplicateTo'].'&newCandidateID='.$this->data['candidateID']
                                    ))); ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </h1></header>

            <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">Candidate Details</h2>

            <?php if ($this->data['isAdminHidden'] == 1): ?>
                <div class="alert alert-warning py-2">
                    This Candidate is hidden.  Only Site Administrators can view it or search for it.  To make it visible by the site users, click
                    <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=administrativeHideShow" style="display:inline;">
                        <input type="hidden" name="postback" value="postback">
                        <input type="hidden" name="candidateID" value="<?php echo Template::escapeAttr($this->candidateID); ?>">
                        <input type="hidden" name="state" value="0">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Here.</button>
                    </form>
                </div>
            <?php endif; ?>

            <div class="row g-3 mb-2">

                    <?php $profileImage = false; ?>
                    <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData): ?>
                         <?php if ($attachmentsData['isProfileImage'] == '1'): ?>
                             <?php $profileImage = true; ?>
                         <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($profileImage): ?>
                        <div class="col-12 col-lg">
                    <?php else: ?>
                        <div class="col-12 col-lg">
                    <?php endif; ?>
                        <dl class="card card-body p-2 mb-2">
                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Name:</dt>
                                <dd class="col-sm-8 mb-0">
                                    <span class="<?php echo Template::escapeAttr($this->data['titleClass']); ?>">
                                        <?php $this->_($this->data['firstName']); ?>
                                        <?php $this->_($this->data['middleName']); ?>
                                        <?php $this->_($this->data['lastName']); ?>
                                        <?php if ($this->data['isActive'] != 1): ?>
                                            &nbsp;<span>(INACTIVE)</span>
                                        <?php endif; ?>
                                        <?php TemplateUtility::printSingleQuickActionMenu(new CandidateQuickActionMenu(DATA_ITEM_CANDIDATE, $this->data['candidateID'], $_SESSION['CATS']->getAccessLevel('candidates.edit'))); ?>
                                    </span>
                                </dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">E-Mail:</dt>
                                <dd class="col-sm-8 mb-0">
                                    <a href="<?php echo Template::escapeUrl('mailto:' . $this->data['email1']); ?>">
                                        <?php $this->_($this->data['email1']); ?>
                                    </a>
                                </dd>
                            </div>
                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">2nd E-Mail:</dt>
                                <dd class="col-sm-8 mb-0">
                                    <a href="<?php echo Template::escapeUrl('mailto:' . $this->data['email2']); ?>">
                                        <?php $this->_($this->data['email2']); ?>
                                    </a>
                                </dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Home Phone:</dt>
                                <dd class="col-sm-8 mb-0"><?php $this->_($this->data['phoneHome']); ?></dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Cell Phone:</dt>
                                <dd class="col-sm-8 mb-0"><?php $this->_($this->data['phoneCell']); ?></dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Work Phone:</dt>
                                <dd class="col-sm-8 mb-0"><?php $this->_($this->data['phoneWork']); ?></dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Best Time To Call:</dt>
                                <dd class="col-sm-8 mb-0"><?php $this->_($this->data['bestTimeToCall']); ?></dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Address:</dt>
                                <dd class="col-sm-8 mb-0">
                                    <?php echo nl2br(Template::escapeHtml($this->data['address'])); ?>
                                    <?php if (!empty($this->data['address2'])): ?>
                                        <br><?php $this->_($this->data['address2']); ?>
                                    <?php endif; ?>
                                </dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">&nbsp;</dt>
                                <dd class="col-sm-8 mb-0">
                                    <?php $this->_($this->data['cityAndState']); ?>
                                    <?php $this->_($this->data['zip']); ?>
                                </dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Web Site:</dt>
                                <dd class="col-sm-8 mb-0">
                                    <?php if (!empty($this->data['webSite'])): ?>
                                        <a href="<?php echo Template::escapeUrl($this->data['webSite']); ?>" target="_blank"><?php $this->_($this->data['webSite']); ?></a>
                                    <?php endif; ?>
                                </dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Source:</dt>
                                <dd class="col-sm-8 mb-0"><?php $this->_($this->data['source']); ?></dd>
                            </div>

                            <?php for ($i = 0; $i < intval(count($this->extraFieldRS)/2); $i++): ?>
                                <div class="row g-2 mb-1">
                                    <dt class="col-sm-4 small fw-semibold"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</dt>
                                    <dd class="col-sm-8 mb-0"><?php echo($this->extraFieldRS[$i]['display']); ?></dd>
                                </div>
                            <?php endfor; ?>

                            <div class="row g-2 mb-1">

                            </div>
                        </dl>
                    </div>

                    <?php if ($profileImage): ?>
                        <div class="col-12 col-lg">
                    <?php else: ?>
                        <div class="col-12 col-lg">
                    <?php endif; ?>
                        <dl class="card card-body p-2 mb-2">
                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Date Available:</dt>
                                <dd class="col-sm-8 mb-0"><?php $this->_($this->data['dateAvailable']); ?></dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Current Employer:</dt>
                                <dd class="col-sm-8 mb-0"><?php $this->_($this->data['currentEmployer']); ?></dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Key Skills:</dt>
                                <dd class="col-sm-8 mb-0"><?php $this->_($this->data['keySkills']); ?></dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Can Relocate:</dt>
                                <dd class="col-sm-8 mb-0"><?php $this->_($this->data['canRelocate']); ?></dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Current Pay:</dt>
                                <dd class="col-sm-8 mb-0"><?php $this->_($this->data['currentPay']); ?></dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Desired Pay:</dt>
                                <dd class="col-sm-8 mb-0"><?php $this->_($this->data['desiredPay']); ?></dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Pipeline:</dt>
                                <dd class="col-sm-8 mb-0"><?php $this->_($this->data['pipeline']); ?></dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Submitted:</dt>
                                <dd class="col-sm-8 mb-0"><?php $this->_($this->data['submitted']); ?></dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Created:</dt>
                                <dd class="col-sm-8 mb-0"><?php $this->_($this->data['dateCreated']); ?> (<?php $this->_($this->data['enteredByFullName']); ?>)</dd>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Owner:</dt>
                                <dd class="col-sm-8 mb-0"><?php $this->_($this->data['ownerFullName']); ?></dd>
                            </div>

                            <?php for ($i = (intval(count($this->extraFieldRS))/2); $i < (count($this->extraFieldRS)); $i++): ?>
                                <div class="row g-2 mb-1">
                                    <dt class="col-sm-4 small fw-semibold"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</dt>
                                    <dd class="col-sm-8 mb-0"><?php echo($this->extraFieldRS[$i]['display']); ?></dd>
                                </div>
                            <?php endfor; ?>
                        </dl>
                    </div>
                    <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData): ?>
                         <?php if ($attachmentsData['isProfileImage'] == '1'): ?>
                            <div class="col-12 col-lg">
                                <dl class="card card-body p-2 mb-2">
                                    <div class="row g-2 mb-1">
                                        <dt class="col-sm-4 small fw-semibold">
                                            <?php if (!$this->isPopup): ?>
                                                <?php if ($this->getUserAccessLevel('candidates.deleteAttachment') >= ACCESS_LEVEL_DELETE): ?>
                                                    <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=deleteAttachment" style="display:inline;" onsubmit="return confirm('Delete this attachment?');">
                                                        <input type="hidden" name="postback" value="postback">
                                                        <input type="hidden" name="candidateID" value="<?php echo Template::escapeAttr($this->candidateID); ?>">
                                                        <input type="hidden" name="attachmentID" value="<?php $this->_($attachmentsData['attachmentID']) ?>">
                                                        <input type="image" src="images/actions/delete.gif" alt="Delete attachment" width="16" height="16" title="Delete">
                                                    </form>
                                                <?php endif; ?>
                                            <?php else: ?>

                                            <?php endif; ?>
                                            Picture:
                                        </dt>
                                    </div>
                                    <div class="row g-2 mb-1">
                                        <dd class="col-sm-8 mb-0">
                                            <a href="<?php echo Template::escapeUrl($attachmentsData['retrievalURL']); ?>">
                                                <img src="<?php echo Template::escapeUrl($attachmentsData['retrievalURL']); ?>" alt="" width="125">
                                            </a>
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                         <?php endif; ?>
                    <?php endforeach; ?>

            </div>

            <?php if($this->EEOSettingsRS['enabled'] == 1): ?>
                <div class="row g-3 mb-2">

                        <div class="col-12 col-lg">
                            <dl class="card card-body p-2 mb-2">
                                <?php for ($i = 0; $i < intval(count($this->EEOValues)/2); $i++): ?>
                                    <div class="row g-2 mb-1">
                                        <dt class="col-sm-4 small fw-semibold"><?php $this->_($this->EEOValues[$i]['fieldName']); ?>:</dt>
                                        <?php if($this->EEOSettingsRS['canSeeEEOInfo']): ?>
                                            <dd class="col-sm-8 mb-0"><?php $this->_($this->EEOValues[$i]['fieldValue']); ?></dd>
                                        <?php else: ?>
                                            <dd class="col-sm-8 mb-0"><i><a href="javascript:void(0);" title="Ask an administrator to see the EEO info, or have permission granted to see it.">(Hidden)</a></i></dd>
                                        <?php endif; ?>
                                    </div>
                                <?php endfor; ?>
                            </dl>
                        </div>
                        <?php if ($profileImage): ?>
                            <div class="col-12 col-lg">
                        <?php else: ?>
                            <div class="col-12 col-lg">
                        <?php endif; ?>
                            <dl class="card card-body p-2 mb-2">
                                <?php for ($i = (intval(count($this->EEOValues))/2); $i < intval(count($this->EEOValues)); $i++): ?>
                                    <div class="row g-2 mb-1">
                                        <dt class="col-sm-4 small fw-semibold"><?php $this->_($this->EEOValues[$i]['fieldName']); ?>:</dt>
                                        <?php if($this->EEOSettingsRS['canSeeEEOInfo']): ?>
                                            <dd class="col-sm-8 mb-0"><?php $this->_($this->EEOValues[$i]['fieldValue']); ?></dd>
                                        <?php else: ?>
                                            <dd class="col-sm-8 mb-0"><i><a href="javascript:void(0);" title="Ask an administrator to see the EEO info, or have permission  granted to see it.">(Hidden)</a></i></dd>
                                        <?php endif; ?>
                                    </div>
                                <?php endfor; ?>
                            </dl>
                        </div>

                </div>
            <?php endif; ?>

            <div class="row g-3 mb-2">

                    <div class="col-12 col-lg">
                        <dl class="card card-body p-2 mb-2">
                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Misc. Notes:</dt>
                                <?php if ($this->isShortNotes): ?>
                                    <dd id="shortNotes" style="display:block;" class="col-sm-8 mb-0">
                                        <?php echo($this->data['shortNotes']); ?><span class="moreText">...</span>&nbsp;
                                        <p><a href="#" class="moreText" onclick="toggleNotes(); return false;">[More]</a></p>
                                    </dd>
                                    <dd id="fullNotes" style="display:none;" class="col-sm-8 mb-0">
                                        <?php echo($this->data['notes']); ?>&nbsp;
                                        <p><a href="#" class="moreText" onclick="toggleNotes(); return false;">[Less]</a></p>
                                    </dd>
                                <?php else: ?>
                                    <dd id="shortNotes" style="display:block;" class="col-sm-8 mb-0">
                                        <?php echo($this->data['notes']); ?>
                                    </dd>
                                <?php endif; ?>
                            </div>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Upcoming Events:</dt>
                                <dd class="col-sm-8 mb-0">
                                <?php foreach ($this->calendarRS as $rowNumber => $calendarData): ?>
                                    <div>
                                        <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=calendar&amp;view=DAYVIEW&amp;month=<?php echo($calendarData['month']); ?>&amp;year=20<?php echo($calendarData['year']); ?>&amp;day=<?php echo($calendarData['day']); ?>&amp;showEvent=<?php echo($calendarData['eventID']); ?>">
                                            <img src="<?php $this->_($calendarData['typeImage']) ?>" alt="">
                                            <?php $this->_($calendarData['dateShow']) ?>:
                                            <?php $this->_($calendarData['title']); ?>
                                        </a>
                                    </div>
                                <?php endforeach; ?>
                                <?php if ($this->getUserAccessLevel('pipelines.addActivity') >= ACCESS_LEVEL_EDIT): ?>
                                    <a href="#" onclick="showPopWin(<?php echo Template::escapeJsAttr(CATSUtility::getIndexName() . '?m=candidates&a=addActivity&candidateID=' . $this->candidateID . '&jobOrderID=-1&onlyScheduleEvent=true'); ?>, 600, 350, null); return false;">
                                        <img src="images/calendar_add.gif" width="16" height="16" alt="Schedule Event" class="absmiddle">&nbsp;Schedule Event
                                    </a>
                                <?php endif; ?>
                                </dd>
                            </div>

                            <?php if (isset($this->questionnaires) && !empty($this->questionnaires)): ?>
                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Questionnaires:</dt>
                                <dd class="col-sm-8 mb-0">
                                    <div class="table-responsive"><table class="table table-sm table-striped table-hover align-middle mb-0"><tbody>
                                    <tr>
                                        <td>Title (Internal)</td>
                                        <td>Completed</td>
                                        <td>Description (Public)</td>
                                    </tr>
                                    <?php foreach ($this->questionnaires as $questionnaire): ?>
                                    <tr>
                                        <td><a href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=candidates&a=show_questionnaire&candidateID=' . $this->candidateID . '&questionnaireTitle=' . urlencode($questionnaire['questionnaireTitle']) . '&print=no'); ?>"><?php echo Template::escapeHtml($questionnaire['questionnaireTitle']); ?></a></td>
                                        <td><?php echo date('F j. Y', strtotime($questionnaire['questionnaireDate'])); ?></td>
                                        <td><?php echo Template::escapeHtml($questionnaire['questionnaireDescription']); ?></td>
                                        <td>
                                            <a class="btn btn-sm btn-outline-secondary" href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=candidates&a=show_questionnaire&candidateID=' . $this->candidateID . '&questionnaireTitle=' . urlencode($questionnaire['questionnaireTitle']) . '&print=no'); ?>">
                                                <img src="images/actions/view.gif" width="16" height="16" class="absmiddle" alt="view">&nbsp;View
                                            </a>
                                            &nbsp;
                                            <a class="btn btn-sm btn-outline-secondary" href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=candidates&a=show_questionnaire&candidateID=' . $this->candidateID . '&questionnaireTitle=' . urlencode($questionnaire['questionnaireTitle']) . '&print=yes'); ?>">
                                                <img src="images/actions/print.gif" width="16" height="16" class="absmiddle" alt="print">&nbsp;Print
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    </tbody></table></div>
                                </dd>
                            </div>
                            <?php endif; ?>

                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Attachments:</dt>
                                <dd class="col-sm-8 mb-0">
                                    <div class="table-responsive"><table class="table table-sm table-striped table-hover align-middle mb-0 attachmentsTable"><tbody>
                                        <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData): ?>
                                            <?php if ($attachmentsData['isProfileImage'] != '1'): ?>
                                                <tr>
                                                    <td>
                                                        <?php echo $attachmentsData['retrievalLink']; ?>
                                                            <img src="<?php $this->_($attachmentsData['attachmentIcon']) ?>" alt="" width="16" height="16">
                                                            &nbsp;
                                                            <?php $this->_($attachmentsData['originalFilename']) ?>
                                                        </a>
                                                    </td>
                                                    <td><?php echo($attachmentsData['previewLink']); ?></td>
                                                    <td><?php $this->_($attachmentsData['dateCreated']) ?></td>
                                                    <td>
                                                        <?php if (!$this->isPopup): ?>
                                                            <?php if ($this->getUserAccessLevel('candidates.deleteAttachment') >= ACCESS_LEVEL_DELETE): ?>
                                                                <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=deleteAttachment" style="display:inline;" onsubmit="return confirm('Delete this attachment?');">
                                                                    <input type="hidden" name="postback" value="postback">
                                                                    <input type="hidden" name="candidateID" value="<?php echo Template::escapeAttr($this->candidateID); ?>">
                                                                    <input type="hidden" name="attachmentID" value="<?php $this->_($attachmentsData['attachmentID']) ?>">
                                                                    <input type="image" src="images/actions/delete.gif" alt="Delete attachment" width="16" height="16" title="Delete">
                                                                </form>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </tbody></table></div>
                                    <?php if (!$this->isPopup): ?>
                                        <?php if ($this->getUserAccessLevel('candidates.createAttachment') >= ACCESS_LEVEL_EDIT): ?>
                                            <?php if (isset($this->attachmentLinkHTML)): ?>
                                                <?php echo($this->attachmentLinkHTML); ?>
                                            <?php else: ?>
                                                <a href="#" onclick="showPopWin(<?php echo Template::escapeJsAttr(CATSUtility::getIndexName() . '?m=candidates&a=createAttachment&candidateID=' . $this->candidateID); ?>, 400, 125, null); return false;">
                                            <?php endif; ?>
                                                <img src="images/paperclip_add.gif" width="16" height="16" alt="Add Attachment" class="absmiddle">&nbsp;Add Attachment
                                            </a>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </dd>
                            </div>
                            <div class="row g-2 mb-1">
                                <dt class="col-sm-4 small fw-semibold">Tags:
                                    <?php if (!$this->isPopup){ ?>
                                        <?php if ($this->getUserAccessLevel('candidates.addCandidateTags') >= ACCESS_LEVEL_EDIT){ ?>
                                                <a href="#" onclick="showPopWin(<?php echo Template::escapeJsAttr(CATSUtility::getIndexName() . '?m=candidates&a=addCandidateTags&candidateID=' . $this->candidateID); ?>, 400, 125, null); return false;">
                                                Add/Remove
                                            </a>
                                        <?php } ?>
                                    <?php } ?>

                                </dt>
                                <dd class="col-sm-8 mb-0"><?php echo implode(', ', array_map(array('Template', 'escapeHtml'), $this->assignedTags)); ?>
                                </dd>
                            </div>
                        </dl>
                    </div>

            </div>
<?php if (!$this->isPopup): ?>
            <?php if ($this->getUserAccessLevel('candidates.edit') >= ACCESS_LEVEL_EDIT): ?>
                <a class="btn btn-sm btn-primary" id="edit_link" href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=candidates&a=edit&candidateID=' . $this->candidateID); ?>">
                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit">&nbsp;Edit
                </a>

            <?php endif; ?>
            <?php if ($this->getUserAccessLevel('candidates.delete') >= ACCESS_LEVEL_DELETE): ?>
                <form id="delete_link" method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=delete" style="display:inline;" onsubmit="return confirm('Delete this candidate?');">
                    <input type="hidden" name="postback" value="postback">
                    <input type="hidden" name="candidateID" value="<?php echo Template::escapeAttr($this->candidateID); ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="delete">&nbsp;Delete
                    </button>
                </form>

            <?php endif; ?>
            <?php if ($this->privledgedUser): ?>
                <a class="btn btn-sm btn-outline-secondary" id="history_link" href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=settings&a=viewItemHistory&dataItemType=100&dataItemID=' . $this->candidateID); ?>">
                    <img src="images/icon_clock.gif" width="16" height="16" class="absmiddle">&nbsp;View History
                </a>

            <?php endif; ?>
            <?php if ($this->getUserAccessLevel('candidates.administrativeHideShow') >= ACCESS_LEVEL_SA): ?>
                <?php if ($this->data['isAdminHidden'] == 1): ?>
                    <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=administrativeHideShow" style="display:inline;">
                        <input type="hidden" name="postback" value="postback">
                        <input type="hidden" name="candidateID" value="<?php echo Template::escapeAttr($this->candidateID); ?>">
                        <input type="hidden" name="state" value="0">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <img src="images/resume_preview_inline.gif" width="16" height="16" class="absmiddle" alt="delete">&nbsp;Administrative Show
                        </button>
                    </form>
                    <?php else: ?>
                    <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=administrativeHideShow" style="display:inline;">
                        <input type="hidden" name="postback" value="postback">
                        <input type="hidden" name="candidateID" value="<?php echo Template::escapeAttr($this->candidateID); ?>">
                        <input type="hidden" name="state" value="1">
                        <button type="submit" class="btn btn-sm btn-outline-secondary">
                            <img src="images/resume_preview_inline.gif" width="16" height="16" class="absmiddle" alt="delete">&nbsp;Administrative Hide
                        </button>
                    </form>
                <?php endif; ?>

            <?php endif; ?>
            <?php if ($this->getUserAccessLevel('candidates.duplicates') >= ACCESS_LEVEL_SA): ?>
                <a class="btn btn-sm btn-outline-secondary" href="#" onclick="showPopWin(<?php echo Template::escapeJsAttr(CATSUtility::getIndexName() . '?m=candidates&a=linkDuplicate&candidateID=' . $this->candidateID); ?>, 750, 390, null); return false;">
                    <img src="images/actions/duplicates.png" width="16" height="16" class="absmiddle" alt="add duplicate">&nbsp;Link duplicate
                </a>

            <?php endif; ?>
<?php endif; ?>
            <br>
            <br>

            <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">Job Orders for Candidates</h2>
            <div class="table-responsive"><table class="table table-sm table-striped table-hover align-middle mb-0 sortablepair">
                <thead><tr>
                    <th scope="col"></th>
                    <th scope="col">Match</th>
                    <th scope="col">Ref. Number</th>
                    <th scope="col">Title</th>
                    <th scope="col">Company</th>
                    <th scope="col">Owner</th>
                    <th scope="col">Added</th>
                    <th scope="col">Entered By</th>
                    <th scope="col">Status</th>
<?php if (!$this->isPopup): ?>
                    <th scope="col">Action</th>
<?php endif; ?>
                </tr></thead><tbody>

                <?php foreach ($this->pipelinesRS as $rowNumber => $pipelinesData): ?>
                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>" id="pipelineRow<?php echo($rowNumber); ?>">
                        <td>
                            <span id="pipelineOpen<?php echo($rowNumber); ?>">
                                <a href="javascript:void(0);" onclick="document.getElementById('pipelineDetails<?php echo($rowNumber); ?>').style.display=''; document.getElementById('pipelineClose<?php echo($rowNumber); ?>').style.display = ''; document.getElementById('pipelineOpen<?php echo($rowNumber); ?>').style.display = 'none'; PipelineDetails_populate(<?php echo($pipelinesData['candidateJobOrderID']); ?>, 'pipelineInner<?php echo($rowNumber); ?>', <?php echo Template::escapeJsAttr($this->sessionCookie); ?>);">
                                    <img src="images/arrow_next.png" alt="Show History" title="Show History">
                                </a>
                            </span>
                            <span id="pipelineClose<?php echo($rowNumber); ?>" style="display: none;">
                                <a href="javascript:void(0);" onclick="document.getElementById('pipelineDetails<?php echo($rowNumber); ?>').style.display = 'none'; document.getElementById('pipelineClose<?php echo($rowNumber); ?>').style.display = 'none'; document.getElementById('pipelineOpen<?php echo($rowNumber); ?>').style.display = '';">
                                    <img src="images/arrow_down.png" alt="Hide History" title="Hide History">
                                </a>
                            </span>
                        </td>
                        <td>
                            <?php echo($pipelinesData['ratingLine']); ?>
                        </td>
                        <td>
                            <?php $this->_($pipelinesData['clientJobID']) ?>
                        </td>
                        <td>
                            <a href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=joborders&a=show&jobOrderID=' . $pipelinesData['jobOrderID']); ?>" class="<?php echo Template::escapeAttr($pipelinesData['linkClass']); ?>">
                                <?php $this->_($pipelinesData['title']) ?>
                            </a>
                        </td>
                        <td>
                            <a href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=companies&companyID=' . $pipelinesData['companyID'] . '&a=show'); ?>">
                                <?php $this->_($pipelinesData['companyName']) ?>
                            </a>
                        </td>
                        <td><?php $this->_($pipelinesData['ownerAbbrName']) ?></td>
                        <td><?php $this->_($pipelinesData['dateCreated']) ?></td>
                        <td><?php $this->_($pipelinesData['addedByAbbrName']) ?></td>
                        <td><?php $this->_($pipelinesData['status']) ?></td>
<?php if (!$this->isPopup): ?>
                        <td>
                            <?php eval(Hooks::get('CANDIDATE_TEMPLATE_SHOW_PIPELINE_ACTION')); ?>
                            <?php if ($this->getUserAccessLevel('pipelines.screening') >= ACCESS_LEVEL_EDIT && !$_SESSION['CATS']->hasUserCategory('sourcer')): ?>
                                <?php if ($pipelinesData['ratingValue'] < 0): ?>
                                <a href="#" id="screenLink<?php echo($pipelinesData['candidateJobOrderID']); ?>" onclick="moImageValue<?php echo($pipelinesData['candidateJobOrderID']); ?> = 0; setRating(<?php echo($pipelinesData['candidateJobOrderID']); ?>, 0, 'moImage<?php echo($pipelinesData['candidateJobOrderID']); ?>', <?php echo Template::escapeJsAttr($_SESSION['CATS']->getCookie() . ' '); ?>); return false;">
                                    <img id="screenImage<?php echo($pipelinesData['candidateJobOrderID']); ?>" src="images/actions/screen.gif" width="16" height="16" class="absmiddle" alt="" title="Mark as Screened">
                                </a>
                                <?php else: ?>
                                    <img src="images/actions/blank.gif" width="16" height="16" class="absmiddle" alt="">
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if ($this->getUserAccessLevel('pipelines.changeStatus') >= ACCESS_LEVEL_EDIT): ?>
                                <a href="#" onclick="showPopWin(<?php echo Template::escapeJsAttr(CATSUtility::getIndexName() . '?m=candidates&a=changeStatus&candidateID=' . $this->candidateID . '&jobOrderID=' . $pipelinesData['jobOrderID']); ?>, 600, 430, null); return false;" >
                                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="" title="Change Status">
                                </a>
                            <?php endif; ?>
                            <?php if ($this->getUserAccessLevel('pipelines.removeFromPipeline') >= ACCESS_LEVEL_DELETE): ?>
                                <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=removeFromPipeline" style="display:inline;" onsubmit="return confirm('Delete from ' + <?php echo Template::escapeJsAttr($pipelinesData['title']); ?> + ' (' + <?php echo Template::escapeJsAttr($pipelinesData['companyName']); ?> + ') pipeline?')">
                                    <input type="hidden" name="postback" value="postback">
                                    <input type="hidden" name="candidateID" value="<?php echo Template::escapeAttr($this->candidateID); ?>">
                                    <input type="hidden" name="jobOrderID" value="<?php echo($pipelinesData['jobOrderID']); ?>">
                                    <input type="image" src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="" title="Remove from Job Order">
                                </form>
                            <?php endif; ?>
                        </td>
<?php endif; ?>
                    </tr>
                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>" id="pipelineDetails<?php echo($rowNumber); ?>" style="display:none;">
                        <td colspan="11">
                            <div class="row g-3 mb-2">

                                    <div class="col-12 col-lg">
                                        <div id="pipelineInner<?php echo($rowNumber); ?>">
                                            <img src="images/indicator.gif" alt=""> Loading pipeline details...
                                        </div>
                                    </div>

                            </div>
                        </td>
                    </tr>

                <?php endforeach; ?>
            </tbody></table></div>

<?php if (!$this->isPopup): ?>
            <?php if ($this->getUserAccessLevel('candidates.considerForJobSearch') >= ACCESS_LEVEL_EDIT): ?>
                <a href="#" onclick="showPopWin(<?php echo Template::escapeJsAttr(CATSUtility::getIndexName() . '?m=candidates&a=considerForJobSearch&candidateID=' . $this->candidateID); ?>, 750, 390, null); return false;">
                    <img src="images/consider.gif" width="16" height="16" class="absmiddle" alt="Add to Job Order">&nbsp;Add This Candidate to Job Order
                </a>
            <?php endif; ?>
<?php endif; ?>
            <br>
            <br>

            <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">Lists</h2>

            <div class="table-responsive"><table id="listsTable" class="table table-sm table-striped table-hover align-middle mb-0 sortable">
                <thead><tr>
                    <th scope="col">Name</th>
                </tr></thead><tbody>
                <?php foreach($this->lists as $rowNumber => $list): ?>
                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                        <td>
                            <a href="<?php echo Template::escapeUrl('index.php?m=lists&a=showList&savedListID=' . $list['listID']); ?>"><?php $this->_($list['name']); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody></table></div>

            <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2">Activity</h2>

            <div class="table-responsive"><table id="activityTable" class="table table-sm table-striped table-hover align-middle mb-0 sortable">
                <thead><tr>
                    <th scope="col">Date</th>
                    <th scope="col">Type</th>
                    <th scope="col">Regarding</th>
                    <th scope="col">Notes</th>
                    <th scope="col">Entered By</th>
<?php if (!$this->isPopup): ?>
                    <th scope="col">Action</th>
<?php endif; ?>
                </tr></thead><tbody>

                <?php foreach ($this->activityRS as $rowNumber => $activityData): ?>
                    <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                        <td id="activityDate<?php echo Template::escapeAttr($activityData['activityID']); ?>"><?php $this->_($activityData['dateCreated']) ?></td>
                        <td id="activityType<?php echo Template::escapeAttr($activityData['activityID']); ?>"><?php $this->_($activityData['typeDescription']) ?></td>
                        <td id="activityRegarding<?php echo Template::escapeAttr($activityData['activityID']); ?>" data-joborder-id="<?php echo Template::escapeAttr(isset($activityData['jobOrderID']) ? $activityData['jobOrderID'] : ''); ?>"><?php $this->_($activityData['regarding']) ?></td>
                        <td id="activityNotes<?php echo Template::escapeAttr($activityData['activityID']); ?>"><?php echo nl2br(TemplateUtility::highlightStatusChangeActivityNote($activityData['notes'])); ?></td>
                        <td><?php $this->_($activityData['enteredByAbbrName']) ?></td>
<?php if (!$this->isPopup): ?>
                        <td >
                            <?php if ($this->getUserAccessLevel('candidates.edit') >= ACCESS_LEVEL_EDIT): ?>
                                <a href="#" id="editActivity<?php echo Template::escapeAttr($activityData['activityID']); ?>" onclick="Activity_editEntry(<?php echo (int) $activityData['activityID']; ?>, <?php echo (int) $this->candidateID; ?>, <?php echo (int) DATA_ITEM_CANDIDATE; ?>, <?php echo Template::escapeJsAttr($this->sessionCookie); ?>); return false;">
                                    <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="" title="Edit">
                                </a>
                            <?php endif; ?>
                            <?php if ($this->getUserAccessLevel('candidates.delete') >= ACCESS_LEVEL_DELETE): ?>
                                <a href="#" id="deleteActivity<?php echo Template::escapeAttr($activityData['activityID']); ?>" onclick="Activity_deleteEntry(<?php echo (int) $activityData['activityID']; ?>, <?php echo Template::escapeJsAttr($this->sessionCookie); ?>); return false;">
                                    <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="" title="Delete">
                                </a>
                            <?php endif; ?>
                        </td>
<?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody></table></div>
<?php if (!$this->isPopup): ?>
            <div id="addActivityDiv">
                <?php if ($this->getUserAccessLevel('pipelines.addActivity') >= ACCESS_LEVEL_EDIT): ?>
                    <a href="#" id="addActivityLink" onclick="showPopWin(<?php echo Template::escapeJsAttr(CATSUtility::getIndexName() . '?m=candidates&a=addActivity&candidateID=' . $this->candidateID . '&jobOrderID=-1'); ?>, 600, 480, null); return false;">
                        <img src="images/new_activity_inline.gif" width="16" height="16" class="absmiddle" title="Log an Activity" alt="Log an Activity">&nbsp;Log an Activity
                    </a>
                <?php endif; ?>
                <img src="images/indicator2.gif" id="addActivityIndicator" alt="" style="visibility: hidden;" height="16" width="16">
            </div>
<?php endif; ?>
        </div>
</main>

<?php TemplateUtility::printFooter(); ?>
