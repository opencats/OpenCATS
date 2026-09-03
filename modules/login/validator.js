/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

function checkLoginForm(form)
{
    var errorMessage = "";

    errorMessage += checkUsername();
    errorMessage += checkPassword();

    if (errorMessage != "")
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkUsername()
{
    var errorMessage = "";

    fieldValue = document.getElementById("username");
    fieldLabel = document.getElementById("usernameLabel");
    if (fieldValue == "")
    {
        errorMessage = "    - You must enter a username.\n";

        fieldLabel.style.color = "#ff0000";
    }
    else
    {
        fieldLabel.style.color = "#000";
    }

    return errorMessage;
}

function checkPassword()
{
    var errorMessage = "";

    fieldValue = document.getElementById("password");
    fieldLabel = document.getElementById("passwordLabel");
    if (fieldValue == "")
    {
        errorMessage = "    - You must enter a password.\n";

        fieldLabel.style.color = "#ff0000";
    }
    else
    {
        fieldLabel.style.color = "#000";
    }

    return errorMessage;
}
