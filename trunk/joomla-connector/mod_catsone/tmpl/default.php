<?php // no direct access
defined('_JEXEC') or die('Restricted access');
?>
<b class="ja-title">Type of job:</b><br />
<br /><a href="<?php echo JRoute::_('index.php?option=com_catsone'); ?>" class="button" style="text-decoration:none;">See all<?php echo ' ( '.$amount.' )';?></a>
<ul class="mostread">
	<?php
	foreach($list as $l):
	?>
	<li class="mostread<?php echo $params->get('moduleclass_sfx'); ?>">
		<a href="<?php echo JRoute::_('index.php?option=com_catsone&jobType='. $l['value']);?>"><?php echo $l['value'].' ( '.$l['count'].' )';?></a>
	</li>
	<?php
	endforeach;
	?>
</ul>
