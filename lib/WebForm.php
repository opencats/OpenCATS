<?php
/**
 * CATS
 * Form Generation/Validation Library
 *
 * Copyright (C) 2006 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: WebForm.php 3705 2007-11-26 23:34:51Z will $
 */

include_once('./lib/Graphs.php');

define('WFT_TEXT',                  1);
define('WFT_PASSWORD',              2);
define('WFT_SELECT',                3);
define('WFT_PHONE',                 4);
define('WFT_EMAIL',                 5);
define('WFT_CC_NUMBER',             6);
define('WFT_CC_EXPIRATION',         7);
define('WFT_CC_TYPE',               8);
define('WFT_ANTI_SPAM_IMAGE',       9);
define('WFT_DATE',                  10);
define('WFT_BOOLEAN',               11);
define('WFT_CURRENCY',              12);
define('WFT_CC_CVV2',               13);
define('WFT_TEXTAREA',              14);

define('WTR_PHONE',                 '/^[0-9]{3}[\-\.]?[0-9]{3}[\-\.]?[0-9]{4}$/');
//define('WTR_EMAIL',                 '/^[_a-zA-Z0-9\-]+(.[_a-zA-Z0-9\-]+)*@[a-zA-Z0-9\-]+(.[A-Za-z0-9\-]+)*(.[A-Za-z]{2,4})$/');
define('WTR_EMAIL',                 "/^.*@.*$/");
define('WTR_CC_NUMBER',             '/^[0-9]{4}-?[0-9]{4}-?[0-9]{4}-?[0-9]{3,4}-?$/');
define('WTR_CC_CVV2',               '/^[0-9]{3,4}$/');
define('WTR_DATE',                  '/^\d{1,2}(\-|\/|\.)\d{1,2}\1\d{2,4}$/');
define('WTR_CURRENCY',              '/^[\$]?[0-9]+[\.]?[0-9]{0,2}$/');

define('WTRF_PHONE',                'Invalid phone number, use xxx-xxx-xxxx');
define('WTRF_EMAIL',                'Invalid e-mail address');
define('WTRF_CC_NUMBER',            'Invalid credit card number, use xxxx-xxxx-xxxx-xxxx (with or without slashes)');
define('WTRF_CC_CVV2',              'That is not a valid CVV2 code');
define('WTRF_DATE',                 'Invalid date. Use format ##/##/####');
define('WTRF_CURRENCY',             'Invalid dollar amount. Use format $###.## with no commas.');

// Special Credit Card Values
define('WF_CARD_NAME_VISA',            'Visa');
define('WF_CARD_NAME_MASTERCARD',      'MasterCard');
define('WF_CARD_NAME_AMERICANEXPRESS', 'American Express');
define('WF_CARD_NAME_DISCOVER',        'Discover');

define('WF_CARD_TYPE_VISA',            100);
define('WF_CARD_TYPE_MASTERCARD',      200);
define('WF_CARD_TYPE_AMERICANEXPRESS', 300);
define('WF_CARD_TYPE_DISCOVER',        400);


/**
 *	Form Generation/Validation Library
 *	@package    CATS
 *	@subpackage Library
 */
class WebForm
{
    private $_fields;
    private $_requiredFields;
    private $_layout;
    private $_defaultLayout;
    private $_printedPostBack;
    private $_tabIndex;
    private $_verifyForm;
    private $_printedHelpBox;
    private $_relPath;

    /**
     * Populate private variables with default data.
     *
     */
    public function __construct()
    {
        $this->_fields = array();
        $this->_requiredFields = array();
        $this->_layout = '';
        $this->_defaultLayout = '';
        $this->_printedPostback = 0;
        $this->_tabIndex = 1;
        $this->_verifyForm = 0;
        $this->_printedHelpBox = 0;
        $this->_relPath = '';
    }

    /**
     * Sets the relative path of the current page to be used when generating
     * images or links to the primary website.
     *
     * @param string Path name (i.e.: ../)
     */
    public function setRelativePath($txt)
    {
        $this->_relPath = $txt;
    }

    /**
     * A verify form does not directly display components. Instead, it displays
     * the data which can be clicked to load the component for altering data.
     * Typically step #2 of the form process (verification).
     *
     * @param boolean true for verify mode
     */
    public function setVerifyForm($tf)
    {
        $this->_verifyForm = $tf;
    }

    /**
     * Adds a field to the webform to collect data and be validated.
     *
     * @param string Unique identifier (i.e.: firstName)
     * @param string Text to display near the data (i.e.: First Name)
     * @param int See the WFT_ constants in the WebForm library (i.e.: WFT_PHONE_NUMBER)
     * @param boolean Is the field required to continue (true = required)
     * @param int Display size of the field (i.e.: <input size="this value">)
     * @param int Minimum length allowed for input.
     * @param int Maximum length allowed for input.
     * @param mixed The default value for text inputs. Array of values for WFT_SELECT.
     * @param string Perl regular expression that must be matched to continue.
     * @param string Message to display when regex_test fails.
     * @param string Help message to display when a user hovers the field.
     * @param string Rules to display when the user hovers the field.
     */
    public function addField($id, $caption, $type, $required = false, $size = 16, $minlen = 0, $maxlen = 255,
        $defaultValue = '', $regex_test = '', $regex_fail = '', $helpBody = '',
        $helpRules = '')
    {
        switch ($type)
        {
            case WFT_CURRENCY:
                if ($regex_test == '') $regex_test = WTR_CURRENCY;
                if ($regex_fail == '') $regex_fail = WTRF_CURRENCY;
                $minlen = 0;
                $maxlen = 10;
                break;
            case WFT_PHONE:
                if ($regex_test == '') $regex_test = WTR_PHONE;
                if ($regex_fail == '') $regex_fail = WTRF_PHONE;
                $minlen = 10;
                $maxlen = 13;
                break;
            case WFT_EMAIL:
                if ($regex_test == '') $regex_test = WTR_EMAIL;
                if ($regex_fail == '') $regex_fail = WTRF_EMAIL;
                $minlen = 10;
                $maxlen = 255;
                break;
            case WFT_CC_NUMBER:
                if ($regex_test == '') $regex_test = WTR_CC_NUMBER;
                if ($regex_fail == '') $regex_fail = WTRF_CC_NUMBER;
                $size = 30;
                $minlen = 15;
                $maxlen = 19;
                break;
            case WFT_CC_CVV2:
                if ($regex_test == '') $regex_test = WTR_CC_NUMBER;
                if ($regex_fail == '') $regex_fail = WTRF_CC_NUMBER;
                $size = 5;
                $minlen = 3;
                $maxlen = 4;
                break;
            case WFT_DATE:
                if ($regex_test == '') $regex_test = WTR_DATE;
                if ($regex_fail == '') $regex_fail = WTRF_DATE;
                $minlen = 6;
                $maxlen = 10;
                break;
            case WFT_CC_TYPE:
                $defaultValue = array(
                    array( 'id' => WF_CARD_NAME_VISA, 'caption' => WF_CARD_NAME_VISA, 'selected' => false ),
                    array( 'id' => WF_CARD_NAME_MASTERCARD, 'caption' => WF_CARD_NAME_MASTERCARD, 'selected' => false ),
                    array( 'id' => WF_CARD_NAME_AMERICANEXPRESS, 'caption' => WF_CARD_NAME_AMERICANEXPRESS, 'selected' => false ),
                    array( 'id' => WF_CARD_NAME_DISCOVER, 'caption' => WF_CARD_NAME_DISCOVER, 'selected' => false )
                );
                break;
            case WFT_BOOLEAN:
                $defaultValue = array(
                    array( 'id' => 'true', 'caption' => 'True', 'selected' => false ),
                    array( 'id' => 'false', 'caption' => 'False', 'selected' => false )
                );
                $minlen = 0;
                $maxlen = 10;
                break;
            case WFT_CC_EXPIRATION:
                // expiration has two select boxes
                $this->_tabIndex++;
                break;
            case WFT_ANTI_SPAM_IMAGE:
                $minlen = 6;
                $maxlen = 6;
                break;
        }
        $this->_fields[] = array(
            'id' => $id,
            'caption' => $caption,
            'size' => $size,
            'type' => $type,
            'required' => $required,
            'defaultValue' => $defaultValue,
            'validatedData' => '',
            'regex_test' => $regex_test,
            'regex_fail' => $regex_fail,
            'length' => array( $minlen, $maxlen ),
            'helpBody' => $helpBody,
            'html' => array(),
            'helpRules' => $helpRules,
            'tabIndex' => $this->_tabIndex++
        );
        $this->_defaultLayout .= sprintf('[%s][NL]', $id);
    }

    /**
     * Sets the layout of the form. Use [INSERT_FIELD_ID] to specify the insertion point
     * of a field. Use [NL] to indicate a new line.
     *
     * Example: [firstName][lastName][NL][address][state]
     *          This will create a 2x2 form with first and last names on the first line
     *          and address and state on the second.
     *
     * @param string
     */
    public function setLayout($text)
    {
        $this->_layout = $text;
    }

    /**
     * Used to pre-populate fields with data. This is useful if you're displaying
     * fields in "edit" fashion where the data already exists and needs to be
     * edited after the fact.
     *
     * Example $vFields value: array( "INSERT_FIELD_ID" => "Enter Value" )
     *
     * @param array
     */
    public function setValidatedFields($vfields)
    {
        foreach($vfields as $vfieldname => $vfieldval)
        {
            for ($x=0; $x < count($this->_fields); $x++)
            {
                if (!strcmp($this->_fields[$x]['id'], $vfieldname))
                {
                    $this->_fields[$x]['validatedData'] = $vfieldval;
                }
            }
        }
    }

    /**
     * Get the validated data from the fields on the webform. This is formatted
     * data that needs no further validity-checking.
     *
     * @return string
     */
    public function getValidatedFields()
    {
        $retVal = array();
        $errors = $this->validateFields();
        foreach($this->_fields as $field)
        {
            $retVal[$field['id']] = $field['validatedData'];
        }
        return array( $retVal, $errors );
    }

    /**
     * Validate all fields on the WebForm against any regular expressions provided,
     * constraints of their WFT_ field type, and for minimum and maximum size noting
     * all errors and successes appropriately.
     *
     * @return array of errors
     */
    private function validateFields()
    {
        $errors = array();
        for ($x=0; $x < count($this->_fields); $x++)
        {
            $field = $this->_fields[$x];
            if ($field['type'] == WFT_CC_EXPIRATION)
            {
                // one or both fields left blank
                if (strlen(trim($this->getPostValue($field['id'] . 'Month'))) == 0 ||
                    strlen(trim($this->getPostValue($field['id'] . 'Year'))) == 0)
                {
                    if ($field['required'])
                        $errors[] = 'You must select an card expiration month and year';
                    $monthValue = $yearValue = -1;
                    $value = '';
                }
                else
                {
                    $monthValue = intval($this->getPostValue($field['id'] . 'Month'));
                    $yearValue = intval($this->getPostValue($field['id'] . 'Year'));
                    $curYear = intval(date('Y'));
                    if ($yearValue < $curYear)
                        $errors[] = 'The expiration year is in the past';
                    if ($monthValue < 1 || $monthValue > 12)
                        $errors[] = 'The expiration month is not valid';
                }
            }
            else if($field['required'] && !strlen(trim($this->getPostValue($field['id']))))
            {
                if (strlen($field['caption']) > 0)
                    $errors[] = $field['caption'] . ' is a required field';
                else
                    $errors[] = 'This field is required';
                $value = '';
            }
            else if($field['type'] == WFT_CURRENCY)
            {
                $value = trim($this->getPostValue($field['id']));
                $value = str_replace('$', '', $value);
                $cur = floatval($value);
                $value = strval($cur);
            }
            else if($field['type'] == WFT_ANTI_SPAM_IMAGE)
            {
                $antiSpamInput = $this->getPostValue($field['id']);
                $wordVerifyID = $this->getPostValue('wordVerifyID');
                $graphs = new Graphs();
                $wordVerifyText = $graphs->getVerificationImageText($wordVerifyID);
                if (strtoupper($antiSpamInput) != $wordVerifyText || $antiSpamInput == '')
                {
                    $errors[] = 'The text you entered did not correspond with the text in the security image';
                    $value = 0;
                }
                else
                {
                    $value = 1;
                }
                $graphs->clearVerificationImageText($wordVerifyID);
            }
            else if($field['type'] == WFT_SELECT || $field['type'] == WFT_CC_TYPE || $field['type'] == WFT_BOOLEAN)
            {
                $value = $this->getPostValue($field['id']);
                if (!strcmp($value, 'noset'))
                {
                    $errors[] = $field['caption'] . ': You must select an option';
                }
            }
            else if($field['type'] == WFT_CC_NUMBER)
            {
                $value = '';
                // Clean credit card number input
                $cardNumber = preg_replace('/[^0-9]/', '', $this->getPostValue($field['id']));

                if ($field['required'] == false && !strlen($cardNumber))
                {
                    $value = '';
                }
                else
                {
                    // Guess the card type by using a pregex pattern matching algorithm
                    $cardType = $this->getCreditCardTypeByNumber($cardNumber);
                    if ($cardType == -1)
                        $errors[] = 'The credit card number you entered is not a recognized Visa, MasterCard, American Express '
                            . 'or Discover card.';
                    else if (!$this->isCardNumberValid($cardType, $cardNumber))
                        $errors[] = 'The credit card number you entered has not been recognized and may be invalid.';
                    else
                    {
                        // Valid card number, now change all card type fields to match
                        // the autodetected card type (visa, mastercard, etc.)
                        $value = $cardNumber;
                        $cardTypeName = $this->getCreditCardName($cardType);

                        for ($y=0; $y < count($this->_fields); $y++)
                        {
                            if ($this->_fields[$y]['type'] == WFT_CC_TYPE)
                            {
                                $this->_fields[$y]['validatedDataOverride'] = $cardTypeName;
                                $this->_fields[$y]['validatedData'] = $cardTypeName;
                            }
                        }
                    }
                }
            }
            else
            {
                $value = trim($this->getPostValue($field['id']));

                if (!($field['required'] == false && !strlen($value)))
                {
                    if (strlen($field['regex_test']) > 0)
                    {
                        if (!preg_match($field['regex_test'], $value))
                        {
                            $errors[] = $field['regex_fail'];
                        }
                    }
                    if (strlen($value) < $field['length'][0] || strlen($value) > $field['length'][1])
                    {
                        if ($field['length'][0] == $field['length'][1])
                        {
                            if (strlen(trim($field['caption'])) > 0)
                                $errors[] = sprintf("%s must be %d characters in length",
                                    $field['caption'], $field['length'][0]);
                            else
                                $errors[] = sprintf("This field must be %d characters in length",
                                    $field['length'][0]);
                        }
                        else
                            $errors[] = sprintf("%s must be between %s characters in length",
                                $field['caption'], implode(' and ', $field['length']));
                    }
                }
                $value = str_replace(array("\r","\n","\t","\f"), '', strip_tags($value));
            }

            // Set the validated (form returned) data
            switch($field['type'])
            {
                case WFT_CC_EXPIRATION:
                    if ($monthValue != -1 && $yearValue != -1)
                        $this->_fields[$x]['validatedData'] = sprintf('%d/%d', $monthValue, $yearValue);
                    else
                        $this->_fields[$x]['validatedData'] = '';
                    break;
                default:
                    if (isset($this->_fields[$x]['validatedDataOverride']) && strlen($this->_fields[$x]['validatedDataOverride']))
                        $this->_fields[$x]['validatedData'] = $this->_fields[$x]['validatedDataOverride'];
                    else
                        $this->_fields[$x]['validatedData'] = $value;
                    break;
            }
        }
        return $errors;
    }

    /**
     * Get the HTML output for the entire webform.
     *
     * @param string HTML arguments to be inserts in the <table> object containing the webform.
     * @return string HTML to be outputted
     */
    public function getForm($args = '')
    {
        if (strlen($args) > 0) $args = ' ' . $args;
        if (!strlen($form = $this->_layout))
            $form = $this->_defaultLayout;

        // if this is a post back where the fields have been completed and need to be validated/populated
        if (!strcmp($this->getPostValue('webFormPostBack'), '1'))
        {
            // the fields have received input
            $this->validateFields();
        }

        for ($x=0; $x<count($this->_fields); $x++)
        {
            if (isset($this->_fields[$x]))
            {
                $field = $this->_fields[$x];
                if (strpos($form, $field['id']) !== false)
                {
                    $form = str_replace(
                        '[' . $field['id'] . ']',
                        sprintf(
                            "<td valign=\"top\" align=\"right\" style=\"padding-right: 5px; font-size: 10pt; font-weight: normal;\">%s</td>\n"
                            . "<td valign=\"top\" align=\"left\" style=\"padding-right: 5px; height: 25px;\">%s\n"
                            . "<div id=\"%sCaption\" class=\"webFormCaption\"></div></td>\n",
                            $this->getFieldCaption($field),
                            ($this->_verifyForm ? $this->getFieldVerify($field) : $this->getFieldInput($field)),
                            $field['id']
                        ),
                        $form
                    );
                }
            }
        }
        $form = str_replace('[NL]', "</tr>\n<tr>\n", $form);
        $form = sprintf("<table%s>\n", $args) . $form . "</table>\n";

        if (!$this->_printedPostBack)
        {
            $form = "<input type=\"hidden\" name=\"webFormPostBack\" value=\"1\" />\n" . $form;
            $this->_printedPostBack = 1;
        }

        if (!$this->_printedHelpBox)
        {
            $form .= '<div class="webFormHelpBox" id="webFormHelpBox"> </div>';
            $form .= '<div class="webFormErrorBox" id="webFormErrorBox"> </div>';
            $this->_printedHelpBox = 1;
        }

        return $form;
    }

    /**
     * Add javascript/HTML to a field element if and when it's displayed. Available
     * $html_tag values are the standard onmouseover, onmouseout, onclick, etc.. The
     * $value supplied should be pure javascript (with the special exclusion of double
     * quote " characters.)
     *
     * @param string ID of the field
     * @param string Name of the tag (i.e.: onmouseover)
     * @param string Javascript (excluding double quote characters ")
     */
    public function addFieldHtml($id, $html_tag, $value)
    {
        for ($x=0; $x<count($this->_fields); $x++)
        {
            if (!strcmp($this->_fields[$x]['id'], $id))
            {
                if (isset($this->_fields[$x]['html'][$html_tag]))
                {
                    $this->_fields[$x]['html'][$html_tag] .= ' ' . $value;
                }
                else
                {
                    $this->_fields[$x]['html'][$html_tag] = $value;
                }
            }
        }
    }

    /**
     * Returns the javascript for the HTML tag specified with $html_tag.
     *
     * @param string ID of the field
     * @param string Name of the tag (i.e.: onmouseover)
     * @return string HTML/javascript or blank string '' if none exists.
     */
    public function getFieldHtml($id, $html_tag)
    {
        for ($x=0; $x<count($this->_fields); $x++)
        {
            if (!strcmp($this->_fields[$x]['id'], $id))
            {
                if (isset($this->_fields[$x]['html'][$html_tag]))
                {
                    return $this->_fields[$x]['html'][$html_tag];
                }
            }
        }
        return '';
    }

    /**
     * Gets the value of form data regardless of GET/POST usage.
     *
     * @param string Name of the form element
     * @return string Value of the form element
     */
    public static function getPostValue($name)
    {
        if (isset($_GET[$name])) return $_GET[$name];
        else if(isset($_POST[$name])) return $_POST[$name];
        else return '';
    }

    /**
     * Get the string caption of a field.
     *
     * @param string ID of the field
     * @return string Caption value
     */
    public function getFieldCaption($field)
    {
        $cap = $field['caption'];
        if ($field['required']) $cap .= ' *';
        return $cap;
    }

    /**
     * Returns the HTML/JavaScript for a field when the WebForm is in Verify mode.
     *
     * @param string ID of the field
     * @return string HTML output of the field
     */
    public function getFieldVerify($field)
    {
        switch ($field['type'])
        {
            case WFT_TEXTAREA:
                $class = "webFormVerifyFieldContainerBox";
                break;
            default:
                $class = "webFormVerifyFieldContainer";
                break;
        }

        $size = ($field['size'] * 11) + 40;
        $verify = sprintf(
            "<div id=\"%sContainer\" style=\"width: %dpx; cursor: pointer;\" class=\"%s\" "
            . "onclick=\"webFormEditField('%s', true, %s);\" onmouseover=\"webFormFieldHover('%s', true);\" "
            . "onmouseout=\"webFormFieldHover('%s', false);\">\n<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">"
            . "<tr><td align=\"left\" valign=\"center\" style=\"padding-right: 2px;\">"
            . "<img id=\"%sEdit\" src=\"%simages/edit.gif\" onclick=\"webFormEditField('%s', true, %s);\" "
            . "onmouseover=\"webFormFieldHover('%s', true);\" onmouseout=\"webFormFieldHover('%s', false);\" "
            . "style=\"cursor: pointer; visibility: hidden;\"></td><td align=\"left\" valign=\"top\"><a href=\"javascript:void(0);\" "
            . "onclick=\"webFormEditField('%s', true, %s);\" onmouseover=\"webFormFieldHover('%s', true);\" "
            . "onmouseout=\"webFormFieldHover('%s', false);\" class=\"webFormVerifyFieldLink\">"
            . "\n",
            $field['id'], $size, $class,
            $field['id'], ($field['required'] ? 'true' : 'false'), $field['id'],
            $field['id'],
            $field['id'], $this->_relPath, $field['id'], ($field['required'] ? 'true' : 'false'), $field['id'], $field['id'],
            $field['id'], ($field['required'] ? 'true' : 'false'), $field['id'], $field['id']
        );

        $caption = '';
        switch ($field['type'])
        {
            case WFT_CC_TYPE: case WFT_SELECT: case WFT_BOOLEAN:
                foreach ($field['defaultValue'] as $option)
                {
                    if (!strcmp($option['id'], $field['validatedData']))
                    {
                        $caption = $option['caption'];
                    }
                }
                if ($caption == '')
                    $caption = "<span style=\"color: #999999;\">(Empty)</span>";
                break;
            case WFT_PHONE:
                if (strlen($field['validatedData']) == 10)
                    $caption = substr($field['validatedData'], 0, 3) . '-' . substr($field['validatedData'], 3, 3) . '-' . substr($field['validatedData'], 6, 4);
                else if(strlen($field['validatedData']) == 0)
                    $caption = "<span style=\"color: #999999;\">(Empty)</span>";
                else
                    $caption = $field['validatedData'];
                break;
            default:
                if (!strlen($field['validatedData']))
                    $caption = "<span style=\"color: #999999;\">(Empty)</span>";
                else
                    $caption = $field['validatedData'];
                break;
        }
        $verify .= sprintf(
            "<span id=\"%sEditText\" class=\"webFormVerifyText\">%s</span></td></tr></table></div>%s"
            . "<input type=\"button\" style=\"display: none;\" id=\"%sSave\" class=\"webFormSaveButton\" "
            . "onclick=\"webFormEditField('%s', false, %s);\" value=\"Save\"> <input type=\"button\" "
            . "style=\"display: none;\" id=\"%sCancel\" class=\"webFormSaveButton\" "
            . "onclick=\"webFormCancelEditField('%s', %s);\" value=\"Cancel\">\n\n",
            $field['id'], $caption,
            $this->getFieldInput($field, 'style="display: none;"'),
            $field['id'], $field['id'], ($field['required'] ? 'true' : 'false'), $field['id'], $field['id'],
            ($field['required'] ? 'true' : 'false')
        );
        return $verify;
    }

    /**
     * Returns the HTML/JavaScript for a field when the WebForm is NOT in Verify mode.
     *
     * @param string ID of the field
     * @param string Additional options to the <input> element
     * @return string HTML of the field
     */
    public function getFieldInput($field, $options = '')
    {
        $onblur = $onmouseover = $onmouseout = $onkeyup = $onclick = '';
        if (count($field['html']) > 0)
        {
            if (isset($field['html']['onblur'])) $onblur = $field['html']['onblur'] . ' ';
            if (isset($field['html']['onmouseover'])) $onmouseover = $field['html']['onmouseover'] . ' ';
            if (isset($field['html']['onmouseout'])) $onmouseout = $field['html']['onmouseout'] . ' ';
            if (isset($field['html']['onkeyup'])) $onkeyup = $field['html']['onkeyup'] . ' ';
            if (isset($field['html']['onclick'])) $onclick = $field['html']['onclick'] . ' ';
        }

        if ($this->_verifyForm)
        {
            switch ($field['type'])
            {
                case WFT_CC_EXPIRATION:
                    break;
                default:
                    $onblur .= sprintf(' webFormEditField(\'%s\', false, %s); ',
                        $field['id'], $field['required'] ? 'true' : 'false'
                    );
                    break;
            }
        }

        $onmouseover .= 'webFormShowErrorBox(this); ';
        $onmouseout .= 'webFormHideErrorBox(); ';
        $onkeyup .= 'webFormHideErrorBox(); ';

        if (strlen($field['helpBody']) > 0)
        {
            $onmouseover .= sprintf("webFormShowHelpBox(this, '%s', '%s', '%s'); ",
                $field['caption'], $field['helpBody'], $field['helpRules']
            );
            $onmouseout .= 'webFormHideHelpBox(this); ';
            $onkeyup .= 'webFormHideHelpBox(this); ';
        }

        // Validate the field when leaving focus
        $onblur .= sprintf("webFormValidateField('%s', %s); ",
            $field['id'],($field['required'] ? 'true' : 'false')
        );

        $extendedJavaScript = '';
        if (strlen($onblur) > 0) $extendedJavaScript .= sprintf(' onblur="%s"', $onblur);
        if (strlen($onmouseover) > 0) $extendedJavaScript .= sprintf(' onmouseover="%s"', $onmouseover);
        if (strlen($onmouseout) > 0) $extendedJavaScript .= sprintf(' onmouseout="%s"', $onmouseout);
        if (strlen($onkeyup) > 0) $extendedJavaScript .= sprintf(' onkeyup="%s"', $onkeyup);
        if (strlen($onclick) > 0) $extendedJavaScript .= sprintf(' onclick="%s"', $onclick);

        switch ($field['type'])
        {
            case WFT_TEXT:
            case WFT_DATE:
            case WFT_PHONE:
            case WFT_EMAIL:
            case WFT_CC_NUMBER:
            case WFT_PASSWORD:
            case WFT_CC_CVV2:
            case WFT_CURRENCY:
                if ($field['type'] == WFT_PASSWORD)
                    $type = 'password';
                else
                    $type = 'text';
                $input = sprintf(
                    "<input class=\"webFormElementText\" type=\"%s\" name=\"%s\" id=\"%s\" value=\"%s\" tabindex=\"%d\" "
                    . "size=\"%d\" maxlength=\"%d\"%s",
                    $type,
                    $field['id'], $field['id'],
                    (strlen($field['validatedData']) > 0 ? $field['validatedData'] : $field['defaultValue']),
                    $field['tabIndex'], $field['size'], $field['length'][1],
                    (strlen($options) > 0 ? ' ' . $options : '')
                );
                $input .= sprintf(' %s', $extendedJavaScript);
                $input .= " />\n";
                break;

            case WFT_TEXTAREA:
                $input = sprintf(
                    "<textarea class=\"webFormElementTextBox\" name=\"%s\" id=\"%s\" tabindex=\"%d\" "
                    . "rows=\"3\" cols=\"%d\" maxlength=\"%d\"%s %s>%s</textarea>",
                    $field['id'], $field['id'],
                    $field['tabIndex'], $field['size'], $field['length'][1],
                    (strlen($options) > 0 ? ' ' . $options : ''),
                    $extendedJavaScript,
                    (strlen($field['validatedData']) > 0 ? $field['validatedData'] : $field['defaultValue'])
                );
                break;

            case WFT_CC_TYPE:
            case WFT_SELECT:
            case WFT_BOOLEAN:
                $input = sprintf(
                    "<select class=\"webFormElementSelect\" name=\"%s\" id=\"%s\" tabindex=\"%d\"%s%s>\n"
                    . "<option value=\"%s\">- Select Option -</option>\n",
                    $field['id'], $field['id'], $field['tabIndex'],
                    (strlen($options) > 0 ? ' ' . $options : ''),
                    $extendedJavaScript,
                    ($field['required'] ? 'nosel' : '')
                );

                $selected = '';
                foreach($field['defaultValue'] as $option)
                {
                    if (!strcmp($field['validatedData'], $option['id'])) $selected = $option['id'];
                }
                if ($selected == '')
                {
                    foreach($field['defaultValue'] as $option)
                    {
                        if ($option['selected']) $selected = $option['id'];
                    }
                }

                foreach($field['defaultValue'] as $option)
                {
                    $input .= sprintf(
                        "<option value=\"%s\"%s>%s</option>\n",
                        $option['id'], (!strcmp($selected, $option['id']) ? ' selected' : ''), $option['caption']
                    );
                }
                $input .= "</select>\n";
                break;

            case WFT_CC_EXPIRATION:
                $curYear = intval(date('Y'));
                if (strlen($field['validatedData']) > 0)
                {
                    $mp = explode('/', $field['validatedData']);
                    $selMonth = $mp[0];
                    $selYear = $mp[1];
                }
                else
                {
                    $selMonth = 0;
                    $selYear = 0;
                }
                $monthScript = sprintf(
                    "<script>document.getElementById('%sMonth').value = '%d';</script>",
                    $field['id'], $selMonth
                );
                $input = sprintf(
                    "<select class=\"webFormElementSelect\" name=\"%sMonth\" id=\"%sMonth\" tabindex=\"%d\" %s %s>\n"
                    . "<option value=\"%s\">- Select Month -</option>\n"
                    . "<option value=\"1\">1 - January</option>\n"
                    . "<option value=\"2\">2 - February</option>\n"
                    . "<option value=\"3\">3 - March</option>\n"
                    . "<option value=\"4\">4 - April</option>\n"
                    . "<option value=\"5\">5 - May</option>\n"
                    . "<option value=\"6\">6 - June</option>\n"
                    . "<option value=\"7\">7 - July</option>\n"
                    . "<option value=\"8\">8 - August</option>\n"
                    . "<option value=\"9\">9 - September</option>\n"
                    . "<option value=\"10\">10 - October</option>\n"
                    . "<option value=\"11\">11 - November</option>\n"
                    . "<option value=\"12\">12 - December</option>\n"
                    . "</select> %s"
                    . "<select class=\"webFormElementSelect\" name=\"%sYear\" id=\"%sYear\" tabindex=\"%d\" %s %s>\n"
                    . "<option value=\"%s\">- Select Year -</option>\n",
                    $field['id'], $field['id'], $field['tabIndex'],
                    $extendedJavaScript,
                    (strlen($options) > 0 ? ' ' . $options : ''),
                    ($field['required'] ? 'nosel' : ''),
                    $monthScript,
                    $field['id'], $field['id'], $field['tabIndex']+1,
                    $extendedJavaScript,
                    (strlen($options) > 0 ? ' ' . $options : ''),
                    ($field['required'] ? 'nosel' : '')
                );

                for ($year=$curYear; $year < $curYear + 15; $year++)
                {
                    $input .= sprintf(
                        "<option value=\"%d\"%s>%d</option>\n",
                        $year,
                        ($year == $selYear ? ' selected' : ''),
                        $year
                    );
                }
                $input .= "</select>\n";
                break;

            case WFT_ANTI_SPAM_IMAGE:
                $graphs = new Graphs();
                $verificationImage = $graphs->verificationImage();

                // If there's a relative path, convert the image URL
                if ($this->_relPath != '')
                {
                    $verificationImage = str_replace('<img src="', sprintf('<img src="%s', $this->_relPath), $verificationImage);
                }

                $input = sprintf(
                    "<div style=\"padding: 0px 0px 0px 0px; text-align: left;\">\n"
                    . "Please type the characters in the image below (case-insensitive)\n"
                    . "<p>\n%s\n<p>\n<input type=\"text\" name=\"%s\" id=\"%s\" size=\"8\" "
                    . "maxlength=\"10\" tabindex=\"%d\" %s %s>\n"
                    . "<div id=\"%sCaption\" class=\"webFormCaption\"></div>\n"
                    . "</div>\n",
                    $verificationImage, $field['id'], $field['id'], $field['tabIndex'],
                    (strlen($options) > 0 ? ' ' . $options : ''),
                    $extendedJavaScript, $field['id']
                );
                break;
        }
        return $input;
    }

    /**
     * Returns the HTML for a button to be used as the submit button. This is an image
     * button.
     *
     * @param string URL of the image to display
     * @param string Caption used as the ALT property of the image.
     * @param string DOM element name for the form to submit
     * @return string HTML of the image button
     */
    public function getImageButton($image, $caption, $form)
    {
        return sprintf(
            "<a href=\"javascript:void(0);\" onclick=\"webFormSubmit('%s');\"><img src=\"%s\" border=\"0\" alt=\"%s\" /></a>",
            $form, $image, $caption
        );
    }

    /**
     * Returns the HTML for a button to be used as the submit button for a form.
     *
     * @param string Caption of the button
     * @param string DOM element name for the form to submit
     * @return string HTML for the button.
     */
    public function getButton($caption, $form)
    {
        return sprintf(
            "<input id=\"webFormSubmitButton\" type=\"button\" value=\"%s\" style=\"padding: 3px 15px 3px 15px; "
            . "font-weight: bold; font-size: 10pt;\" onclick=\"webFormSubmit('%s');\">\n",
            $caption, $form);
    }

    /**
     * Translates WF_CARD_NAME_ constants to WF_CARD_TYPE_.
     *
     * @param string WF_CARD_NAME_* value
     * @return string WF_CARD_TYPE_* value
     */
    private function getCreditCardType($name)
    {
        if (!strcmp($name, WF_CARD_NAME_VISA))
            return WF_CARD_TYPE_VISA;
        else if(!strcmp($name, WF_CARD_NAME_MASTERCARD))
            return WF_CARD_TYPE_MASTERCARD;
        else if(!strcmp($name, WF_CARD_NAME_AMERICANEXPRESS))
            return WF_CARD_TYPE_AMERICANEXPRESS;
        else if(!strcmp($name, WF_CARD_NAME_DISCOVER))
            return WF_CARD_TYPE_DISCOVER;
        else return -1;
    }

    /**
     * Translates WF_CARD_TYPE_ constants to WF_CARD_NAME_.
     *
     * @param string WF_CARD_TYPE_* value
     * @return string WF_CARD_NAME_* value
     */
    private function getCreditCardName($name)
    {
        if (!strcmp($name, WF_CARD_TYPE_VISA))
            return WF_CARD_NAME_VISA;
        else if(!strcmp($name, WF_CARD_TYPE_MASTERCARD))
            return WF_CARD_NAME_MASTERCARD;
        else if(!strcmp($name, WF_CARD_TYPE_AMERICANEXPRESS))
            return WF_CARD_NAME_AMERICANEXPRESS;
        else if(!strcmp($name, WF_CARD_TYPE_DISCOVER))
            return WF_CARD_NAME_DISCOVER;
        else return -1;
    }

    /**
     * Determine the type of credit card based on the credit card number.
     *
     * @param string Full (unmasked) number of the credit card.
     * @return string WF_CARD_TYPE_*
     */
    private function getCreditCardTypeByNumber($cardNumber)
    {
        if (!strcmp($cardNumber, '1234123412348888')) return WF_CARD_TYPE_MASTERCARD;
        if (!strcmp($cardNumber, '1234123412349999')) return WF_CARD_TYPE_VISA;

        if (preg_match('/^5[1-5]\d{14}$/', $cardNumber)) return WF_CARD_TYPE_MASTERCARD;
        else if (preg_match('/^4\d{12}(\d{3})?$/', $cardNumber)) return WF_CARD_TYPE_VISA;
        else if (preg_match('/^3[47]\d{13}$/', $cardNumber)) return WF_CARD_TYPE_AMERICANEXPRESS;
        else if (preg_match('/^6011\d{12}$/', $cardNumber)) return WF_CARD_TYPE_DISCOVER;
        else return -1;
    }

    /**
     * Verifies if a credit card is a valid credit card number.
     *
     * @param string WF_CARD_TYPE_*
     * @param string Full (unmasked) number of the credit card
     * @return boolean true if it is valid
     */
    private function isCardNumberValid($cardType, $cardNumber)
    {
        if (empty($cardNumber))
        {
            return false;
        }

        if (!strcmp($cardNumber, '1234123412349999')) return true;
        if (!strcmp($cardNumber, '1234123412348888')) return true;

        /* Create a regular expression to validate card numbers by issuer. */
        switch ($cardType)
        {
            case WF_CARD_TYPE_MASTERCARD:
                $regex = '/^5[1-5]\d{14}$/';
                break;

            case WF_CARD_TYPE_VISA:
                $regex = '/^4\d{12}(\d{3})?$/';
                break;

            case WF_CARD_TYPE_AMERICANEXPRESS:
                $regex = '/^3[47]\d{13}$/';
                break;

            case WF_CARD_TYPE_DISCOVER:
                $regex = '/^6011\d{12}$/';
                break;

            default:
                return false;
                break;
        }

        /* Fail if the card number is not valid for the specified issuer. */
        if (!preg_match($regex, $cardNumber))
        {
            return false;
        }

        /* Reverse the card number; we have to start from the right. */
        $reversedCardNumber = strrev($cardNumber);

        /* 1) Loop through each digit in the (reversed) card number.
         *      A) Multiply every second digit by 2.
         *      B) If this multiplication results in a two-digit number, add
         *         the two digits together and use the resulting value instead.
         * 2) Add all of the values obtained in step 1 together. Every digit
         *    gets added, even ones that weren't doubled.
         * 3) If the value obtained in step 2 is evenly divisible by 10, the
         *    card number is valid.
         */
        $sum = 0;
        for ($i = 0; $i < strlen($reversedCardNumber); $i++)
        {
            $currentDigit = $reversedCardNumber[$i];

            /* Double every second digit. */
            if (($i % 2) != 0)
            {
                $currentDigit *= 2;
            }

            /* If we just generated a two-digit number, we add the value of
             * each digit togeather instead of using the two-digit number.
             */
            if ($currentDigit > 9)
            {
                /* Divide by 10 and take the remainder to get second digit. */
                $secondDigit = $currentDigit % 10;

                /* Subtract second digit and divide by 10 to get first digit. */
                $firstDigit = ($currentDigit - $secondDigit) / 10;

                $currentDigit = $firstDigit + $secondDigit;
            }

            $sum += $currentDigit;
        }

        if (($sum % 10) != 0)
        {
            return false;
        }

        return true;
    }

    /**
     * Get the CSS necessary to render the WebForm. This should be displayed as an
     * included page or embedded into the <head> tag of the containing page.
     * Make sure to add all fields and form settings prior to displaying the CSS!
     *
     * @return string
     */
    public function getCSS()
    {
        ob_start();
        ?>
        div.webFormHelpBox {
            position: absolute;
            left: 320px;
            top: 330px;
            width: 200px;
            font-family: Arial, Verdana, sans-serif;
            font-size: 8pt;
            line-height: 8pt;
            font-weight: normal;
            background-color: #f7f7f7;
            border: 1px solid #cccccc;
            padding: 8px;
            visibility: hidden;
        }
        div.webFormErrorBox {
            position: absolute;
            left: 320px;
            top: 330px;
            width: 200px;
            font-family: Arial, Verdana, sans-serif;
            font-size: 8pt;
            line-height: 8pt;
            font-weight: normal;
            background-color: #fddbdb;
            border: 1px solid #9a1515;
            padding: 8px;
            visibility: hidden;
            color: #000000;
        }
        td.wfErrorText {
            font-family: Arial, Verdana, sans-serif;
            font-size: 12px;
            font-weight: normal;
            color: #000000;
            line-height: 14px;
        }
        div.webFormCaption {
            font-size: 8pt;
            color: #888888;
            line-height: 10pt;
            font-family: Arial, Verdana, sans-serif;
            text-align: left;
            width: 150px;
            padding: 2px;
            display: none;
        }
        span.webFormVerifyText {
            font-family: Arial, Verdana, sans-serif;
            font-size: 9pt;
            font-weight: normal;
        }
        input.webFormSaveButton {
            font-family: Arial, Verdana, sans-serif;
            font-size: 7pt;
            font-weight: bold;
        }
        div.webFormVerifyFieldContainer {
            padding: 1px;
            background-color: #f0f0f0;
            border: 1px solid #c0c0c0;
        }
        div.webFormVerifyFieldContainerBox {
            padding: 1px;
            background-color: #f0f0f0;
            border: 1px solid #c0c0c0;
            /*height: 63px;*/
        }
        a.webFormVerifyFieldLink {
            font-family: Arial, Verdana, sans-serif;
            font-size: 10pt;
            font-weight: normal;
            text-decoration: none;
            color: #000000;
        }
        input:hover {
            background-color: #f2f2f2;
        }
        textarea:hover {
            background-color: #f2f2f2;
        }

        /* Elements <input>-type items */
        <?php if ($this->_verifyForm) { ?>
        .webFormElementText {
            padding: 3px 0px 2px 18px;
            background-color: #ffffff;
            border: 1px solid #c0c0c0;
        }
        .webFormElementTextBox {
            padding: 0px 0px 0px 0px;
            background-color: #ffffff;
            border: 1px solid #c0c0c0;
        }
        .webFormElementSelect {

        }
        <?php } else { ?>
        .webFormElementText {
            padding: 3px 0px 2px 3px;
            background-color: #ffffff;
            border: 1px solid #c0c0c0;
        }
        .webFormElementTextBox {
            padding: 0px 0px 0px 0px;
            background-color: #ffffff;
            border: 1px solid #c0c0c0;
        }
        .webFormElementSelect {

        }
        <?php } ?>

        <?php
        $css = ob_get_contents();
        ob_end_clean();

        return $css;
    }

    /**
     * Get the JavaScript necessary to render the WebForm. This should be displayed as an
     * included page or embedded into the <head> tag of the containing page.
     * Make sure to add all fields and form settings prior to displaying the JavaScript!
     *
     * @return string
     */
    public function getJavaScript()
    {
        ob_start();
        ?>
        webFormChangesMade = false;

        <?php
        foreach($this->_fields as $field)
        {
            if ($field['type'] == WFT_CC_EXPIRATION)
            {
                echo sprintf("        var wf%sMonthError = false;\n        var wf%sYearError = false;",
                    $field['id'], $field['id']
                );
            }
            else
            {
                echo sprintf("        var wf%sError = false;\n",
                    $field['id']
                );
            }
        }
        ?>

        function webFormSubmit(form)
        {
            var errors = '';
            var tmperrors = '';
            <?php
            foreach($this->_fields as $field)
            {
                if ($field['type'] == WFT_CC_EXPIRATION)
                {
                    ?>
                    var <?php echo $field['id'] ?>Month = document.getElementById('<?php echo $field['id'] ?>Month');
                    var <?php echo $field['id'] ?>Year = document.getElementById('<?php echo $field['id'] ?>Year');
                    if (<?php echo $field['id'] ?>Month.value == 'nosel' || <?php echo $field['id'] ?>Year.value == 'nosel')
                        errors += ":You must select an expiration date and year for your credit card";
                    <?php
                }
                else if($field['type'] == WFT_SELECT || $field['type'] == WFT_CC_TYPE || $field['type'] == WFT_BOOLEAN)
                {
                    ?>
                    var <?php echo $field['id'] ?> = document.getElementById('<?php echo $field['id'] ?>');
                    if (<?php echo $field['id'] ?>.value == 'nosel')
                        errors += ":You must select a value for <?php echo addslashes($field['caption']) ?>";
                    <?php
                }
                else
                {
                    ?>
                    errors += webFormValidateField('<?php echo $field['id']; ?>', <?php echo ($field['required'] ? 'true' : 'false'); ?>);
                    <?php
                }
            }
            ?>

            if (errors != '')
            {
                alert("Please correct the following problems with your entries:\n" + errors.replace(/:/g,"\n- "));
            }
            else
            {
                eval('document.' + form + '.submit();');
            }
        }
        function webFormValidateField(id, required)
        {
            var obj = document.getElementById(id);
            var objCaption = document.getElementById(id + 'Caption');
            var retVal;

            if(obj)
            {
                if (obj.value == '' && required == false) return '';
                var str = obj.value;
                str = str.replace(/'/g,"\\'");
                str = str.replace(/\r/g,"");
                str = str.replace(/\n/g,"");
                eval( 'retVal = webFormValidate' + id + '(\'' + str + '\');' );

                if (retVal != '')
                {
                    obj.style.backgroundColor = '#FFE8E8';
                    if (objCaption) objCaption.innerHTML = retVal.substr(1);
                    <?php
                    if ($this->_verifyForm)
                    {
                        echo sprintf("webFormEditField('%s', true, %s);", $field['id'], ($field['required'] ? 'true' : 'false'));
                    }
                    ?>
                }
                else
                {
                    obj.style.backgroundColor= '#FFFFFF';
                    if (objCaption) objCaption.innerHTML = '';
                }
                return retVal;
            }
            return '';
        }

        <?php
        if ($this->_verifyForm)
        {
            ?>
            function webFormFormatEditFields()
            {
                <?php
                // get the formatting right for display
                foreach ($this->_fields as $field)
                {
                    switch ($field['type'])
                    {
                        case WFT_PHONE:
                            ?>programs:/Utilities/
                            var obj = document.getElementById('<?php echo $field['id'] ?>EditText');
                            if (obj)
                            {
                                var str = obj.innerHTML;
                                if (str.length == 10)
                                    obj.innerHTML = str.substring(0, 3) + '-' + str.substring(3, 6) + '-' + str.substring(6, 10);
                            }
                            <?php
                            break;
                    }
                }
                ?>
            }

            function webFormFieldHover(id, tf)
            {
                var editButton = document.getElementById(id + 'Edit');
                var editContainer = document.getElementById(id + 'Container');
                if (tf == true && editButton)
                {
                    editButton.style.visibility = 'visible';
                    editContainer.style.border = '1px dotted #333333';
                    editContainer.style.backgroundColor = '#e0e0e0';
                }
                else if(editButton)
                {
                    editButton.style.visibility = 'hidden';
                    editContainer.style.border = '1px solid #c0c0c0';
                    editContainer.style.backgroundColor = '#f0f0f0';
                }
            }

            function webFormCancelEditField(id, required)
            {
                var inputBox = document.getElementById(id);
                var inputBoxMonth = document.getElementById(id+'Month');
                var inputBoxYear = document.getElementById(id+'Year');
                var editText = document.getElementById(id + 'EditText');

                if (inputBox)
                {
                    var oldText = editText.innerHTML;
                    if (oldText.indexOf('(Empty)') != -1)
                        inputBox.value = '';
                    else
                        inputBox.value = oldText;
                }
                else if(inputBoxMonth && inputBoxYear)
                {
                    var expire = editText.innerHTML;
                    var parts = expire.split('/');
                    inputBoxMonth.value = parts[0];
                    inputBoxYear.value = parts[1];
                }
                webFormEditField(id, false, false);
            }

            function webFormEditField(id, tf, required)
            {
                var editButton = document.getElementById(id + 'Edit');
                var editContainer = document.getElementById(id + 'Container');
                var editText = document.getElementById(id + 'EditText');
                var inputBox = document.getElementById(id);
                var saveButton = document.getElementById(id + 'Save');
                var cancelButton = document.getElementById(id + 'Cancel');

                var inputBoxMonth = document.getElementById(id+'Month');
                var inputBoxYear = document.getElementById(id+'Year');

                // for verify images
                if (!saveButton) return;

                if (tf == true)
                {
                    showVal = "";
                    hideVal = "none";
                    saveButton.style.display = "";
                    cancelButton.style.display = "";
                    editButton.style.display = "none";
                    editContainer.style.display = "none";
                    editText.style.display = "none";
                    if (inputBox)
                    {
                        inputBox.style.display = "";
                        inputBox.focus();
                        if (inputBox.type != 'select-one')
                            inputBox.select();
                    }
                    if (inputBoxMonth && inputBoxYear)
                    {
                        inputBoxMonth.style.display = "";
                        inputBoxMonth.focus();
                        inputBoxYear.style.display = "";
                    }
                }
                else
                {
                    errors = webFormValidateField(id, required);
                    if (errors == '')
                    {
                        saveButton.style.display = "none";
                        cancelButton.style.display = "none";
                        editButton.style.display = "";
                        editContainer.style.display = "";
                        editText.style.display = "";
                        if (inputBox) inputBox.style.display = "none";
                        if (inputBoxMonth && inputBoxYear)
                        {
                            inputBoxMonth.style.display = "none";
                            inputBoxYear.style.display = "none";
                            if (inputBoxMonth.value != '' && inputBoxYear.value != '')
                                editText.innerHTML = inputBoxMonth.value + "/" + inputBoxYear.value;
                            else
                                editText.innerHTML = "<span style=\"color: #999999;\">(Empty)</span>";
                        }
                        else
                        {
                            var str;

                            if (inputBox.type == 'select-one')
                            {
                                // <select> element
                                str = inputBox.options[inputBox.selectedIndex].text;
                            }
                            else
                            {
                                str = inputBox.value;
                            }

                            if (str.length == 0)
                                editText.innerHTML = "<span style=\"color: #999999;\">(Empty)</span>";
                            else
                                editText.innerHTML = str;
                        }
                        webFormChangesMade = true;
                    }
                    webFormFormatEditFields();
                }
            }
            <?php
        }
        ?>

        <?php
        foreach($this->_fields as $field)
        {
            ?>
            function webFormValidate<?php echo $field['id'] ?>(data)
            {
                <?php
                if ($field['required'])
                {
                    ?>
                    if (data == '')
                    {
                        wf<?php echo $field['id']; ?>Error = true;
                        return ':<?php echo (strlen($field['caption']) > 0 ? $field['caption'] : 'This field'); ?> is a required field';
                    }
                    <?php
                }
                if (!$field['required']) echo "if (data.length > 0)\n{\n";
                ?>
                if (data.length < <?php echo $field['length'][0]; ?> || data.length > <?php echo $field['length'][1]; ?>)
                {
                    <?php
                    if ($field['length'][0] == $field['length'][1])
                    {
                        ?>
                        wf<?php echo $field['id']; ?>Error = true;
                        return ':<?php echo (strlen($field['caption']) > 0 ? $field['caption'] : 'This field'); ?> must be <?php echo $field['length'][0] ?> characters in length';
                        <?php
                    }
                    else
                    {
                        ?>
                        wf<?php echo $field['id']; ?>Error = true;
                        return ':<?php echo $field['caption']; ?> must be between <?php echo implode(' and ', $field['length']) ?> characters in length';
                        <?php
                    }
                    ?>
                }
                <?php
                if (!$field['required'])
                {
                    echo "}\n";
                }
                if (strlen($field['regex_test']) > 0)
                {
                    ?>
                    var re = <?php echo $field['regex_test']; ?>;
                    if (!data.match(re))
                    {
                        wf<?php echo $field['id']; ?>Error = true;
                        return ':<?php echo $field['regex_fail']; ?>';
                    }
                    <?php
                }
                if ($field['type'] == WFT_SELECT)
                {
                    ?>
                    if (data == 'nosel')
                    {
                        wf<?php echo $field['id']; ?>Error = true;
                        return ':Please select a value for <?php echo $field['caption']; ?>';
                    }
                    <?php
                }
                ?>
                wf<?php echo $field['id']; ?>Error = false;
                return '';
            }
            <?php
        }

        ?>
        function webFormShowErrorBox(obj)
        {
            var xy = webFormFindPos(obj);
            var error = false;
            var errorMessage = '';
            var errorBox = document.getElementById('webFormErrorBox');

            if (document.getElementById(obj.name))
            {
                eval('error = wf' + obj.name + 'Error;');
            }

            if (error)
            {
                errorMessage = '<table><tr><td align="left" valign="center"><img src="<?php echo $this->_relPath; ?>images/wf_error.gif" border="0" align="left" style="padding-right: 5px;"/></td><td align="left" valign="center" class="wfErrorText">' + document.getElementById(obj.name + 'Caption').innerHTML + "</td></tr></table>";
                errorBox.style.left = '' + (xy[0] + 140) + 'px';
                errorBox.style.top = '' + (xy[1] - 10) + 'px';
                errorBox.style.visibility = 'visible';
                errorBox.innerHTML = errorMessage;
            }
        }

        function webFormHideErrorBox()
        {
            var errorBox = document.getElementById('webFormErrorBox');
            if (errorBox)
                errorBox.style.visibility = 'hidden';
        }

        function webFormShowHelpBox(obj, title, desc, rules)
        {
            var xy = webFormFindPos(obj);
            var helpBox = document.getElementById('webFormHelpBox');
            var error = false;

            if (document.getElementById(obj.name))
            {
                eval('error = wf' + obj.name + 'Error;');
            }

            if (helpBox && obj && !error)
            {
                helpBox.style.left = '' + (xy[0] + 140) + 'px';
                helpBox.style.top = '' + (xy[1] - 30) + 'px';
                helpBox.style.visibility = 'visible';
                helpBox.innerHTML = '<b>' + title + '</b><br />' + desc + '<p><b>Rules:</b><br />' + rules;
            }
        }

        function webFormHideHelpBox()
        {
            var helpBox = document.getElementById('webFormHelpBox');
            if (helpBox)
                helpBox.style.visibility = 'hidden';
        }

        function webFormFindPos(obj)
        {
        	var curleft = curtop = 0;
        	if (obj.offsetParent)
        	{
        		curleft = obj.offsetLeft
        		curtop = obj.offsetTop
        		while (obj = obj.offsetParent)
        		{
        			curleft += obj.offsetLeft
        			curtop += obj.offsetTop
        		}
        	}
        	return [curleft,curtop];
        }
        <?php

        $js = ob_get_contents();
        ob_end_clean();
        return $js;
    }
}

?>
