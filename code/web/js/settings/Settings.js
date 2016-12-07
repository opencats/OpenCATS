/*
 * CATS
 * Settings Javascript Library
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
 * $Id: Settings.js 2424 2007-05-03 07:15:02Z will $
 */

var currentModuleToAdd = null;

function customizeDashboard_showAddComponent(moduleName)
{
    customizeDashboard_hideAll();
    
    document.getElementById('addComponentB0').style.display = '';
    document.getElementById('addComponentB1').style.display = '';
    document.getElementById('addComponentB2').style.display = '';
    document.getElementById('addComponent_' + moduleName).style.display = '';
    
    currentModuleToAdd = moduleName;
}

function customizeDashboard_addToColumn(columnID, position)
{
    if (!currentModuleToAdd)
    {
        alert('To add a component, first pick a component to add from the component selector.');
        return;
    }

    document.getElementById('add_columnID').value = columnID;
    document.getElementById('add_columnPosition').value = position;
    document.getElementById('addComponentForm').submit();
}

function customizeDashboardViewEdit(componentID)
{
    customizeDashboard_hideAll();
    
    document.getElementById('editComponent' + componentID).style.display = '';
}

function customizeDashboard_removeComponent(componentID)
{
    document.getElementById('remove_componentID').value = componentID;
    document.getElementById('removeComponentForm').submit();
}

function customizeDashboard_moveComponent(componentID, position, columnID)
{
    document.getElementById('move_componentID').value  = componentID;
    document.getElementById('move_newComponentPosition').value = position;
    document.getElementById('move_componentColumn').value = columnID;
    document.getElementById('moveComponentForm').submit();
}

/**
 * Populates a company's location information from a CATS AJAX function by
 * company ID.
 *
 * @return void
 */
function testEmailSettings(sessionCookie)
{
    var testButton = document.getElementById('test');

    var testEmailAddress = document.getElementById('testEmailAddress').value;
    var fromAddress      = document.getElementById('fromAddress').value;


    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '';
    POSTData += '&testEmailAddress=' + urlEncode(testEmailAddress);
    POSTData += '&fromAddress='      + urlEncode(fromAddress);
    
    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        document.getElementById('testButtonSpan').style.display='';
        document.getElementById('testButtonSpanActive').style.display='none';

        if (http.readyState != 4)
        {
            return;
        }

        testButton.disabled = false;
        testButton.style.color = '#333';

        var testOutput = document.getElementById('testOutput');

        if (!http.responseXML)
        {
            testOutput.innerHTML = '<span style="color: #ff0000"><br />An error occurred.<br /><br />'
                + http.responseText + '</span>';
            return;
        }

        /* Return if we have any errors. */
        var errorCodeNode    = http.responseXML.getElementsByTagName('errorcode').item(0);
        var errorMessageNode = http.responseXML.getElementsByTagName('errormessage').item(0);
        if (!errorCodeNode.firstChild || errorCodeNode.firstChild.nodeValue != '0')
        {
            testOutput.innerHTML = '<span style="color: #ff0000"><br />An error occurred.<br /><br />'
                + errorMessageNode.firstChild.nodeValue + '</span>';
        }
        else
        {
            testOutput.innerHTML = '<br /><span style="color: #419933">Test reported Success!<br /><br /> '
                + 'Check your E-Mail to verify that you received the test message.</span>';
        }
    }

    testButton.disabled = true;
    testButton.style.color = '#aaa';
    AJAX_callCATSFunction(
        http,
        'testEmailSettings',
        POSTData,
        callBack,
        5000,
        sessionCookie,
        false,
        false
    );

    document.getElementById('testButtonSpan').style.display='none';
    document.getElementById('testButtonSpanActive').style.display='';
}

