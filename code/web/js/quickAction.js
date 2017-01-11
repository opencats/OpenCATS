var quickAction = {};

quickAction.MenuOption = function(title, action)
{
    this.title = title;
    this.action = action;
};

quickAction.MenuOption.prototype.getTitle = function()
{
    return this.title;
};

quickAction.MenuOption.prototype.getAction = function()
{
    return this.action;
};


quickAction.MenuOption.prototype.getHtml = function()
{
    return '<a href="javascript:void(0);" onclick="' + this.getAction() + '">' + this.getTitle() + '</a><br />';
};

quickAction.LinkMenuOption = function(title, action, option)
{
    quickAction.MenuOption.call(this, title, action);
    this.option = option;
};

quickAction.LinkMenuOption.prototype = Object.create(quickAction.MenuOption.prototype);

quickAction.LinkMenuOption.prototype.getOption = function()
{
    return this.option;
};

quickAction.LinkMenuOption.prototype.getHtml = function()
{
    var message = "'Are you sure?'";
    var result;
    switch(this.getOption())
    {
        case 0:
            var itemAction = "'" + this.getAction() + "'";
            result = '<a href=# onclick="showPopWin(' + itemAction + ', 750, 540, null); return false;">' + this.getTitle() + '</a><br />';
            break;
        case 1:
        default:
            result = '<a href="' + this.getAction() + '" onclick="return confirm(' + message + ')">' + this.getTitle() + '</a><br />';
            break;
    }
    return result;
};


quickAction.DefaultMenu = function(menuDataItemType, menuDataItemId, menuX, menuY, permissions)
{
    this.element = document.getElementById('singleQuickActionMenu');
    this.menuDataItemType = menuDataItemType;
    this.menuDataItemId = menuDataItemId;
    this.menuX = menuX;
    this.menuY = menuY;
    this.permissions = permissions;
};

quickAction.DefaultMenu.prototype.getType = function()
{
    return this.menuDataItemType;
};

quickAction.DefaultMenu.prototype.getPermissions = function()
{
    return this.permissions;
};

quickAction.DefaultMenu.prototype.getId = function()
{
    return this.menuDataItemId;
};

quickAction.DefaultMenu.prototype.getOptions = function()
{
    return [
        new quickAction.MenuOption('Add To List', 'showQuickActionAddToList(' +  this.menuDataItemType + ', ' + this.menuDataItemId + ');')
    ];
};

quickAction.DefaultMenu.prototype.toggle = function()
{
    if (this.element.style.display != 'block')
    {
        this.element.style.display = 'block';
        this.element.style.left = this.menuX + 'px';
        this.element.style.top = this.menuY + 'px';
        this.element.innerHTML = '';
        var options = this.getOptions();
        for (var i = 0; i < options.length; ++i)
        {
            this.element.innerHTML += options[i].getHtml();
        }
    }
};

/* Creates and displays a popup menu for an individual data item on the page to do some simple action to. */
function showHideSingleQuickActionMenu(menu)
{
    menu.toggle();
};

/* Shows a popup for adding a item to a list. */
function showQuickActionAddToList(menuDataItemType, menuDataItemId)
{
    /* Create a popup window for adding this data item type to a list (content loaded from server) */
    showPopWin(CATSIndexName + '?m=lists&a=quickActionAddToListModal&dataItemType='+ menuDataItemType +'&dataItemID='+ menuDataItemId, 450, 350, null);
};

/* Shows a popup for adding a item to a list. */
function showQuickActionAddToPipeline(menuDataItemId)
{
    /* Create a popup window for adding this candidate to the pipeline */
    showPopWin(CATSIndexName + '?m=candidates&a=considerForJobSearch&candidateID=' + menuDataItemId, 750, 390, null);
};
