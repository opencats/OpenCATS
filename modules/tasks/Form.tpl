<?php include './modules/tasks/Header.tpl'; ?>
<section class="card">
<div class="card-header"><h2 class="h6 mb-0"><?php echo $this->action === 'add' ? 'Add Task' : 'Edit Task'; ?></h2></div>
<div class="card-body">
<form method="post" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&a=' . $this->action); ?>">
<input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>">
<?php if (isset($this->task['taskID'])): ?><input type="hidden" name="taskID" value="<?php echo (int) $this->task['taskID']; ?>"><?php endif; ?>
<div class="row g-3">
<div class="col-12"><label class="form-label" for="title">Title <span class="text-danger">*</span></label><input class="form-control" id="title" name="title" maxlength="255" required value="<?php echo Template::escapeAttr($this->task['title']); ?>"></div>
<div class="col-12"><label class="form-label" for="description">Description</label><textarea class="form-control" id="description" name="description" rows="4"><?php echo Template::escapeHtml($this->task['description']); ?></textarea></div>
<div class="col-md-6"><label class="form-label" for="dueDate">Due date</label><?php $dateName = 'dueDate'; $dateValue = $this->task['dueDate'] ?? ''; $dateSmall = false; $dateLabel = 'Due date'; include './modules/tasks/DateInput.tpl'; ?><div class="form-text">Optional calendar date; leave blank for undated work.</div></div>
<?php foreach (array('priority' => array('Priority', Tasks::getPriorities()), 'status' => array('Status', Tasks::getStatuses()), 'purpose' => array('Purpose', Tasks::getPurposes())) as $field => $definition): ?>
<div class="col-md-6"><label class="form-label" for="<?php echo $field; ?>"><?php echo $definition[0]; ?></label><select class="form-select" id="<?php echo $field; ?>" name="<?php echo $field; ?>">
<?php foreach ($definition[1] as $code => $label): ?><option value="<?php echo Template::escapeAttr($code); ?>" <?php if ($this->task[$field] === $code) echo 'selected'; ?>><?php echo Template::escapeHtml($label); ?></option><?php endforeach; ?>
</select></div>
<?php endforeach; ?>
<?php if ($this->permissions['manage']): ?>
<div class="col-md-6"><label class="form-label" for="assignedTo">Assigned recruiter</label><select class="form-select" id="assignedTo" name="assignedTo">
<?php foreach ($this->users as $id => $name): ?><option value="<?php echo Template::escapeAttr($id); ?>" <?php if ((string) $this->task['assignedTo'] === (string) $id) echo 'selected'; ?>><?php echo Template::escapeHtml($name); ?></option><?php endforeach; ?>
</select><div class="form-text">Standalone Tasks require an assignee.</div></div>
<div class="col-md-6"><label class="form-label" for="dataItemType">Parent type</label><select class="form-select" id="dataItemType" name="dataItemType"><option value="">Standalone</option>
<?php foreach (Tasks::getParentTypes() as $id => $name): ?><option value="<?php echo (int) $id; ?>" <?php if ($this->task['dataItemType'] == $id) echo 'selected'; ?>><?php echo Template::escapeHtml($name); ?></option><?php endforeach; ?>
</select></div>
<div class="col-md-6"><label class="form-label" for="dataItemID">Parent record ID</label><input class="form-control" type="number" min="1" max="2147483647" id="dataItemID" name="dataItemID" value="<?php echo Template::escapeAttr($this->task['dataItemID']); ?>"><div class="form-text">Use Add Task on a record to preselect its parent. Clear both parent fields for standalone work.</div></div>
<div class="col-md-6"><label class="form-label" for="candidateJobOrderID">Candidate–Job Order pipeline ID</label><input class="form-control" type="number" min="1" max="2147483647" id="candidateJobOrderID" name="candidateJobOrderID" list="task-pipelines" value="<?php echo Template::escapeAttr($this->task['candidateJobOrderID']); ?>"><datalist id="task-pipelines"><?php foreach ($this->pipelineChoices as $pipelineID => $label): ?><option value="<?php echo (int) $pipelineID; ?>"><?php echo Template::escapeHtml($label); ?></option><?php endforeach; ?></datalist><div class="form-text">Optional existing pipeline entry. Suggestions use the current Candidate or Job Order parent. Both records must be accessible and match a Candidate or Job Order parent.</div></div>
<?php else: ?>
<div class="col-12"><p class="text-body-secondary mb-0">Assignment and record links can be changed only by an authorised creator or administrator.</p></div>
<?php endif; ?>
<div class="col-12 d-flex gap-2"><button class="btn btn-primary" type="submit">Save Task</button><a class="btn btn-outline-secondary" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks' . (isset($this->task['taskID']) ? '&a=show&taskID=' . (int) $this->task['taskID'] : '')); ?>">Cancel</a></div>
</div>
</form>
</div>
</section>
<?php include './modules/tasks/Footer.tpl'; ?>
