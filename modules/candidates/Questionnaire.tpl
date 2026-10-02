<?php TemplateUtility::printHeader('Candidate - '.$this->cData['firstName'].' '.$this->cData['lastName'] . ' Questionnaire', array( 'js/activity.js', 'js/sorttable.js', 'js/match.js', 'js/lib.js', 'js/pipeline.js')); ?>
<?php if (!$this->print): ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>

        <?php TemplateUtility::printQuickSearch(); ?>
<?php endif; ?>
<main id="main" class="container-fluid py-2 oc-candidate-questionnaire-page"><div id="contents">
<?php if (!$this->print): ?>


            <header class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Candidates: Questionnaire Results</h1></header>

            <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2"><?php echo $this->title; ?></h2>

            <div>
                <div class="row g-2 align-items-start mb-2">
                    <div class="col-12 col-sm">
                        <button type="button" class="btn btn-sm btn-outline-secondary" value="<- Back to Candidate Profile" onclick="document.location.href='<?php echo CATSUtility::getIndexName(); ?>?m=candidates&a=show&candidateID=<?php echo $this->candidateID; ?>';"><- Back to Candidate Profile</button>
                    </div>
                    <div class="col-12 col-sm">
                        <a href="<?php echo CATSUtility::getIndexName() . '?' . str_replace('print=no', 'print=yes', $_SERVER['QUERY_STRING']); ?>">
                        <img src="images/actions/print.gif">
                        Printer Friendly
                        </a>
                    </div>
                </div>
            </div>

            <div class="mb-2"></div>

<?php endif; ?>

            <div>
                <div class="row g-2 align-items-start mb-2">
                    <div class="col-12 col-sm">
                        <span>
                        <b>Candidate Information:</b><br>
                        <?php echo $this->cData['lastName'] . ', ' . $this->cData['firstName']; ?><br>
                        <?php echo $this->cData['address']; ?><br>
                        <?php if (!empty($this->cData['address2'])): ?>
                            <?php echo $this->cData['address2']; ?><br>
                        <?php endif; ?>
                        <?php echo ($str = $this->cData['city'] . ' ' . $this->cData['state'] . ' ' . $this->cData['zip']) . strlen($str) > 2 ? '<br />' : ''; ?>
                        <?php echo ($str = $this->cData['phoneHome'] . ' ' . $this->cData['phoneWork'] . ' ' . $this->cData['phoneCell']) . strlen($str) > 2 ? '<br />' : ''; ?>
                        <a href="mailto:<?php echo ($str = $this->cData['email1']); ?>"><?php echo $this->cData['email1']; ?></a><?php echo strlen($str) > 0 ? '<br />' : ''; ?>
                        <br>
                        </span>
                    </div>
                    <div class="col-12 col-sm">
                        <span>
                        <b>Notes:</b>
                        <br>
                        <?php echo $this->cData['notes']; ?>
                        <div class="mb-2"></div>
                        <b>Will Relocate:</b><br>
                        <?php echo $this->cData['canRelocate'] ? 'Yes' : 'No'; ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="mb-2"></div>

            <h2 class="h5">
            <?php echo $this->qData[0]['questionnaireDescription']; ?>
            </h2>

            <div class="mb-2"></div>

            <div class="table-responsive"><table class="table table-sm table-striped table-hover align-middle mb-0"><thead><tr><th scope="col">Question</th><th scope="col">Answer</th></tr></thead><tbody>
            <?php $highlight = true; ?>
            <?php foreach ($this->qData as $question): ?>
                <?php $highlight = !$highlight; ?>
                <tr>
                    <td><?php echo $question['questionText']; ?></td>
                    <td><?php echo $question['answerText']; ?></td>
                </tr>

            <?php endforeach; ?>
            </tbody></table></div>

            <?php if (isset($this->resumeText) && !empty($this->resumeText)): ?>
            <div class="mb-2"></div>
            <div>
            <b><?php echo $this->cData['firstName'] . ' ' . $this->cData['lastName']; ?>'s Resume:</b>
            <br>
            <div>
            <?php echo $this->resumeText; ?>
            </div>
            </div>
            <?php endif; ?>
</div></main>
<?php if (!$this->print): ?>
<?php TemplateUtility::printFooter(); ?>
<?php endif; ?>

<?php if ($this->print): ?></body></html><?php endif; ?>
