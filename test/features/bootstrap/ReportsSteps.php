<?php

trait ReportsSteps
{
    private $reportsOutput;

    /** @When I render the isolated :kind report with title :title and :results results */
    public function renderIsolatedReportsOutput($kind, $title, $results)
    {
        // The shell/footer double lives only in this child process, never in the application.
        $process = proc_open(
            array(PHP_BINARY, LEGACY_ROOT . '/test/scripts/renderReportsOutput.php', $kind, $title, $results),
            array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')),
            $pipes
        );
        if (!is_resource($process))
        {
            throw new \RuntimeException('Could not start isolated Reports renderer.');
        }
        fclose($pipes[0]);
        $html = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        $status = proc_close($process);
        if ($status !== 0 || $errors !== '')
        {
            throw new \RuntimeException('Isolated Reports rendering failed: ' . $errors . $html);
        }
        $this->reportsOutput = new \Symfony\Component\DomCrawler\Crawler($html);
    }

    /** @Then the isolated report heading is :title with category :kind */
    public function isolatedReportsHeading($title, $kind)
    {
        if ($this->reportsOutput->filter('main h1')->text() !== $title
            || $this->reportsOutput->filter('main header p')->text() !== $kind)
        {
            throw new \RuntimeException('Report title or category changed.');
        }
    }

    /** @Then the isolated report has no result tables */
    public function isolatedReportsEmpty()
    {
        if ($this->reportsOutput->filter('main table, main h2')->count() !== 0)
        {
            throw new \RuntimeException('Empty report unexpectedly contains results.');
        }
    }

    /** @Then the isolated report job heading is :heading */
    public function isolatedReportsJobHeading($heading)
    {
        $headings = $this->reportsOutput->filter('main h2');
        if ($headings->count() !== 1 || $headings->text() !== $heading)
        {
            throw new \RuntimeException('Report job/company/owner heading changed.');
        }
    }

    /** @Then the isolated report table contains exactly: */
    public function isolatedReportsTable(\Behat\Gherkin\Node\TableNode $expected)
    {
        $tables = $this->reportsOutput->filter('main table');
        if ($tables->count() !== 1)
        {
            throw new \RuntimeException('Expected exactly one report result table.');
        }
        $actual = $tables->filter('tr')->each(function ($row) {
            return $row->filter('th, td')->each(function ($cell) { return $cell->text(); });
        });
        if ($actual !== $expected->getRows())
        {
            throw new \RuntimeException('Report columns, row order or values changed: ' . json_encode($actual));
        }
    }

    /** @Then Reports totals match the existing records */
    public function reportsTotals()
    {
        foreach (array('Companies' => 'company', 'Candidates' => 'candidate', 'Job Orders' => 'joborder', 'Contacts' => 'contact') as $label => $table)
        {
            $count = DatabaseConnection::getInstance()->getAssoc("SELECT COUNT(*) AS total FROM " . $table . " WHERE date_created > '1900-01-01'");
            $cell = $this->getSession()->getPage()->find('xpath', '//tr[td[normalize-space()="Total ' . $label . '"]]/td[last()]');
            if (!$cell || trim($cell->getText()) !== (string) $count['total'])
            {
                throw new \RuntimeException('Reports total differs for ' . $label);
            }
        }
    }

    /** @Given I capture the Reports PDF form submission */
    public function captureReportsSubmission()
    {
        // Observe the real submit event without requesting or parsing a PDF.
        $this->getSession()->executeScript("document.getElementById('jobOrderReportForm').addEventListener('submit', function(event) {
            event.preventDefault();
            window.reportsRequest = {method: this.method, action: this.action, fields: {}};
            for (var i = 0; i < this.elements.length; i++) {
                var field = this.elements[i];
                if (field.name && !field.disabled) window.reportsRequest.fields[field.name] = field.value;
            }
        });");
    }

    /** @Then the Reports PDF request preserves the entered parameters */
    public function reportsCapturedRequest()
    {
        $request = $this->getSession()->evaluateScript('window.reportsRequest');
        $expected = array('m' => 'reports', 'a' => 'generateJobOrderReportPDF',
            'dataSet' => '9,7,3,1', 'dataSet1' => '9', 'dataSet2' => '7',
            'dataSet3' => '3', 'dataSet4' => '1', 'notes' => 'Report notes', 'ext' => '.pdf');
        if (!$request || strtolower($request['method']) !== 'get'
            || basename(parse_url($request['action'], PHP_URL_PATH)) !== 'index.php')
        {
            throw new \RuntimeException('Reports PDF submission did not use the expected route.');
        }
        foreach ($expected as $name => $value)
        {
            if (!isset($request['fields'][$name]) || $request['fields'][$name] !== $value)
            {
                throw new \RuntimeException('Reports PDF parameter changed: ' . $name);
            }
        }
    }

    /** @Then Reports dataset is :value */
    public function reportsDataset($value)
    {
        $actual = $this->getSession()->evaluateScript("document.getElementById('dataSet').value");
        if ($actual !== $value)
        {
            throw new \RuntimeException('Unexpected Reports dataset: ' . $actual);
        }
    }

    /** @When I choose Reports radio :name value :value */
    public function reportsRadio($name, $value)
    {
        $this->getSession()->getPage()->find('css', 'input[name="' . $name . '"][value="' . $value . '"]')->setValue($value);
    }

    /** @Then Reports preserves all period links */
    public function reportsPeriodLinks()
    {
        foreach (array('today', 'yesterday', 'thisWeek', 'lastWeek', 'thisMonth', 'lastMonth', 'thisYear', 'lastYear', 'toDate') as $period)
        {
            foreach (array('showSubmissionReport', 'showPlacementReport') as $action)
            {
                $link = $this->getSession()->getPage()->find('css', 'a[href="index.php?m=reports&a=' . $action . '&period=' . $period . '"]');
                if (!$link || $link->getAttribute('target') !== '_blank')
                {
                    throw new \RuntimeException('Missing Reports link/new-window target: ' . $action . '/' . $period);
                }
            }
        }
    }

    /** @Then Reports form submits :action using GET */
    public function reportsFormAction($action)
    {
        $form = $this->getSession()->getPage()->find('css', '#jobOrderReportForm');
        if (!$form || strtolower($form->getAttribute('method')) !== 'get'
            || $form->getAttribute('action') !== 'index.php'
            || $form->find('css', 'input[name=m]')->getValue() !== 'reports'
            || $form->find('css', 'input[name=a]')->getValue() !== $action)
        {
            throw new \RuntimeException('Reports form request changed.');
        }
    }

    /** @Then Reports has field :field with value :value */
    public function reportsFieldValue($field, $value)
    {
        $element = $this->getSession()->getPage()->find('css', '[name="' . $field . '"][value="' . $value . '"]');
        if (!$element || ($element->getAttribute('type') === 'radio' && !$element->isChecked()))
        {
            throw new \RuntimeException('Unexpected Reports field value: ' . $field);
        }
    }
}
