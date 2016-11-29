/*
 * CATS
 * Quick Action JavaScript Library
 *
 * Portions Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 * $Id: quickAction.js 3198 2007-10-14 23:36:43Z will $
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
    
  
    
    switch (dataItemType)
    {
        case DATA_ITEM_CANDIDATE:
            addItemToPopupMenu('Add To List', 'showQuickActionAddToList();');
            addItemToPopupMenu('Add To Pipeline', 'showQuickActionAddToPipeline();');
            break;
        default:
            addItemToPopupMenu('Add To List', 'showQuickActionAddToList();');
    }
}

function showHideSingleQuickActionMenuExtended(dataItemType, dataItemID, menuX, menuY, url1, url2)
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
    
  
    
    switch (dataItemType)
    {
        case DATA_ITEM_DUPLICATE:
            addLinkToPopupMenu('Merge', urldecode(url1), 0);
            addLinkToPopupMenu('Remove duplicity warning', urldecode(url2), 1);
            break;
        default:
            addItemToPopupMenu('Add To List', 'showQuickActionAddToList();');
    }
}

function urldecode(url) {
  return decodeURIComponent(url.replace(/\+/g, ' '));
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

function addLinkToPopupMenu(itemTitle, itemAction, option)
{
    var singleQuickActionMenu = document.getElementById('singleQuickActionMenu');
    var message = "'Are you sure?'";
    switch(option)
    {
        case 0:
            itemAction = "'" + itemAction + "'";
            singleQuickActionMenu.innerHTML += '<a href=# onclick="showPopWin(' + itemAction + ', 750, 540, null); return false;">' + itemTitle + '</a><br />';
            break;
        case 1:
        default:
            singleQuickActionMenu.innerHTML += '<a href="' + itemAction + '" onclick="return confirm(' + message + ')">' + itemTitle + '</a><br />';
            break;
    }
    
}

function closeQuickActionMenu()
{
    var singleQuickActionMenu = document.getElementById('singleQuickActionMenu');
    singleQuickActionMenu.style.display = 'none';
}

function mergeCandidates()
{
    
}

