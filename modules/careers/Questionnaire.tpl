<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */
?>
<section id="careerContent" class="card card-body p-4">
    <h1 class="h3 mb-4"><?php $this->_($this->data['description']); ?></h1>
    <?php foreach ($this->questions as $question): ?>
    <?php $field = 'questionnaire' . $this->questionnaireID . 'Question' . $question['questionID']; ?>
    <fieldset class="mb-4">
        <legend class="fs-6 fw-semibold"><?php $this->_($question['questionText']); ?></legend>
        <?php if ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_TEXT || empty($question['answers'])): ?>
            <label class="visually-hidden" for="<?php echo $field; ?>"><?php $this->_($question['questionText']); ?></label>
            <textarea class="form-control" rows="3" name="<?php echo $field; ?>" id="<?php echo $field; ?>" maxlength="<?php echo $question['maximumLength']; ?>"></textarea>
        <?php elseif ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_RADIO): ?>
            <?php $nochecked = true; ?>
            <?php foreach ($question['answers'] as $answer): ?>
            <div class="form-check">
                <label class="form-check-label">
                    <input class="form-check-input" type="radio" name="<?php echo $field; ?>" id="<?php echo $field; ?>" value="<?php echo $answer['answerID']; ?>"<?php if ($nochecked) { $nochecked = false; echo ' checked'; } ?>>
                    <?php $this->_($answer['answerText']); ?>
                </label>
            </div>
            <?php endforeach; ?>
        <?php elseif ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_CHECKBOX): ?>
            <?php foreach ($question['answers'] as $answer): ?>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="<?php echo $field . 'Answer' . $answer['answerID']; ?>" id="<?php echo $field . 'Answer' . $answer['answerID']; ?>" value="yes">
                <label class="form-check-label" for="<?php echo $field . 'Answer' . $answer['answerID']; ?>"><?php $this->_($answer['answerText']); ?></label>
            </div>
            <?php endforeach; ?>
        <?php elseif ($question['questionType'] == QUESTIONNAIRE_QUESTION_TYPE_SELECT): ?>
            <label class="visually-hidden" for="<?php echo $field; ?>"><?php $this->_($question['questionText']); ?></label>
            <select class="form-select" name="<?php echo $field; ?>" id="<?php echo $field; ?>">
                <?php foreach ($question['answers'] as $answer): ?>
                <option value="<?php echo $answer['answerID']; ?>"><?php $this->_($answer['answerText']); ?></option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>
    </fieldset>
    <?php endforeach; ?>
</section>
