<HTML>
<HEAD>
<TITLE>Page Preview</TITLE>
</HEAD>
<FRAMESET rows="65, *">
      <FRAME src="<?php echo(CATSUtility::getIndexName()); ?>?m=settings&amp;a=previewPageTop&amp;message=<?php echo(urlencode($this->previewMessage)); ?>">
      <FRAME src="<?php echo($this->previewPage); ?>">
</FRAMESET>
</HTML>
