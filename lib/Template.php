<?php
/**
 * CATS
 * Template Library
 *
 * The Original Code is "CATS Standard Edition".
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 *
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: Template.php 3587 2007-11-13 03:55:57Z will $
 */

/**
 * Template Library
 * @package    CATS
 * @subpackage Library
 */
class Template
{
    private $_templateFile;

    private $_filters = [];

    // Define properties that were previously dynamic
    private $messageSuccess;

    private $message;

    private $username;

    private $reloginVars;

    private $siteName;

    private $siteNameFull;

    private $dateString;

    private $dataGrid;

    private $dataGrid2;

    private $placedRS;

    private $upcomingEventsFupHTML;

    private $upcomingEventsHTML;

    private $active;

    private $numActivities;

    private $quickLinks;

    private $totalJobOrders;

    private $errMessage;

    private $totalCandidates;

    private $userID;

    private $totalContacts;

    private $summaryHTML;

    private $statisticsData;

    private $isDemoUser;

    private $subActive;

    private $userIsSuperUser;

    private $superUserActive;

    private $allowAjax;

    private $defaultPublic;

    private $firstDayMonday;

    private $userEmail;

    private $calendarEventTypes;

    private $eventsString;

    private $view;

    private $year;

    private $month;

    private $showEvent;

    private $currentDateMDY;

    private $allowEventReminders;

    private $dayHourStart;

    private $dayHourEnd;

    private $militaryTime;

    private $currentMonth;

    private $currentYear;

    private $currentDay;

    private $currentHour;

    private $md5InstanceName;

    private $arrayKeysString;

    private $counterFilters;

    private $data;

    private $isPopup;

    private $attachmentsRS;

    private $extraFieldRS;

    private $EEOSettingsRS;

    private $EEOValues;

    private $isShortNotes;

    private $calendarRS;

    private $assignedTags;

    private $privledgedUser;

    private $pipelinesRS;

    private $lists;

    private $activityRS;

    private $listRS;

    private $savedSearchRS;

    private $isResumeMode;

    private $isResultsMode;

    private $mode;

    private $pager;

    private $exportForm;

    private $departmentsRS;

    private $jobOrdersRS;

    private $contactsRSWC;

    private $contactsRS;

    private $companyID;

    private $contactID;

    private $isFinishedMode;

    private $onlyScheduleEvent;

    private $changesMade;

    private $eventHTML;

    private $modal;

    private $errorTitle;

    private $errorMessage;

    private $isDemo;

    private $careerPortalUnlock;

    private $careerPortalSettings;

    private $careerPortalSettingsRS;

    private $careerPortalURL;

    private $careerPortalTemplateNames;

    private $careerPortalTemplateCustomNames;

    private $template;

    private $submissionJobOrdersRS;

    private $reportTitle;

    public $sessionCookie;

    private $candidateID;

    private $defaultCompanyID;

    private $RS;

    private $selectedCompanyID;

    private $noCompanies;

    private $jobTypes;

    private $careerPortalEnabled;

    private $questionnaires;

    private $systemAdministration;

    private $calendarSettingsRS;

    private $timeZone;

    private $isDateDMY;

    private $rs;

    private $regardingRS;

    private $activityAdded;

    private $reportsToRS;

    private $tagsRS;

    private $topLog;

    private $sourceInRS;

    private $sourcesRS;

    private $sourcesString;

    private $emailTemplateDisabled;

    private $canEmail;

    private $usersRS;

    private $isModal;

    private $isParsingEnabled;

    private $associatedAttachment;

    private $associatedTextResume;

    private $parsingStatus;

    private $contents;

    private $associatedAttachmentRS;

    private $subTemplateContents;

    private $multipleFilesEnabled;

    private $uploadPath;

    private $isPublic;

    private $questionnaireData;

    private $questionnaireID;

    private $pipelineEntriesPerPage;

    private $jobOrderID;

    private $pipelineGraph;

    private $license;

    private $auth_mode;

    private $accessLevels;

    private $defaultAccessLevel;

    private $categories;

    private $privledged;

    private $loginAttempts;

    private $jobOrderFilters;

    private $pageStart;

    private $pageEnd;

    private $totalResults;

    private $templateName;

    private $wildCardString;

    private $defaultCompanyRS;

    private $extraFieldsForJobOrders;

    private $eeoEnabled;

    private $extraFieldsForCandidates;

    private $isJobOrdersMode;

    private $pipelineRS;

    private $statusRS;

    private $selectedJobOrderID;

    private $selectedStatusID;

    private $statusChangeTemplate;

    private $emailDisabled;

    private $notificationHTML;

    private $success;

    private $recipients;

    private $emailTemplatesRS;

    private $dataItemDesc;

    private $dataItemIDArray;

    private $savedListsRS;

    private $dataItemType;

    private $success_to;

    private $candidateIDArrayStored;

    private $candidateIDArray;

    private $candidateJoborderStatusSendsMessage;

    private $mailerSettingsRS;

    private $bulk;

    private $typeOfImport;

    /**
     * Prints $string with all HTML special characters converted to &codes;.
     *$isModa
     * Ex: 'If x < 2 & x > 0, x = 1.' -> 'If x &lt; 2 &amp; x &gt; 0, x = 1.'.private $
     *
     * @param string $string
     */
    public function _($string)
    {
        echo(htmlspecialchars($string));
    }

    /**
     * Assigns the specified property value to the specified property name
     * for access within the template.
     *
     * @param string $propertyName
     * @param mixed $propertyValue
     */
    public function assign($propertyName, $propertyValue)
    {
        if (property_exists($this, $propertyName)) {
            $this->$propertyName = $propertyValue;
        }
    }

    /**
     * Assigns the specified property value to the specified property name,
     * by reference, for access within the template.
     *
     * @param string $propertyName
     * @param mixed $propertyValue
     */
    public function assignByReference($propertyName, &$propertyValue)
    {
        if (property_exists($this, $propertyName)) {
            $this->$propertyName = &$propertyValue;
        }
    }

    /**
     * TODO: Document me.
     */
    public function addFilter($code)
    {
        $this->_filters[] = $code;
    }

    /**
     * Evaluates a template file. All assignments (see the Template::assign()
     * and Template::assignByReference() methods) must be made before calling
     * this method. The template filename is relative to index.php.
     *
     * @param string $template
     */
    public function display($template)
    {
        /* File existence checking. */
        $file = realpath('./' . $template);
        if (! $file) {
            echo 'Template error: File \'', $template, '\' not found.', "\n\n";
            return;
        }

        $this->_templateFile = $file;

        /* We don't want any variable name conflicts here. */
        unset($file, $template);

        /* Include the template, with output buffering on, and echo it. */
        ob_start();
        include($this->_templateFile);
        $html = ob_get_clean();

        if (strpos($html, '<!-- NOSPACEFILTER -->') === false && strpos($html, 'textarea') === false) {
            $html = preg_replace('/^\s+/m', '', $html);
        }

        foreach ($this->_filters as $filter) {
            eval($filter);
        }

        echo($html);
    }

    /**
     * Returns access level of logged in user for securedObject
     * Intended to be used in tpl classes to check if user has access to a particular part of the page and if it shall be generated or not.
     *
     * @param string $securedObjectName
     * @return mixed
     */
    protected function getUserAccessLevel($securedObjectName)
    {
        return $_SESSION['CATS']->getAccessLevel($securedObjectName);
    }
}
