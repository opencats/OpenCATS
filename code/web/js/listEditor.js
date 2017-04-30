/*
 * CATS
 * List Editor JavaScript Library
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
 * $Id: listEditor.js 2300 2007-04-04 22:48:37Z will $
 */

var listEditorList = Array();
var listEditorModifiedList = Array();
var listOutputElement;
var listSelectElement;
var _theTitle;
var modifyingIndex;
var _allowCountUpdate;
var _allowEditing;
var submitForm;
var allowDelete = false;
var _menuIndex = 2;

/* Final argument defaults to true */
function listEditor(theTitle, theListBoxID, theCSVID, allowCountUpdate, submitOnClose, menuIndex)
{
    _theTitle = theTitle;
    _allowCountUpdate = allowCountUpdate;

    getArrayFromCSVText(document.getElementById(theCSVID).value);

    listOutputElement = document.getElementById(theCSVID);
    listSelectElement = document.getElementById(theListBoxID);

    showPopWinHTML(drawListEditor(), 300, 280, null);

    //Fix for IE6 - this line can be ommitted in Mozilla
    document.getElementById('listEditorSelectDiv').innerHTML = '<select id="listEditorSelect" size="15" style="width: 165px;"></select>';

    submitForm = null;

    if (typeof(submitOnClose) != "undefined")
    {
        submitForm = submitOnClose;
    }

    _menuIndex = 2;
    if (typeof(menuIndex) != 'undefined')
    {
        _menuIndex = menuIndex;
    }

    listSelectElement.value = 'num';
    document.getElementById('popupTitle').innerHTML = theTitle;

    var objTargetElement = document.getElementById('listEditorSelect');
    objTargetElement.length = 0;

    for (var i = 0; i < listEditorList.length; i++)
    {
        var intTargetLen = objTargetElement.length++;

        objTargetElement.options[intTargetLen].text = listEditorList[i];
        objTargetElement.options[intTargetLen].value = intTargetLen;
    }

    document.getElementById('listEditorText').onkeydown = listEditorParseKeyDown;
}

function listEditorExtraFields(theTitle, theListBoxID, theCSVID, allowCountUpdate, submitOnClose, menuIndex)
{
    _theTitle = theTitle;
    _allowCountUpdate = allowCountUpdate;

    getArrayFromCSVText(document.getElementById(theCSVID).value);

    listOutputElement = document.getElementById(theCSVID);
    listSelectElement = document.getElementById(theListBoxID);

    showPopWinHTML(drawListEditorExtraFields(), 320, 300, null);

    //Fix for IE6 - this line can be ommitted in Mozilla
    document.getElementById('listEditorSelectDiv').innerHTML = '<select id="listEditorSelect" size="15" style="width: 165px;"></select>';

    submitForm = null;

    if (typeof(submitOnClose) != "undefined")
    {
        submitForm = submitOnClose;
    }

    _menuIndex = 2;
    if (typeof(menuIndex) != 'undefined')
    {
        _menuIndex = menuIndex;
    }

    listSelectElement.value = 'num';
    document.getElementById('popupTitle').innerHTML = theTitle;

    var objTargetElement = document.getElementById('listEditorSelect');
    objTargetElement.length = 0;

    for (var i = 0; i < listEditorList.length; i++)
    {
        var intTargetLen = objTargetElement.length++;

        objTargetElement.options[intTargetLen].text = listEditorList[i];
        objTargetElement.options[intTargetLen].value = intTargetLen;
    }

    document.getElementById('listEditorText').onkeydown = listEditorParseKeyDown;
}

function listEditorUpdateSelectFromCSV(theListBoxID, theCSVID, allowCountUpdate, allowEditing)
{
    _allowCountUpdate = allowCountUpdate;
    _allowEditing = allowEditing;

    getArrayFromCSVText(document.getElementById(theCSVID).value);

    listOutputElement = document.getElementById(theCSVID);
    listSelectElement = document.getElementById(theListBoxID);
    listSelectElement.value = 'num';

    listEditorDumpList();
}

function strReplace(needle, replaceWith, haystack)
{
    var pos;
    while (haystack.indexOf(needle) != -1)
    {
        pos = haystack.indexOf(needle);
        haystack = "" + (haystack.substring(0, pos) + replaceWith +
            haystack.substring((pos + needle.length), haystack.length));
    }
    return haystack;
}

function getArrayFromCSVText(theText)
{
    if (theText.indexOf('&DELETEALLOWED&') != -1)
    {
         theText = theText.substring(0,theText.indexOf('&DELETEALLOWED&'));
    }
    
    listEditorList = Array();
    listEditorModifiedList = Array();

    if (theText == '')
    {
        return Array();
    }

    theText = strReplace('""','!!DOUBLEQUOTE!!', theText);
    theText = strReplace('^','!!EXPONENT!!', theText);

    var pos;
    var pos2;

    while (theText.indexOf('"') != -1)
    {
        pos = theText.indexOf('"');
        theText = "" + (theText.substring(0, pos) + '^' +
            theText.substring((pos + 1), theText.length));

        pos2 = theText.indexOf('"');
        if (pos2 != -1)
        {
            theText = "" + (theText.substring(0, pos2) + '^' +
                theText.substring((pos2 + 1), theText.length));

            theTextSub = theText.substring(pos + 1, pos2);

            theTextSub = strReplace(',','!!COMMA!!', theTextSub);

            theText = "" + (theText.substring(0, pos) + '^' +
                theTextSub + '^' + theText.substring((pos2 + 1), theText.length));
        }
    }

    theText = strReplace('^','', theText);

    var tArray = theText.split(',');

    for (var i = 0; i < tArray.length; i++)
    {
        tArray[i] = strReplace('!!DOUBLEQUOTE!!', '"', tArray[i]);
        tArray[i] = strReplace('!!EXPONENT!!', '^', tArray[i]);
        tArray[i] = strReplace('!!COMMA!!', ',', tArray[i]);

        if (tArray[i].indexOf('!!EDIT!!') == 0)
        {
            listEditorModifiedList = listEditorModifiedList.concat(Array(Array(
                    tArray[i].substring(tArray[i].indexOf('!!EDIT!!')+8,tArray[i].indexOf('!!INTO!!')),
                    tArray[i].substring(tArray[i].indexOf('!!INTO!!')+8,tArray[i].length)
                )));
            tArray.splice(i,1);
            i--;
        }
    }

    listEditorList = tArray;
}

function listEditorBuildSelect()
{
    var objTargetElement = document.getElementById('listEditorSelect');

    objTargetElement.length = 0;

    for (var i = 0; i < listEditorList.length; i++)
    {
        var intTargetLen = objTargetElement.length++;

        objTargetElement.options[intTargetLen].text = listEditorList[i];
        objTargetElement.options[intTargetLen].value = intTargetLen;
    }
}

function listEditorDumpList()
{
    sOut = '';
    sOutModified = ''
    for (var i = 0; i < listEditorList.length; i++)
    {
        var s = listEditorList[i];
        s = strReplace('"', '!!DOUBLEQUOTE!!', s);
        s = strReplace('!!DOUBLEQUOTE!!', '""', s);
        s = '"'+s+'"';
        if (i != listEditorList.length - 1)
        {
            sOut += s + ',';
        }
        else
        {
            sOut += s;
        }
    }

    for (var i = 0; i < listEditorModifiedList.length; i++)
    {
        var s = listEditorModifiedList[i][0];
        var s2 = listEditorModifiedList[i][1]
        s = strReplace('"', '!!DOUBLEQUOTE!!', s);
        s = strReplace('!!DOUBLEQUOTE!!', '""', s);
        s2 = strReplace('"', '!!DOUBLEQUOTE!!', s2);
        s2 = strReplace('!!DOUBLEQUOTE!!', '""', s2);
        s = '"!!EDIT!!'+s+'!!INTO!!'+s2+'"';
        if (i != listEditorModifiedList.length - 1)
        {
            sOutModified += s + ',';
        }
        else
        {
            sOutModified += s;
        }
    }

    //Saftey:  Unless we have deleted something, don't allow deletes.
    if (allowDelete)
    {
        sOut = sOut + '&DELETEALLOWED&';
    }

    if (sOutModified != '')
    {
        sOut = sOut + ',' + sOutModified;
    }

    listOutputElement.value = sOut;

    if (_allowCountUpdate == false)
    {
        listSelectElement.length = 3;
        listSelectElement.options[_menuIndex].selected = true;
    }
    else if (_allowEditing == false)
    {
        listSelectElement.length = 1;
    }
    else
    {
        listSelectElement.options[1].text = '' + listEditorList.length + ' ' + _theTitle;
        listSelectElement.length = 3;
    }


    for (var i = 0; i < listEditorList.length; i++)
    {
        var intTargetLen = listSelectElement.length++;

        listSelectElement.options[intTargetLen].text = listEditorList[i];
        listSelectElement.options[intTargetLen].value = listEditorList[i]
    }
}

function listEditorParseKeyDown(evt)
{
    if (!evt)
    {
        evt = window.event;
    }

    if (typeof(evt.keyCode) == 'number')
    {
        /* Intercept keydown enter, and prevent form submission by returning
         * false.
         */
        if (evt.keyCode == 13)
        {
            if (document.getElementById('listEditorButtonAdd').style.display=='')
            {
                listEditorAddValue();
            }
            else
            {
                listEditorSaveValue();
            }

            return false;
        }
    }
}

function drawListEditor()
{
    var html =
        '<table>' +
            '<tr>' +
                '<td>' +
                    '<input type="text" id="listEditorText" style="width: 165px;">&nbsp;' +
                '</td>' +
                '<td>' +
                    '<input id="listEditorButtonAdd" type="button" value="Add" style="width: 80px;" class="button" onclick="listEditorAddValue();" />' +
                    '<input id="listEditorButtonSave" type="button" value="Save" style="width: 80px; display: none;" class="button" onclick="listEditorSaveValue();" />' +
                '</td>' +
            '</tr>' +
            '<tr>' +
                '<td>' +
                    '<div id="listEditorSelectDiv">' +
                        '<select id="listEditorSelect" size="15" style="width: 165px;"></select>' +
                    '</div>' +
                '</td>' +
                '<td>' +
                    '<input id="listEditorButtonDoModify" type="button" value="Modify" style="width: 80px;" class="button" onclick="listEditorDoModify();" />' +
                    '<input id="listEditorButtonDoAdd" type="button" value="Add" style="width: 80px; display: none;" class="button" onclick="listEditorDoAdd();" /><br /><br />' +
                    '<input id="listEditorButtonDelete" type="button" value="Delete" style="width: 80px;" class="button" onclick="listEditorDeleteValue();" /><br /><br />' +
                    '<input id="closeButton" type="button" value="Close" style="width: 80px;" class="button" onclick="closeWindow()" />' +
                '</td>' +
            '</tr>' +
        '</table>';

    return html;
}

function drawListEditorExtraFields()
{
    var html =
        '<table>' +
            '<tr>' +
                '<td>' +
                    '<input type="text" id="listEditorText" style="width: 165px;" />&nbsp;' +
                '</td>' +
                '<td>' +
                  '<span id="listEditorButtonAdd">'+
                    '<input id="listEditorButtonAdd" type="button" value="Add as Textbox" style="width: 110px;" class="button" onclick="listEditorAddValue();" /><br />' +
                    '<input id="listEditorButtonAdd" type="button" value="Add as Checkbox" style="width: 110px;" class="button" onclick="listEditorAddValueCB();" /></span>' +
                    '<input id="listEditorButtonSave" type="button" value="Save" style="width: 80px; display: none;" class="button" onclick="listEditorSaveValue();" />' +
                '</td>' +
            '</tr>' +
            '<tr>' +
                '<td>' +
                    '<div id="listEditorSelectDiv">' +
                        '<select id="listEditorSelect" size="15" style="width: 165px;"></select>' +
                    '</div>' +
                '</td>' +
                '<td>' +
                    '<input id="listEditorButtonDoModify" type="button" value="Modify" style="width: 80px;" class="button" onclick="listEditorDoModify();" />' +
                    '<input id="listEditorButtonDoAdd" type="button" value="Add" style="width: 80px; display: none;" class="button" onclick="listEditorDoAdd();" /><br /><br />' +
                    '<input id="listEditorButtonDelete" type="button" value="Delete" style="width: 80px;" class="button" onclick="listEditorDeleteValue();" /><br /><br />' +
                    '<input id="closeButton" type="button" value="Close" style="width: 80px;" class="button" onclick="closeWindow()" />' +
                '</td>' +
            '</tr>' +
        '</table>';

    return html;
}

function closeWindow()
{
    hidePopWin(false);
    if (submitForm != null)
    {
        document.getElementById(submitForm).submit();
    }
}

function listEditorAddValue()
{
    var theValue = document.getElementById('listEditorText').value;
    document.getElementById('listEditorText').value = '';

    if (theValue == '')
    {
        return;
    }

    for (var i = 0; i< listEditorList.length; i++)
    {
        if (listEditorList[i] == theValue)
        {
            return;
        }
    }

    listEditorList = listEditorList.concat(Array(theValue));

    listEditorList.sort();

    listEditorBuildSelect();
    listEditorDumpList();
}

function listEditorAddValueCB()
{
    var theValue = document.getElementById('listEditorText').value;
    theValue = '(CB) ' + theValue;

    document.getElementById('listEditorText').value = '';

    if (theValue == '')
    {
        return;
    }

    for (var i = 0; i< listEditorList.length; i++)
    {
        if (listEditorList[i] == theValue)
        {
            return;
        }
    }

    listEditorList = listEditorList.concat(Array(theValue));

    listEditorList.sort();

    listEditorBuildSelect();
    listEditorDumpList();
}

function listEditorSaveValue()
{
    var theValue = document.getElementById('listEditorText').value;

    listEditorModifiedList = listEditorModifiedList.concat(
        Array(Array(
            document.getElementById('listEditorSelect').options[modifyingIndex].text,
            theValue)));

    document.getElementById('listEditorText').value = '';

    listEditorList.splice(modifyingIndex,1);

    listEditorList = listEditorList.concat(Array(theValue));

    listEditorList.sort();

    listEditorBuildSelect();
    listEditorDumpList();

    listEditorDoAdd();
}


function listEditorDeleteValue()
{
    allowDelete = true;
    
    var listObject = document.getElementById('listEditorSelect');

    if (listObject.value == '')
    {
        return;
    }

    var valueNumber = listObject.value * 1;

    if (listEditorList.length == 1)
    {
        listEditorList = Array();
        listObject.length = 0;
    }
    else
    {
        listEditorList.splice(valueNumber,1);
    }
    listEditorBuildSelect();
    listEditorDumpList();
    
    return;
}

function listEditorDoModify()
{
    if (document.getElementById('listEditorSelect').value == '')
    {
        return;
    }

    var valueNumber = document.getElementById('listEditorSelect').value * 1;

    modifyingIndex = valueNumber;

    document.getElementById('listEditorText').value =
        document.getElementById('listEditorSelect').options[modifyingIndex].text;

    document.getElementById('listEditorText').focus();

    document.getElementById('listEditorButtonDoAdd').style.display='';
    document.getElementById('listEditorButtonDoModify').style.display='none';
    document.getElementById('listEditorButtonSave').style.display='';
    document.getElementById('listEditorButtonAdd').style.display='none';
}

function listEditorDoAdd()
{
    document.getElementById('listEditorText').value = '';

    document.getElementById('listEditorButtonDoAdd').style.display='none';
    document.getElementById('listEditorButtonDoModify').style.display='';
    document.getElementById('listEditorButtonSave').style.display='none';
    document.getElementById('listEditorButtonAdd').style.display='';
}
