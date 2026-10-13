<?php foreach ($reviewForm as $field => $value): ?>
<input type="hidden" name="<?php echo Template::escapeAttr($field); ?>" value="<?php echo Template::escapeAttr($value); ?>">
<?php endforeach; ?>
<input type="hidden" name="csrfToken" value="<?php echo Template::escapeAttr($_SESSION['CATS']->getCSRFToken()); ?>">
