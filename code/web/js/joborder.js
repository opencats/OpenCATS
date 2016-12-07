/*
 * CATS
 * Job Orders JavaScript Library
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
 * $Id: joborder.js 3726 2007-11-27 22:58:30Z andrew $
 */

var currentCompanyID = -1;

function watchCompanyIDChangeJO(sessionCookie)
{
    if (currentCompanyID == -1)
    {
        currentCompanyID = document.getElementById('companyID').value * 1;
    }

    if (currentCompanyID != document.getElementById('companyID').value * 1 && document.getElementById('companyID').value * 1 > 0 && document.getElementById('companyID').value != '')
    {
        updateCompanyData(sessionCookie);
    }
    setTimeout("watchCompanyIDChangeJO('"+sessionCookie+"');", 600);
}

function updateCompanyData(sessionCookie)
{
    currentCompanyID = document.getElementById('companyID').value * 1;
    CompanyLocation_populate('companyID', sessionCookie);
    CompanyContacts_populate('companyID', 'contactID', 'contactsIndicator', sessionCookie);
    CompanyDepartments_populateJO(currentCompanyID, sessionCookie);
}

function CompanyDepartments_populateJO(companyID, sessionCookie)
{

    if (companyID == '' || !stringIsNumeric(companyID))
    {
        return;
    }

    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '&companyID=' + urlEncode(companyID);

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        if (!http.responseXML)
        {
            var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                             + http.responseText;
            alert(errorMessage);
            return;
        }

        /* Return if we have any errors. */
        var errorCodeNode    = http.responseXML.getElementsByTagName('errorcode').item(0);
        var errorMessageNode = http.responseXML.getElementsByTagName('errormessage').item(0);
        if (!errorCodeNode.firstChild || errorCodeNode.firstChild.nodeValue != '0')
        {
            if (errorCodeNode.firstChild.nodeValue != '-2')
            {
                var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                                 + errorMessageNode.firstChild.nodeValue;
                alert(errorMessage);
            }

            return;
        }

        var departmentsNode = http.responseXML.getElementsByTagName('departments').item(0);

        if (departmentsNode.firstChild)
        {
            document.getElementById('departmentsCSV').value = departmentsNode.firstChild.nodeValue;
        }
        else
        {
            document.getElementById('departmentsCSV').value = '';
        }
        listEditorUpdateSelectFromCSV('departmentSelect', 'departmentsCSV', true, false);
        document.getElementById('departmentSelect').disabled = false;
    }

    AJAX_callCATSFunction(
        http,
        'getCompanyLocationAndDepartments',
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
}

function checkPublic(e)
{
    var styleSheet = document.getElementById('displayQuestionnaires').style;

    if (e.checked)
    {
        if (styleSheet.display)
        {
            styleSheet.display = 'table-row';
        }
    }
    else
    {
        if (styleSheet.display)
        {
            styleSheet.display = 'none';
        }
    }
}
