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
 * $Id: doubleListEditor.js 3676 2007-11-21 21:02:15Z brian $
 */


/* FIXME This Code can be optimized */
var doubleListEditorListRight = Array();
var doubleListEditorListLeft = Array();
var doubleListEditorModifiedListRight = Array();
var doubleListEditorModifiedListLeft = Array();
var doubleListOutputElementRight;
var doubleListOutputElementLeft;
var doubleListSelectElementRight;
var doubleListSelectElementLeft;
var _doubleListTitle;
var modifyingIndex;
var oldValue;
var doubleAllowDeleteLeft = false;
var doubleAllowDeleteRight = false;

function doubleListEditor(theTitle, rightListCSVID, leftListCSVID,  allowCountUpdate)
{
    _doubleListTitle = theTitle;
    doubleListEditorListRight = doubleListGetArrayFromCSVText(document.getElementById(rightListCSVID).value);
    doubleListEditorListLeft = doubleListGetArrayFromCSVText(document.getElementById(leftListCSVID).value);
    doubleListOutputElementRight = document.getElementById(rightListCSVID);
    doubleListOutputElementLeft = document.getElementById(leftListCSVID);

    showPopWinHTML(drawDoubleListEditor(), 620, 315, null);

    /* Fix for IE6. */
    document.getElementById('doubleListEditorSelectDivRight').innerHTML = '<select id="doubleListEditorSelectRight" size="14" style="width: 165px;" class="selectBox"></select>';
    document.getElementById('doubleListEditorSelectDivLeft').innerHTML = '<select id="doubleListEditorSelectLeft" size="16" style="width: 165px;" class="selectBox"></select>';

    document.getElementById('popupTitle').innerHTML = theTitle;

    var objTargetElement = document.getElementById('doubleListEditorSelectRight');
    for (var i = 0; i < doubleListEditorListRight.length; i++)
    {
        var intTargetLen = objTargetElement.length++;
        objTargetElement.options[intTargetLen].text = doubleListEditorListRight[i];
        objTargetElement.options[intTargetLen].value = intTargetLen;
    }

    objTargetElement = document.getElementById('doubleListEditorSelectLeft');
    for ( i = 0; i < doubleListEditorListLeft.length; i++)
    {
        intTargetLen = objTargetElement.length++;
        objTargetElement.options[intTargetLen].text = doubleListEditorListLeft[i];
        objTargetElement.options[intTargetLen].value = intTargetLen;
    }
}

function drawDoubleListEditor()
{
    var html =
        '<table>' +
            '<tr>' +
                '<th style="padding-left: 10px; padding-top: 5px;">' +
                    'Candidate\'s Saved Lists' +
                '</th>' +
                '<th>&nbsp;</th>' +
                '<th style="padding-left: 10px; padding-top: 5px;">' +
                    'Available Saved Lists' +
                '</th>' +
            '</tr>' +
            '<tr>' +
                '<td valign="top">' +
                    '<table>' +
                        '<tr>' +
                            '<td>' +
                                '<div id="doubleListEditorSelectDivLeft">' +
                                    '<select id="doubleListEditorSelectLeft" size="16" style="width: 165px;" class="selectBox">' +
                                    '</select>' +
                                '</div>' +
                            '</td>' +
                        '</tr>' +
                    '</table>' +
                '</td>' +
                '<td valign="top">' +
                    '<table>' +
                        '<tr>' +
                            '<td>&nbsp;</td>' +
                        '</tr>' +
                        '<tr>' +
                            '<td>&nbsp;</td>' +
                        '</tr>' +
                        '<tr>' +
                            '<td valign="top" style="margin-top: 5px;">' +
                                '<input id="doubleListEditorButtonAdd" type="button" value="< -- Add" style="width: 90px;" class="button" onclick="doubleListEditorAddValue()" />' +
                            '</td>' +
                        '</tr>' +
                        '<tr>' +
                            '<td valign="top">' +
                                '<input id="doubleListEditorButtonRemove" type="button" value="Remove" style="width: 90px;" class="button" onclick="doubleListEditorRemoveValue()" />' +
                            '</td>' +
                        '</tr>' +
                    '</table>' +
                '</td>' +
                '<td  valign="top">' +
                    '<table>' +
                        '<tr>' +
                            '<td>' +
                                '<input type="text" id="doubleListEditorNewText" style="width: 163px;" />' +
                            '</td>' +
                        '</tr>' +
                        '<tr>' +
                            '<td>' +
                                '<div id="doubleListEditorSelectDivRight">' +
                                    '<select id="doubleListEditorSelectRight" size="14" style="width: 165px;" class="selectBox"></select>' +
                                '</div>' +
                            '</td>' +
                         '</tr>' +
                    '</table>' +
                '</td>' +
                '<td valign="top">' +
                    '<table>' +
                        '<tr>' +
                            '<td valign="top">' +
                                '<input id="doubleListEditorNew" type="button" value="Add Saved List" style="width: 110px;" class="button" onclick="doubleListEditorNewValue();" />' +
                                '<input id="doubleListEditorSave" type="button" value="Save" style="width: 110px; display: none;" class="button" onclick="doubleListEditorSaveValue()" />' +
                            '</td>' +
                        '</tr>' +
                        '<tr>' +
                            '<td>&nbsp;</td>' +
                        '</tr>' +
                        '<tr>' +
                            '<td valign="top">' +
                                '<input id="doubleListEditorRename" type="button" value="Rename Saved List" style="width: 110px;" class="button" onclick="doubleListEditorDoModify()" />' +
                                '<input id="doubleListEditorNewList" type="button" value="Add Saved List" style="width: 110px; display: none;" class="button" onclick="doubleListEditorDoAdd();" />' +
                            '</td>' +
                        '</tr>' +
                        '<tr>' +
                            '<td valign="top">' +
                                '<input id="doubleListEditorButtonDelete" type="button" value="Delete Saved List" style="width: 110px;" class="button" onclick="doubleListEditorDeleteValue()" />' +
                            '</td>' +
                        '</tr>' +
                        '<tr>' +
                            '<td valign="top">&nbsp;</td>' +
                        '</tr>' +
                        '<tr>' +
                            '<td valign="top">' +
                                '<input id="doubleListEditorButtonClose" type="button" value="Close" style="width: 110px;" class="button" onclick="hidePopWin(false);" />' +
                            '</td>' +
                        '</tr>' +
                    '</table>' +
                '</td>' +
            '</tr>' +
        '</table>';

    return html;
}

function doubleListEditorBuildSelectRight()
{
    var objTargetElement = document.getElementById('doubleListEditorSelectRight');
    objTargetElement.length = 0;

    for (var i = 0; i < doubleListEditorListRight.length; i++)
    {
        var intTargetLen = objTargetElement.length++;

        objTargetElement.options[intTargetLen].text = doubleListEditorListRight[i];
        objTargetElement.options[intTargetLen].value = intTargetLen;
    }
}

function doubleListEditorDumpListRight()
{
    sOut = '';
    sOutModified = ''

    for (var i = 0; i < doubleListEditorListRight.length; i++)
    {
        var s = doubleListEditorListRight[i];
        s = strReplace('"', '!!DOUBLEQUOTE!!', s);
        s = strReplace('!!DOUBLEQUOTE!!', '""', s);
        s = '"'+s+'"';
        if (i != doubleListEditorListRight.length - 1)
        {
            sOut += s + ',';
        }
        else
        {
            sOut += s;
        }
    }

    for (var i = 0; i < doubleListEditorModifiedListRight.length; i++)
    {
        var s = doubleListEditorModifiedListRight[i][0];
        var s2 = doubleListEditorModifiedListRight[i][1]
        s = strReplace('"', '!!DOUBLEQUOTE!!', s);
        s = strReplace('!!DOUBLEQUOTE!!', '""', s);
        s2 = strReplace('"', '!!DOUBLEQUOTE!!', s2);
        s2 = strReplace('!!DOUBLEQUOTE!!', '""', s2);
        s = '"!!EDIT!!'+s+'!!INTO!!'+s2+'"';
        if (i != doubleListEditorModifiedListRight.length - 1)
        {
            sOutModified += s + ',';
        }
        else
        {
            sOutModified += s;
        }
    }

    //Saftey:  Unless we have deleted something, don't allow deletes.
    if (doubleAllowDeleteRight)
    {
        sOut = sOut + '&DELETEALLOWED&';
    }

    if (sOutModified != '')
    {
        sOut = sOut + ',' + sOutModified;
    }
    doubleListOutputElementRight.value = sOut;
}

function doubleListEditorBuildSelectLeft()
{
    var objTargetElement = document.getElementById('doubleListEditorSelectLeft');

    objTargetElement.length = 0;

    for (var i = 0; i < doubleListEditorListLeft.length; i++)
    {
        var intTargetLen = objTargetElement.length++;

        objTargetElement.options[intTargetLen].text = doubleListEditorListLeft[i];
        objTargetElement.options[intTargetLen].value = intTargetLen;
    }
}

function doubleListEditorDumpListLeft()
{
    sOut = '';
    sOutModified = ''

    for (var i = 0; i < doubleListEditorListLeft.length; i++)
    {
        var s = doubleListEditorListLeft[i];
        s = strReplace('"', '!!DOUBLEQUOTE!!', s);
        s = strReplace('!!DOUBLEQUOTE!!', '""', s);
        s = '"'+s+'"';
        if (i != doubleListEditorListLeft.length - 1)
        {
            sOut += s + ',';
        }
        else
        {
            sOut += s;
        }
    }

    for (var i = 0; i < doubleListEditorModifiedListLeft.length; i++)
    {
        var s = doubleListEditorModifiedListLeft[i][0];
        var s2 = doubleListEditorModifiedListLeft[i][1]
        s = strReplace('"', '!!DOUBLEQUOTE!!', s);
        s = strReplace('!!DOUBLEQUOTE!!', '""', s);
        s2 = strReplace('"', '!!DOUBLEQUOTE!!', s2);
        s2 = strReplace('!!DOUBLEQUOTE!!', '""', s2);
        s = '"!!EDIT!!'+s+'!!INTO!!'+s2+'"';
        if (i != doubleListEditorModifiedListLeft.length - 1)
        {
            sOutModified += s + ',';
        }
        else
        {
            sOutModified += s;
        }
    }

    if (sOutModified != '')
    {
        sOut = sOut + ',' + sOutModified;
    }
    
    //Saftey:  Unless we have deleted something, don't allow deletes.
    if (doubleAllowDeleteLeft)
    {
        sOut = sOut + '&DELETEALLOWED&';
    }

    doubleListOutputElementLeft.value = sOut;
}


function doubleListGetArrayFromCSVText(theText)
{
    if (theText.indexOf('&DELETEALLOWED&') != -1)
    {
         theText = theText.substring(0,theText.indexOf('&DELETEALLOWED&'));
    }    
    
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

    listEditorModifiedList = Array();

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
    return tArray;
}


function doubleListEditorNewValue()
{
    var theValue = document.getElementById('doubleListEditorNewText').value;
    document.getElementById('doubleListEditorNewText').value = '';

    if (theValue == '')
    {
        return;
    }
    for (var i = 0; i< doubleListEditorListRight.length; i++)
    {
        if (doubleListEditorListRight[i] == theValue)
        {
            return;
        }
    }

    doubleListEditorListRight = doubleListEditorListRight.concat(Array(theValue));
    doubleListEditorListRight.sort();

    doubleListEditorBuildSelectRight();
    doubleListEditorDumpListRight();
}

function doubleListEditorDeleteValue()
{
    doubleAllowDeleteRight = true;    
    
    var listObject = document.getElementById('doubleListEditorSelectRight');

    if (listObject.value == '')
    {
        return;
    }

    var valueNumber = listObject.value * 1;
    var delValue = document.getElementById('doubleListEditorSelectRight').options[valueNumber].text;

    if (doubleListEditorListRight.length == 1)
    {
        doubleListEditorListRight = Array();
        listObject.length = 0;
    }
    else
    {
        doubleListEditorListRight.splice(valueNumber,1);
    }
    doubleListEditorBuildSelectRight();
    doubleListEditorDumpListRight();

    for (var i = 0; i < doubleListEditorListLeft.length; i++)
    {
        if (doubleListEditorListLeft[i] == delValue)
        {
            doubleListEditorListLeft.splice(i, 1);
            doubleListEditorBuildSelectLeft();
            doubleListEditorDumpListLeft();
        }
    }

    return;
}

function doubleListEditorRemoveValue()
{
    doubleAllowDeleteLeft = true; 
    
    var listObject = document.getElementById('doubleListEditorSelectLeft');

    if (listObject.value == '')
    {
        return;
    }

    var valueNumber = listObject.value * 1;

    if (doubleListEditorListLeft.length == 1)
    {
        doubleListEditorListLeft = Array();
        listObject.length = 0;
    }
    else
    {
        doubleListEditorListLeft.splice(valueNumber,1);
    }
    doubleListEditorBuildSelectLeft();
    doubleListEditorDumpListLeft();

    return;
}

function doubleListEditorAddValue()
{
    var listObject = document.getElementById('doubleListEditorSelectRight');
    var theValue = listObject.options[listObject.selectedIndex].text;

    if (theValue == '')
    {
        return;
    }

    for (var i = 0; i< doubleListEditorListLeft.length; i++)
    {
        if (doubleListEditorListLeft[i] == theValue)
        {
            return;
        }
    }

    doubleListEditorListLeft = doubleListEditorListLeft.concat(Array(theValue));
    doubleListEditorListLeft.sort();

    doubleListEditorBuildSelectLeft();
    doubleListEditorDumpListLeft();
}

function doubleListEditorDoModify()
{
    if (document.getElementById('doubleListEditorSelectRight').value == '')
    {
        return;
    }

    var valueNumber = document.getElementById('doubleListEditorSelectRight').value * 1;

    modifyingIndex = valueNumber;

    oldValue = document.getElementById('doubleListEditorSelectRight').options[modifyingIndex].text;

    document.getElementById('doubleListEditorNewText').value =
    document.getElementById('doubleListEditorSelectRight').options[modifyingIndex].text;

    document.getElementById('doubleListEditorNewText').focus();

    document.getElementById('doubleListEditorSave').style.display='';
    document.getElementById('doubleListEditorNew').style.display='none';
    document.getElementById('doubleListEditorNewList').style.display='';
    document.getElementById('doubleListEditorRename').style.display='none';
}

function doubleListEditorDoAdd()
{
    document.getElementById('doubleListEditorNewText').value = '';
    document.getElementById('doubleListEditorSave').style.display='none';
    document.getElementById('doubleListEditorNew').style.display='';
    document.getElementById('doubleListEditorNewList').style.display='none';
    document.getElementById('doubleListEditorRename').style.display='';
}

function doubleListEditorSaveValue()
{
    var newValue = document.getElementById('doubleListEditorNewText').value;

    doubleListEditorModifiedListRight = doubleListEditorModifiedListRight.concat(
        Array(Array(
            document.getElementById('doubleListEditorSelectRight').options[modifyingIndex].text,
            newValue)));

    document.getElementById('doubleListEditorNewText').value = '';

    doubleListEditorListRight.splice(modifyingIndex,1);

    doubleListEditorListRight = doubleListEditorListRight.concat(Array(newValue));

    doubleListEditorListRight.sort();

    doubleListEditorBuildSelectRight();
    doubleListEditorDumpListRight();

    for (var i = 0; i < doubleListEditorListLeft.length; i++)
    {
        if (doubleListEditorListLeft[i] == oldValue)
        {
            doubleListEditorListLeft[i] = newValue;
            doubleListEditorBuildSelectLeft();
            doubleListEditorDumpListLeft();
        }
    }
    doubleListEditorDoAdd();
}
