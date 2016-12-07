/*
 * CATS
 * Address Parser JavaScript Library
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
 * $Id: addressParser.js 2451 2007-05-10 20:03:10Z brian $
 */

/**
 * Sends a textarea back to ParseAddress.php for parsing.
 *
 * @return void
 */
function AddressParser_parse(editBoxID, mode, indicatorID, arrowButtonID)
{
    var http = AJAX_getXMLHttpObject();

    var editBox     = document.getElementById(editBoxID);
    var arrowButton = document.getElementById(arrowButtonID);
    var indicator   = document.getElementById(indicatorID);

    /* Disable the arrow button and edit box. */
    arrowButton.disabled = true;
    editBox.disabled = true;
    indicator.style.visibility = 'visible';

    /* Build HTTP POST data. */
    var POSTData = '';
    POSTData += '&addressBlock=' + urlEncode(editBox.value);
    POSTData += '&mode=' + urlEncode(mode);

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
            return;
        }
    
        var firstNameField  = document.getElementById('firstName');
        var middleNameField = document.getElementById('middleName');
        var lastNameField   = document.getElementById('lastName');
        var nameField       = document.getElementById('name');
        var addressField    = document.getElementById('address');
        var phoneHomeField  = document.getElementById('phoneHome');
        var phoneCellField  = document.getElementById('phoneCell');
        var phoneWorkField  = document.getElementById('phoneWork');
        var cityField       = document.getElementById('city');
        var stateField      = document.getElementById('state');
        var zipField        = document.getElementById('zip');
        var emailField      = document.getElementById('email1');
        var faxField        = document.getElementById('faxNumber');

        //alert(http.responseText);
        
        /* Return if we have any errors. */
        var errorCodeNode = http.responseXML.getElementsByTagName('errorcode').item(0);
        var errorMessageNode = http.responseXML.getElementsByTagName('errormessage').item(0);
        if (!errorCodeNode || !errorCodeNode.firstChild ||
            errorCodeNode.firstChild.nodeValue != '0')
        {
            if (errorMessageNode && errorMessageNode.firstChild)
            {
                var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                                 + errorMessageNode.firstChild.nodeValue;
                alert(errorMessage);
            }

            if (firstNameField)  firstNameField.value  = '';
            if (middleNameField) middleNameField.value = '';
            if (lastNameField)   lastNameField.value   = '';
            if (nameField)       nameField.value       = '';
            if (addressField)    addressField.value    = '';
            if (phoneHomeField)  phoneHomeField.value  = '';
            if (phoneCellField)  phoneCellField.value  = '';
            if (phoneWorkField)  phoneWorkField.value  = '';
            if (cityField)       cityField.value       = '';
            if (stateField)      stateField.value      = '';
            if (zipField)        zipField.value        = '';
            if (emailField)      emailField.value      = '';
            if (faxField)        faxField.value        = '';

            /* Enable the arrow button and edit box. */
            arrowButton.disabled = false;
            editBox.disabled = false;
            indicator.style.visibility = 'hidden';

            return;
        }

        /* Locate the nodes we need in the XML response data. */
        var cityNode    = http.responseXML.getElementsByTagName('city').item(0);
        var stateNode   = http.responseXML.getElementsByTagName('state').item(0);
        var zipNode     = http.responseXML.getElementsByTagName('zip').item(0);
        var emailNode   = http.responseXML.getElementsByTagName('email').item(0);
        var companyNode = http.responseXML.getElementsByTagName('company').item(0);

        var addressNode        = http.responseXML.getElementsByTagName('address').item(0);
        var addressLineNodes   = addressNode.getElementsByTagName('line');
        var addressLineOneNode = addressLineNodes.item(0);
        var addressLineTwoNode = addressLineNodes.item(1);

        var nameNode       = http.responseXML.getElementsByTagName('name').item(0);
        var firstNameNode  = nameNode.getElementsByTagName('first').item(0);
        var middleNameNode = nameNode.getElementsByTagName('middle').item(0);
        var lastNameNode   = nameNode.getElementsByTagName('last').item(0);

        var phoneNumbersNode = http.responseXML.getElementsByTagName('phonenumbers').item(0);
        var homePhoneNode    = phoneNumbersNode.getElementsByTagName('home').item(0);
        var cellPhoneNode    = phoneNumbersNode.getElementsByTagName('cell').item(0);
        var workPhoneNode    = phoneNumbersNode.getElementsByTagName('work').item(0);
        var faxNode          = phoneNumbersNode.getElementsByTagName('fax').item(0);


        /* Use the data from the XML response to fill the form fields. */
        if (nameField && companyNode.firstChild)
        {
            nameField.value = companyNode.firstChild.nodeValue;
        }
        else if (nameField)
        {
            nameField.value = '';
        }
        
        if (firstNameField && firstNameNode.firstChild)
        {
            firstNameField.value = firstNameNode.firstChild.nodeValue;
        }
        else if (firstNameField)
        {
            firstNameField.value = '';
        }
        
        if (middleNameField && middleNameNode.firstChild)
        {
            middleNameField.value = middleNameNode.firstChild.nodeValue;
        }
        else if (middleNameField)
        {
            middleNameField.value = '';
        }

        if (lastNameField && lastNameNode.firstChild)
        {
            lastNameField.value = lastNameNode.firstChild.nodeValue;
        }
        else if (lastNameField)
        {
            lastNameField.value = '';
        }

        if (addressField)
        {
            if (addressLineOneNode.firstChild)
            {
                addressField.value = addressLineOneNode.firstChild.nodeValue;
                
                if (addressLineTwoNode.firstChild && addressLineOneNode.firstChild != '')
                {
                    addressField.value += "\n" + addressLineTwoNode.firstChild.nodeValue;
                }
            }
            else
            {
                addressField.value = '';
            }
        }

        if (phoneHomeField && homePhoneNode.firstChild)
        {
            phoneHomeField.value = homePhoneNode.firstChild.nodeValue;
        }
        else if (phoneHomeField)
        {
            phoneHomeField.value = '';
        }

        if (phoneCellField && cellPhoneNode.firstChild)
        {
            phoneCellField.value = cellPhoneNode.firstChild.nodeValue;
        }
        else if (phoneCellField)
        {
            phoneCellField.value = '';
        }

        if (phoneWorkField && workPhoneNode.firstChild)
        {
            phoneWorkField.value = workPhoneNode.firstChild.nodeValue;
        }
        else if (phoneWorkField)
        {
            phoneWorkField.value = '';
        }

        if (faxField && faxNode.firstChild)
        {
            faxField.value = faxNode.firstChild.nodeValue;
        }
        else if (faxField)
        {
            faxField.value = '';
        }

        if (cityField && cityNode.firstChild)
        {
            cityField.value = cityNode.firstChild.nodeValue;
        }
        else if (cityField)
        {
            cityField.value = '';
        }

        if (stateField && stateNode.firstChild)
        {
            stateField.value = stateNode.firstChild.nodeValue;
        }
        else if (stateField)
        {
            stateField.value = '';
        }

        if (zipField && zipNode.firstChild)
        {
            zipField.value = zipNode.firstChild.nodeValue;
        }
        else if (zipField)
        {
            zipField.value = '';
        }

        if (emailField && emailNode.firstChild)
        {
            emailField.value = emailNode.firstChild.nodeValue;
            
            /* Check for duplicate candidate records for candidates page. */
            if (typeof(document.getElementById('candidateAlreadyInSystemTable')) != 'undefined')
            {
                checkEmailAlreadyInSystem(emailNode.firstChild.nodeValue);
            }
            
        }
        else if (emailField)
        {
            emailField.value = '';
        }

        /* Enable the arrow button and edit box. */
        arrowButton.disabled = false;
        editBox.disabled = false;
        indicator.style.visibility = 'hidden';
    }

    AJAX_callCATSFunction(
        http,
        'getParsedAddress',
        POSTData,
        callBack,
        0,
        null,
        false,
        false
    );
}
