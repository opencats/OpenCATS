quickAction.CandidateMenu = function(menuDataItemType, menuDataItemId, menuX, menuY, permissions)
{
    quickAction.DefaultMenu.call(this, menuDataItemType, menuDataItemId, menuX, menuY, permissions);
};

quickAction.CandidateMenu.prototype = Object.create(quickAction.DefaultMenu.prototype);

quickAction.CandidateMenu.prototype.getOptions = function()
{
    var options = quickAction.DefaultMenu.prototype.getOptions.call(this);
    options.push(new quickAction.MenuOption('Add To Pipeline', 'showQuickActionAddToPipeline(' + this.getType() + ');'));
    return options;
};
