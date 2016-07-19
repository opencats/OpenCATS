/*
 * CATS
 * Column Resizing JavaScript Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
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
 *
 * $Id: dataGrid.js 3500 2007-11-08 18:22:40Z brian $
 */
 
/* FIXME:  Too many globals!  This code needs cleanup. */
/* Set to true when we are resizing a column. */
var _isResizing = false;
/* Set to true when moving a column. */
var _isMoving = false;
/* TH object we are resizing or moving. */
var _objectResizing = null;
/* DIV tag within the above TH object. */
var _objectResizingDiv = null;
/* Table object containing the column being resized. */
var _objectTableResizing = null;
/* The width of the column prior to being resized. */
var _startWidthResizing = null;
/* The width of the table prior to being resized. */
var _startTableWidthResizing = null;
/* The absolute size of the table - if the table is too small, the last column (overridecell) is enlarged
   to make the table at least this wide. */
var _minimumTableWidth = null;
/* TH object that gets larger if the table is too small. */
var _objectOverrideCell = null;
/* DIV tag within the above TH object. */
var _objectOverrideDiv = null;
/* instance name of the table (1 word, no spaces) used to save the table layout when it changes. */
var _instance = null;
/* The starting X position when either a move or resize is in progress. */
var _mouseStartPos = null;
/* The current mouse X position relative to arbritrary point (depends on browser). (updated in real time) */
var _mouse_x;
/* The current mouse Y position relative to arbritrary point  (depends on browser). (updated in real time) */
var _mouse_y;
/* If true, the mouse button is currently down. */
var _mouse_down;
/* The actuall width of the last cell as saved in the database prior to its transformation to make table minimum size. */
var _lastCellRealWidth = null;
/* String object with session cookie. */
var _sessionCookie = null;
/* The name of the current column being manipulated (header) */
var _columnName = null;
/* The InnerHTML contents of the cell being moved. */
var _origInnerHTML = null;
/* The DIV object of a cell that looks the same as the cell being moved. */
var _objectMovableCell = null;
/* The starting X position of the mouse on a move or resize. */
var _start_x = null;
/* String of cellid's for current cell we are resizing to resize the filters if they are visible. */
var _cellIDs = null;
/* Previous state of filter row visibility */
var _filterWasVisible = false;
/* Object of associated filter input box when resizing */
var _objectFilterInputID = null;
/* Size of the cell which is being dragged */
var _sizeOfResizingCell = null;
/* The element that allows scrolling left to right on an oversized table */
var _objectOverflow = null
/* The parameters in use for the move action */
var _baseParameters = null;
/* All the GET variables for the current page except for the pager parameters. */
var _unrelatedRequestString = null;

/* Called when a column starts moving; sets all the variables related to the
 * table and column we are resizing.
 */
function startResize(objectResizingID, objectTableResizingID,
    objectOverrideCellID, minimumTableWidth, instance, sessionCookie,
    columnName, cellIDs, sizeOfResizingCell)
{
     _objectResizing = document.getElementById(objectResizingID);
     _objectResizingDiv = document.getElementById(objectResizingID + 'div');
     _objectTableResizing = document.getElementById(objectTableResizingID);
     _objectOverrideCell = document.getElementById(objectOverrideCellID);
     _objectOverrideDiv = document.getElementById(objectOverrideCellID + 'div');
     
     _instance = instance;
     _sessionCookie = sessionCookie;
     _columnName = columnName;
     _minimumTableWidth = minimumTableWidth;
     _sizeOfResizingCell = sizeOfResizingCell;
     
     _startWidthResizing = _objectResizing.offsetWidth;
     _startTableWidthResizing = _objectTableResizing.offsetWidth;
     _mouseStartPos = _mouse_x;
     _isResizing = true;
     _isMoving = false;
     
     var filterRow = document.getElementById('filterRow' + instance);
}

function startMove(objectResizingID, objectTableResizingID,
    objectOverrideCellID, instance, sessionCookie, columnName,
    objectMovableCellID, objectOverFlowDivID, baseParameters, unrelatedRequestString)
{
    _objectResizing = document.getElementById(objectResizingID);
    _objectResizingDiv = document.getElementById(objectResizingID + 'div');
    _objectTableResizing = document.getElementById(objectTableResizingID);
    _objectOverflow = document.getElementById(objectOverFlowDivID);
    _objectOverrideCell = document.getElementById(objectOverrideCellID);
    _objectOverrideDiv = document.getElementById(objectOverrideCellID + 'div');
    _objectMovableCell = document.getElementById(objectMovableCellID);
    
    _instance = instance;
    _sessionCookie = sessionCookie;
    _columnName = columnName;
    _baseParameters = baseParameters;
    _unrelatedRequestString = unrelatedRequestString;
    
    _mouseStartPos = _mouse_x;
    _isResizing = false;
 
    window.setTimeout('startMoveAfterDelay();', 450)
}

/* If the mouse is still clicked after .45 seconds, execute the move script. */
function startMoveAfterDelay()
{
    if (!_mouse_down)
    {
        return;
    }

    _isMoving = true;
    /* Remove label on item, create duplicate column floating above everything */
    _origInnerHTML = _objectResizingDiv.innerHTML;
    _objectResizingDiv.innerHTML = '';
    _objectMovableCell.style.width = _objectResizing.offsetWidth + 'px';
    _objectMovableCell.innerHTML = _origInnerHTML;
    _objectMovableCell.style.display = '';
    _objectMovableCell.style.left = docjslib_getRealLeft(_objectResizing) + 'px';
    _objectMovableCell.style.top = docjslib_getRealTop(_objectResizing) + 'px';
    _start_x = docjslib_getRealLeft(_objectResizing);
}


/* Called whenever a mousedown event happens. */
function handleMouseDown()
{
    _mouse_down = true;
}

/* Called whenever a mouseup event happens, decides if it needs to finalize a move or resize. */
function handleMouseUp()
{
    _mouse_down = false;
    
    if (_isResizing)
    {
        if (_objectOverrideCell == _objectResizing)
        {
            _lastCellRealWidth = _objectOverrideCell.offsetWidth;
        }
        _isResizing = false;
        enforceMinimumTableWidth();
        saveColumnSize();
    }
    
    if (_isMoving)
    {
        finishMoving();
    }
}

/* Determines where the user is intending to move the column to, then submits to the server the move through GET. */
function finishMoving()
{
    _isMoving = false;

    var middleOfCell = docjslib_getRealLeft(_objectMovableCell) + (_objectMovableCell.offsetWidth / 2) + _objectOverflow.scrollLeft;
    
    var tblHeadObj = _objectTableResizing.tHead;
    var tblHeadRowObj = tblHeadObj.rows[0];
    var cellPositions = Array();
    var cellActions = Array();
    var positionArrayCounter = 0;
    
    /* Create 2 arrays containing cell positions and actions for moving to that position */
    for (var i=0; i < tblHeadRowObj.cells.length; i++) 
    {
        if ((' ' + tblHeadRowObj.cells[i].className + ' ').indexOf('resizeableCell') == -1 && 
            docjslib_getRealLeft(tblHeadRowObj.cells[i]) != 0 && 
            tblHeadRowObj.cells[i].id != '')
        {
            cellPositions[positionArrayCounter] = docjslib_getRealLeft(tblHeadRowObj.cells[i]);
            
            if (tblHeadRowObj.cells[i].id == _objectResizing.id)
            {
                cellActions[positionArrayCounter] = 'nothing - left side';
            } 
            else if (positionArrayCounter != 0 && cellActions[positionArrayCounter - 1] == 'nothing - left side')
            {
                cellActions[positionArrayCounter] = 'nothing - right side';
            }
            else
            {
                cellActions[positionArrayCounter] = tblHeadRowObj.cells[i].id;
            }
            positionArrayCounter++;
        }
    }
    
    /* Create an action for the very end of the grid (move to last slot) */
    if (positionArrayCounter != 0)
    {
        cellPositions[positionArrayCounter] = docjslib_getRealLeft(_objectOverrideCell) + _objectOverrideCell.offsetWidth;
        cellActions[positionArrayCounter] = 'moveToEnd';
    }
    
    /* Determine smallest distance away from middle of cell. */
    var smallestIndex = 0;
    var smallestValue = 100000;
    
    for (var i = 0; i < cellPositions.length; i++)
    {
        if (Math.abs(cellPositions[i] - middleOfCell) < smallestValue)
        {
            smallestValue = Math.abs(cellPositions[i] - middleOfCell);
            smallestIndex = i;
        }
    }

    /* Submit the move to the server if there is any action to be done. */
    if ((' ' + cellActions[smallestIndex] + ' ').indexOf('nothing') == -1)
    {
        _objectResizingDiv.innerHTML = '';
        populateAjaxPager(_instance, _baseParameters, _sessionCookie, urlEncode(_objectResizing.id) + ',' + urlEncode(cellActions[smallestIndex]), _unrelatedRequestString)
    }
    else
    {
        _objectMovableCell.style.display = 'none';
        _objectResizingDiv.innerHTML = _origInnerHTML;
    }
}

/* Called by stopResize to store in the database the new column widths. */
function saveColumnSize()
{
    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '';
    POSTData += '&columnName=' + urlEncode(_columnName);
    POSTData += '&columnWidth=' + _objectResizing.style.width.substring(0, _objectResizing.style.width.length-2);
    POSTData += '&instance=' + _instance;

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        //alert(http.responseText);

        if (!http.responseXML)
        {
            var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                             + http.responseText;
            //alert(errorMessage);
            return;
        }

        /* Return if we have any errors. */
        var errorCodeNode    = http.responseXML.getElementsByTagName('errorcode').item(0);
        var errorMessageNode = http.responseXML.getElementsByTagName('errormessage').item(0);
        if (!errorCodeNode.firstChild || errorCodeNode.firstChild.nodeValue != '0')
        {
            var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                             + errorMessageNode.firstChild.nodeValue;
            //alert(errorMessage);
            return;
        }
    }

    AJAX_callCATSFunction(
        http,
        'setColumnWidth',
        POSTData,
        callBack,
        0,
        _sessionCookie,
        false
    );
}

/* Called whenever the mouse moves, tracks mouse position. */
function handleMouseMove(e)
{
    if (!e)
    {
        var e = window.event;
    }
    
    if (e.pageX || e.pageY)
    {
        _mouse_x = e.pageX;
        _mouse_y = e.pageY;
    }
    else if (e.clientX || e.clientY)
    {
      /*  _mouse_x = e.clientX + document.body.scrollLeft
            + document.documentElement.scrollLeft;
        _mouse_y = e.clientY + document.body.scrollTop
            + document.documentElement.scrollTop;*/
    }

    if (_isResizing)
    {
        updateResize();
    }
    
    if (_isMoving)
    {
        updateMove();
    }
}

/* Called whenever the mouse moves and the column is resizing, changes column width. */
function updateResize()
{
    if (_startWidthResizing - (_mouseStartPos - _mouse_x) > 10)
    {
        newWidth = _startWidthResizing - (_mouseStartPos - _mouse_x);
        newTableWidth = _startTableWidthResizing - (_mouseStartPos - _mouse_x);
    }
    _objectResizing.style.width = newWidth + 'px';
    _objectResizingDiv.style.width = newWidth + 'px';
    _objectTableResizing.style.width = newTableWidth + 'px';
}

function updateMove()
{
    newXPos = _start_x - (_mouseStartPos - _mouse_x) - _objectOverflow.scrollLeft ;
    _objectMovableCell.style.left = (newXPos) + 'px';
}
 

/* Called when a table is done being output by DynamicPager, forces the table
 * to be the correct calculated size.
 */
function setTableWidth(tableID, tableWidth, objectOverrideCell,
    objectOverrideDiv, minimumTableWidth, objectTableResizing)
{
    document.getElementById(tableID).style.width = tableWidth;
    
    _objectTableResizing = document.getElementById(tableID);
    _minimumTableWidth = minimumTableWidth;
    _objectOverrideCell = objectOverrideCell;
    _objectOverrideDiv = objectOverrideDiv;
    enforceMinimumTableWidth();
}

/* Checks to make sure the table as at least large enough to cover the entire visible area, if it is not
   function resizes the last column to cover the visible area.
 */
function enforceMinimumTableWidth()
{
    var tableWidth = _objectTableResizing.offsetWidth;
    if (tableWidth == 0)
    {
        tableWidth = _objectTableResizing.width;
    }
    
    var cellCurrentWidth = _objectOverrideCell.offsetWidth;
    if (cellCurrentWidth == 0)
    {
        cellCurrentWidth = _objectOverrideCell.width;
    }

    var overflow = true;
    
    if (_lastCellRealWidth != null && tableWidth > _minimumTableWidth &&
        cellCurrentWidth - (tableWidth - _minimumTableWidth) > _lastCellRealWidth) 
    {
        var sizeMod = _minimumTableWidth - tableWidth;
        var newCellWidth = cellCurrentWidth + sizeMod;
        _objectOverrideCell.style.width = newCellWidth + 'px'; 
        _objectOverrideDiv.style.width = newCellWidth + 'px';
        _objectTableResizing.style.width = (tableWidth + sizeMod) + 'px';
        overflow = false;
    }
    
    if (_lastCellRealWidth != null && tableWidth > _minimumTableWidth &&
        cellCurrentWidth - (tableWidth - _minimumTableWidth) < _lastCellRealWidth) 
    {
        var sizeMod = _lastCellRealWidth - cellCurrentWidth;
        if (_lastCellRealWidth-4 > 10)
        {
            _objectOverrideCell.style.width = (_lastCellRealWidth-4) + 'px'; 
            _objectOverrideDiv.style.width = (_lastCellRealWidth-4) + 'px';  
            _objectTableResizing.style.width = (tableWidth + sizeMod) + 'px';   
        }          
    }
    
    if (_lastCellRealWidth == null)
    {

        _lastCellRealWidth = cellCurrentWidth;
    }

    if (tableWidth < _minimumTableWidth)
    {
        _objectOverrideCell.style.width = (cellCurrentWidth + (_minimumTableWidth - tableWidth)) + 'px';
        _objectOverrideDiv.style.width = (cellCurrentWidth + (_minimumTableWidth - tableWidth)) + 'px';
        overflow = false;
    }
    
    if (overflow)
    {
        /* TODO:  DETECT BROWSER, IF IE BROWSER ADD EXTRA SPACE TO PREVENT VERT SCROLLBAR. */
    }
}

/* Helper for add/remove column window. */
function toggleHideShowControls(md5InstanceName) 
{
    var cellHideShow = document.getElementById('cellHideShow' + md5InstanceName);
    var ColumnBox = document.getElementById('ColumnBox' + md5InstanceName);
    var helpShim = document.getElementById('helpShim' + md5InstanceName);
    
    if (ColumnBox.style.display=='block') 
    {
        ColumnBox.style.display = 'none';
        helpShim.style.display = 'none';
    } 
    else 
    {
        ColumnBox.style.display = 'block';
        ColumnBox.style.left = docjslib_getRealLeft(cellHideShow) + 'px';
        helpShim.style.display = 'block';
        helpShim.style.zIndex = 1;
        helpShim.style.left = docjslib_getRealLeft(ColumnBox) + 'px';
        helpShim.style.top = docjslib_getRealTop(ColumnBox) + 'px';
        helpShim.style.width = ColumnBox.offsetWidth + 'px';
        helpShim.style.height = ColumnBox.offsetHeight + 'px';
    }
}

/* Helper for add/remove column window. */
function toggleHideShowAction(md5InstanceName) 
{
    var cellHideShow = document.getElementById('cellHideShow' + md5InstanceName);
    var ActionArea = document.getElementById('ActionArea' + md5InstanceName);
    var helpShim = document.getElementById('helpShim' + md5InstanceName);
    
    if (ActionArea.style.display=='block') 
    {
        ActionArea.style.display = 'none';
        helpShim.style.display = 'none';
    } 
    else 
    {
        ActionArea.style.display = 'block';
        ActionArea.style.left = docjslib_getRealLeft(cellHideShow) + 'px';
        helpShim.style.display = 'block';
        helpShim.style.zIndex = 1;
        helpShim.style.left = docjslib_getRealLeft(ActionArea) + 'px';
        helpShim.style.top = docjslib_getRealTop(ActionArea) + 'px';
        helpShim.style.width = ActionArea.offsetWidth + 'px';
        helpShim.style.height = ActionArea.offsetHeight + 'px';
    }
}

/* Helpers for the show/hide filter control. */
function showFilterSet(cellIndexes, instanceName)
{
    var filterRow = document.getElementById('filterRow' + instanceName);
    var widthEval = 0;
    filterRow.style.display = '';
    filterRow.style.height = '20px';
    
    var cellIndexArray = cellIndexes.split(',');
    for (var i = 0; i < cellIndexArray.length; i++)
    {        
        if (i < cellIndexArray.length - 1)
        {
            //FIXME: Clean up.
            widthEval = (docjslib_getRealLeft(document.getElementById('cell' + instanceName + cellIndexArray[i+1])) - docjslib_getRealLeft(document.getElementById('cell' + instanceName + cellIndexArray[i])) - 2);
            if (widthEval < 0)
            {
                widthEval = 0;
            }
            document.getElementById('filter_cell' + i + instanceName).style.width = widthEval + 'px';
        }
        else
        {
            widthEval = ((docjslib_getRealLeft(document.getElementById('table' + instanceName)) + document.getElementById('table' + instanceName).offsetWidth) - docjslib_getRealLeft(document.getElementById('cell' + instanceName + cellIndexArray[i])) - 2)
            if (widthEval < 0)
            {
                widthEval = 0;
            }
            document.getElementById('filter_cell' + i + instanceName).style.width = widthEval + 'px';
        }
        document.getElementById('filter_cell' + i + instanceName).style.top = '-3px';
    }
    filterRow.style.height = document.getElementById('filter_cell' + cellIndexArray[0] + instanceName).offsetHeight + 'px';
}

function hideFilterSet(instanceName)
{
    var filterRow = document.getElementById('filterRow' + instanceName);
    filterRow.style.display = 'none';      
}

/* Called by stopResize to store in the database the new column widths. */
/* If dynamicArgument is set, then any value DYNAMICARGUMENT in the serialized paramater array gets set to the value. */
function populateAjaxPager(instance, parameters, sessionCookie, dynamicArgument, unrelatedRequestString)
{
    md5instance = md5(urlDecode(instance));
    
    if (document.getElementById('exportBoxLink' + md5instance) != null)
    {
        document.getElementById('exportBoxLink' + md5instance).style.display = 'none';
    }
    
    document.getElementById('ajaxTableIndicator' + md5instance).style.display = '';
    
    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '&i=' + instance + '&p=' + parameters;
    
    if (typeof(dynamicArgument) != 'undefined')
    {
        POSTData += '&dynamicArgument=' + urlEncode(dynamicArgument);
    }
    
    if (typeof(unrelatedRequestString) != 'undefined')
    {
        POSTData += '&unrelatedRequestString=' + urlEncode(unrelatedRequestString);
    }

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        document.getElementById('OverflowDiv' + md5instance).innerHTML = http.responseText;
        
        execJS(http.responseText);
    }

    AJAX_callCATSFunction(
        http,
        'getDataGridPager',
        POSTData,
        callBack,
        0,
        sessionCookie,
        false
    );
}

/* Adds or removes a value from an array depending on if the value is already in the array. */
function addRemoveFromExportArray(arrayObject, theValue)
{
    var inArray = false;
    for (var i = 0; i < arrayObject.length; i++)
    {
        if (arrayObject[i] == theValue && !inArray)
        {
            arrayObject.splice(i, 1);
            inArray = true;
        }
    }
    
    if (!inArray)
    {
        arrayObject.push(theValue);
    }
}

/* Removes a column from a filter. */
function removeColumnFromFilter(filterElementID, columnName)
{
    var arguments = document.getElementById(filterElementID).value.split(',');
    var newArguments = '';
    
    for (var i = 0; i < arguments.length; i++)
    {   
        if ((arguments[i].indexOf('=') == -1 || urlDecode(arguments[i].substr(0, arguments[i].indexOf('='))) != columnName) && arguments[i] != '')
        {
            newArguments += arguments[i] + ',';
        }
    }
    
    document.getElementById(filterElementID).value = newArguments;
}

/* Adds a column to a filter. */
function addColumnToFilter(filterElementID, columnName, operator, value)
{
    removeColumnFromFilter(filterElementID, columnName);
    
    document.getElementById(filterElementID).value += urlEncode(columnName) + operator + urlEncode(value) + ',';
}

/* Clears the filter. */
function clearFilter(filterElementID)
{
    document.getElementById(filterElementID).value = '';
}

/* DHTML filter options are encoded into column name!@!filter type pairs.  Ex:  First Name!@!===~ returns First Name. */
function getFilterColumnNameFromOptionValue(theValue)
{
    return theValue.substr(0, theValue.indexOf('!@!'));
}

/* DHTML filter options are encoded into column name!@!filter type pairs.  Ex:  First Name!@!===~ returns '===~'; */
function getFilterColumnTypesFromOptionValue(theValue)
{
    return theValue.substr(theValue.indexOf('!@!') + 3);
}



/* Shows a new DHTML filter for the user to add a filter to. */
function showNewFilter(
    filterCounter,
    filterAreaID,
    selectableColumns,
    instanceName
) {
    var filterArea = document.getElementById(filterAreaID);
    filter.makePreviousSelectionBoxesUnselectable(
        filterCounter,
        filterAreaID,
        selectableColumns
    );
    var currentFilter = filter.FilterFactory.createFromPossibleOperatorType(
        selectableColumns[0],
        filterCounter,
        filterAreaID,
        selectableColumns,
        instanceName
    );
    filterArea.appendChild(currentFilter.render());
    var disableAddFilterButton = selectableColumns.length > 1 ? false : true;
    document.getElementsByName('addFilterButton' + instanceName)[0].disabled = disableAddFilterButton;
}

/* Generic message to display when a user tries to export selected, but nothing is selected. */
function dataGridNoSelected()
{
    alert ('You have not selected any items!');
}

/* Mouse handler hooks. */
document.onmouseup = handleMouseUp;
document.onmousedown = handleMouseDown;
document.onmousemove = handleMouseMove;

