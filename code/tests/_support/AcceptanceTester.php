<?php

use \Codeception\Scenario;
use \AppBundle\Entity\User;

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
            $this->switchToIFrame();
        } else {
            $this->switchToIFrame($iFrameId);
        }
    }

    /**
     * @Given fill in :field with :value
     */
    public function fillInWith($field, $value)
    {
        $this->fillField($field, $value);
    }

    /**
     * @Given press :button
     */
    public function press($button)
    {
        $this->click($button);
    }

    /**
     * @Then I should see :text
     */
    public function iShouldSee($text)
    {
        $this->see($text);
    }
}
