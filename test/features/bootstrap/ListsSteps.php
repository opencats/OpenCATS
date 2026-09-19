<?php

/** Focused Lists workflows; fixtures never alter the existing saved lists. */
trait ListsSteps
{
    private $listsFixtureID = null;
    private $listsSelectedIDs = array();
    private $listsPagingIDs = array();

    private function listsEntity($kind)
    {
        $entities = array(
            'candidate' => array(100, 20000),
            'company' => array(200, 20002),
            'contact' => array(300, 30001),
            'joborder' => array(400, 40001)
        );
        return $entities[$kind];
    }

    /** @Given a disposable :kind saved list */
    public function listsFixture($kind)
    {
        list($type, $id) = $this->listsEntity($kind);
        $db = DatabaseConnection::getInstance();
        $db->query("INSERT INTO saved_list (description, data_item_type, is_dynamic, created_by, number_entries, date_created, date_modified) VALUES ('Lists workflow fixture', " . $type . ", 0, 1, 1, NOW(), NOW())");
        $this->listsFixtureID = (int) $db->getLastInsertID();
        $db->query('INSERT INTO saved_list_entry (saved_list_id, data_item_type, data_item_id, date_created) VALUES (' . $this->listsFixtureID . ', ' . $type . ', ' . $id . ', NOW())');
    }

    /** @When I open the disposable saved list */
    public function listsOpenFixture()
    {
        $this->visitPath('/index.php?m=lists&a=showList&savedListID=' . $this->listsFixtureID);
    }

    /** @When I open the Lists modal for :kind */
    public function listsOpenModal($kind)
    {
        list($type, $id) = $this->listsEntity($kind);
        $this->visitPath('/index.php?m=lists&a=quickActionAddToListModal&dataItemType=' . $type . '&dataItemID=' . $id);
    }

    /** @When I select and edit the disposable list */
    public function listsEditFixture()
    {
        $page = $this->getSession()->getPage();
        $page->findById('savedListRowCheck' . $this->listsFixtureID)->check();
        $page->findById('savedListRow' . $this->listsFixtureID)->pressButton('Edit');
    }

    /** @Then the disposable list name is :name */
    public function listsAssertName($name)
    {
        $field = $this->getSession()->getPage()->findById('savedListRowInput' . $this->listsFixtureID);
        if (!$field->isVisible() || $field->getValue() !== $name)
        {
            throw new \RuntimeException('The selected list did not enter edit mode with its name.');
        }
    }

    /** @When I rename the disposable list to :name */
    public function listsRename($name)
    {
        $this->getSession()->getPage()->findById('savedListRowInput' . $this->listsFixtureID)->setValue($name);
    }

    /** @When I save the disposable list name */
    public function listsSaveFixture()
    {
        $this->getSession()->getPage()->findById('savedListRowEditing' . $this->listsFixtureID)->pressButton('Save');
    }

    /** @When I save the new Lists name */
    public function listsSaveNew()
    {
        $this->getSession()->getPage()->findById('savedListNew')->pressButton('Save');
    }

    /** @Then the new list contains the candidate */
    public function listsNewMembership()
    {
        $db = DatabaseConnection::getInstance();
        $row = $db->getAssoc("SELECT saved_list_id FROM saved_list WHERE description = 'Lists workflow created'");
        $createdID = (int) $row['saved_list_id'];
        $entry = $db->getAssoc('SELECT data_item_id FROM saved_list_entry WHERE saved_list_id = ' . $createdID . ' AND data_item_type = 100');
        if (!$entry || (int) $entry['data_item_id'] !== 20000)
        {
            throw new \RuntimeException('Add To Lists did not preserve candidate membership.');
        }
    }

    /** @Then the Lists grid parameter :key is :value */
    public function listsGridParameter($key, $value)
    {
        parse_str(parse_url($this->getSession()->getCurrentUrl(), PHP_URL_QUERY), $query);
        $parameters = json_decode($query['parameterslists:ListsDataGrid'], true);
        if ((string) $parameters[$key] !== $value)
        {
            throw new \RuntimeException('Unexpected Lists DataGrid parameter: ' . $key);
        }
    }

    /** @When I open Add To List with two selected candidates */
    public function listsBulkModal()
    {
        $this->visitPath('/index.php?m=candidates');
        $page = $this->getSession()->getPage();
        $checkboxes = $page->findAll('css', 'input[id^="checked_"]');
        if (count($checkboxes) < 2)
        {
            throw new \RuntimeException('Lists integration requires two candidate fixtures.');
        }
        foreach (array_slice($checkboxes, 0, 2) as $checkbox)
        {
            $this->listsSelectedIDs[] = substr($checkbox->getAttribute('id'), strlen('checked_'));
            $checkbox->check();
        }
        $page->clickLink('Action');
        $page->find('css', 'a[onclick*="addToListFromDatagridModal"][onclick*="serializeArray"]')->click();
        $this->getSession()->switchToIFrame('popupFrameIFrame');
        $this->iWaitUntilISee('Add To Lists');
    }

    /** @Then the Lists modal retains the selected candidates */
    public function listsBulkIDs()
    {
        $actual = explode(',', $this->getSession()->getPage()->findById('dataItemArray')->getValue());
        sort($actual);
        sort($this->listsSelectedIDs);
        if ($actual !== $this->listsSelectedIDs)
        {
            throw new \RuntimeException('DataGrid selection did not reach Add To List.');
        }
    }

    /** @Then the Lists modal closes */
    public function listsModalClosed()
    {
        $this->getSession()->switchToIFrame(null);
        if ($this->getSession()->getPage()->findById('popupContainer')->isVisible())
        {
            throw new \RuntimeException('Cancel did not close the shared modal.');
        }
    }

    /** @When I remove the candidate from the disposable list */
    public function listsRemoveMember()
    {
        $page = $this->getSession()->getPage();
        $page->findById('checked_20000')->check();
        $page->clickLink('Action');
        $page->find('css', 'a[onclick*="removeFromListDatagrid"]')->click();
    }

    /** @Then the disposable list has no members and the candidate still exists */
    public function listsMemberRemoved()
    {
        $db = DatabaseConnection::getInstance();
        $entry = $db->getAssoc('SELECT saved_list_entry_id FROM saved_list_entry WHERE saved_list_id = ' . $this->listsFixtureID);
        $candidate = $db->getAssoc('SELECT candidate_id FROM candidate WHERE candidate_id = 20000');
        if ($entry || !$candidate)
        {
            throw new \RuntimeException('Remove From This List changed entity or membership semantics.');
        }
    }

    /** @Then the disposable list is deleted and the candidate still exists */
    public function listsDeleted()
    {
        $this->listsMemberRemoved();
        $list = DatabaseConnection::getInstance()->getAssoc('SELECT saved_list_id FROM saved_list WHERE saved_list_id = ' . $this->listsFixtureID);
        if ($list)
        {
            throw new \RuntimeException('Delete List did not submit the existing POST action.');
        }
    }

    /** @Given an empty disposable saved list */
    public function listsEmptyFixture()
    {
        $this->listsFixture('candidate');
        $db = DatabaseConnection::getInstance();
        $db->query('DELETE FROM saved_list_entry WHERE saved_list_id = ' . $this->listsFixtureID);
        $db->query('UPDATE saved_list SET number_entries = 0 WHERE saved_list_id = ' . $this->listsFixtureID);
    }

    /** @When I delete the disposable list in the modal */
    public function listsDeleteModal()
    {
        $this->getSession()->getPage()->findById('savedListRowEditing' . $this->listsFixtureID)->pressButton('Delete');
        if (!$this->getSession()->wait(5000, "document.getElementById('savedListRowAjaxing" . $this->listsFixtureID . "').style.display === 'none'"))
        {
            throw new \RuntimeException('Modal deletion did not finish.');
        }
        foreach (array('savedListRow', 'savedListRowEditing', 'savedListRowAjaxing') as $prefix)
        {
            if ($this->getSession()->getPage()->findById($prefix . $this->listsFixtureID)->isVisible())
            {
                throw new \RuntimeException('Deleted modal row is still visible.');
            }
        }
    }

    /** @Given 16 disposable lists for pagination */
    public function listsPagingFixtures()
    {
        $db = DatabaseConnection::getInstance();
        for ($i = 0; $i < 16; ++$i)
        {
            $db->query("INSERT INTO saved_list (description, data_item_type, is_dynamic, created_by, number_entries, date_created, date_modified) VALUES ('Lists paging fixture " . $i . "', 100, 0, 1, 0, NOW(), NOW())");
            $this->listsPagingIDs[] = (int) $db->getLastInsertID();
        }
    }

    /** @When I filter the saved list by first name :name */
    public function listsFilter($name)
    {
        $page = $this->getSession()->getPage();
        $page->find('css', 'select[id$="columnName"]')->selectOption('First Name');
        $page->find('css', 'input[id$="1value"]')->setValue($name);
        $page->pressButton('Apply');
    }

    /** @AfterScenario @lists */
    public function listsCleanup()
    {
        $db = DatabaseConnection::getInstance();
        if ($this->listsPagingIDs)
        {
            $db->query('DELETE FROM saved_list WHERE saved_list_id IN (' . implode(',', $this->listsPagingIDs) . ')');
        }
        // Include a list created by this workflow even if its final assertion failed.
        if ($this->listsFixtureID)
        {
            $created = $db->getAssoc("SELECT saved_list_id FROM saved_list WHERE description = 'Lists workflow created' AND created_by = 1");
            $ids = array($this->listsFixtureID);
            if ($created)
            {
                $ids[] = (int) $created['saved_list_id'];
            }
            $idList = implode(',', $ids);
            $db->query('DELETE FROM saved_list_entry WHERE saved_list_id IN (' . $idList . ')');
            $db->query('DELETE FROM saved_list WHERE saved_list_id IN (' . $idList . ')');
            foreach ($ids as $id)
            {
                $db->query("DELETE FROM mru WHERE data_item_type = 700 AND url LIKE '%savedListID=" . $id . "'");
            }
        }
    }
}
