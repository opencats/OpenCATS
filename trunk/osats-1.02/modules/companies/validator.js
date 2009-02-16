/*
 * CATS
 * Companies Form Validation
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * All rights reserved.
 *
 * $Id: validator.js 1887 2007-02-20 05:17:10Z will $
 */

function checkAddForm(form)
{
    var errorMessage = '';

    errorMessage += checkName();

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

    errorMessage += checkName();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkAttachmentForm(form)
{
    var errorMessage = '';

    errorMessage += checkFilename();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkSearchByNameForm(form)
{
    var errorMessage = '';

    errorMessage += checkSearchName();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkSearchByKeyTechnologiesForm(form)
{
    var errorMessage = '';

    errorMessage += checkSearchKeyTechnologies();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkName()
{
    var errorMessage = '';

    fieldValue = document.getElementById('name').value;
    fieldLabel = document.getElementById('nameLabel');
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter a name.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkSearchName()
{
    var errorMessage = '';

    fieldValue = document.getElementById('wildCardString_name').value;
    fieldLabel = document.getElementById('wildCardStringLabel_name');
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter some search text.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkSearchKeyTechnologies()
{
    var errorMessage = '';

    fieldValue = document.getElementById('wildCardString_keyTechnologies').value;
    fieldLabel = document.getElementById('wildCardStringLabel_keyTechnologies');
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter some search text.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkFilename()
{
    var errorMessage = '';

    fieldValue = document.getElementById('file').value;
    fieldLabel = document.getElementById('file');
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter a file to upload.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}