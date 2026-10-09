<?php if (empty($this->tasks)): ?><p class="text-body-secondary mb-0">No accessible open Tasks.</p>
<?php else: ?><div class="table-responsive"><table class="table table-sm table-striped mb-0"><thead><tr><th>Task</th><th>Status</th><th>Assigned recruiter</th><th>Due date</th><th>Priority</th></tr></thead><tbody>
<?php foreach ($this->tasks as $row): ?>
<tr><td><?php echo TasksDataGrid::renderCell($row, 'title'); ?></td><td><?php echo TasksDataGrid::renderCell($row, 'status'); ?></td><td><?php echo Template::escapeHtml($this->users[$row['assignedTo'] ?? ''] ?? ('User #' . $row['assignedTo'] . ' (inactive)')); ?></td><td><?php echo TasksDataGrid::renderCell($row, 'dueDate'); ?></td><td><?php echo TasksDataGrid::renderCell($row, 'priority'); ?></td></tr>
<?php endforeach; ?>
</tbody></table></div><?php endif; ?>
