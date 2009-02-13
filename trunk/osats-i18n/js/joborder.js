/*
 * OSATS
 * GNU License
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