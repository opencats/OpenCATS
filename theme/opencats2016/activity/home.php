<?php get_header(); ?>
    <div id="main" class="home">
        <?php TemplateUtility::printQuickSearch(); ?>
        <div id="contents" style="padding-top: 10px;">
            <?php if ($numActivities): ?>
            <table width="100%">
                <tr>
                    <td width="3%">
                        <img src="images/activities.gif" width="24" height="24" alt="Activities" style="border: none; margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Activities</h2></td>
                    <td align="right">
                        <?php $dataGrid->printNavigation(false); ?>&nbsp;&nbsp;<?php echo($quickLinks); ?>
                    </td>
                </tr>
            </table>

            <p class="note">
                <span style="float:left;">Activities - Page <?php echo($dataGrid->getCurrentPageHTML()); ?></span>
                <span style="float:right;">
                    <?php $dataGrid->drawRowsPerPageSelector(); ?>
                    <?php $dataGrid->drawShowFilterControl(); ?>
                </span>&nbsp;
            </p>

            <?php $dataGrid->drawFilterArea(); ?>
            <?php $dataGrid->draw();  ?>

            <div style="display:block;">
                <span style="float:left;">
                    <?php $dataGrid->printActionArea(); ?>
                </span>
                <span style="float:right;">
                    <?php $dataGrid->printNavigation(true); ?>
                </span>&nbsp;
            </div>

            <?php else: ?>

            <br /><br /><br /><br />
            <div style="height: 95px; background: #E6EEFF url(images/nodata/activitiesTop.jpg);">
                &nbsp;
            </div>
            <br /><br />
            <table cellpadding="0" cellspacing="0" border="0" width="956">
                <tr>
                <td style="padding-left: 62px;" align="center" valign="center">

                    <div style="text-align: center; width: 700px; line-height: 22px; font-size: 18px; font-weight: bold; color: #666666; padding-bottom: 20px;">
                    Activities are automatically recorded based on actions you perform.
                    </div>
                </td>

                </tr>
            </table>

            <?php endif; ?>
        </div>
    </div>
<?php get_footer(); ?>
