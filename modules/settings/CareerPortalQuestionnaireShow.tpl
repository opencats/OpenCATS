<?php if (!$this->isModal): ?>
<?php TemplateUtility::printHeader('Settings', array('js/questionnaire.js')); ?>
<?php TemplateUtility::printHeaderBlock(); ?>
<?php TemplateUtility::printTabs($this->active, $this->subActive); ?>
    <?php TemplateUtility::printQuickSearch(); ?>
<main id="main" class="container-fluid py-2">

        <div id="contents">
            <header class="oc-page-header mb-2">
                <h1 class="h5 fw-semibold mb-0">Settings: Administration: Questionnaire Preview</h1>
            </header>
            <br />

            <form method="post" action="<?php echo CATSUtility::getIndexName(); ?>?m=settings&a=careerPortalQuestionnairePreview&questionnaireID=<?php echo $this->questionnaireID; ?>" name="questionnairePreviewForm">
<?php endif; ?>
    <?php if ($this->isModal): ?>
    <style>
        td.QAquestionText { padding: 10px; }
        td.QAanswerText { padding: 10px; }
        .QAinput { }
        .QAtextarea { width: 400px; height: 75px; }
        .QAtext { border: 1px solid #888888; padding: 5px; }
        .QAtext:hover { border: 1px solid black; padding: 5px; background-color: #f8f8f8; }
        .QAtext:focus { border: 1px solid black; padding: 5px; background-color: #f0f0f0; }
        .QAselect { border: 1px solid #888888; padding: 5px; }
        .QAselect:hover { border: 1px solid black; padding: 5px; background-color: #f8f8f8; }
        td.QAhighlightOn {  }
        td.QAhighlightOff {  }
    </style>
    <?php endif; ?>

    <div style="padding: 0 15px 0 15px;">

    <span style="font-size: 18px; font-weight: bold;">
    <?php $this->_($this->data['description']); ?>
    </span>

    <br /><br />

    <?php if (!$this->isModal): ?><div class="table-responsive mb-2"><?php endif; ?>
    <table cellpadding="0" cellspacing="0" border="0" width="100%" style="padding: 5px;"<?php if (!$this->isModal): ?> class="table table-sm align-middle"<?php endif; ?>>
    <?php $highlightRow = false; ?>
    <?php foreach ($this->questions as $question): ?>
        <?php $highlightRow = !$highlightRow; ?>

        <tr>
            <td class="QAquestionText QAhighlight<?php echo $highlightRow ? 'On' : 'Off'; ?>" width="50%" align="left" valign="top"><?php $this->_($question['questionText']); ?></td>
            <td class="QAanswerText QAhighlight<?php echo $highlightRow ? 'On' : 'Off'; ?>" width="50%" align="left" valign="top">
                <?php if ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_TEXT || empty($question['answers'])): ?>
                    <textarea name="questionnaire<?php echo $this->questionnaireID; ?>Question<?php echo $question['questionID']; ?>" id="questionnaire<?php echo $this->questionnaireID; ?>Question<?php echo $question['questionID']; ?>" maxlength="<?php echo $question['maximumLength']; ?>" class="QAtextarea<?php if (!$this->isModal): ?> form-control form-control-sm<?php endif; ?>"></textarea>

                <?php elseif ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_RADIO): ?>
                    <?php $nochecked = true; ?>
                    <?php foreach ($question['answers'] as $answer): ?>
                        <div style="padding: 2px 0 2px 0;" class="QAinput">
                        <input type="radio"<?php if (!$this->isModal): ?> class="form-check-input"<?php endif; ?> name="questionnaire<?php echo $this->questionnaireID; ?>Question<?php echo $question['questionID']; ?>" value="<?php echo $answer['answerID']; ?>" id="questionnaire<?php echo $this->questionnaireID; ?>Question<?php echo $question['questionID']; ?>"<?php if ($nochecked) { $nochecked = false; echo ' checked'; } ?> /> <?php $this->_($answer['answerText']); ?>
                        </div>
                    <?php endforeach; ?>

                <?php elseif ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_CHECKBOX): ?>
                    <?php foreach ($question['answers'] as $answer): ?>
                        <div style="padding: 2px 0 2px 0;" class="QAinput">
                        <input type="checkbox"<?php if (!$this->isModal): ?> class="form-check-input"<?php endif; ?> name="questionnaire<?php echo $this->questionnaireID; ?>Question<?php echo $question['questionID']; ?>Answer<?php echo $answer['answerID']; ?>" id="questionnaire<?php echo $this->questionnaireID; ?>Question<?php echo $question['questionID']; ?>Answer<?php echo $answer['answerID']; ?>" value="yes" /> <?php $this->_($answer['answerText']); ?>
                        </div>
                    <?php endforeach; ?>

                <?php elseif ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_SELECT): ?>
                    <select name="questionnaire<?php echo $this->questionnaireID; ?>Question<?php echo $question['questionID']; ?>" id="questionnaire<?php echo $this->questionnaireID; ?>Question<?php echo $question['questionID']; ?>" class="QASelect<?php if (!$this->isModal): ?> form-select form-select-sm<?php endif; ?>">
                    <?php foreach ($question['answers'] as $answer): ?>
                        <option value="<?php echo $answer['answerID']; ?>"><?php $this->_($answer['answerText']); ?></option>
                    <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </td>
        </tr>

    <?php endforeach; ?>
    </table>
    <?php if (!$this->isModal): ?></div><?php endif; ?>

    </div>

<?php if (!$this->isModal): ?>
            </form>
        </div>


    </main>
<?php TemplateUtility::printFooter(); ?>
<?php endif; ?>
