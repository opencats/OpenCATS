/*
 * OSATS
 * GNU License
*/

function populateEmailForm(cnt)
{
    var isValid = false;
    var emailTo = '';
    for (var x = 0; x < cnt; x++)
    {
        var cb = document.getElementById('email_site_user_cb_' + x);
        if(cb)
        {
            if(cb.checked == true)
            {
                isValid = true;
                if(emailTo != '')
                {
                    emailTo += ', ' + cb.value;
                }
                else
                {
                    emailTo = cb.value;
                }
            }
        }
    }

    if(isValid == true)
    {
        var emailFormTo = document.getElementById('emailTo');
        var emailFormToHidden = document.getElementById('emailToHidden');
        showEmailForm(true);
        if(emailFormTo)
        {
            emailFormTo.innerHTML = emailTo;
            emailFormToHidden.value = emailTo;
        }
    }
    else
    {
        alert("You must select at least one person to send an e-mail.");
    }
}

function showEmailForm(tf)
{
    var emailForm = document.getElementById('siteEmailForm');
    if(emailForm)
    {
        if(tf == true)
        {
            emailForm.style.visibility = 'visible';
        }
        else
        {
            emailForm.style.visibility = 'hidden';
        }
    }
}

function setAllBoxes(cnt, tf)
{
    for (var x = 0; x < cnt; x++)
    {
        var cb = document.getElementById('email_site_user_cb_' + x);
        if(cb)
        {
            cb.checked = tf;
        }
    }
}

function submitFinalEmail()
{
    var emailToHidden = document.getElementById('emailToHidden');

    if(emailToHidden.value == '' || document.getElementById('emailSubject').value == '' || document.getElementById('emailBody').value == '')
    {
        alert('You must have select at least one name and have a complete subject and body!');
    }
    else
    {
        // Submit the form
        document.sendSiteUserEmail.submit();
    }
}