<?php
$reviewNotice = $_GET['warningReview'] ?? null;
if (is_string($reviewNotice) && in_array($reviewNotice, array('saved', 'restored'), true)):
?>
<div class="alert alert-success" role="status"><?php echo $reviewNotice === 'restored' ? 'Warning restored through Show Again.' : 'Personal review saved. The selected review date is shown below.'; ?></div>
<?php endif; ?>
