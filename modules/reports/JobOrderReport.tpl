<?php /* $Id: JobOrderReport.tpl 2441 2007-05-04 20:42:02Z brian $ */ ?>
<?php TemplateUtility::printHeader('Job Orders', array('modules/joborders/validator.js', 'js/company.js', 'js/sweetTitles.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/job_orders.gif" width="24" height="24" border="0" alt="Job Orders" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Reports: Job Order Report</h2></td>
                </tr>
            </table>

            <p class="note">Generate a job order report.</p>

            <form name="jobOrderReportForm" id="jobOrderReportForm" action="<?php echo(CATSUtility::getIndexName()); ?>" method="get">
                <input type="hidden" name="m" value="reports">
                <input type="hidden" name="a" value="generateJobOrderReportPDF">

                <table class="editTable" width="700">
                    <tr>
                        <td class="tdVertical" style="width: 140px;">
                            <label id="siteNameLabel" for="siteName">Company Name:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="siteName" id="siteName" value="<?php $this->_($this->reportParameters['siteName']); ?>" style="width: 250px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="companyNameLabel" for="companyName">Company:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="companyName" id="companyName" value="<?php $this->_($this->reportParameters['companyName']); ?>" style="width: 250px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="jobOrderNameLabel" for="jobOrderName">Position (Title):</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="jobOrderName" id="jobOrderName" value="<?php $this->_($this->reportParameters['jobOrderName']); ?>" style="width: 250px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="periodLineLabel" for="periodLine">Job Order Period:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="periodLine" id="periodLine" value="<?php $this->_($this->reportParameters['periodLine']); ?>" style="width: 250px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="accountManagerLabel" for="accountManager">Account Manager:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="accountManager" id="accountManager" value="<?php $this->_($this->reportParameters['accountManager']); ?>" style="width: 250px;" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="recruiterLabel" for="recruiter">Recruiter:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="recruiter" id="recruiter" value="<?php $this->_($this->reportParameters['recruiter']); ?>" style="width: 250px;" />&nbsp;*
                        </td>
                    </tr>
                </table>

                <table class="editTable" width="700">
                    <input type="hidden" name="dataSet" id="dataSet" value="0,0,0,0">
                    <script type="text/javascript">
                        function setDataSet()
                        {
                            document.getElementById('dataSet').value =
                                document.getElementById('dataSet1').value + ',' +
                                document.getElementById('dataSet2').value + ',' +
                                document.getElementById('dataSet3').value + ',' +
                                document.getElementById('dataSet4').value;
                        }
                    </script>

                    <tr>
                        <td class="tdVertical" style="width: 140px;">
                            <label id="dataSet1Label"for="dataSet1">Candidates Screened:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="dataSet1" id="dataSet1" value="<?php $this->_($this->reportParameters['dataSet1']); ?>" style="width: 75px;" onchange="setDataSet();" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="dataSet2Label"for="dataSet2">Candidates Submitted:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="dataSet2" id="dataSet2" value="<?php $this->_($this->reportParameters['dataSet2']); ?>" style="width: 75px;" onchange="setDataSet();" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="dataSet3Label"for="dataSet3">Candidates Interviewed:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="dataSet3" id="dataSet3" value="<?php $this->_($this->reportParameters['dataSet3']); ?>" style="width: 75px;" onchange="setDataSet();" />&nbsp;*
                        </td>
                    </tr>

                    <tr>
                        <td class="tdVertical">
                            <label id="dataSet4Label"for="dataSet4">Candidates Placed:</label>
                        </td>
                        <td class="tdData">
                            <input type="text" class="inputbox" name="dataSet4" id="dataSet4" value="<?php $this->_($this->reportParameters['dataSet4']); ?>" style="width: 75px;" onchange="setDataSet();" />&nbsp;*
                        </td>
                    </tr>
                </table>

                <script type="text/javascript">setDataSet();</script>

                <table class="editTable" width="700">
                    <tr>
                        <td class="tdVertical" style="width: 140px;">
                            <label id="notesLabel" for="notes">Misc. Notes:</label>
                        </td>
                        <td class="tdData">
                            <textarea class="inputbox" name="notes" id="notes" rows="5" style="width: 400px;" /></textarea>
                        </td>
                    </tr>
                </table>

                <input type="submit" class="button" name="submit" value="Generate Report" />&nbsp;
                <input type="reset"  class="button" name="reset"  value="Reset" />&nbsp;
                
                <!-- IE PDF Hack -->
                <input type="hidden" name="ext" value=".pdf" />
            </form>

            <script type="text/javascript">
                document.jobOrderReportForm.siteName.focus();
            </script>
        </div>
    </div>
<?php TemplateUtility::printFooter(); ?>
