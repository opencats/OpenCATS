<?php
$label = $this->sectorMaintenance ? 'Sector' : 'Desk';
$title = $this->sectorMaintenance ? 'Job Order Sectors' : 'Recruitment Desks';
$action = $this->sectorMaintenance ? 'sectors' : 'desks';
$idField = $this->sectorMaintenance ? 'sectorID' : 'deskID';
$prefix = $this->sectorMaintenance ? 'sector' : 'desk';
?>
<?php TemplateUtility::printHeader($title); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<main id="main" class="container-fluid py-2">
<h1 class="h5"><?php echo Template::escapeHtml($title); ?></h1>
<p>Inactive <?php echo $label; ?>s remain on existing <?php echo $this->sectorMaintenance ? 'Job Orders' : 'users and Job Orders'; ?> but are unavailable for new assignment. Renaming a <?php echo $label; ?> updates its displayed name on existing records.</p>
<?php if ($this->message !== ''): ?><p class="alert alert-success"><?php echo Template::escapeHtml($this->message); ?></p><?php endif; ?>
<?php foreach (array_merge($this->desks, array(array($idField=>'', 'name'=>'', 'isActive'=>1))) as $desk): ?>
<form class="card card-body mb-2" method="post" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName()); ?>?m=settings&amp;a=<?php echo $action; ?>">
<input type="hidden" name="postback" value="postback">
<input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>">
<input type="hidden" name="<?php echo $idField; ?>" value="<?php echo Template::escapeAttr($desk[$idField]); ?>">
<div class="row g-2 align-items-end">
<div class="col-sm-6"><label class="form-label" for="<?php echo $prefix; ?>Name<?php echo Template::escapeAttr($desk[$idField]); ?>"><?php echo $desk[$idField] === '' ? 'New ' . $label . ' name' : $label . ' name'; ?></label><input class="form-control form-control-sm" id="<?php echo $prefix; ?>Name<?php echo Template::escapeAttr($desk[$idField]); ?>" name="name" value="<?php echo Template::escapeAttr($desk['name']); ?>" maxlength="64" required></div>
<div class="col-sm-3"><label class="form-label" for="<?php echo $prefix; ?>Active<?php echo Template::escapeAttr($desk[$idField]); ?>">Availability</label><select class="form-select form-select-sm" id="<?php echo $prefix; ?>Active<?php echo Template::escapeAttr($desk[$idField]); ?>" name="isActive"><option value="1"<?php if ($desk['isActive']): ?> selected<?php endif; ?>>Active</option><option value="0"<?php if (!$desk['isActive']): ?> selected<?php endif; ?>>Inactive</option></select></div>
<div class="col-sm-3"><button class="btn btn-primary btn-sm" type="submit"><?php echo $desk[$idField] === '' ? 'Add ' . $label : 'Save ' . $label; ?></button></div>
</div>
</form>
<?php endforeach; ?>
</main>
<?php TemplateUtility::printFooter(); ?>
