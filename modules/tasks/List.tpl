<?php include './modules/tasks/Header.tpl'; ?>
<?php $filters = TasksDataGrid::getTaskFilters($_GET); ?>
<section class="card mb-2">
<div class="card-header d-flex flex-wrap align-items-center gap-2"><h2 class="h6 mb-0">My Tasks / accessible work</h2>
<?php if ($_SESSION['CATS']->getAccessLevel('tasks.add') >= ACCESS_LEVEL_EDIT): ?><a class="btn btn-sm btn-primary ms-auto" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&a=add'); ?>">Add Task</a><?php endif; ?></div>
<div class="card-body">
<form method="get" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName()); ?>" class="row g-2 align-items-end">
<input type="hidden" name="m" value="tasks">
<?php foreach (array('assignedTo' => array('Assigned recruiter', array('all' => 'All accessible') + $this->users),
    'status' => array('Status', array('open_work' => 'Open work', '' => 'All statuses') + Tasks::getStatuses()),
    'priority' => array('Priority', array('' => 'All priorities') + Tasks::getPriorities()),
    'purpose' => array('Purpose', array('' => 'All purposes') + Tasks::getPurposes()),
    'due' => array('Due', array('' => 'All dates', 'overdue' => 'Overdue', 'today' => 'Due today', 'upcoming' => 'Upcoming', 'undated' => 'Undated')),
    'dataItemType' => array('Parent type', array('' => 'All types') + Tasks::getParentTypes())) as $field => $definition): ?>
<div class="col-sm-6 col-lg-4"><label class="form-label" for="filter-<?php echo $field; ?>"><?php echo $definition[0]; ?></label><select class="form-select form-select-sm" id="filter-<?php echo $field; ?>" name="<?php echo $field; ?>">
<?php foreach ($definition[1] as $code => $label): ?><option value="<?php echo Template::escapeAttr($code); ?>" <?php if ((string) $filters[$field] === (string) $code) echo 'selected'; ?>><?php echo Template::escapeHtml($label); ?></option><?php endforeach; ?>
</select></div>
<?php endforeach; ?>
<?php foreach (array('dueFrom' => 'Due from', 'dueTo' => 'Due through', 'dataItemID' => 'Parent record ID') as $field => $label): ?>
<div class="col-sm-6 col-lg-4"><label class="form-label" for="<?php echo $field; ?>"><?php echo $label; ?></label><?php if ($field === 'dataItemID'): ?>
<input class="form-control form-control-sm" type="number" id="dataItemID" name="dataItemID" value="<?php echo Template::escapeAttr($filters[$field]); ?>">
<?php else: $dateName = $field; $dateValue = $filters[$field]; $dateSmall = true; $dateLabel = $label; include './modules/tasks/DateInput.tpl'; endif; ?></div>
<?php endforeach; ?>
<div class="col-12"><button class="btn btn-sm btn-primary" type="submit">Filter Tasks</button> <a class="btn btn-sm btn-outline-secondary" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks'); ?>">Reset to my open Tasks</a></div>
</form>
</div>
</section>
<section class="card">
<div class="card-header d-flex flex-wrap justify-content-between gap-2"><span><?php echo (int) $this->dataGrid->getNumberOfRows(); ?> authorised Tasks</span><?php $this->dataGrid->printNavigation(false); ?></div>
<div class="card-body p-2"><?php if ($this->dataGrid->getNumberOfRows()): $this->dataGrid->draw(); else: ?><p class="mb-0 text-body-secondary">No Tasks match these filters.</p><?php endif; ?></div>
<div class="card-footer"><?php $this->dataGrid->printNavigation(false); ?></div>
</section>
<?php include './modules/tasks/Footer.tpl'; ?>
