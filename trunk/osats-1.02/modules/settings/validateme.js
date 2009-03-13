/*
 * OSATS
 * Form Validator for input of data from various forms through out the settings area.
 *
 */

function CheckMyForm(form)
{
   var myname, mylast, myusername, myemail, mypass, mypass2, setpass;
   with(window.document.UserForm)
   {
      myname    	= First;  
      mylast 	= Last;
      myusername = UserName;
      myemail   	= EmailAddress;
      mypass	=	Password;
      mypass2	=	Password2;
      setpass  =  SetPass;

   }
   if(trim(myname.value) == '')
   {
      alert('Please enter a first name');
      myname.focus();
      return false;
   }
   else if(trim(mylast.value) == '')
   {
      alert('Please enter a last name');
      mylast.focus();
      return false;
   }
   else if(trim(myemail.value) == '')
   {
      alert('Please enter an email address');
      myemail.focus();
      return false;
   }
   else if(trim(myusername.value) == '')
   {
      alert('Please a username');
      myusername.focus();
      return false;
   }
   else if (trim(setpass.value) == '1')
   {
   	if(trim(mypass.value) == '')
   	{
      alert('Please create a password');
      mypass.focus();
      return false;
   	}
   	else if(trim(mypass2.value) == '')
   	{
      alert('Please retype the password again!');
      mypass2.focus();
      return false;
   	}
   	else if(trim(mypass2.value) !== (trim(mypass.value)))
   	{
      alert('The passwords do not match!');
      mypass2.focus();
      return false;
   	}
   	else if (trim(mypass.value) == 'OSATS')
   	{
		alert('You cannot use the password: OSATS');
		mypass.focus();
		return false;
	}
   }
   
   else
   {
	  myname.value    = trim(myname.value);
      myemail.value   = trim(myemail.value);
      mylast.value = trim(mylast.value);
      myusername.value = trim(myusername.value);
      if (trim(setpass.value) == '1')
      {
	  	mypass.value = trim(mypass.value);
      	mypass2.value = trim(mypass2.value);
      }
	  return true;
   }
}

function CheckMySiteName(form)
{
    var mysitename;
    with(window.document.SiteNameForm)
   	{
    	mysitename    	= MySiteName;  
	}
    if (trim(mysitename.value) == '')
    {
    	alert('Please type in a Site Name or choose Back to cancel!');
    	mysitename.focus();
    	return false;
    }
    else
    {
    	mysitename.value    = trim(mysitename.value);
    	return true;
    }
}

function ChangePass(form) 
{
    var mynewpass1, mynewpass2;
    with (window.document.PwdUserForm)
    {
		mynewpass1 = Password;
		mynewpass2 = Password2;
	}
    if (trim(mynewpass1.value) == '')
    {
		alert ('You cannot have a blank password!');
		mynewpass1.focus();
		return false;
	}
	else if (trim(mynewpass1.value) !== (trim(mynewpass2.value)))
    {
    	alert('Your Passwords do not Match! Try again.');
    	mynewpass1.focus();
    	return false;
    }
    else if (trim(mynewpass1.value) == 'OSATS')
   	{
		alert('You cannot use the password: OSATS');
		mynewpass1.focus();
		return false;
	}
    else
    {
    	mynewpass1.value    = trim(mynewpass1.value);
    	return true;
    }
}
