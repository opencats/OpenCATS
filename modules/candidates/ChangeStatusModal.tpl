<?php /* $Id: ChangeStatusModal.tpl $ */ ?>
<?php if ($this->isJobOrdersMode): ?>
    <?php TemplateUtility::printModalHeader('Job Orders', array(), 'Job Orders: Change Status'); ?>
<?php else: ?>
    <?php TemplateUtility::printModalHeader('Candidates', array(), 'Candidates: Change Status'); ?>
<?php endif; ?>

<?php if (!$this->isFinishedMode): ?>

<script type="text/javascript">
    var statusByJobOrderID = {};
    var statusDescriptionByJobOrderID = {};
    var jobOrderTitleByID = {};
    var jobOrderCompanyByID = {};

    <?php if ($this->isJobOrdersMode): ?>
        statusByJobOrderID[<?php echo((int) $this->pipelineData['jobOrderID']); ?>] = <?php echo((int) $this->pipelineData['statusID']); ?>;
        statusDescriptionByJobOrderID[<?php echo((int) $this->pipelineData['jobOrderID']); ?>] = '<?php echo(str_replace("'", "\\'", $this->pipelineData['status'])); ?>';
        jobOrderTitleByID[<?php echo((int) $this->pipelineData['jobOrderID']); ?>] = '<?php echo(str_replace("'", "\\'", $this->pipelineData['title'])); ?>';
        jobOrderCompanyByID[<?php echo((int) $this->pipelineData['jobOrderID']); ?>] = '<?php echo(str_replace("'", "\\'", $this->pipelineData['companyName'])); ?>';
    <?php else: ?>
        <?php foreach ($this->pipelineRS as $pipelinesData): ?>
            statusByJobOrderID[<?php echo((int) $pipelinesData['jobOrderID']); ?>] = <?php echo((int) $pipelinesData['statusID']); ?>;
            statusDescriptionByJobOrderID[<?php echo((int) $pipelinesData['jobOrderID']); ?>] = '<?php echo(str_replace("'", "\\'", $pipelinesData['status'])); ?>';
            jobOrderTitleByID[<?php echo((int) $pipelinesData['jobOrderID']); ?>] = '<?php echo(str_replace("'", "\\'", $pipelinesData['title'])); ?>';
            jobOrderCompanyByID[<?php echo((int) $pipelinesData['jobOrderID']); ?>] = '<?php echo(str_replace("'", "\\'", $pipelinesData['companyName'])); ?>';
        <?php endforeach; ?>
    <?php endif; ?>

    var statusTriggersEmailMap = {};
    <?php foreach ($this->statusRS as $statusData): ?>
        statusTriggersEmailMap[<?php echo((int) $statusData['statusID']); ?>] = <?php echo((int) $statusData['triggersEmail']); ?>;
    <?php endforeach; ?>

    function CS_getRegardingID()
    {
        var regardingSelect = document.getElementById('regardingID');
        if (regardingSelect)
        {
            return parseInt(regardingSelect.options[regardingSelect.selectedIndex].value, 10);
        }

        return parseInt(document.getElementById('regardingIDHidden').value, 10);
    }

    function CS_selectStatus(statusID)
    {
        var statusSelect = document.getElementById('statusID');
        for (var i = 0; i < statusSelect.options.length; i++)
        {
            if (parseInt(statusSelect.options[i].value, 10) === statusID)
            {
                statusSelect.selectedIndex = i;
                return;
            }
        }
    }

    function CS_onSendEmailChange()
    {
        var triggerEmail = document.getElementById('triggerEmail');
        var sendEmailRow = document.getElementById('sendEmailCheckTR');

        if (triggerEmail.checked)
        {
            sendEmailRow.style.display = '';
        }
        else
        {
            sendEmailRow.style.display = 'none';
        }
    }

    function CS_clearEmail()
    {
        var triggerEmailSpan = document.getElementById('triggerEmailSpan');
        var triggerEmail = document.getElementById('triggerEmail');
        var sendEmailRow = document.getElementById('sendEmailCheckTR');

        triggerEmailSpan.style.display = 'none';
        triggerEmail.checked = false;
        sendEmailRow.style.display = 'none';
    }

    function CS_generateEmail(regardingID)
    {
        var statusSelect = document.getElementById('statusID');
        var template = document.getElementById('origionalCustomMessage').value;
        template = template.replace(/%CANDSTATUS%/g, statusSelect.options[statusSelect.selectedIndex].text);
        template = template.replace(/%CANDPREVSTATUS%/g, statusDescriptionByJobOrderID[regardingID]);
        template = template.replace(/%JBODTITLE%/g, jobOrderTitleByID[regardingID]);
        template = template.replace(/%JBODCLIENT%/g, jobOrderCompanyByID[regardingID]);

        document.getElementById('customMessage').value = template;
    }

    function CS_onStatusChange()
    {
        var regardingID = CS_getRegardingID();
        var statusSelect = document.getElementById('statusID');
        var triggerEmail = document.getElementById('triggerEmail');
        var triggerEmailSpan = document.getElementById('triggerEmailSpan');
        var emailIsDisabled = document.getElementById('emailIsDisabled');
        var selectedStatusID = parseInt(statusSelect.value, 10);
        var currentStatusID = parseInt(statusByJobOrderID[regardingID], 10);

        if (isNaN(regardingID) || isNaN(selectedStatusID) || selectedStatusID <= 0 || selectedStatusID === currentStatusID)
        {
            CS_clearEmail();
            return;
        }

        triggerEmailSpan.style.display = 'inline';
        if (statusTriggersEmailMap[selectedStatusID] == 1 && emailIsDisabled.value == '0')
        {
            triggerEmail.checked = true;
        }
        else
        {
            triggerEmail.checked = false;
        }

        CS_onSendEmailChange();
        CS_generateEmail(regardingID);
    }

    function CS_onRegardingChange()
    {
        var regardingID = CS_getRegardingID();
        var statusSelect = document.getElementById('statusID');
        var currentStatusID = parseInt(statusByJobOrderID[regardingID], 10);

        if (isNaN(currentStatusID))
        {
            statusSelect.disabled = true;
            CS_clearEmail();
            return;
        }

        statusSelect.disabled = false;
        CS_selectStatus(currentStatusID);
        CS_onStatusChange();
    }

    function CS_checkForm()
    {
        var statusSelect = document.getElementById('statusID');
        if (statusSelect.disabled)
        {
            alert('Form Error:\n    - You must select a job order.');
            return false;
        }

        return true;
    }
</script>

<form name="changeStatusForm" id="changeStatusForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=<?php if ($this->isJobOrdersMode): ?>joborders<?php else: ?>candidates<?php endif; ?>&amp;a=changeStatus" method="post" onsubmit="return CS_checkForm();" autocomplete="off">
    <input type="hidden" name="postback" id="postback" value="postback" />
    <input type="hidden" id="candidateID" name="candidateID" value="<?php echo($this->candidateID); ?>" />
    <input type="hidden" id="addActivityProvided" name="addActivityProvided" value="1" />
<?php if ($this->isJobOrdersMode): ?>
    <input type="hidden" id="regardingIDHidden" name="regardingID" value="<?php echo($this->selectedJobOrderID); ?>" />
<?php endif; ?>

    <table class="editTable" width="560">
        <tr>
            <td class="tdVertical">
                <label id="regardingIDLabel" for="regardingID">Regarding:</label>
            </td>
            <td class="tdData">
<?php if ($this->isJobOrdersMode): ?>
                <span><?php $this->_($this->pipelineData['title']); ?></span> (<?php $this->_($this->pipelineData['companyName']); ?>)
<?php else: ?>
                <select id="regardingID" name="regardingID" class="inputbox" style="width: 220px;" onchange="CS_onRegardingChange();">
                    <?php foreach ($this->pipelineRS as $pipelinesData): ?>
                        <option <?php if ($this->selectedJobOrderID == $pipelinesData['jobOrderID']): ?>selected="selected" <?php endif; ?>value="<?php $this->_($pipelinesData['jobOrderID']) ?>"><?php $this->_($pipelinesData['title']) ?> (<?php $this->_($pipelinesData['companyName']) ?>)</option>
                    <?php endforeach; ?>
                </select>
<?php endif; ?>
            </td>
        </tr>

        <tr>
            <td class="tdVertical">
                <label id="statusIDLabel" for="statusID">Status:</label>
            </td>
            <td class="tdData">
                <select id="statusID" name="statusID" class="inputbox" style="width: 180px;" onchange="CS_onStatusChange();"<?php if (!$this->isJobOrdersMode && $this->selectedJobOrderID == -1): ?> disabled<?php endif; ?>>
                    <?php foreach ($this->statusRS as $statusData): ?>
                        <option<?php if ($this->selectedStatusID == $statusData['statusID']): ?> selected="selected"<?php endif; ?> value="<?php $this->_($statusData['statusID']) ?>"><?php $this->_($statusData['status']) ?></option>
                    <?php endforeach; ?>
                </select>
                &nbsp;&nbsp;
                <span id="triggerEmailSpan" style="display: none;"><input type="checkbox" name="triggerEmail" id="triggerEmail" onclick="CS_onSendEmailChange();" />Send E-Mail Notification to Candidate</span>
            </td>
        </tr>

        <tr id="sendEmailCheckTR" style="display: none;">
            <td class="tdVertical">
                <label id="triggerEmailLabel" for="triggerEmail">E-Mail:</label>
            </td>
            <td class="tdData">
                Custom Message<br />
                <input type="hidden" id="origionalCustomMessage" value="<?php $this->_($this->statusChangeTemplate); ?>" />
                <input type="hidden" id="emailIsDisabled" value="<?php echo($this->emailDisabled); ?>" />
                <textarea style="height:135px; width:375px;" name="customMessage" id="customMessage" cols="50" class="inputbox"></textarea>
            </td>
        </tr>

        <tr id="addActivityTR">
            <td class="tdVertical">
                <label id="addActivityLabel" for="addActivity">Activity:</label>
            </td>
            <td class="tdData">
                <input type="checkbox" name="addActivity" id="addActivity" style="margin-left: 0px;" checked="checked" />Log an Activity
            </td>
        </tr>
    </table>

    <input type="submit" class="button" name="submit" id="submit" value="Save" />&nbsp;
<?php if ($this->isJobOrdersMode): ?>
    <input type="button" class="button" name="close" value="Cancel" onclick="parentGoToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php echo($this->selectedJobOrderID); ?>');" />
<?php else: ?>
    <input type="button" class="button" name="close" value="Cancel" onclick="parentGoToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php echo($this->candidateID); ?>');" />
<?php endif; ?>
</form>

<script type="text/javascript">
<?php if ($this->isJobOrdersMode): ?>
    CS_onStatusChange();
<?php else: ?>
    CS_onRegardingChange();
<?php endif; ?>
</script>

<?php else: ?>
    <?php if ($this->statusChanged): ?>
        <?php if ($this->isJobOrdersMode): ?>
            <p>The pipeline status has been changed from <span class="bold"><?php $this->_($this->oldStatusDescription); ?></span> to <span class="bold"><?php $this->_($this->newStatusDescription); ?></span>.</p>
        <?php else: ?>
            <p>The candidate's status has been changed from <span class="bold"><?php $this->_($this->oldStatusDescription); ?></span> to <span class="bold"><?php $this->_($this->newStatusDescription); ?></span>.</p>
        <?php endif; ?>
    <?php else: ?>
        <?php if ($this->isJobOrdersMode): ?>
            <p>The pipeline status has not been changed.</p>
        <?php else: ?>
            <p>The candidate's status has not been changed.</p>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($this->activityAdded): ?>
        <p>An activity entry of type <span class="bold"><?php $this->_($this->activityType); ?></span> has been added.</p>
    <?php else: ?>
        <p>No activity entries have been added.</p>
    <?php endif; ?>

    <?php echo($this->notificationHTML); ?>

    <form>
<?php if ($this->isJobOrdersMode): ?>
        <input type="button" name="close" class="button" value="Close" onclick="parentGoToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=show&amp;jobOrderID=<?php echo($this->regardingID); ?>');" />
<?php else: ?>
        <input type="button" name="close" class="button" value="Close" onclick="parentGoToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=candidates&amp;a=show&amp;candidateID=<?php echo($this->candidateID); ?>');" />
<?php endif; ?>
    </form>
<?php endif; ?>

    </body>
</html>
