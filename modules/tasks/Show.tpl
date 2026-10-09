<?php include './modules/tasks/Header.tpl'; ?>
<?php if ($this->notice === 'saved'): ?><div class="alert alert-success" role="status">Task saved.</div><?php endif; ?>
<?php if ($this->notice === 'activity'): ?>
<div class="alert alert-success" role="status">Task completed. No Activity has been recorded. Use the existing Activity form below to record an actual interaction. Cancelling or failing that form leaves this Task completed.</div>
<?php endif; ?>
<section class="card mb-2">
<div class="card-header d-flex flex-wrap align-items-center gap-2"><h2 class="h6 mb-0"><?php echo Template::escapeHtml($this->task['title']); ?></h2>
<?php if ($this->permissions['edit']): ?><a class="btn btn-sm btn-primary ms-auto" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&a=edit&taskID=' . (int) $this->task['taskID']); ?>">Edit Task</a><?php endif; ?></div>
<div class="card-body">
<p class="text-break"><?php echo nl2br(Template::escapeHtml($this->task['description'])); ?></p>
<dl class="row mb-0">
<?php foreach (array('Status' => Tasks::getStatuses()[$this->task['status']], 'Priority' => Tasks::getPriorities()[$this->task['priority']],
    'Due date' => $this->task['dueDate'] === null ? 'Undated' : TaskPresentation::date($this->task['dueDate']), 'Purpose' => Tasks::getPurposes()[$this->task['purpose']],
    'Assigned recruiter' => $this->users[$this->task['assignedTo'] ?? ''] ?? 'Unassigned',
    'Pipeline context' => $this->task['candidateJobOrderID'] === null ? 'None' : 'Pipeline #' . $this->task['candidateJobOrderID'],
    'Created' => TaskPresentation::dateTime($this->task['dateCreated']) . ' (user #' . $this->task['createdBy'] . ')',
    'Modified' => TaskPresentation::dateTime($this->task['dateModified']), 'Completed' => $this->task['dateCompleted'] === null ? '—' : TaskPresentation::dateTime($this->task['dateCompleted']) . ' (user #' . $this->task['completedBy'] . ')') as $label => $value): ?>
<dt class="col-sm-3"><?php echo $label; ?></dt><dd class="col-sm-9"><?php echo Template::escapeHtml($value); ?></dd>
<?php endforeach; ?>
<dt class="col-sm-3">Parent</dt><dd class="col-sm-9">
<?php if ($this->task['dataItemType'] === null): ?>Standalone
<?php else: ?><a href="<?php echo Template::escapeAttr(TasksUI::parentURL($this->task['dataItemType'], $this->task['dataItemID'])); ?>"><?php echo Template::escapeHtml(Tasks::getParentTypes()[$this->task['dataItemType']] . ' #' . $this->task['dataItemID']); ?></a><?php endif; ?>
</dd>
</dl>
</div>
<div class="card-footer d-flex flex-wrap gap-2">
<?php foreach (array('complete' => 'Complete', 'cancel' => 'Cancel Task', 'reopen' => 'Reopen', 'completeLog' => 'Complete & Log Activity') as $action => $label): ?>
<?php if (!$this->permissions[$action === 'completeLog' ? 'complete' : $action] || ($action === 'completeLog' && $this->task['dataItemType'] === null && empty($this->activityTarget))) continue; ?>
<form method="post" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&a=' . $action); ?>">
<input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>">
<input type="hidden" name="taskID" value="<?php echo (int) $this->task['taskID']; ?>">
<button class="btn btn-sm btn-outline-primary" type="submit"><?php echo Template::escapeHtml($label); ?></button>
</form>
<?php endforeach; ?>
</div>
</section>
<?php if ($this->notice === 'activity' && $this->task['status'] === 'completed' && ($this->task['dataItemType'] !== null || !empty($this->activityTarget))): ?>
<section class="card mb-2"><div class="card-body">
<?php
$type = $this->task['dataItemType'];
$activityURL = '';
if (isset($this->activityTarget['candidateID']) && $_SESSION['CATS']->getAccessLevel('pipelines.addActivity') >= ACCESS_LEVEL_EDIT)
    $activityURL = CATSUtility::getIndexName() . '?m=candidates&a=addActivity&candidateID=' . (int) $this->activityTarget['candidateID'] . (isset($this->activityTarget['jobOrderID']) ? '&jobOrderID=' . (int) $this->activityTarget['jobOrderID'] : '');
elseif (isset($this->activityTarget['contactID']) && $_SESSION['CATS']->getAccessLevel('contacts.addActivityScheduleEvent') >= ACCESS_LEVEL_EDIT)
    $activityURL = CATSUtility::getIndexName() . '?m=contacts&a=addActivityScheduleEvent&contactID=' . (int) $this->activityTarget['contactID'];
?>
<?php if ($activityURL !== ''): ?>
<a class="btn btn-sm btn-primary" href="<?php echo Template::escapeAttr($activityURL); ?>" onclick="showPopWin(<?php echo Template::escapeJsAttr($activityURL); ?>, 600, 480, null); return false;">Open Activity form</a>
<?php elseif ($type === null): ?>
<p class="mb-0">You do not have permission to log an Activity for this context.</p>
<?php else: ?>
<p class="mb-2">Use the linked record's existing Activity workflow. Company interactions are recorded against a Contact; Job Order interactions require a Candidate in its pipeline. Existing Activity permissions apply.</p>
<a class="btn btn-sm btn-primary" href="<?php echo Template::escapeAttr(TasksUI::parentURL($type, $this->task['dataItemID'])); ?>">Open linked record to log Activity</a>
<?php endif; ?>
<p class="small text-body-secondary mt-2 mb-0">Record the interaction once. Reloading or repeating Task completion does not submit an Activity.</p>
</div></section>
<?php endif; ?>
<section class="card">
<div class="card-header"><h2 class="h6 mb-0">Task history</h2></div>
<div class="card-body">
<?php if (empty($this->history)): ?><p class="mb-0 text-body-secondary">No history is available within your current access.</p>
<?php else: ?>
<div class="table-responsive"><table class="table table-sm table-striped mb-0"><thead><tr><th>Date</th><th>Recruiter</th><th>Field</th><th>Previous value</th><th>New value</th></tr></thead><tbody>
<?php foreach ($this->history as $row): ?>
<?php
$fieldLabels = array('!newEntry!' => 'Created', 'title' => 'Title', 'description' => 'Description',
    'dueDate' => 'Due date', 'priority' => 'Priority', 'status' => 'Status', 'purpose' => 'Purpose',
    'assignedTo' => 'Assigned recruiter', 'parent' => 'Parent', 'candidateJobOrderID' => 'Pipeline context',
    'createdBy' => 'Created by', 'dateCreated' => 'Created', 'completedBy' => 'Completed by', 'dateCompleted' => 'Completed');
$choices = array('status' => Tasks::getStatuses(), 'priority' => Tasks::getPriorities(), 'purpose' => Tasks::getPurposes());
if (isset($choices[$row['theField']]))
    foreach (array('previousValue', 'newValue') as $valueField)
        $row[$valueField] = $choices[$row['theField']][$row[$valueField]] ?? $row[$valueField];
$row['dateModified'] = TaskPresentation::dateTime($row['setDate']);
if (in_array($row['theField'], array('dueDate', 'dateCreated', 'dateModified', 'dateCompleted'), true))
    foreach (array('previousValue', 'newValue') as $valueField)
        $row[$valueField] = $row['theField'] === 'dueDate' ? TaskPresentation::date($row[$valueField]) : TaskPresentation::dateTime($row[$valueField]);
$row['theField'] = $fieldLabels[$row['theField']] ?? $row['theField'];
?>
<tr>
<?php foreach (array('dateModified', 'enteredByFullName', 'theField', 'previousValue', 'newValue') as $field): ?><td class="text-break"><?php echo Template::escapeHtml($row[$field] ?? ($field === 'theField' ? 'Created' : '')); ?></td><?php endforeach; ?>
</tr><?php endforeach; ?>
</tbody></table></div>
<?php endif; ?>
</div>
</section>
<?php include './modules/tasks/Footer.tpl'; ?>
