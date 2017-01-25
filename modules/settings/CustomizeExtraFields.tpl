<?php /* $Id: CustomizeExtraFields.tpl 3660 2007-11-19 18:26:19Z brian $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('js/highlightrows.js', 'modules/settings/validator.js', 'js/listEditor.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, ''); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Settings: Customization</h2></td>
                </tr>
            </table>

            <p class="note">Customize Extra Fields</p>

            <form name="editSettingsForm" id="editSettingsForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=customizeExtraFields" method="post">
              <input type="hidden" name="postback" value="postback" />
              <table class="sortable" width="920">
                <div id="changedDiv" style="display:none;">
                  <div style="font-weight:bold; border: 1px solid #000; background-color: #ff0000; padding:5px;">
                     You have made changes, to apply them press 'Save' at the bottom of the page.
                  </div>
                  <br />
                </div>
                <input type="hidden" name="commandList" id="commandList" />
                <script type="text/javascript">
                   function appendCommandList(command)
                   {
                      document.getElementById('commandList').value = document.getElementById('commandList').value + encodeURI(command) + ',';
                      document.getElementById('changedDiv').style.display = '';
                      document.getElementById('buttonSave').style.display = '';
                      document.getElementById('buttonDone').style.display = 'none';
                   }
                   var inlineEditIDCounter = 0;
                </script>
                    <?php foreach (array(array("name" => "Job Orders", "RS" => $this->extraFieldSettingsJobOrdersRS, "type" => DATA_ITEM_JOBORDER), 
                                         array("name" => "Candidates", "RS" => $this->extraFieldSettingsCandidatesRS, "type" => DATA_ITEM_CANDIDATE), 
                                         array("name" => "Companies", "RS" => $this->extraFieldSettingsCompaniesRS, "type" => DATA_ITEM_COMPANY), 
                                         array("name" => "Contacts", "RS" => $this->extraFieldSettingsContactsRS, "type" => DATA_ITEM_CONTACT)) as $index => $data): ?>
                        <tr>
                            <td style="width:150px;">
                                <?php echo($data['name']); ?>
                            </td>
                            <td>
                                <script type="text/javascript">
                                
                                    //TODO: Document me.
                                    var alternatingClassVariable<?php echo($index); ?> = "<?php TemplateUtility::printAlternatingRowClass(count($data['RS'])); ?>";
                                    var onIndex<?php echo($index); ?> = <?php echo(count($data['RS'])); ?>;
                                    function alternatingClass<?php echo($index); ?>()
                                    {
                                        var r = alternatingClassVariable<?php echo($index); ?>;
                                        if (alternatingClassVariable<?php echo($index); ?> == "evenTableRow")
                                        {
                                            alternatingClassVariable<?php echo($index); ?> = "oddTableRow";
                                        }
                                        else
                                        {
                                            alternatingClassVariable<?php echo($index); ?> = "evenTableRow";
                                        }    
                                        return r;
                                    }
                                    
                                    //TODO: Document me.
                                    function checkForDuplicateRowOnTable<?php echo($index); ?>(rowName)
                                    {
                                       tbl = document.getElementById('extraFieldsTable<?php echo($index); ?>');
                                       var lastRow = tbl.rows.length;
                                       for (var i = 1; i < lastRow; i++)
                                       {
                                          var row = tbl.rows[i];
                                          if (row.style.display == 'none')
                                          {
                                             continue;
                                          }
                                          
                                          var col = row.getElementsByTagName('td')[1];
                                          var html = col.innerHTML;
                                          
                                          if (html.indexOf('<') != -1)
                                          {
                                             html = html.substr(0,html.indexOf('<'));
                                          }
                                          
                                          //html.trim();
                                          html = html.replace(/^\s+|\s+$/g,"");
                                          
                                          if (html == rowName) 
                                          {
                                             return true;
                                          }
                                       }
                                       
                                       return false;
                                    }
                                    
                                    function getRowNameByRowIndex<?php echo($index); ?>(rowIndex)
                                    {
                                       tbl = document.getElementById('extraFieldsTable<?php echo($index); ?>');
                                       
                                       var row = tbl.rows[rowIndex+1];

                                       var col = row.getElementsByTagName('td')[1];
                                       var html = col.innerHTML;

                                       if (html.indexOf('<') != -1)
                                       {
                                          html = html.substr(0,html.indexOf('<'));
                                       }

                                       //html.trim();
                                       html = html.replace(/^\s+|\s+$/g,"");

                                       return html;
                                    }
                                    
                                    function getRowIndexByRowName<?php echo($index); ?>(rowName)
                                    {
                                       tbl = document.getElementById('extraFieldsTable<?php echo($index); ?>');
                                       var lastRow = tbl.rows.length;
                                       for (var i = 1; i < lastRow; i++)
                                       {
                                          var row = tbl.rows[i];
                                          if (row.style.display == 'none')
                                          {
                                             continue;
                                          }
                                          
                                          var col = row.getElementsByTagName('td')[1];
                                          var html = col.innerHTML;
                                          
                                          if (html.indexOf('<') != -1)
                                          {
                                             html = html.substr(0,html.indexOf('<'));
                                          }
                                          
                                          //html.trim();
                                          html = html.replace(/^\s+|\s+$/g,"");
                                          
                                          if (html == rowName) 
                                          {
                                             return i;
                                          }
                                       }
                                       
                                       return 0;
                                    }
                                    
                                    //TODO: Document me.
                                    function addRowToTable<?php echo($index); ?>(rowName, rowTypeName, rowIndex)
                                    {
                                        tbl = document.getElementById('extraFieldsTable<?php echo($index); ?>');
                                        var lastRow = tbl.rows.length;
                                        var row = tbl.insertRow(lastRow);
                                        row.className = alternatingClass<?php echo($index); ?>();
                                        row.id = "table<?php echo($index); ?>row" + rowIndex;

                                        var cellLeft = row.insertCell(0);
                                        var image = document.createElement('img');
                                        image.src = 'images/actions/delete.gif';
                                        image.border = 0;
                                        var hyperlink = document.createElement('a');
                                        hyperlink.href='javascript:void(0);';
                                        hyperlink.onclick=function() { deleteRow<?php echo($index); ?>(rowIndex, rowName); };
                                        hyperlink.appendChild(image);
                                        cellLeft.appendChild(hyperlink);
 
                                        var cellLeft = row.insertCell(1);
                                        var textNode = document.createTextNode(rowName);
                                        cellLeft.appendChild(textNode);
                                        
                                        var cellLeft = row.insertCell(2);
                                        var textNode = document.createTextNode(rowTypeName);
                                        cellLeft.appendChild(textNode);                                            
                                    }
                                    
                                    //TODO: Document me.
                                    function deleteRowFromTable<?php echo($index); ?>(rowIndex)
                                    {
                                        row = document.getElementById("table<?php echo($index); ?>row" + rowIndex);
                                        row.style.display='none';
                                        
                                        //TODO: BH: (optional) Reset all the even/odd classes for this entire table.
                                    }
                                    
                                    //TODO: Document me.
                                    function addOptionsAreaToTable<?php echo($index); ?>(rowIndex, rowName)
                                    {
                                        tbl = document.getElementById('extraFieldsTable<?php echo($index); ?>');
                                        var row = tbl.rows[rowIndex+1];
                                        var col = row.getElementsByTagName('td')[1];
                                        
                                        var brTag = document.createElement('br');
                                        col.appendChild(brTag);
                                        
                                        var tbl2 = document.createElement('table');
                                        tbl2.width='300';
                                        tbl2.id = 'optionsTable<?php echo($index); ?>row' + rowIndex;
                                        tbl2.className='sortable';
                                        col.appendChild(tbl2);
                                        
                                        var headerTr = tbl2.insertRow(0);
                                       
                                        var headerTh = document.createElement('th');
                                        headerTh.width="20";
                                        headerTr.appendChild(headerTh);
                                        
                                        var headerTh2 = document.createElement('th');
                                        headerTh2.align = 'left';
                                        headerTh2.innerHTML = 'Options';
                                        headerTr.appendChild(headerTh2);

                                        
                                        var addOptionDiv = document.createElement('div');
                                        
                                        addOptionDiv.innerHTML = 
                                            '<a href="javascript:void(0);" id="addOptionLine<?php echo($index); ?>row' + rowIndex + '" onclick="document.getElementById(&quot;addOption<?php echo($index); ?>row' + rowIndex + '&quot;).style.display=&quot;&quot;; document.getElementById(&quot;addOptionLine<?php echo($index); ?>row' + rowIndex + '&quot;).style.display=&quot;none&quot;; document.getElementById(&quot;addOptionInput<?php echo($index); ?>row' + rowIndex +'&quot;).focus(); document.getElementById(&quot;addOptionInput<?php echo($index); ?>row' + rowIndex +'&quot;).value = &quot;&quot;;">' +
                                                '<img src="images/actions/add_small.gif" border="0" />&nbsp;Add option to ' + rowName +
                                            '</a>' + 
                                            '<span style="display:none;" id="addOption<?php echo($index); ?>row' + rowIndex + '">' + 
                                                'Name:&nbsp;<input id="addOptionInput<?php echo($index); ?>row' + rowIndex + '" style="width:200px;" value="" class="inputbox" /><br />' +
                                                '<input type="button" class="button" value="Add Field" onclick="addOption<?php echo($index); ?>(document.getElementById(&quot;addOptionInput<?php echo($index); ?>row' + rowIndex + '&quot;).value, document.getElementById(&quot;optionsTable<?php echo($index); ?>row' + rowIndex + '&quot;), &quot;' + rowName + '&quot;);  document.getElementById(&quot;addOption<?php echo($index); ?>row' + rowIndex + '&quot;).style.display=&quot;none&quot;; document.getElementById(&quot;addOptionLine<?php echo($index); ?>row' + rowIndex + '&quot;).style.display=&quot;&quot;;" />&nbsp;' +
                                                '<input type="button" class="button" value="Cancel" onclick="document.getElementById(&quot;addOption<?php echo($index); ?>row' + rowIndex + '&quot;).style.display=&quot;none&quot;; document.getElementById(&quot;addOptionLine<?php echo($index); ?>row' + rowIndex + '&quot;).style.display=&quot;&quot;;" />' +
                                            '</span>';
                                        
                                        col.appendChild(addOptionDiv);
                                        
                                        tbl2.usingEvenOdd = "evenTableRow";
                                                       
                                        return tbl2;
                                    }
                                    
                                    function addOptionToTable<?php echo($index); ?>(optionName, tbl, fieldName)
                                    {
                                        r = tbl.usingEvenOdd;
                                
                                        if (tbl.usingEvenOdd == "evenTableRow")
                                        {
                                            tbl.usingEvenOdd = "oddTableRow";
                                        }
                                        else
                                        {
                                            tbl.usingEvenOdd = "evenTableRow";
                                        }    
                                        
                                        var lastRow = tbl.rows.length;
                                        var row = tbl.insertRow(lastRow);
                                        row.className = r;
                                        row.id = tbl.id + 'option'+lastRow;
                                        
                                        var cellLeft = row.insertCell(0);
                                        var image = document.createElement('img');
                                        image.src = 'images/actions/delete.gif';
                                        image.border = 0;
                                        var hyperlink = document.createElement('a');
                                        hyperlink.href='javascript:deleteOption<?php echo($index); ?>('+getRowIndexByRowName<?php echo($index); ?>(fieldName)+', '+lastRow+', "'+optionName+'", "'+fieldName+'"); void(0);';
                                        hyperlink.appendChild(image);
                                        cellLeft.appendChild(hyperlink);
                                        
                                        var cellLeft = row.insertCell(1);
                                        var textNode = document.createTextNode(optionName);
                                        cellLeft.appendChild(textNode);
                                    }
                                    
                                    function deleteOptionFromTable<?php echo($index); ?>(rowObject, optionName, fieldName)
                                    {
                                        rowObject.style.display = 'none';
                                    }
                                    
                                    //TODO: Document me.
                                    function addRow<?php echo($index); ?>(rowName, rowType, rowTypeName)
                                    {
                                        thisIndex = onIndex<?php echo($index); ?>;
                                        onIndex<?php echo($index); ?>++;
                                        
                                        if (checkForDuplicateRowOnTable<?php echo($index); ?>(rowName))
                                        {
                                           while (checkForDuplicateRowOnTable<?php echo($index); ?>(rowName))
                                           {
                                              rowName = rowName + ' (2)';
                                           }
                                        }
                                        
                                        addRowToTable<?php echo($index); ?>(rowName, rowTypeName, thisIndex);
                                        
                                        if(<?php foreach($this->extraFieldTypes as $efi => $eft): ?><?php if($eft['hasOptions']): ?>rowType == <?php echo($efi); ?> || <?php endif; ?><?php endforeach; ?> false)
                                        {
                                            addOptionsAreaToTable<?php echo($index); ?>(thisIndex, rowName);
                                        }
                                        
                                        appendCommandList('ADDFIELD <?php echo(urlencode($data['type'])); ?> '+encodeURI(rowType)+' '+encodeURI(rowName));
                                    }
                                    
                                    //TODO: Document me.
                                    function deleteRow<?php echo($index); ?>(rowIndex, rowName)
                                    {                                      
                                        //FIXME: Do we really need this?
                                        /*
                                        var c= confirm("Do you really want to delete the field "+rowName+"?");
                                        if (!c)
                                        {
                                            return;
                                        }
                                        */
                                        
                                        deleteRowFromTable<?php echo($index); ?>(rowIndex);
                                        
                                        appendCommandList('DELETEFIELD <?php echo(urlencode($data['type'])); ?> '+encodeURI(rowName));
                                    }

                                    //TODO: Document me.
                                    function swapRows<?php echo($index); ?>(rowIndex1, rowIndex2)
                                    {
                                        if (rowIndex2 < 0 || rowIndex2 > document.getElementById('extraFieldsTable<?php echo($index); ?>').rows.length - 2)
                                        {
                                            return;
                                        }
                                        
                                        var rowName1 = getRowNameByRowIndex<?php echo($index); ?>(rowIndex1);
                                        var rowName2 = getRowNameByRowIndex<?php echo($index); ?>(rowIndex2);
                                        tbl = document.getElementById('extraFieldsTable<?php echo($index); ?>');
                                        
                                        var row = tbl.rows[rowIndex1+1];
                                        var col = row.getElementsByTagName('td')[1];
                                        var html = col.innerHTML;                     

                                        var row2 = tbl.rows[rowIndex2+1];
                                        var col2 = row2.getElementsByTagName('td')[1];
                                        var html2 = col2.innerHTML;
                                        
                                        while(html.indexOf("row"+rowIndex1) != -1)
                                        {
                                            html = html.replace("row"+rowIndex1, "row"+rowIndex2);
                                        }
                                        
                                        while(html.indexOf("deleteOption<?php echo($index); ?>("+(rowIndex1+1)) != -1)
                                        {                                        
                                            html = html.replace("deleteOption<?php echo($index); ?>("+(rowIndex1+1), "deleteOption<?php echo($index); ?>("+(rowIndex2+1));
                                        }
                                        
                                        while(html2.indexOf("row"+rowIndex2) != -1)
                                        {
                                            html2 = html2.replace("row"+rowIndex2, "row"+rowIndex1);
                                        }                                        

                                        while(html2.indexOf("deleteOption<?php echo($index); ?>("+(rowIndex1+1)) != -1)
                                        {                                        
                                            html2 = html2.replace("deleteOption<?php echo($index); ?>("+(rowIndex1+1), "deleteOption<?php echo($index); ?>("+(rowIndex2+1));
                                        }
                                        col.innerHTML = html2;
                                        col2.innerHTML = html;    
                                        
                                        appendCommandList('SWAPFIELDS <?php echo(urlencode($data['type'])); ?> '+encodeURI(rowName1)+':'+encodeURI(rowName2));
                                    }

                                    //TODO: Document me.
                                    function editRow<?php echo($index); ?>(rowIndex)
                                    {
                                         var rowName = getRowNameByRowIndex<?php echo($index); ?>(rowIndex);
                                         
                                         tbl = document.getElementById('extraFieldsTable<?php echo($index); ?>');

                                         var row = tbl.rows[rowIndex+1];
                                         var col = row.getElementsByTagName('td')[1];
                                         var html = col.innerHTML;                     

                                        inlineEditIDCounter++;

                                        html = html.replace(rowName, "<!--inline--><input type=\"hidden\" id=\"inlineEditOrg"+inlineEditIDCounter+"\"><input id=\"inlineEdit"+inlineEditIDCounter+"\"><input type=\"button\" onclick=\"saveRow<?php echo($index); ?>("+rowIndex+", "+inlineEditIDCounter+")\" value=\"Save\"><!--/inline-->");

                                        col.innerHTML = html;
                                         
                                        document.getElementById('inlineEdit'+inlineEditIDCounter).value = rowName;
                                        document.getElementById('inlineEditOrg'+inlineEditIDCounter).value = rowName;
                                     }

                                     //TODO: Document me.
                                     function saveRow<?php echo($index); ?>(rowIndex, inlineID)
                                     {
                                          var rowNameOrg = document.getElementById('inlineEditOrg'+inlineID).value;
                                          var rowNameNew = document.getElementById('inlineEdit'+inlineID).value;
                                          
                                          appendCommandList('RENAMEROW <?php echo(urlencode($data['type'])); ?> '+encodeURI(rowNameOrg)+':'+encodeURI(rowNameNew));

                                          document.getElementById('editSettingsForm').submit();
                                      }                                    
                                    
                                    //TODO: Document me.
                                    function onAddField<?php echo($index); ?>()
                                    {
                                       if(document.getElementById('addFieldName<?php echo($index); ?>').value == '')
                                       {
                                          return;
                                       }
                                       
                                        addRow<?php echo($index); ?>(document.getElementById('addFieldName<?php echo($index); ?>').value, 
                                                                     document.getElementById('addFieldSelect<?php echo($index); ?>').value, 
                                                                     document.getElementById('addFieldSelect<?php echo($index); ?>').options[
                                                                            document.getElementById('addFieldSelect<?php echo($index); ?>').selectedIndex
                                                                        ].text
                                                                    );
                                                                    
                                        onHideAddArea<?php echo($index); ?>();                             
                                    }
                                    
                                    //TODO: Document me.
                                    function addOption<?php echo($index); ?>(optionName, tbl, fieldName)
                                    {   
                                        if (optionName == '')
                                        {
                                           return;
                                        }
                                        
                                        addOptionToTable<?php echo($index); ?>(optionName, tbl, fieldName);
                                        
                                        appendCommandList('ADDOPTION <?php echo(urlencode($data['type'])); ?> '+encodeURI(fieldName)+':'+encodeURI(optionName));
                                    }
                                    
                                    //TODO: Document me.
                                    function deleteOption<?php echo($index); ?>(rowIndex, optionIndex, optionName, fieldName)
                                    {   
                                        tbl = document.getElementById('extraFieldsTable<?php echo($index); ?>');
                                        
                                        var row = tbl.rows[rowIndex];
                                        var cell = row.getElementsByTagName('td')[1];
                                        
                                        var tblOptions = cell.getElementsByTagName('table')[0];
                                        var rowOptions = tblOptions.rows[optionIndex];
                                        
                                        deleteOptionFromTable<?php echo($index); ?>(rowOptions, optionName, fieldName);
                                        
                                        appendCommandList('DELETEOPTION <?php echo(urlencode($data['type'])); ?> '+encodeURI(fieldName)+':'+encodeURI(optionName));
                                    }                                    
                                    
                                    //TODO: Document me.
                                    function onHideAddArea<?php echo($index); ?>()
                                    {
                                        document.getElementById('addField<?php echo($index); ?>').style.display='none'; 
                                        document.getElementById('addFieldOption<?php echo($index); ?>').style.display='';
                                    }
                                </script>
                                
                                <table class="sortable" width="560" id="extraFieldsTable<?php echo($index); ?>">
                                    <thead>
                                        <tr>
                                            <th width="75">
                                            </th>
                                            <th align="left" width="325" nowrap="nowrap">
                                                Field Name
                                            </th>
                                            <th align="left">
                                                Field Type
                                            </th>
                                        </tr>
                                    </thead>
                                    <?php foreach($data['RS'] as $rsIndex => $rsData): ?>
                                        <tr class="<?php TemplateUtility::printAlternatingRowClass($rsIndex); ?>" id="table<?php echo($index); ?>row<?php echo($rsIndex); ?>">
                                            <td>
                                                <a href="javascript:void(0);" onclick="deleteRow<?php echo($index); ?>(<?php echo($rsIndex); ?>, urlDecode('<?php echo(urlencode($rsData['fieldName'])); ?>'));"  style="padding:0px;">
                                                    <img src="images/actions/delete.gif" border="0" style="padding:0px;"/>
                                                </a>
                                                <a href="javascript:void(0);" onclick="swapRows<?php echo($index); ?>(<?php echo($rsIndex); ?>, <?php echo($rsIndex-1); ?>);"  style="padding:0px;">
                                                    <img src="images/scrollTop.jpg" border="0"  style="padding:0px;"/>
                                                </a>                                                 
                                                <a href="javascript:void(0);" onclick="swapRows<?php echo($index); ?>(<?php echo($rsIndex); ?>, <?php echo($rsIndex+1); ?>);"  style="padding:0px;">
                                                    <img src="images/scrollBottom.jpg" border="0"  style="padding:0px;"/>
                                                </a>
                                                <a href="javascript:void(0);" onclick="editRow<?php echo($index); ?>(<?php echo($rsIndex); ?>);"  style="padding:0px;">
                                                    <img src="images/edit.gif" border="0"  style="padding:0px;"/>
                                                </a>
                                            </td>
                                            <td align="left">
                                                <?php $this->_($rsData['fieldName']); ?>
                                            </td>
                                            <td align="left">
                                                <?php $this->_($this->extraFieldTypes[$rsData['extraFieldType']]['name']); ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </table>
                                <?php foreach($data['RS'] as $rsIndex => $rsData): ?>
                                    <?php if ($this->extraFieldTypes[$rsData['extraFieldType']]['hasOptions']): ?>
                                       <script type="text/javascript">
                                          t = addOptionsAreaToTable<?php echo($index); ?>(<?php echo($rsIndex); ?>, urlDecode("<?php echo (urlencode($rsData['fieldName'])); ?>"));
                                          <?php $options = explode(',', $rsData['extraFieldOptions']); ?>
                                          <?php foreach($options as $option): ?>
                                             <?php if($option != ''): ?>
                                                addOptionToTable<?php echo($index); ?>(urlDecode("<?php echo ($option); ?>"), t, urlDecode("<?php echo (urlencode($rsData['fieldName'])); ?>"));
                                             <?php endif; ?>   
                                          <?php endforeach; ?>
                                       </script>
                                    <?php endif; ?>   
                                <?php endforeach; ?>
                                                                
                                <div id="addField<?php echo($index); ?>" style="display:none;">
                                    <table>
                                        <tr>
                                            <td>
                                                Name:
                                            </td>
                                            <td>
                                                <input id="addFieldName<?php echo($index); ?>" style="width:240px;" value="" class="inputbox" />
                                            </td>
                                            <td>
                                                Type:
                                            </td>
                                            <td>
                                                <select id="addFieldSelect<?php echo($index); ?>">
                                                  <?php foreach($this->extraFieldTypes as $extraFieldTypeIndex => $extraFieldTypeData): ?>
                                                    <option value="<?php echo($extraFieldTypeIndex); ?>"><?php $this->_($extraFieldTypeData['name']); ?></option>
                                                  <?php endforeach; ?>
                                               </select>
                                            </td>
                                        </tr>
                                    </table>                                    
                                    <input type="button" class="button" value="Add Field" onclick="onAddField<?php echo($index); ?>();" />&nbsp;
                                    <input type="button" class="button" value="Cancel" onclick="onHideAddArea<?php echo($index); ?>();" />
                                </div>
                                <div id="addFieldOption<?php echo($index); ?>">
                                    <a href="javascript:void(0);" onclick="document.getElementById('addField<?php echo($index); ?>').style.display=''; document.getElementById('addFieldOption<?php echo($index); ?>').style.display='none'; document.getElementById('addFieldName<?php echo($index); ?>').value=''; document.getElementById('addFieldName<?php echo($index); ?>').focus();">
                                        <img src="images/actions/add_small.gif" border="0" />&nbsp;Add field to <?php echo($data['name']); ?>
                                    </a>
                                </div>
                                <br />
                                <br />
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    
                </table>
                <input type="submit" class="button" value="Save" style="display:none;" id="buttonSave" />
                <input type="button" name="back" class = "button" value="Done" id="buttonDone"  onclick="document.location.href='<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=administration';" />
            </form>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
