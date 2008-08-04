/* CATS
 * JavaScript Library
 * Portions Copyright (C) 2006 - 2007 Cognizo Technologies Inc.
 *
 * The Contents of this file are subject to the CATS Public Licence
 * A copy of the licence can be found at www.catsone.com
 *
 * This file contains functions to highlight rows of a table and preserve the
 * orginal style of the table, includeing table cells which may be differnt
 * then the style of the rows in which they are contianed.
 */

var savedStates = new Array();
var savedStateCount = 0;
var highlightColor = '#ffffcc';

function deSelect()
{
    if (document.selection)
    {
        document.selection.empty();
    }
    else if (window.getSelection)
    {
        window.getSelection().removeAllRanges();
    }
}

function saveBackgroundStyle(currentElement)
{
    saved = new Object();

    saved.element         = currentElement;
    saved.className       = currentElement.className;
    saved.backgroundColor = currentElement.style['backgroundColor'];

    return saved;
}

function restoreBackgroundStyle(savedState)
{
    savedState.element.style['backgroundColor'] = savedState.backgroundColor;

    if (savedState.className)
    {
        savedState.element.className = savedState.className;
    }
}

function findNode(startingNode, tagName)
{
    currentElement = startingNode;

    var i = 0;
    while (currentElement && (!currentElement.tagName || (currentElement.tagName && currentElement.tagName != tagName)))
    {
        currentElement = startingNode.childNodes[i];
        i++;
    }

    if (currentElement && currentElement.tagName && currentElement.tagName == tagName)
    {
        return currentElement;
    }
    else if (startingNode.firstChild)
    {
        return findNode(startingNode.firstChild, tagName);
    }

    return 0;
}

function highlightTableRow(currentElement)
{
    for (var i = 0; i < savedStateCount; i++)
    {
        restoreBackgroundStyle(savedStates[i]);
    }

    savedStateCount = 0;

    while (currentElement && ((currentElement.tagName && currentElement.tagName != 'TR') || !currentElement.tagName))
    {
        currentElement = currentElement.parentNode;
    }

    if (!currentElement || (currentElement && currentElement.id && currentElement.id == 'header'))
    {
        return;
    }

    var tableRow = currentElement;

    if (tableRow == selectedTableRow && savedStateCountSelected > 0)
    {
        return;
    }

    if (tableRow)
    {
        savedStates[savedStateCount] = saveBackgroundStyle(tableRow);
        savedStateCount++;
    }

    var tableCell = findNode(currentElement, 'TD');

    while (tableCell)
    {
        if (tableCell.tagName == 'TD')
        {
            if (!tableCell.style)
            {
                tableCell.style = {};
            }
            else
            {
                savedStates[savedStateCount] = saveBackgroundStyle(tableCell);
                savedStateCount++;
            }

            tableCell.style['backgroundColor'] = highlightColor;
            tableCell.style.cursor = 'default';
        }

        tableCell = tableCell.nextSibling;
    }
}

/* Triggered on table mouseover. This can be anywhere in the table, not just
 * a row itself.
 */
function trackTableHighlight(mEvent)
{
    if (!mEvent)
    {
        mEvent = window.event;
    }

    if (mEvent.srcElement)
    {
        highlightTableRow(mEvent.srcElement);
    }
    else if (mEvent.target)
    {
        highlightTableRow(mEvent.target);
    }
}


/* The following is for selecting a row. */
var savedStatesSelected = new Array();
var savedStateCountSelected = 0;
var selectedColor = '#bde7ff';
var selectedColorChild = '#88d4ff';
var selectedTableRow = 0;
var selectedTableCell = 0;

function restoreBackgroundStyleSelected(savedState)
{
    savedState.element.style['backgroundColor'] = savedState.backgroundColor;

    if (savedState.className)
    {
        savedState.element.className = savedState.className;
    }
}

function findNode(startingNode, tagName)
{
    currentElement = startingNode;

    var i = 0;
    while (currentElement && (!currentElement.tagName || (currentElement.tagName && currentElement.tagName != tagName)))
    {
        currentElement = startingNode.childNodes[i];
        i++;
    }

    if (currentElement && currentElement.tagName && currentElement.tagName == tagName)
    {
        return currentElement;
    }
    else if (startingNode.firstChild)
    {
        return findNode(startingNode.firstChild, tagName);
    }

    return 0;
}

function highlightTableRowSelected(currentElement, customSelectedColor)
{
    deSelect();
    selectedTableCell = currentElement;

    for (var i = 0; i < savedStateCount; i++)
    {
        restoreBackgroundStyle(savedStates[i]);
    }

    savedStateCount = 0;

    for (var i = 0; i < savedStateCountSelected; i++)
    {
        restoreBackgroundStyleSelected(savedStatesSelected[i]);
    }

    savedStateCountSelected = 0;

    while (currentElement && ((currentElement.tagName && currentElement.tagName != 'TR') || !currentElement.tagName))
    {
        currentElement = currentElement.parentNode;
    }

    if (!currentElement || (currentElement && currentElement.id && currentElement.id == 'header'))
    {
        return;
    }

    var tableRow = currentElement;
    selectedTableRow = tableRow;

    if (tableRow)
    {
        savedStatesSelected[savedStateCount] = saveBackgroundStyle(tableRow);
        savedStateCountSelected++;
    }

    var tableCell = findNode(currentElement, 'TD');

    while (tableCell)
    {
        if (tableCell.tagName == 'TD')
        {
            if (!tableCell.style)
            {
                tableCell.style = {};
            }
            else
            {
                savedStatesSelected[savedStateCountSelected] = saveBackgroundStyle(tableCell);
                savedStateCountSelected++;
            }

            if (tableCell == selectedTableCell || tableCell == selectedTableCell.parentNode)
            {
                tableCell.style['backgroundColor'] = selectedColorChild;
            }
            else
            {
                if (customSelectedColor != null)
                {
                    tableCell.style['backgroundColor'] = customSelectedColor;
                }
                else
                {
                    tableCell.style['backgroundColor'] = selectedColor;
                }
            }
            tableCell.style.cursor = 'default';
        }

        tableCell = tableCell.nextSibling;
    }
}

/* Triggered on table mouseclick. This can be anywhere in the table, not just
 * a row itself.
 */
function trackTableSelect(mEvent, customSelectedColor)
{
    if (!mEvent)
    {
        mEvent = window.event;
    }

    if (mEvent.srcElement)
    {
        highlightTableRowSelected(mEvent.srcElement, customSelectedColor);
    }
    else if (mEvent.target)
    {
        highlightTableRowSelected(mEvent.target, customSelectedColor);
    }
}

