<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class JobOrderAttachmentAuthorizationTest extends AuthorizationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once './lib/DataGrid.php';
        require_once './modules/joborders/JobOrdersUI.php';
        $this->accessLevel = ACCESS_LEVEL_DELETE;
        $_GET['a'] = 'deleteAttachment';
        $_POST = array('postback' => 'postback', 'jobOrderID' => '7', 'attachmentID' => '42');
        // Stop after the real delete rather than issuing a redirect and exiting PHP.
        $_SESSION['hooks']['JO_ON_DELETE_ATTACHMENT_POST'] = array('return false;');
    }

    public static function invalidParents(): array
    {
        return array(array('candidate', 7), array('company', 7), array('contact', 7),
            array('joborder', 8), array('missing', 7));
    }

    private function attachment($type, $parent): array
    {
        if ($type === 'missing') return array();
        return array('attachmentID' => '42', 'dataItemType' => (string) constant('DATA_ITEM_' . strtoupper($type)),
            'dataItemID' => (string) $parent, 'directoryName' => '');
    }

    #[DataProvider('invalidParents')]
    public function testRejectsAttachmentOutsideRequestedJobOrder($type, $parent): void
    {
        $this->db->method('getAssoc')->willReturn($this->attachment($type, $parent));
        $this->assertCommonError(COMMONERROR_BADINDEX, fn() => $this->ui(JobOrdersUI::class)->handleRequest());
        self::assertSame(array(), $this->queries);
    }

    public function testDeletesMatchingAttachmentWithStringDatabaseIDs(): void
    {
        $this->db->method('getAssoc')->willReturn($this->attachment('joborder', 7));
        $this->ui(JobOrdersUI::class)->handleRequest();
        self::assertCount(1, $this->queries);
        self::assertMatchesRegularExpression('/DELETE FROM\s+attachment\s+WHERE\s+attachment_id = 42/', $this->queries[0]);
    }

    public function testDeleteAclIsStillRequired(): void
    {
        $this->accessOverrides['joborders.deleteAttachment'] = ACCESS_LEVEL_EDIT;
        $this->db->method('getAssoc')->willReturn($this->attachment('joborder', 7));
        $this->assertCommonError(COMMONERROR_PERMISSION, fn() => $this->ui(JobOrdersUI::class)->handleRequest());
        self::assertSame(array(), $this->queries);
    }
}
