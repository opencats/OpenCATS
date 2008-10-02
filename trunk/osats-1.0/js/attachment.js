/*
 * CATS
 * Attachment JavaScript Library
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
 * $Id: attachment.js 3078 2007-09-21 20:25:28Z will $
 */

var _spanObject;
var downloadBlock = false;
var downloadBlockUrl = false;
var downloadCancel = false;


function doPrepareAndDownload(getVars, url, spanObject, sessionCookie)
{
    if (downloadBlock)
    {
        if (spanObject != _spanObject)
        {
            alert ('A file is already being downloaded, please wait...');
        }
        return;
    }
    
    downloadBlock = true;
    downloadBlockUrl = url;
    
    spanObject.innerHTML = "<br /><div class=\"downloadSpan\"><nobr><img src=\"images/indicator.gif\">&nbsp;Preparing Download... <input type=\"button\" class=\"button\" onclick=\"_spanObject.innerHTML = ''; downloadCancel = true;\" value=\"Cancel\"></nobr></div>";

    _spanObject = spanObject;

    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '&' + getVars;

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
            downloadBlock = false; 
            return;
        }
            
        if (!downloadCancel)
        {
            window.location.href = url;
        }
        
        downloadCancel = false;
        
        setTimeout('downloadBlock = false; if(typeof(_spanObject != "undefined") && typeof(_spanObject.innerHTML != "undefined")) _spanObject.innerHTML = \'\';', 500);

    }
    
    AJAX_callCATSFunction(
        http,
        'getAttachmentLocal',
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
}
