<?php TemplateUtility::printHeader('Candidate - '.$this->cData['firstName'].' '.$this->cData['lastName'] . ' Questionnaire', array( 'js/activity.js', 'js/sorttable.js', 'js/match.js', 'js/lib.js', 'js/pipeline.js')); ?>
<?php if (!$this->print): ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active); ?>

        <?php TemplateUtility::printQuickSearch(); ?>
<?php endif; ?>
<main id="main" class="container-fluid py-2 oc-candidate-questionnaire-page"><div id="contents">
<?php if (!$this->print): ?>


            <header class="oc-page-header mb-2"><h1 class="h5 fw-semibold mb-0">Candidates: Questionnaire Results</h1></header>

            <h2 class="h6 card-header bg-secondary-subtle py-1 px-2 fw-semibold mb-2"><?php echo Template::escapeHtml($this->title); ?></h2>

            <div>
                <div class="row g-2 align-items-start mb-2">
                    <div class="col-12 col-sm">
                        <a class="btn btn-sm btn-outline-secondary" href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?m=candidates&a=show&candidateID=' . urlencode($this->candidateID)); ?>">&lt;- Back to Candidate Profile</a>
                    </div>
                    <div class="col-12 col-sm">
                        <?php
                        // Preserve the title through the controller's additional URL decoding.
                        $printQuery = http_build_query(array(
                            'm' => 'candidates',
                            'a' => 'show_questionnaire',
                            'candidateID' => $this->candidateID,
                            'questionnaireTitle' => urlencode($this->title),
                            'print' => 'yes',
                        ), '', '&');
                        ?>
                        <a href="<?php echo Template::escapeUrl(CATSUtility::getIndexName() . '?' . $printQuery); ?>">
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
                        <?php echo Template::escapeHtml($this->cData['lastName'] . ', ' . $this->cData['firstName']); ?><br>
                        <?php echo Template::escapeHtml($this->cData['address']); ?><br>
                        <?php if (!empty($this->cData['address2'])): ?>
                            <?php echo Template::escapeHtml($this->cData['address2']); ?><br>
                        <?php endif; ?>
                        <?php $location = trim($this->cData['city'] . ' ' . $this->cData['state'] . ' ' . $this->cData['zip']); ?>
                        <?php if ($location !== ''): ?>
                            <?php echo Template::escapeHtml($location); ?><br>
                        <?php endif; ?>
                        <?php $phones = trim($this->cData['phoneHome'] . ' ' . $this->cData['phoneWork'] . ' ' . $this->cData['phoneCell']); ?>
                        <?php if ($phones !== ''): ?>
                            <?php echo Template::escapeHtml($phones); ?><br>
                        <?php endif; ?>
                        <a href="<?php echo Template::escapeUrl('mailto:' . $this->cData['email1']); ?>"><?php echo Template::escapeHtml($this->cData['email1']); ?></a><?php echo strlen($this->cData['email1'] ?? '') > 0 ? '<br />' : ''; ?>
                        <br>
                        </span>
                    </div>
                    <div class="col-12 col-sm">
                        <span>
                        <b>Notes:</b>
                        <br>
                        <?php echo nl2br(Template::escapeHtml($this->cData['notes'])); ?>
                        <div class="mb-2"></div>
                        <b>Will Relocate:</b><br>
                        <?php echo $this->cData['canRelocate'] ? 'Yes' : 'No'; ?>
                        </span>
                    </div>
                </div>
            </div>
            <div class="mb-2"></div>

            <h2 class="h5">
            <?php echo Template::escapeHtml($this->qData[0]['questionnaireDescription'] ?? ''); ?>
            </h2>

            <div class="mb-2"></div>

            <div class="table-responsive"><table class="table table-sm table-striped table-hover align-middle mb-0"><thead><tr><th scope="col">Question</th><th scope="col">Answer</th></tr></thead><tbody>
            <?php $highlight = true; ?>
            <?php foreach ($this->qData as $question): ?>
                <?php $highlight = !$highlight; ?>
                <tr>
                    <td><?php echo Template::escapeHtml($question['questionText']); ?></td>
                    <td><?php echo Template::escapeHtml($question['answerText']); ?></td>
                </tr>

            <?php endforeach; ?>
            </tbody></table></div>

            <?php if (isset($this->resumeText) && !empty($this->resumeText)): ?>
            <div class="mb-2"></div>
            <div>
            <b><?php echo Template::escapeHtml($this->cData['firstName'] . ' ' . $this->cData['lastName']); ?>'s Resume:</b>
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
