<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Mink\Driver\Selenium2Driver;
use OpenCATS\Entity\Company;
use OpenCATS\Entity\CompanyRepository;
use OpenCATS\Entity\JobOrder;
use OpenCATS\Entity\JobOrderRepository;
use Behat\Mink\Exception\ElementHtmlException;

include_once('./config.php');
include_once(LEGACY_ROOT . '/constants.php');
include_once(LEGACY_ROOT . '/lib/DatabaseConnection.php');
include_once(LEGACY_ROOT . '/lib/History.php');
include_once(LEGACY_ROOT . '/lib/Search.php');
include_once(LEGACY_ROOT . '/lib/Users.php');
include_once(LEGACY_ROOT . '/lib/CareerPortal.php');
include_once(LEGACY_ROOT . '/lib/Questionnaire.php');

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context, SnippetAcceptingContext
{
    use ActivitiesSteps;
    use ReportsSteps;
    protected $scenarioTitle = null;
    private $roleData;

    /**
     * Keep Calendar workflow fixtures isolated, including after a failed scenario.
     * @BeforeScenario @calendar
     * @AfterScenario @calendar
     */
    public function cleanCalendarWorkflowEvents()
    {
        $users = new Users();
        $adminID = (int) $users->getIDByUsername('admin');
        DatabaseConnection::getInstance()->query(
            "DELETE FROM calendar_event WHERE entered_by = " . $adminID
            . " AND title IN ('Calendar workflow event', 'Calendar workflow updated')"
            . " AND date >= '2030-06-01' AND date < '2030-07-01'"
        );
    }

    private $calendarSiteFormats = null;
    private $calendarQueueTime = null;

    private $calendarFixtureRecords = array();
    private $calendarFixtureEvents = array();

    private $calendarAjaxSettings = null;

    /** @Given Calendar AJAX is :enabled */
    public function calendarAjaxSetting($enabled)
    {
        $db = DatabaseConnection::getInstance();
        $this->calendarAjaxSettings = $db->getAllAssoc(
            "SELECT value FROM settings WHERE settings_type = " . SETTINGS_CALENDAR . " AND setting = 'noAjax'"
        );
        $db->query("DELETE FROM settings WHERE settings_type = " . SETTINGS_CALENDAR . " AND setting = 'noAjax'");
        $db->query("INSERT INTO settings (settings_type, setting, value) VALUES (" . SETTINGS_CALENDAR . ", 'noAjax', '"
            . ($enabled === 'enabled' ? '0' : '1') . "')");
    }

    /** @Given Calendar has another user's private event */
    public function calendarPrivateEvent()
    {
        $db = DatabaseConnection::getInstance();
        $ownerID = (int) (new Users())->getIDByUsername('testerRead');
        $db->query("INSERT INTO calendar_event (type, date, title, entered_by, public) VALUES"
            . " (300, '2030-06-12 10:00:00', 'Calendar private fixture', " . $ownerID . ", 0)");
        $this->calendarFixtureEvents[] = (int) $db->getLastInsertID();
    }

    /** @Given Calendar has grouped events linked to a :kind */
    public function calendarLinkedEvents($kind)
    {
        $db = DatabaseConnection::getInstance();
        $adminID = (int) (new Users())->getIDByUsername('admin');
        $db->query("INSERT INTO company (name) VALUES ('Calendar workflow company')");
        $companyID = (int) $db->getLastInsertID();
        $this->calendarFixtureRecords['company'] = $companyID;
        $types = array(
            'company' => DATA_ITEM_COMPANY,
            'candidate' => DATA_ITEM_CANDIDATE,
            'contact' => DATA_ITEM_CONTACT,
            'joborder' => DATA_ITEM_JOBORDER
        );
        if (!isset($types[$kind]))
        {
            throw new \InvalidArgumentException('Unsupported Calendar association');
        }
        if ($kind === 'candidate')
        {
            $db->query("INSERT INTO candidate (first_name, last_name) VALUES ('Calendar', 'workflow candidate')");
        }
        elseif ($kind === 'contact')
        {
            $db->query("INSERT INTO contact (company_id, company_department_id, first_name, last_name) VALUES ("
                . $companyID . ", 0, 'Calendar', 'workflow contact')");
        }
        elseif ($kind === 'joborder')
        {
            $db->query("INSERT INTO joborder (company_id, title) VALUES (" . $companyID . ", 'Calendar workflow joborder')");
        }
        $recordID = $kind === 'company' ? $companyID : (int) $db->getLastInsertID();
        $this->calendarFixtureRecords[$kind] = $recordID;
        for ($i = 0; $i < 2; ++$i)
        {
            $db->query(sprintf(
                "INSERT INTO calendar_event (type, date, title, description, entered_by, data_item_id, data_item_type, public)"
                . " VALUES (300, '2030-06-12 09:15:00', 'Calendar workflow event', 'Linked calendar details', %d, %d, %d, 1)",
                $adminID, $recordID, $types[$kind]
            ));
            $this->calendarFixtureEvents[] = (int) $db->getLastInsertID();
        }
    }

    /** @Then the Calendar event association should still link to :kind */
    public function calendarAssociationPersists($kind)
    {
        $recordID = $this->calendarFixtureRecords[$kind];
        $link = $this->getSession()->getPage()->find('css', '#viewEventLink a');
        $parameters = array('company' => 'companyID', 'candidate' => 'candidateID',
            'contact' => 'contactID', 'joborder' => 'jobOrderID');
        if (!$link || strpos($link->getAttribute('href'), $parameters[$kind] . '=' . $recordID) === false)
        {
            throw new \RuntimeException('Calendar association was lost or changed');
        }
    }

    /** @Given Calendar uses :dateFormat dates and :timeFormat hour time */
    public function calendarUsesFormats($dateFormat, $timeFormat)
    {
        $db = DatabaseConnection::getInstance();
        $this->calendarSiteFormats = $db->getAllAssoc(
            'SELECT site_id, date_format_ddmmyy, time_format_24 FROM site'
        );
        $db->query('UPDATE site SET date_format_ddmmyy = ' . ($dateFormat === 'DMY' ? 1 : 0)
            . ', time_format_24 = ' . ($timeFormat === '24' ? 1 : 0));
    }

    /** @Given Calendar reminder controls are available */
    public function calendarRemindersAvailable()
    {
        $file = LEGACY_ROOT . '/queue.time';
        $this->calendarQueueTime = file_exists($file) ? filemtime($file) : false;
        touch($file);
    }

    /** @AfterScenario @calendar */
    public function restoreCalendarEnvironment()
    {
        $db = DatabaseConnection::getInstance();
        foreach ($this->calendarFixtureEvents as $eventID)
        {
            $db->query('DELETE FROM calendar_event WHERE calendar_event_id = ' . $eventID);
        }
        foreach (array_reverse($this->calendarFixtureRecords, true) as $table => $id)
        {
            $db->query('DELETE FROM ' . $table . ' WHERE ' . $table . '_id = ' . $id);
        }
        if ($this->calendarAjaxSettings !== null)
        {
            $db->query("DELETE FROM settings WHERE settings_type = " . SETTINGS_CALENDAR . " AND setting = 'noAjax'");
            foreach ($this->calendarAjaxSettings as $setting)
            {
                $db->query("INSERT INTO settings (settings_type, setting, value) VALUES (" . SETTINGS_CALENDAR . ", 'noAjax', "
                    . $db->makeQueryString($setting['value']) . ")");
            }
        }
        if ($this->calendarSiteFormats !== null)
        {
            foreach ($this->calendarSiteFormats as $site)
            {
                DatabaseConnection::getInstance()->query(sprintf(
                    'UPDATE site SET date_format_ddmmyy = %d, time_format_24 = %d WHERE site_id = %d',
                    $site['date_format_ddmmyy'], $site['time_format_24'], $site['site_id']
                ));
            }
        }
        if ($this->calendarQueueTime !== null)
        {
            $file = LEGACY_ROOT . '/queue.time';
            if ($this->calendarQueueTime === false)
            {
                if (file_exists($file))
                {
                    unlink($file);
                }
            }
            else
            {
                touch($file, $this->calendarQueueTime);
            }
        }
    }

    /** @Then Calendar field :id should be :state */
    public function calendarFieldState($id, $state)
    {
        $field = $this->getSession()->getPage()->findById($id);
        if (!$field || $field->hasAttribute('disabled') !== ($state === 'disabled'))
        {
            throw new \RuntimeException('Unexpected enabled state for Calendar field ' . $id);
        }
    }

    /** @Then Calendar panel :id should be :state */
    public function calendarPanelState($id, $state)
    {
        $this->spins(function () use ($id, $state) {
            $panel = $this->getSession()->getPage()->findById($id);
            if (!$panel || $panel->isVisible() !== ($state === 'visible'))
            {
                throw new \RuntimeException('Unexpected visibility for Calendar panel ' . $id);
            }
        });
    }

    /** @Then Calendar view :id should have :count events */
    public function calendarEventCount($id, $count)
    {
        $this->spins(function () use ($id, $count) {
            $events = $this->getSession()->getPage()->findAll('css', '#' . $id . ' .calendarEntry');
            if (count($events) !== (int) $count)
            {
                throw new \RuntimeException('Unexpected Calendar event count in ' . $id);
            }
        });
    }

    /** @Then the Calendar heading should be :heading */
    public function calendarHeading($heading)
    {
        $actual = $this->getSession()->getPage()->findById('calendarTitle')->getText();
        if (preg_replace('/\\s+/', ' ', trim($actual)) !== $heading)
        {
            throw new \RuntimeException('Unexpected Calendar heading: ' . $actual);
        }
    }



    /** @When I cancel the Calendar dialog */
    public function cancelCalendarDialog()
    {
        $this->getSession()->getDriver()->getWebDriverSession()->dismiss_alert();
    }

    /**
     * Initializes context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
        $this->roleData = array(
            'Administrator' => new Role('admin', 'opencats-test-admin'),
                                'User' => new Role('john@mycompany.net', 'john99')
        );
    }

    /**
     * @Given I am authenticated as :role
     */
    public function iAmAuthenticatedAs($role)
    {
        $roleData = empty($this->roleData[$role]) ? null : $this->roleData[$role];

        if (!$roleData)
        {
            throw new PendingException();
        }

        if ($role === 'Administrator')
        {
            $this->setAdministratorPassword($roleData->getPassword());
        }

        $this->iLoginAs($roleData->getUserName(), $roleData->getPassword());
    }

    /**
     * @Given the administrator is using the default password
     */
    public function theAdministratorIsUsingTheDefaultPassword()
    {
        $this->setAdministratorPassword(DEFAULT_ADMIN_PASSWORD);
    }

    /**
     * Set the administrator password for test setup.
     */
    private function setAdministratorPassword($password)
    {
        $users = new Users();
        $userID = $users->getIDByUsername('admin');

        if ($userID === false)
        {
            throw new \RuntimeException('Administrator user does not exist.');
        }

        if (!$users->resetPassword($userID, $password))
        {
            throw new \RuntimeException('Unable to reset administrator password.');
        }
    }
    /**
     * @Given There is a person called :fullName with :property
     */
    public function thereIsAPersonCalledWith($fullName, $property)
    {
        $this->visitPath('/index.php?m=candidates&a=add');
        list($firstName, $lastName) = explode(" ", $fullName);
        $this->fillField('firstName', $firstName);
        $this->fillField('lastName', $lastName);
        list($key, $value) = explode("=", $property);
        $this->fillField($key, $value);
        $this->pressButton('Add Candidate');
    }

    /**
     * @Given I am spoofing a session with :cookieValue cookie
     */
    public function iAmSpoofingASessionWithCookie($cookieValue)
    {
        $this->getSession()->setCookie(CATS_SESSION_NAME, $cookieValue);
    }

    /**
     * @Given I wait for :element
     */
    public function iWaitFor($element)
    {
        $this->spins(function() use ($element) {
            if ($element === '#CompanyResults div#suggest0') {
                $this->activateCompanySuggestionLookup();
            }
            $field = $this->getSession()->getPage()->find('css', $element);
            if (null === $field) {
                throw new Exception('form field ' . $element . 'id|name|label|value|placeholder');
            }
        });
    }

    /**
     * @Then I wait until I see :text
     */
    public function iWaitUntilISee($text)
    {
        $this->spins(function() use ($text) {
            $pageText = $this->getSession()->getPage()->getText();
            if (strpos($pageText, $text) === false) {
                throw new Exception(sprintf('Could not see text "%s" yet.', $text));
            }
        });
    }

    /**
     * @Then /^I wait for the activity note box to appear$/
     */
    public function iWaitForTheSuggestionBoxToAppear()
    {
        $this->getSession()->wait(5000, "$('iframe', parent.document).length > 0");
    }

    public function spins($closure, $tries = 10)
    {
        for ($i = 0; $i <= $tries; $i++) {
            try {
                $closure();

                return;
            } catch (\Exception $e) {
                if ($i == $tries) {
                    throw $e;
                }
            }

            sleep(1);
        }
    }

    /**
     * @BeforeScenario
     */
    public function cacheScenarioName($event)
    {
        // it's only to have a clean screenshot name later
        $this->scenarioTitle = $event->getScenario()->getTitle();
    }

    /**
     * @AfterStep
     */
    public function takeScreenshotAfterFailedStep($event)
    {
        if ($event->getTestResult()->getResultCode() !== TestResult::FAILED) {
            return;
        }

        $this->takeAScreenshot();
    }

    /**
     * @Then take a screenshot
     */
    public function takeAScreenshot()
    {
        if (!$this->isJavascript()) {
            print "Screenshot cannot be taken from non javascript scenario.\n";

            return;
        }

        $screenshot = $this->getSession()->getDriver()->getScreenshot();

        $filename = $this->getScreenshotFilename();
        file_put_contents($filename, $screenshot);

        print sprintf("Screenshot is available :\n%s\n", $filename);
    }

    protected function getScreenshotFilename()
    {
        $filename = $this->scenarioTitle;
        $filename = preg_replace("#[^a-zA-Z0-9\._-]#", '_', $filename);

        $directory = __DIR__ . '/../../screenshots';
        if (!is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return sprintf('%s/%s.png', $directory, $filename);
    }

    protected function isJavascript()
    {
        return $this->getSession()->getDriver() instanceof Selenium2Driver;
    }

    /**
     * @Given /^I switch to the iframe "([^"]*)"$/
     */
    public function iSwitchToIframe($iFrameId)
    {
        if (empty($iFrameId)) {
            $this->getSession()->switchToIframe(null);
        } else {
            $this->getSession()->wait(5000, "$('iframe', parent.document).length > 0");
            $check = 1; //@todo need to check using js if exists
            if($check <= 0) {
                throw new \Exception('Element not found');
            } else {
                $javascript = "
                (function(){
                var elem = document.getElementById('$iFrameId');
                var iframes = elem.getElementsByTagName('iframe');
                var f = iframes[0];
                f.id = \"no_name_iframe\";
            })()";
            $this->getSession()->executeScript($javascript);
            }
            $this->getSession()->switchToIframe("no_name_iframe");
        }
    }

    /**
     * @Given There is a company called :companyName
     */
    public function thereIsACompanyCalled($companyName)
    {
        $company = new Company($companyName);
        $CompanyRepository = new CompanyRepository(DatabaseConnection::getInstance());
        $CompanyRepository->persist($company, new Dummy_History());
    }

    /**
     * @Given There is a user :userName named :fullName with :password password
     */
    public function thereIsAUserWithParams($userName, $fullName, $password) {
        list($firstName, $lastName) = explode(" ", $fullName);
        $users = new Users();
        $users->add(
            $lastName,
            $firstName,
            '',
            $userName,
            $password,
            ACCESS_LEVEL_DELETE
        );
    }

    /**
     * @When /^(?:|I )should see "([^"]*)" in alert popup$/
     *
     * @param string $message The message.
     *
     * @return bool
     */
    public function assertPopupMessage($message)
    {
        return strpos(
            $this->getSession()->getDriver()->getWebDriverSession()->getAlert_text(),
                      $message
        ) != -1;
    }

    /**
     * @When /^(?:|I )confirm the popup$/
     */
    public function confirmPopup()
    {
        $this->getSession()->getDriver()->getWebDriverSession()->accept_alert();
    }

    /**
     * @Given I manually press :key
     */
    public function manuallyPress($key)
    {
        $script = "jQuery.event.trigger({ type : 'keypress', which : '" . $key . "' });";
        $this->getSession()->evaluateScript($script);
    }

    /**
     * @Given I set hidden field :field to :value
     */
    public function iSetHiddenFieldTo($field, $value)
    {
        $page = $this->getSession()->getPage();
        $inputField = $page->find('css', sprintf('input[type="hidden"][name="%s"]', $field));

        if (null === $inputField)
        {
            $inputField = $page->find('css', sprintf('input[type="hidden"]#%s', $field));
        }

        if (null === $inputField)
        {
            throw new \InvalidArgumentException(sprintf('Could not find hidden field: "%s"', $field));
        }

        $script = sprintf(
            '(function(){ var f = document.getElementsByName(%s)[0] || document.getElementById(%s); if (f) { f.value = %s; } })();',
                          json_encode($field),
                          json_encode($field),
                          json_encode($value)
        );

        $this->getSession()->executeScript($script);
    }

    /** Click on the element with the provided xpath query
     *
     * @When I click on the element :locator
     */
    public function iClickOnTheElement($locator)
    {
        $this->clickOnTheElement($locator);
    }

    private function clickOnTheElement($locator, $retries = 15)
    {
        $element = $this->getSession()->getPage()->find('css', $locator); // runs the actual query and returns the element
        if (null === $element && $locator === '#CompanyResults div#suggest0') {
            $this->activateCompanySuggestionLookup();
            sleep(1);
            $element = $this->getSession()->getPage()->find('css', $locator);
        }

        if (null === $element) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', $locator));
        }
        try {
            $element->click();
        } catch(Exception $e) {
            if ($retries > 0) {
                print_r("Retry stale element. Retries: " . $retries);
                sleep(1);
                $this->clickOnTheElement($locator, $retries -1);
            } else {
                print_r("Do not retry stale element. Retries: " . $retries);
                throw $e;
            }
        }
    }

    private function activateCompanySuggestionLookup()
    {
        $script = <<<'JS'
        (function () {
        var field = document.getElementById('companyName');
        if (!field) {
            return false;
    }

    field.focus();

    if (typeof suggestListActivate === 'function' && typeof sessionCookie !== 'undefined') {
        suggestListActivate(
            'getCompanyNames',
            'companyName',
            'CompanyResults',
            'companyID',
            'ajaxTextEntryHover',
            0,
            sessionCookie,
            'helpShim'
            );
    }

    if (typeof Event === 'function') {
        field.dispatchEvent(new Event('input', { bubbles: true }));
        field.dispatchEvent(new Event('keyup', { bubbles: true }));
    } else {
        var inputEvent = document.createEvent('Event');
        inputEvent.initEvent('input', true, true);
        field.dispatchEvent(inputEvent);

        var keyupEvent = document.createEvent('Event');
        keyupEvent.initEvent('keyup', true, true);
        field.dispatchEvent(keyupEvent);
    }

    if (typeof suggestListPopulate === 'function' && typeof sessionCookie !== 'undefined') {
        suggestListPopulate(0, sessionCookie, field.value, maxInitialResults, -1);
    }

    return true;
    }());
    JS;

    $this->getSession()->executeScript($script);
    }

    /**
     * @When I select :option in the :selectLocator select
     */
    public function selectState($option, $selectLocator) {
        $page = $this->getSession()->getPage();
        $selectElement = $page->find('css', $selectLocator);
        if (null === $selectElement) {
            throw new \InvalidArgumentException(sprintf('Could not evaluate CSS selector: "%s"', $selectLocator));
        }
        $selectElement->selectOption($option);
        sleep(1);
    }

    /**
     * @Given There is a job order for a :jobTitle for :companyName
     */
    public function thereIsAJobOrderForAFor($jobTitle, $companyName)
    {
        $CompanyRepository = new CompanyRepository(DatabaseConnection::getInstance());
        $companies = $CompanyRepository->findByName($companyName);
        $companyId = $companies[0]['companyID'];
        $jobOrder = JobOrder::create(
            $jobTitle,
            $companyId,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            ''
        );
        $JobOrderRepository = new JobOrderRepository(DatabaseConnection::getInstance());
        $JobOrderRepository->persist($jobOrder, new Dummy_History());
    }

    /**
     * @Given There is a public career portal job :jobTitle with questionnaire :questionnaireTitle
     */
    public function thereIsAPublicCareerPortalJobWithQuestionnaire(
        $jobTitle,
        $questionnaireTitle
    )
    {
        /*
         * The standard test database already contains the CATS 2.0 Career
         * Portal template. Enable the portal for this scenario.
         */
        $careerPortalSettings = new CareerPortalSettings();
        $careerPortalSettings->set('enabled', '1');

        /*
         * CAPTCHA is unrelated to this regression test. Override the standard
         * CATS 2.0 application template for this scenario without requiring it.
         */
        $template = $careerPortalSettings->getTemplate('CATS 2.0');

        $careerPortalSettings->setForTemplate(
            'Content - Apply for Position',
            str_replace(
                '<input-captcha req>',
                '',
                $template['Content - Apply for Position']
            ),
            'CATS 2.0'
        );

        /*
         * Create a small questionnaire with two optional checkbox questions.
         * The feature answers only the first question, reproducing the partial
         * questionnaire submission that exposed issue #849.
         */
        $questionnaire = new Questionnaire();
        $questionnaireID = $questionnaire->add(
            $questionnaireTitle,
            $questionnaireTitle,
            true
        );

        $questionnaire->addQuestions(
            $questionnaireID,
            array(
                array(
                    'questionText' => 'First test question',
                    'questionPosition' => 1,
                    'questionType' => QUESTIONNAIRE_QUESTION_TYPE_CHECKBOX,
                    'answers' => array(
                        array(
                            'answerText' => 'First test answer',
                            'answerPosition' => 1
                        )
                    )
                ),
                array(
                    'questionText' => 'Second test question',
                    'questionPosition' => 2,
                    'questionType' => QUESTIONNAIRE_QUESTION_TYPE_CHECKBOX,
                    'answers' => array(
                        array(
                            'answerText' => 'Second test answer',
                            'answerPosition' => 1
                        )
                    )
                )
            )
        );

        /*
         * Reuse the existing company fixture helper and the same
         * JobOrder / JobOrderRepository pattern as the existing job-order
         * Behat fixture.
         */
        $this->thereIsACompanyCalled('Career Portal Test Company');

        $CompanyRepository = new CompanyRepository(DatabaseConnection::getInstance());
        $companies = $CompanyRepository->findByName('Career Portal Test Company');
        $companyId = $companies[0]['companyID'];

        $jobOrder = JobOrder::create(
            $jobTitle,
            $companyId,
            '',
            '<strong>Career Portal formatted description</strong>',
            '',
            '',
            '',
            '',
            '',
            true,
            1,
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            $questionnaireID
        );

        $JobOrderRepository = new JobOrderRepository(DatabaseConnection::getInstance());
        $JobOrderRepository->persist($jobOrder, new Dummy_History());
    }

    /**
     * @Given I login as :username :password
     */
    public function iLoginAs($username, $password)
    {
        $this->visitPath('/index.php?m=login');
        $this->fillField('username', $username);
        $this->fillField('password', $password);
        $this->pressButton('Login');

    }

    /**
     * Looks for a table, then looks for a row that contains the given text.
     * Once it finds the right row, it clicks a link in that row.
     *
     * Really handy when you have a generic "Edit" link on each row of
     * a table, and you want to click a specific one (e.g. the "Edit" link
     * in the row that contains "Item #2")
     *
     * @When I click on :linkName on the row containing :rowText
     */
    public function iClickOnOnTheRowContaining($linkName, $rowText)
    {
        /** @var $row \Behat\Mink\Element\NodeElement */
        $row = $this->getSession()->getPage()->find('css', sprintf('table tr:contains("%s")', $rowText));
        if (!$row) {
            throw new \Exception(sprintf('Cannot find any row on the page containing the text "%s"', $rowText));
        }
        $row->clickLink($linkName);
    }

    /**
     * @override: @Then /^the "(?P<element>[^"]*)" element should contain "(?P<value>(?:[^"]|\\")*)"$/
     */
    public function assertElementContains($selector, $value)
    {
        $selectorType = 'css';
        $html = $this->fixStepArgument($value);
        $element = $this->assertSession()->elementExists($selectorType, $selector);
        $actual = $element->getOuterHtml();
        $regex = '/'.preg_quote($html, '/').'/umi';

        $message = sprintf(
            'The regex "%s" does not matches HTML %s.',
            $regex,
            $actual
        );

        if (!preg_match($regex, $actual)) {
            throw new ElementHtmlException($message, $this->getSession()->getDriver(), $element);
        }
    }
}

class Role
{
    private $userName;
    private $password;

    function __construct($userName, $password)
    {
        $this->userName = $userName;
        $this->password = $password;
    }

    function getUserName()
    {
        return $this->userName;
    }

    function getPassword()
    {
        return $this->password;
    }
}

// FIXME: Should abstract session from history
class Dummy_History extends History
{
    public function __construct() {}
    public function storeHistoryNew($dataItemType, $dataItemID) {}
}
