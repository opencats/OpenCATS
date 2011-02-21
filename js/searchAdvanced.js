/*
 * CATS
 * Search Advanced JavaScript Library
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
 * $Id: searchAdvanced.js 2372 2007-04-24 21:57:11Z will $
 */

var modes;
var data;

function advancedSearchReset()
{
    nodes = [];
    data = [];
    data[0] = document.getElementById('searchText').value;
    nodes[0] = '';
    advancedSearchDraw();
}

function advancedSearchSet()
{
    var text = '';
    var textp = '';

    for (var i = 0; i < nodes.length; i++)
    {
        if (typeof data[i] != 'undefined')
        {
            text += data[i] + nodes[i];
            if (i != 0)
            {
                textp += '{[+';
            }
            textp += data[i] + '[|]' + nodes[i];
        }
        else if (typeof nodes[i] != 'undefined')
        {
            text += nodes[i];
            textp += '[|]' + nodes[i];
        }
    }

    document.getElementById('searchText').value = text;
    document.getElementById('advancedSearchParser').value = textp;
}

function advancedSearchDraw()
{
    var html = '<br />Advanced search:<br />';

    if (nodes.length > 2)
    {
        html += '<select id="nothing" style="width:120px;"><option>--------</option></select>';
    }

    html += '<input type="text" id="searchValue' + 0 + '" value="' + data[0] + '" value="' + data[0] + '" onkeyup="data[0] = document.getElementById(\'searchValue\'+' + 0 + ').value; advancedSearchSet(); ">';

    for (var i = 0; i < nodes.length; i++)
    {
        if (nodes.length > 2)
        {
            html += '<br />';
        }

        html += '<select id="searchSelect' + i + '" onchange="setSearchNode(' + i + ');" style="width:120px;">';
        html += '<option value=""></option>';
        html += '<option value=" AND " '      + ((nodes[i] == " AND "     ) ? 'selected' : '') + '>AND</option>';
        html += '<option value=" OR " '       + ((nodes[i] == " OR "      ) ? 'selected' : '') + '>OR</option>';
        html += '<option value=" AND NOT " '  + ((nodes[i] == " AND NOT " ) ? 'selected' : '') + '>NOT</option>';
        html += '<option value="* " '         + ((nodes[i] == "* "        ) ? 'selected' : '') + '>Partial Match</option>';
        html += '<option value=" AND (" '     + ((nodes[i] == " AND ("    ) ? 'selected' : '') + '>Nested AND</option>';
        html += '<option value=" OR (" '      + ((nodes[i] == " OR ("     ) ? 'selected' : '') + '>Nested OR</option>';
        html += '<option value=" AND NOT (" ' + ((nodes[i] == " AND NOT (") ? 'selected' : '') + '>Nested NOT</option>';

        if (i != 0)
        {
            html += '<option value=")" ' + ((nodes[i] == ")") ? 'selected' : '') + '>)</option>';
        }

        html += '</select>';
        if (nodes[i] == " AND " || nodes[i] == " OR " || nodes[i] == " AND NOT "
           || nodes[i] == " AND (" || nodes[i] == " OR (" || nodes[i] == " AND NOT (" )
        {
            html += '<input type="text" id="searchValue' + (i + 1) + '" value="' + ((typeof data[i + 1] != "undefined") ? data[i + 1] : "") + '" onkeyup="data['+(i+1)+'] = document.getElementById(\'searchValue\'+' + (i + 1) + ').value; advancedSearchSet();">'
        }
        else
        {
            html += '<input type="text" id="searchValue' + (i + 1) + '" style="display:none;" value="' + ((typeof data[i + 1] != "undefined") ? data[i + 1] : "") + '" onkeyup="data[' + (i + 1) + '] = document.getElementById(\'searchValue\'+' + (i + 1) + ').value; advancedSearchSet();">'
        }
    }
    html += '<br /><br />';
    html += '<input type="button" class="button" id="searchAdvanced" name="searchAdvanced" value="Search" onclick="advancedSearchSet(); document.getElementById(\'advancedSearchOn\').value=' + data.length + '; document.getElementById(\'searchForm\').submit();" />&nbsp;';
    html += '<input type="button" class="button" name="simpleSearch" value="Simple" onclick="document.getElementById(\'advancedSearchField\').style.display=\'none\';" />&nbsp;';
    html += '<input type="button" class="button" name="resetSearch" value="Reset" onclick="document.getElementById(\'searchText\').value = \'\'; advancedSearchReset();" />&nbsp;';
    document.getElementById('advancedSearchField').innerHTML = html;
}

function setSearchNode(nodeNum)
{
    var dropDownList = document.getElementById('searchSelect'+nodeNum);
    nodes[nodeNum] = dropDownList[dropDownList.selectedIndex].value;
    if (nodes[nodeNum] == " AND (" || nodes[nodeNum] == " OR (" || nodes[nodeNum] == " NOT (")
    {
        nodes[nodeNum + 1] = ")";
        data[nodeNum + 2] = "";
    }
    else
    {
        if (nodes.length == nodeNum+1)
        {
            nodes[nodeNum + 1] = "";
        }
    }
    if (nodes[nodeNum] == ")")
    {
         data[nodeNum + 1] = "";
    }

    advancedSearchSet();
    advancedSearchDraw();

    if (nodes[nodeNum] != ")")
    {
        document.getElementById('searchValue'+(nodeNum+1)).focus();
    }
}

function advancedSearchConsider()
{
    if (typeof(advancedValidFields) == 'undefined') return;
    var dropDownList = document.getElementById('searchMode');
    var theField = dropDownList[dropDownList.selectedIndex].value;
    var goodField = false;
    for (var i = 0; i < advancedValidFields.length; i++)
    {
        if (theField == advancedValidFields[i])
        {
            goodField = true;
        }
    }
    if (goodField)
    {
        document.getElementById('advancedSearch').style.display = '';
    }
    else
    {
        document.getElementById('advancedSearch').style.display = 'none';
        document.getElementById('advancedSearchField').style.display = 'none';
    }
}
