<?php

/** Disposable database fixtures shared by functional and access-level Home checks. */
trait HomeDataSteps
{
    private $homeData = array();
    private $homeActor;
    private $homeActivityNote = 'Home regression conversation';

    private function homeInsert($table, $values)
    {
        $db = DatabaseConnection::getInstance();
        $db->query('INSERT INTO ' . $table . ' ' . $values);
        $id = (int) $db->getLastInsertID();
        $this->homeData[$table][] = $id;
        return $id;
    }

    /** @Given Home has working-day records for :username */
    public function homeWorkingData($username)
    {
        require_once './config.php';
        require_once LEGACY_ROOT . '/lib/Users.php';
        $this->homeActor = (int) (new Users())->getIDByUsername($username);
        if (!$this->homeActor) throw new \RuntimeException('Missing Home fixture user: ' . $username);
        $actor = $this->homeActor;
        $other = (int) (new Users())->getIDByUsername($username === 'testerRead' ? 'testerEdit' : 'testerRead');
        $company = $this->homeInsert('company', "(name, owner, commercial_tier, relationship_status) VALUES ('Home regression account', $actor, 'A', 'Client')");
        $this->homeInsert('company', "(name, owner, commercial_tier, relationship_status) VALUES ('Home regression other account', $other, 'A', 'Client')");
        $candidate = $this->homeInsert('candidate', "(first_name, last_name, owner, entered_by) VALUES ('Home regression', 'candidate', $actor, $actor)");
        $hiddenCandidate = $this->homeInsert('candidate', "(first_name, last_name, owner, entered_by, is_admin_hidden) VALUES ('Home regression', 'hidden candidate', $actor, $actor, 1)");
        $job = $this->homeInsert('joborder', "(title, company_id, owner, recruiter, status, date_created, first_actionable_at) VALUES ('Home regression job', $company, $actor, $actor, 'Active', NOW(), NOW())");
        $hidden = $this->homeInsert('joborder', "(title, company_id, owner, recruiter, status, is_admin_hidden, date_created, first_actionable_at) VALUES ('Home regression hidden job', $company, $actor, $actor, 'Active', 1, NOW(), DATE_SUB(NOW(), INTERVAL 30 DAY))");
        $this->homeInsert('task', "(title, assigned_to, created_by, date_created, date_modified, due_date, data_item_type, data_item_id) VALUES ('Home regression follow-up', $actor, $actor, NOW(), NOW(), CURDATE(), 400, $job)");
        $this->homeInsert('task', "(title, assigned_to, created_by, date_created, date_modified, due_date, data_item_type, data_item_id) VALUES ('Home regression hidden task', $actor, $actor, NOW(), NOW(), CURDATE(), 400, $hidden)");
        $this->homeInsert('task', "(title, assigned_to, created_by, date_created, date_modified) VALUES ('Home regression other task', $other, $other, NOW(), NOW())");
        $this->homeInsert('activity', "(data_item_type, data_item_id, joborder_id, entered_by, type, notes, date_created, date_occurred) VALUES (100, $candidate, $job, $actor, 100, 'Home regression conversation', NOW(), NOW())");
        $this->homeInsert('activity', "(data_item_type, data_item_id, joborder_id, entered_by, type, notes, date_created, date_occurred) VALUES (100, $hiddenCandidate, $job, $actor, 100, 'Home regression hidden conversation', NOW(), NOW())");
        $this->homeInsert('calendar_event', "(title, entered_by, public, date, type, data_item_type, data_item_id) VALUES ('Home regression appointment', $actor, 0, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 300, 400, $job)");
        $this->homeInsert('calendar_event', "(title, entered_by, public, date, type, data_item_type, data_item_id) VALUES ('Home regression hidden appointment', $actor, 0, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 300, 400, $hidden)");
        $this->homeInsert('calendar_event', "(title, entered_by, public, date, type) VALUES ('Home regression private appointment', $other, 0, DATE_ADD(CURDATE(), INTERVAL 1 DAY), 300)");
        $this->homeDataVerified();
    }

    /** @Then the Home working-day fixtures have their expected database state */
    public function homeDataVerified()
    {
        $db = DatabaseConnection::getInstance();
        $company = $this->homeData['company'][0];
        $job = $this->homeData['joborder'][0];
        $actor = $this->homeActor;
        $candidate = $this->homeData['candidate'][0];
        $checks = array(
            "SELECT COUNT(*) AS n FROM company WHERE company_id = $company AND owner = $actor AND commercial_tier = 'A' AND relationship_status = 'Client'",
            "SELECT COUNT(*) AS n FROM joborder WHERE joborder_id = $job AND company_id = $company AND owner = $actor AND recruiter = $actor AND status = 'Active' AND is_admin_hidden = 0",
            'SELECT COUNT(*) AS n FROM task WHERE task_id = ' . $this->homeData['task'][0] . " AND assigned_to = $actor AND status = 'open' AND due_date = CURDATE() AND data_item_type = 400 AND data_item_id = $job",
            'SELECT COUNT(*) AS n FROM activity WHERE activity_id = ' . $this->homeData['activity'][0] . " AND data_item_type = 100 AND data_item_id = $candidate AND joborder_id = $job AND entered_by = $actor AND notes = 'Home regression conversation'",
            'SELECT COUNT(*) AS n FROM calendar_event WHERE calendar_event_id = ' . $this->homeData['calendar_event'][0] . " AND entered_by = $actor AND public = 0 AND date >= CURDATE() AND data_item_type = 400 AND data_item_id = $job",
            'SELECT COUNT(*) AS n FROM joborder WHERE joborder_id = ' . $this->homeData['joborder'][1] . " AND owner = $actor AND is_admin_hidden = 1",
            'SELECT COUNT(*) AS n FROM task WHERE task_id = ' . $this->homeData['task'][1] . ' AND data_item_type = 400 AND data_item_id = ' . $this->homeData['joborder'][1] . " AND assigned_to = $actor AND status = 'open'",
            'SELECT COUNT(*) AS n FROM task WHERE task_id = ' . $this->homeData['task'][2] . " AND assigned_to <> $actor AND created_by <> $actor AND data_item_type IS NULL",
            'SELECT COUNT(*) AS n FROM calendar_event WHERE calendar_event_id = ' . $this->homeData['calendar_event'][2] . " AND entered_by <> $actor AND public = 0 AND date >= CURDATE()",
            'SELECT COUNT(*) AS n FROM company WHERE company_id = ' . $this->homeData['company'][1] . " AND owner <> $actor AND commercial_tier = 'A' AND relationship_status = 'Client'",
            'SELECT COUNT(*) AS n FROM candidate WHERE candidate_id = ' . $this->homeData['candidate'][1] . ' AND is_admin_hidden = 1',
            'SELECT COUNT(*) AS n FROM activity WHERE activity_id = ' . $this->homeData['activity'][1] . ' AND data_item_type = 100 AND data_item_id = ' . $this->homeData['candidate'][1] . " AND joborder_id = $job AND notes = 'Home regression hidden conversation'"
        );
        foreach ($checks as $sql)
            if ((int) $db->getAssoc($sql)['n'] !== 1) throw new \RuntimeException('Home fixture state does not match: ' . $sql);
    }

    /** @Then Home shows the working-day records */
    public function homeWorkingRecords()
    {
        $sections = array(
            "Today's priorities" => array('Home regression follow-up'),
            'My accounts by tier' => array('Home regression account', 'Client'),
            'My current jobs' => array('Home regression job', 'Active'),
            'My Calendar' => array('Home regression appointment')
        );
        foreach ($sections as $heading => $records)
            foreach ($records as $text)
                if (strpos($this->homeSection($heading)->getText(), $text) === false)
                    throw new \RuntimeException('Missing ' . $text . ' in ' . $heading);
        $this->homeRecordLinks();
    }

    /** @Then Home links to the seeded records */
    public function homeRecordLinks()
    {
        $page = $this->getSession()->getPage();
        $records = array(
            array('Home regression follow-up', 'taskID', $this->homeData['task'][0]),
            array('Home regression account', 'companyID', $this->homeData['company'][0]),
            array('Home regression job', 'jobOrderID', $this->homeData['joborder'][0]),
            array('Home regression appointment', 'showEvent', $this->homeData['calendar_event'][0])
        );
        foreach ($records as [$name, $parameter, $id])
        {
            $found = false;
            foreach ($page->findAll('named', array('link', $name)) as $link)
            {
                parse_str((string) parse_url(html_entity_decode($link->getAttribute('href')), PHP_URL_QUERY), $query);
                if (($query[$parameter] ?? null) == $id && $link->getText() === $name) $found = true;
            }
            if (!$found) throw new \RuntimeException('Missing record destination: ' . $name);
        }
    }

    /** @When the Home working-day records are resolved */
    public function homeResolveData()
    {
        $db = DatabaseConnection::getInstance();
        $updates = array(
            'task' => "status = 'completed'", 'company' => 'commercial_tier = NULL',
            'joborder' => "status = 'Closed'", 'calendar_event' => 'date = DATE_SUB(CURDATE(), INTERVAL 1 DAY)'
        );
        foreach ($updates as $table => $change)
        {
            $id = $this->homeData[$table][0];
            $db->query('UPDATE ' . $table . ' SET ' . $change . ' WHERE ' . $table . '_id = ' . $id);
            if ((int) $db->getAssoc('SELECT COUNT(*) AS n FROM ' . $table . ' WHERE ' . $table . '_id = ' . $id . ' AND ' . str_replace(' = NULL', ' IS NULL', $change))['n'] !== 1)
                throw new \RuntimeException('Fixture update was not persisted: ' . $table);
        }
    }

    private function homeSection($heading)
    {
        $section = $this->getSession()->getPage()->find('xpath', '//section[.//h2[normalize-space()="' . $heading . '"]]');
        if (!$section) throw new \RuntimeException('Missing Home section: ' . $heading);
        return $section;
    }

    /** @Then Home no longer shows the resolved working-day records */
    public function homeResolvedRecords()
    {
        foreach (array('Home regression follow-up', 'Home regression job', 'Home regression appointment') as $text)
            $this->assertSession()->pageTextNotContains($text);
        if (strpos($this->homeSection('My accounts by tier')->getText(), 'Home regression account') !== false)
            throw new \RuntimeException('Unclassified Company remains in accounts by tier.');
    }

    /** @Then Home shows the seeded Company warning */
    public function homeCompanyWarning()
    {
        $queue = $this->homeSection('All priorities');
        foreach (array('Home regression account', 'Plan the next follow-up', 'No qualifying contact') as $text)
            if (strpos($queue->getText(), $text) === false) throw new \RuntimeException('Missing Company warning evidence: ' . $text);
        if (!$queue->find('css', 'a[href*="companyID=' . $this->homeData['company'][0] . '"]'))
            throw new \RuntimeException('Company warning source is incorrect.');
    }

    /** @When the seeded Company gets a qualifying contact and next action */
    public function homeCompanyContact()
    {
        $company = $this->homeData['company'][0];
        $actor = $this->homeActor;
        $this->homeInsert('activity', "(data_item_type, data_item_id, entered_by, type, date_created, date_occurred) VALUES (200, $company, $actor, 500, NOW(), NOW())");
        $this->homeInsert('task', "(title, assigned_to, created_by, date_created, date_modified, purpose, due_date, data_item_type, data_item_id) VALUES ('Home regression next action', $actor, $actor, NOW(), NOW(), 'relationship_follow_up', DATE_ADD(CURDATE(), INTERVAL 1 DAY), 200, $company)");
    }

    /** @Then Home shows the changed Company warning evidence */
    public function homeChangedCompanyWarning()
    {
        $queue = $this->homeSection('All priorities')->getText();
        foreach (array('Home regression account', 'Upcoming follow-up') as $text)
            if (strpos($queue, $text) === false) throw new \RuntimeException('Missing changed warning: ' . $text);
        foreach (array('Plan the next follow-up', 'Reconnect with this Company', 'No qualifying contact') as $text)
            if (strpos($queue, $text) !== false) throw new \RuntimeException('Stale warning evidence remains: ' . $text);
    }

    /** @When I open the seeded Home Task */
    public function homeOpenTask()
    {
        $this->visitPath('/index.php?m=tasks&a=show&taskID=' . $this->homeData['task'][0]);
        $this->assertSession()->pageTextContains('Home regression follow-up');
    }

    /** @When I open Activity for the seeded Home priority */
    public function homeOpenActivity()
    {
        $this->visitPath('/index.php?m=home&a=contextActivity&homeContext=priority-task-' . $this->homeData['task'][0]);
    }

    /** @When I open context for the seeded Home priority */
    public function homeOpenContext()
    {
        $button = $this->getSession()->getPage()->find('css', 'button[aria-label="Context & actions: Home regression follow-up"]');
        if (!$button) throw new \RuntimeException('Missing priority context control.');
        if ($button->getAttribute('aria-expanded') !== 'true') $button->click();
        if (!$this->getSession()->getPage()->waitFor(5, function ($page) {
            return strpos($page->getText(), $this->homeActivityNote) !== false;
        }))
            throw new \RuntimeException('Recorded Activity did not load for the selected priority.');
    }

    /** @Then Home displays the seeded recorded Activity */
    public function homeActivityRecord()
    {
        $this->assertSession()->pageTextContains($this->homeActivityNote);
        $link = $this->getSession()->getPage()->findLink('Home regression candidate');
        if (!$link || strpos($link->getAttribute('href'), 'candidateID=' . $this->homeData['candidate'][0]) === false)
            throw new \RuntimeException('Recorded Activity subject link is incorrect.');
    }

    /** @When the seeded Home Activity is corrected */
    public function homeCorrectActivity()
    {
        $db = DatabaseConnection::getInstance();
        $this->homeActivityNote = 'Home regression corrected conversation';
        $id = $this->homeData['activity'][0];
        $db->query('UPDATE activity SET notes = ' . $db->makeQueryString($this->homeActivityNote) . ' WHERE activity_id = ' . $id);
        $row = $db->getAssoc('SELECT notes FROM activity WHERE activity_id = ' . $id);
        if ($row['notes'] !== $this->homeActivityNote) throw new \RuntimeException('Activity correction did not persist.');
    }

    /** A compact queue may legitimately omit a warning behind other authorised work. */
    private function homeFindCompanyWarning()
    {
        $this->visitPath('/index.php?m=home&a=priorities&filter=warnings');
        $visited = array();
        while (true)
        {
            $url = $this->getSession()->getCurrentUrl();
            if (isset($visited[$url])) throw new \RuntimeException('Priority pagination repeated a page.');
            $visited[$url] = true;
            $queue = $this->homeSection('All priorities');
            if ($queue->findLink('Home regression account'))
            {
                $this->homeCompanyWarning();
                return;
            }
            if (!$queue->findLink('Next >>')) throw new \RuntimeException('Seeded Company warning is absent from all warning priorities.');
            $this->followLinkAndWaitForNavigation('Next >>');
        }
    }

    /** @Then Home enforces working-day access :access */
    public function homeDataAccess($access)
    {
        if ($access === 'DISABLED')
        {
            foreach (array('Home regression follow-up', 'Home regression account', 'Home regression job', 'Home regression appointment') as $text)
                $this->assertSession()->pageTextNotContains($text);
            $this->assertSession()->pageTextNotContains('Complete Task');
            $this->homeOpenActivity();
            $this->assertSession()->pageTextNotContains('Home regression conversation');
            return;
        }
        $this->homeWorkingRecords();
        $this->assertSession()->pageTextNotContains('Home regression other account');
        $this->assertSession()->pageTextNotContains('Home regression other task');
        $this->assertSession()->pageTextNotContains('Home regression private appointment');
        $admin = in_array($access, array('ADMIN', 'MULTI_ADMIN', 'ROOT'), true);
        foreach (array('Home regression hidden job', 'Home regression hidden task', 'Home regression hidden appointment') as $text)
        {
            if ($admin) $this->assertSession()->pageTextContains($text);
            else $this->assertSession()->pageTextNotContains($text);
        }
        $page = $this->getSession()->getPage();
        $button = $page->find('css', 'button[data-home-task="' . $this->homeData['task'][0] . '"]');
        if (($button !== null) !== ($access !== 'READONLY')) throw new \RuntimeException('Incorrect Home Task action permission.');
        $this->homeFindCompanyWarning();
        $this->homeOpenActivity();
        $this->homeActivityRecord();
        if ($admin) $this->assertSession()->pageTextContains('Home regression hidden conversation');
        else $this->assertSession()->pageTextNotContains('Home regression hidden conversation');
        $this->visitPath('/index.php?m=home&a=contextActivity&homeContext=priority-task-' . $this->homeData['task'][2]);
        $this->assertSession()->pageTextNotContains('Home regression other task');
        $this->assertSession()->pageTextContains("You don't have permission");
        if (!$admin)
        {
            $this->visitPath('/index.php?m=tasks&a=show&taskID=' . $this->homeData['task'][1]);
            $this->assertSession()->pageTextNotContains('Home regression hidden task');
            $this->assertSession()->pageTextContains('Task unavailable.');
        }
        $this->homeOpenTask();
    }

    /** @AfterScenario @home_data */
    public function homeDataCleanup()
    {
        if (!$this->homeData) return;
        $db = DatabaseConnection::getInstance();
        foreach (array('calendar_event', 'activity', 'task', 'joborder', 'candidate', 'company') as $table)
        {
            if (empty($this->homeData[$table])) continue;
            $ids = implode(',', $this->homeData[$table]);
            if (in_array($table, array('company', 'joborder', 'candidate', 'task'), true))
            {
                $types = array('company' => 200, 'joborder' => 400, 'candidate' => DATA_ITEM_CANDIDATE, 'task' => DATA_ITEM_TASK);
                $db->query('DELETE FROM history WHERE data_item_type = ' . $types[$table] . ' AND data_item_id IN (' . $ids . ')');
                $db->query('DELETE FROM warning_review WHERE data_item_type = ' . $types[$table] . ' AND data_item_id IN (' . $ids . ')');
                $parameter = $table === 'company' ? 'companyID' : ($table === 'joborder' ? 'jobOrderID' : ($table === 'candidate' ? 'candidateID' : 'taskID'));
                $db->query('DELETE FROM mru WHERE data_item_type = ' . $types[$table] . ' AND url REGEXP ' .
                    $db->makeQueryString($parameter . '=(' . implode('|', $this->homeData[$table]) . ')(&|$)'));
            }
            $db->query('DELETE FROM ' . $table . ' WHERE ' . $table . '_id IN (' . $ids . ')');
        }
        $this->homeData = array();
    }
}
