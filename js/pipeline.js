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

/* Completed requirements use one native reopening modal before pipeline addition. */
(function () {
    'use strict';
    let opening = false;
    const index = typeof CATSIndexName === 'string' ? CATSIndexName : 'index.php';

    function showReopening(root, resume, cancelled) {
        const modal = new bootstrap.Modal(root);
        const form = root.querySelector('form');
        const submit = form.querySelector('[type="submit"]');
        const reset = form.querySelector('[type="reset"]');
        const errorBox = root.querySelector('[data-reopen-error]');
        let saving = false;
        let saved = false;
        root.addEventListener('shown.bs.modal', function () { form.elements.newVacancies.focus(); });
        root.addEventListener('hide.bs.modal', function (event) { if (saving) event.preventDefault(); });
        root.addEventListener('hidden.bs.modal', function () {
            modal.dispose();
            root.remove();
            if (!saved) cancelled();
        }, {once:true});
        form.addEventListener('reset', function () {
            errorBox.classList.add('d-none');
            form.elements.newVacancies.setCustomValidity('');
        });
        form.addEventListener('submit', async function (event) {
            event.preventDefault();
            if (saving || !form.reportValidity()) return;
            saving = true;
            submit.disabled = reset.disabled = true;
            errorBox.classList.add('d-none');
            try {
                const response = await fetch(form.action, {method:'POST', body:new FormData(form), credentials:'same-origin', cache:'no-store'});
                if (!(response.headers.get('content-type') || '').includes('application/json'))
                    throw new Error('Unable to confirm reopening. Reload the Job Order before trying again.');
                const result = await response.json();
                if (!result.success) {
                    if (result.reload) {
                        errorBox.textContent = result.message;
                        errorBox.classList.remove('d-none');
                        // Refresh the authorised current state; do not replay the candidate addition.
                        window.location.replace(index + '?m=joborders&a=show&jobOrderID=' + encodeURIComponent(form.elements.jobOrderID.value) + '&reopenChanged=1');
                        return;
                    }
                    throw new Error(result.message || 'Unable to reopen the Job Order.');
                }
                saved = true;
                saving = false;
                modal.hide();
                resume();
            } catch (error) {
                errorBox.textContent = error.message || 'Unable to confirm reopening. Reload the Job Order.';
                errorBox.classList.remove('d-none');
                errorBox.focus();
            } finally {
                saving = false;
                submit.disabled = reset.disabled = false;
            }
        });
        modal.show();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const root = document.getElementById('job-reopen-modal');
        if (!root) return;
        const id = root.querySelector('[name="jobOrderID"]').value;
        showReopening(root, function () {
            window.location.replace(index + '?m=joborders&a=considerCandidateSearch&jobOrderID=' + encodeURIComponent(id));
        }, function () {
            if (window.parent !== window && typeof window.parent.hidePopWin === 'function') window.parent.hidePopWin();
            else window.location.replace(index + '?m=joborders&a=show&jobOrderID=' + encodeURIComponent(id));
        });
    });

    document.addEventListener('submit', async function (event) {
        const form = event.target.closest('form[data-pipeline-add]');
        if (!form || !window.bootstrap) return;
        event.preventDefault();
        if (opening || document.getElementById('job-reopen-modal')) return;
        opening = true;
        const resume = function () { HTMLFormElement.prototype.submit.call(form); };
        try {
            const url = index + '?m=joborders&a=reopen&jobOrderID=' + encodeURIComponent(form.elements.jobOrderID.value);
            const response = await fetch(url, {credentials:'same-origin', cache:'no-store'});
            if ((response.headers.get('content-type') || '').includes('application/json')) {
                const result = await response.json();
                if (!result.success) throw new Error(result.message || 'Job Order unavailable.');
                if (result.required === false) { resume(); return; }
            }
            if (!response.ok) throw new Error('Unable to open the Job Order. Reload its current state.');
            const holder = document.createElement('div');
            holder.innerHTML = await response.text();
            const root = holder.querySelector('#job-reopen-modal');
            if (!root) throw new Error('Reload the page and check your session before adding candidates.');
            document.body.appendChild(root);
            showReopening(root, resume, function () { form.querySelector('button').focus(); });
        } catch (error) {
            window.alert(error.message || 'Unable to open reopening. Reload the Job Order.');
        } finally { opening = false; }
    });
}());
