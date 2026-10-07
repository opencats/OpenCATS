<?php
$tierLabels = (new CompanySettings())->getAll();
$classificationFields = array(
    'commercialTier' => array('Commercial tier', $tierLabels),
    'relationshipStatus' => array('Relationship lifecycle', array_combine(
        \OpenCATS\Entity\Company::getRelationshipStatuses(),
        \OpenCATS\Entity\Company::getRelationshipStatuses()
    ))
);
?>
<section class="card mb-2">
<div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">Company classification</div>
<div class="card-body p-2 row g-2">
<?php foreach ($classificationFields as $field => $definition): ?>
<div class="col-sm-6">
<label class="form-label" for="<?php echo Template::escapeAttr($field); ?>"><?php echo Template::escapeHtml($definition[0]); ?></label>
<select class="form-select form-select-sm" id="<?php echo Template::escapeAttr($field); ?>" name="<?php echo Template::escapeAttr($field); ?>">
<option value="">Unclassified</option>
<?php foreach ($definition[1] as $code => $label): ?>
<option value="<?php echo Template::escapeAttr($code); ?>"<?php if (($this->data[$field] ?? null) === $code): ?> selected<?php endif; ?>><?php echo Template::escapeHtml($field === 'commercialTier' ? CompanySettings::formatTier($code, $tierLabels) : $label); ?></option>
<?php endforeach; ?>
</select>
</div>
<?php endforeach; ?>
</div>
</section>
