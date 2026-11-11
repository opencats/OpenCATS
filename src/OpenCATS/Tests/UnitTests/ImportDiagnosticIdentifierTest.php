<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class ImportDiagnosticIdentifierTest extends AuthorizationTestCase
{
    private array $diagnosticLookups = array();
    private array $importRecord = array();
    private array $templateValues = array();

    protected function setUp(): void
    {
        parent::setUp();
        require_once './modules/import/ImportUI.php';

        // Keep the actual integer formatter and intercept reads and writes.
        $this->db = $this->getMockBuilder(DatabaseConnection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(array('getAssoc', 'getAllAssoc', 'query', 'escapeString', 'makeQueryString'))
            ->getMock();
        (new ReflectionProperty(DatabaseConnection::class, '_instance'))->setValue(null, $this->db);
        $this->db->method('getAssoc')->willReturnCallback(function ($sql) {
            if (str_contains($sql, 'import.import_id ='))
            {
                $this->diagnosticLookups[] = $sql;
                return $this->importRecord;
            }

            return array(); // Bulk attachment summary on the fallback page.
        });
        $this->db->expects(self::once())->method('getAllAssoc')
            ->willReturn(array(array('importID' => '42')));
        $this->db->method('query')->willReturnCallback(function ($sql) {
            $this->queries[] = $sql;
            return true;
        });
        $this->db->method('escapeString')->willReturnArgument(0);
        $this->db->method('makeQueryString')->willReturnCallback(fn($value) => "'" . $value . "'");
    }

    private function dispatch(string $expectedTemplate): void
    {
        $template = $this->createMock(Template::class);
        $template->method('assign')->willReturnCallback(function ($key, $value) {
            $this->templateValues[$key] = $value;
        });
        $template->expects(self::once())->method('display')->with($expectedTemplate);

        $ui = new ImportUI();
        (new ReflectionProperty(UserInterface::class, '_template'))->setValue($ui, $template);
        $ui->handleRequest();
    }

    public static function invalidIdentifiers(): array
    {
        return array(
            'missing' => array(array()),
            'empty' => array(array('importID' => '')),
            'zero' => array(array('importID' => '0')),
            'padded zero' => array(array('importID' => '00')),
            'negative' => array(array('importID' => '-42')),
            'non-numeric' => array(array('importID' => 'example')),
            'array' => array(array('importID' => array('42'))),
            'extra characters' => array(array('importID' => '42abc'))
        );
    }

    #[DataProvider('invalidIdentifiers')]
    public function testInvalidDiagnosticIdentifiersSkipTheLookup(array $parameters): void
    {
        $_GET = array('a' => 'viewerrors') + $parameters;

        $this->dispatch('./modules/import/Import1.tpl');

        self::assertSame(array(), $this->diagnosticLookups);
        self::assertSame(array(), $this->queries);
    }

    public function testDiagnosticLookupNormalizesTheSqlIdentifierAndPreservesEscaping(): void
    {
        $_GET = array('a' => 'viewerrors', 'importID' => '0042');
        $this->importRecord = array('importID' => '42', 'importErrors' => 'A < B & "quoted"');

        $this->dispatch('./modules/import/ImportRecent.tpl');

        self::assertCount(1, $this->diagnosticLookups);
        self::assertStringEndsWith('import.import_id = 42', $this->diagnosticLookups[0]);
        self::assertSame('0042', $this->templateValues['importID']);
        self::assertSame('A &lt; B &amp; &quot;quoted&quot;', $this->templateValues['importErrors']);
    }

    public function testMissingDiagnosticRecordKeepsTheEmptyDiagnosticView(): void
    {
        $_GET = array('a' => 'viewerrors', 'importID' => '43');

        $this->dispatch('./modules/import/ImportRecent.tpl');

        self::assertCount(1, $this->diagnosticLookups);
        self::assertStringEndsWith('import.import_id = 43', $this->diagnosticLookups[0]);
        self::assertSame('', $this->templateValues['importErrors']);
    }

    public function testRevertStillLooksUpAndRevertsTheSelectedImport(): void
    {
        $_GET = array('a' => 'revert');
        $_POST = array('postback' => 'postback', 'importID' => '42');
        $this->importRecord = array('importID' => '42', 'moduleName' => 'contact');

        $this->dispatch('./modules/import/ImportRecent.tpl');

        self::assertCount(1, $this->diagnosticLookups);
        self::assertStringEndsWith('import.import_id = 42', $this->diagnosticLookups[0]);
        self::assertSame(array(
            'DELETE FROM extra_field_settings WHERE import_id = 42',
            'DELETE FROM contact WHERE import_id = 42',
            'DELETE FROM extra_field WHERE import_id = 42',
            'DELETE FROM company WHERE import_id = 42',
            "DELETE FROM import WHERE import_id = '42'"
        ), array_map(fn($sql) => preg_replace('/\s+/', ' ', $sql), $this->queries));
        self::assertSame('The revert was successful.', $this->templateValues['successMessage']);
    }

    public function testRevertWithNoMatchingRecordDoesNotWrite(): void
    {
        $_GET = array('a' => 'revert');
        $_POST = array('postback' => 'postback', 'importID' => '43');

        $this->dispatch('./modules/import/Import1.tpl');

        self::assertCount(1, $this->diagnosticLookups);
        self::assertStringEndsWith('import.import_id = 43', $this->diagnosticLookups[0]);
        self::assertSame(array(), $this->queries);
    }
}
