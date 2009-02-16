/*
 * OSATS
 * GNU License
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