/*
 * OSATS
 * Open Source License Applies
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
