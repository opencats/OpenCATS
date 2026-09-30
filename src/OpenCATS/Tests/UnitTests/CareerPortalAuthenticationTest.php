<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class CareerPortalAuthenticationTest extends TestCase
{
    private $ui;
    private $db;
    private $jobs;
    private $template;
    private $queries = array();
    private $match = true;
    private $registration = '<input-email><input-lastName><input-zip><input-rememberMe><input-submit>';

    protected function setUp(): void
    {
        require_once './constants.php';
        require_once './lib/UserInterface.php';
        require_once './lib/Template.php';
        require_once './lib/TemplateUtility.php';
        // CATSUtility loads config.php, which repeats PHPUnit's HTML_ENCODING constant.
        @include_once './lib/CATSUtility.php';
        $GLOBALS['countries'] = array('US' => 'United States');
        require_once './lib/Hooks.php';
        require_once './modules/careers/CareersUI.php';
        session_start();
        $_SESSION = array('careerPortalCaptcha' => 'AbCd', 'unrelated' => 'retained');
        $_GET = array('ID' => '42');
        $_POST = array('email' => 'candidate@example.com', 'lastName' => 'Applicant', 'zip' => '12345', 'captcha' => 'abcd', 'isNew' => 'no');
        $_COOKIE = array('cats1cw' => '"email"="candidate@example.com""lastName"="Applicant""zip"="12345"');
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['QUERY_STRING'] = '';
        $this->db = $this->createStub(DatabaseConnection::class);
        (new ReflectionProperty(DatabaseConnection::class, '_instance'))->setValue(null, $this->db);
        $this->db->method('makeQueryString')->willReturnCallback(fn($value) => "'" . $value . "'");
        $this->db->method('makeQueryInteger')->willReturnCallback(fn($value) => (string) $value);
        $this->db->method('getNumRows')->willReturnCallback(fn() => $this->match ? 1 : 0);
        $this->db->method('getAssoc')->willReturnCallback(function ($sql) {
            $this->queries[] = $sql;
            if (str_contains($sql, 'SELECT candidate_id FROM candidate')) return $this->match ? array('candidate_id' => 7) : array();
            if (str_contains($sql, 'candidate.candidate_id AS candidateID')) return $this->candidate();
            if (preg_match('/FROM\s+user\b/', $sql)) return array('userID' => 1);
            if (str_contains($sql, 'FROM site')) return array('name' => 'Test site');
            if (preg_match('/FROM\s+site\b/', $sql)) return array('name' => 'Test site');
            if (preg_match('/FROM\s+joborder\b/', $sql)) return array('public' => 1, 'title' => 'Test job', 'questionnaireID' => 0);
            return array();
        });
        $this->db->method('getAllAssoc')->willReturnCallback(function ($sql) {
            $this->queries[] = $sql;
            if (str_starts_with($sql, 'SHOW COLUMNS')) return array(array('Field' => 'last_name'), array('Field' => 'zip'));
            if (preg_match('/FROM\s+settings\b/', $sql)) return array(array('setting' => 'enabled', 'value' => '1'), array('setting' => 'candidateRegistration', 'value' => '1'));
            if (str_contains($sql, 'career_portal_template')) {
                $values = array('Content - Candidate Registration' => $this->registration,
                    'Content - Candidate Profile' => '<input-firstName><input-email1><input-submit>',
                    'Content - Apply for Position' => '<input-firstName><input-email><input-captcha req>',
                    'Content - Main' => '<registeredCandidate><registeredLogin>',
                    'Content - Search Results' => '<registeredCandidate>',
                    'Header' => '', 'Footer' => '', 'CSS' => '');
                return array_map(fn($key, $value) => array('setting' => $key, 'value' => $value), array_keys($values), array_values($values));
            }
            if (preg_match('/FROM\s+joborder\b/', $sql)) return array(array('jobOrderID' => 42, 'departmentID' => 0, 'title' => 'Test job', 'city' => '', 'state' => '', 'country' => ''));
            return array();
        });
        $this->jobs = $this->createStub(JobOrders::class);
        $this->ui = (new ReflectionClass(CareersUI::class))->newInstanceWithoutConstructor();
        $this->template = new class extends Template { public function display($file) {} };
        (new ReflectionProperty(UserInterface::class, '_template'))->setValue($this->ui, $this->template);
    }

    protected function tearDown(): void
    {
        session_destroy();
        (new ReflectionProperty(DatabaseConnection::class, '_instance'))->setValue(null, null);
    }

    private function candidate(): array
    {
        $candidate = array_fill_keys(array('middleName', 'address', 'address2', 'city', 'state', 'country',
            'phoneHome', 'phoneCell', 'phoneWork', 'email2', 'bestTimeToCall', 'keySkills', 'source',
            'currentEmployer', 'dateAvailable', 'currentPay', 'desiredPay', 'notes', 'webSite',
            'eeoGender', 'eeoEthnicType', 'eeoVeteranType', 'eeoDisabilityStatus'), '');
        return $candidate + array('candidateID' => 7, 'firstName' => 'Career', 'lastName' => 'Applicant',
            'email1' => 'candidate@example.com', 'zip' => '12345', 'isActive' => 1, 'canRelocate' => 0, 'owner' => 1, 'isHot' => 0);
    }

    private function invoke($method, ...$args)
    {
        return (new ReflectionMethod(CareersUI::class, $method))->invoke($this->ui, ...$args);
    }

    private function login($publicJobs = array(array('jobOrderID' => 42)))
    {
        return $this->invoke('candidateLogin', $this->registration, $this->jobs, $publicJobs);
    }

    private function assertNoCandidateLookup(): void
    {
        self::assertDoesNotMatchRegularExpression('/(?:FROM|COLUMNS FROM)\s+candidate\b/i', implode("\n", $this->queries));
    }

    public function testLoginRotatesSessionAndLoadsCandidateWithoutCredentialReplay(): void
    {
        $this->jobs = $this->createMock(JobOrders::class);
        $this->jobs->expects(self::once())->method('get')->with(42)->willReturn(array('public' => 1));
        $oldID = session_id();
        self::assertTrue($this->login());
        self::assertNotSame($oldID, session_id());
        self::assertSame(7, $_SESSION['careerPortalCandidateID']);
        self::assertSame('retained', $_SESSION['unrelated']);
        self::assertArrayNotHasKey('careerPortalCaptcha', $_SESSION);
        $_POST = array();
        $_COOKIE = array();
        $this->queries = array();
        self::assertSame(7, $this->invoke('getCareerPortalCandidate')['candidateID']);
        self::assertStringContainsString('Welcome back Career', $this->invoke('getRegisteredCandidateBlock', $this->registration));
        self::assertStringNotContainsString('SELECT candidate_id FROM candidate', implode("\n", $this->queries));
        self::assertStringNotContainsString('cats1cw', implode("\n", headers_list()));
    }

    public static function invalidIDs(): array
    {
        return array_map(fn($id) => array($id), array(null, '', '42x', '-1', '0', array('42'), '999999999999999999999999'));
    }

    #[DataProvider('invalidIDs')]
    public function testInvalidJobPreventsCandidateQueries($id): void
    {
        $_GET['ID'] = $id;
        $this->jobs = $this->createMock(JobOrders::class);
        $this->jobs->expects(self::never())->method('get');
        self::assertFalse($this->login());
        $this->assertNoCandidateLookup();
    }

    public static function unavailableJobs(): array
    {
        return array(array(array(), array()), array(array('public' => 0), array(array('jobOrderID' => 42))),
            array(array('public' => 1), array()));
    }

    #[DataProvider('unavailableJobs')]
    public function testUnavailableJobPreventsCandidateQueries($job, $listing): void
    {
        $this->jobs->method('get')->willReturn($job);
        self::assertFalse($this->login($listing));
        $this->assertNoCandidateLookup();
        self::assertSame('AbCd', $_SESSION['careerPortalCaptcha']);
    }

    public function testInvalidCaptchaPreventsCandidateQueriesAndIsConsumed(): void
    {
        $this->jobs->method('get')->willReturn(array('public' => 1));
        $_POST['captcha'] = 'wrong';
        self::assertFalse($this->login());
        $this->assertNoCandidateLookup();
        self::assertArrayNotHasKey('careerPortalCaptcha', $_SESSION);
        self::assertArrayNotHasKey('careerPortalCandidateID', $_SESSION);
    }

    public static function incorrectDetails(): array
    {
        return array(array('email'), array('lastName'), array('zip'));
    }

    #[DataProvider('incorrectDetails')]
    public function testIncorrectDetailsHaveIdenticalFailure($field): void
    {
        $this->jobs->method('get')->willReturn(array('public' => 1));
        $_POST[$field] = 'incorrect';
        $this->match = false;
        self::assertFalse($this->login());
        self::assertArrayNotHasKey('careerPortalCandidateID', $_SESSION);
        self::assertCount(1, array_filter($this->queries, fn($sql) => str_starts_with($sql, 'SELECT candidate_id FROM candidate')));
    }

    public function testOldCookieDoesNotAuthenticate(): void
    {
        self::assertFalse($this->invoke('getCareerPortalCandidate'));
        $this->assertNoCandidateLookup();
    }

    public function testNewApplicantContinuesWithoutAuthentication(): void
    {
        $this->jobs->method('get')->willReturn(array('public' => 1));
        $_POST = array('isNew' => 'yes', 'email' => 'new@example.com');
        self::assertTrue($this->login());
        $this->assertNoCandidateLookup();
        self::assertArrayNotHasKey('careerPortalCandidateID', $_SESSION);
    }

    public function testRegistrationFormUsesExplicitLoginAndExistingCaptcha(): void
    {
        $_GET['p'] = 'candidateRegistration';
        $this->invoke('careersPage');
        $content = $this->template->template['Content'];
        self::assertStringContainsString('p=candidateLogin', $content);
        self::assertStringContainsString('p=captcha', $content);
        self::assertStringContainsString('name="captcha"', $content);
        self::assertStringNotContainsString('rememberMe', $content);
        self::assertStringNotContainsString('processLogin', $content);
        $this->assertNoCandidateLookup();
    }

    public function testLogoutClearsOnlyCandidateStateAndRotatesSession(): void
    {
        $_SESSION['careerPortalCandidateID'] = 7;
        $_POST = array('pa' => 'logout');
        $_GET['p'] = 'showAll';
        $oldID = session_id();
        $this->invoke('careersPage');
        self::assertArrayNotHasKey('careerPortalCandidateID', $_SESSION);
        self::assertSame('retained', $_SESSION['unrelated']);
        self::assertNotSame($oldID, session_id());
        $this->assertNoCandidateLookup();
    }

    public function testProfileAndApplicationPopulateFromSession(): void
    {
        $_SESSION['careerPortalCandidateID'] = 7;
        $_POST = array();
        $_COOKIE = array();
        foreach (array('registeredCandidateProfile', 'applyToJob') as $page) {
            $_GET['p'] = $page;
            $this->invoke('careersPage');
            self::assertStringContainsString('value="Career"', $this->template->template['Content']);
            self::assertStringContainsString('value="candidate@example.com"', $this->template->template['Content']);
        }
        self::assertStringNotContainsString('SELECT candidate_id FROM candidate', implode("\n", $this->queries));
    }
    public function testExplicitLoginRouteAuthenticatesOnlyOnce(): void
    {
        $_GET['p'] = 'candidateLogin';
        $this->invoke('careersPage');
        self::assertSame(7, $_SESSION['careerPortalCandidateID']);
        self::assertStringContainsString('value="Career"', $this->template->template['Content']);
        $_SESSION['careerPortalCaptcha'] = 'AbCd';
        $_POST['lastName'] = 'not replayed';
        $this->invoke('careersPage');
        self::assertCount(1, array_filter($this->queries, fn($sql) => str_starts_with($sql, 'SELECT candidate_id FROM candidate')));
    }

    public static function updatePages(): array
    {
        return array(array('onRegisteredCandidateProfile'), array('onApplyToJobOrder'));
    }

    #[DataProvider('updatePages')]
    public function testUpdatesUseSessionCandidateInsteadOfPostedID($page): void
    {
        $_SESSION['careerPortalCandidateID'] = 7;
        $_COOKIE = array();
        $_GET['p'] = $page;
        $_POST = array('ID' => '42', 'candidateID' => '999', 'firstName' => 'Updated',
            'lastName' => 'Changed', 'email' => 'new@example.com', 'captcha' => 'abcd');
        // Stop at the real Candidates::update database boundary, before redirects/mail.
        $this->db->method('query')->willReturnCallback(function ($sql) {
            if (preg_match('/UPDATE\s+candidate\s+SET/', $sql)) {
                self::assertMatchesRegularExpression('/candidate_id = 7\s*$/', $sql);
                self::assertStringContainsString("'Updated'", $sql);
                throw new RuntimeException('candidate update reached');
            }
            return true;
        });
        try {
            $this->invoke('careersPage');
            self::fail('The candidate update was not reached.');
        } catch (RuntimeException $exception) {
            self::assertSame('candidate update reached', $exception->getMessage());
        }
        self::assertStringNotContainsString('SELECT candidate_id FROM candidate', implode("\n", $this->queries));
    }

    public function testNewApplicantRouteRetainsApplicationForm(): void
    {
        $_GET['p'] = 'candidateLogin';
        $_POST = array('isNew' => 'yes', 'email' => 'new@example.com');
        $this->invoke('careersPage');
        self::assertStringContainsString('value="new@example.com"', $this->template->template['Content']);
        self::assertArrayNotHasKey('careerPortalCandidateID', $_SESSION);
        $this->assertNoCandidateLookup();
    }

    public function testNoCredentialCookieWritersRemain(): void
    {
        $source = file_get_contents('./modules/careers/CareersUI.php');
        self::assertStringNotContainsString('setcookie(', $source);
        self::assertStringNotContainsString('getCookieFields', $source);
        self::assertStringNotContainsString('cats%dcw', $source);
    }

}
