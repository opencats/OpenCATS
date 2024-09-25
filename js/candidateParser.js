var transferOn = new Image(47, 18);
transferOn.src = 'images/parser/transfer.gif';
var transferOff = new Image(47, 18);
transferOff.src = 'images/parser/transfer_grey.gif';

function loadDocumentFileContents()
{
    var file = document.getElementById('documentFile');
    var obj = document.getElementById('loadDocument');

    obj.value = '';

    obj.value = 'true';
    document.addCandidateForm.submit();
}

function parseDocumentFileContents() {
    console.log("Starting to parse document file contents..."); // Log this
    var text = document.getElementById('documentText');
    var file = document.getElementById('documentFile');
    var obj = document.getElementById('loadDocument');
    var obj2 = document.getElementById('parseDocument');
    var img = document.getElementById('transfer');

    obj.value = '';
    obj2.value = '';

    if (text.value == '' && file.value == '') {
        console.log("No text or file to parse."); // Add this
        return;
    }

    obj.value = 'true';
    obj2.value = 'true';
    console.log("Form submitted for parsing."); // Add this
    document.addCandidateForm.submit();
}


function documentFileChange()
{
    var obj = document.getElementById('documentLoad');
    var img = document.getElementById('transfer');

    if (obj && obj.value != '')
    {
        if (img)
        {
            img.src = transferOn.src;
            img.style.cursor='pointer';
        }
        obj.disabled=false;
    }
    else
    {
        if (img)
        {
            img.style.cursor='default';
            img.src = transferOff.src;
        }
        obj.disabled=true;
    }
}

function documentCheck()
{
    var obj = document.getElementById('documentText');
    var img = document.getElementById('transfer');
    var file = document.getElementById('documentFile');
    var tempFile = document.getElementById('documentTempFile');

    if ((obj.value).length > 0 || file.value != '')
    {
        if (img)
        {
            img.src = transferOn.src;
            img.style.cursor='pointer';
        }
    }
    else
    {
        if (img)
        {
            img.style.cursor='default';
            img.src = transferOff.src;
        }
    }
}

function removeDocumentFile()
{
    var obj1 = document.getElementById('documentText');
    var obj2 = document.getElementById('documentTempFile');
    var obj3 = document.getElementById('loadDocument');
    var obj4 = document.getElementById('parseDocument');
    var obj5 = document.getElementById('showAttachmentDetails');

    obj1.value = '';
    obj2.value = '';
    obj3.value = '';
    obj4.value = '';
    obj5.style.display = 'none';
}
