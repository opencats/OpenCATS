/*
 * CATS
 * Candidate JavaScript Library
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
 * $Id: candidate.js 3078 2007-09-21 20:25:28Z will $
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

    AJAX_callCATSFunction(
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

function checkPhoneAlreadyInSystem(phone, sessionCookie)
{
    if (phone == '')
    {
        return;
    }

    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '&phone=' + urlEncode(phone);

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

    AJAX_callCATSFunction(
        http,
        'getCandidateIdByPhone',
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
}

function onSubmitPhoneInSystem()
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


