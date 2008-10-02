<?php /* $Id: Candidates.tpl 3445 2007-11-06 23:17:04Z will $ */ ?>
<?php TemplateUtility::printHeader('Candidates', array( 'js/highlightrows.js', 'js/export.js', 'js/dataGrid.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <style type="text/css">
    div.addCandidateButton { background: #4172E3 url(images/nodata/candidatesButton.jpg); cursor: pointer; width: 337px; height: 67px; }
    div.addCandidateButton:hover { background: #4172E3 url(images/nodata/candidateButton-o.jpg); cursor: pointer; width: 337px; height: 67px; }
    div.addMassImportButton { background: #4172E3 url(images/nodata/addMassImport.jpg); cursor: pointer; width: 337px; height: 67px; }
    div.addMassImportButton:hover { background: #4172E3 url(images/nodata/addMassImport-o.jpg); cursor: pointer; width: 337px; height: 67px; }
    </style>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents"<?php echo !$this->totalCandidates ? ' style="background-color: #E6EEFF; padding: 0px;"' : ''; ?>>
            <?php if ($this->totalCandidates): ?>
            <table width="100%">
                <tr>
                    <td width="3%">
                        <img src="images/candidate.gif" width="24" height="24" alt="Candidates" style="border: none; margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Candidates: Home</h2></td>
                    <td align="right">
                        <form name="candidatesViewSelectorForm" id="candidatesViewSelectorForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get">
                            <input type="hidden" name="m" value="candidates" />
                            <input type="hidden" name="a" value="listByView" />

                            <table class="viewSelector">
                                <tr>
                                    <td valign="top" align="right" nowrap="nowrap">
                                        <?php $this->dataGrid->printNavigation(false); ?>
                                    </td>
                                    <td valign="top" align="right" nowrap="nowrap">
                                        <input type="checkbox" name="onlyMyCandidates" id="onlyMyCandidates" <?php if ($this->dataGrid->getFilterValue('OwnerID') ==  $this->userID): ?>checked<?php endif; ?> onclick="<?php echo $this->dataGrid->getJSAddRemoveFilterFromCheckbox('OwnerID', '==',  $this->userID); ?>" />
                                        Only My Candidates&nbsp;
                                    </td>
                                    <td valign="top" align="right" nowrap="nowrap">
                                        <input type="checkbox" name="onlyHotCandidates" id="onlyHotCandidates" <?php if ($this->dataGrid->getFilterValue('IsHot') == '1'): ?>checked<?php endif; ?> onclick="<?php echo $this->dataGrid->getJSAddRemoveFilterFromCheckbox('IsHot', '==', '\'1\''); ?>" />
                                        <label for="onlyHotCandidates">Only Hot Candidates</label>&nbsp;
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </td>
                </tr>
            </table>

            <?php if ($this->topLog != ''): ?>
            <div style="margin: 20px 0px 20px 0px;">
                <?php echo $this->topLog; ?>
            </div>
            <?php endif; ?>

            <?php if ($this->errMessage != ''): ?>
            <div id="errorMessage" style="padding: 25px 0px 25px 0px; border-top: 1px solid #800000; border-bottom: 1px solid #800000; background-color: #f7f7f7;margin-bottom: 15px;">
            <table>
                <tr>
                    <td align="left" valign="center" style="padding-right: 5px;">
                        <img src="images/large_error.gif" align="left">
                    </td>
                    <td align="left" valign="center">
                        <span style="font-size: 12pt; font-weight: bold; color: #800000; line-height: 12pt;">There was a problem with your request:</span>
                        <div style="font-size: 10pt; font-weight: bold; padding: 3px 0px 0px 0px;"><?php echo $this->errMessage; ?></div>
                    </td>
                </tr>
            </table>
            </div>
            <?php endif; ?>

            <p class="note">
                <span style="float:left;">Candidates - Page <?php echo($this->dataGrid->getCurrentPageHTML()); ?> (<?php echo($this->dataGrid->getNumberOfRows()); ?> Items)</span>
                <span style="float:right;">
                    <?php $this->dataGrid->drawRowsPerPageSelector(); ?>
                    <?php $this->dataGrid->drawShowFilterControl(); ?>
                </span>&nbsp;
            </p>

            <?php $this->dataGrid->drawFilterArea(); ?>
            <?php $this->dataGrid->draw();  ?>

            <div style="display:block;">
                <span style="float:left;">
                    <?php $this->dataGrid->printActionArea(); ?>
                </span>
                <span style="float:right;">
                    <?php $this->dataGrid->printNavigation(true); ?>
                </span>&nbsp;
            </div>

            <?php else: ?>

            <br /><br /><br /><br />
            <div style="height: 95px; background: #E6EEFF url(images/nodata/candidatesTop.jpg);">
                &nbsp;
            </div>
            <br /><br />
            <table cellpadding="0" cellspacing="0" border="0" width="956">
                <tr>
                <td style="padding-left: 62px;" align="center" valign="center">

                    <div style="text-align: center; width: 600px; line-height: 22px; font-size: 18px; font-weight: bold; color: #666666; padding-bottom: 20px;">
                    Add candidates to keep track of possible applicants you can consider for your job orders.
                    </div>

                    <table cellpadding="10" cellspacing="0" border="0">
                        <tr>
                            <td style="padding-right: 20px;">
                                <a href="<?php echo CATSUtility::getIndexName(); ?>?m=candidates&amp;a=add">
                                <div class="addCandidateButton">&nbsp;</div>
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo CATSUtility::getIndexName(); ?>?m=import&amp;a=massImport">
                                <div class="addMassImportButton">&nbsp;</div>
                                </a>
                            </td>
                        </tr>
                    </table>
                </td>

                </tr>
            </table>

            <?php endif; ?>
        </div>
    </div>

    <div id="bottomShadow"></div>
<?php TemplateUtility::printFooter(); ?>
