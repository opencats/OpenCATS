/*
 * OSATS
 * GNU License
*/

var starElement = null;

function defineImages(imageArray)
{
    _starImageArray = imageArray;
}

function getImageByIndex(imageIndex)
{
    /* The '-1' star image is actually the 7th item in the image array. */
    if (imageIndex < 0)
    {
        imageIndex = (imageIndex * -1) + 5;
    }

    return _starImageArray[imageIndex];
}

function showImage(itemID, imageIndex)
{
    document.getElementById(itemID).src = getImageByIndex(imageIndex);
}

function setRating(candidateJobOrderID, rating, imageID, sessionCookie)
{
    starElement = document.getElementById(imageID);
    starElement.src = 'images/stars/starsave.gif';

    if (candidateJobOrderID == '' || !stringIsNumeric(candidateJobOrderID))
    {
        return;
    }

    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '';
    POSTData += '&candidateJobOrderID=' + candidateJobOrderID;
    POSTData += '&rating='              + rating;

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
            alert(errorMessage);
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

        /* Locate the node we need in the XML response data. */
        var ratingNode = http.responseXML.getElementsByTagName('newrating').item(0);

        /* Use the data from the XML response to fill the form fields. */
        if (!ratingNode || !ratingNode.firstChild)
        {
            alert("An error occurred while receiving a response from the server.");
            return;
        }

        var newRatingValue = parseInt(ratingNode.firstChild.nodeValue);

        /* Remove 'screen' action icon (if exists) if rating is >=0. */
        if (newRatingValue >= 0)
        {
            screenImage = document.getElementById('screenImage' + candidateJobOrderID);
            screenLink  = document.getElementById('screenLink'  + candidateJobOrderID);

            if (screenImage && screenLink)
            {
                /* Replace the entire link with a blank icon. */
                screenImage.src = 'images/actions/blank.gif';
                var screenImageClone = screenImage.cloneNode(true);
                screenLink.parentNode.replaceChild(screenImageClone, screenLink);
            }
        }

        starElement.src = getImageByIndex(newRatingValue);
    }

    AJAX_callCATSFunction(
        http,
        'setCandidateJobOrderRating',
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
}