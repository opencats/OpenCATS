/*
 * CATS
 * Tests Form Validation
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * All rights reserved.
 *
 * $Id: validator.js 1479 2007-01-17 00:22:21Z will $
 */

function selectAllCheckboxes(formID)
{
    var inputs = document.getElementById(formID).getElementsByTagName('input');
    var selectAllCheckBox = document.getElementById('selectAll');

    for (var i = 0; i < inputs.length; i++)
    {
        if (inputs[i].id != 'selectAll' && inputs[i].type == 'checkbox')
        {
            inputs[i].checked = selectAllCheckBox.checked;
        }
    }
}

function selectAllCheckboxesByClassName(formID, selectAll, className)
{
    var inputs = document.getElementById(formID).getElementsByTagName('input');
    var selectAllCheckBox = document.getElementById(selectAll);

    for (var i = 0; i < inputs.length; i++)
    {
        if (inputs[i].className == className && inputs[i].type == 'checkbox')
        {
            inputs[i].checked = selectAllCheckBox.checked;
        }
    }
}

function checkSelectForm(form)
{
    var errorMessage = '';

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}
