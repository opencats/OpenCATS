/*
 * OSATS
 * GNU License
*/

var candidateIsAlreadyInSystem = false;
var candidateIsAlreadyInSystemID = -1;
var candidateIsAlreadyInSystemName = '';

function checkEmailAlreadyInSystem(email, sessionCookie)
{
    if (email == '')
    {
        return;
    }

    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '&email=' + urlEncode(email);

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
            /* alert(errorMessage); */
            return;
        }

        var idNode = http.responseXML.getElementsByTagName('id').item(0);

        if (idNode.firstChild.nodeValue != -1)
        {
            candidateIsAlreadyInSystem = true;
            candidateIsAlreadyInSystemID = idNode.firstChild.nodeValue;
            candidateIsAlreadyInSystemName = http.responseXML.getElementsByTagName('name').item(0).firstChild.nodeValue;

            document.getElementById('candidateAlreadyInSystemName').innerHTML = candidateIsAlreadyInSystemName;
            document.getElementById('candidateAlreadyInSystemTable').style.display = '';
        }
        else
        {
            candidateIsAlreadyInSystem = false;
            document.getElementById('candidateAlreadyInSystemTable').style.display = 'none';
        }
    }

    AJAX_callOSATSFunction(
        http,
        'getCandidateIdByEmail',
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
}

function onSubmitEmailInSystem()
{
    if (candidateIsAlreadyInSystem)
    {
        var agree=confirm("Warning:  The candidate may already be in the system.\n\nAre you sure you want to add the candidate?");
        if (agree)
        	return true ;
        else
        	return false ;
    }
}