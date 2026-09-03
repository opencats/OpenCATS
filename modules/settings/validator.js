/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

function checkChangePasswordForm(form)
{
    var errorMessage = "";

    errorMessage += checkCurrentPassword();
    errorMessage += checkNewPassword();
    errorMessage += checkRetypeNewPassword();

    if (errorMessage == "")
    {
        errorMessage += checkPasswordsMatch();
    }

    if (errorMessage != "")
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkAddUserForm(form)
{
    var errorMessage = "";

    errorMessage += checkUserFirstName();
    errorMessage += checkUserLastName();
    errorMessage += checkUserUsername();
    errorMessage += checkUserPassword();
    errorMessage += checkUserRetypePassword();

    if (errorMessage == "")
    {
        errorMessage += checkUserPasswordsMatch();
    }

    if (errorMessage != "")
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkEditUserForm(form)
{
    var errorMessage = "";

    errorMessage += checkUserFirstName();
    errorMessage += checkUserLastName();
    errorMessage += checkUserUsername();

    if (errorMessage != "")
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkSiteNameForm(form)
{
    var errorMessage = "";

    errorMessage += checkValidSiteName();

    if (errorMessage != "")
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}


function checkUserFirstName()
{
    var errorMessage = "";

    fieldValue = document.getElementById("firstName").value;
    fieldLabel = document.getElementById("firstNameLabel");
    if (fieldValue == "")
    {
        errorMessage = "    - You must enter a first name.\n";

        fieldLabel.style.color = "#ff0000";
    }
    else
    {
        fieldLabel.style.color = "#000";
    }

    return errorMessage;
}

function checkUserLastName()
{
    var errorMessage = "";

    fieldValue = document.getElementById("lastName").value;
    fieldLabel = document.getElementById("lastNameLabel");
    if (fieldValue == "")
    {
        errorMessage = "    - You must enter a last name.\n";

        fieldLabel.style.color = "#ff0000";
    }
    else
    {
        fieldLabel.style.color = "#000";
    }

    return errorMessage;
}

function checkUserUsername()
{
    var errorMessage = "";

    fieldValue = document.getElementById("username").value;
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

function checkUserPassword()
{
    var errorMessage = "";

    fieldValue = document.getElementById("password").value;
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

function checkUserRetypePassword()
{
    var errorMessage = "";

    fieldValue = document.getElementById("retypePassword").value;
    fieldLabel = document.getElementById("retypePasswordLabel");
    if (fieldValue == "")
    {
        errorMessage = "    - You must enter a password in both fields.\n";

        fieldLabel.style.color = "#ff0000";
    }
    else
    {
        fieldLabel.style.color = "#000";
    }

    return errorMessage;
}

function checkUserPasswordsMatch()
{
    var errorMessage = "";

    fieldValue1 = document.getElementById("password").value;
    fieldValue2 = document.getElementById("retypePassword").value;

    fieldLabel1 = document.getElementById("passwordLabel");
    fieldLabel2 = document.getElementById("retypePasswordLabel");

    if (fieldValue1 != fieldValue2)
    {
        errorMessage = "    - The two passwords you entered do not match.\n";

        fieldLabel1.style.color = "#ff0000";
        fieldLabel2.style.color = "#ff0000";
    }
    else
    {
        fieldLabel1.style.color = "#000";
        fieldLabel2.style.color = "#000";
    }

    return errorMessage;
}

function checkCurrentPassword()
{
    var errorMessage = "";

    fieldValue = document.getElementById("currentPassword").value;
    fieldLabel = document.getElementById("currentPasswordLabel");
    if (fieldValue == "")
    {
        errorMessage = "    - You must enter your current password.\n";

        fieldLabel.style.color = "#ff0000";
    }
    else
    {
        fieldLabel.style.color = "#000";
    }

    return errorMessage;
}

function checkNewPassword()
{
    var errorMessage = "";

    fieldValue = document.getElementById("newPassword").value;
    fieldLabel = document.getElementById("newPasswordLabel");
    if (fieldValue == "")
    {
        errorMessage = "    - You must enter a new password.\n";

        fieldLabel.style.color = "#FF0000";
    }
    else
    {
        fieldLabel.style.color = "#000";
    }

    return errorMessage;
}

function checkRetypeNewPassword()
{
    var errorMessage = "";

    fieldValue = document.getElementById("retypeNewPassword").value;
    fieldLabel = document.getElementById("retypeNewPasswordLabel");
    if (fieldValue == "")
    {
        errorMessage = "    - You must enter your new password in both fields.\n";

        fieldLabel.style.color = "#ff0000";
    }
    else
    {
        fieldLabel.style.color = "#000";
    }

    return errorMessage;
}

function checkPasswordsMatch()
{
    var errorMessage = "";

    fieldValue1 = document.getElementById("newPassword").value;
    fieldValue2 = document.getElementById("retypeNewPassword").value;

    fieldLabel1 = document.getElementById("newPasswordLabel");
    fieldLabel2 = document.getElementById("retypeNewPasswordLabel");

    if (fieldValue1 != fieldValue2)
    {
        errorMessage = "    - The two passwords you entered do not match.\n";

        fieldLabel1.style.color = "#ff0000";
        fieldLabel2.style.color = "#ff0000";
    }
    else
    {
        fieldLabel1.style.color = "#000";
        fieldLabel2.style.color = "#000";
    }

    return errorMessage;
}

function checkValidSiteName()
{
    var errorMessage = "";
    fieldValue = document.getElementById("siteName").value;
    fieldLabel = document.getElementById("siteNameLabel");

    if (fieldValue == "")
    {
        errorMessage = "    - You must enter a Site Name.\n";

        fieldLabel.style.color = "#ff0000";
    }
    else
    {
       fieldLabel.style.color = "#000";
    }
    return errorMessage;
}