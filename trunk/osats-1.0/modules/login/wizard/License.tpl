<div id="login" style="">
    <div id="subFormBlock" style="text-align: center; margin-bottom: 10px;">
        <b>In order to continue, you must agree to the license terms below:</b>
        <br /><br />
        <iframe style="width: 530px; height: 175px;" src="<?php echo (isset($this->terms) ? $this->terms : 'LICENSE'); ?>"></iframe>
        <br />
        <br />
        <input type="checkbox" id="iAgree" name="iAgree" onclick="if (this.checked) enableNext(); else disableNext();">I agree to the terms and conditions listed above.<br />
    </div>
</div>
