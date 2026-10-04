<?php

use PHPUnit\Framework\TestCase;

/**
 * Exercise legacy dispatch and data helpers without a database or error-page exit.
 * Each concrete test runs in a separate process to isolate legacy globals.
 */
abstract class AuthorizationTestCase extends TestCase
{
    protected $db;
    protected $session;
    protected $accessLevel;
    protected $loggedIn = false;
    protected $accessOverrides = array();
    protected $queries = array();

    protected function setUp(): void
    {
        require_once './constants.php';
        require_once './lib/UserInterface.php';
        require_once './lib/Template.php';
        require_once './lib/Hooks.php';
        // The legacy config repeats the HTML_ENCODING constant from phpunit.xml.
        @include_once './lib/CATSUtility.php';
        require_once './lib/Session.php';
        require_once './lib/DatabaseConnection.php';
        $this->accessLevel = ACCESS_LEVEL_EDIT;
        $this->session = $this->createStub(CATSSession::class);
        $this->session->method('getAccessLevel')->willReturnCallback(
            fn($key) => $this->accessOverrides[$key] ?? $this->accessLevel
        );
        $this->session->method('getUserID')->willReturn(10);
        $this->session->method('hasUserCategory')->willReturn(false);
        $this->session->method('isLoggedIn')->willReturnCallback(fn() => $this->loggedIn);
        $this->session->method('isDemo')->willReturnCallback(function () {
            // CommonErrors terminates PHP after rendering. Stop at that boundary,
            // preserving the real error code and all authorization logic before it.
            foreach (debug_backtrace() as $frame)
            {
                if (($frame['class'] ?? '') === 'CommonErrors' && $frame['function'] === 'fatal')
                {
                    throw new RuntimeException('CommonErrors', $frame['args'][0]);
                }
            }
            return false;
        });
        $_SESSION = array('CATS' => $this->session);
        $_GET = $_POST = $_REQUEST = array();
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $this->db = $this->createStub(DatabaseConnection::class);
        (new ReflectionProperty(DatabaseConnection::class, '_instance'))->setValue(null, $this->db);
        $this->db->method('makeQueryString')->willReturnCallback(fn($value) => "'" . $value . "'");
        $this->db->method('makeQueryStringOrNULL')->willReturnCallback(fn($value) => $value === null ? 'NULL' : "'" . $value . "'");
        $this->db->method('makeQueryInteger')->willReturnCallback(fn($value) => (string) $value);
        $this->db->method('query')->willReturnCallback(function ($sql) {
            $this->queries[] = $sql;
            return true;
        });
        $this->db->method('getLastInsertID')->willReturn(42);
    }

    protected function ui($class)
    {
        $ui = (new ReflectionClass($class))->newInstanceWithoutConstructor();
        (new ReflectionProperty(UserInterface::class, '_template'))->setValue($ui, $this->createStub(Template::class));
        return $ui;
    }

    protected function assertCommonError($code, callable $action): void
    {
        try
        {
            $action();
            self::fail('Request should have been rejected.');
        }
        catch (RuntimeException $error)
        {
            self::assertSame('CommonErrors', $error->getMessage());
            self::assertSame($code, $error->getCode());
        }
    }

    protected function tearDown(): void
    {
        (new ReflectionProperty(DatabaseConnection::class, '_instance'))->setValue(null, null);
    }
}
