<?php
include_once(LEGACY_ROOT . '/vendor/autoload.php');
use OpenCATS\UI\QuickActionMenu;
?>
<?php if ($this->isPopup): ?>
<?php TemplateUtility::printHeader('Job Order - ' . $this->data['title'], array('js/sorttable.js', 'js/match.js', 'js/pipeline.js', 'js/attachment.js')); ?>
<?php else: ?>
<?php TemplateUtility::printHeader('Job Order - ' . $this->data['title'], array( 'js/sorttable.js', 'js/match.js', 'js/pipeline.js', 'js/attachment.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<?php endif; ?>

<main id="main" class="container-fluid py-2 oc-joborder-show-page">
    <div id="contents">
        <section class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Job Orders: Job Order Details</h1></section>

        <?php if ($this->data['isAdminHidden'] == 1): ?>
        <div class="alert alert-warning py-2" role="alert">
            This Job Order is hidden.  Only Site Administrators can view it or search for it.  To make it visible by the site users, click
            <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=administrativeHideShow" style="display:inline;">
                <input type="hidden" name="postback" value="postback">
                <input type="hidden" name="jobOrderID" value="<?php echo Template::escapeAttr($this->jobOrderID); ?>">
                <input type="hidden" name="state" value="0">
                <button type="submit" class="btn btn-sm btn-link p-0">Here.</button>
            </form>
        </div>
        <?php endif; ?>

        <?php if (isset($this->frozen)): ?>
        <div id="candidateAlreadyInSystemTable" class="alert alert-warning py-2" role="alert">
            This Job Order is <?php $this->_($this->data['status']); ?> and can not be modified.
            <?php if ($this->getUserAccessLevel('joborders.edit') >= ACCESS_LEVEL_EDIT): ?>
            <a id="frozen_edit_link" class="btn btn-sm btn-outline-secondary" href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=joborders&a=edit&jobOrderID=' . $this->jobOrderID); ?>">
            <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit">&nbsp;Edit
            </a>
            the Job Order to make it Active.&nbsp;&nbsp;
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <section class="card mb-2 oc-joborder-details">
            <div class="card-body p-2">
                <div class="row g-2 mb-2">
                    <div class="col-12 col-lg-6">
                        <div class="oc-joborder-detail-fields">
                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Title:</div>
                                <div class="col-sm-8">
                                    <span class="<?php echo Template::escapeAttr($this->data['titleClass']); ?>"><?php $this->_($this->data['title']); ?></span>
                                    <?php echo($this->data['public']) ?>
                                    <?php TemplateUtility::printSingleQuickActionMenu(new QuickActionMenu(DATA_ITEM_JOBORDER, $this->data['jobOrderID'], $_SESSION['CATS']->getAccessLevel('joborders.edit'))); ?>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Company Name:</div>
                                <div class="col-sm-8">
                                    <a href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=companies&a=show&companyID=' . $this->data['companyID']); ?>">
                                    <?php $this->_($this->data['companyName']); ?>
                                    </a>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Department:</div>
                                <div class="col-sm-8">
                                    <?php $this->_($this->data['department']); ?>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">CATS Job ID:</div>
                                <div class="col-sm-8"><?php $this->_($this->data['jobOrderID']); ?></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Company Job ID:</div>
                                <div class="col-sm-8"><?php $this->_($this->data['companyJobID']); ?></div>
                            </div>

                            <!-- CONTACT INFO -->
                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Contact Name:</div>
                                <div class="col-sm-8">
                                    <a href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=contacts&a=show&contactID=' . $this->data['contactID']); ?>">
                                    <?php $this->_($this->data['contactFullName']); ?>
                                    </a>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Contact Phone:</div>
                                <div class="col-sm-8"><?php $this->_($this->data['contactWorkPhone']); ?></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Contact Email:</div>
                                <div class="col-sm-8">
                                    <a href="<?php echo Template::escapeUrl('mailto:' . $this->data['contactEmail']); ?>"><?php $this->_($this->data['contactEmail']); ?></a>
                                </div>
                            </div>
                            <!-- /CONTACT INFO -->

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Location:</div>
                                <div class="col-sm-8"><?php $this->_($this->data['cityAndState']); ?></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Max Rate:</div>
                                <div class="col-sm-8"><?php $this->_($this->data['maxRate']); ?></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Salary:</div>
                                <div class="col-sm-8"><?php $this->_($this->data['salary']); ?></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Start Date:</div>
                                <div class="col-sm-8"><?php $this->_($this->data['startDate']); ?></div>
                            </div>

                            <?php for ($i = 0; $i < intval(count($this->extraFieldRS)/2); $i++): ?>
                            <?php if(($this->extraFieldRS[$i]['extraFieldType']) != EXTRA_FIELD_TEXTAREA): ?>
                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</div>
                                <div class="col-sm-8"><?php echo($this->extraFieldRS[$i]['display']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php endfor; ?>

                            <?php eval(Hooks::get('JO_TEMPLATE_SHOW_BOTTOM_OF_LEFT')); ?>

                        </div>
                    </div>

                    <div class="col-12 col-lg-6">
                        <div class="oc-joborder-detail-fields">
                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Duration:</div>
                                <div class="col-sm-8"><?php $this->_($this->data['duration']); ?></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Openings:</div>
                                <div class="col-sm-8"><?php $this->_($this->data['openings']); if ($this->data['openingsAvailable'] != $this->data['openings']): ?> (<?php $this->_($this->data['openingsAvailable']); ?> Available)<?php endif; ?></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Type:</div>
                                <div class="col-sm-8"><?php $this->_($this->data['typeDescription']); ?></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Status:</div>
                                <div class="col-sm-8"><?php $this->_($this->data['status']); ?></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Candidates:</div>
                                <div class="col-sm-8"><?php $this->_($this->data['pipeline']) ?></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Submitted:</div>
                                <div class="col-sm-8"><?php $this->_($this->data['submitted']) ?></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Days Old:</div>
                                <div class="col-sm-8"><?php $this->_($this->data['daysOld']); ?></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Created:</div>
                                <div class="col-sm-8"><?php $this->_($this->data['dateCreated']); ?> (<?php $this->_($this->data['enteredByFullName']); ?>)</div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Recruiter:</div>
                                <div class="col-sm-8"><?php $this->_($this->data['recruiterFullName']); ?></div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Owner:</div>
                                <div class="col-sm-8"><?php $this->_($this->data['ownerFullName']); ?></div>
                            </div>

                            <?php for ($i = (intval(count($this->extraFieldRS))/2); $i < (count($this->extraFieldRS)); $i++): ?>
                            <?php if(($this->extraFieldRS[$i]['extraFieldType']) != EXTRA_FIELD_TEXTAREA): ?>
                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</div>
                                <div class="col-sm-8"><?php echo($this->extraFieldRS[$i]['display']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php endfor; ?>

                            <?php eval(Hooks::get('JO_TEMPLATE_SHOW_BOTTOM_OF_RIGHT')); ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <?php if ($this->isPublic): ?>
        <div class="alert alert-info py-2" role="status">
            <b>This job order is public<?php if ($this->careerPortalURL === false): ?>.</b><?php else: ?>
            and will be shown on your
            <?php if ($this->getUserAccessLevel('joborders.careerPortalUrl') >= ACCESS_LEVEL_SA): ?>
            <a style="font-weight: bold;" href="<?php echo Template::escapeUrl($this->careerPortalURL); ?>">Careers Website</a>.
            <?php else: ?>
            Careers Website.
            <?php endif; ?></b>
            <?php endif; ?>

            <?php if ($this->questionnaireID !== false): ?>
            <br>Applicants must complete the "<i><?php $this->_($this->questionnaireData['title']); ?></i>" (<a href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=settings&a=careerPortalQuestionnaire&questionnaireID=' . $this->questionnaireID); ?>">edit</a>) questionnaire when applying.
            <?php else: ?>
            <br>You have not attached any
            <?php if ($this->getUserAccessLevel('setting.carrerPortalSettings') >= ACCESS_LEVEL_SA): ?>
            <a href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=settings&a=careerPortalSettings'); ?>">Questionnaires</a>.
            <?php else: ?>
            Questionnaires.
            <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <section class="card mb-2 oc-joborder-details">
            <div class="card-body p-2">
                <div class="row g-2 mb-2">
                    <div class="col-12">
                        <div class="oc-joborder-detail-fields">
                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Attachments:</div>
                                <div class="col-sm-8">
                                    <div class="table-responsive">
                                        <table class="attachmentsTable table table-sm align-middle mb-2">
                                            <?php foreach ($this->attachmentsRS as $rowNumber => $attachmentsData): ?>
                                            <tr>
                                                <td>
                                                    <?php echo $attachmentsData['retrievalLink']; ?>
                                                    <img src="<?php echo Template::escapeUrl($attachmentsData['attachmentIcon']); ?>" alt="" width="16" height="16">
                                                    &nbsp;
                                                    <?php $this->_($attachmentsData['originalFilename']) ?>
                                                    </a>
                                                </td>
                                                <td><?php $this->_($attachmentsData['dateCreated']) ?></td>
                                                <td>
                                                    <?php if (!$this->isPopup): ?>
                                                    <?php if ($this->getUserAccessLevel('joborders.deleteAttachment') >= ACCESS_LEVEL_DELETE): ?>
                                                    <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=deleteAttachment" style="display:inline;" onsubmit="return confirm('Delete this attachment?');">
                                                        <input type="hidden" name="postback" value="postback">
                                                        <input type="hidden" name="jobOrderID" value="<?php echo Template::escapeAttr($this->jobOrderID); ?>">
                                                        <input type="hidden" name="attachmentID" value="<?php echo Template::escapeAttr($attachmentsData['attachmentID']); ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" aria-label="Delete attachment">Delete</button>
                                                    </form>
                                                    <?php endif; ?>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </table>
                                    </div>
                                    <?php if (!$this->isPopup): ?>
                                    <?php if ($this->getUserAccessLevel('joborders.createAttachment') >= ACCESS_LEVEL_EDIT): ?>
                                    <?php if (isset($this->attachmentLinkHTML)): ?>
                                    <?php echo($this->attachmentLinkHTML); ?>
                                    <?php else: ?>
                                    <a class="btn btn-sm btn-outline-secondary" href="#" onclick="showPopWin(<?php echo Template::escapeJsAttr(CATSUtility::getIndexName() . '?m=joborders&a=createAttachment&jobOrderID=' . $this->jobOrderID); ?>, 400, 125, null); return false;">
                                    <?php endif; ?>
                                    <img src="images/paperclip_add.gif" width="16" height="16" alt="add attachment" class="absmiddle">&nbsp;Add Attachment
                                    </a>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Description:</div>

                                <div class="col-sm-8">
                                    <?php if($this->data['description'] != ''): ?>
                                    <div id="shortDescription" class="overflow-auto border rounded p-2" style="height: 170px;">
                                        <?php echo($this->data['description']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>

                            </div>

                            <?php for ($i = (intval(count($this->extraFieldRS))/2); $i < (count($this->extraFieldRS)); $i++): ?>
                            <?php if(($this->extraFieldRS[$i]['extraFieldType']) == EXTRA_FIELD_TEXTAREA): ?>
                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</div>
                                <div class="col-sm-8"><?php echo($this->extraFieldRS[$i]['display']); ?></div>
                            </div>
                            <?php endif; ?>
                            <?php endfor; ?>

                            <div class="row g-2 mb-2">
                                <div class="col-sm-4 fw-semibold small">Internal Notes:</div>

                                <div class="col-sm-8">
                                    <?php if($this->data['notes'] != ''): ?>
                                    <div id="shortNotes" class="overflow-auto border rounded p-2" style="height: 240px;">
                                        <?php echo($this->data['notes']); ?>
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-12 oc-joborder-pipeline-graph">
                                    <?php echo($this->pipelineGraph);  ?>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <?php if (!$this->isPopup): ?>
        <div id="actionbar" class="d-flex flex-wrap justify-content-between gap-2 mb-3 oc-joborder-actions">
            <span class="d-flex flex-wrap align-items-center gap-2">
            <?php if ($this->getUserAccessLevel('joborders.edit') >= ACCESS_LEVEL_EDIT): ?>
            <a id="edit_link" class="btn btn-sm btn-primary" href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=joborders&a=edit&jobOrderID=' . $this->jobOrderID); ?>">
            <img src="images/actions/edit.gif" width="16" height="16" class="absmiddle" alt="edit">&nbsp;Edit
            </a>

            <?php endif; ?>
            <?php if ($this->getUserAccessLevel('joborders.delete') >= ACCESS_LEVEL_DELETE): ?>
            <form id="delete_link" method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=delete" style="display:inline;" onsubmit="return confirm('Delete this job order?');">
                <input type="hidden" name="postback" value="postback">
                <input type="hidden" name="jobOrderID" value="<?php echo Template::escapeAttr($this->jobOrderID); ?>">
                <button type="submit" class="btn btn-sm btn-outline-danger">
                <img src="images/actions/delete.gif" width="16" height="16" class="absmiddle" alt="delete">&nbsp;Delete
                </button>
            </form>

            <?php endif; ?>
            <?php if ($this->getUserAccessLevel('joborders.hidden') >= ACCESS_LEVEL_SA): ?>
            <?php if ($this->data['isAdminHidden'] == 1): ?>
            <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=administrativeHideShow" style="display:inline;">
                <input type="hidden" name="postback" value="postback">
                <input type="hidden" name="jobOrderID" value="<?php echo Template::escapeAttr($this->jobOrderID); ?>">
                <input type="hidden" name="state" value="0">
                <button type="submit" class="btn btn-sm btn-link p-0">
                <img src="images/resume_preview_inline.gif" width="16" height="16" class="absmiddle" alt="delete">&nbsp;Administrative Show
                </button>
            </form>
            <?php else: ?>
            <form method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=administrativeHideShow" style="display:inline;">
                <input type="hidden" name="postback" value="postback">
                <input type="hidden" name="jobOrderID" value="<?php echo Template::escapeAttr($this->jobOrderID); ?>">
                <input type="hidden" name="state" value="1">
                <button type="submit" class="btn btn-sm btn-link p-0">
                <img src="images/resume_preview_inline.gif" width="16" height="16" class="absmiddle" alt="delete">&nbsp;Administrative Hide
                </button>
            </form>
            <?php endif; ?>

            <?php endif; ?>
            </span>
            <span class="d-flex flex-wrap align-items-center gap-2">
            <?php if (!empty($this->data['public']) && $this->careerPortalEnabled): ?>
            <a id="public_link" class="btn btn-sm btn-outline-secondary" href="<?php echo Template::escapeUrl(CATSUtility::getAbsoluteURI() . 'careers/' . CATSUtility::getIndexName() . '?p=showJob&ID=' . $this->jobOrderID); ?>">
            <img src="images/public.gif" width="16" height="16" class="absmiddle" alt="Online Application">&nbsp;Online Application
            </a>

            <?php endif; ?>
            <?php /* TODO: Make report available for every site. */ ?>
            <a id="report_link" class="btn btn-sm btn-outline-secondary" href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=reports&a=customizeJobOrderReport&jobOrderID=' . $this->jobOrderID); ?>">
            <img src="images/reportsSmall.gif" width="16" height="16" class="absmiddle" alt="report">&nbsp;Generate Report
            </a>
            <?php if ($this->privledgedUser): ?>

            <a id="history_link" class="btn btn-sm btn-outline-secondary" href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=settings&a=viewItemHistory&dataItemType=400&dataItemID=' . $this->jobOrderID); ?>">
            <img src="images/icon_clock.gif" width="16" height="16" class="absmiddle">&nbsp;View History
            </a>
            <?php endif; ?>
            </span>
        </div>
        <?php endif; ?>
        <br>
        <br>

        <section class="card oc-joborder-pipeline">
            <div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">Candidate in Job Order</div>
            <div class="card-body p-2">

                <div id="ajaxPipelineControl" class="d-flex flex-wrap align-items-center gap-2 small mb-2">
                    <label for="numberOfEntriesSelect" class="mb-0">Number of visible entries:</label>
                    <select id="numberOfEntriesSelect" onchange="PipelineJobOrder_changeLimit(<?php $this->_($this->data['jobOrderID']); ?>, this.value, <?php if ($this->isPopup) echo(1); else echo(0); ?>, 'ajaxPipelineTable', <?php echo Template::escapeJsAttr($this->sessionCookie); ?>, 'ajaxPipelineTableIndicator', <?php echo Template::escapeJsAttr(CATSUtility::getIndexName()); ?>);" class="form-select form-select-sm w-auto">
                    <option value="15" <?php if ($this->pipelineEntriesPerPage == 15): ?>selected<?php endif; ?>>15 entries</option>
                    <option value="30" <?php if ($this->pipelineEntriesPerPage == 30): ?>selected<?php endif; ?>>30 entries</option>
                    <option value="50" <?php if ($this->pipelineEntriesPerPage == 50): ?>selected<?php endif; ?>>50 entries</option>
                    <option value="99999" <?php if ($this->pipelineEntriesPerPage == 99999): ?>selected<?php endif; ?>>All entries</option>
                    </select>&nbsp;
                    <span id="ajaxPipelineNavigation">
                    </span>&nbsp;
                    <img src="images/indicator.gif" alt="" id="ajaxPipelineTableIndicator">
                </div>

                <div id="ajaxPipelineTable" class="table-responsive">
                </div>
                <input class="form-check-input" type="checkbox" aria-label="Select all candidates" name="select_all" onclick="selectAll_candidates(this)" title="Select all candidates"> <button type="button" class="btn btn-sm btn-outline-secondary" onclick="exportFromPipeline()" title="Export selected candidates">Export</button>
                <script>
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
                		window.location.href='<?= CATSUtility::getIndexName()?>?m=export&a=exportByDataGrid&i=<?= urlencode($instance_name); ?>&p=<?= urlencode(json_encode($params)) ?>&dynamicArgument<?= $instance_md5 ?>=' + serializeArray(exportArray<?= $instance_md5 ?>);
            		} else {
                		alert('No data selected');
            		}
            	}


            </script>
                <script>
                PipelineJobOrder_populate(<?php $this->_($this->data['jobOrderID']); ?>, 0, <?php $this->_($this->pipelineEntriesPerPage); ?>, 'dateCreatedInt', 'desc', <?php if ($this->isPopup) echo(1); else echo(0); ?>, 'ajaxPipelineTable', <?php echo Template::escapeJs($this->sessionCookie); ?>, 'ajaxPipelineTableIndicator', <?php echo Template::escapeJs(CATSUtility::getIndexName()); ?>);
            </script>

                <?php if (!$this->isPopup): ?>
                <?php if ($this->getUserAccessLevel('joborders.considerCandidateSearch') >= ACCESS_LEVEL_EDIT && !isset($this->frozen)): ?>
                <a class="btn btn-sm btn-outline-secondary" href="#" onclick="showPopWin(<?php echo Template::escapeJsAttr(CATSUtility::getIndexName() . '?m=joborders&a=considerCandidateSearch&jobOrderID=' . $this->jobOrderID); ?>, 820, 550, null); return false;">
                <img src="images/consider.gif" width="16" height="16" class="absmiddle" alt="add candidate">&nbsp;Add Candidate to This Job Order
                </a>
                <?php endif; ?>
                <?php endif; ?>
            </div>
        </section>
    </div>
</main>
<?php TemplateUtility::printFooter(); ?>
