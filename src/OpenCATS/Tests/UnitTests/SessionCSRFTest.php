<?php
use PHPUnit\Framework\TestCase;

if( !defined('LEGACY_ROOT') )
{
    define('LEGACY_ROOT', '.');
}

include_once(LEGACY_ROOT . '/lib/Session.php');

class SessionCSRFTest extends TestCase
{
    private $_session;

    protected function setUp(): void
    {
        $this->_session = new CATSSession();
    }

    function testGetCSRFTokenCreatesToken()
    {
        $token = $this->_session->getCSRFToken();

        $this->assertTrue(is_string($token));
        $this->assertSame(64, strlen($token));
        $this->assertTrue(ctype_xdigit($token));
    }

    function testGetCSRFTokenStableWithoutRotation()
    {
        $t1 = $this->_session->getCSRFToken();
        $t2 = $this->_session->getCSRFToken();

        $this->assertEquals($t1, $t2);
    }

    function testRotateCSRFTokenChangesToken()
    {
        $old = $this->_session->getCSRFToken();
        $new = $this->_session->rotateCSRFToken();

        $this->assertNotEquals($old, $new);
        $this->assertEquals($new, $this->_session->getCSRFToken());
        $this->assertSame(64, strlen($new));
        $this->assertTrue(ctype_xdigit($new));
    }

    function testIsCSRFTokenValid()
    {
        $token = $this->_session->getCSRFToken();

        $this->assertTrue($this->_session->isCSRFTokenValid($token));
        $this->assertFalse($this->_session->isCSRFTokenValid($token . '00'));
        $this->assertFalse($this->_session->isCSRFTokenValid('invalid'));
    }

    function testCSRFTokenEdgeCases()
    {
        $freshSession = new CATSSession();
        $this->assertFalse($freshSession->isCSRFTokenValid('anything'));

        $this->_session->storeValueByName('csrfToken', '');
        $this->assertFalse($this->_session->isCSRFTokenValid('anything'));

        $this->assertFalse($this->_session->isCSRFTokenValid(null));
        $this->assertFalse($this->_session->isCSRFTokenValid(123));
        $this->assertFalse($this->_session->isCSRFTokenValid(false));
        $this->assertFalse($this->_session->isCSRFTokenValid(array()));

        $this->_session->storeValueByName('csrfToken', 123);
        $token = $this->_session->getCSRFToken();

        $this->assertTrue(is_string($token));
        $this->assertSame(64, strlen($token));
        $this->assertTrue(ctype_xdigit($token));
    }
}
?>
