/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

function checkDate(form)
{
    var errorMessage = "";

    startMonth = document.getElementById("startMonth").value;
    startDay = document.getElementById("startDay").value;
    startYear = document.getElementById("startYear").value;

    startMonth--;

    endMonth = document.getElementById("endMonth").value;
    endDay = document.getElementById("endDay").value;
    endYear = document.getElementById("endYear").value;

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
