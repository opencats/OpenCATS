<?php /* $Id: SubmissionReport.tpl 1948 2007-02-23 09:49:27Z will $ */ ?>
<?php TemplateUtility::printHeader($this->reportTitle); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
    <table>
        <tr>
            <td width="3%">
                <img src="images/reports.gif" width="24" height="24" border="0" alt="Reports" style="margin-top: 3px;" />&nbsp;
            </td>
            <td><h2><?php $this->_($this->reportTitle); ?></h2></td>
        </tr>
    </table>

    <p class="note">Submissions</p>

    <?php foreach ($this->submissionJobOrdersRS as $rowNumber => $submissionJobOrdersData): ?>
        <span style="font: normal normal bold 13px/130% Arial, Tahoma, sans-serif;"><?php $this->_($submissionJobOrdersData['title']) ?> at <?php $this->_($submissionJobOrdersData['companyName']) ?> (<?php $this->_($submissionJobOrdersData['ownerFullName']) ?>)</span>
        <br />
        <table class="sortable" width="925">
            <tr>
                <th align="left" nowrap="nowrap">First Name</th>
                <th align="left" nowrap="nowrap">Last Name</th>
                <th align="left" nowrap="nowrap">Candidate Owner</th>
                <th align="left" nowrap="nowrap">Date Submitted</th>
            </tr>

            <?php foreach ($submissionJobOrdersData['submissionsRS'] as $rowNumber => $submissionsData): ?>
                <tr class="<?php TemplateUtility::printAlternatingRowClass($rowNumber); ?>">
                    <td valign="top" align="left"><?php $this->_($submissionsData['firstName']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($submissionsData['lastName']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($submissionsData['ownerFullName']) ?>&nbsp;</td>
                    <td valign="top" align="left"><?php $this->_($submissionsData['dateSubmitted']) ?>&nbsp;</td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endforeach; ?>
<?php TemplateUtility::printReportFooter(); ?>
