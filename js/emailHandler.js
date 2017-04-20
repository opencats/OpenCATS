/*
 * CATS
 * JavaScript Library
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
 * $Id: emailHandler.js 3078 2007-09-21 20:25:28Z will $
 */

function populateEmailForm(cnt)
{
    var isValid = false;
    var emailTo = "";
    for (var x = 0; x < cnt; x++)
    {
        var cb = document.getElementById("email_site_user_cb_" + x);
        if(cb)
        {
            if(cb.checked === true)
            {
                isValid = true;
                if(emailTo !== "")
                {
                    emailTo += ", " + cb.value;
                }
                else
                {
                    emailTo = cb.value;
                }
            }
        }
    }

    if(isValid === true)
    {
        var emailFormTo = document.getElementById("emailTo");
        var emailFormToHidden = document.getElementById("emailToHidden");
        showEmailForm(true);
        if(emailFormTo)
        {
            emailFormTo.innerHTML = emailTo;
            emailFormToHidden.value = emailTo;
        }
    }
    else
    {
        alert("You must select at least one person to send an e-mail.");
    }
}

function showEmailForm(tf)
{
    var emailForm = document.getElementById("siteEmailForm");
    if(emailForm)
    {
        if(tf === true)
        {
            emailForm.style.visibility = "visible";
        }
        else
        {
            emailForm.style.visibility = "hidden";
        }
    }
}

function setAllBoxes(cnt, tf)
{
    for (var x = 0; x < cnt; x++)
    {
        var cb = document.getElementById("email_site_user_cb_" + x);
        if(cb)
        {
            cb.checked = tf;
        }
    }
}

function submitFinalEmail()
{
    var emailToHidden = document.getElementById("emailToHidden");

    if(emailToHidden.value === "" || document.getElementById("emailSubject").value === "" || document.getElementById("emailBody").value === "")
    {
        alert("You must have select at least one name and have a complete subject and body!");
    }
    else
    {
        // Submit the form
        document.sendSiteUserEmail.submit();
    }
}

function getTemplateTextAJAX(templateId, sessionCookie)
{

    if (templateId === "" || !stringIsNumeric(templateId))
    {
        return;
    }

    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = "&templateID=" + urlEncode(templateId);

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState !== 4)
        {
            return;
        }

        if (!http.responseXML)
        {
            alert("An error occurred while receiving a response from the server.\n\n" + http.responseText);
            return;
        }

        /* Return if we have any errors. */
        var errorCodeNode    = http.responseXML.getElementsByTagName("errorcode").item(0);
        var errorMessageNode = http.responseXML.getElementsByTagName("errormessage").item(0);
        if (!errorCodeNode.firstChild || errorCodeNode.firstChild.nodeValue !== "0")
        {
            if (errorCodeNode.firstChild.nodeValue !== "-2")
            {
                alert("An error occurred while receiving a response from the server.\n\n" + errorMessageNode.firstChild.nodeValue);
            }
            return;
        }

        var templateText = http.responseXML.getElementsByTagName("text").item(0);

        if (templateText.firstChild)
        {
            var text = templateText.firstChild.nodeValue;
            text = text.replace(/(?:\r\n|\r|\n)/g, "<br />");
            CKEDITOR.instances["emailBody"].setData(text);
        }
        else
        {
            CKEDITOR.instances["emailBody"].setData("");
        }
    }

    AJAX_callCATSFunction(
        http,
        "showTemplate",
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
}

function showTemplate(sessionCookie)
{
    document.getElementById("candidateName").value = -1;
    document.getElementById("emailPreview").innerHTML = "";

    var templateId = $("#emailTemplate").children(":selected").attr("value");
    if(templateId < 1)
    {
        document.getElementById("emailBody").value = "";
        CKEDITOR.instances["emailBody"].setData(" ");
        return;
    }
    else
    {
        getTemplateTextAJAX(templateId, sessionCookie);
    }
}

function replaceTemplateTags(sessionCookie)
{
    var candidateId = $("#candidateName").children(":selected").attr("value");
    var templateText = CKEDITOR.instances["emailBody"].getData();

    if(candidateId < 1)
    {
        document.getElementById("emailPreview").innerHTML = "";
        return;
    }
    else
    {
        getReplaceText_AJAX(candidateId, templateText, sessionCookie);
    }
}

function getReplaceText_AJAX(candidateId, templateText, sessionCookie)
{

    if (candidateId === "" || !stringIsNumeric(candidateId))
    {
        return;
    }

    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = "&candidateID=" + urlEncode(candidateId) + "&templateText=" + urlEncode(templateText);

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        if (!http.responseXML)
        {
            alert("An error occurred while receiving a response from the server.\n\n" + http.responseText);
            return;
        }

        /* Return if we have any errors. */
        var errorCodeNode    = http.responseXML.getElementsByTagName("errorcode").item(0);
        var errorMessageNode = http.responseXML.getElementsByTagName("errormessage").item(0);

        if (!errorCodeNode.firstChild || errorCodeNode.firstChild.nodeValue != "0")
        {
            if (errorCodeNode.firstChild.nodeValue != "-2")
            {
                alert("An error occurred while receiving a response from the server.\n\n" + errorMessageNode.firstChild.nodeValue);
            }
            return;
        }

        var templateTextReplaced = http.responseXML.getElementsByTagName("text").item(0);

        if (templateTextReplaced.firstChild)
        {
            document.getElementById("emailPreview").innerHTML = templateTextReplaced.firstChild.textContent;
        }
        else
        {
            document.getElementById("emailPreview").innerHTML = "";
        }
    }

    AJAX_callCATSFunction(
        http,
        "replaceTemplateTags",
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
};
