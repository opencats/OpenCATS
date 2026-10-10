<?php TemplateUtility::printHeader('Relationship attention'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
<main id="main" class="container-fluid py-2">
<h1 class="h5">Relationship attention</h1>
<p>A Company is monitored when both its tier and lifecycle are enabled. Review intervals use calendar days. Changes affect derived attention results without changing classifications, Activities or Tasks.</p>
<?php if ($this->message !== ''): ?><p class="alert alert-success" role="status"><?php echo Template::escapeHtml($this->message); ?></p><?php endif; ?>
<form method="post" action="<?php echo Template::escapeAttr(CATSUtility::getIndexName()); ?>?m=settings&amp;a=relationshipAttention">
<input type="hidden" name="postback" value="postback">
<input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>">
<fieldset class="mb-3"><legend class="h6">Commercial tiers</legend>
<?php foreach ($this->attention['tiers'] as $code => $tier): ?>
<div class="row g-2 mb-2 align-items-center">
<label class="col-sm-3 col-form-label" for="attentionTier<?php echo Template::escapeAttr($code); ?>"><?php echo Template::escapeHtml(CompanySettings::formatTier($code, $this->tierLabels)); ?></label>
<div class="col-sm-3">
<select class="form-select form-select-sm" id="attentionTier<?php echo Template::escapeAttr($code); ?>" name="attention[tiers][<?php echo Template::escapeAttr($code); ?>][enabled]">
<option value="1"<?php if ($tier['enabled']) echo ' selected'; ?>>Enabled</option>
<option value="0"<?php if (!$tier['enabled']) echo ' selected'; ?>>Disabled</option>
</select></div>
<label class="col-sm-2 col-form-label" for="attentionDays<?php echo Template::escapeAttr($code); ?>">Review days</label>
<div class="col-sm-2"><input class="form-control form-control-sm" type="number" min="1" max="36500" id="attentionDays<?php echo Template::escapeAttr($code); ?>" name="attention[tiers][<?php echo Template::escapeAttr($code); ?>][days]" value="<?php echo Template::escapeAttr($tier['days'] ?? ''); ?>"></div>
</div>
<?php endforeach; ?>
<p class="form-text">Set a positive review interval before enabling D or Unclassified. Disabled tiers may have a blank interval.</p>
</fieldset>
<fieldset class="mb-3"><legend class="h6">Relationship lifecycles</legend>
<?php foreach ($this->attention['lifecycles'] as $status => $enabled): ?>
<div class="row g-2 mb-2">
<label class="col-sm-3 col-form-label" for="attentionStatus<?php echo Template::escapeAttr(str_replace(' ', '', $status)); ?>"><?php echo Template::escapeHtml($status); ?></label>
<div class="col-sm-3"><select class="form-select form-select-sm" id="attentionStatus<?php echo Template::escapeAttr(str_replace(' ', '', $status)); ?>" name="attention[lifecycles][<?php echo Template::escapeAttr($status); ?>]">
<option value="1"<?php if ($enabled) echo ' selected'; ?>>Enabled</option>
<option value="0"<?php if (!$enabled) echo ' selected'; ?>>Disabled</option>
</select></div></div>
<?php endforeach; ?>
</fieldset>
<button class="btn btn-primary btn-sm" type="submit">Save settings</button>
<a class="btn btn-outline-secondary btn-sm" href="<?php echo Template::escapeAttr(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration">Administration</a>
</form>
</main>
<?php TemplateUtility::printFooter(); ?>
