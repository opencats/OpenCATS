/*
 * CATS
 * Activity JavaScript Library
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
 * $Id: activity.js 3423 2007-11-06 18:33:44Z brian $
 */

/* Activity entry type flags. These should match up with the flags
 * from ActivityEntries.php.
 */
ACTIVITY_CALL        = 100;
ACTIVITY_EMAIL       = 200;
ACTIVITY_MEETING     = 300;
ACTIVITY_OTHER       = 400;
ACTIVITY_CALL_TALKED = 500;
ACTIVITY_CALL_LVM    = 600;
ACTIVITY_CALL_MISSED = 700;

function Activity_fillTypeSelect(selectList, selectedText)
{
    var optionElements = new Array();

    /* Call option. */
    optionElements[0] = document.createElement('option');
    optionElements[0].value = ACTIVITY_CALL;
    optionElements[0].appendChild(document.createTextNode('Call'));

    /* Call (Talked) option. */
    optionElements[1] = document.createElement('option');
    optionElements[1].value = ACTIVITY_CALL_TALKED;
    optionElements[1].appendChild(document.createTextNode('Call (Talked)'));

    /* Call (LVM) option. */
    optionElements[2] = document.createElement('option');
    optionElements[2].value = ACTIVITY_CALL_LVM;
    optionElements[2].appendChild(document.createTextNode('Call (LVM)'));

    /* Call (Missed) option. */
    optionElements[3] = document.createElement('option');
    optionElements[3].value = ACTIVITY_CALL_MISSED;
    optionElements[3].appendChild(document.createTextNode('Call (Missed)'));

    /* Email option. */
    optionElements[4] = document.createElement('option');
    optionElements[4].value = ACTIVITY_EMAIL;
    optionElements[4].appendChild(document.createTextNode('E-Mail'));

    /* Meeting option. */
    optionElements[5] = document.createElement('option');
    optionElements[5].value = ACTIVITY_MEETING;
    optionElements[5].appendChild(document.createTextNode('Meeting'));

    /* Other option. */
    optionElements[6] = document.createElement('option');
    optionElements[6].value = ACTIVITY_OTHER;
    optionElements[6].appendChild(document.createTextNode('Other'));

    /* Select the correct option. */
    if (selectedText)
    {
        if (selectedText == 'Call')
        {
            optionElements[0].setAttribute('selected', 'selected');
        }
        else if (selectedText == 'Call (Talked)')
        {
            optionElements[1].setAttribute('selected', 'selected');
        }
        else if (selectedText == 'Call (LVM)')
        {
            optionElements[2].setAttribute('selected', 'selected');
        }
        else if (selectedText == 'Call (Missed)')
        {
            optionElements[3].setAttribute('selected', 'selected');
        }
        else if (selectedText == 'E-Mail')
        {
            optionElements[4].setAttribute('selected', 'selected');
        }
        else if (selectedText == 'Meeting')
        {
            optionElements[5].setAttribute('selected', 'selected');
        }
        else if (selectedText == 'Other')
        {
            optionElements[6].setAttribute('selected', 'selected');
        }
    }

    /* Append options to select list. */
    for (var i = 0; i < optionElements.length; i++)
    {
        selectList.appendChild(optionElements[i]);
    }
}

function Activity_fillRegardingSelect(selectList, jobOrderNodes, selectedText)
{
    /* General option. */
    generalOption = document.createElement('option');
    generalOption.value = 'NULL';
    generalOption.appendChild(document.createTextNode('General'));
    if (selectedText == 'General')
    {
        generalOption.setAttribute('selected', 'selected');
    }
    selectList.appendChild(generalOption);

    /* Loop through all of the <joborder> nodes. */
    for (var i = 0; i < jobOrderNodes.length; i++)
    {
        var IDNode          = jobOrderNodes[i].getElementsByTagName('id').item(0);
        var titleNode       = jobOrderNodes[i].getElementsByTagName('title').item(0);
        var companyNameNode = jobOrderNodes[i].getElementsByTagName('companyname').item(0);
        var assignedNode    = jobOrderNodes[i].getElementsByTagName('assigned').item(0);

        if (!IDNode.firstChild || !titleNode.firstChild ||
            !companyNameNode.firstChild || !assignedNode.firstChild)
        {
            continue;
        }

        var option = document.createElement('option');
        var optionText = titleNode.firstChild.nodeValue + ' (' + companyNameNode.firstChild.nodeValue + ')';

        /* Append a '*' for assigned job orders. */
        if (assignedNode.firstChild.nodeValue == '1')
        {
            optionText += ' (*)';
        }

        option.value = IDNode.firstChild.nodeValue;
        option.appendChild(document.createTextNode(optionText));
        if (selectedText == optionText)
        {
            option.setAttribute('selected', 'selected');
        }
        selectList.appendChild(option);
    }
}

function Activity_editEntry(activityID, dataItemID, dataItemType, sessionCookie)
{
    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '';
    POSTData += '&dataItemID='   + dataItemID;
    POSTData += '&dataItemType=' + dataItemType;

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

        //alert(http.responseText);

        /* Return if we have any errors. */
        var errorCodeNode    = http.responseXML.getElementsByTagName('errorcode').item(0);
        var errorMessageNode = http.responseXML.getElementsByTagName('errormessage').item(0);
        if (!errorCodeNode.firstChild || (errorCodeNode.firstChild.nodeValue != '0' &&
            errorCodeNode.firstChild.nodeValue != '-2'))
        {
            if (errorMessageNode.firstChild)
            {
                var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                                 + errorMessageNode.firstChild.nodeValue;
                alert(errorMessage);
            }

            return;
        }

        /* Grab references to TDs we need information from. */
        var typeTD      = document.getElementById('activityType' + activityID);
        var regardingTD = document.getElementById('activityRegarding' + activityID);
        var notesTD     = document.getElementById('activityNotes' + activityID);
        var dateTD      = document.getElementById('activityDate' + activityID);

        /* Find the TR we want to swap out and create a deep clone of it. */
        var editRow    = typeTD.parentNode;
        var oldEditRow = editRow.cloneNode(true);
        var newEditRow = document.createElement('tr');

        /* Find and disable the activity entry's action icons. */
        var editAction = document.getElementById('editActivity' + activityID);
        var deleteAction = document.getElementById('deleteActivity' + activityID);
        //editAction.style.visibility = 'hidden';
        //deleteAction.style.visibility = 'hidden';
        /* FIXME: Spinner? */

        /* Create the cell that will contain the edit form. */
        var editTD = document.createElement('td');
        editTD.setAttribute('colspan', '6');
        editTD.setAttribute('valign', 'top');
        editTD.setAttribute('align',  'left');

        /* Create the "Type" select list and add options to it. */
        var typeSelectList = document.createElement('select');
        Activity_fillTypeSelect(typeSelectList, typeTD.firstChild.nodeValue);
        typeSelectList.className = 'inputbox';

        /* Create the "Regarding" select list and add options to it. */
        var regardingSelectList = document.createElement('select');
        Activity_fillRegardingSelect(
            regardingSelectList,
            http.responseXML.getElementsByTagName('joborder'),
            regardingTD.firstChild.nodeValue
        );
        regardingSelectList.className = 'inputbox';

        /* Create the "Notes" TEXTAREA and fill it with the text from the cell,
         * as long as that text isn't "(No Notes)".
         */
        var notesTextArea = document.createElement('textarea');
        notesTextArea.setAttribute('cols', '60');
        notesTextArea.className = 'inputbox';

        /* The .replace regex strips HTML. */
        if (notesTD.firstChild && notesTD.innerHTML != '(No Notes)')
        {
            notesTextArea.appendChild(
                document.createTextNode(
                    unEscapeHTML(notesTD.innerHTML.replace(/(<([^>]+)>)/ig,""))
                )
            );
        }

        /* Create a submit button. */
        var submitButton = document.createElement('input');
        submitButton.setAttribute('type', 'submit');
        submitButton.setAttribute('value', 'Submit');
        submitButton.className = 'input-button';

        /* Create a cancel button. */
        var cancelButton = document.createElement('input');
        cancelButton.setAttribute('type', 'button');
        cancelButton.setAttribute('value', 'Cancel');
        cancelButton.className = 'input-button';

        /* Date editor. */
        var dateSpan = document.createElement('span');
        var dateAndTime = unEscapeHTML(dateTD.innerHTML.replace(/(<([^>]+)>)/ig,""));
        dateSpan.innerHTML = DateInputForDOM('dateEditActivity' + activityID, true, 'MM-DD-YY', dateAndTime.substr(0,dateAndTime.indexOf(' ')), -1);

        var timeString = dateAndTime.substr(dateAndTime.indexOf(' ')+2);
        var hourString = timeString.substr(0,timeString.indexOf(':'));
        var timeString = timeString.substr(timeString.indexOf(':')+1);
        var minuteString = timeString.substr(0,timeString.indexOf(' '));
        var timeString = timeString.substr(timeString.indexOf(' ')+1);
        var amPmString = timeString.substr(0,timeString.indexOf(')'));
        
        /* Time editor. */
        var hourSelect = document.createElement('select');
        hourSelect.setAttribute('id', 'hourEditActivity' + activityID);
        for (var i = 1; i<= 12; ++i)
        {
            var hourSelectOption = document.createElement('option');
            hourSelectOption.value = i;
            hourSelectOption.innerHTML = i;
            if (hourString * 1 == i)
            {
                hourSelectOption.selected = true;
            }
            hourSelect.appendChild(hourSelectOption);
        }
        
        var minuteSelect = document.createElement('select');
        minuteSelect.setAttribute('id', 'minuteEditActivity' + activityID);
        for (var i = 0; i<= 59; ++i)
        {
            var minuteSelectOption = document.createElement('option');
            minuteSelectOption.value = i;
            minuteSelectOption.innerHTML = i;
            if (minuteString * 1 == i)
            {
                minuteSelectOption.selected = true;
            }
            minuteSelect.appendChild(minuteSelectOption);
        }
        
        var AMPMSelect = document.createElement('select');
        AMPMSelect.setAttribute('id', 'ampmEditActivity' + activityID);
        
        var AMPMSelectOptionAM = document.createElement('option');
        AMPMSelectOptionAM.value = 'AM';
        AMPMSelectOptionAM.innerHTML = 'AM';
        if (amPmString == 'AM')
        {
            AMPMSelectOptionAM.selected = true;
        }
        AMPMSelect.appendChild(AMPMSelectOptionAM);
        
        var AMPMSelectOptionPM = document.createElement('option');
        AMPMSelectOptionPM.value = 'PM';
        AMPMSelectOptionPM.innerHTML = 'PM';
        if (amPmString == 'PM')
        {
            AMPMSelectOptionPM.selected = true;
        }
        AMPMSelect.appendChild(AMPMSelectOptionPM);
        
        var dateTimeTable = document.createElement('table');
        var dateTimeTableTr = document.createElement('tr');
        var dateTimeTableTdLeft = document.createElement('td');
        var dateTimeTableTdRight = document.createElement('td');
        
        dateTimeTableTdLeft.appendChild(dateSpan);    
        dateTimeTableTdRight.appendChild(hourSelect);    
        dateTimeTableTdRight.appendChild(minuteSelect); 
        dateTimeTableTdRight.appendChild(AMPMSelect);    
        dateTimeTableTr.appendChild(dateTimeTableTdLeft);
        dateTimeTableTr.appendChild(dateTimeTableTdRight);
        dateTimeTable.appendChild(dateTimeTableTr);
        
        editTD.appendChild(dateTimeTable);
        editTD.appendChild(typeSelectList);
        editTD.appendChild(document.createTextNode("\u00a0"));
        editTD.appendChild(regardingSelectList);
        editTD.appendChild(document.createElement('br'));
        editTD.appendChild(notesTextArea);
        editTD.appendChild(document.createElement('br'));
        editTD.appendChild(submitButton);
        editTD.appendChild(document.createTextNode("\u00a0"));
        editTD.appendChild(cancelButton);
        newEditRow.appendChild(editTD);

        /* Swap the old row for the new one. */
        editRow.parentNode.replaceChild(newEditRow, editRow);

        /* Add action events. */
        submitButton.onclick = function()
        {
            /* Submit the edited entry back to the server on submit. */
            Activity_submitEditedEntry(
                notesTextArea.value,
                document.getElementById('dateEditActivity' + activityID).value,
                document.getElementById('hourEditActivity' + activityID).value,
                document.getElementById('minuteEditActivity' + activityID).value,
                document.getElementById('ampmEditActivity' + activityID).value,
                oldEditRow,
                newEditRow,
                activityID,
                editAction,
                deleteAction,
                typeSelectList[typeSelectList.selectedIndex].value,
                regardingSelectList[regardingSelectList.selectedIndex].value,
                sessionCookie
            );
            return true;
        }
        cancelButton.onclick = function()
        {
            /* Swap the old row back in on cancel. */
            newEditRow.parentNode.replaceChild(oldEditRow, newEditRow);

            /* Re-enable the activity entry's action icons. */
            editAction.style.visibility = 'visible';
            deleteAction.style.visibility = 'visible';
            return true;
        }

        /* Focus on the new text area. */
        notesTextArea.focus();
    }

    AJAX_callCATSFunction(
        http,
        'getDataItemJobOrders',
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
}

/**
 * Sends a textarea back to editActivity.php for processing.
 *
 * @return void
 */
function Activity_submitEditedEntry(notes, date, hour, minute, ampm,
    oldEditRow, newEditRow, activityID, editAction, deleteAction,
    selectedType, jobOrderID, sessionCookie)
{
    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '';
    POSTData += '&notes='      + urlEncode(escapeHTML(notes));
    POSTData += '&date='       + urlEncode(date);
    POSTData += '&hour='       + urlEncode(hour);
    POSTData += '&minute='     + urlEncode(minute);
    POSTData += '&ampm='       + urlEncode(ampm);
    POSTData += '&activityID=' + activityID;
    POSTData += '&type='       + selectedType;
    POSTData += '&jobOrderID=' + jobOrderID;

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        //alert(http.responseText);

        /* Swap the old row back in. */
        newEditRow.parentNode.replaceChild(oldEditRow, newEditRow);

        /* Grab references to TDs in which we need to replace information. */
        var typeTD      = document.getElementById('activityType' + activityID);
        var regardingTD = document.getElementById('activityRegarding' + activityID);
        var notesTD     = document.getElementById('activityNotes' + activityID);
        var dateTD     = document.getElementById('activityDate' + activityID);
        
        /* Return if we have any errors. */
        var errorCodeNode    = http.responseXML.getElementsByTagName('errorcode').item(0);
        var errorMessageNode = http.responseXML.getElementsByTagName('errormessage').item(0);
        if (!errorCodeNode.firstChild || errorCodeNode.firstChild.nodeValue != '0')
        {
            if (errorMessageNode.firstChild)
            {
                var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                                 + errorMessageNode.firstChild.nodeValue;
                alert(errorMessage);
            }

            return;
        }

        var typeNode      = http.responseXML.getElementsByTagName('typedescription').item(0);
        var notesNode     = http.responseXML.getElementsByTagName('notes').item(0);
        var regardingNode = http.responseXML.getElementsByTagName('regarding').item(0);
        var dateNode      = http.responseXML.getElementsByTagName('date').item(0);

        /* Replace the text inside the "Type" TD. */
        if (typeTD.firstChild && typeNode.firstChild)
        {
            typeTD.firstChild.nodeValue = typeNode.firstChild.nodeValue;
        }

        /* Replace the text inside the "Regarding" TD. */
        if (regardingTD.firstChild && regardingNode.firstChild)
        {
            regardingTD.innerHTML = regardingNode.firstChild.nodeValue;
        }

        /* Replace the text inside the notes text span. */
        if (notesTD.firstChild && notesNode.firstChild)
        {
            notesTD.innerHTML = notesNode.firstChild.nodeValue;
        }
        
        /* Replace the text inside the notes text span. */
        if (dateTD.firstChild && dateNode.firstChild)
        {
            dateTD.innerHTML = dateNode.firstChild.nodeValue;
        }        

        /* Re-enable the activity entry's action icons. */
        editAction.style.visibility = 'visible';
        deleteAction.style.visibility = 'visible';
    }

    AJAX_callCATSFunction(
        http,
        'editActivity',
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
}

function Activity_deleteEntry(activityID, sessionCookie)
{
    if (!confirm('Delete this activity?'))
    {
        return false;
    }

    /* Find and disable the activity entry's action icons. */
    var editAction = document.getElementById('editActivity' + activityID);
    var deleteAction = document.getElementById('deleteActivity' + activityID);
    editAction.style.visibility = 'hidden';
    deleteAction.style.visibility = 'hidden';
    /* FIXME: Spinner? */

    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '&activityID=' + activityID;

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        //alert(http.responseText);

        /* Return if we have any errors. */
        var errorCodeNode    = http.responseXML.getElementsByTagName('errorcode').item(0);
        var errorMessageNode = http.responseXML.getElementsByTagName('errormessage').item(0);
        if (!errorCodeNode.firstChild || errorCodeNode.firstChild.nodeValue != '0')
        {
            if (errorMessageNode.firstChild)
            {
                var errorMessage = "An error occurred while receiving a response from the server.\n\n"
                                 + errorMessageNode.firstChild.nodeValue;
                alert(errorMessage);
            }

            return;
        }

        /* Figure out what row this is. */
        var typeTD  = document.getElementById('activityType' + activityID);
        var typeRow = typeTD.parentNode;

        /* Remove the row. */
        typeRow.parentNode.removeChild(typeRow);
    }

    AJAX_callCATSFunction(
        http,
        'deleteActivity',
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
}

//FIXME: Document me.
function AS_onRegardingChange(statusesArray, jobOrdersArray, regardingSelectID,
    statusSelectID, statusRowID, sendEmailRowID, sendEmailCheckboxID,
    sendEmailSpanID, changeStatusID, changeStatusSpanAID, changeStatusSpanBID)
{
    var regardingSelectList = document.getElementById(regardingSelectID);
    var statusSelectList = document.getElementById(statusSelectID);
    var statusRow = document.getElementById(statusRowID);
    var sendEmailRow = document.getElementById(sendEmailRowID);
    var sendEmailCheckbox = document.getElementById(sendEmailCheckboxID);
    var sendEmailSpan = document.getElementById(sendEmailSpanID);
    var changeStatus = document.getElementById(changeStatusID);
    var changeStatusSpanA = document.getElementById(changeStatusSpanAID);
    var changeStatusSpanB = document.getElementById(changeStatusSpanBID);

    var regardingID = regardingSelectList[
        regardingSelectList.selectedIndex
    ].value;

    if (regardingID != '-1')
    {
        changeStatus.disabled = false;
        changeStatus.checked = false;
        statusSelectList.disabled = true;
        statusSelectList[0].selected = true;
        sendEmailRow.style.display = 'none';
        sendEmailCheckbox.checked = false;
        sendEmailSpan.style.display = 'none';
        changeStatusSpanA.style.color = '#000';
        changeStatusSpanB.style.color = '#aaa';

        var statusIndex = findValueInArray(jobOrdersArray, regardingID);

        /* This shouldn't happen, but, just in case... */
        if (statusIndex == -1)
        {
            return;
        }

        statusSelectIndex = findValueInSelectList(
            statusSelectList,
            statusesArray[statusIndex]
        );

        /* This shouldn't happen, but, just in case... */
        if (statusSelectIndex == -1)
        {
            return;
        }

        statusSelectList[statusSelectIndex].selected = true;
    }
    else
    {
        changeStatusSpanA.style.color = '#aaa';
        changeStatusSpanB.style.color = '#aaa';
        statusSelectList[0].selected = true;
        changeStatus.checked = false;
        changeStatus.disabled = true;
        statusSelectList.disabled = true;
        sendEmailRow.style.display = 'none';
        sendEmailCheckbox.checked = false;
        sendEmailSpan.style.display = 'none';
    }
}

//FIXME: Document me.
function AS_onStatusChange(statusesArray, jobOrdersArray, regardingSelectID,
    statusSelectID, sendEmailRowID, sendEmailSpanID, activityEntryID,
    activityTypeID, regardingIDOverride, emailTextID, emailTextOrigionalID,
    triggerEmailID, statusesArrayString, jobOrdersArrayStringTitle,
    jobOrdersArrayStringCompany, statusTriggersEmailArray, emailIsDisabledID)
{
    var regardingSelectList = document.getElementById(regardingSelectID);
    var statusSelectList = document.getElementById(statusSelectID);
    var activityEntry = document.getElementById(activityEntryID);
    var activityType = document.getElementById(activityTypeID);
    var sendEmailSpan = document.getElementById(sendEmailSpanID);
    var emailText = document.getElementById(emailTextID);
    var emailTextOrigional = document.getElementById(emailTextOrigionalID);
    var triggerEmail = document.getElementById(triggerEmailID);
    var emailIsDisabled = document.getElementById(emailIsDisabledID);

    if (regardingIDOverride == null)
    {
        var regardingID = regardingSelectList[
            regardingSelectList.selectedIndex
        ].value;
    }
    else
    {
        var regardingID = regardingIDOverride;
    }

    if (statusSelectList[statusSelectList.selectedIndex].value != '-1' &&
        regardingID != '-1')
    {
        /* Find the jobOrdersArray index of the selected Job Order. This index
         * is the same index as the job in statusesArray.
         */
        var statusIndex = findValueInArray(jobOrdersArray, regardingID);

        /* This shouldn't happen, but, just in case... */
        if (statusIndex == -1)
        {
            sendEmailSpan.style.display = 'inline';
            return;
        }

        /* If the selected status is the same as the candidate's current
         * status, no notification e-mails.
         */
        if (statusesArray[statusIndex] == statusSelectList[statusSelectList.selectedIndex].value)
        {
            sendEmailSpan.style.display = 'none';
            triggerEmail.checked = false;
        }
        else
        {
            if (statusTriggersEmailArray[statusSelectList.selectedIndex-1] == 1 && emailIsDisabled.value == "0")
            {
                sendEmailSpan.style.display = 'inline';
                triggerEmail.checked = true;
            }
            else
            {
                sendEmailSpan.style.display = 'inline';
                triggerEmail.checked = false;
            }
            AS_onSendEmailChange('triggerEmail', 'sendEmailCheckTR', 'visibleTR');
            AS_onChangeStatusChangeGenerateEmail(
                emailText,
                emailTextOrigional,
                statusSelectList[statusSelectList.selectedIndex].text,
                statusesArrayString[statusIndex],
                jobOrdersArrayStringTitle[statusIndex],
                jobOrdersArrayStringCompany[statusIndex]
            );
            if (activityEntry.value == '' || activityEntry.value.indexOf('Status change: ') != -1)
            {
                activityEntry.value = 'Status change: ' +
                    statusSelectList[statusSelectList.selectedIndex].text;
            }
        }
    }
}

function replaceAll(templateString, findString, replaceString)
{
    var idx = templateString.indexOf(findString);
    while (idx > -1)
    {
        templateString = templateString.replace(findString, replaceString);
        idx = templateString.indexOf(findString);
    }

    return templateString;
}

//FIXME: Document me.
function AS_onChangeStatusChangeGenerateEmail(emailText, emailTextOrigional,
    statusString, prevStatusString, jobOrderTitle, jobOrderCompany)
{
    var templateString = emailTextOrigional.value;

    templateString = replaceAll(templateString, "%CANDSTATUS%", statusString);
    templateString = replaceAll(templateString, "%CANDPREVSTATUS%", prevStatusString);
    templateString = replaceAll(templateString, "%JBODTITLE%", jobOrderTitle);
    templateString = replaceAll(templateString, "%JBODCLIENT%", jobOrderCompany);

    emailText.value = templateString;
}

//FIXME: Document me.
function AS_onChangeStatusChange(changeStatusCheckboxID, statusSelectID,
    changeStatusSpanBID)
{
    var changeStatusCheckbox = document.getElementById(changeStatusCheckboxID);
    var statusSelect = document.getElementById(statusSelectID);
    var changeStatusSpanB = document.getElementById(changeStatusSpanBID);

    if (changeStatusCheckbox.checked)
    {
        statusSelect.disabled = false;
        changeStatusSpanB.style.color = '#000';
    }
    else
    {
        statusSelect.disabled = true;
        changeStatusSpanB.style.color = '#aaa';
    }
}

//FIXME: Document me.
function AS_onSendEmailChange(triggersEmailCheckboxID, sendEmailRowID, visibleRowID)
{
    var triggersEmailCheckbox = document.getElementById(triggersEmailCheckboxID);
    var sendEmailRow = document.getElementById(sendEmailRowID);
    var visibleRow = document.getElementById(visibleRowID);

    if (triggersEmailCheckbox.checked)
    {
        sendEmailRow.style.display = visibleRow.style.display;
    }
    else
    {
        sendEmailRow.style.display = 'none';
    }
}

//FIXME: Document me.
function AS_onAddActivityChange(addActivityCheckboxID, activityTypeSelectID,
    activityNoteID, spanAID, spanBID)
{
    var addActivityCheckbox = document.getElementById(addActivityCheckboxID);
    var activityTypeSelect = document.getElementById(activityTypeSelectID);
    var activityNote = document.getElementById(activityNoteID);
    var spanA = document.getElementById(spanAID);
    var spanB = document.getElementById(spanBID);

    if (addActivityCheckbox.checked)
    {
        activityTypeSelect.disabled = false;
        activityNote.disabled = false;
        spanA.style.color = '#000';
        spanB.style.color = '#000';
    }
    else
    {
        activityTypeSelect.disabled = true;
        activityNote.disabled = true;
        spanA.style.color = '#aaa';
        spanB.style.color = '#aaa';
    }
}

function AS_onScheduleEventChange(scheduleEventCheckboxID, scheduleEventDivID)
{
    var scheduleEventCheckbox = document.getElementById(scheduleEventCheckboxID);
    var scheduleEventDiv = document.getElementById(scheduleEventDivID);

    if (scheduleEventCheckbox.checked)
    {
        scheduleEventDiv.style.display = 'block';
    }
    else
    {
        scheduleEventDiv.style.display = 'none';
    }
}

function AS_onEventAllDayChange(allDayRadioID)
{
    var allDayRadio = document.getElementById(allDayRadioID);

    if (allDayRadio.checked)
    {
        var disableTime = true;
    }
    else
    {
        var disableTime = false;
    }

    document.getElementById('hour').disabled = disableTime;
    document.getElementById('minute').disabled = disableTime;
    document.getElementById('meridiem').disabled = disableTime;
    document.getElementById('duration').disabled = disableTime;
}
