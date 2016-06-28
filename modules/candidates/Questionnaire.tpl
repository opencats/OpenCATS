<?php /* $Id: Questionnaire.tpl 3668 2007-11-21 00:38:50Z brian $ */ ?>
<?php TemplateUtility::printHeader('Candidate - '.$this->cData['firstName'].' '.$this->cData['lastName'] . ' Questionnaire', array( 'js/activity.js', 'js/sorttable.js', 'js/match.js', 'js/lib.js', 'js/pipeline.js', 'js/attachment.js')); ?>
<?php if (!$this->print): ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>
    <div id="main">
        <?php TemplateUtility::printQuickSearch(); ?>

        <div id="contents">
            <table>
                <tr>
                    <td width="3%">
                        <img src="images/candidate.gif" width="24" height="24" border="0" alt="Candidates" style="margin-top: 3px;" />&nbsp;
                    </td>
                    <td><h2>Candidates: Questionnaire Results</h2></td>
               </tr>
            </table>

            <p class="note"><?php echo $this->title; ?></p>

            <table cellpadding="0" cellspacing="0" width="100%" border="0">
                <tr>
                    <td align="left" valign="top">
                        <input type="button" class="button" value="<- Back to Candidate Profile" onclick="document.location.href='<?php echo CATSUtility::getIndexName(); ?>?m=candidates&a=show&candidateID=<?php echo $this->candidateID; ?>';" />
                    </td>
                    <td align="right">
                        <a href="<?php echo CATSUtility::getIndexName() . '?' . str_replace('print=no', 'print=yes', $_SERVER['QUERY_STRING']); ?>">
                        <img src="images/actions/print.gif" border="0" />
                        Printer Friendly
                        </a>
                    </td>
                </tr>
            </table>

            <br /><br />

<?php endif; ?>

            <table cellpadding="0" cellspacing="0" width="100%" border="0">
                <tr>
                    <td width="40%" align="left" valign="top">
                        <span style="font-size: 16px;">
                        <b>Candidate Information:</b><br />
                        <?php echo $this->cData['lastName'] . ', ' . $this->cData['firstName']; ?><br />
                        <?php echo $this->cData['address']; ?><br />
                        <?php echo ($str = $this->cData['city'] . ' ' . $this->cData['state'] . ' ' . $this->cData['zip']) . strlen($str) > 2 ? '<br />' : ''; ?>
                        <?php echo ($str = $this->cData['phoneHome'] . ' ' . $this->cData['phoneWork'] . ' ' . $this->cData['phoneCell']) . strlen($str) > 2 ? '<br />' : ''; ?>
                        <a style="font-size: 16px;" href="mailto:<?php echo ($str = $this->cData['email1']); ?>"><?php echo $this->cData['email1']; ?></a><?php echo strlen($str) > 0 ? '<br />' : ''; ?>
                        <br />
                        </span>
                    </td>
                    <td align="left" valign="top" width="60%" style="padding-left: 10px;">
                        <span style="font-size: 14px;">
                        <b>Notes:</b>
                        <br />
                        <?php echo $this->cData['notes']; ?>
                        <br /><br />
                        <b>Will Relocate:</b><br />
                        <?php echo $this->cData['canRelocate'] ? 'Yes' : 'No'; ?>
                        </span>
                    </td>
                </tr>
            </table>
            <br /><br />

            <span style="font-size: 22px; font-weight: bold;">
            <?php echo $this->qData[0]['questionnaireDescription']; ?>
            </span>

            <br /><br />

            <table cellpadding="0" cellspacing="0" border="0" width="100%" style="border: 1px solid #c0c0c0;">
            <?php $highlight = true; ?>
            <?php foreach ($this->qData as $question): ?>
                <?php $highlight = !$highlight; ?>
                <tr>
                    <td valign="top" align="left" style="font-size: 14px; background-color: <?php echo $highlight ? '#f0f0f0' : '#f9f9f9'; ?>; padding: 10px;" width="60%"><?php echo $question['questionText']; ?></td>
                    <td valign="top" align="left" style="font-size: 14px; background-color: <?php echo $highlight ? '#f0f0f0' : '#f9f9f9'; ?>; padding: 10px; font-weight: bold;" width="40%"><?php echo $question['answerText']; ?></td>
                </tr>

            <?php endforeach; ?>
            </table>

            <?php if (isset($this->resumeText) && !empty($this->resumeText)): ?>
            <br /><br />
            <span style="font-size: 14px;">
            <b><?php echo $this->cData['firstName'] . ' ' . $this->cData['lastName']; ?>'s Resume:</b>
            <br />
            <div>
            <?php echo $this->resumeText; ?>
            </div>
            <?php endif; ?>
<?php if (!$this->print): ?>
        </div>
    </div>

<?php TemplateUtility::printFooter(); ?>
<?php endif; ?>
