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
 * $Id: install.js 3700 2007-11-26 22:59:23Z brian $
 */

var response;
var maxSteps;


function setActiveStep(step)
{
    for (var i = 1; i <= maxSteps; i++)
    {
        if (i == step)
        {
            document.getElementById('step' + i).style.fontWeight = 'bold';
        }
        else
        {
            document.getElementById('step' + i).style.fontWeight = '';
        }

    }
}

function hideDivsWithin(node)
{
    var divNodes = node.getElementsByTagName('div');

    for (var i = 0; i < divNodes.length; i++)
    {
        divNodes[i].style.display = 'none';
    }
}

function showTextBlock(textBlock)
{
    document.getElementById(textBlock).style.display = '';
}

function Installpage_populate(postData, message)
{
    var htmlObjectID = 'subFormBlock';
    var http = AJAX_getXMLHttpObject();

    if (typeof(message) != 'undefined')
    {
        document.getElementById(htmlObjectID).innerHTML += message;
    }

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        response = http.responseText;

        hideDivsWithin(document.getElementById('allSpans'));
        document.getElementById(htmlObjectID).innerHTML = response;
        execJS(response);

    }

    AJAX_callCATSFunction(
        http,
        'install:ui',
        '&' + postData,
        callBack,
        10*60000, /* Ten minutes */
        null,
        false,
        false
    );
}

function Installpage_maint()
{
    var htmlObjectID = 'subFormBlock';
    var http = AJAX_getXMLHttpObject();

    if (typeof(message) != 'undefined')
    {
        document.getElementById(htmlObjectID).innerHTML += message;
    }

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        response = http.responseText;

        if (response.indexOf('setProgressUpdating') == -1)
 		{	
	        Installpage_populate('a=reindexResumes');
        }
        else
        {
            execJS(response);
        }
    }

    AJAX_callCATSFunction(
        http,
        'install:maint',
        '&performMaintenence=yes',
        callBack,
        10*60000, /* Ten minutes */
        null,
        false,
        false
    );
}

function Installpage_append(postData, message)
{
    var htmlObjectID = 'subFormBlock';
    var http = AJAX_getXMLHttpObject();
    var currentText = document.getElementById(htmlObjectID).innerHTML;
    if (typeof(message) != 'undefined')
    {
        document.getElementById(htmlObjectID).innerHTML += message;
    }

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        response = http.responseText;

        document.getElementById(htmlObjectID).innerHTML = currentText + response;
        document.getElementById('execute').innerHTML = response;

        execJS(response);
    }

    AJAX_callCATSFunction(
        http,
        'install:ui',
        '&' + postData,
        callBack,
        10*60000, /* Ten minutes */
        null,
        false,
        false
    );
}

function changeMailForm()
{
    var selectBox = document.mailForm.mailSupport;
    var mailOption = selectBox.options[selectBox.selectedIndex].value;
    var sendmailBox = document.getElementById('mailSendmailBox');
    var smtpBox = document.getElementById('mailSmtpBox');
    var smtpAuthBox = document.getElementById('mailSmtpAuthorizationBox');


    if (mailOption == 'opt2')
    {
        smtpBox.style.display = 'none';
        smtpAuthBox.style.display = 'none';
        sendmailBox.style.display = '';
    }
    else if (mailOption == 'opt3')
    {
        smtpBox.style.display = '';
        smtpAuthBox.style.display = 'none';
        sendmailBox.style.display = 'none';
    }
    else if (mailOption == "opt4")
    {
        smtpBox.style.display = '';
        smtpAuthBox.style.display = '';
        sendmailBox.style.display = 'none';
    }
    else
    {
        sendmailBox.style.display = 'none';
        smtpBox.style.display = 'none';
        smtpAuthBox.style.display = 'none';
    }
}

var firstProgressInstall;
var totalProgressInstall = 0;

function setProgressUpdating(progress, currentVersion, maxVersion, module)
{
	document.getElementById('upToDateSqlQuery').innerHTML = progress;
	document.getElementById('upToDateModuleName').innerHTML = 'Processing Module:  ' + module + ' (' + currentVersion + ')';
	
	if (totalProgressInstall != maxVersion)
	{
	    totalProgressInstall = maxVersion;
	    firstProgressInstall = currentVersion;
	}
	
    theProgress = Math.round(((currentVersion - firstProgressInstall) * 100) / (totalProgressInstall - firstProgressInstall));

    if (theProgress > 100)
    {
        return;
    }
    
    document.getElementById('d1').style.display = '';
    document.getElementById('d2').style.display = '';
    document.getElementById('d3').style.display = '';
    document.getElementById('upToDateSqlQuery').style.display = '';
    document.getElementById('upToDateSqlQueryLabel').style.display = '';

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

