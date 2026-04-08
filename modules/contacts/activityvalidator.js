/*
 * OpenCATS
 * Contacts Form Validation
 */

function checkActivityForm(form)
{
    var errorMessage = "";

    errorMessage += checkActivityType();
    errorMessage += checkEventTitle();

    if (errorMessage != "")
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkActivityType()
{
    var errorMessage = "";

    var addActivity = document.getElementById("addActivity").checked;
    if (!addActivity)
    {
        return "";
    }

    var fieldValue = document.getElementById("activityTypeID").value;
    var fieldLabel = document.getElementById("addActivitySpanA");
    if (fieldValue == "")
    {
        errorMessage = "    - You must select an activity type.\n";

        fieldLabel.style.color = "#ff0000";
    }
    else
    {
        fieldLabel.style.color = "#000";
    }

    return errorMessage;
}

function checkEventTitle()
{
    var errorMessage = "";

    var scheduleEvent = document.getElementById("scheduleEvent").checked;
    if (!scheduleEvent)
    {
        return "";
    }

    var fieldValue = document.getElementById("title").value;
    var fieldLabel = document.getElementById("titleLabel");
    if (fieldValue == "")
    {
        errorMessage = "    - You must enter an event title.\n";

        fieldLabel.style.color = "#ff0000";
    }
    else
    {
        fieldLabel.style.color = "#000";
    }

    return errorMessage;
}
