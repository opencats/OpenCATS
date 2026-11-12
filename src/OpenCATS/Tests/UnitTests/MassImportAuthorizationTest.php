<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/../Support/AuthorizationTestCase.php';
require_once './constants.php';

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

    private function stageLegacyBulkResumes(): array
    {
        $_GET = array('a' => 'showMassImport');
        $this->importer()->handleRequest();
        return $_SESSION['CATS_LEGACY_BULK_RESUMES'];
    }

    private function processLegacyBulkResumes(?array $queue, array $request = array()): array
    {
        if (!is_link('lib'))
        {
            foreach (array('lib', 'constants.php', 'modules') as $path)
            {
                symlink($this->checkout . '/' . $path, $path);
            }
        }
        $process = proc_open(array(PHP_BINARY, __DIR__ . '/../Support/runAuthorizationAjax.php'),
            array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes, $this->checkout);
        fwrite($pipes[0], json_encode($request + array(
            'endpoint' => 'processMassImportItem', 'level' => ACCESS_LEVEL_SA,
            'queue' => $queue, 'fixture' => $this->fixture
        ), JSON_THROW_ON_ERROR));
        fclose($pipes[0]);
        $output = stream_get_contents($pipes[1]);
        $stderr = stream_get_contents($pipes[2]);
        fclose($pipes[1]);
        fclose($pipes[2]);
        self::assertSame(0, proc_close($process), $stderr . $output);
        self::assertSame('', $stderr);
        $result = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
        self::assertSame(array(), $result['issues']);
        return $result;
    }

    public function testLegacyBulkResumeStagingRequiresItsOwnAdministratorPermission(): void
    {
        $this->accessOverrides['import.massImport'] = ACCESS_LEVEL_SA;
        $this->accessOverrides['import.bulkResumes'] = ACCESS_LEVEL_EDIT;
        $_GET = array('a' => 'showMassImport');

        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->importer()->handleRequest());

        self::assertArrayNotHasKey('CATS_LEGACY_BULK_RESUMES', $_SESSION);
        self::assertDirectoryDoesNotExist('upload/bulkresumes');
    }

    public function testLegacyBulkResumeProcessingUsesOnlyTheStagedResume(): void
    {
        $this->accessOverrides['import.bulkResumes'] = ACCESS_LEVEL_SA;
        mkdir('upload/bulkresumes/nested', 0700, true);
        file_put_contents('upload/bulkresumes/nested/resume.txt', 'Short resume fixture');
        file_put_contents('upload/massimport/other.txt', self::RESUME);
        symlink('../../massimport/other.txt', 'upload/bulkresumes/nested/linked.txt');

        $queue = $this->stageLegacyBulkResumes();

        self::assertSame(array('upload/bulkresumes/nested/resume.txt'), array_keys($queue['files']));
        $result = $this->processLegacyBulkResumes($queue);
        self::assertSame('0,1,1', $result['output']);
        $writes = implode("\n", $result['writes']);
        self::assertMatchesRegularExpression('/INSERT INTO attachment\s*\(/', $writes);
        self::assertStringContainsString('Short resume fixture', $writes);
        self::assertDoesNotMatchRegularExpression('/INSERT INTO candidate\b/', $writes);
        self::assertFileExists('upload/massimport/other.txt');
    }

    public function testLegacyBulkResumeStagingDoesNotFollowALinkedStagingDirectory(): void
    {
        $this->accessLevel = ACCESS_LEVEL_SA;
        file_put_contents('upload/massimport/resume.txt', self::RESUME);
        symlink('massimport', 'upload/bulkresumes');

        $queue = $this->stageLegacyBulkResumes();

        self::assertSame(array(), $queue['files']);
        $result = $this->processLegacyBulkResumes($queue);
        self::assertSame('done', $result['output']);
        self::assertSame(array(), $result['writes']);
    }

    public static function rejectedBulkProcessingRequests(): array
    {
        return array(
            'no permission' => array(array('acl' => array('import.bulkResumes' => ACCESS_LEVEL_EDIT)), ERROR_NO_PERMISSION),
            'logged out' => array(array('loggedIn' => false), 'You are not logged in'),
            'GET' => array(array('method' => 'GET'), 'Invalid request.'),
            'bad token' => array(array('validToken' => false), 'Invalid request.')
        );
    }

    #[DataProvider('rejectedBulkProcessingRequests')]
    public function testLegacyBulkResumeProcessingKeepsRequestAndPermissionChecks($request, $message): void
    {
        $this->accessLevel = ACCESS_LEVEL_SA;
        mkdir('upload/bulkresumes', 0700);
        file_put_contents('upload/bulkresumes/resume.txt', self::RESUME);
        $result = $this->processLegacyBulkResumes($this->stageLegacyBulkResumes(), $request);

        self::assertStringContainsString($message, $result['output']);
        self::assertSame(array(), $result['writes']);
        self::assertFileExists('upload/bulkresumes/resume.txt');
    }

    public function testLegacyBulkResumeProcessingRejectsStaleAndOutOfScopeQueueEntries(): void
    {
        $this->accessLevel = ACCESS_LEVEL_SA;
        mkdir('upload/bulkresumes', 0700);
        foreach (array('replaced', 'removed', 'linked', 'different') as $name)
        {
            file_put_contents('upload/bulkresumes/' . $name . '.txt', self::RESUME);
        }
        file_put_contents('upload/massimport/other.txt', self::RESUME);
        $queue = $this->stageLegacyBulkResumes();

        // Keep the old inode live so the replacement cannot reuse it.
        rename('upload/bulkresumes/replaced.txt', 'upload/bulkresumes/old.txt');
        file_put_contents('upload/bulkresumes/replaced.txt', 'Replacement resume');
        unlink('upload/bulkresumes/removed.txt');
        unlink('upload/bulkresumes/linked.txt');
        symlink('../massimport/other.txt', 'upload/bulkresumes/linked.txt');
        $queue['files']['upload/bulkresumes/different.txt']['path'] = realpath('upload/bulkresumes/old.txt');
        $queue['files']['upload/massimport/other.txt'] = array('path' => realpath('upload/massimport/other.txt'));
        $queue['files']['upload/bulkresumes/invalid.txt'] = null;

        $result = $this->processLegacyBulkResumes($queue);
        self::assertSame('0,0,6', $result['output']);
        self::assertSame(array(), $result['writes']);
        self::assertSame(array(), $result['reads']);
        self::assertFileExists('upload/massimport/other.txt');

        foreach (array(null, array('userID' => 99, 'files' => $queue['files'])) as $invalidQueue)
        {
            $result = $this->processLegacyBulkResumes($invalidQueue);
            self::assertSame('No mass import in progress.', $result['output']);
            self::assertSame(array(), $result['writes']);
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
