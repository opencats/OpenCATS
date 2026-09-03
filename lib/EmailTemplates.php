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

include_once(LEGACY_ROOT . '/lib/Mailer.php');

/**
 *	E-Mail Templates Library
 *	@package    CATS
 *	@subpackage Library
 */
class EmailTemplates
{
    private $_db;

    public function __construct()
    {
        $this->_db = DatabaseConnection::getInstance();
    }

    public function delete($templateID)
    {
        $sql = sprintf(
            "DELETE FROM
                email_template
            WHERE
                email_template_id = %s
            AND
                tag = %s",
            $this->_db->makeQueryInteger($templateID),
            $this->_db->makeQueryString("CUSTOM")
        );
        
        $this->_db->query($sql);
    }
    
    public function add($text, $title, $tag, $possibleVariables)
    {
        $sql = sprintf(
            "INSERT INTO email_template(
                text,
                allow_substitution,
                tag,
                title,
                possible_variables,
                disabled
            )
            VALUES (
                %s,
                1,
                %s,
                %s,
                %s,
                0
            )",
            $this->_db->makeQueryStringOrNULL($text),
            $this->_db->makeQueryStringOrNULL($tag),
            $this->_db->makeQueryStringOrNULL($title),
            $this->_db->makeQueryStringOrNULL($possibleVariables)
        );
        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        $templateID = $this->_db->getLastInsertID();
        
        return $templateID;
    }
    
    /**
     * Updates an e-mail template.
     *
     * @param integer e-mail template ID
     * @param string template text
     * @return boolean True if successful; false otherwise.
     */
    public function update($emailTemplateID, $title, $text, $disabled)
    {
        if($title != "")
        {
            $sql = sprintf(
                "UPDATE
                    email_template
                SET
                    title = %s,
                    text = %s,
                    disabled = %s
                WHERE
                    email_template_id = %s",
                $this->_db->makeQueryStringOrNULL($title),
                $this->_db->makeQueryStringOrNULL($text),
                $disabled,
                $emailTemplateID
            );
        }
        else
        {
            $sql = sprintf(
                "UPDATE
                    email_template
                SET
                    text = %s,
                    disabled = %s
                WHERE
                    email_template_id = %s",
                $this->_db->makeQueryStringOrNULL($text),
                $disabled,
                $emailTemplateID
            );
        }
        

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
                email_template_id = %s",
            $disabled,
            $emailTemplateID
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
                email_template.email_template_id = %s",
            $emailTemplateID
        );
        $rs = $this->_db->getAssoc($sql);

        if (!empty($rs))
        {
            $mailerSettings = new MailerSettings();
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
                DateUtility::getAdjustedDate(DateUtility::getDateTimeFormat($dateFormat)),
                $siteName,
                $fullName,
                '<a href="mailto:'. $email .'">'. $email .'</a>'
            );
        }
        else
        {
            $siteRS = $this->_db->getAssoc("SELECT name FROM site LIMIT 1");

            if (!isset($siteRS['name']))
            {
                die('An error has occurred: No site exists with this site name.');
            }

            $siteName = $siteRS['name'];

            $is24 = !empty($siteRS['timeFormat24']);
            $replacementStrings = array(
                DateUtility::getAdjustedDate(DateUtility::getDateTimeFormat($dateFormat, $is24)),
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
                email_template.tag = %s",
            $this->_db->makeQueryStringOrNULL($emailTemplateTag)
        );
        $rs = $this->_db->getAssoc($sql);

        if (!empty($rs))
        {
            $mailerSettings = new MailerSettings();
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
        $sql =
            "SELECT
                email_template.email_template_id AS emailTemplateID,
                email_template.title AS emailTemplateTitle,
                email_template.tag AS emailTemplateTag,
                email_template.text AS text,
                email_template.possible_variables AS possibleVariables,
                email_template.allow_substitution AS allowSubstitution,
                email_template.disabled AS disabled
            FROM
                email_template";

        return $this->_db->getAllAssoc($sql);
    }

    public function getAllCustom()
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
                email_template.tag = %s",
            $this->_db->makeQueryString("CUSTOM")
        );

        return $this->_db->getAllAssoc($sql);
    }
}

?>
