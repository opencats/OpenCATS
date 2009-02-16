/*
 * OSATS
 * GNU License
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
