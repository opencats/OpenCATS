/*
 * CATS
 * Hello (Sample Module) Validation
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1 (the "License"); you may not use this file except in
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
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2006
 * (or from the year in which this file was created to the year 2006) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 * $Id: validator.js 76 2007-01-17 07:13:06Z will $
 */

function checkHelloForm(form)
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


function checkName()
{
    var errorMessage = '';

    fieldValue = document.getElementById('helloName').value;
    fieldLabel = document.getElementById('helloNameLabel');

    if (fieldValue == '')
    {
        errorMessage = "    - You must enter a name.\n";
        fieldLabel.style.color = '#FF0000';
    }
    else
    {
        fieldLabel.style.color = '#000000';
    }

    return errorMessage;
}

