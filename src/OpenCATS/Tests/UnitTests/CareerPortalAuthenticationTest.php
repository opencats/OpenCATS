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
    private $questionnaireID = 0;
    private $vacancyState = 'listed';
    private $existingApplication = false;
    private $candidateEmail = 'candidate@example.com';
    private $registrationEnabled = '1';
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
        $_SERVER['HTTP_HOST'] = 'example.test';
        $_SERVER['REQUEST_URI'] = '/careers/?p=onApplyToJobOrder';
        $_FILES = array();
        $this->db = $this->createStub(DatabaseConnection::class);
        (new ReflectionProperty(DatabaseConnection::class, '_instance'))->setValue(null, $this->db);
        $this->db->method('makeQueryString')->willReturnCallback(fn($value) => "'" . $value . "'");
        $this->db->method('makeQueryStringOrNULL')->willReturnCallback(fn($value) => "'" . $value . "'");
        $this->db->method('makeQueryIntegerOrNULL')->willReturnCallback(fn($value) => (string) (int) $value);
        $this->db->method('getLastInsertID')->willReturn(7);
        $this->db->method('makeQueryInteger')->willReturnCallback(fn($value) => (string) $value);
        $this->db->method('getNumRows')->willReturnCallback(fn() => $this->match ? 1 : 0);
        $this->db->method('getAssoc')->willReturnCallback($this->candidateQueryResult(...));
        $this->db->method('getAllAssoc')->willReturnCallback($this->portalQueryRows(...));
        $this->jobs = $this->createStub(JobOrders::class);
        $this->ui = (new ReflectionClass(CareersUI::class))->newInstanceWithoutConstructor();
        // Keep real template assignment for content assertions, without rendering files.
        $this->template = new Template();
        $renderer = $this->createStub(Template::class);
        $renderer->method('assign')->willReturnCallback($this->template->assign(...));
        $renderer->method('display')->willReturnCallback(function ($path) {
            if ($path === './modules/settings/CareerPortalQuestionnaireShow.tpl')
            {
                echo 'Questionnaire fixture';
            }
        });
        (new ReflectionProperty(UserInterface::class, '_template'))->setValue($this->ui, $renderer);
    }

    private function candidateQueryResult($sql): array
    {
        $this->queries[] = $sql;
        if (str_contains($sql, 'SELECT candidate_id FROM candidate')) return $this->match ? array('candidate_id' => 7) : array();
        if (!$this->match && str_contains($sql, 'candidate.email1 =')) return array();
        if (preg_match('/FROM\s+user\b/', $sql)) return array('userID' => 1);
        if (preg_match('/FROM\s+site\b/', $sql)) return array('name' => 'Test site', 'dateFormatDDMMYY' => 0);
        if (preg_match('/FROM\s+candidate_joborder\b/', $sql))
        {
            if (str_contains($sql, 'COUNT(candidate_id)')) return array('candidateIDCount' => 0);
            return $this->existingApplication ? array('candidateJobOrderID' => 8) : array();
        }
        if (str_contains($sql, 'candidate.candidate_id AS candidateID')) return $this->candidate();
        if (preg_match('/FROM\s+joborder\b/', $sql))
        {
            return $this->vacancyState === 'missing' ? array() : array(
                'jobOrderID' => 42, 'public' => (int) ($this->vacancyState !== 'private'),
                'title' => 'Test job', 'questionnaireID' => $this->questionnaireID,
                'ownerFullName' => 'Test owner', 'companyName' => 'Test company',
                'owner_email' => 'owner@example.test', 'recruiter_email' => 'recruiter@example.test'
            );
        }
        if (preg_match('/FROM\s+career_portal_questionnaire\b/', $sql)) return array(
            'questionnaireID' => $this->questionnaireID, 'title' => 'Screening', 'description' => 'Application questions'
        );
        return array();
    }

    private function portalQueryRows($sql): array
    {
        $this->queries[] = $sql;
        if (str_starts_with($sql, 'SHOW COLUMNS')) return array(array('Field' => 'last_name'), array('Field' => 'zip'));
        if (preg_match('/FROM\s+settings\b/', $sql)) return array(array('setting' => 'enabled', 'value' => '1'), array('setting' => 'candidateRegistration', 'value' => $this->registrationEnabled));
        if (str_contains($sql, 'career_portal_template')) {
            $values = array('Content - Candidate Registration' => $this->registration,
                'Content - Candidate Profile' => '<input-firstName><input-email1><input-submit>',
                'Content - Apply for Position' => '<input-firstName><input-email><input-captcha req>',
                'Content - Main' => '<registeredCandidate><registeredLogin>',
                'Content - Thanks for your Submission' => 'Thank you for your submission for <title>',
                'Content - Search Results' => '<numberOfSearchResults><registeredCandidate>',
                'Content - Questionnaire' => '<questionnaire><submit>',
                'Header' => '', 'Footer' => '', 'CSS' => '');
            return array_map(fn($key, $value) => array('setting' => $key, 'value' => $value), array_keys($values), array_values($values));
        }
        if (preg_match('/FROM\s+career_portal_questionnaire_question\b/', $sql))
        {
            return array(array(
                'questionID' => 2, 'questionType' => QUESTIONNAIRE_QUESTION_TYPE_TEXT,
                'questionText' => 'Availability', 'minimumLength' => 0, 'maximumLength' => 255, 'questionPosition' => 1
            ));
        }
        if (preg_match('/FROM\s+joborder\b/', $sql))
        {
            self::assertStringContainsString('joborder.public = 1', $sql);
            self::assertStringContainsString('joborder.is_admin_hidden = 0', $sql);
            self::assertStringContainsString('joborder.status IN ' . JobOrderStatuses::getShareStatusSQL(), $sql);
            return $this->vacancyState === 'listed' ? array(array(
                'jobOrderID' => '42', 'departmentID' => 0, 'title' => 'Test job',
                'city' => '', 'state' => '', 'country' => ''
            )) : array();
        }
        return array();
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
            'email1' => $this->candidateEmail, 'zip' => '12345', 'isActive' => 1, 'canRelocate' => 0, 'owner' => 1, 'isHot' => 0);
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

    public static function anonymousApplicationJobs(): array
    {
        return array(
            'with questionnaire' => array(1, '1'),
            'without questionnaire or registration' => array(0, '0')
        );
    }

    #[DataProvider('anonymousApplicationJobs')]
    public function testAnonymousEmailMatchKeepsSubmissionResponseWithoutWrites($questionnaireID, $registrationEnabled): void
    {
        $this->questionnaireID = $questionnaireID;
        $this->registrationEnabled = $registrationEnabled;
        $_GET = array('p' => 'onApplyToJobOrder');
        if ($questionnaireID)
        {
            $_GET['questionnairePostBack'] = '1';
        }
        $_POST = array('ID' => '42', 'firstName' => 'Career', 'lastName' => 'Applicant',
            'email' => 'candidate@example.com', 'captcha' => 'abcd');
        $this->db->method('query')->willReturnCallback(function ($sql) {
            self::fail('Unexpected query after an unverified email match: ' . $sql);
        });

        $this->invoke('careersPage');

        self::assertMatchesRegularExpression('/candidate\.email1\s*=.*candidate@example\.com/', implode("\n", $this->queries));
        self::assertSame('Thank you for your submission for Test job', $this->template->template['Content']);
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

    public static function unavailableApplicationRoutes(): iterable
    {
        foreach (array('missing', 'private', 'hidden', 'status excluded') as $state)
        {
            yield "$state questionnaire" => array($state, 'onApplyToJobOrder', false);
            yield "$state final submission" => array($state, 'onApplyToJobOrder', true);
        }
        yield 'hidden registration form' => array('hidden', 'candidateRegistration', false);
        yield 'hidden resume preview' => array('hidden', 'applyToJob', false);
    }

    #[DataProvider('unavailableApplicationRoutes')]
    public function testUnavailableVacanciesStopBeforeQuestionnaireOrWrites($state, $page, $postback): void
    {
        $this->vacancyState = $state;
        $this->questionnaireID = 1;
        $_SESSION['careerPortalCandidateID'] = 7;
        $_GET['p'] = $page;
        $_POST['ID'] = '42';
        if ($postback)
        {
            $_GET['questionnairePostBack'] = '1';
        }
        if ($page === 'applyToJob')
        {
            $_POST['applyToJobSubAction'] = 'resumeParse';
        }
        $this->db->method('query')->willReturnCallback(fn() => self::fail('Unavailable vacancies must not cause writes.'));

        ob_start();
        try
        {
            $this->ui->handleRequest();
            $response = ob_get_contents();
        }
        finally
        {
            ob_end_clean();
        }

        self::assertStringContainsString('This position is no longer available.', $response);
        self::assertStringContainsString('p=showAll', $response);
        self::assertStringNotContainsString('Test job', $response);
        self::assertStringNotContainsString('career_portal_questionnaire', implode("\n", $this->queries));
        self::assertArrayNotHasKey('Content', $this->template->template ?? array());
        $this->assertNoCandidateLookup();
    }

    public function testListedVacancyStillRendersItsQuestionnaire(): void
    {
        $_GET['p'] = 'showAll';
        $this->ui->handleRequest();
        self::assertStringStartsWith('1', $this->template->template['Content']);

        $this->questionnaireID = 1;
        $_GET['p'] = 'onApplyToJobOrder';
        $_POST['ID'] = '42';
        $this->db->method('query')->willReturnCallback(fn() => self::fail('Questionnaire display must not write.'));

        $this->ui->handleRequest();

        self::assertSame(1, $this->template->questionnaireID);
        self::assertStringContainsString('Questionnaire fixture', $this->template->template['Content']);
        self::assertStringContainsString('questionnairePostBack=1', $this->template->template['Content']);
        self::assertArrayNotHasKey('careerPortalCaptcha', $_SESSION);
    }

    public static function eligibleApplications(): iterable
    {
        yield 'new candidate' => array(false, false);
        yield 'returning candidate' => array(true, false);
        yield 'repeat application' => array(true, true);
    }

    #[DataProvider('eligibleApplications')]
    public function testListedVacancyKeepsApplicationAndNotificationFlow($returning, $duplicate): void
    {
        require_once './lib/Session.php';
        $staffSession = $this->createStub(CATSSession::class);
        $staffSession->method('getUserID')->willReturn(1);
        $_SESSION['CATS'] = $staffSession;
        $this->match = $returning;
        $this->existingApplication = $duplicate;
        if ($returning)
        {
            $_SESSION['careerPortalCandidateID'] = 7;
        }
        $_GET['p'] = 'onApplyToJobOrder';
        $_POST += array('ID' => '42', 'firstName' => 'Career');
        if ($returning && !$duplicate)
        {
            $this->questionnaireID = 1;
            // Keep the questionnaire's legacy profile update from invoking SMTP.
            $this->candidateEmail = '';
            $_GET['questionnairePostBack'] = '1';
            $_POST['questionnaire1Question2'] = 'Available Monday';
        }
        $writes = array();
        $this->db->method('query')->willReturnCallback(function ($sql) use (&$writes) {
            $writes[] = $sql;
            return true;
        });

        $checkout = getcwd();
        $fixture = sys_get_temp_dir() . '/opencats-careers-' . bin2hex(random_bytes(8));
        mkdir($fixture . '/upload/careerportaladd', 0700, true);
        symlink($checkout . '/lib', $fixture . '/lib');
        file_put_contents($fixture . '/upload/careerportaladd/resume.txt', 'Applicant resume fixture.');
        $_POST['file'] = 'resume.txt';

        chdir($fixture);
        try
        {
            $this->ui->handleRequest();
        }
        finally
        {
            chdir($checkout);
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($fixture, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $file)
            {
                if ($file->isDir() && !$file->isLink())
                {
                    rmdir($file->getPathname());
                }
                else
                {
                    unlink($file->getPathname());
                }
            }
            rmdir($fixture);
        }

        $sql = implode("\n", $writes);
        self::assertMatchesRegularExpression($returning ? '/UPDATE\s+candidate\s+SET\s+is_active/' : '/INSERT INTO candidate\s*\(/', $sql);
        self::assertSame(1, substr_count($sql, 'INSERT INTO attachment ('));
        self::assertSame($duplicate ? 0 : 1, substr_count($sql, 'INSERT INTO candidate_joborder'));
        self::assertStringContainsString($duplicate ? 'User re-applied' : 'User applied', $sql);
        if ($this->questionnaireID)
        {
            self::assertStringContainsString('career_portal_questionnaire_history', $sql);
            self::assertStringContainsString('Available Monday', $sql);
        }
        $reads = implode("\n", $this->queries);
        self::assertStringContainsString('EMAIL_TEMPLATE_CANDIDATEAPPLY', $reads);
        self::assertStringContainsString('EMAIL_TEMPLATE_CANDIDATEPORTALNEW', $reads);
        self::assertSame('Thank you for your submission for Test job', $this->template->template['Content']);
    }

    public function testNoCredentialCookieWritersRemain(): void
    {
        $source = file_get_contents('./modules/careers/CareersUI.php');
        self::assertStringNotContainsString('setcookie(', $source);
        self::assertStringNotContainsString('getCookieFields', $source);
        self::assertStringNotContainsString('cats%dcw', $source);
    }

}
