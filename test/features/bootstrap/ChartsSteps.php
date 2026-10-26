<?php

/** Checks application data/behaviour, never Chart.js internals or generated DOM. */
trait ChartsSteps
{
    private $hiringHistory = array();

    /** @Given Hiring Overview has recorded transitions */
    public function hiringTransitions()
    {
        foreach (array(400, 400, 500, 800) as $status) $this->hiringTransition($status);
    }

    private function hiringTransition($status)
    {
        $db = DatabaseConnection::getInstance();
        $db->query("INSERT INTO candidate_joborder_status_history (candidate_id, joborder_id, date, status_from, status_to) VALUES (20000, 40001, NOW(), 100, " . $status . ')');
        $id = (int) $db->getLastInsertID();
        $this->hiringHistory[] = $id;
        $row = $db->getAssoc('SELECT candidate_id, joborder_id, status_to FROM candidate_joborder_status_history WHERE candidate_joborder_status_history_id = ' . $id);
        if (!$row || (int) $row['candidate_id'] !== 20000 || (int) $row['joborder_id'] !== 40001 || (int) $row['status_to'] !== $status)
            throw new \RuntimeException('Hiring transition fixture was not persisted.');
    }

    /** @When another Hiring Overview hire is recorded */
    public function hiringAnotherHire()
    {
        $this->hiringTransition(800);
    }

    /** @Then Hiring Overview :view agrees with the test database */
    public function hiringDatabaseValues($view)
    {
        $db = DatabaseConnection::getInstance();
        $firstMonday = $db->getAssoc("SELECT value FROM settings WHERE settings_type = " . SETTINGS_CALENDAR . " AND setting = 'firstDayMonday'");
        $start = array('0' => "DATE_SUB(CURDATE(), INTERVAL (DAYOFWEEK(CURDATE()) - 1) DAY)",
            '1' => "DATE_SUB(CURDATE(), INTERVAL (DAYOFMONTH(CURDATE()) - 1) DAY)",
            '2' => "MAKEDATE(YEAR(CURDATE()), 1)");
        if (($firstMonday['value'] ?? 0) == 1) $start['0'] = 'DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)';
        $element = $this->getSession()->getPage()->find('css', 'script[type="application/json"]');
        $chart = $element ? json_decode($element->getText(), true) : null;
        if (!$chart) throw new \RuntimeException('Missing Hiring Overview values.');
        foreach (array(400, 500, 800) as $index => $status)
        {
            $expected = (int) $db->getAssoc('SELECT COUNT(*) AS n FROM candidate_joborder_status_history WHERE status_to = ' . $status . ' AND date >= ' . $start[$view] . ' AND date <= NOW()')['n'];
            if ($expected < 1 || end($chart['datasets'][$index]['data']) !== $expected)
                throw new \RuntimeException('Hiring Overview differs from recorded transitions for status ' . $status);
        }
    }

    /** @AfterScenario @charts */
    public function hiringCleanup()
    {
        if ($this->hiringHistory)
            DatabaseConnection::getInstance()->query('DELETE FROM candidate_joborder_status_history WHERE candidate_joborder_status_history_id IN (' . implode(',', $this->hiringHistory) . ')');
    }

    private $chartEEOSettings = null;

    /** @Given EEO chart panels are enabled for this scenario */
    public function enableChartPanels()
    {
        $db = DatabaseConnection::getInstance();
        $this->chartEEOSettings = $db->getAllAssoc('SELECT setting, value FROM settings WHERE settings_type = ' . SETTINGS_EEO);
        $db->query('DELETE FROM settings WHERE settings_type = ' . SETTINGS_EEO);
        foreach (array('ethnicTracking', 'veteranTracking', 'genderTracking', 'disabilityTracking') as $setting)
        {
            $db->query("INSERT INTO settings (settings_type, setting, value) VALUES (" . SETTINGS_EEO . ", '" . $setting . "', '1')");
        }
    }

    /** @AfterScenario @charts */
    public function restoreChartPanels()
    {
        if ($this->chartEEOSettings === null) return;
        $db = DatabaseConnection::getInstance();
        $db->query('DELETE FROM settings WHERE settings_type = ' . SETTINGS_EEO);
        foreach ($this->chartEEOSettings as $row)
        {
            $db->query('INSERT INTO settings (settings_type, setting, value) VALUES (' . SETTINGS_EEO . ', '
                . $db->makeQueryString($row['setting']) . ', ' . $db->makeQueryString($row['value']) . ')');
        }
    }

    /** @Then chart :id contains :series series and :categories categories */
    public function chartDataShape($id, $series, $categories)
    {
        $element = $this->getSession()->getPage()->find('css', '#' . $id . ' .oc-chart-data');
        $chart = $element ? json_decode($element->getText(), true) : null;
        if (!$chart || count($chart['datasets']) !== (int) $series || count($chart['labels']) !== (int) $categories)
        {
            throw new \RuntimeException('Missing or incorrect application chart data: ' . $id);
        }
        foreach ($chart['datasets'] as $dataset)
        {
            if (count($dataset['data']) !== (int) $categories)
            {
                throw new \RuntimeException('Categories and values do not match: ' . $id);
            }
        }
    }

    /** @Then all EEO charts retain their recorded categories */
    public function eeoChartCategories()
    {
        $db = DatabaseConnection::getInstance();
        foreach (array('ethnic', 'veteran') as $category)
        {
            $rows = $db->getAllAssoc('SELECT type FROM eeo_' . $category . '_type');
            $element = $this->getSession()->getPage()->find('css', '#eeo-' . $category . ' .oc-chart-data');
            $chart = json_decode($element->getText(), true);
            if ($chart['labels'] !== array_column($rows, 'type'))
            {
                throw new \RuntimeException('EEO category labels changed.');
            }
        }
        $this->chartDataShape('eeo-gender', 1, 2);
        $this->chartDataShape('eeo-disability', 1, 2);
    }

    /** @Then the recruiting summary is a downloadable PDF */
    public function chartSummaryPDF()
    {
        $content = $this->getSession()->getDriver()->getContent();
        if (substr($content, 0, 5) !== '%PDF-')
        {
            throw new \RuntimeException('Recruiting summary did not return a PDF.');
        }
        // Retain a local sample for Poppler text and visual inspection.
        file_put_contents(sys_get_temp_dir() . '/opencats-chart-summary.pdf', $content);
    }
}
