<?php /* $Id: CreateAttachmentModal.tpl 3093 2007-09-24 21:09:45Z brian $ */ ?>
<?php TemplateUtility::printModalHeader(__('Candidates'), array(''), __('Assign candidate tag')); ?>

    <?php if (!$this->isFinishedMode): ?>
    	
		<form class="changeCandidateTags" id="changeCandidateTags" method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addCandidateTags">
			<input type="hidden" name="postback" id="postback" value="postback" />
			<input type="hidden" id="candidateID" name="candidateID" value="<?php echo($this->candidateID); ?>" />
			<div><?php //style=" height:  $_GET['frameHeight']-40;px;overflow: scroll;"?>
            <ul>	                                        
			<?php $i=1;
			
			function drw($data, $id, $assignedTags){
				//global $i;
				foreach($data as $k => $v){
					if ($v['tag_parent_id'] == $id){
						?><li><input type="checkbox" name="candidate_tags[]" id="checkbox<?= $i ?>" value="<?= $v['tag_id'] ?>" <?= in_array($v['tag_id'], $assignedTags)?'checked="checked"':''; ?>><label for="checkbox<?= $i++ ?>"><?= $v['tag_title'] ?></label></li><?php 
						echo "\n<ul>" ;
						drw($data, $v['tag_id'],$assignedTags);
						echo "\n</ul>";
					}
				}
			}
			drw($this->tagsRS, '', $this->assignedTags);
			?></ul>
			</div>
            <input type="submit" class="button" name="submit" id="submit" value="<?php echo __("Save");?>" />&nbsp;
            <input type="button" class="button" name="cancel" value="<?php echo __("Cancel");?>" onclick="parentHidePopWin();" />
		</form>
		
    <?php else: ?>
    	<p><?php echo __("All data has been saved");?></p>
        <form>
            <input type="button" name="close" value="<?php echo __("Close");?>" onclick="parentHidePopWinRefresh();" />
        </form>
    <?php endif; ?>
    </body>
</html>
