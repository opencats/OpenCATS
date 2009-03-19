<?php /* $Id: Error.tpl 3078 2007-09-21 20:25:28Z will $ */ ?>
<?php TemplateUtility::printHeader(__('Reports')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>

    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/reports.gif" width="24" height="24" border="0" alt="Reports" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2><?php _e('Reports') ?>: <?php _e('Error') ?></h2></td>
                </tr>
            </table>

            <p class="fatalError">
                <?php _e('A fatal error has occurred.') ?><br />
                <br />
                <?php echo($this->errorMessage); ?>
            </p>
        </div>

	</div>
    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>