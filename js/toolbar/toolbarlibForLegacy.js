/*
 * CATS
 * Firefox Toolbar JS Library - Rules
 *
 * Copyright (C) 2007 Cognizo Technologies, Inc.
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
 * $Id: toolbarlibForLegacy.js 3553 2007-11-11 22:16:13Z will $
 */

cats_quickSearchLabel = '';
cats_storedString = Array();
cats_quickSearchHistory = Array();
cats_emailInDatabase = Array();

/* Candidate-in-System Status Flags */
EMAIL_NOT_IN_SYSTEM  = 0;
EMAIL_IN_SYSTEM      = 1;
EMAIL_STATUS_UNKNOWN = 2;

if (typeof(cats_getAutomaticUpdatePreferences) == 'undefined')
{
    cats_getAutomaticUpdatePreferences = function() { return true; }
}

/* This replaces the toolbar function defined in catstoolbar.js.  For backwards compatability. */
cats_debug = function (msg)
{
	const prefs = Components.classes[
        '@mozilla.org/preferences-service;1'
    ].getService(Components.interfaces.nsIPrefService);

    const catsToolbarBranch = prefs.getBranch('extensions.catstoolbar.');

    if (catsToolbarBranch.prefHasUserValue('debug'))
    {
        if (catsToolbarBranch.getCharPref('debug') == 1)
		{
			var consoleService = Components.classes["@mozilla.org/consoleservice;1"]
				.getService(Components.interfaces.nsIConsoleService);
				
			consoleService.logStringMessage('CATS: ' + msg);
		}
    }
    else
    {
        catsToolbarBranch.setCharPref('debug', '0');
    }
}

/* Remove all buttons from the toolbar. */
cats_toolbarClear = function()
{
    var container = document.getElementById('catsToolBarItem');
    for (var i = container.childNodes.length; i > 0; i--)
    {
        container.removeChild(container.childNodes[0]);
    }
};

cats_toolbarAddGeneric = function(objectType)
{
    var temp = document.createElement(objectType);
    var container = document.getElementById('catsToolBarItem');
    container.appendChild(temp);
}

/* Add a button to the toolbar. */
cats_toolbarAddButton = function(buttonLabel, toolTip, buttonAction,
    buttonClass, id)
{
    var tempButton = document.createElement('toolbarbutton');
    tempButton.setAttribute('label', buttonLabel);
    if (toolTip != '')
    {
        tempButton.setAttribute('tooltiptext', toolTip);
    }
    tempButton.setAttribute('oncommand', buttonAction);
    tempButton.setAttribute('id', id);
    tempButton.setAttribute('class', buttonClass);

    var container = document.getElementById('catsToolBarItem');
    container.appendChild(tempButton);
}


/* Called whenever the browser needs the toolbar to return to standard
 * (page navigation changed, for example).
 */
cats_makeDefaultEnvironment = function()
{
	cats_debug ('cats_makeDefaultEnvironment()');
	
    cats_toolbarClear();
    
    document.getElementById('CATSTB-Options').setAttribute('hidden', true);

    if (cats_connected)
    {
        var button = 'cats-buttonLeft';
    }
    else
    {
        var button = 'cats-buttonLeftDisc';
    }

    cats_toolbarAddButton(
        'Your version of the CATS Toolbar is out of date.  Please click here to get the newest copy.',
        'Your version of the CATS Toolbar is out of date.  Please click here to get the newest copy.',
        'cats_doAuthenticated(\'content.document.location.href = cats_getBaseURL()+\\\'?m=toolbar&a=install\\\';\');',
        button,
        'cats_mainButton'
    );
}

/* Called whenever CATS hits the logout button. */

/* Call to run jsCode as a logged in user, or fail if login fails. */
cats_doAuthenticated = function(jsCode)
{
	cats_debug ('cats_doAuthenticated("'+jsCode+'");');
	
    if (cats_connected)
    {
        outerEval(jsCode);
    }
    else
    {
        cats_changePictureLoading();

        var newIndex = cats_storedString.length;
        cats_storedString[newIndex] = jsCode;

        /* cats_makeRequest(
            '?m=toolbar&a=authenticate&callback='
            + encodeURIComponent('outerEval(cats_storedString[' + newIndex + ']);')
        );
        */
        cats_makeRequest(
            '?m=toolbar&a=authenticate&callback=' + encodeURIComponent(newIndex),
            cats_responseAuthenticate
        );
    }
}

/* AJAX response. */
cats_responseAuthenticate = function()
{
   if (cats_http_request.readyState != 4)
   {
       return;
   }

    var response = cats_http_request.responseText;

    /* PHP Errors? */
    if (response.indexOf('</b> on line <b>') != -1)
    {
        alert('PHP Error: ' + response);
        return;
    }

    /* FIXME: Can't we simplify our protocol here a little bit? Lets make a
     *        real protocol that's consistent. 'cats_connected = true' is
     *        kindof high-bandwidth just to indicate whether or not we're
     *        connected. What about a simple binary protocol with commands
     *        and arguments?
     *
     *        TOOLBAR_COMMAND_ISCONNECTED    = 100;
     *        TOOLBAR_COMMAND_EMAILCHECK     = 200;
     *        TOOLBAR_COMMAND_CHECKUPDATE    = 300;
     *        TOOLBAR_COMMAND_DOWNLOADUPDATE = 400;
     *
     *        ...
     *
     *        TOOLBAR_REPLY_CONNECTED  = 100;
     *        TOOLBAR_REPLY_EVAL       = 200;
     *        TOOLBAR_REPLY_EMAILCHECK = 300;
     *
     *        And then the traffic looks like:
     *
     *            Connected Check:
     *            -> 100
     *            <- 100
     *
     *            E-Mail Check:
     *            -> 200 candidate@email.com
     *            <- 300 candidate@email.com 1
     *
     * Just an idea... Slickness...
     */
    if (response.indexOf('cats_connected = true') == -1)
    {
        if(response.indexOf('Message:') != -1)
        {
            var message = response.substring(response.indexOf('Message:') + 8, response.length - 1);
            cats_authenticationFailed(message);
        }
        else
        {
            cats_authenticationFailed();
        }
        return;
    }

    cats_connected = true;
    cats_changePictureAuthenticated();

    if (response.indexOf('EVAL=') != -1)
    {
        outerEval(cats_storedString[response.substring(
            response.indexOf('EVAL=') + 6,
            response.length - 1
        ) * 1]);
    }
}

/* Adds whatever the us

/* Called when the toolbar could not authenticate. */
cats_authenticationFailed = function(message)
{
    cats_toolbarClear();
    
    if (typeof(message) != 'undefined')
    {
        cats_toolbarAddButton(
            message,
            '',
            'cats_showOptionsDialog();',
            'cats-buttonLeftDisc',
            'cats_mainButton'
        );
    }
    else
    {
        cats_toolbarAddButton(
            'CATS could not login: invalid username or password. Click here to configure the CATS toolbar.',
            '',
            'cats_showOptionsDialog();',
            'cats-buttonLeftDisc',
            'cats_mainButton'
        );
        
        document.getElementById('CATSTB-Options').setAttribute('hidden', false);
    }
    
}

/* Show the "Authenticated" main button image. */
cats_changePictureAuthenticated = function()
{
    document.getElementById('cats_mainButton').setAttribute('class', 'cats-buttonLeft');
}

/* Show the "Loading" main button image. */
cats_changePictureLoading = function()
{
    document.getElementById('cats_mainButton').setAttribute('class', 'cats-buttonLoading');
}
