<?php

/** Focused Home checks with disposable dashboard fixtures. */
trait HomeSteps
{
    private $homeCandidates = array();

    /** @When I follow Home result :text for :destination */
    public function homeResult($text, $destination)
    {
        foreach ($this->getSession()->getPage()->findAll('css', '#contents a[href*="' . $destination . '"]') as $link)
        {
            if (strpos($link->getText(), $text) !== false)
            {
                $link->click();
                return;
            }
        }
        throw new \RuntimeException('Missing Quick Search result: ' . $text);
    }

    /** @Then the Home destination contains :query */
    public function homeDestination($query)
    {
        if (strpos($this->getSession()->getCurrentUrl(), $query) === false)
        {
            throw new \RuntimeException('Unexpected Home link destination: ' . $this->getSession()->getCurrentUrl());
        }
        $this->assertSession()->statusCodeEquals(200);
    }

    /** @Given Home has 16 important candidates */
    public function homeFixtures()
    {
        $db = DatabaseConnection::getInstance();
        for ($i = 1; $i <= 16; ++$i)
        {
            $name = sprintf('HomeFixture%02d', $i);
            $db->query("INSERT INTO candidate (first_name, last_name, owner, entered_by, date_created, date_modified) VALUES ('" . $name . "', 'Dashboard', 1, 1, NOW(), NOW())");
            $id = (int) $db->getLastInsertID();
            $this->homeCandidates[] = $id;
            $db->query("INSERT INTO candidate_joborder (candidate_id, joborder_id, status, date_created, date_modified, added_by) VALUES (" . $id . ", 40001, 400, NOW(), NOW(), 1)");
        }
    }

    /** @AfterScenario @home */
    public function homeCleanup()
    {
        if ($this->homeCandidates)
        {
            $ids = implode(',', $this->homeCandidates);
            $db = DatabaseConnection::getInstance();
            $db->query('DELETE FROM calendar_event WHERE data_item_type = 100 AND data_item_id IN (' . $ids . ')');
            $db->query('DELETE FROM activity WHERE data_item_type = 100 AND data_item_id IN (' . $ids . ')');
            $db->query('DELETE FROM candidate_joborder_status_history WHERE candidate_id IN (' . $ids . ')');
            $db->query('DELETE FROM candidate_joborder WHERE candidate_id IN (' . $ids . ')');
            $db->query('DELETE FROM candidate WHERE candidate_id IN (' . $ids . ')');
            $this->homeCandidates = array();
        }
    }

    /** @Then the Home graph is loaded */
    public function homeGraphLoaded()
    {
        if (!$this->getSession()->wait(5000, "document.getElementById('homeGraph').complete && document.getElementById('homeGraph').naturalWidth === 495"))
        {
            throw new \RuntimeException('Existing Hiring Overview image did not load.');
        }
    }

    /** @Then the Home graph view is :view */
    public function homeGraphView($view)
    {
        $src = $this->getSession()->getPage()->find('css', '#homeGraph')->getAttribute('src');
        if (strpos($src, '&view=' . $view) === false || strpos($src, 'width=495&height=230') === false)
        {
            throw new \RuntimeException('Graph URL contract changed: ' . $src);
        }
    }

    private function homeGrid()
    {
        return $this->getSession()->getPage()->findById('table' . md5('home:ImportantPipelineDashboard'));
    }

    /** @When I sort Home candidates by :column */
    public function homeSort($column)
    {
        foreach ($this->homeGrid()->findAll('named', array('link', $column)) as $link)
        {
            if ($link->isVisible())
            {
                $link->click();
                return;
            }
        }
        throw new \RuntimeException('Missing Home sort link.');
    }

    /** @Then Home shows :count fixture rows starting with :name */
    public function homeRows($count, $name)
    {
        $this->spins(function () use ($count, $name) {
            $rows = $this->homeGrid()->findAll('css', 'tbody tr');
            $rows = array_values(array_filter($rows, function ($row) {
                return strpos($row->getText(), 'HomeFixture') !== false;
            }));
            if (count($rows) !== (int) $count || strpos($rows[0]->getText(), $name) === false)
            {
                throw new \RuntimeException('Unexpected Home rows/order: ' . count($rows) . '; first: ' . ($rows ? $rows[0]->getText() : 'none'));
            }
        });
    }

    /** @Then Home candidate links reach their records */
    public function homeLinks()
    {
        $urls = array();
        foreach (array('candidateID=', 'jobOrderID=', 'companyID=') as $parameter)
        {
            $link = $this->homeGrid()->find('css', 'tbody a[href*="' . $parameter . '"]');
            if (!$link)
            {
                throw new \RuntimeException('Missing Home entity link: ' . $parameter);
            }
            $urls[] = $link->getAttribute('href');
        }
        foreach ($urls as $url)
        {
            $this->visit($url);
            $this->assertSession()->pageTextNotContains('Invalid ID');
            $this->assertSession()->elementExists('css', '#contents');
        }
    }

    /** @Given Home has recent calls, upcoming calendar entries and a hire */
    public function homeWidgetFixtures()
    {
        $db = DatabaseConnection::getInstance();
        $db->query("INSERT INTO candidate (first_name, last_name, owner, entered_by) VALUES ('HomeWidget', 'Fixture', 1, 1)");
        $id = (int) $db->getLastInsertID();
        $this->homeCandidates[] = $id;
        $db->query("INSERT INTO activity (data_item_type, data_item_id, entered_by, date_occurred, date_created, type) VALUES (100, " . $id . ", 1, NOW(), NOW(), 100)");
        $db->query("INSERT INTO candidate_joborder_status_history (candidate_id, joborder_id, date, status_from, status_to) VALUES (" . $id . ", 40001, NOW(), 400, 800)");
        foreach (array(100 => 'Home upcoming call', 300 => 'Home upcoming event') as $type => $title)
        {
            $db->query("INSERT INTO calendar_event (type, date, title, entered_by, public, data_item_type, data_item_id) VALUES (" . $type . ", DATE_ADD(NOW(), INTERVAL 1 DAY), '" . $title . "', 1, 0, 100, " . $id . ")");
        }
    }

    /** @Then Home widget records and Calendar links are present */
    public function homeWidgetRecords()
    {
        $page = $this->getSession()->getPage();
        foreach (array('recentCallsHeading', 'recentHiresHeading') as $heading)
        {
            $section = $page->find('css', 'section[aria-labelledby="' . $heading . '"]');
            if (strpos($section->getText(), 'HomeWidget') === false)
            {
                throw new \RuntimeException('Missing populated Home widget: ' . $heading);
            }
        }
        foreach (array('Home upcoming call', 'Home upcoming event') as $title)
        {
            $link = $page->findLink($title);
            if (!$link || strpos($link->getAttribute('href'), 'showEvent=') === false)
            {
                throw new \RuntimeException('Missing Calendar event link: ' . $title);
            }
        }
    }

    /** @When I sort Home search candidates twice */
    public function homeSearchSorting()
    {
        $page = $this->getSession()->getPage();
        $table = $page->find('xpath', '//table[.//a[contains(@href,"candidateID=")]]');
        $table->clickLink('First Name');
        $ascending = array_map(function ($row) { return trim($row->getText()); }, $table->findAll('css', 'tbody tr'));
        $table->clickLink('First Name');
        $descending = array_map(function ($row) { return trim($row->getText()); }, $table->findAll('css', 'tbody tr'));
        if (count($ascending) !== 16 || $descending !== array_reverse($ascending))
        {
            throw new \RuntimeException('Quick Search sorting did not reverse all fixture rows.');
        }
    }

    /** @Then the Home :kind error preserves its content and shell contract */
    public function homeErrorContract($kind)
    {
        $process = proc_open(array(PHP_BINARY, LEGACY_ROOT . '/test/scripts/renderHomeError.php', $kind),
            array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
        if (!is_resource($process))
        {
            throw new \RuntimeException('Cannot render Home error fixture.');
        }
        fclose($pipes[0]);
        $html = stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        $errors = stream_get_contents($pipes[2]);
        fclose($pipes[2]);
        if (proc_close($process) !== 0 || $errors !== '')
        {
            throw new \RuntimeException('Home error rendering failed: ' . $errors);
        }
        foreach (array('Useful details', 'Return home', $kind === 'fatal' ? 'CATS: Error' : 'Home fixture error') as $text)
        {
            if (strpos($html, $text) === false)
            {
                throw new \RuntimeException('Missing Home error content: ' . $text);
            }
        }
        foreach (array('<html>', '<nav>', '<footer>', 'Quick Search') as $shell)
        {
            if ((strpos($html, $shell) !== false) !== ($kind !== 'modal'))
            {
                throw new \RuntimeException('Home error shell mismatch: ' . $kind);
            }
        }
        if ((strpos($html, 'Home error hook output') !== false) !== ($kind !== 'fatal')
            || (strpos($html, 'demo account') !== false) !== ($kind === 'demo'))
        {
            throw new \RuntimeException('Home error hook/demo contract changed.');
        }
    }
}
