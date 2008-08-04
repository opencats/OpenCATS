/*
 * CATS
 * Job Orders Form Validation
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * All rights reserved.
 *
 * $Id: validator.js 1479 2007-01-17 00:22:21Z will $
 */

var MODE_ADD = 0;
var MODE_EDIT = 1;

function checkAddForm(form)
{
    var errorMessage = '';

    errorMessage += checkEvent(MODE_ADD);
    errorMessage += checkDescription(MODE_ADD);

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }
    return true;
}

function checkEditForm(form)
{
    var errorMessage = '';

    errorMessage += checkEvent(MODE_EDIT);
    errorMessage += checkDescription(MODE_EDIT);


    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }
    return true;
}

function checkEvent(mode)
{
    var errorMessage = '';

    if (mode == MODE_ADD)
    {
        fieldValue = document.getElementById('type').selectedIndex;
        fieldLabel = document.getElementById('eventTypeLabel');
    }
    else
    {
        fieldValue = document.getElementById('typeEdit').selectedIndex;
        fieldLabel = document.getElementById('eventTypeLabelEdit');
    }
    if (fieldValue == '')
    {
        errorMessage = "    - You must select an Event Type.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkDescription(mode)
{
    var errorMessage = '';

    if (mode == MODE_ADD)
    {
        fieldValue = document.getElementById('title').value;
        fieldLabel = document.getElementById('titleLabel');
    }
    else
    {
        fieldValue = document.getElementById('titleEdit').value;
        fieldLabel = document.getElementById('titleLabelEdit');
    }
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter a Description.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}
