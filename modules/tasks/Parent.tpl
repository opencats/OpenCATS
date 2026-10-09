<section class="card mt-2" data-task-panel>
<div class="alert alert-success m-2 mb-0 d-none" data-task-notice role="status"></div>
<div class="card-header d-flex flex-wrap align-items-center gap-2"><h2 class="h6 mb-0">Open Tasks</h2>
<a class="btn btn-sm btn-outline-secondary ms-auto" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&assignedTo=all&status=&dataItemType=' . (int) $this->parentType . '&dataItemID=' . (int) $this->parentID); ?>">All linked Tasks</a>
<?php if ($_SESSION['CATS']->getAccessLevel('tasks.add') >= ACCESS_LEVEL_EDIT): ?>
<a class="btn btn-sm btn-primary" data-task-quick-add="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&a=quickAdd&dataItemType=' . (int) $this->parentType . '&dataItemID=' . (int) $this->parentID); ?>" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks&a=add&dataItemType=' . (int) $this->parentType . '&dataItemID=' . (int) $this->parentID); ?>">Add Task</a>
<?php endif; ?>
</div>
<div class="card-body p-2" data-task-rows>
<?php echo $this->taskRowsHTML; ?>
</div>
</section>
<link rel="stylesheet" href="<?php echo Template::escapeAttr(TemplateUtility::getVersionedAssetURL('modules/tasks/tasks.css')); ?>">
<script src="<?php echo Template::escapeAttr(TemplateUtility::getVersionedAssetURL('vendor/twbs/bootstrap/dist/js/bootstrap.bundle.min.js')); ?>" defer></script>
<script src="<?php echo Template::escapeAttr(TemplateUtility::getVersionedAssetURL('js/tasks.js')); ?>" defer></script>
