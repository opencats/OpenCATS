<?php
namespace AppBundle;
use AppBundle\Helper\Acceptance;
use Codeception\Exception\Fail;
use \Codeception\Scenario;
use AppBundle\Entity\User;
use AppBundle\Entity\DummyHistory;
use OpenCATS\Entity\Company;
use OpenCATS\Entity\CompanyRepository;
use OpenCATS\Entity\JobOrder;
use OpenCATS\Entity\JobOrderRepository;


/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method \Codeception\Lib\Friend haveFriend($name, $actorClass = NULL)
 *
 * @SuppressWarnings(PHPMD)
*/
class AcceptanceTester extends \Codeception\Actor
{
    use _generated\AcceptanceTesterActions;
    private $roleData;

    public function __construct(Scenario $scenario)
    {
        parent::__construct($scenario);
        $this->roleData = array(
            'Administrator' => new User('admin', 'admin'),
            'User' => new User('john@mycompany.net', 'john99')
        );
    }
    /**
    * Define custom actions here
    */
    /**
     * @Given I am authenticated as :role
     */
    public function iAmAuthenticatedAs($role)
    {
        $roleData = empty($this->roleData[$role]) ? null : $this->roleData[$role];
        if (!$roleData) {
            throw new \Codeception\Exception\Fail('The role ' . $role . ' does not exists');
        }
        $this->iLoginAs($roleData->getUserName(), $roleData->getPassword());
    }

    /**
     * @Given I login as :username :password
     */
    public function iLoginAs($username, $password)
    {
        $this->amOnPage('/index.php?m=login');
        $this->fillField('username', $username);
        $this->fillField('password', $password);
        $this->click('Login');
    }

    /**
     * @Given There is a person called :fullName with :property
     */
    public function thereIsAPersonCalledWith($fullName, $property)
    {
        $this->amOnPage('/index.php?m=candidates&a=add');
        list($firstName, $lastName) = explode(" ", $fullName);
        $this->fillField('firstName', $firstName);
        $this->fillField('lastName', $lastName);
        list($key, $value) = explode("=", $property);
        $this->fillField($key, $value);
        $this->click('Add Candidate', "#contents");
    }

    /**
     * @Given I am on :url
     */
    public function iAmOn($url)
    {
        $this->amOnPage($url);
    }

    /**
     * @Given I follow :link
     */
    public function iFollow($link)
    {
        $this->click($link);
    }

    /**
    * @When follow :link
    */
    public function follow($link)
    {
        $this->click($link);
    }

    /**
     * @Given I fill in :field with :value
     */
    public function iFillInWith($field, $value)
    {
        $this->fillField($field, $value);
    }

    /**
     * @Given I wait for the activity note box to appear
     */
    public function iWaitForTheActivityNoteBoxToAppear()
    {
        $this->waitForElementVisible('#popupContainer');
    }

    /**
     * @Given I switch to the iframe :iFrameId
     */
    public function iSwitchToTheIframe($iFrameId)
    {
        if (empty($iFrameId)) {
            echo "1";
            $this->switchToIFrame();
        } else {
            echo "2";
            $this->switchToIFrame($iFrameId);
        }
    }

    /**
     * @Given I switch to the window :iFrameId
     */
    public function iSwitchToTheWindow($windowId)
    {
        if (empty($windowId)) {
            $this->switchToWindow();
        } else {
            $this->switchToWindow($windowId);
        }
    }

    /**
     * @Given fill in :field with :value
     */
    public function thenFillInWith($field, $value)
    {
        $this->fillField($field, $value);
    }


    /**
     * @Given press :button
     */
    public function press($button)
    {
        // FIXME: HACK to simulate Create Job Order button
        if ('Create Job Order' == $button) {
            $this->pressButtonInIframe($button, 'popupFrameIFrame');
            #$this->waitForJS('document.querySelectorAll(\'iframe[name="popupFrameIFrame"]\')[0].contentDocument.querySelectorAll(\'input[value="Create Job Order"]\').length > 0;', 10);
            #$this->executeJS('document.querySelectorAll(\'iframe[name="popupFrameIFrame"]\')[0].contentDocument.querySelectorAll(\'input[value="Create Job Order"]\')[0].onclick()');
        } else  {
            $this->click($button);
        }
    }

    /**
     * @When I press :button
     */
    public function whenPress($button)
    {
        $this->press($button);
    }

    /**
     * @Then I should see :text
     */
    public function iShouldSee($text)
    {
        $this->see($text);
    }

    /**
     * @When I select :option from :select
     */
    public function iSelectFrom($option, $select)
    {
        $this->selectOption($select, $option);
    }

    /**
     * @Given I select :option in the :select
     */
    public function iSelectInThe($option, $select)
    {
        $this->selectOption($select, $option);
    }

    /**
     * @Then I should not see :text
     */
    public function iShouldNotSee($text)
    {
        $this->cantSee($text);
    }

    /**
     * @Then the :element element should contain :attribute
     */
    public function theElementShouldContain($element, $attribute)
    {
        $this->grabAttributeFrom($element, $attribute);
    }

    /**
     * @Given There is a company called :companyName
     */
    public function thereIsACompanyCalled($companyName)
    {
        try {
            $siteId = $this->getSiteId();
            $company= new Company(
                $siteId,
                $companyName
            );
            $CompanyRepository = new CompanyRepository(\DatabaseConnection::getInstance());
            $CompanyRepository->persist($company, new DummyHistory($siteId));
        } catch (\Exception $e) {
            print_r($e->getMessage());
            print_r($e->getTraceAsString());
        }
    }

    private function getSiteId()
    {
        $site = new \Site(-1);
        return $site->getFirstSiteID();
    }

    /**
     * @Given There is a user :userName named :fullName with :password password
     */
    public function thereIsAUserNamedWithPassword($userName, $fullName, $password)
    {
        list($firstName, $lastName) = explode(" ", $fullName);
        $siteId = $this->getSiteId();
        $users = new \Users($siteId);
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
     * @Then I should see :message in alert popup
     */
    public function iShouldSeeInAlertPopup($message)
    {
        $this->seeInPopup($message);
    }

    /**
     * @Given I wait :timeInSeconds seconds
     */
    public function iWait($timeInSeconds)
    {
        $this->wait($timeInSeconds);
    }

    /**
     * @Given I am spoofing a session with :cookieValue cookie
     */
    public function iAmSpoofingASessionWithCookie($cookieValue)
    {
        $this->spoofSessionWithCookie(CATS_SESSION_NAME, $cookieValue);
    }


    /**
     * @Then I confirm the popup
     */
    public function iConfirmThePopup()
    {
        $this->acceptPopup();
    }

    /**
     * @Then I cancel the popup
     */
    public function iCancelThePopup()
    {
        $this->cancelPopup();
    }

    /**
     * @Given I wait for :element
     */
    public function iWaitFor($element)
    {
        $this->waitForElement($element);
    }

    /**
     * @Given I click on the element :element
     */
    public function iClickOnTheElement($element)
    {
        $this->click($element);
    }

    /**
     * @Given There is a job order for a :jobTitle for :companyName
     */
    public function thereIsAJobOrderForAFor($jobTitle, $companyName)
    {
        $siteId = $this->getSiteId();
        $CompanyRepository = new CompanyRepository(\DatabaseConnection::getInstance());
        $companies = $CompanyRepository->findByName($siteId, $companyName);
        $companyId = $companies[0]['companyID'];
        $jobOrder = JobOrder::create(
            $siteId,
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
            ''
        );
        $JobOrderRepository = new JobOrderRepository(\DatabaseConnection::getInstance());
        $JobOrderRepository->persist($jobOrder, new DummyHistory($siteId));

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
        return $this->clickOnOnTheRowContaining($linkName, $rowText);
    }


    // From Security context .php
    /**
     * @Given I am logged in with :accessLevel access level
     */
    public function iAmLoggedInWithAccessLevel($accessLevel)
    {
        $this->accessLevel = $accessLevel;
        switch($accessLevel) {
            case 'DISABLED':
                $username = "testerDisabled";
                $password = "tester";
                break;
            case 'READONLY':
                $username = "testerRead";
                $password = "tester";
                break;
            case 'EDIT':
                $username = "testerEdit";
                $password = "tester";
                break;
            case 'DELETE':
                $username = "testerDelete";
                $password = "tester";
                break;
            case 'DEMO':
                $username = "testerDemo";
                $password = "tester";
                break;
            case 'ADMIN':
                $username = "testerSA";
                $password = "tester";
                break;
            case 'MULTI_ADMIN':
                $username = "testerMultiSA";
                $password = "tester";
                break;
            case 'ROOT':
                $username = "testerRoot";
                $password = "tester";
                break;
            default:
                throw new PendingException();
        }
        $this->amOnPage('/index.php?m=login&a=logout');
        $this->amOnPage('/index.php?m=login');
        $this->fillField('username', $username);
        $this->fillField('password', $password);
        $this->press('Login');
    }

    /**
     * @When I do :type request on url :url
     */
    public function iDoGETRequestOnUrl($type, $url)
    {
        switch($type){
            case "GET":
                $this->iDoGETRequest($url);
                break;
            case "POST":
                $this->iDoPOSTRequestOnUrl($url);
                break;
            default:
                throw new PendingException();

        }
    }

    /**
     * @Then I should not have permission
     */
    public function iShouldNotHavePermission()
    {
        try {
            if($this->accessLevel == "DISABLED")
            {
                $this->theResponseShouldContain("opencats - Login");
                return;
            }
            $expectedTexts = array("You don't have permission", "Invalid user level for action", "You are not allowed to change your password.");
            $response = $this->getVisibleText();

            foreach ($expectedTexts as &$text)
            {
                $position = strpos($response, $text);
                if($position !== false)
                {
                    return;
                }
            }
            throw new ExpectationException("'".$expectedTexts[0]."' was not found in the response from this request and it should be", $this->getSession());

        } catch (\Exception $e) {
            print_r($e->getMessage());
        }
    }

    /**
     * @When I do POST request on url :url
     */
    public function iDoPOSTRequestOnUrl($url)
    {
        try {
            $this->amOnPage($url);
            $this->submitForm('form', array('postback' => 'postback'));
        } catch (\Exception $e) {
            print_r($e->getMessage());
        }

    }

    /**
     * @When I do GET request :url
     */
    public function iDoGETRequest($url)
    {
        $this->amOnPage($url);
    }


    /**
     * @Then I should  have permission
     */
    public function iShouldHavePermission()
    {
        $this->theResponseShouldNotContain("You don't have permission");
        $this->theResponseShouldNotContain("Invalid user level for action");
        $this->theResponseShouldNotContain("opencats - Login");
    }


    /**
     * @When I follow link :name
     */
    public function iFollowLink($name)
    {
        $this->click($name);
    }

    /**
     * @Then the response should  contain :text
     */
    public function theResponseShouldContain($text)
    {
        $position = strpos($this->getVisibleText(), $text);
        if($position === false)
        {
            throw new Fail("'". $this->getVisibleText() ."' was not found in the response from this request and it should be");
        }
    }

    /**
     * @Then the response should not contain :text
     */
    public function theResponseShouldNotContain($text)
    {
        $position = strpos($this->getVisibleText(), $text);
        if($position !== false)
        {
            throw new Fail("'".$text."' was found in the response from this request and it should be not");
        }
    }

    /**
     * @Then the page should  contain :text
     */
    public function thePageShouldContain($text)
    {
        $this->theResponseShouldContain($text);
    }

    /**
     * @Then the page should not contain :text
     */
    public function thePageShouldNotContain($text)
    {
        $this->theResponseShouldNotContain($text);
    }

}

