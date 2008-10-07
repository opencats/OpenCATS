<?php
/**
 * OSATS
 */

include_once('./lib/Questionnaire.php');

class DefaultQuestionnaireUtility {
    private $_defaultQuestionnaires;

    public function __construct()
    {
        $this->_defaultQuestionnaires = array(
            array(
                'title' => 'Sample IT Questionnaire',
                'description' => 'Please answer the following questions:',
                'isActive' => true,
                'questions' => array(
                    array(
                        'questionType' => QUESTIONNAIRE_QUESTION_TYPE_RADIO,
                        'questionText' => 'Are you at least 18 years of age?',
                        'questionPosition' => 1,
                        'answers' => array(
                            array(
                                'answerText' => 'Yes',
                                'answerPosition' => 1
                            ),
                            array(
                                'answerText' => 'No',
                                'answerPosition' => 2,
                                'actionNotes' => 'Under 18'
                            )
                        )
                    ),
                    array(
                        'questionType' => QUESTIONNAIRE_QUESTION_TYPE_RADIO,
                        'questionText' => 'Are you a resident of the U.S. or permitted to work in the U.S.?',
                        'questionPosition' => 2,
                        'answers' => array(
                            array(
                                'answerText' => 'Yes',
                                'answerPosition' => 1,
                                'actionNotes' => 'Authorized to work in the U.S.',
                            ),
                            array(
                                'answerText' => 'No',
                                'answerPosition' => 2,
                                'actionNotes' => 'NOT AUTHORIZED to work in the U.S.',
                            )
                        )
                    ),
                    array(
                        'questionType' => QUESTIONNAIRE_QUESTION_TYPE_SELECT,
                        'questionText' => 'How would you rate your experience level?',
                        'questionPosition' => 3,
                        'answers' => array(
                            array(
                                'answerText' => 'Junior',
                                'answerPosition' => 1,
                                'actionNotes' => 'Rates self as junior-level'
                            ),
                            array(
                                'answerText' => 'Intermediate',
                                'answerPosition' => 2,
                                'actionNotes' => 'Rates self as intermediate-level'
                            ),
                            array(
                                'answerText' => 'Expert',
                                'answerPosition' => 3,
                                'actionNotes' => 'Rates self as expert-level'
                            )
                        )
                    ),
                    array(
                        'questionType' => QUESTIONNAIRE_QUESTION_TYPE_CHECKBOX,
                        'questionText' => 'Which languages do you have experience with?',
                        'questionPosition' => 4,
                        'answers' => array(
                            array(
                                'answerText' => ($ans = 'C/C++'),
                                'answerPosition' => 1,
                                'actionKeySkills' => $ans
                            ),
                            array(
                                'answerText' => ($ans = 'PHP'),
                                'answerPosition' => 2,
                                'actionKeySkills' => $ans
                            ),
                            array(
                                'answerText' => ($ans = 'Perl'),
                                'answerPosition' => 3,
                                'actionKeySkills' => $ans
                            ),
                            array(
                                'answerText' => ($ans = 'Java'),
                                'answerPosition' => 4,
                                'actionKeySkills' => $ans
                            ),
                            array(
                                'answerText' => ($ans = 'Python'),
                                'answerPosition' => 5,
                                'actionKeySkills' => $ans
                            ),
                            array(
                                'answerText' => ($ans = 'Ruby'),
                                'answerPosition' => 6,
                                'actionKeySkills' => $ans
                            ),
                            array(
                                'answerText' => ($ans = '.NET'),
                                'answerPosition' => 7,
                                'actionKeySkills' => $ans
                            ),
                            array(
                                'answerText' => ($ans = 'Visual Basic'),
                                'answerPosition' => 8,
                                'actionKeySkills' => $ans
                            )
                        )
                    ),
                    array(
                        'questionType' => QUESTIONNAIRE_QUESTION_TYPE_CHECKBOX,
                        'questionText' => 'Which databases do you have experience with?',
                        'questionPosition' => 5,
                        'answers' => array(
                            array(
                                'answerText' => ($ans = 'MySQL'),
                                'answerPosition' => 1,
                                'actionKeySkills' => $ans
                            ),
                            array(
                                'answerText' => ($ans = 'PostgreSQL'),
                                'answerPosition' => 2,
                                'actionKeySkills' => $ans
                            ),
                            array(
                                'answerText' => ($ans = 'Microsoft SQL Server'),
                                'answerPosition' => 3,
                                'actionKeySkills' => $ans
                            ),
                            array(
                                'answerText' => ($ans = 'Oracle'),
                                'answerPosition' => 4,
                                'actionKeySkills' => $ans
                            )
                        )
                    ),
                    array(
                        'questionType' => QUESTIONNAIRE_QUESTION_TYPE_SELECT,
                        'questionText' => 'Are you willing to relocate?',
                        'questionPosition' => 6,
                        'answers' => array(
                            array(
                                'answerText' => 'Yes',
                                'answerPosition' => 1,
                                'actionCanRelocate' => 1
                            ),
                            array(
                                'answerText' => 'Yes, with a moving allowance',
                                'answerPosition' => 2,
                                'actionCanRelocate' => 1,
                                'actionNotes' => 'Requires moving allowance'
                            ),
                            array(
                                'answerText' => 'No',
                                'answerPosition' => 3,
                                'actionCanRelocate' => 0
                            )
                        )
                    ),
                )
            )
        );
    }

    public function get()
    {
        return $this->_defaultQuestionnaires;
    }
}

?>