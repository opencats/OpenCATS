<?php TemplateUtility::printHeader('Recruitment Desks'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<main id="main" class="container-fluid py-2">
<h1 class="h5">Recruitment Desks</h1>
<p>Inactive Desks remain on existing users and Job Orders but are unavailable for new assignment. Renaming a Desk updates its displayed name on existing records.</p>
<?php if ($this->message !== ''): ?><p class="alert alert-success"><?php echo Template::escapeHtml($this->message); ?></p><?php endif; ?>
<?php foreach (array_merge($this->desks, array(array('deskID'=>'', 'name'=>'', 'isActive'=>1))) as $desk): ?>
<form class="card card-body mb-2" method="post" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName()); ?>?m=settings&amp;a=desks">
<input type="hidden" name="postback" value="postback">
<input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>">
<input type="hidden" name="deskID" value="<?php echo Template::escapeAttr($desk['deskID']); ?>">
<div class="row g-2 align-items-end">
<div class="col-sm-6"><label class="form-label" for="deskName<?php echo Template::escapeAttr($desk['deskID']); ?>"><?php echo $desk['deskID'] === '' ? 'New Desk name' : 'Desk name'; ?></label><input class="form-control form-control-sm" id="deskName<?php echo Template::escapeAttr($desk['deskID']); ?>" name="name" value="<?php echo Template::escapeAttr($desk['name']); ?>" maxlength="64" required></div>
<div class="col-sm-3"><label class="form-label" for="deskActive<?php echo Template::escapeAttr($desk['deskID']); ?>">Availability</label><select class="form-select form-select-sm" id="deskActive<?php echo Template::escapeAttr($desk['deskID']); ?>" name="isActive"><option value="1"<?php if ($desk['isActive']): ?> selected<?php endif; ?>>Active</option><option value="0"<?php if (!$desk['isActive']): ?> selected<?php endif; ?>>Inactive</option></select></div>
<div class="col-sm-3"><button class="btn btn-primary btn-sm" type="submit"><?php echo $desk['deskID'] === '' ? 'Add Desk' : 'Save Desk'; ?></button></div>
</div>
</form>
<?php endforeach; ?>
</main>
<?php TemplateUtility::printFooter(); ?>
