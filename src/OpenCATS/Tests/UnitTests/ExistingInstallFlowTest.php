<?php
use PHPUnit\Framework\TestCase;

class ExistingInstallFlowTest extends TestCase
{
    private $uiSource;
    private $jsSource;

    protected function setUp(): void
    {
        $root = dirname(__DIR__, 4);

        $uiPath = $root . '/modules/install/ajax/ui.php';
        $jsPath = $root . '/js/install.js';

        $this->assertFileExists($uiPath);
        $this->assertFileExists($jsPath);

        $this->uiSource = file_get_contents($uiPath);
        $this->jsSource = file_get_contents($jsPath);
    }

    public function testUpgradeExistingActionSetsSessionFlagAndCallsExistingMaint()
    {
        $block = $this->extractSwitchCase($this->uiSource, 'upgradeExisting');
        $this->assertNotEmpty($block, 'case upgradeExisting must exist in ui.php');

        $this->assertStringContains(
            $block,
            "['existingUpgradeMaintStarted']",
            'upgradeExisting must set the existingUpgradeMaintStarted session flag'
        );

        $this->assertStringContains(
            $block,
            "unset(\$_SESSION['existingUpgradeMaintComplete'])",
            'upgradeExisting must clear the existingUpgradeMaintComplete flag'
        );

        $this->assertMatches(
            '/Installpage_upgradeExistingMaint\s*\(\s*\)/',
            $block,
            'upgradeExisting must invoke Installpage_upgradeExistingMaint()'
        );
    }

    public function testJsUpgradeExistingMaintSetsNextActionThenCallsMaint()
    {
        $this->assertMatches(
            '/function\s+Installpage_upgradeExistingMaint\s*\(/',
            $this->jsSource,
            'Installpage_upgradeExistingMaint() must be defined in install.js'
        );

        $fnBody = $this->extractJsFunction($this->jsSource, 'Installpage_upgradeExistingMaint');
        $this->assertNotEmpty($fnBody, 'Installpage_upgradeExistingMaint function body must be extractable');

        $this->assertMatches(
            '/installMaintNextAction\s*=\s*["\']a=upgradeExistingMaintComplete["\']/',
            $fnBody,
            'Installpage_upgradeExistingMaint must set installMaintNextAction to upgradeExistingMaintComplete'
        );

        $this->assertMatches(
            '/Installpage_maint\s*\(\s*\)/',
            $fnBody,
            'Installpage_upgradeExistingMaint must call Installpage_maint()'
        );

        $setPos = $this->firstMatchPosition(
            '/installMaintNextAction\s*=/',
            $fnBody
        );
        $callPos = $this->firstMatchPosition(
            '/Installpage_maint\s*\(/',
            $fnBody
        );
        $this->assertNotNull($setPos);
        $this->assertNotNull($callPos);
        $this->assertGreaterThan(
            $setPos,
            $callPos,
            'installMaintNextAction must be set before Installpage_maint() is called'
        );
    }

    public function testUpgradeExistingMaintCompleteResumesInstaller()
    {
        $block = $this->extractSwitchCase($this->uiSource, 'upgradeExistingMaintComplete');
        $this->assertNotEmpty($block, 'case upgradeExistingMaintComplete must exist in ui.php');

        $this->assertStringContains(
            $block,
            "['existingUpgradeMaintComplete']",
            'upgradeExistingMaintComplete must reference the existingUpgradeMaintComplete session key'
        );

        $this->assertMatches(
            '/a=resumeParsing/',
            $block,
            'upgradeExistingMaintComplete must redirect to a=resumeParsing'
        );
    }

    public function testMaintActionSkipsSecondRunAfterExistingUpgrade()
    {
        $block = $this->extractSwitchCase($this->uiSource, 'maint');
        $this->assertNotEmpty($block, 'case maint must exist in ui.php');

        $this->assertStringContains(
            $block,
            "['existingUpgradeMaintComplete']",
            'maint must check for the existingUpgradeMaintComplete session flag'
        );

        $this->assertMatches(
            '/unset\s*\(\s*\$_SESSION\s*\[\s*[\'"]existingUpgradeMaintStarted[\'"]\s*\]\s*\)/',
            $block,
            'maint must unset existingUpgradeMaintStarted'
        );

        $this->assertMatches(
            '/unset\s*\(\s*\$_SESSION\s*\[\s*[\'"]existingUpgradeMaintComplete[\'"]\s*\]\s*\)/',
            $block,
            'maint must unset existingUpgradeMaintComplete'
        );

        $this->assertMatches(
            '/a=reindexResumes/',
            $block,
            'maint must continue to a=reindexResumes when skipping existing-upgrade maintenance'
        );

        $normalMaintPattern = '/Installpage_maint\s*\(\s*\)/';
        $this->assertMatches(
            $normalMaintPattern,
            $block,
            'maint must still contain the normal Installpage_maint() call for non-existing-upgrade paths'
        );
    }

    public function testMaintContinuationRequiresSuccessfulHttpStatus()
    {
        $fnBody = $this->extractJsFunction($this->jsSource, 'Installpage_maint');
        $this->assertNotEmpty($fnBody, 'Installpage_maint function body must be extractable');

        $statusPos = $this->firstMatchPosition('/http\\.status\\s*==\\s*200/', $fnBody);
        $populatePos = $this->firstMatchPosition('/Installpage_populate\\s*\\(\\s*installMaintNextAction\\s*\\)/', $fnBody);
        $resetPos = $this->firstMatchPosition('/installMaintNextAction\\s*=\\s*[\'"]a=reindexResumes[\'"]\\s*;/', $fnBody);

        $this->assertNotNull($statusPos, 'Installpage_maint must check for a successful HTTP status before continuing');
        $this->assertNotNull($populatePos, 'Installpage_maint must continue using installMaintNextAction after successful maintenance');
        $this->assertNotNull($resetPos, 'Installpage_maint must reset installMaintNextAction after using it');

        $this->assertGreaterThan(
            $statusPos,
            $populatePos,
            'Installpage_maint must check the HTTP status before using installMaintNextAction'
        );

        $this->assertGreaterThan(
            $populatePos,
            $resetPos,
            'Installpage_maint must reset installMaintNextAction after using it'
        );
    }

    public function testResetDatabaseClearsExistingUpgradeFlags()
    {
        $block = $this->extractSwitchCase($this->uiSource, 'resetDatabase');
        $this->assertNotEmpty($block, 'case resetDatabase must exist in ui.php');

        $this->assertMatches(
            '/unset\s*\(\s*\$_SESSION\s*\[\s*[\'"]existingUpgradeMaintStarted[\'"]\s*\]\s*\)/',
            $block,
            'resetDatabase must clear existingUpgradeMaintStarted'
        );

        $this->assertMatches(
            '/unset\s*\(\s*\$_SESSION\s*\[\s*[\'"]existingUpgradeMaintComplete[\'"]\s*\]\s*\)/',
            $block,
            'resetDatabase must clear existingUpgradeMaintComplete'
        );
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function extractSwitchCase(string $source, string $caseName): string
    {
        $pattern = '/case\s+[\'"]' . preg_quote($caseName, '/') . '[\'"]\s*:/';
        if (!preg_match($pattern, $source, $m, PREG_OFFSET_CAPTURE)) {
            return '';
        }

        $start = $m[0][1];
        $bodyStart = $start + strlen($m[0][0]);
        $len = strlen($source);
        $depth = 0;

        for ($i = $bodyStart; $i < $len; $i++) {
            $ch = $source[$i];

            if ($ch === '{') {
                $depth++;
            } elseif ($ch === '}') {
                $depth--;
                if ($depth < 0) {
                    return substr($source, $start, $i - $start);
                }
            }

            if ($depth === 0 && ($ch === 'c' || $ch === 'd')) {
                if (preg_match('/^(?:case\s+[\'"]|default\s*:)/', substr($source, $i))) {
                    return substr($source, $start, $i - $start);
                }
            }
        }

        return substr($source, $start);
    }

    private function extractJsFunction(string $source, string $funcName): string
    {
        $pattern = '/function\s+' . preg_quote($funcName, '/') . '\s*\([^)]*\)\s*\{/';
        if (!preg_match($pattern, $source, $m, PREG_OFFSET_CAPTURE)) {
            return '';
        }

        $braceStart = strpos($source, '{', $m[0][1]);
        $depth = 0;
        $i = $braceStart;
        $len = strlen($source);

        while ($i < $len) {
            if ($source[$i] === '{') {
                $depth++;
            } elseif ($source[$i] === '}') {
                $depth--;
                if ($depth === 0) {
                    return substr($source, $braceStart, $i - $braceStart + 1);
                }
            }
            $i++;
        }

        return '';
    }

    private function firstMatchPosition(string $pattern, string $subject): ?int
    {
        if (preg_match($pattern, $subject, $m, PREG_OFFSET_CAPTURE)) {
            return $m[0][1];
        }
        return null;
    }

    private function assertStringContains(string $haystack, string $needle, string $message = ''): void
    {
        $this->assertTrue(
            strpos($haystack, $needle) !== false,
            $message ?: "Failed asserting that string contains '{$needle}'"
        );
    }

    private function assertMatches(string $pattern, string $string, string $message = ''): void
    {
        $this->assertTrue(
            (bool) preg_match($pattern, $string),
            $message ?: "Failed asserting that string matches pattern {$pattern}"
        );
    }
}
