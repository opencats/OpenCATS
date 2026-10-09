<?php TemplateUtility::printHeader('Tasks', array('js/dataGrid.js', 'js/dataGridFilters.js', 'js/tasks.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
<?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">
<div id="contents">
<section class="oc-page-header d-flex flex-wrap align-items-center gap-2 mb-2">
<h1 class="h5 fw-semibold mb-0">Tasks</h1>
<a class="btn btn-sm btn-outline-secondary ms-auto" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName() . '?m=tasks'); ?>">My Tasks</a>
</section>
<?php if ($this->error !== ''): ?><div class="alert alert-danger" role="alert"><?php echo Template::escapeHtml($this->error); ?></div><?php endif; ?>
