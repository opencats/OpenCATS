<?php

include_once(".\lib\DatabaseConnection.php");
include_once(".\constants.php");

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\ElementHtmlException;


define('SITE_ID', 1);
define('ADMIN_ID', 1);
/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context, SnippetAcceptingContext
{
    private $roleData;
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
            'Administrator' => new Role('admin', 'admin'),
            'User' => new Role('john@mycompany.net', 'john99')
        );
    }
    
    
    /**
     * @Given I am authenticated as :role
     */
    public function iAmAuthenticatedAs($role)
    {
        $roleData = empty($this->roleData[$role]) ? null : $this->roleData[$role]; 
        if (!$roleData) {
            throw new PendingException();
        }
        $this->visitPath('/index.php?m=login');
        $this->fillField('username', $roleData->getUserName());
        $this->fillField('password', $roleData->getPassword());
        $this->pressButton('Login');
    }
    
    /**
     * @Given I am logged in with :accessLevel access level
     */
    public function iAmLoggedInWithAccessLevel($accessLevel)
    {
        switch($accessLevel)
        {
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
        
        $this->visitPath('/index.php?m=login');
        $this->fillField('username', $username);
        $this->fillField('password', $password);
        $this->pressButton('Login');
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
    
    /**
     * @Then the page should  contain :text
     */
    public function assertHTMLcontains($text)
    {
        $this->assertSession()->responseContains($text);
    }
    
    /**
     * @Then the page should not contain :text
     */
    public function assertHTMLnotContains($text)
    {
        $this->assertSession()->responseNotContains($text);
    }
    
    /**
     * @Then I will log out
     */
    public function iWillLogOut()
    {
        $this->clickLink('Logout');
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