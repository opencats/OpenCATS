/*
 * CATS
 * Candidates Form Validation
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
 * $Id: validator.js 1479 2007-01-17 00:22:21Z will $
 */

function checkDate(form)
{
    var errorMessage = '';

    startMonth = document.getElementById('startMonth').value;
    startDay = document.getElementById('startDay').value;
    startYear = document.getElementById('startYear').value;

    startMonth--;

    endMonth = document.getElementById('endMonth').value;
    endDay = document.getElementById('endDay').value;
    endYear = document.getElementById('endYear').value;

    endMonth--;

    var startDate = new Date();
    startDate.setDate(startDay);
    startDate.setMonth(startMonth);
    startDate.setFullYear(startYear);

    var endDate = new Date();
    endDate.setDate(endDay);
    endDate.setMonth(endMonth);
    endDate.setFullYear(endYear);

    if (startDate > endDate)
    {
        alert("You must enter a Date that is later\n then the begining search date.");
        return false;
    }
    else
    {
        return true;
    }
}
