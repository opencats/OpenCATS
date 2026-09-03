/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
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
