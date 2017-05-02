/*
 * CATS
 * Search Advanced JavaScript Library
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
 * $Id: export.js 2356 2007-04-20 17:18:14Z brian $
 */

//FIXME: Clean up!


function docjslib_getRealLeftExport(imgElem)
{
    xPos = eval(imgElem).offsetLeft;
    tempEl = eval(imgElem).offsetParent;
    while (tempEl != null)
    {
        xPos += tempEl.offsetLeft;
        tempEl = tempEl.offsetParent;
    }
    return xPos;
}

function showBox(boxID)
{
    var box = document.getElementById(boxID);

    box.style.left = docjslib_getRealLeftExport(
        document.getElementById('exportBoxLink')
    ) + 'px';
    box.style.display = 'block';
}

function hideBox(boxID)
{
    document.getElementById(boxID).style.display = 'none';
}

function toggleChecksAll()
{
    var num_elements = document.selectedObjects.length;

    for (var i = 1 ; i < num_elements ; i++)
    {
        e = document.selectedObjects.elements[i];
        if (document.selectAll.allBox.checked == true)
        {
            if (e.type == 'checkbox')
            {
                e.checked = true;
            }
        }
        else
        {
            e.checked = false;
        }
    }
}

function checkSelected()
{
    var num_elements = document.selectedObjects.length;
    var check = false;

    for (var i = 1 ; i < num_elements ; i++)
    {
        e = document.selectedObjects.elements[i];
        if (e.checked == true)
        {
            check = true;
        }
    }

    if (check == true)
    {
        document.selectedObjects.submit();
    }
    else
    {
        alert("Form Error: You must select at least one item for export.");
    }
}
