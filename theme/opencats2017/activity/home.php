<?php get_header(); ?>
    <div class="content">
        <div id="contents" style="padding-top: 10px;">
            <?php if ($numActivities): ?>
            <div class="hpanel">
                <div class="panel-heading">
                    Activities
                </div>
                <div class="panel-body">
                    <?php $dataGrid->printNavigation(false); ?>&nbsp;&nbsp;<?php echo($quickLinks); ?>
                    <div class="row">
                        <div class="col-md-6">
                            Activities - Page <?php echo($dataGrid->getCurrentPageHTML()); ?>
                        </div>
                        <div class="col-md-6 text-right">
                            <?php $dataGrid->drawRowsPerPageSelector(); ?>
                            <?php $dataGrid->drawShowFilterControl(); ?>
                        </div>
                    </div>
                    <?php $dataGrid->drawFilterArea(); ?>
                    <?php $dataGrid->draw();  ?>
                </div>
                <div class="panel-footer">
                    <div class="row">
                        <div class="col-md-6">
                            <?php $dataGrid->printActionArea(); ?>
                        </div>
                        <div class="col-md-6 text-right">
                            <?php $dataGrid->printNavigation(true); ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div class="hpanel">
                <div class="panel-heading">
                    Activities
                </div>
                <div class="panel-body">
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
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
<?php get_footer(); ?>
