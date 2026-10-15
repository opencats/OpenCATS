<?php

/** Focused import fixtures stop at mapping; history owns no application records. */
trait ImportExportSteps
{
    private $importHistoryID;
    private $importStagedFiles = array();

    /** @Then Import offers its five original type values */
    public function importTypesRemain()
    {
        $radios = $this->getSession()->getPage()->findAll('css', 'input[name="typeOfImport"]');
        $values = array_map(function ($radio) { return $radio->getAttribute('value'); }, $radios);
        if ($values !== array('resume', 'Candidates', 'JobOrders', 'Companies', 'Contacts') || !$radios[0]->isChecked())
        {
            throw new \RuntimeException('Import type values or default changed.');
        }
    }

    /** @Then the Import upload contract is intact */
    public function importUploadContract()
    {
        $form = $this->getSession()->getPage()->findById('importDataForm');
        if (strtolower($form->getAttribute('method')) !== 'post' || $form->getAttribute('enctype') !== 'multipart/form-data'
            || strpos($form->getAttribute('action'), 'a=importUploadFile') === false
            || !$form->find('css', 'input[type="file"][name="file"]')
            || count($form->findAll('css', 'input[name="typeOfFile"]')) !== 2)
        {
            throw new \RuntimeException('Upload contract changed.');
        }
    }

    /** @When I upload the small :format Import fixture */
    public function importUploadFixture($format)
    {
        $page = $this->getSession()->getPage();
        $page->find('css', 'input[name="typeOfFile"][value="' . $format . '"]')->selectOption($format);
        $page->attachFileToField('file', realpath('test/data/import-small.' . ($format === 'tab' ? 'tsv' : 'csv')));
        $page->pressButton('Next');
        $file = $this->getSession()->getPage()->findById('fileName');
        if ($file && basename($file->getValue()) === $file->getValue())
        {
            $this->importStagedFiles[] = CATS_TEMP_DIR . '/' . $file->getValue();
        }
    }

    /** @Given a disposable Import history entry */
    public function importHistoryFixture()
    {
        $db = DatabaseConnection::getInstance();
        $db->query("INSERT INTO import (module_name, reverted, date_created, added_lines, import_errors) VALUES ('candidate', 0, NOW(), 0, 'Line 2: focused import error')");
        $this->importHistoryID = (int) $db->getLastInsertID();
    }

    /** @When I open the disposable Import errors */
    public function importOpenErrors()
    {
        $this->visitPath('/index.php?m=import&a=viewerrors&importID=' . $this->importHistoryID);
    }

    /** @Then the Import revert contract is intact */
    public function importRevertContract()
    {
        $forms = $this->getSession()->getPage()->findAll('css', 'form[action*="a=revert"]');
        foreach ($forms as $form)
        {
            if ($form->find('css', 'input[name="importID"]')->getValue() == $this->importHistoryID
                && strtolower($form->getAttribute('method')) === 'post'
                && $form->find('css', 'input[name="postback"]')->getValue() === 'postback') return;
        }
        throw new \RuntimeException('Missing POST revert form for the fixture.');
    }

    /** @Then the disposable Import history entry is gone */
    public function importHistoryGone()
    {
        if (DatabaseConnection::getInstance()->getAssoc('SELECT import_id FROM import WHERE import_id = ' . $this->importHistoryID))
            throw new \RuntimeException('Revert did not delete its history.');
    }

    /** @AfterScenario @importExport */
    public function importCleanup()
    {
        if ($this->importHistoryID)
            DatabaseConnection::getInstance()->query('DELETE FROM import WHERE import_id = ' . $this->importHistoryID);
        foreach ($this->importStagedFiles as $file) if (is_file($file)) unlink($file);
        $this->importHistoryID = null;
        $this->importStagedFiles = array();
    }

    /** @When I request the candidate DataGrid export */
    public function importExportGrid()
    {
        $this->visitPath('/index.php?' . http_build_query(array('m' => 'export', 'a' => 'exportByDataGrid',
            'i' => 'candidates:candidatesListByViewDataGrid', 'p' => json_encode(array('exportIDs' => array(20000))))));
    }

    /** @Then the Export response is a CSV download containing :text */
    public function importExportCSV($text)
    {
        $session = $this->getSession();
        $headers = array_change_key_case($session->getResponseHeaders(), CASE_LOWER);
        $body = $session->getPage()->getContent();
        if ($session->getStatusCode() !== 200 || strpos(implode(' ', (array) $headers['content-type']), 'text/x-csv') === false
            || strpos(implode(' ', (array) $headers['content-disposition']), 'export.csv') === false
            || strpos($body, $text) === false || preg_match('/<(?:html|div|table|!doctype)\b/i', $body))
            throw new \RuntimeException('Export did not return the expected plain CSV download.');
    }

    /** @Then Import sample data can be shown and hidden */
    public function importSampleVisibility()
    {
        $session = $this->getSession();
        $session->executeScript('showSampleData(0);');
        $sample = $session->getPage()->findById('importSample0');
        if (!$sample->isVisible() || strpos($sample->getText(), 'ImportPreview') === false)
            throw new \RuntimeException('Sample data is unavailable.');
        $session->executeScript('hideSampleData(0);');
        if ($sample->isVisible()) throw new \RuntimeException('Sample data did not hide.');
    }

    /** @Then Import loading preserves the form submission contract */
    public function importLoadingContract()
    {
        $session = $this->getSession();
        if (!$session->evaluateScript('return showLoading();')
            || !$session->getPage()->findById('importShow1')->isVisible()
            || $session->getPage()->findById('importHide7')->isVisible()
            || $session->getPage()->findById('postback')->getValue() !== 'postback'
            || $session->getPage()->findById('importIntoField0')->getValue() !== 'first_name')
            throw new \RuntimeException('Loading changed submission or field state.');
    }

    /** @Then the Contacts mapping options still work */
    public function importContactsOptions()
    {
        $session = $this->getSession();
        $page = $session->getPage();
        $page->selectFieldOption('generateCompanies', 'no');
        if ($page->findById('unnamedContactsSpan')->isVisible())
            throw new \RuntimeException('Unnamed contacts option should be hidden.');
        $page->selectFieldOption('generateCompanies', 'yes');
        if (!$page->findById('unnamedContactsSpan')->isVisible())
            throw new \RuntimeException('Unnamed contacts option should be visible.');
        $page->selectFieldOption('importType3', 'foreign');
        if ($page->findById('importIntoSpan3')->isVisible())
            throw new \RuntimeException('Extra Field should not show a built-in destination.');
        if (!$session->evaluateScript("return checkField(4, 'company_id', 'Company required');"))
            throw new \RuntimeException('Detected Company mapping is missing.');
    }

    /** @When I open the :view Import presentation fixture */
    public function importPresentationFixture($view)
    {
        $command = escapeshellarg(PHP_BINARY) . ' test/scripts/renderImportPreview.php ' . escapeshellarg($view);
        $html = shell_exec($command);
        if (!$html || strpos($html, '<!doctype html>') !== 0)
            throw new \RuntimeException('Could not render the isolated Import fixture.');
        if (preg_match('~<script\b[^>]*\bsrc\s*=~i', $html))
            throw new \RuntimeException('Import fixture contains an unexpected external script; refusing to request it.');
        $this->getSession()->visit('data:text/html;base64,' . base64_encode($html));
    }

    /** @Then the Import fixture fits a narrow viewport */
    public function importResponsiveFixture()
    {
        $session = $this->getSession();
        $session->resizeWindow(390, 844);
        if ($session->evaluateScript('return document.documentElement.scrollWidth > window.innerWidth + 1;'))
            throw new \RuntimeException('Import content overflows the narrow viewport.');
        $session->resizeWindow(1280, 900);
    }

    /** @Then the mass-import copy and save controls work */
    public function importCopyFixture()
    {
        $session = $this->getSession();
        $session->executeScript('var source = document.getElementById("document"); source.selectionStart = 0; source.selectionEnd = 13; documentMouseUp(source);');
        $session->getPage()->findById('firstNameCopyBlock')->click();
        if ($session->getPage()->findById('firstName')->getValue() !== 'ImportPreview')
            throw new \RuntimeException('Copy selected resume text failed.');
        $form = $session->getPage()->find('css', 'form[name="verifyForm"]');
        if (strtolower($form->getAttribute('method')) !== 'post'
            || strpos($form->getAttribute('action'), 'postback=1&documentID=0') === false
            || !$form->findButton('Save Changes'))
            throw new \RuntimeException('Manual edit save contract changed.');
    }

    /** @Then the mass-import editor works without remote validation */
    public function importWithoutRemoteValidation()
    {
        $session = $this->getSession();
        if (!$session->evaluateScript('return typeof runResumeParserValidation === "undefined";'))
            throw new \RuntimeException('The fixture unexpectedly supplies the remote validator.');
        $session->getPage()->fillField('firstName', 'EditedPreview');
        $session->getPage()->fillField('city', 'Edited City');
        $session->getPage()->fillField('skills', 'Edited skills');
        $session->getPage()->findById('firstName')->click();
        $session->executeScript('validation();');
        if ($session->getPage()->findById('firstName')->getValue() !== 'EditedPreview'
            || $session->getPage()->findById('city')->getValue() !== 'Edited City'
            || $session->getPage()->findById('skills')->getValue() !== 'Edited skills')
            throw new \RuntimeException('Editing without the remote validator failed.');
        $this->importCopyFixture();
        if (!$session->evaluateScript('return window.importPreviewErrors.length === 0;'))
            throw new \RuntimeException('The editor raised a JavaScript error.');
        if (!$session->evaluateScript('return document.querySelectorAll("script[src]").length === 0 && performance.getEntriesByType("resource").every(function (entry) { return !/resfly\\.com/i.test(entry.name); });'))
            throw new \RuntimeException('The editor requested a Resfly resource or retained an external script.');
    }

    /** @Then mass-import progress fills half the available width */
    public function importProgressFixture()
    {
        $session = $this->getSession();
        $session->executeScript('setProgressBar(50, "Halfway.txt");');
        if ($session->getPage()->findById('fileName')->getText() !== 'Halfway.txt'
            || $session->getPage()->findById('statusBarContainer')->getAttribute('aria-valuenow') !== '50'
            || !$session->evaluateScript('return document.getElementById("statusBar").style.width === "50%";'))
            throw new \RuntimeException('Progress display no longer represents 50 percent.');
    }
}
