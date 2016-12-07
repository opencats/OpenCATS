var failAction = '';
var userActionSuccess = '';

function showAddUser()
{
    var contentContainer = document.getElementById('contentAddUser');
    var firstName = document.getElementById('firstName');
    contentContainer.style.visibility = 'visible';
    firstName.focus();
    firstName.select();
}

function cancelAddUser()
{
    var contentContainer = document.getElementById('contentAddUser');
    if (contentContainer) contentContainer.style.visibility = 'hidden';
}

function addUser()
{
    var firstName = document.getElementById('firstName');
    var lastName = document.getElementById('lastName');
    var loginName = document.getElementById('loginName');
    var email = document.getElementById('email');
    var password1 = document.getElementById('password1');
    var password2 = document.getElementById('password2');
    var accessLevel = 0;

    if (password1.value != password2.value || (password1.value).length < 5)
    {
        alert('Please make sure both passwords match and are both at least 5 characters long.');
        return;
    }
    if ((firstName.value).length < 2 || (lastName.value).length < 2)
    {
        alert('First and last names are required and must be at least 2 characters long.');
        return;
    }
    if ((loginName.value).length < 3)
    {
        alert('Login name is required and must be at least 3 characters long.');
        return;
    }

    for (var i=0; i<400; i++)
    {
        var obj = document.getElementById('accessLevel' + i);
        if (obj && obj.checked)
        {
            accessLevel = i;
        }
    }

    failAction = 'alert("Unable to add this user. Please make sure the name isn\'t already in use and all required fields have been completed.");';
    userActionSuccess = 'cancelAddUser(); loadPage("current");';
    userAction('AddUser&firstName=' + escape(firstName.value) + '&lastName=' + escape(lastName.value) + '&loginName=' + escape(loginName.value) + '&email=' + escape(email.value) + '&accessLevel=' + accessLevel + '&password=' + escape(password1.value));
}

function deleteUser(id)
{
    failAction = 'alert("Unable to delete that user.");';
    userActionSuccess = 'cancelAddUser(); loadPage("current");';
    userAction('DeleteUser&userID=' + id);
}

function keyGood()
{
    funcNext();
}

function checkKey(obj)
{
    userActionSuccess = 'keyGood();';
    failAction = '';
    userAction('CheckKey&key=' + escape(obj.value));
}

function extendedNext()
{
    var obj;

    if (obj = document.getElementById('key'))
    {
        // This is the license page
        checkKey(obj);
        return false;
    }

    if (obj = document.getElementById('localizationBeacon'))
    {
        // This is the localization page
        var timeZone = document.getElementById('timeZone');
        var dateFormat = document.getElementById('dateFormat');
        failAction = 'alert("Unable to set your localization settings! Please try again.");';
        userActionSuccess = 'funcNext();';
        failAction = '';
        userAction('Localization&timeZone=' + escape(timeZone.value) + '&dateFormat=' + escape(dateFormat.value));
        return false;
    }

    if (obj = document.getElementById('iAgree'))
    {
        // This is the license agreement page
        userActionSuccess = 'funcNext();';
        failAction = '';
        userAction('License');
        return false;
    }

    if (obj = document.getElementById('firstTimeSetup'))
    {
        // This is the welcome page
        userActionSuccess = 'funcNext();';
        failAction = '';
        userAction('FirstTimeSetup');
        return false;
    }

    if (obj = document.getElementById('passwordBeacon'))
    {
        var password1 = document.getElementById('password1');
        var password2 = document.getElementById('password2');

        if (password1.value != password2.value)
        {
            alert('Passwords do not match!');
            return false;
        }

        if ((password1.value) == '')
        {
            alert('Please enter a password. This is how you will log into CATS.');
            return false;
        }

        if ((password1.value).length < 5)
        {
            alert('Password is too short. Please use a password with at least 5 characters.');
            return false;
        }

        failAction = '';
        userActionSuccess = 'funcNext();';
        userAction('Password&password=' + escape(password1.value));
        return false;
    }

    if (obj = document.getElementById('emailBeacon'))
    {
        var email = document.getElementById('email');

        if ((email.value) == '')
        {
            alert('Please enter an e-mail address.');
            return false;
        }

        failAction = '';
        userActionSuccess = 'funcNext();';
        userAction('Email&email=' + escape(email.value));
        return false;
    }

    if (obj = document.getElementById('siteBeacon'))
    {
        var siteName = document.getElementById('siteName');

        if ((siteName.value) == '')
        {
            alert('Please enter a name to title your site.');
            return false;
        }

        failAction = '';
        userActionSuccess = 'funcNext();';
        userAction('SiteName&siteName=' + escape(siteName.value));
        return false;
    }

    if (obj = document.getElementById('importBeacon'))
    {
        failAction = 'funcNext();';
        userActionSuccess = 'finishURL="?m=import&a=massImport&step=2"; funcNext();';
        userAction('Import');
        return false;
    }

    if (obj = document.getElementById('websiteBeacon'))
    {
        var website = document.getElementById('websiteURL');

        failAction = 'alert(ajaxObj.responseText); funcNext();';
        userActionSuccess = 'funcNext();';
        userAction('Website&website=' + escape(website.value));
        return false;
    }

    return true;
}

function userAction(action)
{
    var ajaxObj;
    var url = '?m=settings&a=ajax_wizard' + action;

    try
    {
        // Firefox, Opera 8.0+, Safari
        ajaxObj = new XMLHttpRequest();
    }
    catch (e)
    {
        // Internet Explorer
        try
        {
            ajaxObj = new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch (e)
        {
            try
            {
                ajaxObj = new ActiveXObject("Microsoft.XMLHTTP");
            }
            catch (e)
            {
                alert("Your browser does not support AJAX!");
                return false;
            }
        }
    }
    ajaxObj.onreadystatechange = function()
    {
        if (ajaxObj.readyState == 4)
        {
            if (ajaxObj.responseText == 'Ok')
            {
                if (userActionSuccess != '') eval(userActionSuccess);
            }
            else
            {
                if (failAction != '')
                {
                    eval(failAction);
                }
                else
                {
                    alert(ajaxObj.responseText);
                }
            }
        }
    }

    ajaxObj.open("GET",url,true);
    ajaxObj.send(null);
}
