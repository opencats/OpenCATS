/*
 * CATS
 * JavaScript Library
 *
 * Portions Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 * EventCache Copyright (C) 2005 Mark Wubben with modifications made
 * by Cognizo Technologies, Inc. EventCache is licensed under the CC-GNU
 * LGPL <http://creativecommons.org/licenses/LGPL/2.1/>.
 *
 * addEvent() Copyright (C) 2001 Scott Andrew LePera with modifications
 * made by Cognizo Technologies, Inc. No license was given; however,
 * modifications made by Cognizo Technologies, Inc. are subject to the
 * terms of the CATS Public License Version 1.1 (see below).
 * http://www.scottandrew.com/weblog/articles/cbs-events
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
 * $Id: lib.js 3488 2007-11-08 02:19:17Z will $
 */

/* Data item type flags. These should match up with the flags
 * from config.php.
 */
var DATA_ITEM_CANDIDATE = 100;
var DATA_ITEM_COMPANY   = 200;
var DATA_ITEM_CONTACT   = 300;
var DATA_ITEM_JOBORDER  = 400;

/* Set by TemplateUtility drawing headers. */
var CATSIndexName;

/* Default timeout for AJAX requests; 15 seconds. */
var AJAX_TIMEOUT = 15000;

function toggleVisibility()
{
    var singleQuickActionMenu = document.getElementById('singleQuickActionMenu');
    singleQuickActionMenu.style.display = singleQuickActionMenu.style.display == 'block' ? 'none' : 'block';
}

/**
 * Returns true if the string is a valid positive integer.
 *
 * @return boolean
 */
function stringIsNumeric(string)
{
    return !isNaN(string);
}

/**
 * Changes a parent document block's style attribute to make it hidden (by id).
 *
 * @return void
 */
function hideParentBlock(elementID)
{
    element = parent.document.getElementById(elementID);
    element.parentNode.removeChild(element);
}

/**
 * Changes a parent document block's style attribute to make it hidden (by id).
 *
 * @return void
 */
function showParentBlock(elementID)
{
    element = parent.document.getElementById(elementID);
    element.parentNode.removeChild(element);
}

/**
 * Opens a centered popup window.
 *
 * @return void
 */
function openCenteredPopup(url, name, width, height, scrollBars)
{
    var optionString;

    optionString  = 'width=' + width + ',height=' + height;
    optionString += ',top=' + ((screen.availHeight - height) / 2) + ',left=' + ((screen.availWidth - width) / 2);
    optionString += ',scrollbars=';
    optionString += (scrollBars ? 'yes' : 'no');

    /* Open the new window. */
    newWindow = window.open(url, name, optionString);

    /* If this window (parent) has focus, give focus to the popup (child). */
    if (window.focus)
    {
        newWindow.focus();
    }
}

/**
 * Redirects the browser to a url.
 *
 * @return void
 */
function goToURL(url)
{
    window.location = url;
}

/**
 * Redirects the browser to a url.
 *
 * @return void
 */
function parentGoToURL(url)
{
    parent.window.location = url;
}

function parentHidePopWin()
{
    parent.hidePopWin();
}

function parentHidePopWinRefresh()
{
    parent.hidePopWinRefresh();
}

function parentSetPopTitle(title)
{
    parent.setPopTitle(title);
}

/**
 * Replaces HTML special characters in text to be output-safe.
 *
 * @param string text to escape
 * @return string escaped text
 */
function escapeHTML(text)
{
    text = text.replace('&', '&amp;');
    text = text.replace('<', '&lt;');
    text = text.replace('>', '&gt;');
    text = text.replace('"', '&quot;');
    text = text.replace("'", '&apos;');

    return text;
}

/**
 * Replaces output-save HTML with real text characters.
 *
 * @param string text to unescape
 * @return string escaped text
 */
function unEscapeHTML(text)
{
    text = text.replace('&amp;', '&');
    text = text.replace('&lt;', '<');
    text = text.replace('&gt;', '>');
    text = text.replace('&quot;', '"');
    text = text.replace('&apos;', "'");

    return text;
}

/**
 * Encodes text for transmission via HTTP.
 *
 * @param string text to encode
 * @return string encoded text
 */
function urlEncode(text)
{
    /* Force JavaScript to always treat 'text' as a string. */
    text += '';

    /* encodeURIComponent() doesn't handle the ' character. */
    text = text.replace(/\'/g, '%27');
    
    /* Don't use escape(), as it doesn't properly handle UTF-8. */
    text = encodeURIComponent(text);

    return text;
}

/**
 * Acts the same as PHP's urldecode.
 *
 * @param string text to unescape
 * @return string escaped text
 */
function urlDecode(text)
{
	while (text.indexOf('+') != -1)
	{
    	text = text.replace('+', '%20');
	}
	
    /* Don't use unescape(), as it doesn't properly handle UTF-8. */
	text = decodeURIComponent(text);

    return text;
}

/**
 * Converts a JavaScript array to a seralize()-formatted PHP array in string
 * format.
 *
 * PHP: $myArray = unserialize(urldecode($_POST['myArray']));
 * Remember this is unsafe input and it should not be trusted!
 *
 * Pass this through urlEncode() (above) before adding to a request.
 */
function serializeArray(array)
{
    var string = 'a:' + array.length + ':{';
    
    for (var i = 0; i < array.length; ++i)
    {
        string += 'i:' + i + ';s:' + String(array[i]).length + ':"'
            + String(array[i]) + '";';
    }
    
    return string + '}';
}

/**
 * Removes leading and trailing whitespace from text.
 *
 * @param string text to clean up
 * @return string cleaned string
 */
function trim(text)
{
    return text.replace(/^\s*|\s*$/g, '');
}

/**
 * Gets an XMLHTTP / XMLHttpRequest object for AJAX use.
 *
 * @return void
 */
function AJAX_getXMLHttpObject()
{
    /* Array of possible names for the Microsoft XMLHTTP ActiveX. */
    var MSXML_XMLHTTP_PROGIDS = new Array(
        'Microsoft.XMLHTTP',
        'MSXML2.XMLHTTP',
        'MSXML2.XMLHTTP.5.0',
        'MSXML2.XMLHTTP.4.0',
        'MSXML2.XMLHTTP.3.0'
    );

    var xmlHttp;

    try
    {
        xmlHttp = new XMLHttpRequest();
    }
    catch (errorA)
    {
        var found = false;

        /* Try to figure out what Microsoft might have called their ActiveX control. */
        for (var i = 0; (i < MSXML_XMLHTTP_PROGIDS.length && !found); i++)
        {
            try
            {
                xmlHttp = new ActiveXObject(MSXML_XMLHTTP_PROGIDS[i]);
                found = true;
            }
            catch (errorB)
            {
            }
        }

        if (!found)
        {
            return null;
        }
    }

    return xmlHttp;
}

/**
 * Sends HTTP content headers for an AJAX POST request.
 *
 * @return void
 */
function AJAX_sendPOSTHeaders(http, contentLength)
{
    http.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    /* No more allowed! */
    //http.setRequestHeader('Content-length', contentLength);
    //http.setRequestHeader('Connection', 'close');
}

/**
 * Returns a random hash to append to an HTTP POST to keep data from being
 * cached (URL-encoded).
 *
 * @return random POST variable hash
 */
function AJAX_getRandomPOSTHash()
{
    return '&rhash=' + urlEncode(parseInt(Math.random() * 99999999).toString());
}

/**
 * Returns a formatted session cookie to append to an HTTP POST.
 *
 * @return formatted cookie
 */
function AJAX_getPOSTSessionID(sessionCookie)
{
    return '&' + sessionCookie;
}

/**
 * Sends an AJAX HTTP POST request back to the specified URL.
 *
 * @return void
 */
function AJAX_POST(http, url, POSTData, callBack, timeout, sessionCookie,
    silentTimeout)
{
    /* Add a random hash to the POST data to keep IE from caching it. */
    POSTData += AJAX_getRandomPOSTHash();

    /* Append the session cookie if we're using secure AJAX. */
    if (sessionCookie != null)
    {
        POSTData += AJAX_getPOSTSessionID(sessionCookie);
    }

    /* Uncomment for debugging. */
    //alert(POSTData);

    /* Open the socket and send POST headers. */
    http.open('POST', url, true);
    AJAX_sendPOSTHeaders(http, POSTData.length);

    /* Callback function. */
    http.onreadystatechange = callBack;

    /* Send the data. */
    http.send(POSTData);

    /* Abort after timeout expires. */
    if (timeout != 0)
    {
        var timeoutCallback = function()
        {
            if (!AJAX_isCallInProgress(http))
            {
                return;
            }

           http.abort();

           if (!silentTimeout)
           {
               alert(
                   'Timeout on AJAX query after ' + (timeout / 1000) +
                   ' seconds. Please refresh the page and try again.'
               );
           }
        }

        window.setTimeout(timeoutCallback, timeout);
    }
}

/**
 * Sends an AJAX HTTP POST request to the CATS AJAX Delegation Module.
 *
 * @return void
 */
function AJAX_callCATSFunction(http, funcName, POSTData, callBack,
    extraTimeout, sessionCookie, silentTimeout, disableBuffering)
{
    /* Prepend the function name to the postdata. */
    var newPOSTData = 'f=' + funcName + POSTData;

    if (disableBuffering)
    {
        newPOSTData += '&nobuffer=true';
    }

    AJAX_POST(
        http,
        'ajax.php',
        newPOSTData,
        callBack,
        (AJAX_TIMEOUT + extraTimeout),
        sessionCookie,
        silentTimeout
    );
}

/**
 * Is an XMLHTTP object being used for an active call?
 *
 * @return boolean is object active
 */
function AJAX_isCallInProgress(http)
{
    switch (http.readyState)
    {
        case 1:
        case 2:
        case 3:
            return true;
            break;
    }

    return false;
}

/**
 * Is a PHP error message contained in responseText?
 *
 * @return boolean is PHP error
 */
function AJAX_isPHPError(responseText)
{
    return (responseText.indexOf('</b> on line <b>') != -1);
}


/*
 ****************************************************************************
 * Notes / Job Description Truncation
 ****************************************************************************
 */


showFullDescription = false;
showFullNotes       = false;

function toggleDescription()
{
    var shortNode = document.getElementById('shortDescription');
    var fullNode  = document.getElementById('fullDescription');

    toggleNode(showFullDescription, shortNode, fullNode);

    if (showFullDescription == true)
    {
        showFullDescription = false;
    }
    else
    {
        showFullDescription = true;
    }
}

function toggleNotes()
{
    var shortNode = document.getElementById('shortNotes');
    var fullNode  = document.getElementById('fullNotes');

    toggleNode(showFullNotes, shortNode, fullNode);

    if (showFullNotes == true)
    {
        showFullNotes = false;
    }
    else
    {
        showFullNotes = true;
    }
}

function toggleNode(showFull, shortNode, fullNode)
{
    if (showFull == true)
    {
        shortNode.style.display = 'block';
        fullNode.style.display  = 'none';
    }
    else
    {
        shortNode.style.display = 'none';
        fullNode.style.display  = 'block';
    }
}

/**
 * Populates a form's City and State from Zip code using AJAX
 *
 * @return void
 */
function CityState_populate(zipEditID, indicatorID)
{
    var http = AJAX_getXMLHttpObject();

    var zip = document.getElementById(zipEditID).value;
    var indicator = document.getElementById(indicatorID);

    indicator.style.visibility = 'visible';

     /* Build HTTP POST data. */
    var POSTData = '&zip=' + urlEncode(zip);

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        if (!http.responseXML)
        {
            var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                             + http.responseText;
            alert(errorMessage);
            indicator.style.visibility = 'hidden';

            return;
        }

        //alert(http.responseText);

        /* Return if we have any errors. */
        var errorCodeNode    = http.responseXML.getElementsByTagName('errorcode').item(0);
        var errorMessageNode = http.responseXML.getElementsByTagName('errormessage').item(0);
        if (!errorCodeNode.firstChild || errorCodeNode.firstChild.nodeValue != '0')
        {
            if (errorCodeNode.firstChild.nodeValue != '-2')
            {
                var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                                 + errorMessageNode.firstChild.nodeValue;
                /* FIXME
                 * Do we have to popup an error dialog, if the zip lookup AJAX request fails?
                 */
                //alert(errorMessage);
                indicator.style.visibility = 'hidden';
            }
            return;
        }

	var addressNode = http.responseXML.getElementsByTagName('address').item(0);
        var cityNode  = http.responseXML.getElementsByTagName('city').item(0);
        var stateNode = http.responseXML.getElementsByTagName('state').item(0);

	if (document.getElementById('address'))
        {
            if (addressNode.firstChild)
            {
                document.getElementById('address').value = addressNode.firstChild.nodeValue;
            }
            else
            {
                document.getElementById('address').value = '';
            }
        }

        if (document.getElementById('city'))
        {
            if (cityNode.firstChild)
            {
                document.getElementById('city').value = cityNode.firstChild.nodeValue;
            }
            else
            {
                document.getElementById('city').value = '';
            }
        }

        if (document.getElementById('state'))
        {
            if (stateNode.firstChild)
            {
                document.getElementById('state').value = stateNode.firstChild.nodeValue;
            }
            else
            {
                document.getElementById('state').value = '';
            }
        }
        indicator.style.visibility = 'hidden';
    }

    AJAX_callCATSFunction(http, 'zipLookup', POSTData, callBack, 0, null, false, false);
}

/* Returns the value of the radio button that is selected from a radio button
 * group.
 */
function getCheckedValue(radioObj)
{
    if (!radioObj)
    {
        return '';
    }

    var radioLength = radioObj.length;
    if (typeof(radioLength) == 'undefined')
    {
        if (radioObj.checked)
        {
            return radioObj.value;
        }

        return '';
    }

    for (var i = 0; i < radioLength; i++)
    {
        if (radioObj[i].checked)
        {
            return radioObj[i].value;
        }
    }

    return '';
}

/* Checks the specified radio button out of the radio button group by value. */
function setCheckedValue(radioObj, newValue)
{
    if (!radioObj)
    {
        return;
    }

    var radioLength = radioObj.length;
    if (typeof(radioLength) == 'undefined')
    {
        radioObj.checked = (radioObj.value == newValue.toString());
        return;
    }

    for (var i = 0; i < radioLength; i++)
    {
        radioObj[i].checked = false;
        if (radioObj[i].value == newValue.toString())
        {
            radioObj[i].checked = true;
        }
    }
}

function docjslib_getRealLeft(imgElem)
{
    var xPos = eval(imgElem).offsetLeft;
    var tempEl = eval(imgElem).offsetParent;

    while (tempEl != null)
    {
        xPos += tempEl.offsetLeft;
        tempEl = tempEl.offsetParent;
    }

    return xPos;
}

function docjslib_getRealTop(imgElem)
{
    var yPos = eval(imgElem).offsetTop;
    var tempEl = eval(imgElem).offsetParent;

    while (tempEl != null)
    {
        yPos += tempEl.offsetTop;
        tempEl = tempEl.offsetParent;
    }

    return yPos;
}

function findValueInArray(array, value)
{
    for (var i = 0; i < array.length; i++)
    {
        if (array[i] == value)
        {
            return i;
        }
    }

    return -1;
}

function findValueInSelectList(selectList, value)
{
    for (var i = 0; i < selectList.length; i++)
    {
        if (selectList[i].value == value)
        {
            return i;
        }
    }

    return -1;
}

if (Array.prototype.inArray == null)
{
    Array.prototype.inArray = function(value)
    {
        var i;

        for (i = 0; i < this.length; i++)
        {
            if (this[i] === value)
            {
                return true;
            }
        }

        return false;
    };
}

if (Array.prototype.push == null)
{
    Array.prototype.push = function()
    {
        for (var i = 0; i < arguments.length; i++)
        {
            this[this.length] = arguments[i];
        };

        return this.length;
    };
}

/* Event Cache uses an anonymous function to create a hidden scope chain.
 * This is to prevent scoping issues.
 */
var EventCache = function()
{
    var listEvents = [];

    /* This open-brace MUST BE on the same line as the return. */
    return {
        listEvents : listEvents,

        add : function (node, eventName, handler, useCapture)
        {
            listEvents.push(arguments);
        },

        flush : function()
        {
            var i, item;

            for (i = listEvents.length - 1; i >= 0; i = i - 1)
            {
                item = listEvents[i];

                if (item[0].removeEventListener)
                {
                    item[0].removeEventListener(item[1], item[2], item[3]);
                };

                /* From this point on we need the event names to be prefixed
                 * with 'on". */
                if (item[1].substring(0, 2) != 'on')
                {
                    item[1] = 'on' + item[1];
                };

                if (item[0].detachEvent)
                {
                    item[0].detachEvent(item[1], item[2]);
                };

                item[0][item[1]] = null;
            };
        }
    };
}();

function addEvent(obj, type, fn, useCapture)
{
    if (obj.addEventListener)
    {
        obj.addEventListener(type, fn, useCapture);
        EventCache.add(obj, type, fn, useCapture);
    }
    else if (obj.attachEvent)
    {
        obj['e' + type + fn] = fn;
        obj[type + fn] = function()
        {
            obj['e' + type + fn](window.event);
        }
        obj.attachEvent('on' + type, obj[type + fn]);
        EventCache.add(obj, type, fn, useCapture);
    }
    else
    {
        //alert('Handler could not be attached.');
    }
}

function removeEvent(obj, type, fn, useCapture)
{
    if (obj.removeEventListener)
    {
        obj.removeEventListener(type, fn, useCapture);
        return true;
    }

    if (obj.detachEvent)
    {
        return obj.detachEvent('on' + type, fn);
    }

    //alert('Handler could not be removed.');
}

function checkQuickSearchForm(form)
{
    var fieldValue = document.getElementById('quickSearchFor').value;
    var fieldLabel = document.getElementById('quickSearchLabel');

    if (fieldValue == '')
    {
        fieldLabel.style.color = '#ff0000';
        return false;
    }

    fieldLabel.style.color = '#000';

    return true;
}

/* This executes all the <script> tags in dynamically loaded JavaScript. */
function execJS(text)
{
    var working = text;

    var pos = working.indexOf('<script');
    while (pos != -1)
    {
        working = working.substring(pos);
        pos = working.indexOf('>');
        if (pos == -1)
        {
            return;
        }
        working = working.substring(pos);
        pos = working.indexOf('</script>');
        var js = working.substring(1,pos);
        working = working.substring(pos);
        pos = working.indexOf('<script');
        eval(js);
    }
}

/**
*
*  MD5 (Message-Digest Algorithm)
*  http://www.webtoolkit.info/
*
**/
var md5 = function (string) {

	function RotateLeft(lValue, iShiftBits) {
		return (lValue<<iShiftBits) | (lValue>>>(32-iShiftBits));
	}

	function AddUnsigned(lX,lY) {
		var lX4,lY4,lX8,lY8,lResult;
		lX8 = (lX & 0x80000000);
		lY8 = (lY & 0x80000000);
		lX4 = (lX & 0x40000000);
		lY4 = (lY & 0x40000000);
		lResult = (lX & 0x3FFFFFFF)+(lY & 0x3FFFFFFF);
		if (lX4 & lY4) {
			return (lResult ^ 0x80000000 ^ lX8 ^ lY8);
		}
		if (lX4 | lY4) {
			if (lResult & 0x40000000) {
				return (lResult ^ 0xC0000000 ^ lX8 ^ lY8);
			} else {
				return (lResult ^ 0x40000000 ^ lX8 ^ lY8);
			}
		} else {
			return (lResult ^ lX8 ^ lY8);
		}
 	}

 	function F(x,y,z) { return (x & y) | ((~x) & z); }
 	function G(x,y,z) { return (x & z) | (y & (~z)); }
 	function H(x,y,z) { return (x ^ y ^ z); }
	function I(x,y,z) { return (y ^ (x | (~z))); }

	function FF(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(F(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};

	function GG(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(G(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};

	function HH(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(H(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};

	function II(a,b,c,d,x,s,ac) {
		a = AddUnsigned(a, AddUnsigned(AddUnsigned(I(b, c, d), x), ac));
		return AddUnsigned(RotateLeft(a, s), b);
	};

	function ConvertToWordArray(string) {
		var lWordCount;
		var lMessageLength = string.length;
		var lNumberOfWords_temp1=lMessageLength + 8;
		var lNumberOfWords_temp2=(lNumberOfWords_temp1-(lNumberOfWords_temp1 % 64))/64;
		var lNumberOfWords = (lNumberOfWords_temp2+1)*16;
		var lWordArray=Array(lNumberOfWords-1);
		var lBytePosition = 0;
		var lByteCount = 0;
		while ( lByteCount < lMessageLength ) {
			lWordCount = (lByteCount-(lByteCount % 4))/4;
			lBytePosition = (lByteCount % 4)*8;
			lWordArray[lWordCount] = (lWordArray[lWordCount] | (string.charCodeAt(lByteCount)<<lBytePosition));
			lByteCount++;
		}
		lWordCount = (lByteCount-(lByteCount % 4))/4;
		lBytePosition = (lByteCount % 4)*8;
		lWordArray[lWordCount] = lWordArray[lWordCount] | (0x80<<lBytePosition);
		lWordArray[lNumberOfWords-2] = lMessageLength<<3;
		lWordArray[lNumberOfWords-1] = lMessageLength>>>29;
		return lWordArray;
	};

	function WordToHex(lValue) {
		var WordToHexValue="",WordToHexValue_temp="",lByte,lCount;
		for (lCount = 0;lCount<=3;lCount++) {
			lByte = (lValue>>>(lCount*8)) & 255;
			WordToHexValue_temp = "0" + lByte.toString(16);
			WordToHexValue = WordToHexValue + WordToHexValue_temp.substr(WordToHexValue_temp.length-2,2);
		}
		return WordToHexValue;
	};

	function Utf8Encode(string) {
		string = string.replace(/\r\n/g,"\n");
		var utftext = "";

		for (var n = 0; n < string.length; n++) {

			var c = string.charCodeAt(n);

			if (c < 128) {
				utftext += String.fromCharCode(c);
			}
			else if((c > 127) && (c < 2048)) {
				utftext += String.fromCharCode((c >> 6) | 192);
				utftext += String.fromCharCode((c & 63) | 128);
			}
			else {
				utftext += String.fromCharCode((c >> 12) | 224);
				utftext += String.fromCharCode(((c >> 6) & 63) | 128);
				utftext += String.fromCharCode((c & 63) | 128);
			}

		}

		return utftext;
	};

	var x=Array();
	var k,AA,BB,CC,DD,a,b,c,d;
	var S11=7, S12=12, S13=17, S14=22;
	var S21=5, S22=9 , S23=14, S24=20;
	var S31=4, S32=11, S33=16, S34=23;
	var S41=6, S42=10, S43=15, S44=21;

	string = Utf8Encode(string);

	x = ConvertToWordArray(string);

	a = 0x67452301; b = 0xEFCDAB89; c = 0x98BADCFE; d = 0x10325476;

	for (k=0;k<x.length;k+=16) {
		AA=a; BB=b; CC=c; DD=d;
		a=FF(a,b,c,d,x[k+0], S11,0xD76AA478);
		d=FF(d,a,b,c,x[k+1], S12,0xE8C7B756);
		c=FF(c,d,a,b,x[k+2], S13,0x242070DB);
		b=FF(b,c,d,a,x[k+3], S14,0xC1BDCEEE);
		a=FF(a,b,c,d,x[k+4], S11,0xF57C0FAF);
		d=FF(d,a,b,c,x[k+5], S12,0x4787C62A);
		c=FF(c,d,a,b,x[k+6], S13,0xA8304613);
		b=FF(b,c,d,a,x[k+7], S14,0xFD469501);
		a=FF(a,b,c,d,x[k+8], S11,0x698098D8);
		d=FF(d,a,b,c,x[k+9], S12,0x8B44F7AF);
		c=FF(c,d,a,b,x[k+10],S13,0xFFFF5BB1);
		b=FF(b,c,d,a,x[k+11],S14,0x895CD7BE);
		a=FF(a,b,c,d,x[k+12],S11,0x6B901122);
		d=FF(d,a,b,c,x[k+13],S12,0xFD987193);
		c=FF(c,d,a,b,x[k+14],S13,0xA679438E);
		b=FF(b,c,d,a,x[k+15],S14,0x49B40821);
		a=GG(a,b,c,d,x[k+1], S21,0xF61E2562);
		d=GG(d,a,b,c,x[k+6], S22,0xC040B340);
		c=GG(c,d,a,b,x[k+11],S23,0x265E5A51);
		b=GG(b,c,d,a,x[k+0], S24,0xE9B6C7AA);
		a=GG(a,b,c,d,x[k+5], S21,0xD62F105D);
		d=GG(d,a,b,c,x[k+10],S22,0x2441453);
		c=GG(c,d,a,b,x[k+15],S23,0xD8A1E681);
		b=GG(b,c,d,a,x[k+4], S24,0xE7D3FBC8);
		a=GG(a,b,c,d,x[k+9], S21,0x21E1CDE6);
		d=GG(d,a,b,c,x[k+14],S22,0xC33707D6);
		c=GG(c,d,a,b,x[k+3], S23,0xF4D50D87);
		b=GG(b,c,d,a,x[k+8], S24,0x455A14ED);
		a=GG(a,b,c,d,x[k+13],S21,0xA9E3E905);
		d=GG(d,a,b,c,x[k+2], S22,0xFCEFA3F8);
		c=GG(c,d,a,b,x[k+7], S23,0x676F02D9);
		b=GG(b,c,d,a,x[k+12],S24,0x8D2A4C8A);
		a=HH(a,b,c,d,x[k+5], S31,0xFFFA3942);
		d=HH(d,a,b,c,x[k+8], S32,0x8771F681);
		c=HH(c,d,a,b,x[k+11],S33,0x6D9D6122);
		b=HH(b,c,d,a,x[k+14],S34,0xFDE5380C);
		a=HH(a,b,c,d,x[k+1], S31,0xA4BEEA44);
		d=HH(d,a,b,c,x[k+4], S32,0x4BDECFA9);
		c=HH(c,d,a,b,x[k+7], S33,0xF6BB4B60);
		b=HH(b,c,d,a,x[k+10],S34,0xBEBFBC70);
		a=HH(a,b,c,d,x[k+13],S31,0x289B7EC6);
		d=HH(d,a,b,c,x[k+0], S32,0xEAA127FA);
		c=HH(c,d,a,b,x[k+3], S33,0xD4EF3085);
		b=HH(b,c,d,a,x[k+6], S34,0x4881D05);
		a=HH(a,b,c,d,x[k+9], S31,0xD9D4D039);
		d=HH(d,a,b,c,x[k+12],S32,0xE6DB99E5);
		c=HH(c,d,a,b,x[k+15],S33,0x1FA27CF8);
		b=HH(b,c,d,a,x[k+2], S34,0xC4AC5665);
		a=II(a,b,c,d,x[k+0], S41,0xF4292244);
		d=II(d,a,b,c,x[k+7], S42,0x432AFF97);
		c=II(c,d,a,b,x[k+14],S43,0xAB9423A7);
		b=II(b,c,d,a,x[k+5], S44,0xFC93A039);
		a=II(a,b,c,d,x[k+12],S41,0x655B59C3);
		d=II(d,a,b,c,x[k+3], S42,0x8F0CCC92);
		c=II(c,d,a,b,x[k+10],S43,0xFFEFF47D);
		b=II(b,c,d,a,x[k+1], S44,0x85845DD1);
		a=II(a,b,c,d,x[k+8], S41,0x6FA87E4F);
		d=II(d,a,b,c,x[k+15],S42,0xFE2CE6E0);
		c=II(c,d,a,b,x[k+6], S43,0xA3014314);
		b=II(b,c,d,a,x[k+13],S44,0x4E0811A1);
		a=II(a,b,c,d,x[k+4], S41,0xF7537E82);
		d=II(d,a,b,c,x[k+11],S42,0xBD3AF235);
		c=II(c,d,a,b,x[k+2], S43,0x2AD7D2BB);
		b=II(b,c,d,a,x[k+9], S44,0xEB86D391);
		a=AddUnsigned(a,AA);
		b=AddUnsigned(b,BB);
		c=AddUnsigned(c,CC);
		d=AddUnsigned(d,DD);
	}

	var temp = WordToHex(a)+WordToHex(b)+WordToHex(c)+WordToHex(d);

	return temp.toLowerCase();
}
/* End of MD5. */

function rot13(theString)
{ 
	return theString.replace(/[a-zA-Z]/g, function(c)
	    {
		    return String.fromCharCode((c <= "Z" ? 90 : 122) >= (c = c.charCodeAt(0) + 13) ? c : c - 26);
	    }
	);
};

/*
PROJECT: Javascript Based Base64 Encoding and Decoding Engine
DATE: 02/10/2004
AUTHOR: Adrian Bacon
COPYRIGHT: You are free to use this code as you see fit provided
that you send any changes or modifications back to me.
*/
var keyStr = "ABCDEFGHIJKLMNOPQRSTUVWXYZ" +
"abcdefghijklmnopqrstuvwxyz" +
"0123456789+/=";

function decode64(input)
{
   var output = "";
   var chr1, chr2, chr3;
   var enc1, enc2, enc3, enc4;
   var i = 0;

   // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
   input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");

   do {
      enc1 = keyStr.indexOf(input.charAt(i++));
      enc2 = keyStr.indexOf(input.charAt(i++));
      enc3 = keyStr.indexOf(input.charAt(i++));
      enc4 = keyStr.indexOf(input.charAt(i++));

      chr1 = (enc1 << 2) | (enc2 >> 4);
      chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
      chr3 = ((enc3 & 3) << 6) | enc4;

      output = output + String.fromCharCode(chr1);

      if (enc3 != 64) {
         output = output + String.fromCharCode(chr2);
      }
      if (enc4 != 64) {
         output = output + String.fromCharCode(chr3);
      }
   } while (i < input.length);

   return output;
}
