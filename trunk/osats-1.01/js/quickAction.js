/*
 * OSATS
 * GNU License
*/

var _singleQuickActionMenuDataItemType;
var _singleQuickActionMenuDataItemID;

/* Creates and displays a popup menu for an individual data item on the page to do some simple action to. */
function showHideSingleQuickActionMenu(dataItemType, dataItemID, menuX, menuY)
{
    var singleQuickActionMenu = document.getElementById('singleQuickActionMenu');

    if (singleQuickActionMenu.style.display == 'block')
    {
        closeQuickActionMenu();
        return;
    }

    singleQuickActionMenu.style.display = 'block';
    singleQuickActionMenu.style.left = menuX + 'px';
    singleQuickActionMenu.style.top = menuY + 'px';
    singleQuickActionMenu.innerHTML = '';
    _singleQuickActionMenuDataItemType = dataItemType;
    _singleQuickActionMenuDataItemID = dataItemID;

    addItemToPopupMenu('Add To List', 'showQuickActionAddToList();');

    switch (dataItemType)
    {
        case DATA_ITEM_CANDIDATE:
            addItemToPopupMenu('Add To Pipeline', 'showQuickActionAddToPipeline();');
            break;
    }
}

/* Shows a popup for adding a item to a list. */
function showQuickActionAddToList()
{
    /* Create a popup window for adding this data item type to a list (content loaded from server) */
    showPopWin(CATSIndexName + '?m=lists&a=quickActionAddToListModal&dataItemType='+_singleQuickActionMenuDataItemType+'&dataItemID='+_singleQuickActionMenuDataItemID, 450, 350, null);
}

/* Shows a popup for adding a item to a list. */
function showQuickActionAddToPipeline()
{
    /* Create a popup window for adding this candidate to the pipeline */
    showPopWin(CATSIndexName + '?m=candidates&a=considerForJobSearch&candidateID='+_singleQuickActionMenuDataItemID, 750, 390, null);
}

function addItemToPopupMenu(itemTitle, itemAction)
{
    var singleQuickActionMenu = document.getElementById('singleQuickActionMenu');

    singleQuickActionMenu.innerHTML += '<a href="javascript:void(0);" onclick="' + itemAction +' closeQuickActionMenu();">' + itemTitle + '</a><br />';
}

function closeQuickActionMenu()
{
    var singleQuickActionMenu = document.getElementById('singleQuickActionMenu');
    singleQuickActionMenu.style.display = 'none';
}
