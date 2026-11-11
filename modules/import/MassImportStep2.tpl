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
    <span id="fileName" class="fw-semibold"></span>...
</p>

<script type="text/javascript">
    var currentDocument = 0;
    documents = <?php
        $queue = array();
        foreach ($this->files as $documentID => $file)
        {
            $queue[] = array('id' => $documentID, 'name' => $file['realName']);
        }
        echo json_encode($queue, JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE);
    ?>;
    if (documents.length)
    {
        setProgressBar(0, documents[0].name);
    }

    startDocumentParsing();
</script>
