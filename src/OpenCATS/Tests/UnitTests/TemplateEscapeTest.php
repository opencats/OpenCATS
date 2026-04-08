<?php
use PHPUnit\Framework\TestCase;

if( !defined('LEGACY_ROOT') )
{
    define('LEGACY_ROOT', '.');
}

include_once(LEGACY_ROOT . '/lib/Template.php');

class TemplateEscapeTest extends TestCase
{
    function testEscapesScriptPayload()
    {
        $template = new Template();
        $payload = '<script>alert(1)</script>';

        ob_start();
        $template->_($payload);
        $output = ob_get_clean();

        $this->assertSame($output, '&lt;script&gt;alert(1)&lt;/script&gt;');
        $this->assertStringNotContainsString('<script>', $output);
    }

    function testEscapesCommonSpecialChars()
    {
        $template = new Template();
        $payload = 'If x < 2 & x > 0, x = "1".';

        ob_start();
        $template->_($payload);
        $output = ob_get_clean();

        $this->assertSame($output, 'If x &lt; 2 &amp; x &gt; 0, x = &quot;1&quot;.');
    }

    function testEscapesAmpersandsToPreventEntityBypass()
    {
        $template = new Template();
        $payload = '&lt;script&gt;alert(1)&lt;/script&gt;';

        ob_start();
        $template->_($payload);
        $output = ob_get_clean();

        $this->assertSame($output, '&amp;lt;script&amp;gt;alert(1)&amp;lt;/script&amp;gt;');
        $this->assertStringNotContainsString('<script>', $output);
    }
}
