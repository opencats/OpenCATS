<?php TemplateUtility::printHeader('Company commercial tier labels'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<main id="main" class="container-fluid py-2">
<h1 class="h5">Company commercial tier labels</h1>
<p>Change display labels without changing stored A/B/C/D codes. Leave a label blank to use its letter. Classification remains optional and independent of lifecycle and Hot status.</p>
<?php if ($this->message !== ''): ?><p class="alert alert-success" role="status"><?php echo Template::escapeHtml($this->message); ?></p><?php endif; ?>
<form method="post" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName()); ?>?m=settings&amp;a=companyClassification">
<input type="hidden" name="postback" value="postback">
<input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>">
<?php foreach ($this->tierLabels as $code => $label): ?>
<div class="row mb-2">
<label class="col-sm-2 col-form-label" for="tierLabel<?php echo Template::escapeAttr($code); ?>">Tier <?php echo Template::escapeHtml($code); ?></label>
<div class="col-sm-6"><input class="form-control form-control-sm" id="tierLabel<?php echo Template::escapeAttr($code); ?>" name="tierLabels[<?php echo Template::escapeAttr($code); ?>]" value="<?php echo Template::escapeAttr($label); ?>" maxlength="80" type="text"></div>
</div>
<?php endforeach; ?>
<button class="btn btn-primary btn-sm" type="submit">Save labels</button>
<a class="btn btn-outline-secondary btn-sm" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration">Administration</a>
</form>
</main>
<?php TemplateUtility::printFooter(); ?>
