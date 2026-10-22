<?php

/** Calendar output-boundary regressions using disposable local probes only. */
trait CalendarXssSteps
{
    private $calendarXssID;
    private $calendarXssOwner;
    private $calendarXssStored;

    private function calendarXssProbe()
    {
        return '<svg/onload=calendarXss=1><svg/onload=calendarXss=1>&&<>"\'Ω&lt;';
    }

    private function calendarXssDescription()
    {
        return $this->calendarXssProbe() . "\nSecond line & literal <text>\n第三行";
    }

    /** @Given an existing public Calendar probe in :field */
    public function calendarStoredProbe($field)
    {
        $db = DatabaseConnection::getInstance();
        $ownerID = (int) (new Users())->getIDByUsername('testerEdit');
        $title = $field === 'title' ? $this->calendarXssProbe() : 'Calendar stored probe';
        $description = $field === 'description' ? $this->calendarXssDescription() : 'Calendar details';
        $db->query('INSERT INTO calendar_event (type, date, title, description, entered_by, public) VALUES'
            . " (300, '2030-06-12 09:15:00', " . $db->makeQueryString($title) . ', '
            . $db->makeQueryString($description) . ', ' . $ownerID . ', 1)');
        $this->calendarXssID = (int) $db->getLastInsertID();
        $this->calendarFixtureEvents[] = $this->calendarXssID;
        if ($field === 'owner')
        {
            $this->calendarXssOwner = $db->getAssoc('SELECT user_id, first_name, last_name FROM user WHERE user_id = ' . $ownerID);
            // Short enough for both name columns, with repeated executable elements.
            $name = $db->makeQueryString('<svg onload="window.calendarXss=1">');
            $db->query('UPDATE user SET first_name = ' . $name . ', last_name = ' . $name . ' WHERE user_id = ' . $ownerID);
        }
    }

    /** @AfterScenario @calendar-xss */
    public function restoreCalendarProbeOwner()
    {
        if ($this->calendarXssOwner)
        {
            $db = DatabaseConnection::getInstance();
            $db->query('UPDATE user SET first_name = ' . $db->makeQueryString($this->calendarXssOwner['first_name'])
                . ', last_name = ' . $db->makeQueryString($this->calendarXssOwner['last_name'])
                . ' WHERE user_id = ' . (int) $this->calendarXssOwner['user_id']);
        }
    }

    /** @When I open the stored Calendar probe */
    public function openCalendarProbe()
    {
        $this->visitPath('/index.php?m=calendar&view=DAYVIEW&year=2030&month=6&day=12&showEvent=' . $this->calendarXssID);
        $this->getSession()->getPage()->find('css', '#calendarDay .calendarEntry')->click();
    }

    /** @Then Calendar contains no executable probe markup */
    public function calendarProbeSafe()
    {
        $state = $this->getSession()->evaluateScript('({executed: !!window.calendarXss, elements: document.querySelectorAll("#calendarMonth svg, #calendarWeek svg, #calendarDay svg, #viewEventTD svg, #viewEventDescription text").length})');
        if ($state['executed'] || $state['elements'])
        {
            throw new \RuntimeException('Calendar probe executed or became markup: ' . json_encode($state));
        }
    }

    /** @Then Calendar :selector displays the literal :field probe */
    public function calendarLiteralProbe($selector, $field)
    {
        $expected = $field === 'description' ? $this->calendarXssDescription() : $this->calendarXssProbe();
        if ($field === 'owner')
        {
            $expected = '<svg onload="window.calendarXss=1"> <svg onload="window.calendarXss=1">';
        }
        $actual = $this->getSession()->evaluateScript('document.querySelector(' . json_encode($selector) . ').textContent');
        if (strpos(str_replace("\r\n", "\n", $actual), $expected) === false)
        {
            throw new \RuntimeException('Calendar lost literal ' . $field . ': ' . $actual);
        }
        $this->calendarProbeSafe();
        if ($field === 'description' && $this->getSession()->evaluateScript('getComputedStyle(document.getElementById("viewEventDescription")).whiteSpace') !== 'pre-wrap')
        {
            throw new \RuntimeException('Calendar description must preserve line breaks');
        }
    }

    /** @When I navigate away and AJAX-load the Calendar probe month */
    public function calendarAjaxProbe()
    {
        $this->visitPath('/index.php?m=calendar&view=MONTHVIEW&year=2030&month=3');
        // June is outside the initial adjacent-month cache. These are native navigation clicks.
        for ($month = 4; $month <= 6; ++$month)
        {
            $this->getSession()->getPage()->find('css', '#linkMonthForeward > button')->click();
            if (!$this->getSession()->wait(10000, 'document.querySelector("#linkMonthForeward > button") !== null && currentMonth === ' . $month))
            {
                throw new \RuntimeException('Calendar AJAX navigation did not finish');
            }
        }
        if ($this->getSession()->evaluateScript('httpLookup.responseURL.indexOf("a=dynamicData") !== -1') !== true)
        {
            throw new \RuntimeException('Expected Calendar dynamicData AJAX response');
        }
    }

    /** @When the Add Edit user creates a public Calendar probe */
    public function createCalendarProbe()
    {
        $this->iLoginAs('testerEdit', 'opencats-test-security-903');
        $this->visitPath('/index.php?m=calendar&view=DAYVIEW&year=2030&month=6&day=12');
        $this->getSession()->getPage()->clickLink('Add Event');
        $page = $this->getSession()->getPage();
        $page->fillField('title', $this->calendarXssProbe());
        $page->selectFieldOption('type', 'Meeting');
        $page->selectFieldOption('dateAdd_Month_ID', 'Jun');
        $page->selectFieldOption('dateAdd_Day_ID', '12');
        $page->fillField('dateAdd_Year_ID', '30');
        $page->fillField('description', $this->calendarXssDescription());
        $page->checkField('publicEntry');
        $this->pressButtonAndWaitForNavigation('Add Event');
        $db = DatabaseConnection::getInstance();
        $row = $db->getAssoc('SELECT calendar_event_id, title, description FROM calendar_event WHERE title = '
            . $db->makeQueryString($this->calendarXssProbe()) . ' ORDER BY calendar_event_id DESC LIMIT 1');
        if (!$row) throw new \RuntimeException('Add/Edit user could not create event');
        $this->calendarXssID = (int) $row['calendar_event_id'];
        $this->calendarXssStored = array('title' => $row['title'], 'description' => $row['description']);
        $this->calendarFixtureEvents[] = $this->calendarXssID;
    }

    /** @When the Calendar probe is saved twice without changing its text */
    public function editCalendarProbeTwice()
    {
        for ($i = 0; $i < 2; ++$i)
        {
            $this->getSession()->getPage()->pressButton('Edit Event');
            $page = $this->getSession()->getPage();
            if ($page->findById('titleEdit')->getValue() !== $this->calendarXssProbe()
                || $page->findById('descriptionEdit')->getValue() !== $this->calendarXssDescription())
            {
                throw new \RuntimeException('Calendar edit values were encoded or changed');
            }
            $this->pressButtonAndWaitForNavigation('Save');
        }
        $row = DatabaseConnection::getInstance()->getAssoc('SELECT title, description FROM calendar_event WHERE calendar_event_id = ' . $this->calendarXssID);
        if ($row !== $this->calendarXssStored)
        {
            throw new \RuntimeException('Calendar canonical stored values changed');
        }
    }

    /** @Given the Calendar :kind association has a probe label */
    public function calendarAssociationProbe($kind)
    {
        $db = DatabaseConnection::getInstance();
        $value = $db->makeQueryString($this->calendarXssProbe());
        $column = array('company' => 'name', 'joborder' => 'title', 'candidate' => 'first_name', 'contact' => 'first_name')[$kind];
        $db->query('UPDATE ' . $kind . ' SET ' . $column . ' = ' . $value
            . ' WHERE ' . $kind . '_id = ' . $this->calendarFixtureRecords[$kind]);
        if ($kind === 'candidate' || $kind === 'contact')
        {
            $db->query('UPDATE ' . $kind . ' SET last_name = ' . $value
                . ' WHERE ' . $kind . '_id = ' . $this->calendarFixtureRecords[$kind]);
        }
    }

    /** @Then the Calendar association retains its icon and navigable URL for :kind */
    public function calendarProbeLink($kind)
    {
        $this->calendarAssociationPersists($kind);
        $link = $this->getSession()->getPage()->find('css', '#viewEventLink a');
        if (!$link->find('css', 'img')) throw new \RuntimeException('Association icon missing');
        $url = $link->getAttribute('href');
        $this->performDocumentReplacement(function () use ($link) {
            $link->click();
        }, 'following Calendar association');
        parse_str(parse_url($url, PHP_URL_QUERY), $expected);
        parse_str(parse_url($this->getSession()->getCurrentUrl(), PHP_URL_QUERY), $actual);
        if ($actual !== $expected) throw new \RuntimeException('Association navigation changed URL');
    }
}
