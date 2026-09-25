<?php

use PHPUnit\Framework\TestCase;

class InstallerPresentationTest extends TestCase
{
    private function render(string $kind): string
    {
        $command = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(dirname(__DIR__, 4) . '/test/scripts/renderInstaller.php') . ' ' . escapeshellarg($kind);
        exec($command . ' 2>&1', $output, $status);
        $html = implode("\n", $output);
        self::assertSame(0, $status, $html);
        self::assertDoesNotMatchRegularExpression('/PHP (?:Warning|Deprecated|Fatal error):|<b>(?:Warning|Deprecated|Fatal error)<\/b>:/', $html);
        return $html;
    }

    private function document(string $html): DOMXPath
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        $document->loadHTML($html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);
        return new DOMXPath($document);
    }

    public function testShellHasSevenOrderedStagesAndConfigurationContracts(): void
    {
        $xpath = $this->document($this->render('shell'));
        $stages = array('System Check', 'Database Connectivity', 'Loading Data', 'Setup Resume Indexing', 'Mail Settings', 'Loading Extras', 'Finishing Installation');
        foreach ($stages as $index => $stage)
        {
            $nodes = $xpath->query('//*[@id="step' . ($index + 1) . '"]');
            self::assertCount(1, $nodes);
            self::assertStringContainsString($stage, $nodes->item(0)->textContent);
        }
        foreach (array('dbname', 'dbuser', 'dbpass', 'dbhost', 'docExecutable', 'pdfExecutable', 'htmlExecutable', 'rtfExecutable', 'mailFromAddress', 'mailSupport', 'mailSmtpHost', 'mailSmtpPort', 'mailSmtpUsername', 'mailSmtpPassword', 'timeZone', 'dateFormat', 'timeFormat', 'defaultPhoneCountryCodeDigits') as $id)
        {
            self::assertCount(1, $xpath->query('//*[@id="' . $id . '"]'));
            self::assertCount(1, $xpath->query('//label[@for="' . $id . '"]'));
        }
        self::assertSame('localhost', $xpath->query('//*[@id="dbhost"]')->item(0)->getAttribute('value'));
        self::assertSame('password', $xpath->query('//*[@id="dbpass"]')->item(0)->getAttribute('type'));
        self::assertCount(5, $xpath->query('//select[@id="mailSupport"]/option'));
        self::assertCount(1, $xpath->query('//meta[@name="viewport"]'));
    }

    public function testNotInstalledAndUnsupportedPhpPagesRenderWithoutDatabase(): void
    {
        $xpath = $this->document($this->render('notinstalled'));
        self::assertCount(1, $xpath->query('//a[@href="installwizard.php"]'));
        $html = $this->render('unsupported');
        self::assertStringContainsString('8.4.1', $html);
        self::assertStringContainsString('Please install a supported PHP version', $html);
    }

    public function testInstallBlockPreventsStartingAndUnlockedStateStartsSystemChecks(): void
    {
        $blocked = $this->render('locked');
        self::assertStringContainsString("showTextBlock('installLocked')", $blocked);
        self::assertStringNotContainsString('Installpage_append', $blocked);
        $start = $this->render('start');
        self::assertStringContainsString("showTextBlock('startInstall')", $start);
        self::assertStringContainsString('a=installTest', $start);
    }

    public function testSystemChecksAndMissingDatabaseFieldsRenderResults(): void
    {
        $checks = $this->render('checks');
        self::assertStringContainsString('PHP version is', $checks);
        self::assertGreaterThan(5, $this->document($checks)->query('//table//tr/td')->length);
        $database = $this->render('missing-database');
        self::assertStringContainsString('Database name, user and host are required.', $database);
        self::assertStringContainsString("showTextBlock('MySQLTestFailed')", $database);
    }

    public function testPendingMigrationsPreservesAdministratorOnlyUpgradeLink(): void
    {
        $admin = $this->document($this->render('pending-admin'));
        self::assertCount(1, $admin->query('//a[@href="installwizard.php"]'));
        $user = $this->document($this->render('pending-user'));
        self::assertCount(0, $user->query('//a[@href="installwizard.php"]'));
        self::assertStringContainsString('Please contact your administrator', $user->document->textContent);
    }

    public function testInitialConfigurationVariantsPreserveForms(): void
    {
        foreach (array('password', 'localization', 'siteName', 'text', 'conclusion') as $kind)
        {
            $xpath = $this->document($this->render($kind));
            self::assertCount(1, $xpath->query('//form[@id="configurationForm" and @method="post"]'));
            self::assertCount(1, $xpath->query('//input[@type="submit"]'));
        }
    }
}
