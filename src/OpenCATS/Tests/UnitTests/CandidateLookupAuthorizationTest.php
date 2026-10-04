<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class CandidateLookupAuthorizationTest extends AuthorizationTestCase
{
    protected function setUp(): void
    {
        session_start();
        parent::setUp();
        $this->loggedIn = true;
        $_SERVER['REQUEST_METHOD'] = 'GET';
    }

    public static function lookups(): array
    {
        $cases = array();
        foreach (array('Email', 'Phone') as $kind)
        {
            $cases[] = array($kind, false, false, true, true);
            $cases[] = array($kind, true, false, true, false);
            $cases[] = array($kind, true, true, true, true);
            $cases[] = array($kind, false, false, false, false);
        }
        return $cases;
    }

    #[DataProvider('lookups')]
    public function testLookupDoesNotDiscloseInaccessibleCandidate($kind, $hidden, $admin, $exists, $allowed): void
    {
        $this->accessLevel = $admin ? ACCESS_LEVEL_SA : ACCESS_LEVEL_EDIT;
        $_REQUEST = array(strtolower($kind) => $kind === 'Email' ? 'person@example.test' : '01234567890');
        $this->db->method('getAssoc')->willReturn($exists
            ? array('candidateID' => 42, 'candidateFullName' => 'Private Person', 'isAdminHidden' => $hidden ? '1' : '0')
            : array());
        ob_start();
        try
        {
            include './ajax/getCandidateIdBy' . $kind . '.php';
            $xml = ob_get_contents();
        }
        finally
        {
            ob_end_clean();
        }
        if ($allowed)
        {
            self::assertStringContainsString('<id>42</id>', $xml);
            self::assertStringContainsString('<name>Private Person</name>', $xml);
        }
        else
        {
            self::assertStringContainsString('<id>-1</id>', $xml);
            self::assertStringNotContainsString('Private Person', $xml);
            self::assertStringNotContainsString('<name>', $xml);
        }
    }

    protected function tearDown(): void
    {
        session_destroy();
        parent::tearDown();
    }
}
