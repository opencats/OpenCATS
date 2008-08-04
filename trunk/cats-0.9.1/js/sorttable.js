/* Originally by Stuart Langridge.
 * Modifications by Cognizo Technologies, Inc.
 */
var ST_SORT_COLUMN;
var ST_SORT_IN_PAIRS = 0;

/* Find all tables with class="sortable" and make them sortable. */
function st_init()
{
    if (!document.getElementsByTagName)
    {
        return;
    }

    tabels = document.getElementsByTagName('table');
    for (var i = 0; i < tabels.length; i++)
    {
        var table = tabels[i];

        if ((' ' + table.className + ' ').indexOf('notsortable') != -1)
        {
            /* Do nothing. */
        }
        else if ((' ' + table.className + ' ').indexOf('sortablepair') != -1)
        {
            st_makeSortable(table, true);
        }
        else if ((' ' + table.className + ' ').indexOf('sortable') != -1)
        {
            st_makeSortable(table, false);
        }
    }
}

/* Make a table sortable. */
function st_makeSortable(table, sortInPairs)
{
    if (table.rows && table.rows.length > 0)
    {
        var headerRow = table.rows[0];
    }

    if (!headerRow)
    {
        return;
    }

    /* We do have a first row. It is probably the header, so we will make each
     * heading clickable.
     */
    for (var i = 0; i < headerRow.cells.length; i++)
    {
        var cell = headerRow.cells[i];
        var text = st_getInnerText(cell);

        if (sortInPairs)
        {
            var sortInPairsString = 'true';
        }
        else
        {
            var sortInPairsString = 'false';
        }

        cell.innerHTML = '<a href="#" class="sortheader" onclick="st_resortTable(this, '
            + i + ', ' + sortInPairsString + '); return false;" style="text-decoration: none;">'
            + text + '<span class="sortarrow">&nbsp;&nbsp;&nbsp;</span></a>';
    }
}

function st_getInnerText(element)
{
    if (typeof(element) == 'string' || typeof(element) == 'undefined')
    {
        return element;
    }

    if (element.innerText)
    {
        return element.innerText;
    }

    var textString = '';

    var childNodes = element.childNodes;
    for (var i = 0; i < childNodes.length; i++)
    {
        switch (childNodes[i].nodeType)
        {
            case 1: /* ELEMENT_NODE */
                textString += st_getInnerText(childNodes[i]);
                break;
            case 3: /* TEXT_NODE */
                textString += childNodes[i].nodeValue;
                break;
        }
    }

    return textString;
}

function st_resortTable(link, cellID, sortInPairs)
{
    for (var i = 0; i < link.childNodes.length; i++)
    {
        if (link.childNodes[i].tagName && link.childNodes[i].tagName.toLowerCase() == 'span')
        {
            var span = link.childNodes[i];
            break;
        }
    }

    var td     = link.parentNode;
    var table  = st_getParent(td, 'table');
    /* Safari compatability. */
    var column = cellID || td.cellIndex;

    if (table.rows.length <= 1)
    {
        return;
    }

    /* Determine how to sort the data based on the first cell's contents. */
    var firstCellText = st_getInnerText(table.rows[1].cells[column]);
    var firstCellHtml = table.rows[1].cells[column].innerHTML;
    if (firstCellText.match(/^\d\d[\/-]\d\d[\/-]\d\d\d\d\s*$/) || firstCellText.match(/^\d\d[\/-]\d\d[\/-]\d\d$/))
    {
        var sortFunc = st_sort_date;
    }
    else if (firstCellHtml.indexOf('<!--MATCHROW') != -1)
    {
        var sortFunc = st_sort_matchrow;
    }
    else if (firstCellText.match(/^[?$]/))
    {
        var sortFunc = st_sort_currency;
    }
    else if (firstCellText.match(/^[\d\.]+\s*$/))
    {
        var sortFunc = st_sort_numeric;
    }
    else
    {
        var sortFunc = st_sort_caseinsensitive;
    }

    ST_SORT_COLUMN = column;
    ST_SORT_IN_PAIRS = sortInPairs;

    var newRows = new Array();

    /* If we are in "pair" sorting mode, every two rows are treated as
     * one row.
     */
    if (sortInPairs)
    {
        var arrayCounter = 0;
        for (var i = 1; i < table.rows.length; i++)
        {
            if ((i % 2) != 0)
            {
                newRows[arrayCounter] = new Array(2);
                newRows[arrayCounter][0] = table.rows[i];
                newRows[arrayCounter][1] = table.rows[i + 1];
                arrayCounter++;
            }
        }
    }
    else
    {
        for (var i = 1; i < table.rows.length; i++)
        {
            newRows[i - 1] = table.rows[i];
        }
    }

    newRows.sort(sortFunc);

    if (span.getAttribute('sortdir') == 'down')
    {
        var arrow = '&nbsp;&nbsp;&uarr;';
        newRows.reverse();
        span.setAttribute('sortdir', 'up');
    }
    else
    {
        var arrow = '&nbsp;&nbsp;&darr;';
        span.setAttribute('sortdir', 'down');
    }

    /* We appendChild rows that already exist to the tbody, so it moves them rather than creating new ones. */
    if (sortInPairs)
    {
        for (var i = 0; i < newRows.length; i++)
        {
            table.tBodies[0].appendChild(newRows[i][0]);
            table.tBodies[0].appendChild(newRows[i][1]);
        }
    }
    else
    {
        for (var i = 0; i < newRows.length; i++)
        {
            table.tBodies[0].appendChild(newRows[i]);
        }
    }

    /* Delete any other arrows. */
    var spans = document.getElementsByTagName('span');
    for (var i = 0; i < spans.length; i++)
    {
        if (spans[i].className == 'sortarrow')
        {
            if (st_getParent(spans[i], 'table') == st_getParent(link, 'table'))
            {
                spans[i].innerHTML = '&nbsp;&nbsp;&nbsp;';
            }
        }
    }

    span.innerHTML = arrow;

    st_reAlternateTable(table);
}

function st_reAlternateTable(table)
{
    if (ST_SORT_IN_PAIRS)
    {
        for (var i = 1; i < table.rows.length; i += 4)
        {
            table.rows[i].className     = 'evenTableRow';
            table.rows[i + 1].className = 'evenTableRow';

            if ((i + 2) < table.rows.length)
            {
                table.rows[i + 2].className = 'oddTableRow';
                table.rows[i + 3].className = 'oddTableRow';
            }
        }
    }
    else
    {
        for (var i = 1; i < table.rows.length; i++)
        {
            if ((i % 2) == 0)
            {
                table.rows[i].className = 'evenTableRow';
            }
            else
            {
                table.rows[i].className = 'oddTableRow';
            }
        }
    }
}

function st_getParent(element, pTagName)
{
    if (element == null)
    {
        return null;
    }

    if (element.nodeType == 1 && element.tagName.toLowerCase() == pTagName.toLowerCase())
    {
        /* Gecko bug, supposed to be uppercase. */
        return element;
    }

    return st_getParent(element.parentNode, pTagName);
}

function st_sort_date(a, b)
{
    /* Y2K Notes: Two digit years less than 50 are treated as 20XX, greater
     * than 50 are treated as 19XX.
     */
    if (ST_SORT_IN_PAIRS)
    {
        aa = st_getInnerText(a[0].cells[ST_SORT_COLUMN]);
        bb = st_getInnerText(b[0].cells[ST_SORT_COLUMN]);
    }
    else
    {
        aa = st_getInnerText(a.cells[ST_SORT_COLUMN]);
        bb = st_getInnerText(b.cells[ST_SORT_COLUMN]);
    }

    if (aa.length == 10)
    {
        dt1 = aa.substr(6, 4) + aa.substr(0, 2) + aa.substr(3, 2);
    }
    else
    {
        year = aa.substr(6, 2);
        if (parseInt(year) < 50)
        {
            year = '20' + year;
        }
        else
        {
            year = '19' + year;
        }
        dt1 = year + aa.substr(0, 2) + aa.substr(3, 2);
    }

    if (bb.length == 10)
    {
        dt2 = bb.substr(6, 4) + bb.substr(0, 2) + bb.substr(3, 2);
    }
    else
    {
        year = bb.substr(6, 2);
        if (parseInt(year) < 50)
        {
            year = '20' + year;
        }
        else
        {
            year = '19' +year;
        }

        dt2 = year + bb.substr(0, 2) + bb.substr(3, 2);
    }

    if (dt1 == dt2)
    {
        return 0;
    }

    if (dt1 < dt2)
    {
        return -1;
    }

    return 1;
}

function st_sort_currency(a, b)
{
    if (ST_SORT_IN_PAIRS)
    {
        aa = st_getInnerText(a[0].cells[ST_SORT_COLUMN]).replace(/[^0-9.]/g, '');
        bb = st_getInnerText(b[0].cells[ST_SORT_COLUMN]).replace(/[^0-9.]/g, '');
    }
    else
    {
        aa = st_getInnerText(a.cells[ST_SORT_COLUMN]).replace(/[^0-9.]/g, '');
        bb = st_getInnerText(b.cells[ST_SORT_COLUMN]).replace(/[^0-9.]/g, '');
    }

    return (parseFloat(aa) - parseFloat(bb));
}

function st_sort_matchrow(a, b)
{
    if (ST_SORT_IN_PAIRS)
    {
        aa = a[0].cells[ST_SORT_COLUMN].innerHTML;
        bb = b[0].cells[ST_SORT_COLUMN].innerHTML;
    }
    else
    {
        aa = a.cells[ST_SORT_COLUMN].innerHTML;
        bb = b.cells[ST_SORT_COLUMN].innerHTML;
    }

    var var1 = aa.substring(aa.indexOf('<!--MATCHROW') + 13, aa.indexOf('-->'));
    var var2 = bb.substring(bb.indexOf('<!--MATCHROW') + 13, bb.indexOf('-->'));

    return (eval('' + var1 + '-' + var2));
}

function st_sort_numeric(a, b)
{
    if (ST_SORT_IN_PAIRS)
    {
        aa = parseFloat(st_getInnerText(a[0].cells[ST_SORT_COLUMN]));
        bb = parseFloat(st_getInnerText(b[0].cells[ST_SORT_COLUMN]));
    }
    else
    {
        aa = parseFloat(st_getInnerText(a.cells[ST_SORT_COLUMN]));
        bb = parseFloat(st_getInnerText(b.cells[ST_SORT_COLUMN]));
    }

    if (isNaN(aa))
    {
        aa = 0;
    }

    if (isNaN(bb))
    {
        bb = 0;
    }

    return (aa - bb);
}

function st_sort_caseinsensitive(a, b)
{
    if (ST_SORT_IN_PAIRS)
    {
        aa = st_getInnerText(a[0].cells[ST_SORT_COLUMN]).toLowerCase();
        bb = st_getInnerText(b[0].cells[ST_SORT_COLUMN]).toLowerCase();
    }
    else
    {
        aa = st_getInnerText(a.cells[ST_SORT_COLUMN]).toLowerCase();
        bb = st_getInnerText(b.cells[ST_SORT_COLUMN]).toLowerCase();
    }

    if (aa == bb)
    {
        return 0;
    }

    if (aa < bb)
    {
        return -1;
    }

    return 1;
}

function st_sort_default(a, b)
{
    if (ST_SORT_IN_PAIRS)
    {
        aa = st_getInnerText(a[0].cells[ST_SORT_COLUMN]);
        bb = st_getInnerText(b[0].cells[ST_SORT_COLUMN]);
    }
    else
    {
        aa = st_getInnerText(a.cells[ST_SORT_COLUMN]);
        bb = st_getInnerText(b.cells[ST_SORT_COLUMN]);
    }

    if (aa == bb)
    {
        return 0;
    }

    if (aa < bb)
    {
        return -1;
    }

    return 1;
}

addEvent(window, 'load', st_init, true);
addEvent(window, 'unload', EventCache.flush, false);
