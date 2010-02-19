/*
 * OSATS
 * Candidates Form Validation
 *
 *
 *
 */

function checkAddForm(form)
{
    var errorMessage = '';

    errorMessage += checkFirstName();
    errorMessage += checkLastName();

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

    errorMessage += checkFirstName();
    errorMessage += checkLastName();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkCreateAttachmentForm(form)
{
    var errorMessage = '';

    errorMessage += checkAttachmentFile();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkSearchByFullNameForm(form)
{
    var errorMessage = '';

    errorMessage += checkSearchFullName();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkSearchPhoneNumberForm(form)
{
    var errorMessage = '';

    errorMessage += checkPhoneNumber();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkSearchByKeySkillsForm(form)
{
    var errorMessage = '';

    errorMessage += checkSearchKeySkills();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkSearchResumeForm(form)
{
    var errorMessage = '';

    errorMessage += checkSearchResume();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkEmailForm(form)
{
    var errorMessage = '';

    errorMessage += checkEmailSubject();
    // remarked next line until we fix the allowing of html in the Function later called checkEmailBody- Jamin 2-19-2010
	//errorMessage += checkEmailBody();

    if (errorMessage != '')
    {
        alert("Form Error:\n" + errorMessage);
        return false;
    }

    return true;
}

function checkFirstName()
{
    var errorMessage = '';

    fieldValue = document.getElementById('firstName').value;
    fieldLabel = document.getElementById('firstNameLabel');
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter a first name.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkLastName()
{
    var errorMessage = '';

    fieldValue = document.getElementById('lastName').value;
    fieldLabel = document.getElementById('lastNameLabel');
    if (fieldValue == '')
    {
        errorMessage = "    - You must enter a last name.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkSearchFullName()
{
    var errorMessage = '';

    fieldValue = document.getElementById('wildCardString_fullName').value;
    fieldLabel = document.getElementById('wildCardStringLabel_fullName');
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

function checkSearchKeySkills()
{
    var errorMessage = '';

    fieldValue = document.getElementById('wildCardString_keySkills').value;
    fieldLabel = document.getElementById('wildCardStringLabel_keySkills');
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

function checkSearchResume()
{
    var errorMessage = '';

    fieldValue = document.getElementById('wildCardString_resume').value;
    fieldLabel = document.getElementById('wildCardStringLabel_resume');
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

function checkAttachmentFile()
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

function checkPhoneNumber()
{
    var errorMessage = '';

    fieldValue = document.getElementById('wildCardString_phoneNumber').value;
    fieldLabel = document.getElementById('wildCardStringLabel_phoneNumber');

    if (fieldValue == '')
    {
        errorMessage = "    - You must enter numbers to search.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

function checkEmailSubject()
{
    var errorMessage = '';

    fieldValue = document.getElementById('emailSubject').value;
    fieldLabel = document.getElementById('emailSubjectLabel');

    if (fieldValue == '')
    {
        errorMessage = "    - You must enter a subject for your e-mail.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}

//We need to fix this to allow the HTML to be seen or the validation will error on the body!
// So to prevent the error for now..I have remarked out the validation earlier in this code. See the function called CheckEmailForm - Jamin 2-19-2010
/* 
function checkEmailBody()
{
    var errorMessage = '';

    fieldValue = document.getElementById('emailBody').value;
    fieldLabel = document.getElementById('emailBodyLabel');

    if (fieldValue == '')
    {
        errorMessage = "    - You must enter a body for your e-mail.\n";

        fieldLabel.style.color = '#ff0000';
    }
    else
    {
        fieldLabel.style.color = '#000';
    }

    return errorMessage;
}
*/