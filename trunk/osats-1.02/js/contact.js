/*
 * OSATS
 * GNU License
*/

var currentCompanyID = -1;

function watchCompanyIDChange(sessionCookie)
{
    if (currentCompanyID == -1)
    {
        currentCompanyID = document.getElementById('companyID').value * 1;
    }

    if (currentCompanyID != document.getElementById('companyID').value * 1 && document.getElementById('companyID').value * 1 != -1 && document.getElementById('companyID').value != '')
    {
        currentCompanyID = document.getElementById('companyID').value * 1;
        document.getElementById('departmentSelect').disabled = true;
        document.getElementById('departmentSelect').length = 3;
        ContactDepartments_populate(currentCompanyID, sessionCookie);
        CompanyContacts_populate_byCompanyID(currentCompanyID, 'reportsTo', 'ajaxIndicatorReportsTo', sessionCookie);
    }
    setTimeout("watchCompanyIDChange('"+sessionCookie+"');", 600);
}

function ContactDepartments_populate(companyID, sessionCookie)
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

            document.getElementById('address').value  = '';
            document.getElementById('city').value  = '';
            document.getElementById('state').value = '';
            document.getElementById('zip').value = '';
            return;
        }

        var addressNode     = http.responseXML.getElementsByTagName('address').item(0);
        var cityNode        = http.responseXML.getElementsByTagName('city').item(0);
        var stateNode       = http.responseXML.getElementsByTagName('state').item(0);
        var zipNode         = http.responseXML.getElementsByTagName('zip').item(0);
        var departmentsNode = http.responseXML.getElementsByTagName('departments').item(0);

        if (document.getElementById('address'))
        {
            if (addressNode.firstChild)
            {
                document.getElementById('address').value = addressNode.firstChild.nodeValue;
            }
            else
            {
                document.getElementById('address').value = '';
            }
        }

        if (cityNode.firstChild)
        {
            document.getElementById('city').value = cityNode.firstChild.nodeValue;
        }
        else
        {
            document.getElementById('city').value = '';
        }

        if (stateNode.firstChild)
        {
            document.getElementById('state').value = stateNode.firstChild.nodeValue;
        }
        else
        {
            document.getElementById('state').value = '';
        }


        if (document.getElementById('zip'))
        {
            if (zipNode.firstChild)
            {
                document.getElementById('zip').value = zipNode.firstChild.nodeValue;
            }
            else
            {
                document.getElementById('zip').value = '';
            }
        }

        if (departmentsNode.firstChild)
        {
            document.getElementById('departmentsCSV').value = departmentsNode.firstChild.nodeValue;
        }
        else
        {
            document.getElementById('departmentsCSV').value = '';
        }

        listEditorUpdateSelectFromCSV('departmentSelect', 'departmentsCSV', false);
        document.getElementById('departmentSelect').disabled = false;
    }

    AJAX_callOSATSFunction(
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