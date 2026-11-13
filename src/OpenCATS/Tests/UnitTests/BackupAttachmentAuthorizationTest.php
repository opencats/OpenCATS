<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/../Support/AuthorizationTestCase.php';
require_once './constants.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class BackupAttachmentAuthorizationTest extends AuthorizationTestCase
{
    private const FILE_CONTENT = 'Synthetic attachment content';
    private string $fixture;

    protected function setUp(): void
    {
        parent::setUp();
        require_once './lib/DataGrid.php';
        require_once './modules/attachments/AttachmentsUI.php';
        require_once './modules/companies/CompaniesUI.php';
        require_once './modules/settings/SettingsUI.php';

        $this->accessLevel = ACCESS_LEVEL_DELETE;
        $this->session->method('getSiteCompanyID')->willReturn(7);
        $this->fixture = sys_get_temp_dir() . '/opencats-backup-' . bin2hex(random_bytes(8));
        mkdir($this->fixture . '/attachments/test-archive', 0700, true);
        symlink(getcwd() . '/lib', $this->fixture . '/lib');
    }

    protected function tearDown(): void
    {
        $archive = $this->fixture . '/attachments/test-archive';
        if (is_dir($archive))
        {
            FileUtility::recursivelyRemoveDirectory($archive);
        }
        rmdir($this->fixture . '/attachments');
        unlink($this->fixture . '/lib');
        rmdir($this->fixture);
        parent::tearDown();
    }

    private function attachment($contentType = 'catsbackup', $dataItemType = DATA_ITEM_COMPANY): array
    {
        $filename = $contentType === 'catsbackup' ? 'catsbackup.bak' : 'resume.txt';
        return array(
            'attachmentID' => '42', 'dataItemType' => (string) $dataItemType, 'dataItemID' => '7',
            'directoryName' => 'test-archive', 'storedFilename' => $filename,
            'originalFilename' => $filename, 'contentType' => $contentType
        );
    }

    private function requestInFixture(string $endpoint, array $attachment, int $accessLevel): array
    {
        file_put_contents($this->fixture . '/attachments/test-archive/' . $attachment['storedFilename'], self::FILE_CONTENT);
        $process = proc_open(array(PHP_BINARY, __DIR__ . '/../Support/runAuthorizationAjax.php'),
            array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
        fwrite($pipes[0], json_encode(array(
            'endpoint' => $endpoint, 'attachment' => $attachment, 'rows' => array($attachment),
            'parent' => array('isAdminHidden' => 0), 'level' => $accessLevel, 'fixture' => $this->fixture
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
        self::assertNull($result['fatal']);
        return $result;
    }

    public static function allowedDownloads(): array
    {
        return array(
            'admin backup' => array('catsbackup', DATA_ITEM_COMPANY, ACCESS_LEVEL_SA),
            'ordinary company document' => array('text/plain', DATA_ITEM_COMPANY, ACCESS_LEVEL_READ),
            'visible candidate document' => array('text/plain', DATA_ITEM_CANDIDATE, ACCESS_LEVEL_READ)
        );
    }

    #[DataProvider('allowedDownloads')]
    public function testAuthorizedDownloadStreamsTheAttachment($contentType, $dataItemType, $level): void
    {
        $result = $this->requestInFixture('getAttachment', $this->attachment($contentType, $dataItemType), $level);

        self::assertSame(self::FILE_CONTENT, $result['output']);
        self::assertSame(array(), $result['writes']);
    }

    public function testNonAdministratorCannotDownloadBackup(): void
    {
        $_GET = array('a' => 'getAttachment', 'id' => '42', 'directoryNameHash' => md5('test-archive'));
        $this->db->method('getAssoc')->willReturn($this->attachment());

        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->ui(AttachmentsUI::class)->handleRequest());

        self::assertSame(array(), $this->queries);
    }

    public function testHiddenCandidateStillRequiresCandidateVisibility(): void
    {
        $_GET = array('a' => 'getAttachment', 'id' => '42', 'directoryNameHash' => md5('test-archive'));
        $this->db->method('getAssoc')->willReturnCallback(fn($sql) => str_contains($sql, 'FROM') && str_contains($sql, 'attachment_id AS attachmentID')
            ? $this->attachment('text/plain', DATA_ITEM_CANDIDATE) : array('isAdminHidden' => 1));

        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->ui(AttachmentsUI::class)->handleRequest());
    }

    public static function settingsActions(): array
    {
        return array(array('createBackup'), array('deleteBackup'));
    }

    #[DataProvider('settingsActions')]
    public function testNonAdministratorCannotListOrDeleteThroughSettings(string $action): void
    {
        $_GET['a'] = $action;
        $_POST['postback'] = 'postback';
        $this->db->method('getAllAssoc')->willReturnCallback(fn() => self::fail('Backup records were queried.'));

        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->ui(SettingsUI::class)->handleRequest());

        self::assertSame(array(), $this->queries);
    }

    public function testAdministratorSettingsListUsesProtectedDownloadLinks(): void
    {
        $this->accessLevel = ACCESS_LEVEL_SA;
        $_GET['a'] = 'createBackup';
        $backup = $this->attachment();
        $this->db->method('getAllAssoc')->willReturnCallback(function ($sql) use ($backup) {
            self::assertStringContainsString("content_type = 'catsbackup'", $sql);
            return array($backup);
        });

        file_put_contents($this->fixture . '/attachments/test-archive/catsbackup.bak', self::FILE_CONTENT);
        $renderer = $this->createMock(Template::class);
        $renderer->method('assign')->willReturnCallback(function ($name, $value) {
            if ($name === 'attachmentsRS')
            {
                self::assertCount(1, $value);
                self::assertStringContainsString('?m=attachments&amp;a=getAttachment&amp;id=42', $value[0]['retrievalURL']);
                self::assertSame('attachments/test-archive/catsbackup.bak', $value[0]['retrievalURLLocal']);
            }
        });
        $renderer->expects(self::once())->method('display')->with('./modules/settings/Backup.tpl');
        $ui = $this->ui(SettingsUI::class);
        (new ReflectionProperty(UserInterface::class, '_template'))->setValue($ui, $renderer);
        $checkout = getcwd();
        chdir($this->fixture);
        try
        {
            $ui->handleRequest();
        }
        finally
        {
            chdir($checkout);
        }
    }

    public function testAdministratorCanDeleteArchiveThroughSettings(): void
    {
        $result = $this->requestInFixture('deleteBackup', $this->attachment(), ACCESS_LEVEL_SA);

        self::assertCount(1, $result['writes']);
        self::assertStringContainsString("content_type = 'catsbackup'", $result['writes'][0]);
        self::assertFileDoesNotExist($this->fixture . '/attachments/test-archive/catsbackup.bak');
    }

    public function testCompanyViewListsOnlyOrdinaryAttachments(): void
    {
        $_GET = array('a' => 'show', 'companyID' => '7');
        require_once './lib/MRU.php';
        $this->session->method('getMRU')->willReturn($this->createStub(MRU::class));
        $this->db->method('getAssoc')->willReturn(array(
            'name' => 'Fixture company', 'city' => '', 'state' => '', 'notes' => '', 'isHot' => 0
        ));
        $ordinary = $this->attachment(null);
        $this->db->method('getAllAssoc')->willReturnCallback(function ($sql) use ($ordinary) {
            if (!preg_match('/FROM\s+attachment\b/', $sql)) return array();
            self::assertStringContainsString("content_type IS NULL OR content_type <> 'catsbackup'", $sql);
            return array($ordinary);
        });
        $renderer = $this->createMock(Template::class);
        $renderer->method('assign')->willReturnCallback(function ($name, $value) {
            if ($name === 'attachmentsRS')
            {
                self::assertSame(array('resume.txt'), array_column($value, 'originalFilename'));
                self::assertStringContainsString('?m=attachments&amp;a=getAttachment', $value[0]['retrievalLink']);
            }
        });
        $renderer->expects(self::once())->method('display')->with('./modules/companies/Show.tpl');
        $ui = $this->ui(CompaniesUI::class);
        (new ReflectionProperty(UserInterface::class, '_template'))->setValue($ui, $renderer);

        $ui->handleRequest();
    }

    public function testCompanyDeleteRejectsArchiveAndAllowsOrdinaryAttachment(): void
    {
        $_GET['a'] = 'deleteAttachment';
        $_POST = array('postback' => 'postback', 'companyID' => '7', 'attachmentID' => '42');
        $_SESSION['hooks']['CLIENTS_ON_DELETE_ATTACHMENT_POST'] = array('return false;');
        $backup = true;
        $this->db->method('getAssoc')->willReturnCallback(function ($sql) use (&$backup) {
            if (preg_match('/FROM\s+company\b/', $sql)) return array('companyID' => '7');
            return $this->attachment($backup ? 'catsbackup' : null);
        });

        $this->assertCommonError(COMMONERROR_BADINDEX, fn() => $this->ui(CompaniesUI::class)->handleRequest());
        self::assertSame(array(), $this->queries);

        $backup = false;
        $this->ui(CompaniesUI::class)->handleRequest();
        self::assertCount(1, $this->queries);
    }

    public function testSharedDeleteHelpersProtectArchivesFromOtherCallers(): void
    {
        $this->db->method('getAssoc')->willReturn($this->attachment());
        $this->db->method('getAllAssoc')->willReturnCallback(function ($sql) {
            self::assertStringContainsString("content_type <> 'catsbackup'", $sql);
            return array();
        });
        $attachments = new Attachments();

        self::assertFalse($attachments->delete(42));
        $attachments->deleteAll(DATA_ITEM_COMPANY, 7, "AND content_type = 'catsbackup'");

        self::assertSame(array(), $this->queries);
    }
}
