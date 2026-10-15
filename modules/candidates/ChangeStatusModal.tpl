<?php if ($this->isJobOrdersMode): ?>
    <?php TemplateUtility::printModalHeader('Job Orders', array(), 'Job Orders: Change Status'); ?>
<?php else: ?>
    <?php TemplateUtility::printModalHeader('Candidates', array(), 'Candidates: Change Status'); ?>
<?php endif; ?>


<main class="container-fluid p-2 oc-candidate-changestatusmodal">
<?php if (!$this->isFinishedMode): ?>

<script>
    var statusByJobOrderID = {};
    var statusDescriptionByJobOrderID = {};
    var jobOrderTitleByID = {};
    var jobOrderCompanyByID = {};

    <?php if ($this->isJobOrdersMode): ?>
        statusByJobOrderID[<?php echo((int) $this->pipelineData['jobOrderID']); ?>] = <?php echo((int) $this->pipelineData['statusID']); ?>;
        statusDescriptionByJobOrderID[<?php echo((int) $this->pipelineData['jobOrderID']); ?>] = <?php echo(json_encode((string) $this->pipelineData['status'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>;
        jobOrderTitleByID[<?php echo((int) $this->pipelineData['jobOrderID']); ?>] = <?php echo(json_encode((string) $this->pipelineData['title'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>;
        jobOrderCompanyByID[<?php echo((int) $this->pipelineData['jobOrderID']); ?>] = <?php echo(json_encode((string) $this->pipelineData['companyName'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>;
    <?php else: ?>
        <?php foreach ($this->pipelineRS as $pipelinesData): ?>
            statusByJobOrderID[<?php echo((int) $pipelinesData['jobOrderID']); ?>] = <?php echo((int) $pipelinesData['statusID']); ?>;
            statusDescriptionByJobOrderID[<?php echo((int) $pipelinesData['jobOrderID']); ?>] = <?php echo(json_encode((string) $pipelinesData['status'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>;
            jobOrderTitleByID[<?php echo((int) $pipelinesData['jobOrderID']); ?>] = <?php echo(json_encode((string) $pipelinesData['title'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>;
            jobOrderCompanyByID[<?php echo((int) $pipelinesData['jobOrderID']); ?>] = <?php echo(json_encode((string) $pipelinesData['companyName'], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)); ?>;
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
    <input type="hidden" name="postback" id="postback" value="postback">
    <input type="hidden" id="candidateID" name="candidateID" value="<?php echo($this->candidateID); ?>">
    <input type="hidden" id="addActivityProvided" name="addActivityProvided" value="1">
<?php if ($this->isJobOrdersMode): ?>
    <input type="hidden" id="regardingIDHidden" name="regardingID" value="<?php echo($this->selectedJobOrderID); ?>">
<?php endif; ?>

    <div class="card card-body p-2 mb-2">
        <div class="row g-2 align-items-start mb-2">
            <div class="col-sm-4">
                <label id="regardingIDLabel" for="regardingID" class="form-label small mb-1">Regarding:</label>
            </div>
            <div class="col-12 col-sm">
<?php if ($this->isJobOrdersMode): ?>
                <span><?php $this->_($this->pipelineData['title']); ?></span> (<?php $this->_($this->pipelineData['companyName']); ?>)
<?php else: ?>
                <select id="regardingID" name="regardingID" class="form-select form-select-sm" onchange="CS_onRegardingChange();">
                    <?php foreach ($this->pipelineRS as $pipelinesData): ?>
                        <option <?php if ($this->selectedJobOrderID == $pipelinesData['jobOrderID']): ?>selected="selected" <?php endif; ?>value="<?php $this->_($pipelinesData['jobOrderID']) ?>"><?php $this->_($pipelinesData['title']) ?> (<?php $this->_($pipelinesData['companyName']) ?>)</option>
                    <?php endforeach; ?>
                </select>
<?php endif; ?>
            </div>
        </div>

        <div class="row g-2 align-items-start mb-2">
            <div class="col-sm-4">
                <label id="statusIDLabel" for="statusID" class="form-label small mb-1">Status:</label>
            </div>
            <div class="col-12 col-sm">
                <select id="statusID" name="statusID" class="form-select form-select-sm" onchange="CS_onStatusChange();"<?php if (!$this->isJobOrdersMode && $this->selectedJobOrderID == -1): ?> disabled<?php endif; ?>>
                    <?php foreach ($this->statusRS as $statusData): ?>
                        <option<?php if ($this->selectedStatusID == $statusData['statusID']): ?> selected="selected"<?php endif; ?> value="<?php $this->_($statusData['statusID']) ?>"><?php $this->_($statusData['status']) ?></option>
                    <?php endforeach; ?>
                </select>

                <span id="triggerEmailSpan" style="display: none;"><input type="checkbox" name="triggerEmail" id="triggerEmail" onclick="CS_onSendEmailChange();" class="form-check-input"><label for="triggerEmail">Send E-Mail Notification to Candidate</label></span>
            </div>
        </div>

        <div id="sendEmailCheckTR" style="display: none;" class="row g-2 align-items-start mb-2">
            <div class="col-sm-4">
                <label id="triggerEmailLabel" for="triggerEmail" class="form-label small mb-1">E-Mail:</label>
            </div>
            <div class="col-12 col-sm">
                Custom Message<br>
                <input type="hidden" id="origionalCustomMessage" value="<?php $this->_($this->statusChangeTemplate); ?>">
                <input type="hidden" id="emailIsDisabled" value="<?php echo($this->emailDisabled); ?>">
                <textarea name="customMessage" id="customMessage" cols="50" class="form-control form-control-sm"></textarea>
            </div>
        </div>

        <div id="addActivityTR" class="row g-2 align-items-start mb-2">
            <div class="col-sm-4">
                <label id="addActivityLabel" for="addActivity" class="form-label small mb-1">Activity:</label>
            </div>
            <div class="col-12 col-sm">
                <input type="checkbox" name="addActivity" id="addActivity" checked="checked" class="form-check-input"><label for="addActivity">Log an Activity</label>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-sm btn-primary" name="submit" id="submit" value="Save">Save</button>&nbsp;
<?php if ($this->isJobOrdersMode): ?>
    <button type="button" class="btn btn-sm btn-outline-secondary" name="close" value="Cancel" onclick="parentGoToURL(<?php echo Template::escapeJsAttr(CATSUtility::getIndexName() . '?m=joborders&a=show&jobOrderID=' . $this->selectedJobOrderID); ?>);">Cancel</button>
<?php else: ?>
    <button type="button" class="btn btn-sm btn-outline-secondary" name="close" value="Cancel" onclick="parentGoToURL(<?php echo Template::escapeJsAttr(CATSUtility::getIndexName() . '?m=candidates&a=show&candidateID=' . $this->candidateID); ?>);">Cancel</button>
<?php endif; ?>
</form>

<script>
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
        <button type="button" name="close" class="btn btn-sm btn-outline-secondary" value="Close" onclick="parentGoToURL(<?php echo Template::escapeJsAttr(CATSUtility::getIndexName() . '?m=joborders&a=show&jobOrderID=' . $this->regardingID); ?>);">Close</button>
<?php else: ?>
        <button type="button" name="close" class="btn btn-sm btn-outline-secondary" value="Close" onclick="parentGoToURL(<?php echo Template::escapeJsAttr(CATSUtility::getIndexName() . '?m=candidates&a=show&candidateID=' . $this->candidateID); ?>);">Close</button>
<?php endif; ?>
    </form>
<?php endif; ?>

</main>
    </body>
</html>
