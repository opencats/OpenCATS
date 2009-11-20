<?php /* $Id: CreateAttachmentModal.tpl 3093 2007-09-24 21:09:45Z brian $ */ ?>
<?php TemplateUtility::printModalHeader('Candidates', array(''), 'Assign candidate tag'); ?>

    <?php if (!$this->isFinishedMode): ?>
		<form class="changeCandidateTags" id="changeCandidateTags" method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addCandidateTags">
			<input type="hidden" name="postback" id="postback" value="postback" />
			<input type="hidden" id="candidateID" name="candidateID" value="<?php echo($this->candidateID); ?>" />
			<ul>
				<?php $parent=""; foreach($this->tagsRS as $index => $data){ ?>
				<?php if ($parent != $data['parent_tag_title']){
			if ($parent != ""):?></ul><?php endif;?><li><?= $data['parent_tag_title'] ?></li><ul>
				<?php } ?><li><input type="checkbox" name="candidate_tags[]" id="checkbox<?= $i ?>" value="<?= $data['tag_id'] ?>" <?= in_array($data['tag_id'], $this->assignedTags)?'checked="checked"':''; ?>><label for="checkbox<?= $i++ ?>"><?= $data['tag_title'] ?></label></li>
				<?php $parent=$data['parent_tag_title']; }; ?>
			</ul>
            <input type="submit" class="button" name="submit" id="submit" value="Save" />&nbsp;
            <input type="button" class="button" name="cancel" value="Cancel" onclick="parentHidePopWin();" />
		</form>
    <?php else: ?>
    	<p>All data has been saved</p>
        <form>
            <input type="button" name="close" value="Close" onclick="parentHidePopWinRefresh();" />
        </form>
    <?php endif; ?>
    </body>
</html>
