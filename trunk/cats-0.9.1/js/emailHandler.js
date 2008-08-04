/*
 * CATS
 * JavaScript Library
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
 * $Id: emailHandler.js 3078 2007-09-21 20:25:28Z will $
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
