<?php
$reviewReturnTo = 'home';
$homeBase = CATSUtility::getIndexName() . '?m=home';
$allURL = $homeBase . '&a=priorities&filter=' . $this->homeState['homeFilter'];
$homeToday = $this->homeToday ?? DateUtility::getAdjustedDate('Y-m-d');
$visibleWarningCount = count($this->recruitmentWarnings['visibleWarnings']);
$recruitmentCount = (int) (($this->recruitmentWarnings['visibleCounts']['W02'] ?? 0) +
    ($this->recruitmentWarnings['visibleCounts']['W03'] ?? 0) + ($this->recruitmentWarnings['visibleCounts']['W08'] ?? 0));
$clientFollowUpCount = (int) ($this->recruitmentWarnings['visibleCounts']['W07'] ?? 0);
$overview = $this->priorityOverview ?? HomeUI::priorityOverview(array(), $this->recruitmentWarnings['visibleWarnings'], $homeToday);
?>
<?php include(LEGACY_ROOT . '/modules/home/WarningReviewNotice.tpl'); ?>
<?php if (($_GET['taskNotice'] ?? null) === 'saved'): ?><div class="alert alert-success" role="status">Task saved. Priorities updated.</div><?php endif; ?>
<section class="home-summary mb-2" aria-label="My work summary">
    <a class="home-summary-item<?php if ($overview['overdueTaskCount'] > 0) echo ' home-summary-overdue'; ?>" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&status=open_work&due=overdue'); ?>"><strong><?php echo (int) $overview['overdueTaskCount']; ?></strong><span>Overdue Tasks</span></a>
    <a class="home-summary-item<?php if ($overview['dueTodayTaskCount'] > 0) echo ' home-summary-today'; ?>" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&status=open_work&due=today'); ?>"><strong><?php echo (int) $overview['dueTodayTaskCount']; ?></strong><span>Tasks due today</span></a>
    <a class="home-summary-item" href="<?php echo Template::escapeAttr($homeBase . '&a=priorities&filter=tasks'); ?>"><strong><?php echo (int) $this->myTaskCount; ?></strong><span>Open Tasks assigned to me</span></a>
    <a class="home-summary-item" href="<?php echo Template::escapeAttr($homeBase . '&a=priorities&filter=warnings'); ?>"><strong><?php echo $visibleWarningCount; ?></strong><span>Visible warning<?php echo $visibleWarningCount === 1 ? '' : 's'; ?><span class="d-block small">Including: <?php echo $recruitmentCount; ?> recruitment warning<?php echo $recruitmentCount === 1 ? '' : 's'; ?> &middot; <?php echo $clientFollowUpCount; ?> client follow-up<?php echo $clientFollowUpCount === 1 ? '' : 's'; ?></span></span></a>
    <?php if (($this->homeJobs ?? null) !== null): ?><a class="home-summary-item" href="#home-current-jobs"><strong><?php echo (int) $this->homeJobs['total']; ?></strong><span>My active jobs</span></a><?php endif; ?>
</section>
<div class="home-workspace">
<section class="card mb-2" aria-labelledby="prioritiesHeading" data-home-queue>
    <div class="card-header bg-body home-queue-toolbar d-flex flex-wrap align-items-center gap-2">
        <h2 id="prioritiesHeading" class="h5 mb-0" tabindex="-1"><?php echo $this->homeState['homeView'] === 'all' ? 'All priorities' : "Today's priorities"; ?></h2>
        <nav class="nav nav-pills gap-1" aria-label="Priority filters">
            <?php foreach (array('all' => 'All priorities', 'tasks' => 'My Tasks', 'warnings' => 'Warnings') as $value => $label): ?>
            <a class="nav-link py-1 px-2<?php if ($this->homeState['homeFilter'] === $value) echo ' active'; ?>" <?php if ($this->homeState['homeFilter'] === $value) echo 'aria-current="page"'; ?> href="<?php echo Template::escapeAttr($homeBase . '&a=' . ($this->homeState['homeView'] === 'all' ? 'priorities' : 'home') . '&filter=' . $value); ?>"><?php echo Template::escapeHtml($label); ?></a>
            <?php endforeach; ?>
        </nav>
        <a class="ms-auto" href="<?php echo Template::escapeAttr($allURL); ?>">View all priorities (<?php echo (int) $this->priorityPager->getTotalRows(); ?>)</a>
    </div>
    <div class="home-queue-columns" aria-hidden="true"><span>Type</span><span>Subject / reason</span><span>Related record</span><span>Due / evidence</span><span>Actions</span></div>
    <ol class="list-group list-group-flush list-unstyled mb-0" id="priorityRows">
        <?php foreach ($this->priorityRows as $row):
            $task = $row['task'];
            $warning = $row['warnings'][0] ?? null;
            $rowState = array_replace($this->homeState, array('homeContext' => $row['id']));
            $returnQuery = '&' . http_build_query(array_merge(array('returnTo' => 'home'), $rowState));
            $title = $task === null ? $warning['title'] : $task['title'];
            $sourceURL = $task !== null || $warning['entityType'] == DATA_ITEM_TASK ?
                CATSUtility::getIndexName() . '?m=tasks&a=show&taskID=' . (int) ($task['taskID'] ?? $warning['entityID']) . $returnQuery :
                TaskPresentation::parentURL($warning['entityType'], $warning['entityID']);
            $reason = $task === null ? HomeUI::warningReason($warning['reason']) : $task['description'];
            $evidence = $task === null ? HomeUI::warningEvidence($warning) : null;
            $priorityLabel = $task === null ? '' : (Tasks::getPriorities()[$task['priority']] ?? $task['priority']);
        ?>
        <li class="list-group-item home-priority<?php if ($task === null) echo ' home-priority-warning'; ?>" id="<?php echo Template::escapeAttr($row['id']); ?>" tabindex="-1">
            <div class="home-priority-facts">
                <div class="home-priority-type"><?php echo $task === null ? 'Warning' : 'Task'; ?><?php if ($task === null && ($warning['coverage'] ?? '') === 'covered'): ?><span class="d-block text-body-secondary">Task covered</span><?php endif; ?></div>
                <div class="home-priority-subject">
                    <h3 class="h6 mb-0 home-subject-line"><a href="<?php echo Template::escapeAttr($sourceURL); ?>"><span<?php if ($task !== null) echo ' class="home-preview"'; ?>><?php echo Template::escapeHtml($task === null ? $reason : $title); ?></span></a><?php if ($task !== null): ?><span class="home-task-priority<?php if ($task['priority'] === 'high') echo ' home-priority-high'; ?>">Priority: <?php echo Template::escapeHtml($priorityLabel); ?></span><?php endif; ?></h3>
                    <?php if ($task !== null && $reason !== null && $reason !== ''): ?><p class="mb-0 text-body-secondary"><?php echo Template::escapeHtml($reason); ?></p><?php endif; ?>
                    <?php if ($task !== null && $warning !== null): ?><p class="mb-0 text-body-secondary">Warning: <?php echo Template::escapeHtml(HomeUI::warningReason($warning['reason'])); ?></p><?php endif; ?>
                </div>
                <div class="home-priority-related">
                    <?php if ($task === null): ?>
                    <a href="<?php echo Template::escapeAttr($sourceURL); ?>"><span class="home-preview"><?php echo Template::escapeHtml($title); ?></span></a><?php if (!empty($row['parentContext']['companyName'])): ?><span class="home-preview text-body-secondary"><?php echo Template::escapeHtml($row['parentContext']['companyName']); ?></span><?php endif; ?>
                    <?php elseif ($row['parentContext'] !== null): $parent = $row['parentContext']; ?>
                    <a href="<?php echo Template::escapeAttr(TaskPresentation::parentURL($parent['type'], $parent['id'])); ?>"><span class="home-preview"><?php echo Template::escapeHtml($parent['name']); ?></span></a><?php if ($parent['companyName'] !== ''): ?><span class="home-preview text-body-secondary"><?php echo Template::escapeHtml($parent['companyName']); ?></span><?php endif; ?>
                    <?php else: ?><span class="text-body-secondary"><?php echo $task['dataItemType'] === null ? 'No linked record' : 'Linked record unavailable'; ?></span><?php endif; ?>
                </div>
                <div class="home-priority-due">
                    <?php if ($task !== null): ?>
                        <?php if ($task['dueDate'] !== null): ?><span<?php if ($task['dueDate'] <= $homeToday) echo ' class="home-due-cue ' . ($task['dueDate'] < $homeToday ? 'home-due-overdue' : 'home-due-today') . '"'; ?>><?php echo $task['dueDate'] < $homeToday ? 'Overdue' : ($task['dueDate'] === $homeToday ? 'Due today' : 'Due'); ?></span><span class="home-due-date"><?php echo Template::escapeHtml(TaskPresentation::date($task['dueDate'])); ?></span>
                        <?php else: ?><span class="text-body-secondary">No due date</span><?php endif; ?>
                    <?php else: ?>
                        <span class="text-body-secondary"><?php echo Template::escapeHtml($warning['code'] === 'W07' && $evidence['date'] !== '' ? 'Qualifying contact' : $evidence['label']); ?></span>
                        <?php if ($evidence['date'] !== ''): ?><span class="home-due-date"><?php echo Template::escapeHtml($evidence['date']); ?></span><?php endif; ?>
                        <?php if ($evidence['age'] !== ''): ?><span class="d-block text-body-secondary"><?php echo Template::escapeHtml($evidence['age']); ?></span><?php endif; ?>
                    <?php endif; ?>
                </div>
                <div class="home-priority-actions d-flex flex-wrap align-items-center gap-1">
                    <?php if ($task !== null && $row['permissions']['complete']): ?>
                        <button class="btn btn-sm btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#homeTaskComplete" data-home-task="<?php echo (int) $task['taskID']; ?>" data-home-title="<?php echo Template::escapeAttr($title); ?>" data-home-context="<?php echo Template::escapeAttr($row['id']); ?>">Complete Task</button>
                    <?php else:
                        $actionURL = $sourceURL;
                        $actionLabel = $task === null ? ($warning['code'] === 'W07' ? 'Open Company' : ($warning['entityType'] == DATA_ITEM_TASK ? 'Open Task' : 'Open Job Order')) : 'Open Task';
                        if ($task === null && $warning['code'] === 'W07' && $warning['reason'] === 'no_next_action' &&
                            $_SESSION['CATS']->getAccessLevel('tasks.show') >= ACCESS_LEVEL_READ && $_SESSION['CATS']->getAccessLevel('tasks.add') >= ACCESS_LEVEL_EDIT && $_SESSION['CATS']->getAccessLevel('companies.edit') >= ACCESS_LEVEL_EDIT)
                        {
                            $actionURL = CATSUtility::getIndexName() . '?m=tasks&a=add&dataItemType=' . DATA_ITEM_COMPANY . '&dataItemID=' . (int) $warning['entityID'] . $returnQuery;
                            $actionLabel = 'Add follow-up';
                        }
                    ?>
                        <a class="btn btn-sm btn-outline-primary" href="<?php echo Template::escapeAttr($actionURL); ?>"><?php echo $actionLabel; ?></a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-sm btn-link home-context-toggle" aria-label="<?php echo Template::escapeAttr('Context & actions: ' . $title); ?>" data-home-context-toggle data-bs-toggle="collapse" data-bs-target="#context-<?php echo Template::escapeAttr($row['id']); ?>" aria-expanded="false" aria-controls="context-<?php echo Template::escapeAttr($row['id']); ?>"><span data-home-context-label>Context</span></button>
                    <a href="#heading-<?php echo Template::escapeAttr($row['id']); ?>" data-home-context-jump hidden>Read context</a>
                </div>
            </div>
            <div class="collapse home-context" id="context-<?php echo Template::escapeAttr($row['id']); ?>" data-home-context data-home-row="<?php echo Template::escapeAttr($row['id']); ?>" role="region" aria-labelledby="heading-<?php echo Template::escapeAttr($row['id']); ?>">
                <div class="home-context-content border rounded bg-body p-3 mt-2">
                    <div class="d-flex align-items-start gap-2 mb-3">
                        <div class="flex-grow-1"><p class="text-body-secondary mb-1"><?php echo $task === null ? 'Warning context' : 'Task context'; ?></p><h3 id="heading-<?php echo Template::escapeAttr($row['id']); ?>" class="h6 mb-0" tabindex="-1"><?php echo Template::escapeHtml($title); ?></h3></div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#context-<?php echo Template::escapeAttr($row['id']); ?>" aria-label="Close context">Close</button>
                    </div>
                    <?php if ($task !== null): ?>
                        <dl class="home-context-facts mb-3">
                            <dt>Due</dt><dd><?php echo $task['dueDate'] === null ? 'No due date' : Template::escapeHtml(($task['dueDate'] < $homeToday ? 'Overdue · ' : ($task['dueDate'] === $homeToday ? 'Due today · ' : '')) . TaskPresentation::date($task['dueDate'])); ?></dd>
                            <dt>Assigned to</dt><dd><?php echo $task['assignedTo'] == $_SESSION['CATS']->getUserID() ? 'Me' : ($task['assignedTo'] === null ? 'Unassigned' : 'User #' . (int) $task['assignedTo']); ?></dd>
                            <dt>Status</dt><dd><?php echo Template::escapeHtml(Tasks::getStatuses()[$task['status']]); ?></dd>
                            <dt>Purpose</dt><dd><?php echo Template::escapeHtml(Tasks::getPurposes()[$task['purpose']]); ?></dd>
                            <dt>Priority</dt><dd><?php echo Template::escapeHtml($priorityLabel); ?></dd>
                            <?php if ($row['parentContext'] !== null): $parent = $row['parentContext']; ?>
                            <dt><?php echo Template::escapeHtml(Tasks::getParentTypes()[$parent['type']]); ?></dt><dd><a href="<?php echo Template::escapeAttr(TaskPresentation::parentURL($parent['type'], $parent['id'])); ?>"><?php echo Template::escapeHtml($parent['name']); ?></a><?php if ($parent['companyName'] !== ''): ?><span class="d-block"><?php echo Template::escapeHtml($parent['companyName']); ?></span><?php endif; ?></dd>
                            <?php else: ?><dt>Related record</dt><dd><?php echo $task['dataItemType'] === null ? 'No linked record' : 'Linked record unavailable'; ?></dd><?php endif; ?>
                        </dl>
                        <?php if ($task['description'] !== null && $task['description'] !== ''): ?><details class="home-description mb-3"><summary>Description</summary><p class="mt-2 mb-0"><?php echo nl2br(Template::escapeHtml($task['description'])); ?></p></details><?php endif; ?>
                        <nav class="d-flex flex-wrap gap-2 mb-3" aria-label="Task actions">
                            <?php if ($row['permissions']['complete']): ?><button class="btn btn-sm btn-primary home-context-complete" type="button" data-bs-toggle="modal" data-bs-target="#homeTaskComplete" data-home-task="<?php echo (int) $task['taskID']; ?>" data-home-title="<?php echo Template::escapeAttr($title); ?>" data-home-context="<?php echo Template::escapeAttr($row['id']); ?>">Complete Task</button><?php endif; ?>
                            <?php if ($row['permissions']['edit']): ?><a class="btn btn-sm btn-outline-secondary" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&a=edit&taskID=' . (int) $task['taskID'] . $returnQuery); ?>">Edit / Reschedule</a><?php endif; ?>
                            <a class="btn btn-sm btn-link" href="<?php echo Template::escapeAttr($sourceURL); ?>">Task details &amp; history</a>
                            <?php if ($row['parentContext'] !== null): ?><a class="btn btn-sm btn-link" href="<?php echo Template::escapeAttr(TaskPresentation::parentURL($row['parentContext']['type'], $row['parentContext']['id'])); ?>">Open linked record / Log Activity</a><?php endif; ?>
                        </nav>
                    <?php else: ?>
                        <dl class="home-context-facts mb-3">
                            <dt><?php echo Template::escapeHtml(Tasks::getParentTypes()[$warning['entityType']] ?? 'Task'); ?></dt><dd><a href="<?php echo Template::escapeAttr($sourceURL); ?>"><?php echo Template::escapeHtml($title); ?></a></dd>
                            <?php if (!empty($row['parentContext']['companyName'])): ?><dt>Company</dt><dd><?php echo Template::escapeHtml($row['parentContext']['companyName']); ?></dd><?php endif; ?>
                            <?php if ($warning['code'] === 'W07'): $company = $warning['relationshipAttention']; ?>
                                <?php if (!empty($company['tierLabel'])): ?><dt>Commercial tier</dt><dd><?php echo Template::escapeHtml($company['tierLabel']); ?></dd><?php endif; ?>
                                <?php if (!empty($company['relationshipStatus'])): ?><dt>Relationship</dt><dd><?php echo Template::escapeHtml($company['relationshipStatus']); ?></dd><?php endif; ?>
                            <?php endif; ?>
                        </dl>
                    <?php endif; ?>
                    <div class="home-context-history row g-3<?php echo $row['warnings'] ? ' home-history-split' : ''; ?>">
                    <?php if ($row['warnings']): ?><div class="col-12">
                    <?php foreach ($row['warnings'] as $warning): ?>
                        <div class="border-top pt-2 mt-2">
                            <p class="small mb-0"><strong>Calculated warning:</strong> <?php echo Template::escapeHtml(HomeUI::warningReason($warning['reason'])); ?><?php if ($task !== null && !($warning['entityType'] == DATA_ITEM_TASK && $warning['entityID'] == $task['taskID'])): ?> &middot; <a href="<?php echo Template::escapeAttr(TaskPresentation::parentURL($warning['entityType'], $warning['entityID'])); ?>"><?php echo Template::escapeHtml($warning['title']); ?></a><?php endif; ?></p>
                            <?php $warningEvidence = HomeUI::warningEvidence($warning); ?>
                            <p class="text-body-secondary mb-2"><?php echo Template::escapeHtml($warningEvidence['label'] . ($warningEvidence['date'] !== '' ? ': ' . $warningEvidence['date'] : '') . ($warningEvidence['age'] !== '' ? ' · ' . $warningEvidence['age'] : '')); ?></p>
                            <?php if ($warning['code'] === 'W07'): ?>
                            <?php if (count($warning['relationshipAttention']['reasons']) > 1): ?><p class="small mb-0"><?php echo Template::escapeHtml(implode(' · ', array_map(array('HomeUI', 'warningReason'), $warning['relationshipAttention']['reasons']))); ?></p><?php endif; ?>
                            <?php foreach ($warning['relationshipAttention']['tasks'] as $linkedTask): ?><a class="small me-3" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&a=show&taskID=' . (int) $linkedTask['taskID'] . $returnQuery); ?>">Follow-up Task #<?php echo (int) $linkedTask['taskID']; ?></a><?php endforeach; ?>
                            <?php elseif (!empty($warning['taskID'])): ?><a class="small" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&a=show&taskID=' . (int) $warning['taskID'] . $returnQuery); ?>">Follow-up Task #<?php echo (int) $warning['taskID']; ?></a><?php endif; ?>
                            <?php $reviewHomeContext = $rowState; ?>
                            <?php foreach ($this->recruitmentWarnings['reviews'][WarningReviews::reviewKey($warning)] ?? array() as $review): ?>
                                <?php if (!$review['suppressed'] && ($warning['code'] !== 'W07' || in_array($review['identity']['reason_key'], $warning['relationshipAttention']['reasons'], true))) include(LEGACY_ROOT . '/modules/home/WarningReviewControls.tpl'); ?>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                    </div><?php endif; ?>
                    <?php if ($_SESSION['CATS']->getAccessLevel('activity.listByViewDataGrid') >= ACCESS_LEVEL_READ): ?>
                    <div class="col-12">
                    <section class="home-recent-activity border-top pt-2 mt-2" aria-label="Recent recorded Activity" data-home-activity-url="<?php echo Template::escapeAttr($homeBase . '&a=contextActivity&homeView=' . $this->homeState['homeView'] . '&filter=' . $this->homeState['homeFilter'] . '&page=' . $this->homeState['homePage'] . '&homeContext=' . $row['id']); ?>">
                        <h4 class="h6 mb-1">Recent Activity</h4>
                        <div data-home-activity-content role="status">Open context to load recorded Activity.</div>
                    </section>
                    </div>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        </li>
        <?php endforeach; ?>
    </ol>
    <?php if (!$this->priorityRows): ?><div class="card-body py-4"><h3 class="h6">Nothing waiting in this view</h3><p class="text-body-secondary mb-0">No visible priorities match this filter. You can view your Tasks or change the filter above.</p></div><?php endif; ?>
    <div class="card-footer bg-body d-flex flex-wrap justify-content-between align-items-center gap-2">
        <span class="small text-body-secondary">Showing <?php echo count($this->priorityRows); ?> of <?php echo (int) $this->priorityPager->getTotalRows(); ?> priorities in this view</span>
        <nav aria-label="Priority pages"><?php $this->priorityPager->printNavigation('', false); ?></nav>
        <?php if ($this->homeState['homeView'] === 'all'): ?><a class="small" href="<?php echo Template::escapeAttr($homeBase); ?>">Back to today's priorities</a><?php else: ?><a class="fw-semibold" href="<?php echo Template::escapeAttr($allURL); ?>">View all priorities</a><?php endif; ?>
    </div>
</section>
<aside id="homeContextRail" aria-label="Selected priority context" hidden></aside>
</div>
<?php include(LEGACY_ROOT . '/modules/home/SupportingWork.tpl'); ?>
<section class="home-reviews border-top py-2" aria-labelledby="reviewedHeading">
    <div>
        <h2 id="reviewedHeading" class="visually-hidden">Personal reviews</h2>
        <button type="button" class="btn btn-sm btn-link" data-bs-toggle="collapse" data-bs-target="#suppressedWarnings" aria-expanded="false" aria-controls="suppressedWarnings">Personally Reviewed / Remind Later (<?php echo (int) $this->recruitmentWarnings['suppressedReasonCount']; ?>)</button>
        <div class="collapse mt-3" id="suppressedWarnings">
            <?php if (!$this->recruitmentWarnings['suppressedWarnings']): ?><p class="small mb-0">No current personally paused warnings.</p><?php endif; ?>
            <ul class="list-unstyled mb-0">
                <?php foreach ($this->recruitmentWarnings['suppressedWarnings'] as $suppressed):
                    $warning = $suppressed['warning']; $review = $suppressed['review'];
                    $reviewHomeContext = array_replace($this->homeState, array('homeContext' => 'suppressedWarnings'));
                ?>
                <li class="border-top py-2"><a href="<?php echo Template::escapeAttr(TaskPresentation::parentURL($warning['entityType'], $warning['entityID'])); ?>"><?php echo Template::escapeHtml($warning['title']); ?></a>
                <?php include(LEGACY_ROOT . '/modules/home/WarningReviewControls.tpl'); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</section>
<?php $reviewHomeContext = $this->homeState; include(LEGACY_ROOT . '/modules/home/WarningReviewModal.tpl'); ?>
<div class="modal fade" id="homeTaskComplete" tabindex="-1" aria-labelledby="homeTaskCompleteTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable"><div class="modal-content">
        <form method="post" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&a=complete'); ?>" data-home-submit>
            <div class="modal-header"><h2 class="modal-title fs-5" id="homeTaskCompleteTitle">Complete Task</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div>
            <div class="modal-body"><p data-home-task-title></p><p class="small text-body-secondary mb-0">Completes this Task only. No Activity or candidate progress is recorded.</p>
                <input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>">
                <input type="hidden" name="taskID" value=""><input type="hidden" name="returnTo" value="home">
                <?php foreach ($this->homeState as $field => $value): ?><input type="hidden" name="<?php echo Template::escapeAttr($field); ?>" value="<?php echo Template::escapeAttr($value); ?>"><?php endforeach; ?>
            </div>
            <div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-primary">Complete Task</button></div>
        </form>
    </div></div>
</div>
