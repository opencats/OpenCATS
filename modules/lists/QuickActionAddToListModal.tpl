<?php /* $Id: QuickActionAddToListModal.tpl 3198 2007-10-14 23:36:43Z will $ */ ?>
<?php TemplateUtility::printModalHeader('Candidates', array('js/lists.js'), 'Add to '.$this->dataItemDesc.' Static Lists'); ?>
    <table>
        <tr>
            <td><!--Add to Static Lists-->Select the lists you want to add the item<?php if (count($this->dataItemIDArray) > 1): ?>s<?php endif; ?> to.</td>
       </tr>
    </table>
            <div class="addToListListBox" id="addToListBox">
                <input type="hidden" style="width:200px;" id="dataItemArray" value="<?php $this->_(implode(',', $this->dataItemIDArray)); ?>">
                <?php foreach($this->savedListsRS as $index => $data): ?>
                    <div class="<?php TemplateUtility::printAlternatingDivClass($index); ?>" id="savedListRow<?php echo($data['savedListID']); ?>">
                        <span style="float:left;">
                            <input type="checkbox" id="savedListRowCheck<?php echo($data['savedListID']); ?>">
                            &nbsp;
                            <span id="savedListRowDescriptionArea<?php echo($data['savedListID']); ?>"><?php $this->_($data['description']); ?></span>&nbsp;(<?php echo($data['numberEntries']); ?>)
                        </span>
                        <span style="float:right; padding-right:25px;">
                            <a href="javascript:void(0);" onclick="editListRow(<?php echo($data['savedListID']); ?>);" style="text-decoration:none;"><img src="images/actions/edit.gif" border="0">&nbsp;Edit</a>
                        </span>
                    </div>
                    <div class="<?php TemplateUtility::printAlternatingDivClass($index); ?>" style="display:none;" id="savedListRowEditing<?php echo($data['savedListID']); ?>">
                        <span style="float:left;">
                            <input class="inputbox" style="width:220px; padding-left:5px; margin-top:2px;" value="<?php $this->_($data['description']); ?>" id="savedListRowInput<?php echo($data['savedListID']); ?>">
                        </span>
                        <span style="float:right; padding-right:25px;">
                            <a href="javascript:void(0);" onclick="deleteListRow(<?php echo($data['savedListID']); ?>, '<?php echo($this->sessionCookie); ?>', <?php echo($data['numberEntries']); ?>);" style="text-decoration:none;"><img src="images/actions/delete.gif" border="0">&nbsp;Delete</a>&nbsp;
                            <a href="javascript:void(0);" onclick="saveListRow(<?php echo($data['savedListID']); ?>, '<?php echo($this->sessionCookie); ?>');" style="text-decoration:none;"><img src="images/actions/screen.gif" border="0">&nbsp;Save</a>
                        </span>
                    </div>
                    <div class="<?php TemplateUtility::printAlternatingDivClass($index); ?>" style="display:none;" id="savedListRowAjaxing<?php echo($data['savedListID']); ?>">
                        <img src="images/indicator.gif">&nbsp;Saving Changes, Please Wait...
                    </div>
                <?php endforeach; ?>
                <div class="<?php TemplateUtility::printAlternatingDivClass(count($this->savedListsRS)); ?>" style="display:none;" id="savedListNew">
                    <span style="float:left;">
                        <input class="inputbox" style="width:220px; padding-left:5px; margin-top:2px;" value="" id="savedListNewInput">
                    </span>
                    <span style="float:right; padding-right:25px;">
                        <a href="javascript:void(0);" onclick="document.getElementById('savedListNew').style.display='none';" style="text-decoration:none;"><img src="images/actions/delete.gif" border="0">&nbsp;Delete</a>&nbsp;
                        <a href="javascript:void(0);" onclick="commitNewList('<?php echo($this->sessionCookie); ?>', <?php echo($this->dataItemType); ?>);" style="text-decoration:none;"><img src="images/actions/screen.gif" border="0">&nbsp;Save</a>
                    </span>
                </div>
                <div class="<?php TemplateUtility::printAlternatingDivClass(count($this->savedListsRS)); ?>" style="display:none;" id="savedListNewAjaxing">
                    <img src="images/indicator.gif">&nbsp;Saving Changes...
                </div>
            </div>
            <br />
            <div style="float:right;" id="actionArea">
                <input type="button" class="button" value="New List" onclick="addListRow();">&nbsp;
                <input type="button" class="button" value="Add To Lists" onclick="addItemsToList('<?php echo($this->sessionCookie); ?>', <?php echo($this->dataItemType); ?>);">&nbsp;
                <input type="button" class="button" value="Cancel" onclick="parentHidePopWin();">&nbsp;
            </div>
            <div style="display:none; font: normal normal normal 12px/130% Arial, Tahoma, sans-serif;" id="addingToListAjaxing">
                <img src="images/indicator.gif">&nbsp;Adding to Lists, Please Wait <?php if (count($this->dataItemIDArray) > 20): ?>(This could take awhile)<?php endif; ?>...
            </div>
            <div style="display:none; font: normal normal normal 12px/130% Arial, Tahoma, sans-serif;" id="addingToListAjaxingComplete">
                <img src="images/indicator.gif">&nbsp;Items have been added to lists successfully.
            </div>
            <script type="text/javascript">
                function relabelEvenOdd()
                {
                    var onEven = 1;
                    <?php foreach($this->savedListsRS as $index => $data): ?>
                        if (document.getElementById("savedListRow<?php echo($data['savedListID']); ?>").style.display == '' || 
                            document.getElementById("savedListRowEditing<?php echo($data['savedListID']); ?>").style.display == '' || 
                            document.getElementById("savedListRowAjaxing<?php echo($data['savedListID']); ?>").style.display == '')
                        {
                            if (onEven == 1)
                            {
                                document.getElementById("savedListRow<?php echo($data['savedListID']); ?>").className = 'evenDivRow';
                                document.getElementById("savedListRowEditing<?php echo($data['savedListID']); ?>").className = 'evenDivRow';
                                document.getElementById("savedListRowAjaxing<?php echo($data['savedListID']); ?>").className = 'evenDivRow';
                                onEven = 0;
                            }
                            else
                            {
                                document.getElementById("savedListRow<?php echo($data['savedListID']); ?>").className = 'oddDivRow';
                                document.getElementById("savedListRowEditing<?php echo($data['savedListID']); ?>").className = 'oddDivRow';
                                document.getElementById("savedListRowAjaxing<?php echo($data['savedListID']); ?>").className = 'oddDivRow';
                                onEven = 1;
                            }
                        }
                    <?php endforeach; ?>
                    if (onEven == 1)
                    {
                        document.getElementById("savedListNew").className = 'evenDivRow';
                        document.getElementById("savedListNewAjaxing").className = 'evenDivRow';
                    }
                    else
                    {
                        document.getElementById("savedListNew").className = 'oddDivRow';
                        document.getElementById("savedListNewAjaxing").className = 'oddDivRow';
                    }
                }
                function getCheckedBoxes()
                {
                    var checked='';
                     <?php foreach($this->savedListsRS as $index => $data): ?>
                        if (document.getElementById("savedListRowCheck<?php echo($data['savedListID']); ?>").checked)
                        {
                            checked += "<?php echo($data['savedListID']); ?>,";
                        }
                    <?php endforeach; ?>  
                    return checked;                 
                }
            </script>
    </body>
</html>
