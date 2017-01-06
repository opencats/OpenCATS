<?php /* $Id: AddModalPopup.tpl 3321 2007-10-25 22:03:10Z brian $ */ ?>
<?php TemplateUtility::printModalHeader('Job Order', array('modules/joborders/validator.js')); ?>
<div class="row">
    <div class="col-md-12">
        <h2><i class="glyphicon glyphicon-paste"></i> Job Orders: Add Job Order</h2>
    </div>
</div>

        <script type="text/javascript">
            var typeOfAdd="new";
        </script>

        <table class="table">
            <tr>
                <td><label><input type="radio" name="typeOfAddElement" onclick="document.getElementById('copyFrom').disabled=true; typeOfAdd='new';" checked>&nbsp;Empty Job Order</label></td>
            </tr>
            <tr>
                <td><label><input type="radio" name="typeOfAddElement" onclick="document.getElementById('copyFrom').disabled=false; typeOfAdd='existing';">&nbsp;Copy Existing Job Order</label></td>
            </tr>
            <tr id="hideShowCopyExisting">
                <td class="tdData">
                    <select name="copyFrom" id="copyFrom" class="form-control" disabled>
                        <?php foreach($this->rs as $index => $data): ?>
                            <option value="<?php echo($data['jobOrderID']); ?>"><?php $this->_($data['title'].' ('.$data['companyName'].')'); ?></option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
        </table>
        <input type="button" class="btn btn-default"  value="Create Job Order" onclick="parentGoToURL('<?php echo(CATSUtility::getIndexName()); ?>?m=joborders&amp;a=add&amp;jobOrderID='+document.getElementById('copyFrom').value+'&amp;typeOfAdd='+typeOfAdd);"/>&nbsp;
    </body>
</html>