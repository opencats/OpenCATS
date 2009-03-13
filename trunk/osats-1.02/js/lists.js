/*
 * OSATS
 * GNU License
*/

function editListRow(rowNumber)
{
    document.getElementById('savedListRowAjaxing'+rowNumber).style.display = 'none';
    document.getElementById('savedListRowEditing'+rowNumber).style.display = '';
    document.getElementById('savedListRow'+rowNumber).style.display = 'none';
    document.getElementById('savedListRowInput'+rowNumber).focus();
}

function saveListRow(rowNumber, sessionCookie)
{
    document.getElementById('savedListRowAjaxing'+rowNumber).style.display = '';
    document.getElementById('savedListRowEditing'+rowNumber).style.display = 'none';
    document.getElementById('savedListRow'+rowNumber).style.display = 'none';

    /* Write change to database. */

    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '&savedListID='+rowNumber+'&savedListName='+document.getElementById('savedListRowInput'+rowNumber).value;

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
            downloadBlock = false;
            return;
        }

        /* Return if we have any errors. */
        var errorCodeNode    = http.responseXML.getElementsByTagName('errorcode').item(0);
        var errorMessageNode = http.responseXML.getElementsByTagName('errormessage').item(0);
        if (!errorCodeNode.firstChild || errorCodeNode.firstChild.nodeValue != '0')
        {
            var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                             + errorMessageNode.firstChild.nodeValue;
            alert(errorMessage);
            return;
        }

        var response = http.responseXML.getElementsByTagName('response').item(0).firstChild.nodeValue;

        switch(response)
        {
            case "success":
                document.getElementById('savedListRowAjaxing'+rowNumber).style.display = 'none';
                document.getElementById('savedListRowEditing'+rowNumber).style.display = 'none';
                document.getElementById('savedListRow'+rowNumber).style.display = '';
                document.getElementById('savedListRowDescriptionArea'+rowNumber).innerHTML = document.getElementById('savedListRowInput'+rowNumber).value;
                break;

            case "collision":
                document.getElementById('savedListRowAjaxing'+rowNumber).style.display = 'none';
                document.getElementById('savedListRowEditing'+rowNumber).style.display = '';
                document.getElementById('savedListRow'+rowNumber).style.display = 'none';
                alert('That name is already in use, please try another.');
                break;

            case "badName":
                document.getElementById('savedListRowAjaxing'+rowNumber).style.display = 'none';
                document.getElementById('savedListRowEditing'+rowNumber).style.display = '';
                document.getElementById('savedListRow'+rowNumber).style.display = 'none';
                alert('Please type a name for the list.');
                break;
        }
    }

    AJAX_callOSATSFunction(
        http,
        'lists:editListName',
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
}

function addListRow(sessionCookie)
{
    document.getElementById('savedListNew').style.display = '';
    document.getElementById('savedListNewInput').value = '';
    document.getElementById('savedListNewInput').focus();
}

function commitNewList(sessionCookie, dataItemType)
{
    /* Write change to database. */

    var http = AJAX_getXMLHttpObject();

    var POSTData = '&dataItemType='+dataItemType+'&description='+document.getElementById('savedListNewInput').value;

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
            downloadBlock = false;
            return;
        }

        /* Return if we have any errors. */
        var errorCodeNode    = http.responseXML.getElementsByTagName('errorcode').item(0);
        var errorMessageNode = http.responseXML.getElementsByTagName('errormessage').item(0);
        if (!errorCodeNode.firstChild || errorCodeNode.firstChild.nodeValue != '0')
        {
            var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                             + errorMessageNode.firstChild.nodeValue;
            alert(errorMessage);
            return;
        }

        var response = http.responseXML.getElementsByTagName('response').item(0).firstChild.nodeValue;

        switch(response)
        {
            case "success":
                var currTime = new Date();
                var newDoc = document.location + '';
                if (newDoc.indexOf('&scrolldown=true&timePreventsCacheing') != -1)
                {
                    newDoc = newDoc.substr(0,newDoc.indexOf('&scrolldown=true&timePreventsCacheing'));
                }

                newDoc += '&scrolldown=true&timePreventsCacheing='+currTime.getTime();
                document.location = newDoc;
                break;

            case "collision":
                document.getElementById('savedListNew').style.display = '';
                document.getElementById('savedListNewAjaxing').style.display = 'none';
                alert('That name is already in use, please try another.');
                break;

            case "badName":
                document.getElementById('savedListNew').style.display = '';
                document.getElementById('savedListNewAjaxing').style.display = 'none';
                alert('Please type a name for the list.');
                break;
        }
    }

    AJAX_callOSATSFunction(
        http,
        'lists:newList',
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
}

function deleteListFromListView(savedListID, numberEntries)
{
    if (numberEntries != 0 && !confirm("Do you really want to delete this saved list with "+numberEntries+" entries?"))
    {
         return;
    }

    document.location.href = OSATSIndexName + '?m=lists&a=deleteStaticList&savedListID=' + savedListID;
}

function deleteListRow(savedListID, sessionCookie, numberEntries)
{
    if (numberEntries != 0 && !confirm("Do you really want to delete this saved list with "+numberEntries+" entries?"))
    {
         return;
    }

    document.getElementById('savedListRowAjaxing'+savedListID).style.display = '';
    document.getElementById('savedListRowEditing'+savedListID).style.display = 'none';
    document.getElementById('savedListRow'+savedListID).style.display = 'none';

    /* Write change to database. */

    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '&savedListID='+savedListID;

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
            downloadBlock = false;
            return;
        }

        /* Return if we have any errors. */
        var errorCodeNode    = http.responseXML.getElementsByTagName('errorcode').item(0);
        var errorMessageNode = http.responseXML.getElementsByTagName('errormessage').item(0);
        if (!errorCodeNode.firstChild || errorCodeNode.firstChild.nodeValue != '0')
        {
            var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                             + errorMessageNode.firstChild.nodeValue;
            alert(errorMessage);
            return;
        }

        var response = http.responseXML.getElementsByTagName('response').item(0).firstChild.nodeValue;

        switch(response)
        {
            case "success":
                document.getElementById('savedListRowAjaxing'+savedListID).style.display = 'none';
                document.getElementById('savedListRowEditing'+savedListID).style.display = 'none';
                document.getElementById('savedListRow'+savedListID).style.display = 'none';
                relabelEvenOdd();
                break;
        }
    }

    AJAX_callOSATSFunction(
        http,
        'lists:deleteList',
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
}


function addItemsToList(sessionCookie, dataItemType)
{
    var listsToAdd = getCheckedBoxes();
    if (listsToAdd == '')
    {
        return;
    }

    document.getElementById('actionArea').style.display = 'none';
    document.getElementById('addToListBox').style.display = 'none';
    document.getElementById('addingToListAjaxing').style.display = '';

    /* Write change to database. */

    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '&dataItemType='+dataItemType+'&listsToAdd='+listsToAdd+'&itemsToAdd='+document.getElementById('dataItemArray').value;

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
            downloadBlock = false;
            return;
        }

        /* Return if we have any errors. */
        var errorCodeNode    = http.responseXML.getElementsByTagName('errorcode').item(0);
        var errorMessageNode = http.responseXML.getElementsByTagName('errormessage').item(0);
        if (!errorCodeNode.firstChild || errorCodeNode.firstChild.nodeValue != '0')
        {
            var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                             + errorMessageNode.firstChild.nodeValue;
            alert(errorMessage);
            return;
        }

        var response = http.responseXML.getElementsByTagName('response').item(0).firstChild.nodeValue;

        switch(response)
        {
            case "success":
                document.getElementById('addingToListAjaxing').style.display = 'none';
                document.getElementById('addingToListAjaxingComplete').style.display = '';
                setTimeout('parentHidePopWinRefresh();', 1500);
                break;
        }
    }

    AJAX_callOSATSFunction(
        http,
        'lists:addToLists',
        POSTData,
        callBack,
        60000,
        sessionCookie,
        false,
        false
    );
}