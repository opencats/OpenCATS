<?php TemplateUtility::printModalHeader('Job Order', array('modules/joborders/validator.js')); ?>
<main class="container-fluid p-2 oc-joborder-addmodalpopup">
    <section class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Job Orders: Add Job Order</h1></section>

    <script>
            var typeOfAdd="new";
        </script>

    <section class="card mb-2 oc-joborder-other">
        <div class="card-header bg-secondary-subtle py-1 px-2 fw-semibold">Create from</div>
        <div class="card-body p-2">
            <div class="row g-2 align-items-start mb-2">
                <div class="col-12">
                    <label class="form-check-label"><input class="form-check-input" type="radio" name="typeOfAddElement" onclick="document.getElementById('copyFrom').disabled=true; typeOfAdd='new';" checked>&nbsp;Empty Job Order</label></div>
            </div>
            <div class="row g-2 align-items-start mb-2">
                <div class="col-12">
                    <label class="form-check-label"><input class="form-check-input" type="radio" name="typeOfAddElement" onclick="document.getElementById('copyFrom').disabled=false; typeOfAdd='existing';">&nbsp;Copy Existing Job Order</label></div>
            </div>
            <div class="row g-2 align-items-start mb-2" id="hideShowCopyExisting">
                <div class="col-12">
                    <label for="copyFrom" class="form-label small mb-1">Job order to copy</label><select class="form-select form-select-sm" name="copyFrom" id="copyFrom" disabled>
                    <?php foreach($this->rs as $index => $data): ?>
                    <option value="<?php echo($data['jobOrderID']); ?>"><?php $this->_($data['title'].' ('.$data['companyName'].')'); ?></option>
                    <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </section>
    <button type="button" class="btn btn-sm btn-primary" value="Create Job Order" onclick="parentGoToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=add&amp;jobOrderID='+document.getElementById('copyFrom').value+'&amp;typeOfAdd='+typeOfAdd);">Create Job Order</button>&nbsp;
    <button type="button" class="btn btn-sm btn-outline-secondary" name="close" value="Close" onclick="parentHidePopWin();">Close</button>
</main>
</body>
</html>
