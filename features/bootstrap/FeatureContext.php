<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Testwork\Tester\Result\TestResult;
use Behat\Mink\Driver\Selenium2Driver;
use OpenCATS\Entity\Company;
use OpenCATS\Entity\CompanyRepository;

include_once('./config.php');
include_once('./constants.php');
include_once('./lib/DatabaseConnection.php');
include_once('./lib/Site.php');
include_once('./lib/History.php');
include_once('./lib/Users.php');
/**
 * Defines application features from the specific context.
 */
class FeatureContext extends MinkContext implements Context, SnippetAcceptingContext
{
    protected $scenarioTitle = null;
    protected static $wsendUser = null;
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
        $this->iLoginAs($roleData->getUserName(), $roleData->getPassword());
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
        $this->getSession()->setCookie(CATS_SESSION_NAME, 'o964p0pr602975o0671qo50n1208r6nn');
    }
    
    /**
     * @Given I wait for :element
     */
    public function iWaitFor($element)
    {
        $this->spins(function() use ($element) {
            $field = $this->getSession()->getPage()->find('css', $element);
            if (null === $field) {
                throw new Exception('form field ' . $element . 'id|name|label|value|placeholder');
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
    
        $url = $this->getScreenshotUrl($filename);
    
        print sprintf("Screenshot is available :\n%s", $url);
    }
    
    protected function getScreenshotUrl($filename)
    {
        if (!self::$wsendUser) {
            self::$wsendUser = $this->getWsendUser();
        }
    
        exec(sprintf(
            'curl -F "uid=%s" -F "filehandle=@%s" %s 2>/dev/null',
            self::$wsendUser,
            $filename,
            'https://wsend.net/upload_cli'
            ), $output, $return);
    
        return $output[0];
    }
    
    protected function getWsendUser()
    {
        // create a wsend anonymous user
        $curl = curl_init('https://wsend.net/createunreg');
        curl_setopt($curl, CURLOPT_POSTFIELDS, 'start=1');
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
        $wsendUser = curl_exec($curl);
        curl_close($curl);
    
        return $wsendUser;
    }
    
    protected function getScreenshotFilename()
    {
        $filename = $this->scenarioTitle;
        $filename = preg_replace("#[^a-zA-Z0-9\._-]#", '_', $filename);
    
        return sprintf('%s/%s.png', sys_get_temp_dir(), $filename);
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
        $siteId = $this->getSiteId();
        $company= new Company(
            $siteId,
            $companyName
        );
        $CompanyRepository = new CompanyRepository(DatabaseConnection::getInstance());
        $CompanyRepository->persist($company, new Dummy_History($siteId));
    }
    
    /**
     * @Given There is a user :userName named :fullName with :password password
     */
    public function thereIsAUserWithParams($userName, $fullName, $password) {
        list($firstName, $lastName) = explode(" ", $fullName);
        $siteId = $this->getSiteId();
        $users = new Users($siteId);
        $users->add(
            $lastName,
            $firstName,
            $email = '', 
            $userName,
            $password,
            $accessLevel = ACCESS_LEVEL_DELETE
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
        // errors must not pass silently
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
    
    private function getSiteId()
    {
        $site = new Site(-1);
        return $site->getFirstSiteID();
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

class Dummy_History extends History
{
    public function __construct($siteID) {}
    public function storeHistoryNew($dataItemType, $dataItemID) {}
}
