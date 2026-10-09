<?php
$dateValid = true;
try { TaskPresentation::parseDate($dateValue); }
catch (InvalidArgumentException $e) { $dateValid = false; }
?>
<span data-task-date data-date-name="<?php echo Template::escapeAttr($dateName); ?>" data-date-format="<?php echo Template::escapeAttr(TaskPresentation::dateFormat()); ?>" data-date-valid="<?php echo $dateValid ? '1' : '0'; ?>">
<input class="form-control<?php if ($dateSmall) echo ' form-control-sm'; ?>" type="text" id="<?php echo Template::escapeAttr($dateName); ?>" name="<?php echo Template::escapeAttr($dateName); ?>" value="<?php echo Template::escapeAttr($dateValue); ?>" placeholder="<?php echo Template::escapeAttr(TaskPresentation::dateFormat()); ?>" aria-label="<?php echo Template::escapeAttr($dateLabel . ' (' . TaskPresentation::dateFormat() . ')'); ?>">
</span>
