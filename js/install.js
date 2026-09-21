/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

var response;
var maxSteps;
var installMaintNextAction = "a=reindexResumes";
var maintenanceOnly = false;


function setActiveStep(step)
{
    for (var i = 1; i <= maxSteps; i++)
    {
        if (i == step)
        {
            document.getElementById("step" + i).style.fontWeight = "bold";
        }
        else
        {
            document.getElementById("step" + i).style.fontWeight = "";
        }

    }
}

function hideDivsWithin(node)
{
    var divNodes = node.getElementsByTagName("div");

    for (var i = 0; i < divNodes.length; i++)
    {
        divNodes[i].style.display = "none";
    }
}

function showTextBlock(textBlock)
{
    document.getElementById(textBlock).style.display = "";
}

function Installpage_populate(postData, message)
{
    var htmlObjectID = "subFormBlock";
    var http = AJAX_getXMLHttpObject();

    if (typeof(message) != "undefined")
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

        hideDivsWithin(document.getElementById("allSpans"));
        document.getElementById(htmlObjectID).innerHTML = response;
        execJS(response);

    }

    AJAX_callCATSFunction(
        http,
        "install:ui",
        "&" + postData,
        callBack,
        10*60000, /* Ten minutes */
        null,
        false,
        false
    );
}

function Installpage_showMaintenanceRetry(http)
{
    var htmlObjectID = "subFormBlock";
    var statusMessage = "";

    if (http.status != 0)
    {
        statusMessage =
        "<p>HTTP status: " + parseInt(http.status, 10) + "</p>";
    }

    document.getElementById(htmlObjectID).innerHTML =
    "<p><strong>The database upgrade response was interrupted.</strong></p>" +
    "<p>The web server stopped waiting for this upgrade step. " +
    "The database update may still be completing in the background.</p>" +
    "<p>Please wait briefly, then click <strong>Continue Upgrade</strong>. " +
    "OpenCATS will continue from the database schema version that was " +
    "successfully recorded.</p>" +
    statusMessage +
    "<p>" +
    "<input type=\"button\" value=\"Continue Upgrade\" " +
    "onclick=\"this.disabled = true; Installpage_maint(); return false;\" />" +
    "</p>";
}

function Installpage_maint()
{
    var htmlObjectID = "subFormBlock";
    var http = AJAX_getXMLHttpObject();

    if (typeof(message) != "undefined")
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

        if (http.status < 200 || http.status >= 300)
        {
            /*
             * A web server or proxy can stop waiting for a long-running
             * maintenance request even though PHP / MariaDB continues
             * processing it. Allow the installer to resume once the
             * database operation has completed.
             */
            if (maintenanceOnly)
            {
                document.getElementById("maintenanceProgress").style.display = "none";
            }

            Installpage_showMaintenanceRetry(http);
            return;
        }

        if (maintenanceOnly &&
            (AJAX_isPHPError(response) ||
             response.indexOf("<errorcode>-1</errorcode>") != -1 ||
             response.indexOf("Query Error") != -1 ||
             response.indexOf("Access denied.") != -1))
        {
            document.getElementById("maintenanceProgress").style.display = "none";
            document.getElementById("maintenanceError").style.display = "";
            document.getElementById("startMaintenance").disabled = false;
            return;
        }

        if (response.indexOf("setProgressUpdating") == -1)
        {
            if (maintenanceOnly)
            {
                window.location = "index.php";
            }
            else
            {
                Installpage_populate(installMaintNextAction);
                installMaintNextAction = "a=reindexResumes";
            }
        }
        else
        {
            execJS(response);
        }
    }

    AJAX_callCATSFunction(
        http,
        "install:maint",
        "&performMaintenence=yes",
        callBack,
        10*60000, /* Ten minutes */
        null,
        false,
        false
    );
}

function Installpage_upgradeExisting()
{
    Installpage_populate("a=upgradeExisting");
}

function Installpage_upgradeExistingMaint()
{
    /* Existing installations must run schema maintenance before later installer questions. */
    installMaintNextAction = "a=upgradeExistingMaintComplete";
    Installpage_maint();
}

function Installpage_append(postData, message)
{
    var htmlObjectID = "subFormBlock";
    var http = AJAX_getXMLHttpObject();
    var currentText = document.getElementById(htmlObjectID).innerHTML;
    if (typeof(message) != "undefined")
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
        document.getElementById("execute").innerHTML = response;

        execJS(response);
    }

    AJAX_callCATSFunction(
        http,
        "install:ui",
        "&" + postData,
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
    var sendmailBox = document.getElementById("mailSendmailBox");
    var smtpBox = document.getElementById("mailSmtpBox");
    var smtpAuthBox = document.getElementById("mailSmtpAuthorizationBox");


    if (mailOption == "opt2")
    {
        smtpBox.style.display = "none";
        smtpAuthBox.style.display = "none";
        sendmailBox.style.display = "";
    }
    else if (mailOption == "opt3")
    {
        smtpBox.style.display = "";
        smtpAuthBox.style.display = "none";
        sendmailBox.style.display = "none";
    }
    else if (mailOption == "opt4")
    {
        smtpBox.style.display = "";
        smtpAuthBox.style.display = "";
        sendmailBox.style.display = "none";
    }
    else
    {
        sendmailBox.style.display = "none";
        smtpBox.style.display = "none";
        smtpAuthBox.style.display = "none";
    }
}

var firstProgressInstall;
var totalProgressInstall = 0;

function setProgressUpdating(progress, currentVersion, maxVersion, module)
{
    document.getElementById("upToDateSqlQuery").innerHTML = progress;
    document.getElementById("upToDateModuleName").innerHTML = "Processing Module:  " + module + " (" + currentVersion + ")";

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

    document.getElementById("d1").style.display = "";
    document.getElementById("d2").style.display = "";
    document.getElementById("d3").style.display = "";
    document.getElementById("upToDateSqlQuery").style.display = "";
    document.getElementById("upToDateSqlQueryLabel").style.display = "";

    if (theProgress > 12)
    {
        document.getElementById("d1").innerHTML = parseInt(theProgress) + "%";
    }
    else
    {
        document.getElementById("d1").innerHTML = "";
    }

    if (theProgress > 0)
    {
        document.getElementById("d2").style.width = (theProgress * 3) + "px";
    }
}
