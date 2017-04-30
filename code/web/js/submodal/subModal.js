/**
 * POPUP WINDOW CODE v1.1
 * Used for displaying DHTML only popups instead of using buggy modal windows.
 *
 * By Seth Banks (webmaster at subimage dot com)
 * http://www.subimage.com/
 *
 * Contributions by Eric Angel (tab index code) and Scott (hiding/showing selects for IE users)
 *
 * Up to date code can be found at http://www.subimage.com/dhtml/subModal
 *
 * This code is free for you to use anywhere, just keep this comment block.
 */


/**
 * COMMON DHTML FUNCTIONS
 * These are handy functions I use all the time.
 *
 * By Seth Banks (webmaster at subimage dot com)
 * http://www.subimage.com/
 *
 * Up to date code can be found at http://www.subimage.com/dhtml/
 *
 * This code is free for you to use anywhere, just keep this comment block.
 */

/**
 * Code below taken from - http://www.evolt.org/article/document_body_doctype_switching_and_more/17/30655/
 *
 * Modified 4/22/04 to work with Opera/Moz (by webmaster at subimage dot com)
 *
 * Gets the full width/height because it's different for most browsers.
 */
function getViewportHeight()
{
    if (window.innerHeight != window.undefined)
    {
        return window.innerHeight;
    }

    if (document.compatMode == 'CSS1Compat')
    {
        return document.documentElement.clientHeight;
    }

    if (document.body)
    {
        return document.body.clientHeight;
    }

    return window.undefined;
}

function getViewportWidth()
{
    if (window.innerWidth != window.undefined)
    {
        return window.innerWidth;
    }

    if (document.compatMode == 'CSS1Compat')
    {
        return document.documentElement.clientWidth;
    }

    if (document.body)
    {
        return document.body.clientWidth;
    }

    return window.undefined;
}


// Popup code
var gPopupMask = null;
var gPopupContainer = null;
var gPopFrameIFrame = null;
var gPopFrameDiv = null;
var gReturnFunc;
var gPopupIsShown = false;
var gHideSelects = false;
var gTabIndexes = new Array();

/* List of tags that we want to disable tabbing into (for IE). */
var gTabbableTags = new Array(
    'A', 'BUTTON', 'TEXTAREA', 'INPUT', 'IFRAME'
);

// If using Mozilla or Firefox, use Tab-key trap.
if (!document.all)
{
    document.onkeypress = keyDownHandler;
}

/**
 * Initializes popup code on load.
 */
function initPopUp()
{
    gPopupMask = document.getElementById('popupMask');
    gPopupContainer = document.getElementById('popupContainer');
    gPopFrameIFrame = document.getElementById('popupFrameIFrame');
    gPopFrameDiv = document.getElementById('popupFrameDiv');

    // check to see if this is IE version 6 or lower. hide select boxes if so
    // maybe they'll fix this in version 7?
    var brsVersion = parseInt(window.navigator.appVersion.charAt(0), 10);
    if (brsVersion <= 6 && window.navigator.userAgent.indexOf('MSIE') > -1)
    {
        gHideSelects = true;
    }
}

/**
 * @argument width - int in pixels
 * @argument height - int in pixels
 * @argument url - url to display
 * @argument returnFunc - function to call when returning true from the window.
 */
function showPopWin(url, width, height, returnFunc)
{
    _showPopWin(null, url, width, height, returnFunc);
}

function showPopWinHTML(html, width, height, returnFunc)
{
    _showPopWin(html, '', width, height, returnFunc);
}

function _showPopWin(html, url, width, height, returnFunc)
{
    gPopupIsShown = true;
    disableTabIndexes();
    gPopupMask.style.display = 'block';
    gPopupContainer.style.display = 'block';
    // calculate where to place the window on screen
    centerPopWin(width, height);

    var titleBarHeight = parseInt(document.getElementById('popupTitleBar').offsetHeight, 10);

    gPopupContainer.style.width = width + 'px';
    gPopupContainer.style.height = (height+titleBarHeight) + 'px';
    // need to set the width of the iframe to the title bar width because of the dropshadow
    // some oddness was occuring and causing the frame to poke outside the border in IE6
    gPopFrameIFrame.style.width = parseInt(document.getElementById('popupTitleBar').offsetWidth, 10) + 'px';
    gPopFrameIFrame.style.height = (height) + 'px';
    gPopFrameDiv.style.width = parseInt(document.getElementById('popupTitleBar').offsetWidth, 10) + 'px';
    gPopFrameDiv.style.height = (height) + 'px';

    setPopTitle('');

    // set the url
    if (html == null)
    {
        gPopFrameDiv.style.display = 'none';
        gPopFrameIFrame.style.display = '';

        gPopFrameIFrame.src = url;
    }
    else
    {
        gPopFrameDiv.style.display = '';
        gPopFrameIFrame.style.display = 'none';

        gPopFrameDiv.innerHTML = html;
        gPopFrameDiv.innerHTML += '';
    }

    gReturnFunc = returnFunc;
    // for IE
    if (gHideSelects == true)
    {
        hideSelectBoxes();
    }
}

function setPopTitle(title)
{
    document.getElementById('popupTitle').innerHTML = title;
}

//
var gi = 0;
function centerPopWin(width, height)
{
    if (gPopupIsShown == true)
    {
        if (width == null || isNaN(width))
        {
            width = gPopupContainer.offsetWidth;
        }

        if (height == null)
        {
            height = gPopupContainer.offsetHeight;
        }

        var fullHeight = getViewportHeight();
        var fullWidth = getViewportWidth();

        var theBody = document.documentElement;

        var scTop = parseInt(theBody.scrollTop,10);
        var scLeft = parseInt(theBody.scrollLeft,10);

        gPopupMask.style.height = fullHeight + 'px';
        gPopupMask.style.width = fullWidth + 'px';
        gPopupMask.style.top = scTop + 'px';
        gPopupMask.style.left = scLeft + 'px';

        window.status = gPopupMask.style.top + ' ' + gPopupMask.style.left + ' ' + gi++;

        var titleBarHeight = parseInt(document.getElementById('popupTitleBar').offsetHeight, 10);

        gPopupContainer.style.top = (scTop + ((fullHeight - (height+titleBarHeight)) / 2)) + 'px';
        gPopupContainer.style.left =  (scLeft + ((fullWidth - width) / 2)) + 'px';
    }
}

/**
 * @argument callReturnFunc - bool - determines if we call the return function specified
 * @argument returnVal - anything - return value
 */
function hidePopWin(callReturnFunc)
{
    gPopupIsShown = false;
    restoreTabIndexes();

    if (gPopupMask == null)
    {
        return;
    }

    gPopupMask.style.display = 'none';
    gPopupContainer.style.display = 'none';
    if (callReturnFunc == true && gReturnFunc != null)
    {
        gReturnFunc(window.frames['popupFrameIFrame'].returnVal);
    }

    gPopFrameIFrame.src = 'js/submodal/loading.html';

    // display all select boxes
    if (gHideSelects == true)
    {
        displaySelectBoxes();
    }
}

function hidePopWinRefresh(callReturnFunc)
{
    hidePopWin(callReturnFunc);

    var sURL = window.location.href;
    window.location.href = (sURL+' ');
}

// Tab key trap. if popup is shown and key was [TAB], suppress it.
// @argument e - event - keyboard event that caused this function to be called.
function keyDownHandler(e)
{
    if (gPopupIsShown && e.keyCode == 9)
    {
        return false;
    }
}

/**
 * Disable all tab indexes for elements in gTabbableTags (for IE).
 *
 * @return void
 */
function disableTabIndexes()
{
    if (!document.all)
    {
        return;
    }

    var i = 0;
    for (var j = 0; j < gTabbableTags.length; j++)
    {
        var tagElements = document.getElementsByTagName(gTabbableTags[j]);

        for (var k = 0 ; k < tagElements.length; k++)
        {
            gTabIndexes[i] = tagElements[k].tabIndex;
            tagElements[k].tabIndex = '-1';
            i++;
        }
    }
}

/**
 * Re-enable all tab indexes for elements in gTabbableTags (for IE).
 *
 * @return void
 */
function restoreTabIndexes()
{
    if (!document.all)
    {
        return;
    }

    var i = 0;
    for (var j = 0; j < gTabbableTags.length; j++)
    {
        var tagElements = document.getElementsByTagName(gTabbableTags[j]);

        for (var k = 0 ; k < tagElements.length; k++)
        {
            tagElements[k].tabIndex = gTabIndexes[i];
            tagElements[k].tabEnabled = true;
            i++;
        }
    }
}


/**
* Hides all drop down form select boxes on the screen so they do not appear above the mask layer.
* IE has a problem with wanted select form tags to always be the topmost z-index or layer
*
* Thanks for the code Scott!
*/
function hideSelectBoxes()
{
    for (var i = 0; i < document.forms.length; i++)
    {
        for (var j = 0; j < document.forms[i].length; j++)
        {
            if (document.forms[i].elements[j].tagName == 'SELECT')
            {
                document.forms[i].elements[j].style.visibility = 'hidden';
            }
        }
    }
}

/**
* Makes all drop down form select boxes on the screen visible so they do not reappear after the dialog is closed.
* IE has a problem with wanted select form tags to always be the topmost z-index or layer
*/
function displaySelectBoxes()
{
    for (var i = 0; i < document.forms.length; i++)
    {
        for (var j = 0; j < document.forms[i].length; j++)
        {
            if (document.forms[i].elements[j].tagName == 'SELECT')
            {
                document.forms[i].elements[j].style.visibility='visible';
            }
        }
    }
}

addEvent(window, 'load', initPopUp, false);
addEvent(window, 'unload', EventCache.flush, false);
addEvent(window, 'resize', centerPopWin, false);
//addEvent(window, 'scroll', centerPopWin, false);
//window.onscroll = centerPopWin;
