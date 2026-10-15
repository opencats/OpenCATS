<?php

/** Checks application data/behaviour, never Chart.js internals or generated DOM. */
trait ChartsSteps
{
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
