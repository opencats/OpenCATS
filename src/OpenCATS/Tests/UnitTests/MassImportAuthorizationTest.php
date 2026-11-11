<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class MassImportAuthorizationTest extends AuthorizationTestCase
{
    private string $checkout;
    private string $fixture;
    private const RESUME = 'Example resume with enough content to pass the import queue size threshold.';

    protected function setUp(): void
    {
        parent::setUp();
        require_once './lib/CommonErrors.php';
        require_once './modules/import/ImportUI.php';

        $this->checkout = getcwd();
        $this->fixture = sys_get_temp_dir() . '/opencats-massimport-' . bin2hex(random_bytes(8));
        mkdir($this->fixture . '/upload/massimport/nested', 0700, true);
        mkdir($this->fixture . '/upload/massimport-other', 0700);
        // Load the SOAP contract locally without exposing a callable parse operation.
        mkdir($this->fixture . '/wsdl', 0700);
        $wsdl = file_get_contents('wsdl/parse.wsdl');
        file_put_contents($this->fixture . '/wsdl/parse.wsdl', str_replace(
            'name="DocumentParse"', 'name="DisabledForTest"', $wsdl
        ));
        chdir($this->fixture);
    }

    protected function tearDown(): void
    {
        chdir($this->checkout);
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->fixture, FilesystemIterator::SKIP_DOTS),
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
        rmdir($this->fixture);
        parent::tearDown();
    }

    private function importer(?Template $template = null): ImportUI
    {
        $ui = new ImportUI();
        (new ReflectionProperty(UserInterface::class, '_template'))->setValue(
            $ui, $template ?? $this->createStub(Template::class)
        );
        return $ui;
    }

    private function startImport(): array
    {
        $_GET = array('a' => 'massImport', 'step' => '2');
        $this->importer()->handleRequest();
        return $_SESSION['CATS_MASS_IMPORT']['files'] ?? array();
    }

    private function convert($documentID, array $extra = array()): string
    {
        $_GET = array('a' => 'massImportDocument', 'documentID' => $documentID) + $extra;
        ob_start();
        try
        {
            $this->importer()->handleRequest();
            return ob_get_contents();
        }
        finally
        {
            ob_end_clean();
        }
    }

    public static function protectedRoutes(): array
    {
        return array(
            array('massImportDocument', array()),
            array('massImportEdit', array()),
            array('massImportEdit', array('postback' => '1')),
        );
    }

    #[DataProvider('protectedRoutes')]
    public function testHandlersRequireMassImportEditAccess($action, $params): void
    {
        $this->accessLevel = ACCESS_LEVEL_SA;
        $this->accessOverrides['import.massImport'] = ACCESS_LEVEL_READ;
        $_GET = array('a' => $action) + $params;

        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->importer()->handleRequest());
        self::assertArrayNotHasKey('CATS_PARSE_TEMP', $_SESSION);
        self::assertSame(array(), $this->queries);
    }

    public function testCurrentImportConvertsEditsAndCommitsItsValidatedResume(): void
    {
        $resume = 'upload/massimport/nested/resume.txt';
        $changed = 'upload/massimport/changed.txt';
        $outside = 'upload/massimport-other/unrelated.txt';
        file_put_contents($resume, self::RESUME);
        file_put_contents($changed, self::RESUME);
        file_put_contents($outside, str_repeat('Unrelated fixture. ', 6));

        $records = $this->startImport();
        self::assertCount(2, $records);
        $ids = array_flip(array_map(fn($record) => $record['realName'], $records));
        $resumeID = $ids['resume.txt'];
        $changedID = $ids['changed.txt'];

        self::assertSame('Ok', $this->convert($resumeID, array('name' => $outside, 'type' => DOCUMENT_TYPE_UNKNOWN)));
        self::assertSame('Ok', $this->convert($resumeID));
        self::assertSame('Ok', $this->convert($changedID));
        self::assertCount(2, $_SESSION['CATS_PARSE_TEMP']);

        $_GET = array('a' => 'massImportEdit', 'documentID' => $resumeID);
        $template = $this->createMock(Template::class);
        $template->expects(self::once())->method('display')->with('./modules/import/MassImportEdit.tpl');
        $this->importer($template)->handleRequest();
        self::assertStringContainsString(self::RESUME, $_SESSION['CATS_PARSE_TEMP'][$resumeID]['contents']);

        $_GET['postback'] = '1';
        $_POST = array('skills' => 'Fixture editing');
        $this->importer()->handleRequest();
        self::assertSame('Fixture editing', $_SESSION['CATS_PARSE_TEMP'][$resumeID]['parse']['skills']);

        unlink($changed);
        symlink($this->fixture . '/' . $outside, $changed);
        self::assertSame('Fail', $this->convert($changedID));
        $_GET = array('a' => 'massImportEdit', 'documentID' => $changedID);
        $this->assertCommonError(COMMONERROR_BADINDEX, fn() => $this->importer()->handleRequest());

        $_GET = array('a' => 'massImport', 'step' => '4');
        $this->importer()->handleRequest();
        $attachments = glob('attachments/site_1/*/*/resume.txt');
        self::assertCount(1, $attachments);
        self::assertSame(self::RESUME, file_get_contents($attachments[0]));
        self::assertSame(array(), glob('attachments/site_1/*/*/changed.txt'));
        self::assertFileDoesNotExist($resume);
        self::assertFileExists($outside);
        self::assertArrayNotHasKey('CATS_MASS_IMPORT', $_SESSION);
        self::assertArrayNotHasKey('CATS_PARSE_TEMP', $_SESSION);
    }

    public function testQueueRejectsUnregisteredAndStaleReferences(): void
    {
        file_put_contents('upload/massimport/resume.txt', self::RESUME);
        file_put_contents('upload/massimport-other/unrelated.txt', self::RESUME);
        symlink('../massimport-other/unrelated.txt', 'upload/massimport/link.txt');
        symlink('../massimport-other', 'upload/massimport/linked-dir');

        $first = $this->startImport();
        self::assertCount(1, $first);
        $current = $this->startImport();
        self::assertCount(1, $current);

        self::assertSame('Fail', $this->convert(array_key_first($first)));
        self::assertSame('Fail', $this->convert(array('invalid')));
        self::assertSame('Fail', $this->convert('upload/massimport/resume.txt'));
        self::assertArrayNotHasKey('CATS_PARSE_TEMP', $_SESSION);

        $currentID = array_key_first($current);
        self::assertSame('Ok', $this->convert($currentID));
        $_GET = array('a' => 'massImportEdit', 'documentID' => array_key_first($first), 'postback' => '1');
        $this->assertCommonError(COMMONERROR_BADINDEX, fn() => $this->importer()->handleRequest());
        self::assertCount(1, $_SESSION['CATS_PARSE_TEMP']);

        $otherUser = $this->createStub(CATSSession::class);
        $otherUser->method('getUserID')->willReturn(11);
        $otherUser->method('getAccessLevel')->willReturn(ACCESS_LEVEL_EDIT);
        $_SESSION['CATS'] = $otherUser;
        self::assertSame('Fail', $this->convert($currentID));
    }
}
