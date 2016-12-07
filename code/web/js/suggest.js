/*
 * CATS
 * Contacts JavaScript Library
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
 * onFocus = "suggestListActivate(AJAX Lookup function,
 *                                textbox for user input,
 *                                DIV tag with output,
 *                                Hidden element to set ID number,
 *                                Class for highlighted text,
 *                                ID of object we are pulling data for,
 *                                PHP session cookie);"
 * Example:
 *   <input type="hidden" name="companyID" id="companyID" />
 *   <input type="text" name="companyName" id="companyName" class="inputbox"
 *          onFocus="suggestListActivate('getCompanyNames', 'companyName',
 *          'CompanyResults', 'companyID', 'ajaxTextEntryHover', 0,
 *          '<?php echo($this->sessionCookie); ?>');" />
 *  <br />
 *  <div id="CompanyResults" class="ajaxSearchResults"></div>
 *
 * Behaviors:
 *
 * 1. If typed in a option, and it is the only option in the list accept that entry and move on.
 * 2. If clicked on an option, accept that entry
 * 3. If keying up/down and tabbing away, accept that option
 * 4. If clicked on (more results), load more results
 * 5. If keyed down to (more results), load more results and put the highlight on the next option
 * 6. If more options than defined by the constant maxInitialResults, say (more results)
 * 7. Never show more results than maxTotalResults
 *
 *
 * $Id: suggest.js 3554 2007-11-11 22:17:26Z will $
 */

var maxInitialResults = 10;
var maxTotalResults = 50;

var dataNodes;
var selectedIndex;
var lastLookup;
var dataValidInput;
var moreResults;

var lookupFunction;
var textInputID;
var resultsElementID;
var IDElementID;
var highlightClass;
var sessionCookie;
var focusID;

var helpShim;
var helpShimSet = false;

function suggestListActivate(_lookupFunction, _textInputID, _resultsElementID,
    _IDElementID, _highlightClass, _focusID, _sessionCookie, helpShimID)
{
    lookupFunction   = _lookupFunction;
    textInputID      = _textInputID;
    resultsElementID = _resultsElementID;
    IDElementID      = _IDElementID;
    highlightClass   = _highlightClass;
    focusID          = _focusID;
    sessionCookie    = _sessionCookie;

    document.getElementById(textInputID).onblur  = suggestListVerify;
    document.getElementById(textInputID).onkeyup = parseKeyUp;
    document.getElementById(textInputID).onkeydown = parseKeyDown;

    if (typeof(helpShimID) != "undefined")
    {
        helpShim = document.getElementById(helpShimID);
        helpShimSet = true;
    }
}

function suggestListPopulate(focusID, sessionCookie, lookupText, maxResults, defaultSuggestedIndex)
{
    /* Set trimmedLookupText to lookupText without leading or trailing
     * whitespace.
     */
    trimmedLookupText = trim(lookupText);

    /* If the new lookup text, with whitespace omitted, is the same string as
     * before, bail.
     *
     * FIXME: Make sure this can't cause problems when trying to search for,
     * say, a name with a space in it, like "Target Corporation", once you
     * type the space after "Target". Appears fine...
     *
     * FIXME: Why are we checking to see if maxResults != maxTotalResults?
     * If this is to verify that we are in "more results" mode, it is a very
     * bad way, because someone could set maxInitialResults and maxTotalResults
     * to the same thing.
     */
    if (trimmedLookupText == lastLookup && maxResults != maxTotalResults)
    {
        return;
    }

    /* Store the current input box value in a global variable to make sure
     * we aren't doing the same search next time. See above.
     */
    lastLookup = trimmedLookupText;

    /* Get elements. */
    var textInput      = document.getElementById(textInputID);
    var resultsElement = document.getElementById(resultsElementID);
    var IDElement      = document.getElementById(IDElementID);

    /* Bail if the input box is empty or contains only whitespace. */
    if (trimmedLookupText == '')
    {
        /* Clean up a bit. */
        dataNodes = null;
        resultsElement.style.display = 'none';
        resultsElement.innerHTML = '';

        return;
    }

    /* AJAX object. */
    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '&focusID=' + urlEncode(focusID) + '&dataName='
                 + urlEncode(lookupText) + '&maxResults='
                 + urlEncode(maxResults);

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        /* Bail if there is no responseText. This shouldn't happen. */
        if (trim(http.responseText) == '')
        {
            return;
        }

        var errorCodeNode    = http.responseXML.getElementsByTagName('errorcode').item(0);
        var errorMessageNode = http.responseXML.getElementsByTagName('errormessage').item(0);

        /* Bail if there is no errorCodeNode. This shouldn't happen. */
        if (!errorCodeNode || !errorCodeNode.firstChild)
        {
            return;
        }

        /* Error returned from CATS AJAX function. */
        if (errorCodeNode.firstChild.nodeValue != '0')
        {
            return;
        }

        /* Initialize output to something safe. */
        var output = '';

        var resultTags = http.responseXML.getElementsByTagName('result');
        if (resultTags.length == 0)
        {
            /* There are no matching responses. Return and leave the input box
             * contents intact.
             */
            return;
        }

        /* Get elements. */
        var textInput      = document.getElementById(textInputID);
        var resultsElement = document.getElementById(resultsElementID);
        var IDElement      = document.getElementById(IDElementID);

        /* Set datanodes. This stays persistent as long as the restlts box is on
         * the screen.
         */
        dataNodes = resultTags;

        for (var i = 0; i < dataNodes.length; i++)
        {
            var IDNode   = dataNodes[i].getElementsByTagName('id').item(0);
            var nameNode = dataNodes[i].getElementsByTagName('name').item(0);

            if (!IDNode.firstChild || !nameNode.firstChild)
            {
                continue;
            }

            var nameNodeValue = urlDecode(nameNode.firstChild.nodeValue);

            output += '<div id="suggest' + i + '" onclick="'
                    + 'document.getElementById(textInputID).value=\''
                    + trim(nameNodeValue.replace(/'/g,"\\'")) + '\'; '
                    + 'document.getElementById(resultsElementID).style.display = \'none\'; '
                    + 'document.getElementById(IDElementID).value='
                    + IDNode.firstChild.nodeValue + ';'
                    + 'dataValidInput = true;"'
                    + 'onmouseover="this.className += highlightClass" '
                    + 'onmouseout="this.className = this.className.replace(highlightClass, \'\')">'
                    + nameNodeValue + '</div>';
        }

        var totalElements = http.responseXML.getElementsByTagName(
            'totalelements'
        ).item(0).firstChild.nodeValue;

        /* FIXME: Why are we checking to see if maxResults < maxTotalResults?
         * If this is to verify that we are in "more results" mode, it is a very
         * bad way, because someone could set maxInitialResults and maxTotalResults
         * to the same thing.
         */
        if (totalElements > maxResults && maxResults < maxTotalResults)
        {
            /* Append an item that retreives more elements when selected. */
            output += '<div id="suggestmore" onclick="'
                    + 'suggestListPopulate(' + focusID + ', \''
                    + sessionCookie + '\', lastLookup, maxTotalResults, -1);"'
                    + 'onmouseover="this.className += highlightClass"'
                    + ' onmouseout="this.className = this.className.replace(highlightClass, \'\')">'
                    + '(More Results)</div>';
            moreResults = true;
        }
        else
        {
            moreResults = false;
        }

        /* Set innerHTML of the results element to the HTML string, output. */
        resultsElement.innerHTML = output;

        selectedIndex = defaultSuggestedIndex;
        if (selectedIndex != -1)
        {
            /* This happens when the user keys down to more options... */

            /* Get the selected item DIV. */
            suggestListItemDiv = document.getElementById('suggest' + selectedIndex);

            /* Hilight the selected item DIV. */
            suggestListItemDiv.className += highlightClass;

            var selectedDataNodeNameValue = dataNodes[selectedIndex].getElementsByTagName(
                'name'
            ).item(0).firstChild.nodeValue;

            /* Put the selected item name in the input box. */
            textInput.value = trim(selectedDataNodeNameValue);
        }
    }

    AJAX_callCATSFunction(
        http,
        lookupFunction,
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );

    /* Flag the ID field as a bad value. */
    IDElement.value = -1;

    /* Display the results element. */
    resultsElement.style.display = 'block';

    followResultsElement(resultsElementID);
}

function followResultsElement(resultsID)
{
    var resultsElement = document.getElementById(resultsID);
    if (resultsElement.style.display == 'block' && helpShimSet)
    {
        var theTop = docjslib_getRealTop(resultsElement);
        var theLeft = docjslib_getRealLeft(resultsElement);
        helpShim.style.display = 'block';
        helpShim.style.zIndex = 1;
        helpShim.style.top = '' + theTop + 'px';
        helpShim.style.left = '' + theLeft + 'px';
        helpShim.style.width = '' + resultsElement.offsetWidth + '';
        helpShim.style.height = '' + resultsElement.offsetHeight + '';
        setTimeout("followResultsElement('"+resultsID+"');", 500);
    }
    else
    {
        if (helpShimSet)
        {
            helpShim.style.display = 'none';
        }
    }
}

/**
 * Makes sure the value in the data field is a valid entry. If not it reverts
 * the entry.
 *
 * @return void
 */
function suggestListVerify()
{
    /* Get elements. */
    var textInput      = document.getElementById(textInputID);
    var resultsElement = document.getElementById(resultsElementID);
    var IDElement      = document.getElementById(IDElementID);

    /* Set trimmedTextInputValue to the contents of the input box without
     * leading or trailing whitespace.
     */
    var trimmedTextInputValue = trim(textInput.value);

    /* If there is no text in the input box, hide the results list and flag the
     * ID as a bad value.
     */
    if (trimmedTextInputValue == '')
    {
        resultsElement.style.display = 'none';
        IDElement.value = -1;
    }

    /* If only one item was returned by the AJAX call and the value in the
     * textbox is the value from the AJAX call, use the text box value and
     * hide the results element, etc.
     */
    if (dataNodes && dataNodes.length == 1)
    {
        var firstDataNodeNameValue = urlDecode(dataNodes[0].getElementsByTagName(
            'name'
        ).item(0).firstChild.nodeValue);
        var firstDataNodeIDValue = dataNodes[0].getElementsByTagName(
            'id'
        ).item(0).firstChild.nodeValue;

        /* Are they identical? If so, set the input box to the trimmed item
         * name, hide the results element, etc.
         */
        if (trim(firstDataNodeNameValue.toUpperCase()) == trim(textInput.value.toUpperCase()))
        {
            resultsElement.style.display = 'none';
            textInput.value = trim(firstDataNodeNameValue);
            IDElement.value = trim(firstDataNodeIDValue);

            dataValidInput = true;
        }
    }

    /* The value is a value provided by the up/down list. */
    if (dataNodes && selectedIndex >= 0)
    {
        var selectedDataNodeNameValue = urlDecode(dataNodes[selectedIndex].getElementsByTagName(
            'name'
        ).item(0).firstChild.nodeValue);
        var selectedDataNodeIDValue = dataNodes[selectedIndex].getElementsByTagName(
            'id'
        ).item(0).firstChild.nodeValue;

        if (trim(selectedDataNodeNameValue).toUpperCase() == trim(textInput.value.toUpperCase()))
        {
            resultsElement.style.display = 'none';
            IDElement.value = trim(selectedDataNodeIDValue);
            dataValidInput = true;
        }
    }
}

/**
 * This function is called whenever a keyup event is received for the input
 * textbox.
 *
 * @return boolean true, or false to stop processing events
 */
function parseKeyUp(evt)
{
    var upDownEnterPressed = false;

    /* Get elements. */
    var textInput      = document.getElementById(textInputID);
    var resultsElement = document.getElementById(resultsElementID);
    var IDElement      = document.getElementById(IDElementID);

    /* Browser compatability. */
    if (!evt)
    {
        evt = window.event;
    }

    /* Selected "drop-down item". */
    var suggestListItemDiv;

    if (typeof(evt.keyCode) == 'number')
    {
        /* Up arrow key or down arrow key was pressed, and selectedIndex != -1. */
        if (evt.keyCode == 38 || evt.keyCode == 40 && selectedIndex != -1)
        {
            suggestListItemDiv = document.getElementById('suggest' + selectedIndex);

            /* Remove any previous highlighting. */
            suggestListItemDiv.className = suggestListItemDiv.className.replace(
                highlightClass, ''
            );
        }

        /* Up arrow key was pressed. */
        if (evt.keyCode == 40)
        {
            upDownEnterPressed = true;

            if (selectedIndex == (dataNodes.length - 1) && moreResults == true)
            {
                /* We have keyed down to more results; load them. */
                suggestListPopulate(
                    focusID, sessionCookie, lastLookup, maxTotalResults, selectedIndex + 1
                );
            }
            else if (selectedIndex < dataNodes.length-1)
            {
                /* Just scrolling down... */
                selectedIndex++;
            }
        }

        /* Down arrow key was pressed. */
        if (evt.keyCode == 38)
        {
            upDownEnterPressed = true;

            /* Just scrolling up... */
            if (selectedIndex > 0)
            {
                selectedIndex--;
            }
        }

        /* Up arrow key or down arrow key was pressed, and selectedIndex != -1. */
        if (evt.keyCode == 38 || evt.keyCode == 40 && selectedIndex != -1)
        {
            suggestListItemDiv = document.getElementById('suggest' + selectedIndex);

            var selectedDataNodeNameValue = dataNodes[selectedIndex].getElementsByTagName(
                'name'
            ).item(0).firstChild.nodeValue;

            /* Apply new formatting and place select entry in the textbox. */
            suggestListItemDiv.className += highlightClass;
            textInput.value = urlDecode(trim(selectedDataNodeNameValue));
        }

        /* Enter key or tab key was pressed. */
        if (evt.keyCode == 13 || evt.keyCode == 48)
        {
            upDownEnterPressed = true;
            suggestListVerify();
        }
    }

    /* Event was not handled above... */
    if (upDownEnterPressed == false)
    {
        dataValidInput = false;

        /* Call the function that populates the "drop-down list". */
        suggestListPopulate(
            focusID, sessionCookie, textInput.value, maxInitialResults, -1
        );
    }
}

/**
 * This function is called whenever a keydown event is received for the input
 * textbox.
 *
 * @return boolean true, or false to stop processing events
 */
function parseKeyDown(evt)
{
    /* Browser compatability. */
    if (!evt)
    {
        evt = window.event;
    }

    if (typeof(evt.keyCode) == 'number')
    {
        /* Intercept keydown enter, and prevent form submission by returning
         * false.
         */
        if (evt.keyCode == 13)
        {
            return false;
        }
    }
}
