<?php /* $Id: ChangeStatusModal.tpl $ */ ?>
<?php if ($this->isJobOrdersMode): ?>
    <?php TemplateUtility::printModalHeader('Job Orders', array('js/activity.js'), 'Job Orders: Change Status'); ?>
<?php else: ?>
    <?php TemplateUtility::printModalHeader('Candidates', array('js/activity.js'), 'Candidates: Change Status'); ?>
<?php endif; ?>

<?php if (!$this->isFinishedMode): ?>

<script type="text/javascript">
    <?php if ($this->isJobOrdersMode): ?>
        statusesArray = new Array(1);
        jobOrdersArray = new Array(1);
        statusesArrayString = new Array(1);
        jobOrdersArrayStringTitle = new Array(1);
        jobOrdersArrayStringCompany = new Array(1);
        statusesArray[0] = <?php echo($this->pipelineData['statusID']); ?>;
        statusesArrayString[0] = '<?php echo($this->pipelineData['status']); ?>';
        jobOrdersArray[0] = <?php echo($this->pipelineData['jobOrderID']); ?>;
        jobOrdersArrayStringTitle[0] = '<?php echo(str_replace("'", "\\'", $this->pipelineData['title'])); ?>';
        jobOrdersArrayStringCompany[0] = '<?php echo(str_replace("'", "\\'", $this->pipelineData['companyName'])); ?>';
    <?php else: ?>
        <?php $count = count($this->pipelineRS); ?>
        statusesArray = new Array(<?php echo($count); ?>);
        jobOrdersArray = new Array(<?php echo($count); ?>);
        statusesArrayString = new Array(<?php echo($count); ?>);
        jobOrdersArrayStringTitle = new Array(<?php echo($count); ?>);
        jobOrdersArrayStringCompany = new Array(<?php echo($count); ?>);
        <?php for ($i = 0; $i < $count; ++$i): ?>
            statusesArray[<?php echo($i); ?>] = <?php echo($this->pipelineRS[$i]['statusID']); ?>;
            statusesArrayString[<?php echo($i); ?>] = '<?php echo($this->pipelineRS[$i]['status']); ?>';
            jobOrdersArray[<?php echo($i); ?>] = <?php echo($this->pipelineRS[$i]['jobOrderID']); ?>;
            jobOrdersArrayStringTitle[<?php echo($i); ?>] = '<?php echo(str_replace("'", "\\'", $this->pipelineRS[$i]['title'])); ?>';
            jobOrdersArrayStringCompany[<?php echo($i); ?>] = '<?php echo(str_replace("'", "\\'", $this->pipelineRS[$i]['companyName'])); ?>';
        <?php endfor; ?>
    <?php endif; ?>
    statusTriggersEmailArray = new Array(<?php echo(count($this->statusRS)); ?>);
    <?php foreach ($this->statusRS as $rowNumber => $statusData): ?>
        statusTriggersEmailArray[<?php echo($rowNumber); ?>] = <?php echo($statusData['triggersEmail']); ?>;
    <?php endforeach; ?>

    function CS_clearEmail()
    {
        var sendEmailRow = document.getElementById("sendEmailCheckTR");
        var triggerEmailSpan = document.getElementById("triggerEmailSpan");
        var triggerEmail = document.getElementById("triggerEmail");

        triggerEmail.checked = false;
        triggerEmailSpan.style.display = "none";
        sendEmailRow.style.display = "none";
    }

    function CS_onRegardingChange()
    {
        var regardingSelectList = document.getElementById("regardingID");
        var statusSelectList = document.getElementById("statusID");
        var regardingID = regardingSelectList[regardingSelectList.selectedIndex].value;

        if (regardingID == "-1")
        {
            statusSelectList.selectedIndex = 0;
            statusSelectList.disabled = true;
            CS_clearEmail();
            return;
        }

        statusSelectList.disabled = false;

        var statusIndex = findValueInArray(jobOrdersArray, regardingID);
        if (statusIndex == -1)
        {
            statusSelectList.selectedIndex = 0;
            CS_clearEmail();
            return;
        }

        var statusSelectIndex = findValueInSelectList(statusSelectList, statusesArray[statusIndex]);
        if (statusSelectIndex == -1)
        {
            statusSelectList.selectedIndex = 0;
            CS_clearEmail();
            return;
        }

        statusSelectList[statusSelectIndex].selected = true;
        CS_onStatusChange();
    }

    function CS_onStatusChange()
    {
        var statusSelectList = document.getElementById("statusID");
        var triggerEmailSpan = document.getElementById("triggerEmailSpan");
        var triggerEmail = document.getElementById("triggerEmail");
        var emailText = document.getElementById("customMessage");
        var emailTextOrigional = document.getElementById("origionalCustomMessage");
        var emailIsDisabled = document.getElementById("emailIsDisabled");
        var regardingID;

        <?php if ($this->isJobOrdersMode): ?>
            regardingID = "<?php echo($this->selectedJobOrderID); ?>";
        <?php else: ?>
            var regardingSelectList = document.getElementById("regardingID");
            regardingID = regardingSelectList[regardingSelectList.selectedIndex].value;
        <?php endif; ?>

        if (statusSelectList.value == "-1" || regardingID == "-1")
        {
            CS_clearEmail();
            return;
        }

        var statusIndex = findValueInArray(jobOrdersArray, regardingID);
        if (statusIndex == -1)
        {
            CS_clearEmail();
            return;
        }

        if (statusesArray[statusIndex] == statusSelectList.value)
        {
            CS_clearEmail();
            return;
        }

        triggerEmailSpan.style.display = "inline";
        if (statusTriggersEmailArray[statusSelectList.selectedIndex - 1] == 1 && emailIsDisabled.value == "0")
        {
            triggerEmail.checked = true;
        }
        else
        {
            triggerEmail.checked = false;
        }

        AS_onSendEmailChange("triggerEmail", "sendEmailCheckTR", "visibleTR");
        AS_onChangeStatusChangeGenerateEmail(
            emailText,
            emailTextOrigional,
            statusSelectList[statusSelectList.selectedIndex].text,
            statusesArrayString[statusIndex],
            jobOrdersArrayStringTitle[statusIndex],
            jobOrdersArrayStringCompany[statusIndex]
        );
    }

    function CS_checkForm()
    {
        var errorMessage = "";
        var statusSelectList = document.getElementById("statusID");

        <?php if (!$this->isJobOrdersMode): ?>
            var regardingSelectList = document.getElementById("regardingID");
            if (regardingSelectList[regardingSelectList.selectedIndex].value == "-1")
            {
                errorMessage += "    - You must select a job order.\n";
            }
        <?php endif; ?>

        if (statusSelectList.value == "-1")
        {
            errorMessage += "    - You must select a status.\n";
        }

        if (errorMessage != "")
        {
            alert("Form Error:\n" + errorMessage);
            return false;
        }

        return true;
    }
</script>

<form name="changeStatusForm" id="changeStatusForm" action="<?php echo(CATSUtility::getIndexName()); ?>?m=<?php if ($this->isJobOrdersMode): ?>joborders<?php else: ?>candidates<?php endif; ?>&amp;a=changeStatus" method="post" onsubmit="return CS_checkForm();" autocomplete="off">
    <input type="hidden" name="postback" id="postback" value="postback" />
    <input type="hidden" id="candidateID" name="candidateID" value="<?php echo($this->candidateID); ?>" />
<?php if ($this->isJobOrdersMode): ?>
    <input type="hidden" id="regardingID" name="regardingID" value="<?php echo($this->selectedJobOrderID); ?>" />
<?php endif; ?>

    <table class="editTable" width="560">
        <tr id="visibleTR">
            <td class="tdVertical">
                <label id="regardingIDLabel" for="regardingID">Regarding:</label>
            </td>
            <td class="tdData">
<?php if ($this->isJobOrdersMode): ?>
                <span><?php $this->_($this->pipelineData['title']); ?></span>
<?php else: ?>
                <select id="regardingID" name="regardingID" class="inputbox" style="width: 260px;" onchange="CS_onRegardingChange();">
                    <option value="-1">-- Select --</option>
                    <?php foreach ($this->pipelineRS as $rowNumber => $pipelinesData): ?>
                        <option<?php if ($this->selectedJobOrderID == $pipelinesData['jobOrderID']): ?> selected="selected"<?php endif; ?> value="<?php $this->_($pipelinesData['jobOrderID']) ?>"><?php $this->_($pipelinesData['title']) ?> (<?php $this->_($pipelinesData['companyName']) ?>)</option>
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
                <select id="statusID" name="statusID" class="inputbox" style="width: 160px;" onchange="CS_onStatusChange();"<?php if (!$this->isJobOrdersMode && $this->selectedJobOrderID == -1): ?> disabled="disabled"<?php endif; ?>>
                    <option value="-1">(Select a Status)</option>
                    <?php foreach ($this->statusRS as $rowNumber => $statusData): ?>
                        <option<?php if ($this->selectedStatusID == $statusData['statusID']): ?> selected="selected"<?php endif; ?> value="<?php $this->_($statusData['statusID']) ?>"><?php $this->_($statusData['status']) ?></option>
                    <?php endforeach; ?>
                </select>
                &nbsp;&nbsp;
                <span id="triggerEmailSpan" style="display: none;"><input type="checkbox" name="triggerEmail" id="triggerEmail" onclick="AS_onSendEmailChange('triggerEmail', 'sendEmailCheckTR', 'visibleTR');" />Send E-Mail Notification to Candidate</span>
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
    <?php elseif ($this->selectedJobOrderID != -1): ?>
        CS_onRegardingChange();
    <?php endif; ?>
</script>

<?php else: ?>
    <?php if ($this->statusChanged): ?>
        <p>The candidate's status has been changed from <span class="bold"><?php $this->_($this->oldStatusDescription); ?></span> to <span class="bold"><?php $this->_($this->newStatusDescription); ?></span>.</p>
    <?php else: ?>
        <p>The candidate's status has not been changed.</p>
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
