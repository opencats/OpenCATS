/*
 * CATS
 * Contacts Form Validation
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * All rights reserved.
 *
 * $Id$
 */

function checkActivityForm(form)
{
    var errorMessage = '';

    errorMessage += checkActivityType();
    errorMessage += checkEventTitle();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkActivityType()
{
    var errorMessage = '';

    addActivity = document.getElementById('addActivity').checked;
    if (!addActivity)
    {
        return '';
    }

    fieldValue = document.getElementById('activityTypeID').value;
    fieldLabel = document.getElementById('addActivitySpanA');
    if (fieldValue == '')
    {
        errorMessage = "    - You must select an activity type.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkEventTitle()
{
    var errorMessage = '';

    scheduleEvent = document.getElementById('scheduleEvent').checked;
    if (!scheduleEvent)
    {
        return '';
    }

    fieldValue = document.getElementById('title').value;
    fieldLabel = document.getElementById('titleLabel');
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter an event title.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}
