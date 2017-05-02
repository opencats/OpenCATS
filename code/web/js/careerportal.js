/*
 * CATS
 * Career Portal JavaScript Library
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
 * $Id: careerportal.js 3554 2007-11-11 22:17:26Z will $
 */

var usingID = '';

function setModifyingJobDefault(id, url)
{
    hideAllEditingFields();
    document.getElementById('buttonEdit').style.display = 'none';
    document.getElementById('buttonEditDefault').style.display = '';
    document.getElementById('buttonDelete').style.display = 'none';
    document.getElementById('previewBox').src = url;
    document.getElementById('textTemplateName').innerHTML = id;

    usingID = id;
}

function setModifyingJobCustom(id, url)
{
    hideAllEditingFields();
    document.getElementById('buttonEdit').style.display = '';
    document.getElementById('buttonEditDefault').style.display = 'none';
    document.getElementById('buttonDelete').style.display = '';
    document.getElementById('previewBox').src = url;
    document.getElementById('textTemplateName').innerHTML = id;

    usingID = id;

}

function hideAllEditingFields()
{
    document.getElementById('previewBox').style.height='250px';
    document.getElementById('confirmDuplicate').style.display='none';
    document.getElementById('confirmDelete').style.display='none';
    document.getElementById('confirmEditDefault').style.display='none';
    document.getElementById('confirmNew').style.display='none';
}

function showDuplicateInput()
{
    hideAllEditingFields();
    document.getElementById('previewBox').style.height = '210px';
    document.getElementById('confirmDuplicate').style.display = '';
    document.getElementById('duplicateName').value = 'Copy of ' + usingID;
    document.getElementById('origName').value = usingID;
}

function showDeleteInput()
{
    hideAllEditingFields();
    document.getElementById('previewBox').style.height='210px';
    document.getElementById('confirmDelete').style.display='';
    document.getElementById('delName').value = usingID;
}

function showEditDefaultInput()
{
    hideAllEditingFields();
    document.getElementById('previewBox').style.height='210px';
    document.getElementById('confirmEditDefault').style.display='';
}

function showNewInput()
{
    hideAllEditingFields();
    document.getElementById('confirmNew').style.display='';
}

function fullScreenPreview()
{
    hideAllEditingFields();
    window.open(indexURL+'?m=settings&a=previewPage&url='+urlEncode(document.getElementById('previewBox').src)+'&message='+
        urlEncode("This is a full screen preview of the template '"+usingID+"'.")
    );
}

function setAsActive()
{
    document.getElementById('activeName').value = usingID;
    document.getElementById('setAsActiveForm').submit();
}

