<?php //FIXME: This goes in the UI. ?>
<?php if (isset($_SESSION['CATS_PARSE_TEMP']) && is_array($_SESSION['CATS_PARSE_TEMP'])): ?>
    <?php $currentDocument = count($_SESSION['CATS_PARSE_TEMP']); ?>
<?php else: ?>
    <?php $currentDocument = 0; ?>
<?php endif; ?>
<div class="text-center my-4 fw-semibold" role="status">
Please wait whilst OpenCATS processes your resume documents...
<br />
<span class="small text-body-secondary" id="timeWait">&nbsp;</span>
</div>

<div id="statusBarContainer" class="progress" role="progressbar" aria-label="Resume processing" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
    <div id="statusBar" class="progress-bar" style="width: 0%;">&nbsp;</div>
</div>
<br />

<p class="text-center text-break">Processing:
    <span id="fileName" class="fw-semibold">"<?php echo (isset($this->files) && isset($this->files[$currentDocument])) ? $this->files[$currentDocument]['realName'] : ''; ?>"</span>...
</p>

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
