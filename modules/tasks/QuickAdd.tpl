<div class="modal fade oc-task-quick-add" id="task-quick-add-modal" tabindex="-1" aria-labelledby="task-quick-add-heading" aria-hidden="true">
<div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
<form class="modal-content" method="post" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&a=quickAdd'); ?>">
<div class="modal-header py-2">
<h2 class="modal-title fs-6" id="task-quick-add-heading">Add Task</h2>
<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body p-3">
<input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>">
<input type="hidden" name="dataItemType" value="<?php echo (int) $this->context['type']; ?>">
<input type="hidden" name="dataItemID" value="<?php echo (int) $this->context['id']; ?>">
<div class="border rounded bg-body-tertiary p-2 mb-2" aria-label="Regarding">
<div class="small text-body-secondary">Regarding</div>
<div class="fw-semibold text-break"><?php echo Template::escapeHtml($this->context['name']); ?></div>
<?php if ($this->context['companyName'] !== ''): ?><div class="text-break"><?php echo Template::escapeHtml($this->context['companyName']); ?></div><?php endif; ?>
<div class="small text-body-secondary"><?php echo Template::escapeHtml(Tasks::getParentTypes()[$this->context['type']]); ?> #<?php echo (int) $this->context['id']; ?></div>
</div>
<div class="alert alert-danger py-2 d-none" data-task-error role="alert" tabindex="-1"></div>
<div class="mb-2"><label class="form-label mb-1" for="quick-task-title">Title <span class="text-danger">*</span></label><input class="form-control form-control-sm" id="quick-task-title" name="title" maxlength="255" required autofocus></div>
<div class="row g-2 mb-2">
<div class="col-sm-6"><label class="form-label mb-1" for="dueDate">Due date</label><?php $dateName = 'dueDate'; $dateValue = ''; $dateSmall = true; $dateLabel = 'Due date'; include './modules/tasks/DateInput.tpl'; ?></div>
<div class="col-sm-6"><label class="form-label mb-1" for="quick-task-priority">Priority</label><select class="form-select form-select-sm" id="quick-task-priority" name="priority">
<?php foreach (Tasks::getPriorities() as $code => $label): ?><option value="<?php echo Template::escapeAttr($code); ?>" <?php if ($this->task['priority'] === $code) echo 'selected'; ?>><?php echo Template::escapeHtml($label); ?></option><?php endforeach; ?>
</select></div>
</div>
<div class="mb-2"><label class="form-label mb-1" for="quick-task-assignee">Assigned recruiter</label><select class="form-select form-select-sm" id="quick-task-assignee" name="assignedTo">
<?php foreach ($this->users as $id => $name): ?><option value="<?php echo Template::escapeAttr($id); ?>" <?php if ((string) $this->task['assignedTo'] === (string) $id) echo 'selected'; ?>><?php echo Template::escapeHtml($name); ?></option><?php endforeach; ?>
</select></div>
<div class="mb-2"><label class="form-label mb-1" for="quick-task-description">Description / Notes</label><textarea class="form-control form-control-sm" id="quick-task-description" name="description" rows="3"></textarea></div>
<button class="btn btn-sm btn-link px-0" type="button" data-bs-toggle="collapse" data-bs-target="#quick-task-options" aria-expanded="false" aria-controls="quick-task-options">More options</button>
<div class="collapse" id="quick-task-options">
<div class="row g-2 pt-2">
<?php foreach (array('purpose' => array('Purpose', Tasks::getPurposes()), 'status' => array('Status', Tasks::getStatuses())) as $field => $definition): ?>
<div class="col-sm-6"><label class="form-label mb-1" for="quick-task-<?php echo $field; ?>"><?php echo $definition[0]; ?></label><select class="form-select form-select-sm" id="quick-task-<?php echo $field; ?>" name="<?php echo $field; ?>">
<?php foreach ($definition[1] as $code => $label): ?><option value="<?php echo Template::escapeAttr($code); ?>" <?php if ($this->task[$field] === $code) echo 'selected'; ?>><?php echo Template::escapeHtml($label); ?></option><?php endforeach; ?>
</select></div>
<?php endforeach; ?>
<div class="col-12"><label class="form-label mb-1" for="quick-task-pipeline">Candidate–Job Order pipeline context</label><input class="form-control form-control-sm" type="number" min="1" max="2147483647" id="quick-task-pipeline" name="candidateJobOrderID" list="quick-task-pipelines">
<datalist id="quick-task-pipelines"><?php foreach ($this->pipelineChoices as $id => $label): ?><option value="<?php echo (int) $id; ?>"><?php echo Template::escapeHtml($label); ?></option><?php endforeach; ?></datalist>
<div class="form-text">Optional existing pipeline ID. Suggestions are limited to authorised entries for this Candidate or Job Order.</div>
</div>
</div>
</div>
</div>
<div class="modal-footer py-2">
<button class="btn btn-sm btn-outline-secondary" type="button" data-bs-dismiss="modal">Cancel</button>
<button class="btn btn-sm btn-primary" type="submit">Save Task</button>
</div>
</form>
</div>
</div>
