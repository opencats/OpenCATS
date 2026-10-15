<?php

/** One browser workflow using the real CAPTCHA and native PHP session store. */
trait CareerPortalAuthenticationSteps
{
    private $returningCandidateID;
    private $returningCandidateRegistration;

    /** @Given a returning Career Portal candidate exists */
    public function createReturningCareerPortalCandidate()
    {
        $settings = new CareerPortalSettings();
        $this->returningCandidateRegistration = $settings->getAll()['candidateRegistration'];
        $settings->set('candidateRegistration', '1');

        $db = DatabaseConnection::getInstance();
        $db->query("INSERT INTO candidate (first_name, last_name, email1, zip) VALUES "
            . "('Returning', 'Applicant', 'career.portal.returning@example.com', '12345')");
        $this->returningCandidateID = (int) $db->getLastInsertID();
    }

    /** @AfterScenario @returning-career-candidate */
    public function restoreReturningCareerPortalCandidate()
    {
        if ($this->returningCandidateID)
        {
            $db = DatabaseConnection::getInstance();
            foreach (array('candidate_joborder', 'candidate_joborder_status_history', 'career_portal_questionnaire_history', 'candidate') as $table)
            {
                $db->query('DELETE FROM ' . $table . ' WHERE candidate_id = ' . $this->returningCandidateID);
            }
            foreach (array('activity', 'history') as $table)
            {
                $db->query('DELETE FROM ' . $table . ' WHERE data_item_type = ' . DATA_ITEM_CANDIDATE
                    . ' AND data_item_id = ' . $this->returningCandidateID);
            }
        }
        if ($this->returningCandidateRegistration !== null)
        {
            (new CareerPortalSettings())->set('candidateRegistration', $this->returningCandidateRegistration);
        }
    }

    /** @When I choose to apply as a returning candidate */
    public function chooseReturningCareerPortalCandidate()
    {
        $this->getSession()->getPage()->findField('isNewNo')->click();
    }

    /** @Then the returning candidate CAPTCHA is visible and usable */
    public function assertReturningCareerPortalCaptcha()
    {
        $session = $this->getSession();
        $loaded = $session->wait(5000, "(function () { var image = document.querySelector('img[src*=\"p=captcha\"]'); return image && image.complete && image.naturalWidth > 0; })()");
        $image = $session->getPage()->find('css', 'img[src*="p=captcha"]');
        $input = $session->getPage()->findField('captcha');
        if (!$loaded || !$image || !$image->isVisible() || !$input || !$input->isVisible() || $input->hasAttribute('disabled'))
        {
            throw new RuntimeException('The returning-candidate CAPTCHA must be visible and enabled.');
        }
        $form = $session->getPage()->find('css', 'form#register');
        if (!$form || !str_contains($form->getAttribute('action'), 'p=candidateLogin'))
        {
            throw new RuntimeException('The returning-candidate form must POST to candidateLogin.');
        }
    }

    private function readReturningCareerPortalSession()
    {
        $sessionID = $this->getSession()->getCookie(CATS_SESSION_NAME);
        if (!$sessionID)
        {
            throw new RuntimeException('The browser has no Career Portal PHP session.');
        }
        // A separate CLI process avoids replacing Behat's own $_SESSION['CATS'].
        $command = escapeshellarg(PHP_BINARY) . ' '
            . escapeshellarg(LEGACY_ROOT . '/test/scripts/readCareerPortalTestSession.php') . ' '
            . escapeshellarg($sessionID);
        exec($command, $output, $status);
        if ($status !== 0)
        {
            throw new RuntimeException('Unable to read the browser session from the shared CI session store.');
        }
        return json_decode(implode("\n", $output), true, 512, JSON_THROW_ON_ERROR);
    }

    /** @When I correctly complete the Career Portal CAPTCHA */
    public function completeReturningCareerPortalCaptcha()
    {
        $this->assertReturningCareerPortalCaptcha();
        $state = $this->readReturningCareerPortalSession();
        if (empty($state['captcha']))
        {
            throw new RuntimeException('The real CAPTCHA endpoint has not generated a challenge.');
        }
        $this->fillField('captcha', $state['captcha']);
    }

    /** @Then the returning candidate is authenticated without a remembered-candidate cookie */
    public function assertReturningCareerPortalSession()
    {
        $state = $this->readReturningCareerPortalSession();
        if ($state['candidateID'] !== $this->returningCandidateID)
        {
            throw new RuntimeException('The browser session does not identify the returning candidate.');
        }
        if ($this->getSession()->getCookie(sprintf('cats%dcw', CATS_INSTALLATION_SITE)) !== null)
        {
            throw new RuntimeException('A remembered-candidate authentication cookie was created.');
        }
    }

    /** @Then the application belongs to the returning candidate */
    public function assertReturningCareerPortalApplication()
    {
        $db = DatabaseConnection::getInstance();
        $row = $db->getAssoc('SELECT candidate_id FROM candidate_joborder WHERE candidate_id = '
            . $db->makeQueryInteger($this->returningCandidateID));
        if (empty($row))
        {
            throw new RuntimeException('The application was not linked to the returning candidate.');
        }
    }

    /** @Then the returning candidate is logged out */
    public function assertReturningCareerPortalLogout()
    {
        if ($this->readReturningCareerPortalSession()['candidateID'] !== null)
        {
            throw new RuntimeException('Candidate authentication survived logout.');
        }
        $this->visitPath('/index.php?m=careers&p=registeredCandidateProfile');
        $this->assertPageContainsText('You have not registered yet.');
    }
}
