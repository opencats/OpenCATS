<?php
chdir('./../../');
include_once('./config.php');
?>

quickAction.CandidateMenu = function(menuDataItemType, menuDataItemId, menuX, menuY, permissions)
{
    quickAction.DefaultMenu.call(this, menuDataItemType, menuDataItemId, menuX, menuY, permissions);
};

quickAction.CandidateMenu.prototype = Object.create(quickAction.DefaultMenu.prototype);

quickAction.CandidateMenu.prototype.getOptions = function()
{
    var options = quickAction.DefaultMenu.prototype.getOptions.call(this);
    if(this.getPermissions().pipelines_addToPipeline)
    {
        options.push(new quickAction.MenuOption('<?php echo __("Add To Pipeline");?>', 'showQuickActionAddToPipeline(' + this.getType() + ');'));
    }

    return options;
};
