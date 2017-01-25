<?php

include_once("./lib/DatabaseConnection.php");
include_once("./constants.php");

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
 * Defines application features from the security context.
 */
class SecurityContext extends MinkContext implements Context, SnippetAcceptingContext
{
    private $result;
    private $accessLevel;

    /**
     * Initializes Security context.
     *
     * Every scenario gets its own context instance.
     * You can also pass arbitrary arguments to the
     * context constructor through behat.yml.
     */
    public function __construct()
    {
    }

    /**
     * @Given I am logged in with :accessLevel access level
     */
    public function iAmLoggedInWithAccessLevel($accessLevel)
    {
        $this->accessLevel = $accessLevel;
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
        
        $this->visitPath('/index.php?m=login&a=logout');
        $this->visitPath('/index.php?m=login');
        $this->fillField('username', $username);
        $this->fillField('password', $password);
        $this->pressButton('Login');
    }

    /**
     * @When I do :type request on url :url
     */
    public function whenIDoRequestOnUrl($type, $url)
    {
        switch($type){
            case "GET":
                $this->iDoGETRequest($url);
                break;
            case "POST":
                $this->iDoPOSTRequest($url);
                break;
            default:
                throw new PendingException();
                
        }
    }
    
    /**
     * @When I do POST request :url
     */
    public function iDoPOSTRequest($url)
    {
        $url = rtrim($this->getMinkParameter('base_url'), '/') . '/'.$url;
        $data = array('postback' => 'postback');

        // use key 'http' even if you send the request to https://...
        $options = array(
            'http' => array(
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n"."Cookie: CATS=".$this->getSession()->getCookie('CATS')."\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data)
            )
        );
        $context  = stream_context_create($options);
        $this->result = file_get_contents($url, false, $context);
    }

   /**
     * @When I do GET request :url
     */
    public function iDoGETRequest($url)
    {
        $opts = array(
            'http'=>array(
            'method'=>"GET",
            'header'=> "Cookie: CATS=".$this->getSession()->getCookie('CATS')."\r\n"
          )
        );

        $context = stream_context_create($opts);
        $url = rtrim($this->getMinkParameter('base_url'), '/') . '/'.$url.'&';
        $this->result = file_get_contents($url, false, $context);
    }

    /**
     * @Then /^the response should  contain "(?P<text>(?:[^"]|\\")*)"$/
     */
    public function theResponseShouldContain($text)
    {
        $response = $this->result;
        $position = strpos($response, $text);
        if($position === false)
        {
            throw new ExpectationException("'".$text."' was not found in the response from this request and it should be", $this->getSession());
        }
    }

    /**
     * @Then `/^the response should not contain "(?P<text>(?:[^"]|\\")*)"$/`
     */
    public function theResponseShouldNotContain($text)
    {
        $response = $this->result;
        $position = strpos($response, $text);
        if($position !== false)
        {
            throw new ExpectationException("'".$text."' was found in the response from this request and it should be not", $this->getSession());
        }
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
     * @Then I should not have permission
     */
    public function iShouldNotHavePermission()
    {
        
        if($this->accessLevel == "DISABLED")
        {
            $this->theResponseShouldContain("opencats - Login");
            return;
        }
        $expectedTexts = array("You don't have permission", "Invalid user level for action", "You are not allowed to change your password.");
        $response = $this->result;

        foreach ($expectedTexts as &$text)
        {
            $position = strpos($response, $text);
            if($position !== false)
            {
                return;
            }
        }
        throw new ExpectationException("'".$expectedTexts[0]."' was not found in the response from this request and it should be", $this->getSession());
    }    

    /**
     * @When I follow link :name
     */
    public function iFollowLink($name)
    {
        $link = $this->getSession()->getPage()->findLink($name);
        if($link !== null)
        {
            $link->click();
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
