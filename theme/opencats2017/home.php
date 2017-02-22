<?php get_header(); ?>
<div class="content">
<?php TemplateUtility::printQuickSearch(); ?>
    <div class="row">
        <div class="col-md-12">
            <div class="hpanel">
                <div class="panel-heading">
                    My Recent Calls
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <tr>
                            <td align="left" valign="top" style="text-align: left; height:50px;">
                                <div class="noteUnsizedSpan"></div>
                                <?php $dataGrid2->drawHTML();  ?>
                            </td>

                            <td align="center" valign="top" style="text-align: left; font-size:11px; height:50px;">
                                <?php echo($upcomingEventsFupHTML); ?>
                            </td>

                            <td align="center" valign="top" style="text-align: left;font-size:11px; height:50px;">
                                <?php echo($upcomingEventsHTML); ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="hpanel">
                <div class="panel-heading">
                    Recent Hires
                </div>
                <div class="panel-body">
                    <table class="sortable table table-striped" style="margin: 0 0 4px 0;">
                        <tr>
                            <th align="left" style="font-size:11px;">Name</th>
                            <th align="left" style="font-size:11px;">Company</th>
                            <th align="left" style="font-size:11px;">Recruiter</th>
                            <th align="left" style="font-size:11px;">Date</th>
                        </tr>
                        <?php foreach($placedRS as $index => $data): ?>
                        <tr class="<?php TemplateUtility::printAlternatingRowClass($index); ?>">
                            <td style="font-size:11px;"><a href="<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php echo($data['candidateID']); ?>"style="font-size:11px;" class="<?php echo($data['candidateClassName']); ?>"><?php $_($data['firstName']); ?> <?php $_($data['lastName']); ?></a></td>
                            <td style="font-size:11px;"><a href="<?php echo(CATSUtility::getIndexName()); ?>?m=companies&amp;a=show&amp;companyID=<?php echo($data['companyID']); ?>"  style="font-size:11px;" class="<?php echo($data['companyClassName']); ?>"><?php $_($data['companyName']); ?></td>
                            <td style="font-size:11px;"><?php $_(StringUtility::makeInitialName($data['userFirstName'], $data['userLastName'], false, LAST_NAME_MAXLEN)); ?></td>
                            <td style="font-size:11px;"><?php $_($data['date']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </table>
                    <?php if (!count($placedRS)): ?>
                    <div style="height: 207px; border: 1px solid #c0c0c0; background: #E7EEFF url(images/nodata/dashboardNoHiresWhite.jpg);">
                        &nbsp;
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="hpanel">
                <div class="panel-heading">
                    Hiring Overview
                </div>
                <div class="panel-body">
                    <table class="table" style="margin: 0 0 4px 0;">
                        <tr>
                            <td align="center" valign="top" style="text-align: left; width: 50%; height: 240px;">
                                <div class="noteUnsizedSpan"></div>
                                <map name="dashboardmap" id="dashboardmap">
                                   <area href="#" alt="Weekly" title="Weekly"
                                         shape="rect" coords="398,0,461,24" onclick="swapHomeGraph(<?php echo(DASHBOARD_GRAPH_WEEKLY); ?>);" />
                                   <area href="#" alt="Monthly" title="Monthly"
                                         shape="rect" coords="398,25,461,48" onclick="swapHomeGraph(<?php echo(DASHBOARD_GRAPH_MONTHLY); ?>);" />
                                    <area href="#" alt="Yearly" title="Yearly"
                                         shape="rect" coords="398,49,461,74" onclick="swapHomeGraph(<?php echo(DASHBOARD_GRAPH_YEARLY); ?>);" />
                                </map>
                                <img src="<?php echo(CATSUtility::getIndexName()); ?>?m=graphs&amp;a=miniPlacementStatistics&amp;width=495&amp;height=230" id="homeGraph" onclick="" alt="Hiring Overview" usemap="#dashboardmap" border="0" />
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="hpanel">
                <div class="panel-heading">
                    Important Candidates (Submitted, Interviewing, Offered in Active Job Orders) - Page <?php echo($dataGrid->getCurrentPageHTML()); ?> (<?php echo($dataGrid->getNumberOfRows()); ?> Items)
                </div>
                <div class="panel-body">
                    <table class="table table-striped">
                        <tr>
                            <td align="left" valign="top" style="text-align: left; width: 50%; height: 260px;">
                                <?php $dataGrid->draw(); ?>
                                <div style="float:right;"><?php $dataGrid->printNavigation(false); ?>&nbsp;&nbsp;&nbsp;&nbsp;<?php $dataGrid->printShowAll(); ?>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>

                                <?php if (!$dataGrid->getNumberOfRows()): ?>
                                <div style="height: 208px; border: 1px solid #c0c0c0; background: #E7EEFF url(images/nodata/dashboardNoCandidatesWhite.jpg);">
                                    &nbsp;
                                </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php get_footer(); ?>
