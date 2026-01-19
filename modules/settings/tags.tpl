<?php 
/* $Id: EmailSettings.tpl 3310 2007-10-25 21:24:20Z brian $ */ ?>

<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'modules/settings/Settings.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: Administration</h2></td>
                </tr>
            </table>

            <p class="note">Tags Settings</p>

            <table>
                <tr>
                    <td>
<style>
	.lst {list-style:none;}
	.lst ul {list-style:none;}
	.lst li {list-style:none;margin: 2px auto 2px auto;}
	.lst li div {display:inline}
	form {display:inline}
	.lst li img {border:none;vertical-align:middle;}
</style>

<script type="text/javascript" language="javascript"><!--

var lastEdited=null;
function doAdd(frm){
	$.ajax({
		type: frm.method,
		url: frm.action,
		data: $(frm).serialize(),
		success: function(data,textStatus){
			showNew(frm,data);
			}
	});
}

function showNew(frm,data){
	var ul = $(frm).parent().parent();
	$(ul).prepend(data);
	
}

function doDelete(id){
	//alert($('#id_li_tag_'+id).html());return;
	
	if (!confirm('Are you sure you want to delete this tag?')) return;
	$.ajax({
		type: 'POST',
		url: '<?= CATSUtility::getIndexName() ?>?m=settings&a=ajax_tags_del',
		data: ({tag_id:id}),
		success: function(data,textStatus){
				$('#id_li_tag_'+id).remove();
			}
	});
}

function doSave(frm){ $.ajax({type: frm.method, url: frm.action, data: $(frm).serialize(), success: function(data,textStatus){ endEdit(data);}});}

function endEdit(data){ if (lastEdited){ $(lastEdited).find("div").html(""); $(lastEdited).find("a").html(data);$(lastEdited).find("a").show(); }}
function editTag(id){
	el='#id_tag_'+id;
	if (lastEdited){
		$(lastEdited).find("div").html("");
		$(lastEdited).find("a").show();
	}

	$(el).find("a").hide();
	$(el).find("div").html('<form method="post" action="<?= CATSUtility::getIndexName() ?>?m=settings&amp;a=ajax_tags_upd"><input type="hidden" name="tag_id" value="' + id + '" /><input type="text" name="tag_title" value="'+$(el).find('a').html()+'" /> <input type="button" value="save" onclick="doSave(this.form);" /></form>');
	lastEdited = el;
}
-->
</script>
                            <table class="editTable" width="700">

                                <tr id="fromAddressRow">
                                    <td class="tdVertical" style="width: 175px;">
                                        <label>Add/Remove Tags</label>
                                    </td>
                                    <td class="tdData">
                                    	<div  class="lst">
										<ul>
										<?php if (empty($this->tagsRS)) { ?>
											<li><img src="images/actions/add.gif" />
												<form method="post" action="<?= CATSUtility::getIndexName() ?>?m=settings&amp;a=ajax_tags_add">
													<input type="text" name="tag_title" value="" />
													<input type="button" value="Add" onclick="doAdd(this.form);" />
												</form>
											</li>
										<?php } else { ?>
										<?php $f = true; $parent=""; $tag_parent_id = 0; foreach($this->tagsRS as $index => $data){ ?>
											<?php if ($data['tag_parent_id'] == ""){ ?>
											<?php if (!$f) {?>
													<li><img src="images/actions/add.gif" />
														<form method="post" action="<?= CATSUtility::getIndexName() ?>?m=settings&amp;a=ajax_tags_add">
															<input type="hidden" name="tag_parent_id" value="<?= $tag_parent_id ?>" />
															<input type="text" name="tag_title" value="" />
															<input type="button" value="Add" onclick="doAdd(this.form);" />
														</form>
													</li>

												</ul>
											</li>
											<?php } $f = false;
											
												if ($data['tag_parent_id']){
													$tag_parent_id = $data['tag_parent_id'];
												}
												else{ 
													$tag_parent_id = $data['tag_id'];
												}
											
											?>
											<li id="id_li_tag_<?= $data['tag_id'] ?>"><a href="javascript:;" onclick="doDelete(<?= $tag_parent_id ?>);"><img src="images/actions/delete.gif"/></a> <?= $data['tag_title'] ?><ul>
											<?php } else {  ?>
											<li id="id_li_tag_<?= $data['tag_id'] ?>">
												<a href="javascript:;" onclick="doDelete(<?= $data['tag_id'] ?>);"><img src="images/actions/delete.gif" /></a>
												<div id="id_tag_<?= $data['tag_id'] ?>">
													<a href="javascript:;" onclick="editTag(<?= $data['tag_id'] ?>);"><?= $data['tag_title'] ?></a><div></div>
												</div>
											</li>
										<?php } }?>
												<li id="id_li_tag_<?= $data['tag_id'] ?>"><img src="images/actions/add.gif" />
													<form method="post" action="<?= CATSUtility::getIndexName() ?>?m=settings&amp;a=ajax_tags_add">
														<input type="hidden" name="tag_parent_id" value="<?= $tag_parent_id ?>" />
														<input type="text" name="tag_title" value="" />
														<input type="button" value="Add" onclick="doAdd(this.form);" />
													</form>
												</li>
											</ul>
										</li>
										<li>
											<img src="images/actions/add.gif" />
											<form method="post" action="<?= CATSUtility::getIndexName() ?>?m=settings&amp;a=ajax_tags_add">
												<input type="text" name="tag_title" value="" />
												<input type="button" value="Add" onclick="doAdd(this.form);" />
											</form>
										</li>
										<?php } ?>
									</div>
                                  </td>
                                </tr>
                            </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
