<?php

/** Focused Activities behavioural checks; fixtures are removed even after failure. */
trait ActivitiesSteps
{
    private $activitiesCandidateID = null;
    private $activitiesFormats = null;
    private $activitiesQueueTime = null;

    /** @Given Activities has 32 dated entries */
    public function activitiesFixtures()
    {
        $db = DatabaseConnection::getInstance();
        $db->query("INSERT INTO candidate (first_name, last_name) VALUES ('ActivitiesFixture', 'Bootstrap')");
        $this->activitiesCandidateID = (int) $db->getLastInsertID();
        for ($i = 1; $i <= 32; ++$i)
        {
            $db->query(sprintf(
                "INSERT INTO activity (data_item_id, data_item_type, type, notes, date_created, date_occurred, entered_by) VALUES (%d, %d, 100, 'Activities fixture %02d', '2031-06-12 09:00:00', '2031-06-12 09:%02d:00', %d)",
                $this->activitiesCandidateID, DATA_ITEM_CANDIDATE, $i, $i,
                (int) (new Users())->getIDByUsername('admin')
            ));
        }
        $this->visitPath('/index.php?m=activity&a=viewByDate&getback=getback&period=all');
        $this->activitiesFilter('Activities fixture');
    }

    /** @AfterScenario @activities */
    public function cleanActivitiesFixtures()
    {
        if ($this->activitiesCandidateID !== null)
        {
            $db = DatabaseConnection::getInstance();
            $db->query('DELETE FROM calendar_event WHERE data_item_type = ' . DATA_ITEM_CANDIDATE . ' AND data_item_id = ' . $this->activitiesCandidateID);
            $db->query('DELETE FROM activity WHERE data_item_type = ' . DATA_ITEM_CANDIDATE . ' AND data_item_id = ' . $this->activitiesCandidateID);
            $db->query('DELETE FROM candidate WHERE candidate_id = ' . $this->activitiesCandidateID);
            $this->activitiesCandidateID = null;
        }
    }

    /** @Given Activities uses :dateFormat dates and :timeFormat hour time */
    public function activitiesFormats($dateFormat, $timeFormat)
    {
        $db = DatabaseConnection::getInstance();
        $this->activitiesFormats = $db->getAllAssoc('SELECT site_id, date_format_ddmmyy, time_format_24 FROM site');
        $db->query('UPDATE site SET date_format_ddmmyy = ' . ($dateFormat === 'DMY' ? 1 : 0)
            . ', time_format_24 = ' . ($timeFormat === '24' ? 1 : 0));
    }

    /** @Given Activities reminder controls are available */
    public function activitiesReminders()
    {
        $file = LEGACY_ROOT . '/queue.time';
        $this->activitiesQueueTime = file_exists($file) ? filemtime($file) : false;
        touch($file);
    }

    /** @AfterScenario @activities */
    public function restoreActivitiesEnvironment()
    {
        $db = DatabaseConnection::getInstance();
        if ($this->activitiesFormats !== null)
        {
            foreach ($this->activitiesFormats as $site)
            {
                $db->query(sprintf('UPDATE site SET date_format_ddmmyy = %d, time_format_24 = %d WHERE site_id = %d',
                    $site['date_format_ddmmyy'], $site['time_format_24'], $site['site_id']));
            }
            $this->activitiesFormats = null;
        }
        if ($this->activitiesQueueTime !== null)
        {
            $file = LEGACY_ROOT . '/queue.time';
            if ($this->activitiesQueueTime === false)
            {
                unlink($file);
            }
            else
            {
                touch($file, $this->activitiesQueueTime);
            }
            $this->activitiesQueueTime = null;
        }
    }

    /** @Then the Activities event is saved as :visibility with duration :duration */
    public function activitiesSavedEvent($visibility, $duration)
    {
        $rows = DatabaseConnection::getInstance()->getAllAssoc(
            "SELECT title, duration, public, all_day, reminder_enabled, reminder_email, reminder_time FROM calendar_event WHERE data_item_type = " . DATA_ITEM_CANDIDATE
            . " AND data_item_id = " . $this->activitiesCandidateID
        );
        if (count($rows) !== 1 || $rows[0]['title'] !== 'Activities scheduled fixture'
            || (int) $rows[0]['public'] !== ($visibility === 'public' ? 1 : 0)
            || (int) $rows[0]['duration'] !== (int) $duration
            || (int) $rows[0]['all_day'] !== ((int) $duration === 0 ? 1 : 0)
            || ((int) $duration === 90 && ((int) $rows[0]['reminder_enabled'] !== 1
                || $rows[0]['reminder_email'] !== 'activities@example.invalid'
                || (int) $rows[0]['reminder_time'] !== 30)))
        {
            throw new \RuntimeException('Scheduled Activities event differs from submitted fields: ' . json_encode($rows));
        }
    }

    private function activitiesGrid()
    {
        return $this->getSession()->getPage()->findById('table' . md5('activity:ActivityDataGrid'));
    }

    /** @Then Activities shows :count rows starting with :note */
    public function activitiesRows($count, $note)
    {
        $this->spins(function () use ($count, $note) {
            $rows = $this->activitiesGrid()->findAll('css', 'tbody tr');
            if (count($rows) !== (int) $count || strpos($rows[0]->getText(), $note) === false)
            {
                throw new \RuntimeException('Unexpected Activities row count/order: ' . count($rows) . ' rows; first: ' . (isset($rows[0]) ? $rows[0]->getText() : 'none'));
            }
        });
    }

    /** @When I sort Activities by :column */
    public function activitiesSort($column)
    {
        foreach ($this->activitiesGrid()->findAll('named', array('link', $column)) as $link)
        {
            if ($link->isVisible())
            {
                $link->click();
                return;
            }
        }
        throw new \RuntimeException('Visible Activities sort link not found: ' . $column);
    }

    /** @When I filter Activities notes by :text */
    public function activitiesFilter($text)
    {
        $page = $this->getSession()->getPage();
        $area = $page->findById('filterResultsArea' . md5('activity:ActivityDataGrid'));
        if ($area->isVisible())
        {
            $page->pressButton('Remove All');
            $page = $this->getSession()->getPage();
            $area = $page->findById('filterResultsArea' . md5('activity:ActivityDataGrid'));
        }
        if (!$area->isVisible())
        {
            $page->pressButton('More filters');
        }
        $id = 'filterResultsAreaTable' . md5('activity:ActivityDataGrid') . '1';
        $page->findById($id . 'columnName')->selectOption('Notes');
        $page->findById($id . 'operator')->selectOption('contains');
        $page->findById($id . 'value')->setValue($text);
        $page->findById($id . 'value')->blur();
        $page->pressButton('Apply');
    }

    /** @Then the Activities grid contains :text */
    public function activitiesGridContains($text)
    {
        $actual = $this->activitiesGrid()->getText();
        if (strpos($actual, $text) === false)
        {
            throw new \RuntimeException('Missing ' . $text . ' in Activities grid: ' . $actual);
        }
    }

    /** @Then the Activities validation alert contains :message */
    public function activitiesAlert($message)
    {
        $actual = $this->getSession()->getDriver()->getWebDriverSession()->getAlert_text();
        if (strpos($actual, $message) === false)
        {
            throw new \RuntimeException('Unexpected validation alert: ' . $actual);
        }
    }

    /** @When I toggle the Activities column :column */
    public function activitiesColumn($column)
    {
        $page = $this->getSession()->getPage();
        $page->findById('exportBoxLink' . md5('activity:ActivityDataGrid'))->click();
        $page->findById('ColumnBox' . md5('activity:ActivityDataGrid'))->clickLink($column);
    }

    /** @When I move the Activities Date column after First Name */
    public function activitiesMoveColumn()
    {
        $session = $this->getSession()->getDriver()->getWebDriverSession();
        $hash = md5('activity:ActivityDataGrid');
        $source = $session->element('id', 'cell' . $hash . '0');
        $target = $session->element('id', 'cell' . $hash . '2');
        $session->moveto(array('element' => $source->getID()));
        $session->buttondown();
        // The existing column mover starts only after its 450ms hold threshold.
        usleep(600000);
        $session->moveto(array('element' => $target->getID()));
        $session->buttonup();
    }

    /** @Then Activities starts with the :column column */
    public function activitiesFirstColumn($column)
    {
        $this->spins(function () use ($column) {
            $header = $this->getSession()->getPage()->findById('cell' . md5('activity:ActivityDataGrid') . '0');
            if (!$header || trim($header->getText()) !== $column)
            {
                throw new \RuntimeException('Unexpected first Activities column.');
            }
        });
    }

    /** @Then the Activities column :column is :state */
    public function activitiesColumnState($column, $state)
    {
        $visible = false;
        foreach ($this->activitiesGrid()->findAll('named', array('link', $column)) as $link)
        {
            $visible = $visible || $link->isVisible();
        }
        if ($visible !== ($state === 'visible'))
        {
            throw new \RuntimeException('Unexpected Activities column visibility: ' . $column);
        }
    }

    /** @Then the Activities field :id is :state */
    public function activitiesFieldState($id, $state)
    {
        $field = $this->getSession()->getPage()->findById($id);
        if (!$field || $field->hasAttribute('disabled') !== ($state === 'disabled'))
        {
            throw new \RuntimeException('Unexpected field state: ' . $id);
        }
    }

    /** @Then the Activities panel :id is :state */
    public function activitiesPanelState($id, $state)
    {
        $field = $this->getSession()->getPage()->findById($id);
        if (!$field || $field->isVisible() !== ($state === 'visible'))
        {
            throw new \RuntimeException('Unexpected panel state: ' . $id);
        }
    }
}
