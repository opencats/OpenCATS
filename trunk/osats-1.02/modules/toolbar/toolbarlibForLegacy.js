/*
 * OSATS
 *
 *
 *
 */

OSATS_quickSearchLabel = '';
OSATS_storedString = Array();
OSATS_quickSearchHistory = Array();
OSATS_emailInDatabase = Array();

/* Candidate-in-System Status Flags */
EMAIL_NOT_IN_SYSTEM  = 0;
EMAIL_IN_SYSTEM      = 1;
EMAIL_STATUS_UNKNOWN = 2;

if (typeof(OSATS_getAutomaticUpdatePreferences) == 'undefined')
{
    OSATS_getAutomaticUpdatePreferences = function() { return true; }
}

/* This replaces the toolbar function defined in OSATStoolbar.js.  For backwards compatability. */
OSATS_debug = function (msg)
{
	const prefs = Components.classes[
        '@mozilla.org/preferences-service;1'
    ].getService(Components.interfaces.nsIPrefService);

    const OSATSToolbarBranch = prefs.getBranch('extensions.OSATStoolbar.');

    if (OSATSToolbarBranch.prefHasUserValue('debug'))
    {
        if (OSATSToolbarBranch.getCharPref('debug') == 1)
		{
			var consoleService = Components.classes["@mozilla.org/consoleservice;1"]
				.getService(Components.interfaces.nsIConsoleService);

			consoleService.logStringMessage('OSATS: ' + msg);
		}
    }
    else
    {
        OSATSToolbarBranch.setCharPref('debug', '0');
    }
}

/* Remove all buttons from the toolbar. */
OSATS_toolbarClear = function()
{
    var container = document.getElementById('OSATSToolBarItem');
    for (var i = container.childNodes.length; i > 0; i--)
    {
        container.removeChild(container.childNodes[0]);
    }
};

OSATS_toolbarAddGeneric = function(objectType)
{
    var temp = document.createElement(objectType);
    var container = document.getElementById('OSATSToolBarItem');
    container.appendChild(temp);
}

/* Add a button to the toolbar. */
OSATS_toolbarAddButton = function(buttonLabel, toolTip, buttonAction,
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

    var container = document.getElementById('OSATSToolBarItem');
    container.appendChild(tempButton);
}


/* Called whenever the browser needs the toolbar to return to standard
 * (page navigation changed, for example).
 */
OSATS_makeDefaultEnvironment = function()
{
	OSATS_debug ('OSATS_makeDefaultEnvironment()');

    OSATS_toolbarClear();

    document.getElementById('OSATSTB-Options').setAttribute('hidden', true);

    if (OSATS_connected)
    {
        var button = 'OSATS-buttonLeft';
    }
    else
    {
        var button = 'OSATS-buttonLeftDisc';
    }

    OSATS_toolbarAddButton(
        'Your version of the OSATS Toolbar is out of date.  Please click here to get the newest copy.',
        'Your version of the OSATS Toolbar is out of date.  Please click here to get the newest copy.',
        'OSATS_doAuthenticated(\'content.document.location.href = OSATS_getBaseURL()+\\\'?m=toolbar&a=install\\\';\');',
        button,
        'OSATS_mainButton'
    );
}

/* Called whenever OSATS hits the logout button. */

/* Call to run jsCode as a logged in user, or fail if login fails. */
OSATS_doAuthenticated = function(jsCode)
{
	OSATS_debug ('OSATS_doAuthenticated("'+jsCode+'");');

    if (OSATS_connected)
    {
        outerEval(jsCode);
    }
    else
    {
        OSATS_changePictureLoading();

        var newIndex = OSATS_storedString.length;
        OSATS_storedString[newIndex] = jsCode;

        /* OSATS_makeRequest(
            '?m=toolbar&a=authenticate&callback='
            + encodeURIComponent('outerEval(OSATS_storedString[' + newIndex + ']);')
        );
        */
        OSATS_makeRequest(
            '?m=toolbar&a=authenticate&callback=' + encodeURIComponent(newIndex),
            OSATS_responseAuthenticate
        );
    }
}

/* AJAX response. */
OSATS_responseAuthenticate = function()
{
   if (OSATS_http_request.readyState != 4)
   {
       return;
   }

    var response = OSATS_http_request.responseText;

    /* PHP Errors? */
    if (response.indexOf('</b> on line <b>') != -1)
    {
        alert('PHP Error: ' + response);
        return;
    }

    /* FIXME: Can't we simplify our protocol here a little bit? Lets make a
     *        real protocol that's consistent. 'OSATS_connected = true' is
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
    if (response.indexOf('OSATS_connected = true') == -1)
    {
        if(response.indexOf('Message:') != -1)
        {
            var message = response.substring(response.indexOf('Message:') + 8, response.length - 1);
            OSATS_authenticationFailed(message);
        }
        else
        {
            OSATS_authenticationFailed();
        }
        return;
    }

    OSATS_connected = true;
    OSATS_changePictureAuthenticated();

    if (response.indexOf('EVAL=') != -1)
    {
        outerEval(OSATS_storedString[response.substring(
            response.indexOf('EVAL=') + 6,
            response.length - 1
        ) * 1]);
    }
}

/* Adds whatever the us

/* Called when the toolbar could not authenticate. */
OSATS_authenticationFailed = function(message)
{
    OSATS_toolbarClear();

    if (typeof(message) != 'undefined')
    {
        OSATS_toolbarAddButton(
            message,
            '',
            'OSATS_showOptionsDialog();',
            'OSATS-buttonLeftDisc',
            'OSATS_mainButton'
        );
    }
    else
    {
        OSATS_toolbarAddButton(
            'OSATS could not login: invalid username or password. Click here to configure the OSATS toolbar.',
            '',
            'OSATS_showOptionsDialog();',
            'OSATS-buttonLeftDisc',
            'OSATS_mainButton'
        );

        document.getElementById('OSATSTB-Options').setAttribute('hidden', false);
    }

}

/* Show the "Authenticated" main button image. */
OSATS_changePictureAuthenticated = function()
{
    document.getElementById('OSATS_mainButton').setAttribute('class', 'OSATS-buttonLeft');
}

/* Show the "Loading" main button image. */
OSATS_changePictureLoading = function()
{
    document.getElementById('OSATS_mainButton').setAttribute('class', 'OSATS-buttonLoading');
}