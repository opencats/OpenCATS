/*
 * CATS
 * Activity JavaScript Library
 *
 * Portions Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
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
 * $Id: import.js 3700 2007-11-26 22:59:23Z brian $
 */

/* Activity entry type flags. These should match up with the flags
 * from ActivityEntries.php.
 */

var dataTypeList = [];
var totalFilesTried = 0;

function evauluateImportDataType()
{
    for (var i = 0; i < dataTypeList.length; i += 2)
    {
        if (document.getElementById('dataType').value == dataTypeList[i])
        {
            document.getElementById(dataTypeList[i+1]+'td1').style.display = '';
            document.getElementById(dataTypeList[i+1]+'td2').style.display = '';
        }
        else
        {
            document.getElementById(dataTypeList[i+1]+'td1').style.display = 'none';
            document.getElementById(dataTypeList[i+1]+'td2').style.display = 'none';
        }
    }
}

function evaluateFieldSelection(theID)
{
    if (document.getElementById('importType'+theID).value == 'cats')
    {
        document.getElementById('importIntoSpan'+theID).style.display = '';
    }
    else
    {
        document.getElementById('importIntoSpan'+theID).style.display = 'none';
    }
}

function showSampleData(theID)
{
    document.getElementById('importSample' + theID).style.display = '';
}


function hideSampleData(theID)
{
    document.getElementById('importSample' + theID).style.display = 'none';
}

function registerImportDataType(theName, theDiv)
{
    dataTypeList = dataTypeList.concat(theName, theDiv);
}

function checkField(numberOfFields, FieldName, theMessage)
{
    for (var i = 0; i < numberOfFields; i++)
    {
        if (document.getElementById('importType'+i).value == 'cats' &&
           document.getElementById('importIntoField'+i).value == FieldName)
        {
            return true;        }

    }
    alert(theMessage);
    return false;

}

function showLoading()
{
    for (var i = 0; i < 2; i++)
    {
        if (document.getElementById('importShow'+i))
        {
            document.getElementById('importShow'+i).style.display = '';
        }
    }

    for (var i = 0; i < 11; i++)
    {
        if (document.getElementById('importHide'+i))
        {
            document.getElementById('importHide'+i).style.display = 'none';
        }
    }
    return true;
}

function showErrorId(theID)
{
    document.getElementById('errorId'+theID).style.display = '';
    document.getElementById('errorMinus'+theID).style.display = '';
    document.getElementById('errorPlus'+theID).style.display = 'none';
}

function hideErrorId(theID)
{
    document.getElementById('errorId'+theID).style.display = 'none';
    document.getElementById('errorMinus'+theID).style.display = 'none';
    document.getElementById('errorPlus'+theID).style.display = '';
}

function evaluateUnnamedContacts()
{
    if (document.getElementById('generateCompanies').value == 'yes')
    {
        document.getElementById('unnamedContactsSpan').style.display = '';
    }
    else
    {
        document.getElementById('unnamedContactsSpan').style.display = 'none';
    }
}

var numberOfImports = 0;
var numberOfDuplicates = 0;

function startMassImport()
{
	abortImport = false;
    if (confirm("Are you sure you want to import the files?  The source files will be deleted!"))
    {
        document.getElementById('startImport').style.display='none'; 
        document.getElementById('pleaseWaitImport').style.display=''; 
        document.getElementById('back').style.display='none';
        document.getElementById('foundFilesSpan').style.display='none';
        totalFilesTried = 0;
        numberOfImports = 0; 
        numberOfDuplicates = 0; 
        importFile();
    }
}

function importFile()
{
    /* Update Progress bar. */
    setProgress(totalFilesTried / totalFiles);
    document.getElementById('processingResumeNumber').innerHTML = totalFilesTried;

    /* Set up AJAX. */
    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = '';

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        var result = http.responseText;
        
        if (result != 'done')
        {
			var args = result.split(',');
			
			if (!args || args.length != 3)
			{
			    alert('Error: Invalid response from server: ' + result);
            	finishImportNotice('pleaseWaitImport', totalFilesTried);
			    return;
		    }
			
			numberOfDuplicates += (args[0] * 1);
			numberOfImports += (args[1] * 1);
			totalFilesTried += (args[2] * 1);

            if (isNaN(numberOfDuplicates) || isNaN(numberOfImports) || isNaN(totalFilesTried))
            {
			    alert('Error: Invalid response from server: ' + result);
            	finishImportNotice('pleaseWaitImport', totalFilesTried);
			    return;
            }
            
			if (abortImport == false)
			{
				setTimeout("importFile();", 500);
			}
			else
			{
            	finishImportNotice('pleaseWaitImport', totalFilesTried);
            	alert("Import interupted.");				
			}
        }
        else
        {
            finishImportNotice('pleaseWaitImport', totalFilesTried);
            alert("Import complete!");
        }
    }

    AJAX_callCATSFunction(
        http,
        'import:processMassImportItem',
        POSTData,
        callBack,
        10000000,
        null,
        false
    );
}

function finishImportNotice(importStatusID, totalPossibleResumes)
{
    importStatus = document.getElementById(importStatusID);
    
    var html = '<br /><br /><span style="font-weight:bold;">Resume import complete!<br />' +
               '<br />' +
               'Out of '+totalPossibleResumes+' possible resumes, '+numberOfImports+
               ' new resumes were added';
    
    if (numberOfDuplicates > 0)
    {
        html += ' and '+numberOfDuplicates+' already exsisted in the system';
    }
    
    html += '.</span>';
    
    importStatus.innerHTML = html;
                             
    document.getElementById('back').style.display='';
}

function setProgress(theProgress)
{
    theProgress = Math.round(theProgress * 100);
    document.getElementById('progressBar').style.display = '';

    if (theProgress > 100)
    {
        return;
    }

    if (theProgress > 12)
    {
        document.getElementById('d1').innerHTML = parseInt(theProgress) + '%';
    }
    else
    {
        document.getElementById('d1').innerHTML = '';
    }

    if (theProgress > 0)
    {
        document.getElementById('d2').style.width = (theProgress * 3) + 'px';
    }
}
