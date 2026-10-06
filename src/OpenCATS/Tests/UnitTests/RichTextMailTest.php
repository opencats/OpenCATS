<?php

use PHPUnit\Framework\TestCase;
use PHPMailer\PHPMailer\PHPMailer;

include_once(LEGACY_ROOT . '/lib/Mailer.php');

/** The browser regression checks generate these compact HTML shapes. No transport is used. */
class RichTextMailTest extends TestCase
{
    private function renderHTMLMail($html)
    {
        if (!defined('MAIL_MAILER')) define('MAIL_MAILER', MAILER_MODE_SMTP);
        $transport = $this->getMockBuilder(PHPMailer::class)->onlyMethods(array('send'))->getMock();
        $transport->expects($this->once())->method('send')->willReturn(true);
        $reflection = new ReflectionClass(Mailer::class);
        $mailer = $reflection->newInstanceWithoutConstructor();
        $reflection->getProperty('_mailer')->setValue($mailer, $transport);
        $this->assertTrue($mailer->send(
            array('sender@example.test', 'Sender'),
            array(array('recipient@example.test', 'Recipient')),
            'Editor regression', $html, true, false
        ));
        return $transport->Body;
    }

    public function testCompactParagraphsAndListsDoNotGainEmailBreaks(): void
    {
        $html = '<p>Hello é &amp; text</p><p>Second</p><ol><li>One</li><li>Two</li></ol><ul><li>Three</li></ul>';
        $rendered = $this->renderHTMLMail($html);
        $this->assertStringContainsString($html, $rendered);
        $this->assertStringNotContainsString('<br', $rendered);
    }

    public function testExplicitBreaksAndPreWhitespaceKeepExistingMailerSemantics(): void
    {
        $html = "<p>First<br>Second</p><pre>  code\n    indented\n\n last</pre><p>normal\ntext  spaces</p>";
        $rendered = $this->renderHTMLMail($html);
        $this->assertSame(5, substr_count($rendered, '<br />'));
        $this->assertStringContainsString("<pre>  code<br />\n    indented<br />\n<br />\n last</pre>", $rendered);
        $this->assertStringContainsString("<p>normal<br />\ntext  spaces</p>", $rendered);
    }
}
