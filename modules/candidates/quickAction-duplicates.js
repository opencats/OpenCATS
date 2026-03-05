quickAction.CandidateDuplicateMenu = function(menuDataItemType, menuDataItemId, menuX, menuY, permissions, mergeUrl, removeUrl)
{
    quickAction.DefaultMenu.call(this, menuDataItemType, menuDataItemId, menuX, menuY, permissions);
    this.mergeUrl = mergeUrl;
    this.removeUrl = removeUrl;
};

quickAction.CandidateDuplicateMenu.prototype = Object.create(quickAction.DefaultMenu.prototype);

quickAction.CandidateDuplicateMenu.prototype.getOptions = function()
{
    if(this.getPermissions().candidates_merge)
    {
        return [
            new quickAction.LinkMenuOption("Merge", this.urlDecode(this.mergeUrl), 0),
            new quickAction.MenuOption(
                "Remove duplicity warning",
                "return quickActionPostFromUrl('" + this.urlDecode(this.removeUrl) + "', 'Are you sure?');"
            )
        ];
    }
    return null;
};

quickAction.CandidateDuplicateMenu.prototype.urlDecode = function(url)
{
    return decodeURIComponent(url.replace(/\+/g, " "));
};
