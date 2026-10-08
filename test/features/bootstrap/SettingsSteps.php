<?php

trait SettingsSteps
{
    private $sectorFixtureRows = array();
    private $sectorFixtureJobIDs = array();

    /** @BeforeScenario @sectors */
    public function rememberSectorFixtures()
    {
        $db = DatabaseConnection::getInstance();
        $this->sectorFixtureRows = $db->getAllAssoc('SELECT * FROM sector');
        $this->sectorFixtureJobIDs = array_column($db->getAllAssoc('SELECT joborder_id FROM joborder'), 'joborder_id');
    }

    /** @AfterScenario @sectors */
    public function restoreSectorFixtures()
    {
        $db = DatabaseConnection::getInstance();
        foreach ($db->getAllAssoc("SELECT joborder_id FROM joborder WHERE title = 'Sector browser job'") as $row)
        {
            if (!in_array($row['joborder_id'], $this->sectorFixtureJobIDs))
            {
                $id = (int) $row['joborder_id'];
                $db->query('DELETE FROM history WHERE data_item_type = 400 AND data_item_id = ' . $id);
                $db->query('DELETE FROM joborder WHERE joborder_id = ' . $id);
            }
        }
        foreach ($this->sectorFixtureRows as $row)
        {
            $db->query('UPDATE sector SET name = ' . $db->makeQueryString($row['name'])
                . ', is_active = ' . (int) $row['is_active'] . ' WHERE sector_id = ' . (int) $row['sector_id']);
        }
        $ids = array_map('intval', array_column($this->sectorFixtureRows, 'sector_id'));
        if ($ids) $db->query("DELETE FROM sector WHERE name = 'Browser custom Sector' AND sector_id NOT IN (" . implode(',', $ids) . ')');
    }

    /** @When I save Sector :id */
    public function saveSector($id)
    {
        $form = $this->getSession()->getPage()->find('xpath', '//form[input[@name="sectorID" and @value="' . (int) $id . '"]]');
        if (!$form) throw new \RuntimeException('Sector form missing.');
        $this->performDocumentReplacement(fn() => $form->pressButton('Save Sector'), 'saving Sector');
    }

    /** @When I filter Job Orders by Desk :desk, Sector :sector and Recruiter :recruiter */
    public function filterJobClassifications($desk, $sector, $recruiter)
    {
        $page = $this->getSession()->getPage();
        $hash = md5('joborders:JobOrdersListByViewDataGrid');
        $area = $page->findById('filterResultsArea' . $hash);
        if (!$area->isVisible()) $page->pressButton('More filters');
        foreach (array('Desk' => $desk, 'Sector' => $sector, 'Recruiter' => $recruiter) as $column => $value)
        {
            if (!$page->findById('filterResultsAreaTable' . $hash . '1columnName')) $page->pressButton('Add New');
            $prefix = 'filterResultsAreaTable' . $hash . '1';
            $page->findById($prefix . 'columnName')->selectOption($column);
            $page->findById($prefix . 'value')->setValue($value);
            $page->findById($prefix . 'value')->blur();
            $this->pressButtonAndWaitForNavigation('Apply');
        }
    }

    /** @Then the Settings validation alert contains :message */
    public function settingsValidationAlert($message)
    {
        $actual = $this->getSession()->getDriver()->getWebDriverSession()->alert()->getText();
        if (strpos($actual, $message) === false)
        {
            throw new \RuntimeException('Unexpected Settings validation alert: ' . $actual);
        }
    }

    /** @Then the Settings form :name submits to action :action using :method */
    public function settingsFormContract($name, $action, $method)
    {
        $form = $this->getSession()->getPage()->find('css', 'form[name="' . $name . '"], form[id="' . $name . '"]');
        if (!$form || strtolower($form->getAttribute('method')) !== strtolower($method))
        {
            throw new \RuntimeException('Settings form missing or method changed: ' . $name);
        }
        parse_str(parse_url(html_entity_decode($form->getAttribute('action')), PHP_URL_QUERY) ?? '', $query);
        if (($query['m'] ?? '') !== 'settings' || ($query['a'] ?? '') !== $action)
        {
            throw new \RuntimeException('Settings form action changed: ' . $name);
        }
        if (!$form->find('css', 'input[name="postback"]'))
        {
            throw new \RuntimeException('Settings postback field missing: ' . $name);
        }
    }
    private $settingsFieldValues = array();

    /** @When I remember the Settings field :name */
    public function rememberSettingsField($name)
    {
        $this->settingsFieldValues[$name] = $this->getSession()->getPage()->findField($name)->getValue();
    }

    /** @Then the Settings field :name retains its value */
    public function assertSettingsFieldValue($name)
    {
        if ($this->getSession()->getPage()->findField($name)->getValue() !== $this->settingsFieldValues[$name])
        {
            throw new \RuntimeException('Settings field changed: ' . $name);
        }
    }
    private $settingsExtraFieldName;
    private $settingsTemplateName;
    private $settingsQuestionnaireID;
    private $settingsPortalEnabled = null;

    /** @When I enter a unique Settings extra field name */
    public function enterSettingsExtraFieldName()
    {
        $this->settingsExtraFieldName = 'Settings Bootstrap ' . uniqid();
        $this->getSession()->getPage()->fillField('addFieldName0', $this->settingsExtraFieldName);
    }

    /** @Then the new Settings extra field is visible */
    public function newSettingsExtraFieldVisible()
    {
        $this->assertPageContainsText($this->settingsExtraFieldName);
    }

    /** @Given a disposable Settings Career Portal fixture exists */
    public function settingsCareerFixture()
    {
        $db = DatabaseConnection::getInstance();
        $this->settingsPortalEnabled = $db->getAllAssoc('SELECT value FROM settings WHERE settings_type = '
            . SETTINGS_CAREER_PORTAL . " AND setting = 'enabled'");
        $portal = new CareerPortalSettings();
        $db->query('DELETE FROM settings WHERE settings_type = ' . SETTINGS_CAREER_PORTAL . " AND setting = 'enabled'");
        $portal->set('enabled', '1');
        $this->settingsTemplateName = 'Settings Bootstrap ' . uniqid();
        $portal->setForTemplate('Header', '<h1>Settings fixture &amp; preview</h1>', $this->settingsTemplateName);
        $questionnaire = new Questionnaire();
        $this->settingsQuestionnaireID = (int) $questionnaire->add('Settings fixture', 'Settings preview question', 1);
        $questionnaire->addQuestions($this->settingsQuestionnaireID, array(array(
            'questionText' => 'Describe your experience', 'minimumLength' => 0,
            'maximumLength' => 200, 'questionPosition' => 0,
            'questionType' => QUESTIONNAIRE_QUESTION_TYPE_TEXT
        )));
    }

    /** @When I open the Settings fixture template editor */
    public function openSettingsTemplateEditor()
    {
        $this->visit('/index.php?m=settings&a=careerPortalTemplateEdit&templateName=' . urlencode($this->settingsTemplateName));
    }

    /** @When I open the Settings fixture questionnaire preview */
    public function openSettingsQuestionnairePreview()
    {
        $this->visit('/index.php?m=settings&a=careerPortalQuestionnairePreview&questionnaireID=' . $this->settingsQuestionnaireID);
    }

    /** @Then the Settings fixture template content is unchanged */
    public function settingsTemplateUnchanged()
    {
        $template = (new CareerPortalSettings())->getAllFromCustomTemplate($this->settingsTemplateName);
        foreach ($template as $row)
        {
            if ($row['setting'] === 'Header' && $row['value'] === '<h1>Settings fixture &amp; preview</h1>')
            {
                return;
            }
        }
        throw new \RuntimeException('Stored Career Portal template content changed.');
    }

    /** @AfterScenario @settings */
    public function cleanupSettingsFixtures()
    {
        $db = DatabaseConnection::getInstance();
        if ($this->settingsExtraFieldName !== null)
        {
            $db->query('DELETE FROM extra_field_settings WHERE data_item_type = ' . DATA_ITEM_JOBORDER
                . ' AND field_name = ' . $db->makeQueryString($this->settingsExtraFieldName));
            $this->settingsExtraFieldName = null;
        }
        if ($this->settingsTemplateName !== null)
        {
            (new CareerPortalSettings())->deleteCustomTemplate($this->settingsTemplateName);
            $this->settingsTemplateName = null;
        }
        if ($this->settingsQuestionnaireID !== null)
        {
            $questionnaire = new Questionnaire();
            $questionnaire->deleteQuestions($this->settingsQuestionnaireID);
            $questionnaire->delete($this->settingsQuestionnaireID);
            $this->settingsQuestionnaireID = null;
        }
        if ($this->settingsPortalEnabled !== null)
        {
            $db->query('DELETE FROM settings WHERE settings_type = ' . SETTINGS_CAREER_PORTAL . " AND setting = 'enabled'");
            foreach ($this->settingsPortalEnabled as $row)
            {
                $db->query('INSERT INTO settings (settings_type, setting, value) VALUES (' . SETTINGS_CAREER_PORTAL
                    . ", 'enabled', " . $db->makeQueryString($row['value']) . ')');
            }
            $this->settingsPortalEnabled = null;
        }
    }
}
