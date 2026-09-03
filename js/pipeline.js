/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

var _sortBy;
var _sortDirection;

function PipelineDetails_populate(candidateJobOrderID, htmlObjectID, sessionCookie)
{
    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = "&candidateJobOrderID=" + urlEncode(candidateJobOrderID);

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        document.getElementById(htmlObjectID).innerHTML = http.responseText;
    }

    AJAX_callCATSFunction(
        http,
        "getPipelineDetails",
        POSTData,
        callBack,
        0,
        sessionCookie,
        false,
        false
    );
}

function PipelineJobOrder_setLimitDefaultVars(sortBy, sortDirection)
{
    _sortBy = sortBy;
    _sortDirection = sortDirection;
}

function PipelineJobOrder_changeLimit(joborderID, entriesPerPage, isPopup, htmlObjectID, sessionCookie, indicatorID, indexFile)
{
    PipelineJobOrder_populate(joborderID, 0, entriesPerPage, _sortBy, _sortDirection, isPopup, htmlObjectID, sessionCookie, indicatorID, indexFile)
}

function PipelineJobOrder_populate(joborderID, page, entriesPerPage, sortBy,
    sortDirection, isPopup, htmlObjectID, sessionCookie, indicatorID, indexFile)
{
    var http = AJAX_getXMLHttpObject();

    /* Build HTTP POST data. */
    var POSTData = "&joborderID=" + joborderID;
    POSTData += "&page=" + page;
    POSTData += "&entriesPerPage=" + entriesPerPage;
    POSTData += "&sortBy=" + urlEncode(sortBy);
    POSTData += "&sortDirection=" + urlEncode(sortDirection);
    POSTData += "&indexFile=" + urlEncode(indexFile);
    POSTData += "&isPopup=" + urlEncode(isPopup);

    document.getElementById(indicatorID).style.display = "";

    /* Anonymous callback function triggered when HTTP response is received. */
    var callBack = function ()
    {
        if (http.readyState != 4)
        {
            return;
        }

        document.getElementById(indicatorID).style.display = "none";

        document.getElementById(htmlObjectID).innerHTML = http.responseText;

        execJS(http.responseText);
    }

    AJAX_callCATSFunction(
        http,
        "getPipelineJobOrder",
        POSTData,
        callBack,
        55000,
        sessionCookie,
        false,
        false
    );
}


function selectAll_candidates(el){
	var pipeline=document.getElementsByName("checked");
	for(var i=0;i<pipeline.length;i++){
		pipeline[i].checked=el.checked;
	}
}

function getSelected_candidates(){
	var exportArray=[];
	var pipeline=document.getElementsByName("checked");

	for(var i=0;i<pipeline.length;i++){
		if (pipeline[i].checked){
			exportArray.push(pipeline[i].value);
		}
	}
	return exportArray;
}
