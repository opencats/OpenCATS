<?php
use PHPUnit\Framework\TestCase;

include_once(LEGACY_ROOT . '/lib/Template.php');

class TemplateEscapeStringableFixture
{
    public function __toString()
    {
        return 'Stringable <value> "ok"';
    }
}

class TemplateEscapeTest extends TestCase
{
    public function testEscapeHtmlEscapesXssPayloadAndPreservesUnicode()
    {
        $payload = '"><script>alert(1)</script> plain text with ümlauts & quotes';
        $escaped = Template::escapeHtml($payload);

        $this->assertSame('&quot;&gt;&lt;script&gt;alert(1)&lt;/script&gt; plain text with ümlauts &amp; quotes', $escaped);
        $this->assertStringNotContainsString('<script>', $escaped);
    }

    public function testEscapeHtmlSubstitutesInvalidUtf8Sequences()
    {
        $payload = "invalid:\xC3\x28";
        $escaped = Template::escapeHtml($payload);

        $this->assertStringContainsString("invalid:", $escaped);
        $this->assertStringContainsString("\xEF\xBF\xBD", $escaped);
    }

    public function testEscapeAttrMatchesHtmlEscapingRules()
    {
        $payload = '\'"<tag attr="x">&';
        $escaped = Template::escapeAttr($payload);

        $this->assertSame('&#039;&quot;&lt;tag attr=&quot;x&quot;&gt;&amp;', $escaped);
    }

    public function testEscapeUrlBlocksDangerousSchemes()
    {
        $this->assertSame('', Template::escapeUrl('javascript:alert(1)'));
        $this->assertSame('', Template::escapeUrl('vbscript:msgbox(1)'));
        $this->assertSame('', Template::escapeUrl('data:text/html,<script>alert(1)</script>'));
        $this->assertSame('', Template::escapeUrl(" \nJaVaScRiPt:alert(1)"));
    }

    public function testEscapeUrlKeepsSafeRelativeAndAbsoluteUrls()
    {
        $this->assertSame('/path?q=1&amp;lang=de', Template::escapeUrl('/path?q=1&lang=de'));
        $this->assertSame('https://example.com/x?x=1&amp;y=2', Template::escapeUrl('https://example.com/x?x=1&y=2'));
    }

    public function testEscapeJsReturnsQuotedLiteralWithHexEscapes()
    {
        $payload = '</script><img src=x onerror=alert(1)> " \' &';
        $escaped = Template::escapeJs($payload);

        $this->assertSame('"', $escaped[0]);
        $this->assertSame('"', substr($escaped, -1));
        $this->assertStringContainsString('\u003C', $escaped);
        $this->assertStringContainsString('\u003E', $escaped);
        $this->assertStringContainsString('\u0022', $escaped);
        $this->assertStringContainsString('\u0027', $escaped);
        $this->assertStringContainsString('\u0026', $escaped);
        $this->assertStringNotContainsString('<', $escaped);
        $this->assertStringNotContainsString('>', $escaped);
    }

    public function testEscapeJsAlreadyIncludesOuterQuotes()
    {
        $this->assertSame('"safe value"', Template::escapeJs('safe value'));
    }

    public function testEscapeJsAttrEscapesForJsLiteralAndHtmlAttribute()
    {
        $payload = '"><script>alert(1)</script>';
        $escaped = Template::escapeJsAttr($payload);

        $this->assertStringStartsWith('&quot;', $escaped);
        $this->assertStringEndsWith('&quot;', $escaped);
        $this->assertStringContainsString('\u0022', $escaped);
        $this->assertStringContainsString('\u003Cscript\u003Ealert(1)\u003C\/script\u003E', $escaped);
        $this->assertStringNotContainsString('<script>', $escaped);
        $this->assertStringNotContainsString('"', $escaped);
        $this->assertStringNotContainsString("'", $escaped);
        $this->assertStringNotContainsString('<', $escaped);
        $this->assertStringNotContainsString('>', $escaped);
        $this->assertStringNotContainsString('&', str_replace('&quot;', '', $escaped));
    }

    public function testEscapeJsAttrEscapesScriptClosingPayloadForAttributeContext()
    {
        $payload = '</script><img src=x onerror=alert(1)>';
        $escaped = Template::escapeJsAttr($payload);

        $this->assertStringStartsWith('&quot;', $escaped);
        $this->assertStringEndsWith('&quot;', $escaped);
        $this->assertStringContainsString('\u003C\/script\u003E\u003Cimg src=x onerror=alert(1)\u003E', $escaped);
        $this->assertStringNotContainsString('</script>', $escaped);
        $this->assertStringNotContainsString('<img', $escaped);
        $this->assertStringNotContainsString('<', $escaped);
        $this->assertStringNotContainsString('>', $escaped);
        $this->assertStringNotContainsString('"', $escaped);
    }

    public function testTemplateUnderscoreRemainsHtmlEscapeWrapper()
    {
        $template = new Template();
        $payload = '</script><img src=x onerror=alert(1)>';

        ob_start();
        $template->_($payload);
        $output = ob_get_clean();

        $this->assertSame(Template::escapeHtml($payload), $output);
        $this->assertSame('&lt;/script&gt;&lt;img src=x onerror=alert(1)&gt;', $output);
    }

    public function testEscapingHelpersHandleNullNumbersAndStringableObjects()
    {
        $fixture = new TemplateEscapeStringableFixture();

        $this->assertSame('', Template::escapeHtml(null));
        $this->assertSame('12345', Template::escapeHtml(12345));
        $this->assertSame('Stringable &lt;value&gt; &quot;ok&quot;', Template::escapeHtml($fixture));

        $this->assertSame('', Template::escapeAttr(null));
        $this->assertSame('12345', Template::escapeAttr(12345));

        $this->assertSame('', Template::escapeUrl(null));
        $this->assertSame('12345', Template::escapeUrl(12345));

        $this->assertSame('""', Template::escapeJs(null));
        $this->assertSame('"12345"', Template::escapeJs(12345));
        $this->assertSame('"Stringable \\u003Cvalue\\u003E \\u0022ok\\u0022"', Template::escapeJs($fixture));
    }
}
