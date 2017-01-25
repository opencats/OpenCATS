<?php /* $Id: MassImport.tpl 3548 2007-11-09 23:54:52Z andrew $ */ ?>
<?php TemplateUtility::printHeader('Settings', array('js/massImport.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, '', 'settings'); ?>
<link rel="stylesheet" type="text/css" href="modules/import/MassImport.css" />
    <div id="main">
        <div id="contents">
            <div style="width: 880px; padding: 15px;">
                <table cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td class="stepColumn<?php if ($this->step == 1): ?>Selected<?php endif; ?>">
                            <span style="font-size: 18px; font-weight: bold;">Step 1</span>
                            <br />
                            Upload resume documents
                        </td>
                        <td class="stepColumn<?php if ($this->step == 2): ?>Selected<?php endif; ?>">
                            <span style="font-size: 18px; font-weight: bold;">Step 2</span>
                            <br />
                            Process Documents
                        </td>
                        <td class="stepColumn<?php if ($this->step == 3): ?>Selected<?php endif; ?>">
                            <span style="font-size: 18px; font-weight: bold;">Step 3</span>
                            <br />
                            Review
                        </td>
                        <td class="stepColumn<?php if ($this->step == 4): ?>Selected<?php endif; ?>">
                            <span style="font-size: 18px; font-weight: bold;">Step 4</span>
                            <br />
                            Finish Up
                        </td>
                    </tr>
                </table>

                <table cellpadding="0" cellspacing="0" border="0" width="100%">
                    <tr>
                        <td width="29" height="29"><img src="images/parser/statusBottomLeft.jpg" border="0" /></td>
                        <td width="100%" height="29" class="softMiddle">
                            &nbsp;
                        </td>
                        <td width="29" height="29"><img src="images/parser/statusBottomRight.jpg" border="0" /></td>
                    </tr>
                </table>

                <?php if (isset($this->errorMessage)): ?>
                    <div class="stepContainer">
                    <img src="images/friendly_error.jpg" border="0" align="left" />
                    <span style="font-size: 16px;">
                    <?php echo $this->errorMessage; ?>
                    </span>
                    </div>
                <?php else: ?>
                    <?php echo $this->subTemplateContents; ?>
                <?php endif; ?>
            </div>


        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
