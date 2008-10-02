<?php /* $Id: Error.tpl 67 2007-01-17 05:02:33Z will $ */ ?>
<?php TemplateUtility::printHeader('Skype'); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
    <div id="header">
        <ul id="primary">
            <?php TemplateUtility::printTabs($this->active); ?>
        </ul>
    </div>

    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/job_orders.gif" width="24" height="24" border="0" alt="Job Orders" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Skype: Error</h2></td>
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
