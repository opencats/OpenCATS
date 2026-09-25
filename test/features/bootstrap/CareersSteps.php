<?php

/** Focused public portal fixtures and behaviour assertions. */
trait CareersSteps
{
    private $careersSettings = array();
    private $careersOverrides = array();
    private $careersFixtureIDs = array();
    private $careersUploadFile = null;
    private $careersUploadDirectoryCreated = false;

    /** @BeforeScenario @careers */
    public function prepareCareersScenario()
    {
        $db = DatabaseConnection::getInstance();
        $this->careersSettings = $db->getAllAssoc('SELECT * FROM settings WHERE settings_type = ' . SETTINGS_CAREER_PORTAL);
        $this->careersOverrides = $db->getAllAssoc("SELECT * FROM career_portal_template_site WHERE career_portal_name IN ('OpenCATS Bootstrap 5.3', 'Career Portal Legacy Fixture')");
        foreach (array('joborder', 'company', 'candidate', 'career_portal_questionnaire') as $table)
        {
            $key = $table . '_id';
            $this->careersFixtureIDs[$table] = array_column($db->getAllAssoc("SELECT $key FROM $table"), $key);
        }
        $db->query('DELETE FROM settings WHERE settings_type = ' . SETTINGS_CAREER_PORTAL);
        foreach (array('enabled' => '1', 'allowBrowse' => '1', 'activeBoard' => 'OpenCATS Bootstrap 5.3', 'candidateRegistration' => '0') as $key => $value)
        {
            (new CareerPortalSettings())->set($key, $value);
        }
    }

    /** @AfterScenario @careers */
    public function restoreCareersScenario()
    {
        if ($this->careersUploadFile && is_file($this->careersUploadFile))
        {
            unlink($this->careersUploadFile);
        }
        if ($this->careersUploadDirectoryCreated)
        {
            rmdir(LEGACY_ROOT . '/upload/careerportaladd');
        }
        $db = DatabaseConnection::getInstance();
        $db->query('DELETE FROM settings WHERE settings_type = ' . SETTINGS_CAREER_PORTAL);
        $db->query("DELETE FROM career_portal_template_site WHERE career_portal_name IN ('OpenCATS Bootstrap 5.3', 'Career Portal Legacy Fixture')");
        foreach (array('settings' => $this->careersSettings, 'career_portal_template_site' => $this->careersOverrides) as $table => $rows)
        {
            foreach ($rows as $row)
            {
                $db->query('INSERT INTO ' . $table . ' (`' . implode('`, `', array_keys($row)) . '`) VALUES ('
                    . implode(', ', array_map(array($db, 'makeQueryString'), array_values($row))) . ')');
            }
        }
        // Restrict cleanup to records created by these named fixtures during this scenario.
        $filters = array(
            'joborder' => "title LIKE 'Career Portal %'",
            'company' => "name = 'Career Portal Test Company'",
            'candidate' => "email1 LIKE 'career.portal%@example.com'",
            'career_portal_questionnaire' => "title IN ('CI Questionnaire', 'Description Test Questionnaire', 'Portal UI Questionnaire')"
        );
        foreach ($filters as $table => $filter)
        {
            $key = $table . '_id';
            $before = $this->careersFixtureIDs[$table];
            $rows = $db->getAllAssoc("SELECT $key FROM $table WHERE $filter" . ($before ? " AND $key NOT IN (" . implode(',', array_map('intval', $before)) . ')' : ''));
            foreach ($rows as $row)
            {
                $id = (int) $row[$key];
                if ($table === 'career_portal_questionnaire')
                {
                    $db->query('DELETE FROM career_portal_questionnaire_answer WHERE career_portal_questionnaire_id = ' . $id);
                    $db->query('DELETE FROM career_portal_questionnaire_question WHERE career_portal_questionnaire_id = ' . $id);
                }
                if ($table === 'joborder' || $table === 'candidate')
                {
                    $db->query("DELETE FROM candidate_joborder WHERE $key = $id");
                }
                if ($table === 'candidate')
                {
                    $db->query("DELETE FROM career_portal_questionnaire_history WHERE candidate_id = $id");
                    $db->query("DELETE FROM candidate_joborder_status_history WHERE candidate_id = $id");
                    $db->query("DELETE FROM activity WHERE data_item_type = " . DATA_ITEM_CANDIDATE . " AND data_item_id = $id");
                }
                $db->query("DELETE FROM $table WHERE $key = $id");
            }
        }
    }

    /** @Given candidate registration is enabled on the public portal */
    public function enableCareersRegistration()
    {
        $db = DatabaseConnection::getInstance();
        $db->query("UPDATE settings SET value = '1' WHERE settings_type = " . SETTINGS_CAREER_PORTAL . " AND setting = 'candidateRegistration'");
        $db->query("INSERT INTO candidate (first_name, last_name, email1, zip) VALUES ('Career', 'Profile', 'career.portal.profile@example.com', '12345')");
    }

    /** @Given the public portal uses the original custom template */
    public function useLegacyCareersTemplate()
    {
        $db = DatabaseConnection::getInstance();
        $settings = new CareerPortalSettings();
        foreach ($settings->getTemplate('CATS 2.0') as $key => $value)
        {
            $settings->setForTemplate($key, $value, 'Career Portal Legacy Fixture');
        }
        $settings->setForTemplate('CSS', '/* Customer CSS remains untouched */ body { color: #123456; }', 'Career Portal Legacy Fixture');
        $db->query("UPDATE settings SET value = 'Career Portal Legacy Fixture' WHERE settings_type = " . SETTINGS_CAREER_PORTAL . " AND setting = 'activeBoard'");
    }

    /** @Then the application retains its multipart resume controls */
    public function assertCareersResumeControls()
    {
        $form = $this->assertSession()->elementExists('css', 'form#applyToJobForm');
        if ($form->getAttribute('enctype') !== 'multipart/form-data')
        {
            throw new \RuntimeException('Application must retain multipart uploads.');
        }
        foreach (array('resumeFile', 'resumeLoad', 'resumeContents', 'resumePopulate', 'applyToJobSubAction', 'file', 'submitApplicationNow') as $id)
        {
            $this->assertSession()->elementExists('css', '#' . $id);
        }
    }

    /** @When I upload the public portal resume fixture */
    public function uploadCareersResume()
    {
        $directory = LEGACY_ROOT . '/upload/careerportaladd';
        if (!is_dir($directory))
        {
            mkdir($directory, 0777, true);
            chmod($directory, 0777);
            $this->careersUploadDirectoryCreated = true;
        }
        $path = tempnam(sys_get_temp_dir(), 'portal-resume-') . '.txt';
        $this->careersUploadFile = $directory . '/' . basename($path);
        file_put_contents($path, "Career Applicant\ncareer.portal.resume@example.com\nPublic portal resume fixture\n");
        try
        {
            $this->getSession()->getPage()->attachFileToField('resumeFile', $path);
            $this->getSession()->getPage()->pressButton('Upload');
        }
        finally
        {
            unlink($path);
            unlink(substr($path, 0, -4));
        }
    }

    /** @When I view the career portal at :width pixels wide */
    public function resizeCareersWindow($width)
    {
        $this->getSession()->resizeWindow((int) $width, 900);
    }

    /** @Then the career portal validation alert contains :message */
    public function assertCareersValidationAlert($message)
    {
        $actual = $this->getSession()->getDriver()->getWebDriverSession()->getAlert_text();
        if (strpos($actual, $message) === false)
        {
            throw new \RuntimeException('Unexpected Career Portal validation alert: ' . $actual);
        }
    }

    /** @Then the public portal fits the viewport */
    public function assertCareersWidth()
    {
        if (!$this->getSession()->evaluateScript('return document.documentElement.scrollWidth <= window.innerWidth + 1;'))
        {
            throw new \RuntimeException('The public portal overflows the viewport.');
        }
    }

    /** @Then the public portal attribution is visible */
    public function assertCareersAttribution()
    {
        $image = $this->assertSession()->elementExists('css', '#poweredCATS img');
        if (!$image->isVisible() || strpos($image->getAttribute('alt'), 'Powered by: OpenCATS') === false)
        {
            throw new \RuntimeException('Required OpenCATS attribution must remain visible.');
        }
    }
}
