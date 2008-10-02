/*
 * CATS
 * Install JavaScript Library
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
 * $Id: backup.js 3700 2007-11-26 22:59:23Z brian $
 */

// FIXME: Clean me up.

var response;
var backingUp = false;
var refreshCounter = 0;
var progressFile = null;

/* Calls settings -> backup.  startBackup will output JS. */
function startBackup(AJAXFunction, extraPOSTData)
{
    document.getElementById('backupRunning').style.display = '';
    document.getElementById('progress').innerHTML = '';
    
    
    var htmlObjectID = 'tempJs';
    var http = AJAX_getXMLHttpObject();
    
    /* Build HTTP POST data. */
    var POSTData = '';
    POSTData += '&a=start';
    POSTData += extraPOSTData;

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        response = http.responseText;

        if (AJAX_isPHPError(response))
        {
            alert('PHP Error: ' + response);
            return;
        }

        document.getElementById(htmlObjectID).innerHTML = response;
        execJS(response);
    }

    AJAX_callCATSFunction(
        http,
        AJAXFunction,
        POSTData,
        callBack,
        0,
        null,
        false,
        true
    );
}

function watchBackup(directoryName, extraPOSTData, AJAXFunction)
{
    /* Set up display here. */
    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '';
    POSTData += '&a=backup';
    POSTData += extraPOSTData;

    progressFile =  directoryName + 'progress.txt';

    var callback = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        response = http.responseText;

        if (AJAX_isPHPError(response))
        {
            alert('PHP Error: ' + response);
            return;
        }

        setProgress(1);
        backupFinished();
        
    }

    backingUp = true;

    AJAX_callCATSFunction(
        http,
        AJAXFunction,
        POSTData,
        callback,
        30 * 60 * 1000,
        null,
        false,
        true
    );

    // Alternative code: document.getElementById('progressIFrame').src = 'ajax.php?f=settings:backup&a=backup';

    refreshBackup();
}

/* Called by PHP to show what the progress is. */
function setStatus(theStatus)
{
    document.getElementById('backupRunning').style.display = '';
    document.getElementById('progress').innerHTML = '&nbsp;' + theStatus;
}

function setProgress(theProgress)
{
    theProgress = Math.round(theProgress * 100);
    document.getElementById('progressBar').style.display = '';

    if (theProgress > 100)
    {
        return;
    }

    if (theProgress > 12)
    {
        document.getElementById('d1').innerHTML = parseInt(theProgress) + '%';
    }
    else
    {
        document.getElementById('d1').innerHTML = '';
    }

    if (theProgress > 0)
    {
        document.getElementById('d2').style.width = (theProgress * 3) + 'px';
    }
}

function progressComplete(theProgress)
{
    document.getElementById('progressHistory').innerHTML = '<table>' + theProgress + '</table>';
}

/* The following is a crude implementation of AJAX for the purpose of
 * retrieving a file unauthenticated and without using PHP.
 */
function refreshBackup()
{
    var httpGet = AJAX_getXMLHttpObject();

    var handleResponse = function()
    {
        var htmlObjectID = 'tempJs';
    
        if (httpGet.readyState == 4)
        {
            response = httpGet.responseText;
            document.getElementById(htmlObjectID).innerHTML = response;
            if (response.indexOf('setProgress') != -1)
            {
                execJS(response);
            }
    
            httpGet = AJAX_getXMLHttpObject();
            setTimeout('refreshBackup()', 1000);
        }
    }

    if (!backingUp)
    {
        return;
    }

    refreshCounter++;

    httpGet.open('post', progressFile + '?n=' + refreshCounter);
    httpGet.onreadystatechange = handleResponse;
    httpGet.send(null);
}

function backupFinished()
{
    backingUp = false;

    setTimeout('alert("Backup Complete!"); document.location.href = document.location.href+" ";', 500);
}



