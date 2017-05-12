/*
 * CATS
 * Login Form Validation
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
 *
 * $Id: validator.js 1479 2007-01-17 00:22:21Z will $
 */

function checkLoginForm(form)
{
    var errorMessage = '';

    errorMessage += checkUsername();
    errorMessage += checkPassword();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkUsername()
{
    var errorMessage = '';

    fieldValue = document.getElementById('username');
    fieldLabel = document.getElementById('usernameLabel');
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter a username.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkPassword()
{
    var errorMessage = '';

    fieldValue = document.getElementById('password');
    fieldLabel = document.getElementById('passwordLabel');
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter a password.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}
