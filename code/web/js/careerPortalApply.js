/* Set the action of the DOM container */
function setSubAction(action)
{
    var obj = document.getElementById('applyToJobSubAction');
    if (obj)
    {
        obj.value = action;
    }
}

/* Check if there's a resume to upload */
function resumeLoadCheck()
{
    var fileInput = document.getElementById('resumeFile');
    var parseButton = document.getElementById('resumePopulate');
    var resumeUpload = document.getElementById('resumeLoad');

    resumeUpload.disabled = (fileInput.value).length ? false : true;
    if (parseButton)
    {
        parseButton.disabled = (fileInput.value).length ? false : true;
    }
}

/* Load the contents of the uploaded file into the textarea box */
function resumeLoadFile()
{
    setSubAction('resumeLoad');
    document.applyToJobForm.submit();
}

function resumeParse()
{
    var fileInput = document.getElementById('resumeFile');
    var resumeContents = document.getElementById('resumeContents');
    if ((resumeContents.value).length || (fileInput.value).length)
    {
        setSubAction('resumeParse');
        document.applyToJobForm.submit();
    }
}

function resumeContentsChange(e)
{
    var parseButton = document.getElementById('resumePopulate');
    var fileInput = document.getElementById('resumeFile');
    if (parseButton)
    {
        parseButton.disabled = !(e.value).length && !(fileInput.value).length ? true : false;
    }
}

/* Preload default career portal images (should move to template) */
var returnToMainOff = new Image(130, 25);
returnToMainOff.src = '../images/careers_return.gif';
var returnToMainOn = new Image(130, 25);
returnToMainOn.src = '../images/careers_return-o.gif';

var rssFeedOff = new Image(130, 25);
rssFeedOff.src = '../images/careers_rss.gif';
var rssFeedOn = new Image(130, 25);
rssFeedOn.src = '../images/careers_rss-o.gif';

var showAllJobsOff = new Image(130, 25);
showAllJobsOff.src = '../images/careers_show.gif';
var showAllJobsOn = new Image(130, 25);
showAllJobsOn.src = '../images/careers_show-o.gif';

var applyToPositionOff = new Image(130, 25);
applyToPositionOff.src = '../images/careers_apply.gif';
var applyToPositionOn = new Image(130, 25);
applyToPositionOn.src = '../images/careers_apply-o.gif';

var submitApplicationNowOff = new Image(130, 25);
submitApplicationNowOff.src = '../images/careers_submit.gif';
var submitApplicationNowOn = new Image(130, 25);
submitApplicationNowOn.src = '../images/careers_submit-o.gif';

function buttonMouseOver(ename, tf)
{
    var e = document.getElementById(ename);
    var tag;
    if (tf)
    {
        tag = 'On';
    }
    else
    {
        tag = 'Off';
    }
    eval('e.src = ' + ename + tag + '.src');
}

function onFocusFormField(e)
{
    var isNewNo = document.getElementById('isNewNo');

    if (e.id != 'email')
    {
        if (!isNewNo.checked)
        {
            isNewNo.checked = true;
        }
    }
}

function focusFirstField()
{
    var inputs = document.getElementsByTagName('input');
    var emailTabIndex = -1;
    var nextObjDist = -1;
    var nextObj = 0;
    var dist;

    // Get the tabIndex for the required e-mail field
    for (var i = 0; i < inputs.length; i++)
    {
        if (inputs[i].id == 'email')
        {
            emailTabIndex = inputs[i].tabIndex;
        }
    }

    // If there is no e-mail field, we can't do anything
    if (emailTabIndex == -1) return;

    // Get the next closest
    for (var i = 0; i < inputs.length; i++)
    {
        if (inputs[i].id != 'email' && inputs[i].type == 'text')
        {
            dist = Math.abs(emailTabIndex - inputs[i].tabIndex);
            if (nextObjDist == -1 || dist  < nextObjDist)
            {
                nextObjDist = dist;
                nextObj = inputs[i];
            }
        }
    }

    if (nextObj)
    {
        nextObj.focus();
        nextObj.select();
    }
}

function enableFormFields(tf)
{
    var inputs = document.getElementsByTagName('input');
    var rememberMe = document.getElementById('rememberMe');

    if (rememberMe)
    {
        rememberMe.disabled = !tf;
    }

    for (var i = 0; i < inputs.length; i++)
    {
        if (inputs[i].id != 'email' && inputs[i].type == 'text')
        {
            inputs[i].disabled = !tf;
        }
    }
}

function isCandidateRegisteredChange()
{
    var isNewYes = document.getElementById('isNewYes');
    var isNewNo = document.getElementById('isNewNo');

    if (isNewYes.checked)
    {
        enableFormFields(false);
    }
    else
    {
        enableFormFields(true);
        focusFirstField();
    }
}

function validateCandidateRegistration()
{
    var obj;
    var isNewObj = document.getElementById('isNewYes');
    var isNew = isNewObj ? isNewObj.checked : false;

    var formFields = [
        'firstName', 'lastName', 'zipCode', 'address', 'city', 'state', 'homePhone',
        'mobilePhone', 'workPhone'
    ];

    // E-mail address is the only required field regardless of registered/unregistered
    if (obj = document.getElementById('email'))
    {
        if (!(obj.value).match(/^[A-Za-z0-9\.\-\_]+\@[A-Za-z0-9\.\-\_]+\.[A-Za-z0-9]{2,6}$/))
        {
            obj.style.backgroundColor = '#FDF0F0';
            alert('Please enter a valid e-mail address.');
            return false;
        }
    }

    if (!isNew)
    {
        var error = false;
        for (var fieldIndex = 0; fieldIndex < formFields.length; fieldIndex++)
        {
            if (obj = document.getElementById(formFields[fieldIndex]))
            {
                if (!(obj.value).length)
                {
                    obj.style.backgroundColor = '#FDF0F0';
                    error = true;
                }
            }
        }
        if (error)
        {
            alert("Because you have registered before, please complete all the fields to login.\n\nIf you haven\'t registered before, please select \"I have not registered on this website\".");
            return false;
        }
    }

    return true;
}
