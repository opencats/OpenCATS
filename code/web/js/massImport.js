var documentOk = 0;
var documentFail = 0;
var progressBarWidth = 868;
var parsingBlock = false;
var parseURL = '?m=import&a=massImportDocument';
var parseSent = 0;
var parseReceived = 0;

// Maintain seperate arrays for clarity (versus a big multi-dimensional one)
var documentName = Array();
var documentRealName = Array();
var documentExt = Array();
var documentType = Array();
var documentCTime = Array();

function goStep4()
{
    var btn = document.getElementById('nextStep');
    btn.disabled = 'true';
    btn.value = 'Please wait...';
    document.location.href='?m=import&a=massImport&step=4';
}

function setProgressBar(x, fileName)
{
    var barObj = document.getElementById('statusBar');
    var fileNameObj = document.getElementById('fileName');
    var width = Math.floor(progressBarWidth * x / 100);

    fileNameObj.innerHTML = fileName;
    barObj.style.width = width + 'px';
}

function addDocument(name, realName, ext, type, cTime)
{
    documentName.push(name);
    documentRealName.push(realName);
    documentExt.push(ext);
    documentType.push(type);
    documentCTime.push(cTime);
}

function startDocumentParsing()
{
    if (parsingBlock)
    {

    }
    else
    {
        if (currentDocument >= documentName.length)
        {
            // Process is complete.
            //alert('Success: ' + documentOk + '  Failed: ' + documentFail);
            document.location.href = '?m=import&a=massImport&step=3';
            return;
        }

        parseDocument();
    }

    var d = new Date();
    parseSent = d.getTime();
    setTimeout('startDocumentParsing()', 10);
}

function deleteUploadFiles()
{
    var ajaxObj;
    var url = '?m=import&a=massImport&step=99';

    try
    {
        // Firefox, Opera 8.0+, Safari
        ajaxObj = new XMLHttpRequest();
    }
    catch (e)
    {
        // Internet Explorer
        try
        {
            ajaxObj = new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch (e)
        {
            try
            {
                ajaxObj = new ActiveXObject("Microsoft.XMLHTTP");
            }
            catch (e)
            {
                alert("Your browser does not support AJAX!");
                return false;
            }
        }
    }
    ajaxObj.onreadystatechange = function()
    {
        if (ajaxObj.readyState == 4)
        {
            var obj = document.getElementById('uploadQueue');
            if (obj)
            {
                obj.style.display = 'none';
            }
        }
    }

    ajaxObj.open("GET",url,true);
    ajaxObj.send(null);
}

function parseDocument()
{
    var ajaxObj;
    var d = new Date();
    
    var url = parseURL + '&name=' + urlEncode(documentName[currentDocument])
        + '&realName=' + urlEncode(documentRealName[currentDocument])
        + '&ext=' + urlEncode(documentExt[currentDocument])
        + '&type=' + urlEncode(documentType[currentDocument])
        + '&cTime=' + urlEncode(documentCTime[currentDocument]);

    // FIXME: USE CATS AJAX CODE!
    try
    {
        // Firefox, Opera 8.0+, Safari
        ajaxObj = new XMLHttpRequest();
    }
    catch (e)
    {
        // Internet Explorer
        try
        {
            ajaxObj = new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch (e)
        {
            try
            {
                ajaxObj = new ActiveXObject("Microsoft.XMLHTTP");
            }
            catch (e)
            {
                alert("Your browser does not support AJAX!");
                return false;
            }
        }
    }
    ajaxObj.onreadystatechange = function()
    {
        if (ajaxObj.readyState == 4)
        {
            if (ajaxObj.responseText == 'Ok')
            {
                documentOk++;
            }
            else
            {
                documentFail++;
            }
            parsingBlock = false;
            setProgressBar(Math.floor(currentDocument / (documentName.length-1) * 100.0), documentRealName[currentDocument]);
            currentDocument++;
        }
    }

    ajaxObj.open("GET",url,true);
    ajaxObj.send(null);
    parsingBlock = true;
}



function validation()
{
    if (typeof runResumeParserValidation == 'function') runResumeParserValidation();
}

function documentNext()
{
    setDocumentAction('next');
    document.verifyForm.submit();
}

function documentPrevious()
{
    setDocumentAction('previous');
    document.verifyForm.submit();
}

function documentSkip()
{
    setDocumentAction('skip');
    document.verifyForm.submit();
}

function setDocumentAction(action)
{
    var obj = getObj('documentAction');
    if (obj)
    {
        obj.value = action;
    }
}

var enableFieldCopy = false;
var copyBlocks = new Array();
var copyBlockHeights = new Array();

function fieldCopy(name)
{
    if (!enableFieldCopy) return;
    var targ = getObj('document');
    var dest = getObj(name);
    var selText = (targ.value).substring(targ.selectionStart, targ.selectionEnd);

    // Remove double spacing
    while (selText.indexOf('  ') != -1)
    {
        selText = selText.replace(/  /, ' ');
    }

    // Remove double line-returns
    while (selText.indexOf("\n\n") != -1)
    {
        selText = selText.replace(/\n\n/g, "\n");
    }

    // Some... special touches for skills
    if (name == 'skills')
    {
        selText = selText.replace(/[\n\r\t\-\* ]{2,}/g, "\n");
    }

    // Trim beginning and end of data
    while ((selText[0]).match(/[ \n\r\t\-\*\,\.]{1,}/))
    {
        selText = selText.substring(1);
    }
    while ((selText[selText.length-1]).match(/[ \n\r\t\-\*\,\.]{1,}/))
    {
        selText = selText.substring(0, selText.length-1);
    }

    if (targ && dest)
    {
        dest.value = selText;

        getObj('document').selectionStart = 0;
        getObj('document').selectionEnd = 0;
        enableFieldCopy = false;
        checkCopyBlocks();
    }

    validation();
}

function addCopyBlock(name, height)
{
    var block = getObj(name + 'CopyBlock');
    var greyBlock = getObj('copyBlockGrey');
    if (block)
    {
        copyBlocks.push(name);
        copyBlockHeights.push(height);
        block.innerHTML = greyBlock.innerHTML;
    }
}

function documentMouseUp(obj)
{
    var selText = (obj.value).substring(obj.selectionStart, obj.selectionEnd);
    var greyBlock, activeBlock;
    var i, block, height;

    if (selText.length > 0)
    {
        enableFieldCopy = true;
    }
    else
    {
        enableFieldCopy = false;
    }

    for (i=0; i<copyBlocks.length; i++)
    {
        height = copyBlockHeights[i];
        switch (height)
        {
            case 0:
                greyBlock = getObj('copyBlockGreyMini');
                activeBlock = getObj('copyBlockActiveMini');
                break;
            case 1:
                greyBlock = getObj('copyBlockGrey');
                activeBlock = getObj('copyBlockActive');
                break;
        }
        block = getObj(copyBlocks[i] + 'CopyBlock');

        if (enableFieldCopy)
        {
            block.innerHTML = activeBlock.innerHTML;
        }
        else
        {
            block.innerHTML = greyBlock.innerHTML;
        }
    }
}

function checkCopyBlocks()
{
    documentMouseUp(getObj('document'));
    setTimeout('checkCopyBlocks()', 1000);
}



// Grid Functions
var offset = 0;

function gridBrowse()
{
    var row, col, dataItem, targ;
    for (row=0; row<100; row++)
    {
        for (col=0; col<15; col++)
        {
            targ = document.getElementById('grid_row_' + row + '_column_' + col);
            dataItem = document.getElementById('data_' + (row + offset) + '_column_' + col);

            if (targ) targ.innerHTML = '&nbsp;';

            if (targ && !dataItem) return;

            if (targ && dataItem)
            {
                if (row % 2)
                {
                    targ.className = 'dataColumnEven';
                }
                else
                {
                    targ.className = 'dataColumnOdd';
                }
                targ.innerHTML = dataItem.innerHTML;
                success = true;
            }
        }
    }
}

var isGridScrollUp = false;
var isGridScrollDown = false;

function gridScrollUp()
{
    if (!isGridScrollUp) return;
    if (offset > 0) offset--;
    gridBrowse();
    if (isGridScrollUp)
    {
        setTimeout('gridScrollUp()', 40);
    }
}

function gridScrollDown()
{
    if (!isGridScrollDown) return;
    if (offset < (totalRows - 10)) offset++;
    gridBrowse();
    if (isGridScrollDown)
    {
        setTimeout('gridScrollDown()', 40);
    }
}

function startGridScrollUp()
{
    isGridScrollUp = true;
    gridScrollUp();
}

function endScrolling()
{
    isGridScrollUp = false;
    isGridScrollDown = false;
}

function startGridScrollDown()
{
    isGridScrollDown = true;
    gridScrollDown();
}
