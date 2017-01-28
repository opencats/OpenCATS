<?php
namespace AppBundle\Step\Acceptance;

use Behat\Mink\Exception\Exception;
use Codeception\Exception\Fail;

class Security extends \AppBundle\AcceptanceTester
{
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
     * @Then the page should not contain :arg1
     */
    public function thePageShouldNotContain($arg1)
    {
        throw new \Codeception\Exception\Incomplete("Step `the page should not contain :arg1` is not defined");
    }

    /**
     * @Then the page should  contain :arg1
     */
    public function thePageShouldContain($arg1)
    {
        throw new \Codeception\Exception\Incomplete("Step `the page should  contain :arg1` is not defined");
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
}