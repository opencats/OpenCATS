<?php /* $Id: CustomizeDashboard.tpl 2424 2007-05-03 07:15:02Z will $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('modules/settings/validator.js', 'modules/settings/Settings.js')); ?>
<?php 
if (MYTABPOS == 'top') {
	osatutil::TabsAtTop();
	TemplateUtility::printTabs($this->active);
}
?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table width="100%">
                <tr>
                    <td width="3%">
                        <img src="images/settings.gif" width="24" height="24" border="0" alt="Settings" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td align="left"><h2>Settings: Customization</h2></td>
                </tr>
            </table>

            <p class="note">Customize Home Dashboard</p>

            <table width="100%">
                <tr>
                    <td width="50%" valign="top">
                        <form name="addComponentForm" id="addComponentForm" action="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=customizeDashboard&amp;command=addComponent" method="post" autocomplete="off">
                            <input type="hidden" name="postback" value="postback" />
                            <input type="hidden" name="columnPosition" id="add_columnPosition" value="" />
                            <input type="hidden" name="columnID" id="add_columnID" value="" />

                            <table class="searchTable" width="100%">
                                <tr>
                                    <td>
                                        <div>
                                            <p class="noteUnsized" width="100%">Add A Component</p>
                                            <select name="moduleName" id="add_moduleName" style="width:200px;" onchange="customizeDashboard_showAddComponent(this.value);">
                                                <option value="">(Select a Component Type)</option>
                                                <?php foreach ($this->dashboardModules as $name => $properties): ?>
                                                    <option value="<?php echo($name); ?>"><?php echo($properties['title']); ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <br /><br />
                                        </div>
                                        
                                        <?php foreach ($this->dashboardModules as $name => $properties): ?>
                                            <div style="display: none;" id="addComponent_<?php echo($name); ?>">
                                                <br />
                                                <img src="<?php echo($properties['previewImage']); ?>" />
                                                <br /><br />
                                                <?php echo($properties['description']); ?>
                                                <br /><br />
                                                To insert this object, click a column to insert it into on the right.
                                            </div>
                                        <?php endforeach; ?>
                                    </td>
                                </tr>
                            </table>
                        </form>
                        
                        <?php foreach ($this->dashboardComponentsRS as $data): ?>
                            <div <?php if (isset($this->focusComponentID) && $this->focusComponentID == $data['dashboardComponentID']):?>style=""<?php else: ?>style="display:none;"<?php endif; ?> id="editComponent<?php echo($data['dashboardComponentID']); ?>">
                                <br />
                                <table class="searchTable" width="100%">
                                    <tr>
                                        <td>
                                            <p style="font-weight: bold;" width="100%">Edit <?php echo($data['moduleTitle']); ?></p>
                                            <br />
                                            <input type="button" class="button" value="Move Object Up" onclick="customizeDashboard_moveComponent(<?php echo($data['dashboardComponentID']); ?>, -1, <?php echo($data['columnID']); ?>);" />&nbsp;
                                            <input type="button" class="button" value="Move Object Down" onclick="customizeDashboard_moveComponent(<?php echo($data['dashboardComponentID']); ?>, 1, <?php echo($data['columnID']); ?>);" />&nbsp;
                                            <input type="button" class="button" value="Remove Object" onclick="customizeDashboard_removeComponent(<?php echo($data['dashboardComponentID']); ?>);" />&nbsp;
                                            <br />
                                            
                                            <?php if (!empty($data['moduleParameterFields'])): ?>
                                                <form action="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=customizeDashboard&amp;command=modifyComponent" method="post" autocomplete="off">
                                                    <input type="hidden" name="postback" value="postback" />
                                                    <input type="hidden" name="fieldCount" value="<?php echo(count($data['moduleParameterFields'])); ?>" />
                                                    <input type="hidden" name="dashboardComponentID" value="<?php echo($data['dashboardComponentID']); ?>" />
                                                    
                                                    <br />
                                                    <table border="0">
                                                        <?php foreach ($data['moduleParameterFields'] as $index => $field): ?>
                                                            <tr>
                                                                <?php if ($field['type'] == 'null'): ?>
                                                                
                                                                    <td><?php echo($data['moduleParameterFields'][$index]['label']); ?></td>

                                                                <?php elseif ($field['type'] == 'graphColorPicker'): ?>
                                                                
                                                                    <td><?php echo($data['moduleParameterFields'][$index]['label']); ?></td>
                                                                    
                                                                    <td>
                                                                        <select name="field<?php echo($index); ?>">                                                                        
                                                                            <?php foreach ($this->colorOptions as $colorIndex => $value): ?>
                                                                                <option value="<?php echo($colorIndex); ?>" <?php if (isset($data['moduleParameterValues'][$index]) && $colorIndex == $data['moduleParameterValues'][$index]): ?>selected="selected"<?php endif; ?>>
                                                                                    <?php echo($colorIndex); ?>
                                                                                </option>
                                                                            <?php endforeach; ?>
                                                                        </select>
                                                                    </td>

                                                                <?php elseif ($field['type'] == 'textInputShort'): ?>
                                                                
                                                                    <td>
                                                                        <?php echo($data['moduleParameterFields'][$index]['label']); ?>
                                                                        <input type="text" name="field<?php echo($index); ?>" value="<?php if (isset($data['moduleParameterValues'][$index])) $this->_($data['moduleParameterValues'][$index]); ?>" style="width: 80px;" />
                                                                    </td>
                                                                
                                                                <?php elseif ($field['type'] == 'textInputMultiline'): ?>
                                                                
                                                                    <td>
                                                                        <?php echo($data['moduleParameterFields'][$index]['label']); ?><br />
                                                                        <textarea name="field<?php echo($index); ?>" rows="5" style="width:400px;"><?php if (isset($data['moduleParameterValues'][$index])) $this->_($data['moduleParameterValues'][$index]); ?></textarea>
                                                                    </td>
                                                                    
                                                                <?php elseif ($field['type'] == 'textInput'): ?>
                                                                
                                                                    <td>
                                                                        <?php echo($data['moduleParameterFields'][$index]['label']); ?>
                                                                        
                                                                        <input type="text" name="field<?php echo($index); ?>" value="<?php if (isset($data['moduleParameterValues'][$index])) $this->_($data['moduleParameterValues'][$index]); ?>" style="width: 300px;" />
                                                                    </td>
                                                                    
                                                                <?php endif; ?>
                                                            </tr>
                                                        <?php endforeach; ?>
                                                    </table>
                                                    <br clear="all" />
                                                    
                                                    <input type="submit" name="submit" class="button" value="Save Parameters" />&nbsp;
                                                </form>
                                            <?php endif; ?>
                                            
                                            <br />
                                            <br />
                                            <span>Preview:</span>
                                            <table style="text-align: left; margin-top: 8px; width: 100%; background-color: #fff;" class="selectView">
                                                <tr>
                                                    <td><?php echo($data['outputHTMLPreview']); ?></td>
                                                </tr>
                                            </table>
                                        </td>
                                    </tr>
                                </table>
                            </div>
                        <?php endforeach; ?>
                    </td>
                    
                    <td width="50%" valign="top"><!-- style="border-right: 1px solid #000;" -->
                        <table class="searchTable" width="100%">
                            <tr>
                                <td>
                                    <p class="noteUnsized" width="100%">Edit Dashboard</p>
                                    
                                    <p>Click an object to edit or move it.</p>
                                    
                                    <table style="clear: both; margin-top: 8px; width: 100%; background-color: #fff; border-color: #000; border-style: solid;  border-width: 1px;">
                                        <tr>
                                            <td valign="top" style="border-color: #000; border-style: solid;  border-width: 1px; padding:5px;">
                                                <u>Dashboard Left Column:</u>
                                                <br /><br />
                                                
                                                <?php $countRow1 = 0; ?>
                                                <?php foreach ($this->dashboardComponentsRS as $data): ?>
                                                    <?php if ($data['columnID'] == 0): ?>
                                                        <div class="itemCell">
                                                            <a href="javascript:void(0);" onclick="customizeDashboardViewEdit(<?php echo($data['dashboardComponentID']); ?>);">
                                                                <?php echo($data['moduleTitle']); ?>
                                                            </a>
                                                        </div>
                                                        <?php $countRow1++; ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>

                                                <br />
                                                <input type="button" class="button" style="display:none;" id="addComponentB0" value="Add Component Here" onclick="customizeDashboard_addToColumn(0, <?php echo($countRow1); ?>);" />
                                            </td>
                                            
                                            <td valign="top" style="border-color: #000; border-style: solid;  border-width: 1px; padding:5px;">
                                                <u>Dashboard Right Column:</u>
                                                <br /><br />
                                                
                                                <?php $countRow2 = 0; ?>
                                                <?php foreach ($this->dashboardComponentsRS as $data): ?>
                                                    <?php if ($data['columnID'] == 1): ?>
                                                        <div class="itemCell">
                                                            <a href="javascript:void(0);" onclick="customizeDashboardViewEdit(<?php echo($data['dashboardComponentID']); ?>);">
                                                                <?php echo($data['moduleTitle']); ?>
                                                            </a>
                                                        </div>
                                                        <?php $countRow2++; ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                                <br />
                                                <input type="button" class="button" style="display:none;" id="addComponentB1" value="Add Component Here" onclick="customizeDashboard_addToColumn(1, <?php echo($countRow2); ?>);" />
                                            </td>
                                        </tr>
                                    </table>
                                    
                                    <table style="margin-top: 8px; width: 100%; background-color: #fff; border-color: #000; border-style: solid;  border-width: 1px;">
                                        <tr>
                                            <td valign="top" style="border-color: #000; border-style: solid;  border-width: 1px; padding:5px;">
                                                <u>Dashboard Lower Area:</u>
                                                <br /><br />
                                                
                                                <?php $countRow3 = 0; ?>
                                                <?php foreach ($this->dashboardComponentsRS as $data): ?>
                                                    <?php if ($data['columnID'] == 2): ?>
                                                        <span class="itemCell">
                                                            <a href="javascript:void(0);" onclick="customizeDashboardViewEdit(<?php echo($data['dashboardComponentID']); ?>);">
                                                                <?php echo($data['moduleTitle']); ?>
                                                            </a>
                                                        </span>
                                                        <?php $countRow3++; ?>
                                                    <?php endif; ?>
                                                <?php endforeach; ?>
                                                <br />
                                                <input type="button" class="button" style="display:none;" id="addComponentB2" value="Add Component Here" onclick="customizeDashboard_addToColumn(2, <?php echo($countRow3); ?>);" />
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                 </tr>
            </table>
            <br /><br />
            
            <div style="text-align: center;">
                <input type="button" class="button" value="Finished" onclick="document.location.href='<?php echo(osatutil::getIndexName()); ?>?m=home';" />
            </div>
        </div>
<?php
if (MYTABPOS == 'bottom') 
{
    
	TemplateUtility::printTabs($this->active);
	?>
	</div>
    <div id="bottomShadow"></div>
    
    <?php 
	osatutil::TabsAtBottom();
}else{
	?>
	</div>
    <div id="bottomShadow"></div>
    <?php 
}
?>
<?php TemplateUtility::printFooter(); 
		
?>

    
    <form action="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=customizeDashboard&amp;command=removeComponent" id="removeComponentForm" method="post" autocomplete="off">
         <input type="hidden" name="postback" value="postback" />
         <input type="hidden" name="dashboardComponentID" id="remove_componentID" value="" />
    </form>
    
    <form action="<?php echo(osatutil::getIndexName()); ?>?m=settings&amp;a=customizeDashboard&amp;command=moveComponent" id="moveComponentForm" method="post" autocomplete="off">
         <input type="hidden" name="postback" value="postback" />
         <input type="hidden" name="dashboardComponentID" id="move_componentID" value="" />
         <input type="hidden" name="newComponentPosition" id="move_newComponentPosition" value="" />
         <input type="hidden" name="componentColumn" id="move_componentColumn" value="" />
    </form>
    
    <script type="text/javascript">
        function customizeDashboard_hideAll()
        {
            <?php foreach ($this->dashboardModules as $name => $properties): ?>
                document.getElementById('addComponent_<?php echo($name); ?>').style.display = 'none';
            <?php endforeach; ?>
            
            <?php foreach ($this->dashboardComponentsRS as $data): ?>
                document.getElementById('editComponent<?php echo($data['dashboardComponentID']); ?>').style.display = 'none';
            <?php endforeach; ?>
        }
    </script>
