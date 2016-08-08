<?php
/**
 * CATS
 * Career Portal Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
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
 * @version    $Id: CareerPortal.php 3811 2007-12-05 19:32:16Z andrew $
 */

include_once('./lib/Mailer.php');

/**
 *	Career Portal Settings Library
 *	@package    CATS
 *	@subpackage Library
 */
class CareerPortalSettings
{
    // FIXME: Make this private and use a getter.
    public $requiredTemplateFields = array(
        'Header',
        'Content - Main',
        'Content - Search Results',
        'Content - Job Details',
        'Content - Candidate Registration',
        'Content - Candidate Profile',
        'Content - Apply for Position',
        'Content - Questionnaire',
        'Content - Thanks for your Submission',
        'Footer',
        'CSS'
    );
    private $_db;
    private $_siteID;


    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }


    /**
     * Returns all career portal settings and their current values as an
     * associative array.
     *
     * @return array Associative array of all career portal settings and their
     *               current values.
     */
    public function getAll()
    {
        /* Default values. */
        $settings = array(
            'enabled'               => '0', /* false */
            'allowBrowse'           => '1', /* true */
            'candidateRegistration' => '0', /* false */
            'showDepartment'        => '1', /* true */
            'showCompany'           => '0', /* false */
            'activeBoard'           => 'CATS 2.0',
            'allowXMLSubmit'        => '1', /* true */
            'useCATSTemplate'       => ''
        );

        /* Get all career portal settings for this site from the database. */
        $sql = sprintf(
            "SELECT
                settings.setting AS setting,
                settings.value AS value,
                settings.site_id AS siteID
            FROM
                settings
            WHERE
                settings.site_id = %s
            AND
                settings.settings_type = %s",
            $this->_siteID,
            SETTINGS_CAREER_PORTAL
        );
        $rs = $this->_db->getAllAssoc($sql);

        // Override default settings with settings from the database.
        foreach ($rs as $rowIndex => $row)
        {
            if (isset($settings[$id=$row['setting']]))
            {
                $settings[$id] = $row['value'];
            }
        }

        /**
         * Retrieve all setting, value pairs for default or custom template
         * for the activeBoard (if any).
         */
        foreach ($rs as $rowIndex => $row)
        {
            if (!strcmp($row['setting'], 'activeBoard'))
            {
                $activeBoard = $row['value'];

                $templateSource1 = $this->getAllFromDefaultTemplate($activeBoard);
                $templateSource2 = $this->getAllFromCustomTemplate($activeBoard);

                $templateSource = array_merge($templateSource1, $templateSource2);

                foreach ($templateSource as $templateLine)
                {
                    $settings[$templateLine['setting']] = $templateLine['value'];
                }
            }
        }

        return $settings;
    }

    /**
     * Returns all custom template data for a site-specific template name.
     *
     * @param string Template name.
     * @return array Multi-dimensional associative result set array of
     *               template data, or array() if no records were
     *               returned.
     */
    public function getAllFromCustomTemplate($template)
    {
        $sql = sprintf(
            "SELECT
                career_portal_template_site.setting AS setting,
                career_portal_template_site.value AS value
            FROM
                career_portal_template_site
            WHERE
                career_portal_template_site.career_portal_name = %s
            AND
                site_id = %s",
            $this->_db->makeQueryString($template),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all template data for a default template name.
     *
     * @param string Template name.
     * @return array Multi-dimensional associative result set array of
     *               template data, or array() if no records were
     *               returned.
     */
    public function getAllFromDefaultTemplate($template)
    {
        $sql = sprintf(
            "SELECT
                career_portal_template.setting AS setting,
                career_portal_template.value AS value
            FROM
                career_portal_template
            WHERE
                career_portal_template.career_portal_name = %s",
            $this->_db->makeQueryString($template)
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all template data for a template name (default OR custom).
     *
     * @param string Template name.
     * @return array Multi-dimensional associative result set array of
     *               template data, or array() if no records were
     *               returned.
     */
    public function getAllFromTemplate($template)
    {
        $sql = sprintf(
            "SELECT
                career_portal_template.setting AS setting,
                career_portal_template.value AS value
            FROM
                career_portal_template
            WHERE
                career_portal_template.career_portal_name = %s
            UNION ALL
            SELECT
                career_portal_template_site.setting AS setting,
                career_portal_template_site.value AS value
            FROM
                career_portal_template_site
            WHERE
                career_portal_template_site.career_portal_name = %s
            AND
                site_id = %s",
            $this->_db->makeQueryString($template),
            $this->_db->makeQueryString($template),
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all default template names from the database.
     *
     * @return array Multi-dimensional associative result set array of
     *               template data, or array() if no records were
     *               returned.
     */
    public function getDefaultTemplates()
    {
        $sql = sprintf(
            "SELECT
                DISTINCT career_portal_name AS careerPortalName
            FROM
                career_portal_template
            ORDER BY
                career_portal_name ASC"
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all custom template names from the database for this site.
     *
     * @return array Multi-dimensional associative result set array of
     *               template data, or array() if no records were
     *               returned.
     */
    public function getCustomTemplates()
    {
        $sql = sprintf(
            "SELECT
                DISTINCT career_portal_name AS careerPortalName
            FROM
                career_portal_template_site
            WHERE
                site_id = %s",
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all template names from the database for this site, including
     * default templates.
     *
     * @return array Multi-dimensional associative result set array of
     *               template data, or array() if no records were
     *               returned.
     */
    public function getAllTemplates()
    {
        $sql = sprintf(
            "SELECT
                DISTINCT career_portal_name AS careerPortalName
            FROM
                career_portal_template
            UNION ALL
            SELECT
                DISTINCT career_portal_name AS careerPortalName
            FROM
                career_portal_template_site
            WHERE
                site_id = %s
            ORDER BY
                careerPortalName ASC",
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns all template settings and values from a template (default OR
     * custom).
     *
     * @param string Template name.
     * @return array Multi-dimensional associative result set array of
     *               template data, or array() if no records were
     *               returned.
     */
    public function getTemplate($templateName)
    {
        $rs = $this->getAllFromTemplate($templateName);

        $template = array();
        foreach ($rs as $rowIndex => $row)
        {
            $template[$row['setting']] = $row['value'];
        }

        foreach ($this->requiredTemplateFields as $index => $value)
        {
            if (!isset($template[$value]))
            {
                $template[$value] = '';
            }
        }

        return $template;
    }

    /**
     * Deletes a custom template from the database.
     *
     * @param string Template name.
     * @return boolean Was the query executed successfullu?
     */
    public function deleteCustomTemplate($template)
    {
        $sql = sprintf(
            "DELETE FROM
                career_portal_template_site
            WHERE
                site_id = %s
            AND
                career_portal_name = %s",
            $this->_siteID,
            $this->_db->makeQueryString($template)
        );

        return (boolean) $this->_db->query($sql);
    }

    /**
     * Sets a career portal setting for a custom template.
     *
     * @param string Setting name.
     * @param string Setting value.
     * @param string Template name.
     * @return void
     */
    public function setForTemplate($setting, $value, $template)
    {
        $sql = sprintf(
            "DELETE FROM
                career_portal_template_site
            WHERE
                career_portal_template_site.setting = %s
            AND
                career_portal_template_site.career_portal_name = %s
            AND
                site_id = %s",
            $this->_db->makeQueryString($setting),
            $this->_db->makeQueryString($template),
            $this->_siteID
        );
        $this->_db->query($sql);

        $sql = sprintf(
            "INSERT INTO career_portal_template_site (
                setting,
                value,
                site_id,
                career_portal_name
            )
            VALUES (
                %s,
                %s,
                %s,
                %s
            )",
            $this->_db->makeQueryString($setting),
            $this->_db->makeQueryString($value),
            $this->_siteID,
            $this->_db->makeQueryString($template)
         );
         $this->_db->query($sql);
    }

    /**
     * Sets a career portal setting for the current site.
     *
     * @param string Setting name.
     * @param string Setting value.
     * @return void
     */
    public function set($setting, $value)
    {
        /* Delete old setting. */
        $sql = sprintf(
            "DELETE FROM
                settings
            WHERE
                settings.setting = '%s'
            AND
                settings.settings_type = %s
            AND
                settings.site_id = %s",
            SETTINGS_CAREER_PORTAL,
            $this->_db->makeQueryString($setting),
            $this->_siteID
        );
        $this->_db->query($sql);

        /* Add new setting. */
        $sql = sprintf(
            "INSERT INTO settings (
                settings_type,
                setting,
                value,
                site_id
            )
            VALUES (
                %s,
                %s,
                %s,
                %s
            )",
            SETTINGS_CAREER_PORTAL,
            $this->_db->makeQueryString($setting),
            $this->_db->makeQueryString($value),
            $this->_siteID
         );
         $this->_db->query($sql);
    }

    /**
     * Sends an e-mail.
     *
     * @param integer Current user ID.
     * @param string Destination e-mail address.
     * @param string E-mail subject.
     * @param string E-mail body.
     * @return void
     */
    public function sendEmail($userID, $destination, $subject, $body)
    {
        if (empty($destination))
        {
            return;
        }

        /* Send e-mail notification. */
        //FIXME: Make subject configurable.
        $mailer = new Mailer($this->_siteID, $userID);
        $mailerStatus = $mailer->sendToOne(
            array($destination, ''),
            $subject,
            $body,
            true
        );
    }
}

?>
