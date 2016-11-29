<?php 
/**
* Duplicates errors template
* @package OpenCATS
* @subpackage modules/duplicates
* @copyright (C) OpenCats
* @license GNU/GPL, see license.txt
* OpenCATS is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License 2
* as published by the Free Software Foundation.
*/
?>
<?php TemplateUtility::printHeader('Duplicates'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/contact.gif" width="24" height="24" border="0" alt="Contacts" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Duplicates: Error</h2></td>
                </tr>
            </table>

            <p class="fatalError">
                A fatal error has occurred.<br />
                <br />
                <?php echo($this->errorMessage); ?>
            </p>
        </div>
    </div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>
