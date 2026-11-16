<?php

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

require_once './constants.php';

class ActivityPipelineAuthorizationTest extends TestCase
{
    private function request(array $scenario): array
    {
        $process = proc_open(array(PHP_BINARY, __DIR__ . '/../Support/runAuthorizationAjax.php'),
            array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
        fwrite($pipes[0], json_encode($scenario, JSON_THROW_ON_ERROR));
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

    private function activity($type = 100): array
    {
        // Current user is 10; another user's authorship must never prevent access.
        return array('activityID' => 42, 'dataItemID' => 7, 'dataItemType' => $type, 'jobOrderID' => null,
            'enteredBy' => 99, 'enteredByFullName' => 'Other User', 'type' => 400, 'typeDescription' => 'Other',
            'notes' => 'Colleague notes', 'regarding' => 'General', 'dateCreated' => '10-02-26');
    }

    public static function activityPermissions(): array
    {
        return array(
            'candidate edit denied despite contact permission' => array('editActivity', 100, array('candidates.edit' => 0, 'contacts.editActivity' => ACCESS_LEVEL_DELETE), false),
            'candidate delete requires delete level' => array('deleteActivity', 100, array('candidates.delete' => ACCESS_LEVEL_EDIT, 'contacts.deleteActivity' => ACCESS_LEVEL_DELETE), false),
            'candidate edit independent of contact ACL' => array('editActivity', 100, array('candidates.edit' => ACCESS_LEVEL_EDIT, 'contacts.editActivity' => 0), true),
            'candidate delete independent of contact ACL' => array('deleteActivity', 100, array('candidates.delete' => ACCESS_LEVEL_DELETE, 'contacts.deleteActivity' => 0), true),
            'contact edit denied' => array('editActivity', 300, array('contacts.editActivity' => 0), false),
            'contact delete denied' => array('deleteActivity', 300, array('contacts.deleteActivity' => 0), false),
            'contact edit collaborative' => array('editActivity', 300, array('contacts.editActivity' => ACCESS_LEVEL_EDIT), true),
            'contact delete retains edit threshold' => array('deleteActivity', 300, array('contacts.deleteActivity' => ACCESS_LEVEL_EDIT), true),
            'company activity is unsupported' => array('editActivity', 200, array(), false),
            'job activity is unsupported' => array('deleteActivity', 400, array(), false),
            'unsupported type' => array('deleteActivity', 999, array(), false),
            'candidate read disabled' => array('editActivity', 100, array('candidates.show' => 0), false)
        );
    }

    #[DataProvider('activityPermissions')]
    public function testActivityUsesActualParentAclAndAllowsColleagues($endpoint, $type, $acl, $allowed): void
    {
        $result = $this->request(array('endpoint' => $endpoint, 'activity' => $this->activity($type), 'acl' => $acl,
            'parent' => array('isAdminHidden' => 0)));
        $this->assertMutation($result, $allowed, $endpoint === 'deleteActivity' ? 'DELETE FROM' : 'UPDATE');
    }

    private function assertMutation($result, $allowed, $verb): void
    {
        if ($allowed)
        {
            self::assertStringContainsString('<errorcode>0</errorcode>', $result['output']);
            self::assertStringStartsWith($verb, $result['writes'][0]);
        }
        else
        {
            self::assertStringContainsString('<errorcode>-1</errorcode>', $result['output']);
            self::assertSame(array(), $result['writes']);
        }
    }

    public static function hiddenActivityParents(): array
    {
        $cases = array();
        foreach (array('editActivity', 'deleteActivity') as $endpoint)
        {
            foreach (array(100 => 'candidates.hidden') as $type => $permission)
            {
                foreach (array(false, true) as $admin) $cases[] = array($endpoint, $type, $permission, $admin);
            }
        }
        return $cases;
    }

    #[DataProvider('hiddenActivityParents')]
    public function testHiddenActivityParentUsesExistingAdministratorRule($endpoint, $type, $permission, $admin): void
    {
        $result = $this->request(array('endpoint' => $endpoint, 'activity' => $this->activity($type), 'level' => ACCESS_LEVEL_DELETE,
            'acl' => array($permission => $admin ? ACCESS_LEVEL_SA : ACCESS_LEVEL_DELETE), 'parent' => array('isAdminHidden' => 1)));
        $this->assertMutation($result, $admin, $endpoint === 'deleteActivity' ? 'DELETE FROM' : 'UPDATE');
    }

    public static function missingActivityObjects(): array
    {
        return array(array('editActivity', false), array('deleteActivity', false),
            array('editActivity', true), array('deleteActivity', true));
    }

    #[DataProvider('missingActivityObjects')]
    public function testMissingActivityOrParentCannotBeModified($endpoint, $hasActivity): void
    {
        $result = $this->request(array('endpoint' => $endpoint, 'level' => ACCESS_LEVEL_DELETE,
            'activity' => $hasActivity ? $this->activity() : array(), 'parent' => array()));
        $this->assertMutation($result, false, '');
    }

    public static function pipelineParents(): array
    {
        return array(
            'visible collaborative rating' => array(0, 0, array(), true),
            'hidden candidate' => array(1, 0, array(), false),
            'hidden job order' => array(0, 1, array(), false),
            'administrator can rate hidden parents' => array(1, 1, array('candidates.hidden' => ACCESS_LEVEL_SA, 'joborders.hidden' => ACCESS_LEVEL_SA), true),
            'candidate read disabled' => array(0, 0, array('candidates.show' => 0), false),
            'job read disabled' => array(0, 0, array('joborders.show' => 0), false),
            'rating ACL still required' => array(0, 0, array('pipelines.editRating' => 0), false)
        );
    }

    #[DataProvider('pipelineParents')]
    public function testRatingChecksParentAccessWithoutCreatorOwnership($candidateHidden, $jobHidden, $acl, $allowed): void
    {
        $result = $this->request(array('endpoint' => 'setCandidateJobOrderRating', 'acl' => $acl,
            'pipeline' => array('candidateID' => 7, 'isAdminHidden' => $candidateHidden,
                'isJobOrderAdminHidden' => $jobHidden, 'addedBy' => 99)));
        $this->assertMutation($result, $allowed, 'UPDATE');
    }

    public function testMissingPipelineCannotBeRated(): void
    {
        $result = $this->request(array('endpoint' => 'setCandidateJobOrderRating'));
        $this->assertMutation($result, false, '');
    }

    public static function jobOrderPipelineReaders(): array
    {
        return array(
            'hidden job order denied to staff' => array(1, ACCESS_LEVEL_EDIT, array(), false),
            'hidden job order allowed to administrator' => array(1, ACCESS_LEVEL_SA, array(), true),
            'visible job order allowed to reader' => array(0, ACCESS_LEVEL_READ, array(), true),
            'missing job order' => array(null, ACCESS_LEVEL_EDIT, array(), false),
            'job order read denied' => array(0, ACCESS_LEVEL_EDIT, array('joborders.show' => 0), false),
            'candidate read denied' => array(0, ACCESS_LEVEL_EDIT, array('candidates.show' => 0), false)
        );
    }

    #[DataProvider('jobOrderPipelineReaders')]
    public function testJobOrderPipelineChecksReadAccessBeforeLoadingRows($hidden, $level, $acl, $allowed): void
    {
        $visible = array(
            'candidateID' => 42, 'candidateJobOrderID' => 13, 'isAdminHidden' => 0,
            'firstName' => 'VisibleCandidate', 'lastName' => 'Example', 'submitted' => 0,
            'isHotCandidate' => 0, 'addedByFirstName' => 'Test', 'addedByLastName' => 'User',
            'attachmentPresent' => 0, 'isDuplicateCandidate' => 0, 'ratingValue' => 3,
            'state' => '', 'dateCreated' => 'Today', 'status' => 'Submitted',
            'lastActivity' => 'Visible pipeline notes'
        );
        $private = array_replace($visible, array('candidateID' => 43, 'candidateJobOrderID' => 14,
            'isAdminHidden' => 1, 'firstName' => 'HiddenCandidate', 'lastActivity' => 'Hidden pipeline notes'));
        $result = $this->request(array(
            'endpoint' => 'getPipelineJobOrder', 'level' => $level, 'acl' => $acl,
            'parent' => $hidden === null ? array() : array('isAdminHidden' => $hidden),
            'rows' => array($visible, $private),
            'request' => array('joborderID' => '7', 'page' => '0', 'entriesPerPage' => '20',
                'sortBy' => '', 'sortDirection' => '', 'indexFile' => 'index.php', 'isPopup' => '0')
        ));

        if (!$allowed)
        {
            self::assertStringContainsString('<errorcode>-1</errorcode>', $result['output']);
            self::assertStringNotContainsString('VisibleCandidate', $result['output']);
            self::assertStringNotContainsString('Visible pipeline notes', $result['output']);
            self::assertCount(empty($acl) ? 1 : 0, $result['reads']);
            self::assertSame(array(), $result['writes']);
            return;
        }

        self::assertStringContainsString('VisibleCandidate', $result['output']);
        self::assertStringContainsString('Visible pipeline notes', $result['output']);
        self::assertCount(2, $result['reads']);
        self::assertCount(1, $result['writes']);
        self::assertStringContainsString('pipeline_entries_per_page', $result['writes'][0]);
        if ($level === ACCESS_LEVEL_SA)
        {
            self::assertStringContainsString('HiddenCandidate', $result['output']);
            self::assertStringContainsString('Hidden pipeline notes', $result['output']);
        }
        else
        {
            self::assertStringNotContainsString('HiddenCandidate', $result['output']);
            self::assertStringNotContainsString('Hidden pipeline notes', $result['output']);
        }
    }

    public static function detailReaders(): array
    {
        return array(array(false), array(true));
    }

    #[DataProvider('detailReaders')]
    public function testPipelineDetailsCannotLeakHiddenCandidateNotes($admin): void
    {
        $result = $this->request(array('endpoint' => 'getPipelineDetails', 'level' => $admin ? ACCESS_LEVEL_SA : ACCESS_LEVEL_EDIT,
            'pipeline' => array('candidateID' => 7, 'isAdminHidden' => 1, 'isJobOrderAdminHidden' => 0),
            'rows' => array(array('notes' => 'Private notes', 'dateModified' => 'Today', 'enteredByFirstName' => 'Other', 'enteredByLastName' => 'User'))));
        if ($admin) self::assertStringContainsString('Private notes', $result['output']);
        else self::assertStringNotContainsString('Private notes', $result['output']);
    }

    public static function associationReaders(): array
    {
        return array(
            'candidate read denied' => array(DATA_ITEM_CANDIDATE, 'candidates.show', false, false, false),
            'company read denied' => array(DATA_ITEM_COMPANY, 'companies.show', false, false, false),
            'contact read denied' => array(DATA_ITEM_CONTACT, 'contacts.show', false, false, false),
            'job order read denied' => array(DATA_ITEM_COMPANY, 'joborders.show', false, false, false),
            'hidden candidate denied' => array(DATA_ITEM_CANDIDATE, 'candidates.hidden', true, false, false),
            'hidden candidate allowed to administrator' => array(DATA_ITEM_CANDIDATE, null, true, true, true),
            'visible candidate' => array(DATA_ITEM_CANDIDATE, null, false, false, true),
            'company associations' => array(DATA_ITEM_COMPANY, null, false, false, true),
            'contact associations' => array(DATA_ITEM_CONTACT, null, false, false, true),
            'administrator without hidden job order permission' => array(DATA_ITEM_CONTACT, 'joborders.hidden', false, true, true)
        );
    }

    #[DataProvider('associationReaders')]
    public function testRelatedJobOrdersMatchRecordVisibility($type, $deniedPermission, $hiddenParent, $admin, $allowed): void
    {
        $result = $this->request(array(
            'endpoint' => 'getDataItemJobOrders',
            'request' => array('dataItemID' => '7', 'dataItemType' => (string) $type),
            'acl' => $deniedPermission === null ? array() : array($deniedPermission => 0),
            'level' => $admin ? ACCESS_LEVEL_SA : ACCESS_LEVEL_READ,
            'parent' => array('isAdminHidden' => (int) $hiddenParent),
            'rows' => array(
                array('jobOrderID' => 8, 'title' => 'Assigned visible', 'companyName' => 'Example & Sons',
                    'isAssigned' => $type === DATA_ITEM_CONTACT ? '1' : null, 'isAdminHidden' => 0),
                array('jobOrderID' => 9, 'title' => 'Private title', 'companyName' => 'Private company',
                    'isAssigned' => '0', 'isAdminHidden' => 1),
                array('jobOrderID' => 10, 'title' => 'Visible legacy', 'companyName' => 'Example & Sons',
                    'isAssigned' => '0', 'isAdminHidden' => null)
            )
        ));
        self::assertStringContainsString($allowed ? '<errorcode>0</errorcode>' : '<errorcode>-2</errorcode>', $result['output']);
        self::assertSame(array(), $result['writes']);
        if (!$allowed)
        {
            self::assertStringNotContainsString('<joborder>', $result['output']);
            self::assertStringNotContainsString('Private', $result['output']);
            self::assertCount($hiddenParent ? 1 : 0, $result['reads']);
            return;
        }

        $xml = simplexml_load_string($result['output']);
        $includeHidden = $admin && $deniedPermission !== 'joborders.hidden';
        $ids = array_map('strval', $xml->xpath('/data/joborder/id'));
        self::assertSame($includeHidden ? array('8', '9', '10') : array('8', '10'), $ids);
        self::assertSame('Example & Sons', (string) $xml->joborder[0]->companyname);
        if (!$includeHidden)
        {
            self::assertStringNotContainsString('Private', $result['output']);
        }
        $sql = end($result['reads']);
        $visibility = 'joborder.is_admin_hidden <> 1 OR joborder.is_admin_hidden IS NULL';
        self::assertSame(!$includeHidden, str_contains($sql, $visibility));
        if ($type === DATA_ITEM_CONTACT)
        {
            self::assertMatchesRegularExpression('/ORDER BY\s+isAssigned DESC,\s+title ASC/', $sql);
            self::assertSame('1', (string) $xml->joborder[0]->assigned);
            self::assertSame('0', (string) $xml->joborder[count($ids) - 1]->assigned);
        }
        else
        {
            self::assertMatchesRegularExpression('/ORDER BY\s+title ASC/', $sql);
            self::assertSame('0', (string) $xml->joborder[0]->assigned);
        }
    }

    public static function invalidReadRequests(): array
    {
        return array(
            'unauthenticated association' => array('getDataItemJobOrders', array('dataItemID' => '7', 'dataItemType' => '100'), false),
            'invalid parent ID' => array('getDataItemJobOrders', array('dataItemID' => 'abc', 'dataItemType' => '100'), true),
            'unsupported parent type' => array('getDataItemJobOrders', array('dataItemID' => '7', 'dataItemType' => '999'), true),
            'array parent ID' => array('getDataItemJobOrders', array('dataItemID' => array('7'), 'dataItemType' => '100'), true),
            'unauthenticated preview' => array('replaceTemplateTags', array('candidateID' => '7'), false),
            'missing candidate ID' => array('replaceTemplateTags', array(), true),
            'zero candidate ID' => array('replaceTemplateTags', array('candidateID' => '0'), true)
        );
    }

    #[DataProvider('invalidReadRequests')]
    public function testInvalidRelatedRecordRequestsFailBeforeDatabaseAccess($endpoint, $request, $loggedIn): void
    {
        $result = $this->request(array('endpoint' => $endpoint, 'request' => $request, 'loggedIn' => $loggedIn));
        self::assertStringContainsString('<errorcode>-1</errorcode>', $result['output']);
        self::assertSame(array(), $result['reads']);
        self::assertSame(array(), $result['writes']);
    }

    public static function templateReaders(): array
    {
        return array(
            'visible candidate' => array(0, ACCESS_LEVEL_READ, array(), true),
            'candidate read denied' => array(0, ACCESS_LEVEL_EDIT, array('candidates.show' => 0), false),
            'hidden candidate denied' => array(1, ACCESS_LEVEL_EDIT, array(), false),
            'hidden candidate granted' => array(1, ACCESS_LEVEL_SA, array(), true),
            'missing candidate' => array(null, ACCESS_LEVEL_READ, array(), false)
        );
    }

    #[DataProvider('templateReaders')]
    public function testTemplatePreviewRequiresCandidateAccess($hidden, $level, $acl, $allowed): void
    {
        $result = $this->request(array(
            'endpoint' => 'replaceTemplateTags', 'level' => $level, 'acl' => $acl,
            'request' => array('candidateID' => '7',
                'templateText' => '%CANDFIRSTNAME% / %CANDFULLNAME% / %CANDOWNER% / %USERMAIL% / <em>Hi & bye</em>'),
            'parent' => $hidden === null ? array() : array('isAdminHidden' => $hidden, 'firstName' => 'Sample',
                'candidateFullName' => 'Sample Candidate', 'ownerFullName' => 'Record Owner')
        ));
        self::assertSame(array(), $result['writes']);
        self::assertCount($acl ? 0 : 1, $result['reads']);
        $xml = simplexml_load_string($result['output']);
        self::assertSame($allowed ? '0' : '-2', (string) $xml->errorcode);
        if ($allowed)
        {
            self::assertSame('Sample / Sample Candidate / Record Owner / <a href="mailto:"></a> / <em>Hi & bye</em>',
                (string) $xml->text);
            return;
        }
        self::assertFalse(isset($xml->text));
        self::assertStringNotContainsString('Sample', $result['output']);
        self::assertStringNotContainsString('Record Owner', $result['output']);
    }
}
