<?php
include_once(LEGACY_ROOT . '/vendor/autoload.php');
use OpenCATS\UI\QuickActionMenu;
?>
<?php TemplateUtility::printHeader('Contact - ' . $this->data['firstName'] . ' ' . $this->data['lastName'], array( 'js/activity.js', 'js/attachment.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<script>
    window.CATSUserDateFormat  = <?php echo Template::escapeJs($_SESSION['CATS']->isDateDMY() ? 'DD-MM-YY' : 'MM-DD-YY'); ?>;
    window.CATSTimeFormat24    = <?php echo $_SESSION['CATS']->isTimeFormat24() ? 'true' : 'false'; ?>;
</script>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2 oc-contact-show-page">

    <div id="contents">
        <section class="oc-page-header d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
            <h1 class="h5 fw-semibold mb-0">Contact Details</h1>
            <nav class="d-flex flex-wrap align-items-center gap-2 mb-2" aria-label="Contact actions">
                <?php if ($this->getUserAccessLevel('contacts.edit') >= ACCESS_LEVEL_EDIT): ?>
                <a class="btn btn-sm btn-primary" id="edit_link" href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=contacts&a=edit&contactID=' . $this->contactID); ?>">
                <img src="images/actions/edit.gif" width="16" height="16" class="align-middle" alt="edit">&nbsp;Edit
                </a>

                <?php endif; ?>
                <?php if ($this->getUserAccessLevel('contacts.delete') >= ACCESS_LEVEL_DELETE): ?>
                <form id="delete_link" method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=contacts&amp;a=delete" class="d-inline" onsubmit="return confirm('Delete this contact?');">
                    <input type="hidden" name="postback" value="postback">
                    <input type="hidden" name="contactID" value="<?php echo Template::escapeAttr($this->contactID); ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                    <img src="images/actions/delete.gif" width="16" height="16" class="align-middle" alt="delete">&nbsp;Delete
                    </button>
                </form>

                <?php endif; ?>
                <?php if ($this->privledgedUser): ?>
                <a class="btn btn-sm btn-outline-secondary" id="history_link" href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=settings&a=viewItemHistory&dataItemType=300&dataItemID=' . $this->contactID); ?>">
                <img src="images/icon_clock.gif" width="16" height="16" class="align-middle" alt="">&nbsp;View History
                </a>

                <?php endif; ?>

            </nav>
        </section>

        <section class="card mb-2 oc-contact-details">
            <div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">Contact Details</div>
            <div class="card-body p-2">

                <div class="row g-3 small">
                    <div class="col-12 col-lg-6">
                        <dl class="row mb-0">

                            <dt class="col-sm-4 text-body-secondary fw-semibold">Name:</dt>
                            <dd class="col-sm-8">
                                <span class="fw-semibold">
                                <span class="<?php echo Template::escapeAttr($this->data['titleClassContact']); ?>">
                                <?php $this->_($this->data['firstName']); ?>
                                <?php $this->_($this->data['lastName']); ?>
                                <?php TemplateUtility::printSingleQuickActionMenu(new QuickActionMenu(DATA_ITEM_CONTACT, $this->contactID, $_SESSION['CATS']->getAccessLevel('contacts.edit'))); ?>
                                </span>
                                &nbsp;
                                <a id="vCard" href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=contacts&a=downloadVCard&contactID=' . $this->contactID); ?>">
                                <img src="images/vcard.gif" class="align-middle" alt="vCard">
                                </a>
                                </span>
                            </dd>

                            <dt class="col-sm-4 text-body-secondary fw-semibold">Company:</dt>
                            <dd class="col-sm-8">
                                <a href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=companies&a=show&companyID=' . $this->data['companyID']); ?>">
                                <span class="<?php echo Template::escapeAttr($this->data['titleClassCompany']); ?>">
                                <?php $this->_($this->data['companyName']); ?>
                                </span>
                                </a>
                                <?php if ($this->data['leftCompany']): ?>
                                &nbsp;(no longer associated with company)
                                <?php endif; ?>
                            </dd>

                            <dt class="col-sm-4 text-body-secondary fw-semibold">Title:</dt>
                            <dd class="col-sm-8"><?php $this->_($this->data['title']); ?></dd>

                            <dt class="col-sm-4 text-body-secondary fw-semibold">Department:</dt>
                            <dd class="col-sm-8"><?php $this->_($this->data['department']); ?></dd>

                            <dt class="col-sm-4 text-body-secondary fw-semibold">Work Phone:</dt>
                            <dd class="col-sm-8"><?php $this->_($this->data['phoneWork']); ?></dd>

                            <dt class="col-sm-4 text-body-secondary fw-semibold">Cell Phone:</dt>
                            <dd class="col-sm-8"><?php $this->_($this->data['phoneCell']); ?></dd>

                            <dt class="col-sm-4 text-body-secondary fw-semibold">Other Phone:</dt>
                            <dd class="col-sm-8"><?php $this->_($this->data['phoneOther']); ?></dd>

                            <?php for ($i = 0; $i < intval(count($this->extraFieldRS)/2); $i++): ?>

                            <dt class="col-sm-4 text-body-secondary fw-semibold"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</dt>
                            <dd class="col-sm-8"><?php echo($this->extraFieldRS[$i]['display']); ?></dd>

                            <?php endfor; ?>
                        </dl>
                    </div>

                    <div class="col-12 col-lg-6">
                        <dl class="row mb-0">

                            <dt class="col-sm-4 text-body-secondary fw-semibold">Reports To:</dt>
                            <dd class="col-sm-8">
                                <?php if($this->data['reportsTo'] == -1 || $this->data['reportsTo'] == 0 || $this->data['reportsToTitle'] == ''): ?>
                                (None)
                                <?php else: ?>
                                <a href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=contacts&a=show&contactID=' . $this->data['reportsTo']); ?>">
                                <img src="images/contact_small.gif" alt="">&nbsp;
                                <?php $this->_($this->data['reportsToFirstName']); ?>&nbsp;<?php $this->_($this->data['reportsToLastName']); ?>
                                </a>
                                &nbsp;(<?php $this->_($this->data['reportsToTitle']); ?>)
                                <?php endif; ?>
                            </dd>

                            <dt class="col-sm-4 text-body-secondary fw-semibold">E-Mail:</dt>
                            <dd class="col-sm-8">
                                <a href="<?php echo Template::escapeUrl('mailto:' . $this->data['email1']); ?>"><?php $this->_($this->data['email1']); ?></a>
                            </dd>

                            <dt class="col-sm-4 text-body-secondary fw-semibold">2nd E-Mail:</dt>
                            <dd class="col-sm-8">
                                <a href="<?php echo Template::escapeUrl('mailto:' . $this->data['email2']); ?>"><?php $this->_($this->data['email2']); ?></a>
                            </dd>

                            <dt class="col-sm-4 text-body-secondary fw-semibold">Address:</dt>
                            <dd class="col-sm-8">
                            <?php echo nl2br(Template::escapeHtml($this->data['address'])); ?>
                                <?php if (!empty($this->data['address2'])): ?>
                                <br>
                                <?php $this->_($this->data['address2']); ?>
                                <?php endif; ?>
                            </dd>

                            <dt class="col-sm-4 text-body-secondary fw-semibold">&nbsp;</dt>
                            <dd class="col-sm-8">
                                <?php $this->_($this->data['cityAndState']); ?>
                                <?php $this->_($this->data['zip']); ?>
                            </dd>

                            <dt class="col-sm-4 text-body-secondary fw-semibold">Created:</dt>
                            <dd class="col-sm-8"><?php $this->_($this->data['dateCreated']); ?> (<?php $this->_($this->data['enteredByFullName']); ?>)</dd>

                            <dt class="col-sm-4 text-body-secondary fw-semibold">Owner:</dt>
                            <dd class="col-sm-8"><?php $this->_($this->data['ownerFullName']); ?></dd>

                            <?php for ($i = (intval(count($this->extraFieldRS))/2); $i < (count($this->extraFieldRS)); $i++): ?>

                            <dt class="col-sm-4 text-body-secondary fw-semibold"><?php $this->_($this->extraFieldRS[$i]['fieldName']); ?>:</dt>
                            <dd class="col-sm-8"><?php echo($this->extraFieldRS[$i]['display']); ?></dd>

                            <?php endfor; ?>
                        </dl>
                    </div>
                </div>

            </div></section>
        <section class="card mb-2 oc-contact-notes-events">
            <div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">Notes &amp; Upcoming Events</div>
            <div class="card-body p-2">
                <div class="row g-3 small">
                    <div class="col-12">
                        <dl class="row mb-0">

                            <dt class="col-sm-4 text-body-secondary fw-semibold">Misc. Notes:</dt>
                            <?php if ($this->isShortNotes): ?>
                            <dd id="shortNotes" style="display:block;" class="col-sm-8">
                                <?php echo($this->data['shortNotes']); ?><span class="moreText">...</span>&nbsp;
                                <p><a href="#" class="moreText" onclick="toggleNotes(); return false;">[More]</a></p>
                            </dd>
                            <dd id="fullNotes" style="display:none;" class="col-sm-8">
                                <?php echo($this->data['notes']); ?>&nbsp;
                                <p><a href="#" class="moreText" onclick="toggleNotes(); return false;">[Less]</a></p>
                            </dd>
                            <?php else: ?>
                            <dd id="shortNotes" style="display:block;" class="col-sm-8">
                                <?php echo($this->data['notes']); ?>
                            </dd>
                            <?php endif; ?>

                            <dt class="col-sm-4 text-body-secondary fw-semibold">Upcoming Events:</dt>
                            <dd id="upcomingEvents" style="display:block;" class="col-sm-8">
                                <?php foreach ($this->calendarRS as $rowNumber => $calendarData): ?>
                                <div>
                                    <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=calendar&amp;view=DAYVIEW&amp;month=<?php echo($calendarData['month']); ?>&amp;year=20<?php echo($calendarData['year']); ?>&amp;day=<?php echo($calendarData['day']); ?>&amp;showEvent=<?php echo($calendarData['eventID']); ?>">
                                    <img src="<?php $this->_($calendarData['typeImage']) ?>" alt="">
                                    <?php $this->_($calendarData['dateShow']) ?>:
                                    <?php $this->_($calendarData['title']); ?>
                                    </a>
                                </div>
                                <?php endforeach; ?>
                                <?php if ($this->getUserAccessLevel('contacts.addActivityScheduleEvent') >= ACCESS_LEVEL_EDIT): ?>
                                <a href="#" onclick="showPopWin(<?php echo Template::escapeJsAttr(CATSUtility::getIndexName() . '?m=contacts&a=addActivityScheduleEvent&contactID=' . $this->contactID . '&onlyScheduleEvent=true'); ?>, 600, 200, null); return false;">
                                <img src="images/calendar_add.gif" width="16" height="16" alt="Schedule Event" class="align-middle">&nbsp;Schedule Event
                                </a>
                                <?php endif; ?>
                            </dd>

                        </dl>
                    </div>
                </div>
            </div></section>

        <section class="card mb-2 oc-contact-joborders">
            <div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">Job Orders</div>
            <div class="table-responsive">
                <table class="sortable table table-sm table-striped table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Title</th>
                            <th scope="col">Type</th>
                            <th scope="col">Status</th>
                            <th scope="col">Created</th>
                            <th scope="col">Modified</th>
                            <th scope="col">Start</th>
                            <th scope="col">Age</th>
                            <th scope="col">S</th>
                            <th scope="col">P</th>
                            <th scope="col">Recruiter</th>
                            <th scope="col">Owner</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php foreach ($this->jobOrdersRS as $rowNumber => $jobOrdersData): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td>
                                <a href="<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php $this->_($jobOrdersData['jobOrderID']) ?>">
                                <?php $this->_($jobOrdersData['title']) ?>
                                </a>
                            </td>
                            <td><?php $this->_($jobOrdersData['type']) ?></td>
                            <td><?php $this->_($jobOrdersData['status']) ?></td>
                            <td><?php $this->_($jobOrdersData['dateCreated']) ?></td>
                            <td><?php $this->_($jobOrdersData['dateModified']) ?></td>
                            <td><?php $this->_($jobOrdersData['startDate']) ?></td>
                            <td><?php $this->_($jobOrdersData['daysOld']) ?></td>
                            <td><?php $this->_($jobOrdersData['submitted']); ?></td>
                            <td><?php $this->_($jobOrdersData['pipeline']); ?></td>
                            <td><?php $this->_($jobOrdersData['recruiterAbbrName']); ?></td>
                            <td><?php $this->_($jobOrdersData['ownerAbbrName']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        </section>
        <section class="card oc-contact-activity">
            <div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">Activity</div>
            <div class="table-responsive">
                <table id="activityTable" class="sortable table table-sm table-striped table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th scope="col">Date</th>
                            <th scope="col">Type</th>
                            <th scope="col">Regarding</th>
                            <th scope="col">Notes</th>
                            <th scope="col">Entered By</th>
                            <th scope="col">Action</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php foreach ($this->activityRS as $rowNumber => $activityData): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                            <td id="activityDate<?php echo Template::escapeAttr($activityData['activityID']); ?>"><?php $this->_($activityData['dateCreated']) ?></td>
                            <td id="activityType<?php echo Template::escapeAttr($activityData['activityID']); ?>"><?php $this->_($activityData['typeDescription']) ?></td>
                            <td id="activityRegarding<?php echo Template::escapeAttr($activityData['activityID']); ?>" data-joborder-id="<?php echo Template::escapeAttr(isset($activityData['jobOrderID']) ? $activityData['jobOrderID'] : ''); ?>"><?php $this->_($activityData['regarding']) ?></td>
                            <td id="activityNotes<?php echo Template::escapeAttr($activityData['activityID']); ?>"><?php echo nl2br(TemplateUtility::highlightStatusChangeActivityNote($activityData['notes'])); ?></td>
                            <td><?php $this->_($activityData['enteredByAbbrName']) ?></td>
                            <td >
                                <?php if ($this->getUserAccessLevel('contacts.editActivity') >= ACCESS_LEVEL_EDIT): ?>
                                <a href="#" class="btn btn-sm btn-outline-secondary" aria-label="Edit activity" id="editActivity<?php echo Template::escapeAttr($activityData['activityID']); ?>" onclick="Activity_editEntry(<?php echo (int) $activityData['activityID']; ?>, <?php echo (int) $this->contactID; ?>, <?php echo (int) DATA_ITEM_CONTACT; ?>, <?php echo Template::escapeJsAttr($this->sessionCookie); ?>); return false;">
                                <img src="images/actions/edit.gif" width="16" height="16" alt="" class="align-middle" title="Edit">
                                </a>
                                <?php endif; ?>
                                <?php if ($this->getUserAccessLevel('contacts.deleteActivity') >= ACCESS_LEVEL_EDIT): ?>
                                <a href="#" class="btn btn-sm btn-outline-danger" aria-label="Delete activity" id="deleteActivity<?php echo Template::escapeAttr($activityData['activityID']); ?>" onclick="Activity_deleteEntry(<?php echo (int) $activityData['activityID']; ?>, <?php echo Template::escapeJsAttr($this->sessionCookie); ?>); return false;">
                                <img src="images/actions/delete.gif" width="16" height="16" alt="" class="align-middle" title="Delete">
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div id="addActivityDiv" class="card-footer bg-body py-1 px-2">
                <?php if ($this->getUserAccessLevel('contacts.logActivityScheduleEvent') >= ACCESS_LEVEL_EDIT): ?>
                <a href="#" class="btn btn-sm btn-primary" id="addActivityLink" title="Log an Activity / Schedule Event" onclick="showPopWin(<?php echo Template::escapeJsAttr(CATSUtility::getIndexName() . '?m=contacts&a=addActivityScheduleEvent&contactID=' . $this->contactID); ?>, 600, 375, null); return false;">
                <img src="images/new_activity_inline.gif" width="16" height="16" class="align-middle" title="Log an Activity / Schedule Event" alt="Log an Activity / Schedule Event">&nbsp;Log an Activity / Schedule Event
                </a>
                <?php endif; ?>
                <img src="images/indicator2.gif" id="addActivityIndicator" alt="" style="visibility: hidden; margin-left: 5px;" height="16" width="16">
            </div>
        </section>
    </div>
</main>
<?php TemplateUtility::printFooter(); ?>
