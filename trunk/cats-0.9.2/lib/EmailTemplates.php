<?php
/**
 * CATS
 * E-Mail Templates Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
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
 * @version    $Id: EmailTemplates.php 3694 2007-11-26 21:11:00Z brian $
 */

include_once('./lib/Mailer.php');
include_once('./lib/Site.php');

/**
 *	E-Mail Templates Library
 *	@package    CATS
 *	@subpackage Library
 */
class EmailTemplates
{
    private $_db;
    private $_siteID;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }

    /**
     * Updates an e-mail template.
     *
     * @param integer e-mail template ID
     * @param string template text
     * @return boolean True if successful; false otherwise.
     */
    public function update($emailTemplateID, $text, $disabled)
    {
        $sql = sprintf(
            "UPDATE
                email_template
            SET
                text = %s,
                disabled = %s
            WHERE
                email_template_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryStringOrNULL($text),
            $disabled,
            $emailTemplateID,
            $this->_siteID
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return false;
        }

        return true;
    }

    /**
     * Updates an e-mail template.
     *
     * @param integer e-mail template ID
     * @return boolean True if successful; false otherwise.
     */
    public function updateIsActive($emailTemplateID, $disabled)
    {
        $sql = sprintf(
            "UPDATE
                email_template
            SET
                disabled = %s
            WHERE
                email_template_id = %s
            AND
                site_id = %s",
            $disabled,
            $emailTemplateID,
            $this->_siteID
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return false;
        }

        return true;
    }    

    /**
     * Returns all relevent template data for a given e-mail template ID.
     *
     * @param integer e-mail template ID
     * @return array e-mail template data
     */
    public function get($emailTemplateID)
    {
        $sql = sprintf(
            "SELECT
                email_template.email_template_id AS emailTemplateID,
                email_template.title AS emailTemplateTitle,
                email_template.tag AS emailTemplateTag,
                email_template.text AS text,
                email_template.possible_variables AS possibleVariables,
                email_template.allow_substitution AS allowSubstitution,
                email_template.disabled AS disabled
            FROM
                email_template
            WHERE
                email_template.email_template_id = %s
            AND
                email_template.site_id = %s",
            $emailTemplateID,
            $this->_siteID
        );
        $rs = $this->_db->getAssoc($sql);

        if (!empty($rs))
        {
            $mailerSettings = new MailerSettings($this->_siteID);
            $mailerSettingsRS = $mailerSettings->getAll();

            if ($mailerSettingsRS['configured'] == '0' || $mailerSettingsRS['mode'] == 0)
            {
                $rs['disabled'] = '1';
            }

            $rs['textReplaced'] = $this->replaceVariables($rs['text']);
        }

        return $rs;
    }

    /**
     * Preforms some basic find/replace rules on template text and returns the
     * resulting string.
     *
     * @param string template text
     * @return string modified template text
     */
    public function replaceVariables($text)
    {
        $email    = $_SESSION['CATS']->getEmail();
        $siteName = $_SESSION['CATS']->getSiteName();
        $fullName = $_SESSION['CATS']->getFullName();

        if ($_SESSION['CATS']->isDateDMY())
        {
            $dateFormat = 'd-m-y';
        }
        else
        {
            $dateFormat = 'm-d-y';
        }

        if (isset($_SESSION['CATS']))
        {
            $isLoggedIn = $_SESSION['CATS']->isLoggedIn();
        }
        else
        {
            $isLoggedIn = false;
        }

        /* Variables to be replaced. */
        $stringsToFind = array(
            '%DATETIME%',
            '%SITENAME%',
            '%USERFULLNAME%',
            '%USERMAIL%'
        );

        if ($isLoggedIn)
        {
            $replacementStrings = array(
                DateUtility::getAdjustedDate($dateFormat . ' g:i A'),
                $siteName,
                $fullName,
                '<a href="mailto:'. $email .'">'. $email .'</a>'
            );
        }
        else
        {

            $site = new Site(-1);

            $siteID = $site->getFirstSiteID();

            if (!eval(Hooks::get('CAREERS_SITEID'))) return;

            $siteRS = $site->getSiteBySiteID($siteID);

            if (!isset($siteRS['name']))
            {
                die('An error has occurred: No site exists with this site name.');
            }

            $siteName = $siteRS['name'];

            $replacementStrings = array(
                DateUtility::getAdjustedDate($dateFormat . ' g:i A'),
                $siteName,
                '',
                '<a href="mailto:' . $email . '">' . $email . '</a>'
            );
        }

        return str_replace($stringsToFind, $replacementStrings, $text);
    }

    /**
     * Returns all relevent template data for a given e-mail template title.
     *
     * @param string e-mail template Title
     * @return array e-mail template data
     */
    public function getByTag($emailTemplateTag)
    {
        $sql = sprintf(
            "SELECT
                email_template.email_template_id AS emailTemplateID,
                email_template.title AS emailTemplateTitle,
                email_template.tag AS emailTemplateTag,
                email_template.text AS text,
                email_template.possible_variables AS possibleVariables,
                email_template.allow_substitution AS allowSubstitution,
                email_template.disabled AS disabled
            FROM
                email_template
            WHERE
                email_template.tag = %s
            AND
                email_template.site_id = %s",
            $this->_db->makeQueryStringOrNULL($emailTemplateTag),
            $this->_siteID
        );
        $rs = $this->_db->getAssoc($sql);

        if (!empty($rs))
        {
            $mailerSettings = new MailerSettings($this->_siteID);
            $mailerSettingsRS = $mailerSettings->getAll();

            if ($mailerSettingsRS['configured'] == '0' || 
                MAIL_MAILER == 0 || (
                    isset($rs['disabled']) && $rs['disabled'] == '1'))
            {
                $rs['disabled'] = '1';
            }
            else
            {
                $rs['disabled'] = '0';
            }

            $rs['textReplaced'] = $this->replaceVariables($rs['text']);
        }

        return $rs;
    }

    /**
     * Returns all relevent template data for all templates.
     *
     * @return array e-mail template data
     */
    public function getAll()
    {
        $sql = sprintf(
            "SELECT
                email_template.email_template_id AS emailTemplateID,
                email_template.title AS emailTemplateTitle,
                email_template.tag AS emailTemplateTag,
                email_template.text AS text,
                email_template.possible_variables AS possibleVariables,
                email_template.allow_substitution AS allowSubstitution,
                email_template.disabled AS disabled
            FROM
                email_template
            WHERE
                email_template.site_id = %s",
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }
}

?>
