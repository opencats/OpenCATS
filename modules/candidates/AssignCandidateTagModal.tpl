<?php TemplateUtility::printModalHeader('Candidates', array(''), 'Assign candidate tag'); ?>
<main class="container-fluid p-2 oc-candidate-assigncandidatetagmodal">


    <?php if (!$this->isFinishedMode): ?>
        <form class="changeCandidateTags" id="changeCandidateTags" method="post" action="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=addCandidateTags">
            <input type="hidden" name="postback" id="postback" value="postback">
            <input type="hidden" id="candidateID" name="candidateID" value="<?php echo($this->candidateID); ?>">
            <ul>
            <?php $i=1;

            function drw($data, $id, $assignedTags){
                //global $i;
                foreach($data as $k => $v){
                    if ($v['tag_parent_id'] == $id){
                        ?><li><input type="checkbox" name="candidate_tags[]" id="checkbox<?= $i ?>" value="<?= $v['tag_id'] ?>" <?= in_array($v['tag_id'], $assignedTags)?'checked="checked"':''; ?> class="form-check-input"><label for="checkbox<?= $i++ ?>" class="form-label small mb-1"><?= $v['tag_title'] ?></label></li><?php
                        echo "\n<ul>" ;
                        drw($data, $v['tag_id'],$assignedTags);
                        echo "\n</ul>";
                    }
                }
            }
            drw($this->tagsRS, '', $this->assignedTags);
            ?></ul>

            <button type="submit" class="btn btn-sm btn-primary" name="submit" id="submit" value="Save">Save</button>&nbsp;
            <button type="button" class="btn btn-sm btn-outline-secondary" name="cancel" value="Cancel" onclick="parentHidePopWin();">Cancel</button>
        </form>
    <?php else: ?>
        <div class="alert alert-success py-2" role="alert">All data has been saved</div>
        <form>
            <button type="button" name="close" value="Close" onclick="parentHidePopWinRefresh();" class="btn btn-sm btn-outline-secondary">Close</button>
        </form>
    <?php endif; ?>
</main>
    </body>
</html>
