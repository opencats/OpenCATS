<?php //FIXME: This goes in the UI. ?>
<?php if (isset($_SESSION['CATS_PARSE_TEMP']) && is_array($_SESSION['CATS_PARSE_TEMP'])): ?>
    <?php $currentDocument = count($_SESSION['CATS_PARSE_TEMP']); ?>
<?php else: ?>
    <?php $currentDocument = 0; ?>
<?php endif; ?>
<div style="font-size: 22px; font-weight: bold; text-align: center; margin: 60px 0 60px 0; color: #666666;">
Please wait whilst OpenCATS processes your resume documents...
<br />
<span style="font-size: 14px; color: #666666;" id="timeWait">&nbsp;</span>
</div>

<div id="statusBarContainer">
    <div id="statusBar">&nbsp;</div>
</div>
<br />

<div>
    <table cellpadding="0" cellspacing="0">
        <tr>
            <td style="color: #666666;" width="409" align="right">Processing: &nbsp;</td>
            <td id="fileName" style="color: #163C90; font-weight: bold;">"<?php echo (isset($this->files) && isset($this->files[$currentDocument])) ? $this->files[$currentDocument]['realName'] : ''; ?>"</td>
            <td><span style="color: #666666;">...</span></td>
        </tr>
    </table>
</div>

<script type="text/javascript">
    var currentDocument = <?php echo $currentDocument; ?>;

    <?php if (isset($this->files) && !empty($this->files)): ?>
        setProgressBar(
            <?php echo number_format($currentDocument / count($this->files) * 100, 0); ?>,
            '<?php echo addslashes($this->files[$currentDocument]['realName']); ?>'
        );
    <?php endif; ?>

    <?php echo $this->js; ?>

    startDocumentParsing();
</script>
