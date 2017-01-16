<?php
namespace AppBundle;
use AppBundle\Helper\Acceptance;
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
        $siteId = $this->getSiteId();
        $company= new Company(
            $siteId,
            $companyName
        );
        $CompanyRepository = new CompanyRepository(\DatabaseConnection::getInstance());
        $CompanyRepository->persist($company, new DummyHistory($siteId));
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
        /** @var $row \Behat\Mink\Element\NodeElement */
        $row = $this->_findElements(sprintf('table tr:contains("%s")', $rowText));
        if (!$row) {
            throw new \Exception(sprintf('Cannot find any row on the page containing the text "%s"', $rowText));
        }
        $row->clickLink($linkName);

    }

}

