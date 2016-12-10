quickAction.DuplicateCandidateMenu = function(menuDataItemType, menuDataItemId, menuX, menuY, mergeUrl, removeUrl)
{
    quickAction.DefaultMenu.call(this, menuDataItemType, menuDataItemId, menuX, menuY);
    this.mergeUrl = mergeUrl;
    this.removeUrl = removeUrl;
}

quickAction.DuplicateCandidateMenu.prototype = Object.create(quickAction.DefaultMenu.prototype);

quickAction.DuplicateCandidateMenu.prototype.getOptions = function()
{
    return [
        new quickAction.LinkMenuOption('Merge', this.urlDecode(this.mergeUrl), 0),
        new quickAction.LinkMenuOption('Remove duplicity warning', this.urlDecode(this.removeUrl), 1)
    ];
}

quickAction.DuplicateCandidateMenu.prototype.urlDecode = function(url)
{
    return decodeURIComponent(url.replace(/\+/g, ' '));
}
