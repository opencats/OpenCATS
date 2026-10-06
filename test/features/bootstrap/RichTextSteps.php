<?php

/** Real form/DB/AJAX coverage; all fixture records are disposable, no mail is sent. */
trait RichTextSteps
{
    private $richTextJobID;
    private $richTextCandidateID;
    private $richTextTemplateIDs = array();

    /** @Given a disposable rich-text job order */
    public function richTextJobFixture()
    {
        $db = DatabaseConnection::getInstance();
        $html = $db->makeQueryString('<p>Existing &amp; copied é</p><ul><li>Original list</li></ul>');
        $db->query("INSERT INTO joborder (title, company_id, recruiter, owner, entered_by, city, openings, openings_available, description, notes, date_created, date_modified) VALUES ('Rich Text Source', 20002, 1, 1, 1, 'London', 1, 1, $html, 'Plain notes', NOW(), NOW())");
        $this->richTextJobID = (int) $db->getLastInsertID();
    }

    /** @When I open a copy of the rich-text job order */
    public function richTextCopy()
    {
        $this->visitPath('/index.php?m=joborders&a=add&typeOfAdd=existing&jobOrderID=' . $this->richTextJobID);
    }

    /** @Then the :id editor is ready */
    public function richTextReady($id)
    {
        $this->getSession()->wait(5000, 'typeof OpenCATSEditor !== "undefined" && document.querySelector(".sun-editor-editable[contenteditable=true]") !== null');
        if (!$this->getSession()->getPage()->findById($id) || !$this->getSession()->getPage()->find('css', '.sun-editor-editable[contenteditable=true]'))
        {
            throw new \RuntimeException('Rich-text field did not initialize: ' . $id);
        }
    }

    /** @Then the :id editor contains :html */
    public function richTextContains($id, $html)
    {
        $this->richTextReady($id);
        $condition = 'OpenCATSEditor.getHTML(' . json_encode($id) . ').indexOf(' . json_encode($html) . ') !== -1';
        if (!$this->getSession()->wait(5000, $condition))
        {
            throw new \RuntimeException('Editor content missing ' . $html . ': ' . $this->getSession()->evaluateScript('OpenCATSEditor.getHTML(' . json_encode($id) . ')'));
        }
    }

    /** @Then Internal Notes is a plain textarea */
    public function richTextPlainNotes()
    {
        $notes = $this->getSession()->getPage()->findById('notes');
        if (!$notes->isVisible() || $notes->getTagName() !== 'textarea' || $notes->getValue() !== 'Plain notes')
        {
            throw new \RuntimeException('Internal Notes must remain an ordinary textarea.');
        }
    }

    /** @When I set the :id editor HTML to :html */
    public function richTextSet($id, $html)
    {
        $this->richTextReady($id);
        $this->getSession()->executeScript('OpenCATSEditor.setHTML(' . json_encode($id) . ', ' . json_encode($html) . ');');
    }

    /** @When I set the :id source HTML to :html */
    public function richTextSource($id, $html)
    {
        $this->richTextReady($id);
        $this->getSession()->getPage()->find('css', '.se-toolbar [data-command="codeView"]')->click();
        $this->getSession()->getPage()->find('css', 'textarea.se-code-viewer')->setValue($html);
    }

    /** @Then the saved rich-text job description contains :html */
    public function richTextSaved($html)
    {
        $row = DatabaseConnection::getInstance()->getAssoc("SELECT description FROM joborder WHERE title = 'Rich Text Saved Copy' ORDER BY joborder_id DESC LIMIT 1");
        if (!$row || strpos($row['description'], $html) === false)
        {
            throw new \RuntimeException('Current editor HTML was not saved to the job order.');
        }
    }

    /** @When I reopen the saved rich-text job order */
    public function richTextReopen()
    {
        $row = DatabaseConnection::getInstance()->getAssoc("SELECT joborder_id FROM joborder WHERE title = 'Rich Text Saved Copy' ORDER BY joborder_id DESC LIMIT 1");
        $this->visitPath('/index.php?m=joborders&a=edit&jobOrderID=' . $row['joborder_id']);
    }

    /** @Given disposable rich-text email fixtures */
    public function richTextEmailFixtures()
    {
        $db = DatabaseConnection::getInstance();
        $db->query("INSERT INTO candidate (first_name, last_name, email1, entered_by, owner, date_created, date_modified) VALUES ('EditorCandidate', 'Fixture', 'editor@example.test', 1, 1, NOW(), NOW())");
        $this->richTextCandidateID = (int) $db->getLastInsertID();
        foreach (array('Rich Text Template' => "Hello %CANDFIRSTNAME%\r\nSecond line", 'Rich Text Empty Template' => '') as $title => $text)
        {
            $db->query('INSERT INTO email_template (title, text, tag, allow_substitution) VALUES (' . $db->makeQueryString($title) . ', ' . $db->makeQueryString($text) . ", 'CUSTOM', 1)");
            $this->richTextTemplateIDs[] = (int) $db->getLastInsertID();
        }
    }

    /** @When I open the rich-text candidate email form */
    public function richTextEmailOpen()
    {
        $params = array('exportIDs' => array($this->richTextCandidateID), 'noSaveParameters' => true);
        $this->visitPath('/index.php?m=candidates&a=emailCandidates&i=candidates:candidatesListByViewDataGrid&p=' . urlencode(json_encode($params)));
        // Observe the existing AJAX lifecycle, including the deliberately ignored -2 response.
        $this->getSession()->executeScript('window.richTextAjaxDone = 0; var original = XMLHttpRequest.prototype.send; XMLHttpRequest.prototype.send = function(){ this.addEventListener("loadend", function(){ window.richTextAjaxDone++; }); return original.apply(this, arguments); };');
    }

    /** @When I select the rich-text candidate preview */
    public function richTextPreview()
    {
        $this->getSession()->getPage()->selectFieldOption('candidateName', '-1');
        $this->getSession()->getPage()->selectFieldOption('candidateName', (string) $this->richTextCandidateID);
    }

    /** @Then I wait for rich-text preview :text */
    public function richTextPreviewContains($text)
    {
        if (!$this->getSession()->wait(5000, 'document.getElementById("emailPreview").textContent.indexOf(' . json_encode($text) . ') !== -1'))
        {
            throw new \RuntimeException('Candidate variable substitution did not reach the preview.');
        }
    }

    /** @When I capture the email form submission without sending */
    public function richTextCaptureEmail()
    {
        $this->getSession()->executeScript('document.addEventListener("submit", function(e){ if (!e.defaultPrevented) window.richTextPost = new FormData(e.target).get("emailBody"); e.preventDefault(); });');
    }

    /** @Then the captured email body is :html */
    public function richTextEmailPost($html)
    {
        if ($this->getSession()->evaluateScript('window.richTextPost') !== $html)
        {
            throw new \RuntimeException('Email POST did not contain current source-view HTML.');
        }
    }

    /** @Then the email editor and preview are empty */
    public function richTextEmailEmpty()
    {
        $empty = $this->getSession()->evaluateScript('(function(){ var d=document.createElement("div");d.innerHTML=OpenCATSEditor.getHTML("emailBody");return d.textContent.trim()==="" && document.getElementById("emailPreview").innerHTML===""; })()');
        if (!$empty) throw new \RuntimeException('Placeholder selection did not clear the editor and preview.');
        $this->getSession()->executeScript('window.richTextAjaxDone = 0;');
    }

    /** @Then empty email body submission remains allowed */
    public function richTextEmptyBodyAllowed()
    {
        $empty = $this->getSession()->evaluateScript('(function(){ var d=document.createElement("div");d.innerHTML=window.richTextPost;return d.textContent.trim()===""; })()');
        if (!$empty) throw new \RuntimeException('Empty body submission changed the existing validation semantics.');
    }

    /** @Then an empty template error leaves the email body unchanged */
    public function richTextEmptyTemplateError()
    {
        if (!$this->getSession()->wait(5000, 'window.richTextAjaxDone > 0')) throw new \RuntimeException('Template AJAX did not finish.');
        $this->richTextContains('emailBody', '<p>Keep on error</p>');
    }

    /** @AfterScenario @richtext */
    public function richTextCleanup()
    {
        $db = DatabaseConnection::getInstance();
        $rows = $db->getAllAssoc("SELECT joborder_id FROM joborder WHERE title = 'Rich Text Saved Copy' OR joborder_id = " . (int) $this->richTextJobID);
        foreach ($rows as $row)
        {
            $id = (int) $row['joborder_id'];
            $db->query('DELETE FROM history WHERE data_item_type = 400 AND data_item_id = ' . $id);
            $db->query('DELETE FROM joborder WHERE joborder_id = ' . $id);
        }
        if ($this->richTextCandidateID) $db->query('DELETE FROM candidate WHERE candidate_id = ' . (int) $this->richTextCandidateID);
        foreach ($this->richTextTemplateIDs as $id) $db->query('DELETE FROM email_template WHERE email_template_id = ' . (int) $id);
    }
}
