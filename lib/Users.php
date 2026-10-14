<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

if (AUTH_MODE == "ldap" || AUTH_MODE == "sql+ldap") 
{
    require_once(LEGACY_ROOT . '/lib/LDAP.php');
}

/* Login status flags. */
define('LOGIN_SUCCESS',               1);
define('LOGIN_INVALID_USER',         -1);
define('LOGIN_INVALID_PASSWORD',     -2);
define('LOGIN_DISABLED',             -3);
define('LOGIN_CANT_CHANGE_PASSWORD', -4);
define('LOGIN_ROOT_ONLY',            -5);
define('LOGIN_PENDING_APPROVAL',     -6);

/* Add User status flags. */
define('ADD_USER_SUCCESS',            1);
define('ADD_USER_BAD_PASS',          -1);
define('ADD_USER_EXISTS',            -2);
define('ADD_USER_DB_ERROR',          -3);

/* Password for user authenticated against LDAP */ 
define('LDAPUSER_PASSWORD',          '_LDAPUSER_');

/**
 *	Users Library
 *	@package    CATS
 *	@subpackage Library
 */
include_once(LEGACY_ROOT . '/lib/Desks.php');

class Users
{
    private $_db;
    private $_ldap;


    public function __construct()
    {
        $this->_db = DatabaseConnection::getInstance();
    }


    /** Stage 4H is a Settings operation, not a generic bulk administration API. */
    public static function getHandoverCategories()
    {
        return array(
            'task' => array('label' => 'Task assignee', 'module' => 'tasks', 'table' => 'task', 'id' => 'task_id', 'key' => 'taskID', 'field' => 'assignedTo', 'column' => 'assigned_to', 'type' => DATA_ITEM_TASK, 'model' => 'Tasks'),
            'candidate' => array('label' => 'Candidate owner', 'module' => 'candidates', 'table' => 'candidate', 'id' => 'candidate_id', 'key' => 'candidateID', 'field' => 'owner', 'column' => 'owner', 'type' => DATA_ITEM_CANDIDATE, 'model' => 'Candidates'),
            'contact' => array('label' => 'Contact owner', 'module' => 'contacts', 'table' => 'contact', 'id' => 'contact_id', 'key' => 'contactID', 'field' => 'owner', 'column' => 'owner', 'type' => DATA_ITEM_CONTACT, 'model' => 'Contacts'),
            'company' => array('label' => 'Company owner', 'module' => 'companies', 'table' => 'company', 'id' => 'company_id', 'key' => 'companyID', 'field' => 'owner', 'column' => 'owner', 'type' => DATA_ITEM_COMPANY, 'model' => 'Companies'),
            'jobOwner' => array('label' => 'Job Order owner', 'module' => 'joborders', 'table' => 'joborder', 'id' => 'joborder_id', 'key' => 'jobOrderID', 'field' => 'owner', 'column' => 'owner', 'type' => DATA_ITEM_JOBORDER, 'model' => 'JobOrders'),
            'jobRecruiter' => array('label' => 'Job Order recruiter', 'module' => 'joborders', 'table' => 'joborder', 'id' => 'joborder_id', 'key' => 'jobOrderID', 'field' => 'recruiter', 'column' => 'recruiter', 'type' => DATA_ITEM_JOBORDER, 'model' => 'JobOrders')
        );
    }

    public function assertHandoverAdministrator()
    {
        $session = $_SESSION['CATS'] ?? null;
        if (!$session || !$session->isLoggedIn() || $session->getUserID() <= 0 ||
            $session->getRealAccessLevel() < ACCESS_LEVEL_SA ||
            $session->getAccessLevel('settings.handover') < ACCESS_LEVEL_SA ||
            $session->getAccessLevel('settings.manageUsers') < ACCESS_LEVEL_SA)
            throw new RuntimeException('Administrator access required.');
        $actor = $this->get($session->getUserID());
        if (!$actor || $actor['accessLevel'] < ACCESS_LEVEL_SA)
            throw new RuntimeException('Administrator access required.');
    }

    private function handoverID($value)
    {
        if ((!is_int($value) && !is_string($value)) ||
            !preg_match('/^[1-9][0-9]*$/D', (string) $value) || (float) $value > 2147483647)
            throw new InvalidArgumentException('Invalid handover user or record ID.');
        return (int) $value;
    }

    /** Use native ACL evaluation for the recipient, including associated hidden records. */
    public function validateHandoverRecipient($id, $category, $record = array(), $lock = false)
    {
        $id = $this->handoverID($id);
        $definition = self::getHandoverCategories()[$category] ?? null;
        if (!$definition) throw new InvalidArgumentException('Invalid handover category.');
        $user = $this->get($id, $lock);
        if (!$user || $user['accessLevel'] <= ACCESS_LEVEL_DISABLED)
            throw new InvalidArgumentException('Choose an active replacement user.');
        $access = fn($action) => ACL::getAccessLevel($action, explode(',', (string) $user['categories']), $user['accessLevel']);
        $module = $definition['module'];
        if ($access($module . '.show') < ACCESS_LEVEL_READ || $access($module . '.edit') < ACCESS_LEVEL_EDIT)
            throw new InvalidArgumentException('Replacement user cannot manage this category.');
        if (($record['isAdminHidden'] ?? 0) == 1 && $access($module . '.hidden') < ACCESS_LEVEL_SA)
            throw new InvalidArgumentException('Replacement user cannot access this record.');
        if ($category === 'task' && $record)
        {
            $parents = array(DATA_ITEM_COMPANY => 'Companies', DATA_ITEM_CONTACT => 'Contacts',
                DATA_ITEM_CANDIDATE => 'Candidates', DATA_ITEM_JOBORDER => 'JobOrders');
            $associations = array();
            if ($record['dataItemType'] !== null)
            {
                $model = $parents[$record['dataItemType']] ?? null;
                if (!$model) throw new RuntimeException('Task association unavailable.');
                $associations[] = array($model, $record['dataItemID']);
            }
            if ($record['candidateJobOrderID'] !== null)
            {
                $pipeline = $this->_db->getAssoc('SELECT candidate_id, joborder_id FROM candidate_joborder WHERE candidate_joborder_id = ' . (int) $record['candidateJobOrderID']);
                if (!$pipeline) throw new RuntimeException('Task association unavailable.');
                $associations[] = array('Candidates', $pipeline['candidate_id']);
                $associations[] = array('JobOrders', $pipeline['joborder_id']);
            }
            foreach ($associations as [$model, $parentID])
            {
                $parent = (new $model())->get($parentID);
                $module = strtolower($model);
                if (!$parent || $access($module . '.show') < ACCESS_LEVEL_READ ||
                    (($parent['isAdminHidden'] ?? 0) == 1 && $access($module . '.hidden') < ACCESS_LEVEL_SA))
                    throw new InvalidArgumentException('Replacement user cannot access this Task association.');
            }
        }
        return $user;
    }

    private function handoverCanRead($definition, $record)
    {
        if (!$record || $_SESSION['CATS']->getAccessLevel($definition['module'] . '.show') < ACCESS_LEVEL_READ) return false;
        if ($definition['type'] === DATA_ITEM_CANDIDATE) return CandidateAuthorization::canAccessCandidateRecord($record);
        return ($record['isAdminHidden'] ?? 0) != 1 ||
            $_SESSION['CATS']->getAccessLevel($definition['module'] . '.hidden') >= ACCESS_LEVEL_SA;
    }

    private function handoverExclusion($category, $record)
    {
        switch ($category)
        {
            case 'task': return in_array($record['status'], array('open', 'in_progress'), true) ? '' : 'Completed/cancelled Task';
            case 'candidate': return $record['isActive'] == 1 ? '' : 'Inactive Candidate';
            case 'contact': return $record['leftCompany'] == 0 ? '' : 'Contact has left Company';
            // Companies have no archive flag. Conservatively exclude existing dormant/lost lifecycles.
            case 'company': return in_array($record['relationshipStatus'] ?? null, array('Dormant', 'Lost'), true) ? 'Dormant/Lost Company' : '';
            default: return JobOrderStatuses::isActionable($record['status']) ? '' : 'Job Order is not actionable Active';
        }
    }

    /** Preview one bounded page per selected category; no writes or global unscoped counts. */
    public function previewHandover($from, $default, $categories, $overrides = array(), $after = array())
    {
        $this->assertHandoverAdministrator();
        $from = $this->handoverID($from);
        $default = $this->handoverID($default);
        $defaultUser = $this->get($default);
        if (!$defaultUser || $defaultUser['accessLevel'] <= ACCESS_LEVEL_DISABLED) throw new InvalidArgumentException('Choose an active default replacement.');
        if (!$this->get($from) || $from === $default) throw new InvalidArgumentException('Choose different departing and replacement users.');
        $definitions = self::getHandoverCategories();
        if (!is_array($categories) || !$categories || count($categories) > 6 ||
            !is_array($overrides) || !is_array($after)) throw new InvalidArgumentException('Select valid handover categories.');
        $seen = array();
        foreach ($categories as $category)
        {
            if (!is_string($category) || !isset($definitions[$category]) || isset($seen[$category]))
                throw new InvalidArgumentException('Invalid handover category.');
            $seen[$category] = true;
        }
        foreach ($overrides as $value)
            if ($value !== '' && $this->handoverID($value) === $from)
                throw new InvalidArgumentException('Replacement must differ from departing user.');
        foreach (array_merge(array_keys($overrides), array_keys($after)) as $key)
            if (!isset($definitions[$key])) throw new InvalidArgumentException('Invalid handover category.');
        $result = array('from' => $from, 'default' => $default, 'categories' => array());
        foreach ($categories as $category)
        {
            if (!is_string($category) || !isset($definitions[$category]) || isset($result['categories'][$category]))
                throw new InvalidArgumentException('Invalid handover category.');
            $d = $definitions[$category];
            if ($_SESSION['CATS']->getAccessLevel($d['module'] . '.list') < ACCESS_LEVEL_READ ||
                $_SESSION['CATS']->getAccessLevel($d['module'] . '.show') < ACCESS_LEVEL_READ)
                throw new RuntimeException('Category access denied.');
            $to = ($overrides[$category] ?? '') === '' ? $default : $this->handoverID($overrides[$category]);
            if ($to === $from) throw new InvalidArgumentException('Replacement must differ from departing user.');
            $recipient = $this->validateHandoverRecipient($to, $category);
            $cursor = ($after[$category] ?? 0) === 0 ? 0 : $this->handoverID($after[$category]);
            $model = new $d['model']();
            if ($category === 'task') $rows = $model->getHandoverPage($from, $cursor);
            else
            {
                $hidden = in_array($category, array('candidate', 'jobOwner', 'jobRecruiter'), true) &&
                    $_SESSION['CATS']->getAccessLevel($d['module'] . '.hidden') < ACCESS_LEVEL_SA ?
                    ' AND (is_admin_hidden IS NULL OR is_admin_hidden <> 1)' : '';
                $ids = $this->_db->getAllAssoc('SELECT ' . $d['id'] . ' AS id FROM ' . $d['table'] .
                    ' WHERE ' . $d['column'] . ' = ' . $from . ' AND ' . $d['id'] . ' > ' . $cursor . $hidden .
                    ' ORDER BY ' . $d['id'] . ' LIMIT 51');
                $rows = array();
                foreach ($ids as $id)
                {
                    $record = $model->get($id['id']);
                    if ($this->handoverCanRead($d, $record)) $rows[] = $record;
                }
            }
            $page = array('to' => $to, 'recipient' => $recipient['fullName'], 'rows' => array(), 'more' => count($rows) > 50, 'after' => $cursor,
                'eligible' => 0, 'excluded' => 0, 'blocked' => 0);
            foreach (array_slice($rows, 0, 50) as $record)
            {
                $id = (int) $record[$d['key']];
                $page['after'] = $id;
                $reason = $this->handoverExclusion($category, $record);
                $state = $reason === '' ? 'eligible' : 'excluded';
                if ($reason === '')
                {
                    if ($_SESSION['CATS']->getAccessLevel($d['module'] . '.edit') < ACCESS_LEVEL_EDIT ||
                        ($category === 'task' && !$model->getActionPermissions($id)['manage']))
                    {
                        $state = 'blocked'; $reason = 'Assignment update denied.';
                    }
                    else try { $this->validateHandoverRecipient($to, $category, $record); }
                    catch (Throwable $e) { $state = 'blocked'; $reason = 'Replacement cannot access this record or association.'; }
                }
                $page[$state]++;
                $label = $record['title'] ?? $record['name'] ?? trim(($record['firstName'] ?? '') . ' ' . ($record['lastName'] ?? ''));
                $page['rows'][] = array('id' => $id, 'label' => $label, 'state' => $state, 'reason' => $reason);
            }
            $result['categories'][$category] = $page;
        }
        return $result;
    }

    /** Each record is independent. Retries skip changed owners; failures expose no raw SQL/metadata. */
    public function executeHandover($preview, $csrfToken)
    {
        $this->assertHandoverAdministrator();
        if (!$_SESSION['CATS']->isCSRFTokenValid($csrfToken)) throw new RuntimeException('Invalid CSRF token.');
        if (!is_array($preview) || !is_array($preview['categories'] ?? null) ||
            !$preview['categories'] || count($preview['categories']) > 6)
            throw new InvalidArgumentException('Invalid handover preview.');
        $from = $this->handoverID($preview['from'] ?? null);
        $definitions = self::getHandoverCategories();
        foreach ($preview['categories'] as $category => $page)
        {
            if (!isset($definitions[$category]) || !is_array($page) ||
                !is_array($page['rows'] ?? null) || count($page['rows']) > 50 ||
                $this->handoverID($page['to'] ?? null) === $from)
                throw new InvalidArgumentException('Invalid handover preview.');
        }
        $results = array();
        foreach ($preview['categories'] as $category => $page)
        {
            $d = $definitions[$category];
            $model = new $d['model']();
            $outcomes = array();
            foreach ($page['rows'] as $row)
            {
                if ($row['state'] !== 'eligible')
                {
                    $outcomes[] = array('id' => null, 'state' => 'skipped', 'reason' => 'Excluded or blocked in preview; no update requested.');
                    continue;
                }
                $id = $this->handoverID($row['id']);
                $transaction = false;
                $failureReason = 'Administrator or category update permission changed.';
                try
                {
                    $this->assertHandoverAdministrator();
                    if ($_SESSION['CATS']->getAccessLevel($d['module'] . '.edit') < ACCESS_LEVEL_EDIT)
                        throw new RuntimeException('Update denied.');
                    if ($category !== 'task')
                    {
                        $failureReason = 'Unable to start record transaction.';
                        if (!$this->_db->beginTransaction()) throw new RuntimeException('Transaction unavailable.');
                        $transaction = true;
                        $failureReason = 'Record unavailable for update.';
                        if (!$this->_db->getAssoc('SELECT ' . $d['id'] . ' FROM ' . $d['table'] . ' WHERE ' . $d['id'] . ' = ' . $id . ' FOR UPDATE'))
                            throw new RuntimeException('Record unavailable.');
                    }
                    // Read the record before any consistent user read creates an older snapshot.
                    $before = $model->get($id);
                    $visible = $category === 'task' ? !empty($before) : $this->handoverCanRead($d, $before);
                    if (!$visible || $before[$d['field']] != $from || $this->handoverExclusion($category, $before) !== '')
                    {
                        if ($transaction) $this->_db->rollbackTransaction();
                        $outcomes[] = array('id' => $visible ? $id : null, 'state' => 'skipped', 'reason' => 'Unavailable, assignment changed or no longer eligible.');
                        continue;
                    }
                    $failureReason = 'Replacement cannot access this record or association.';
                    $this->validateHandoverRecipient($page['to'], $category, $before, $category !== 'task');
                    $failureReason = 'Assignment write or audit failed; preview again before retrying.';
                    if ($category === 'task')
                    {
                        if (!$model->update($id, array('assignedTo' => $page['to']), $from)) throw new RuntimeException('Write failed.');
                    }
                    else
                    {
                        $updated = $model instanceof JobOrders ?
                            $model->updateHandoverAssignment($id, $from, $page['to'], $d['field']) :
                            $model->updateHandoverAssignment($id, $from, $page['to']);
                        if (!$updated) throw new RuntimeException('Write failed.');
                        $after = $model->get($id);
                        if ($after[$d['field']] != $page['to'] ||
                            !(new History())->storeHistoryChanges($d['type'], $id, $before, $after))
                            throw new RuntimeException('Assignment audit failed.');
                        $failureReason = 'Unable to confirm the record commit; preview again before retrying.';
                        if (!$this->_db->commitTransaction()) throw new RuntimeException('Commit failed.');
                    }
                    $outcomes[] = array('id' => $id, 'state' => 'transferred', 'reason' => 'Assignment updated and audited.');
                }
                catch (Throwable $e)
                {
                    if ($transaction) $this->_db->rollbackTransaction();
                    // Only identify failed records that remain readable; never repeat stale preview labels.
                    $failedID = null;
                    try
                    {
                        $current = $model->get($id);
                        if ($category === 'task' ? !empty($current) : $this->handoverCanRead($d, $current)) $failedID = $id;
                    }
                    catch (Throwable $ignored) {}
                    $outcomes[] = array('id' => $failedID, 'state' => 'failed', 'reason' => $failureReason);
                }
            }
            $results[$category] = $outcomes;
        }
        return $results;
    }

    /** Existing access-level disabling, independent of handover success. */
    public function disableForHandover($id, $csrfToken)
    {
        $this->assertHandoverAdministrator();
        if ($_SESSION['CATS']->getAccessLevel('settings.editUser.POST') < ACCESS_LEVEL_SA ||
            !$_SESSION['CATS']->isCSRFTokenValid($csrfToken)) throw new RuntimeException('Account disable denied.');
        $id = $this->handoverID($id);
        if ($id === (int) $_SESSION['CATS']->getUserID()) throw new InvalidArgumentException('Cannot disable your own account.');
        if (!$this->_db->beginTransaction()) throw new RuntimeException('Account transaction unavailable.');
        try
        {
            if (!$this->_db->getAssoc('SELECT user_id FROM user WHERE user_id = ' . $id . ' FOR UPDATE'))
                throw new RuntimeException('Account unavailable.');
            $user = $this->get($id);
            if (!$user || $user['accessLevel'] > $_SESSION['CATS']->getRealAccessLevel())
                throw new RuntimeException('Account disable denied.');
            if (!$this->update($id, $user['lastName'], $user['firstName'], $user['email'],
                $user['username'], ACCESS_LEVEL_DISABLED, $user['canSeeEEOInfo']) ||
                !(new History())->storeHistoryChanges(DATA_ITEM_USER, $id,
                    array('accessLevel' => $user['accessLevel']), array('accessLevel' => ACCESS_LEVEL_DISABLED)))
                throw new RuntimeException('Unable to disable and audit account.');
            if (!$this->_db->commitTransaction()) throw new RuntimeException('Unable to commit account disable.');
            return true;
        }
        catch (Throwable $e)
        {
            $this->_db->rollbackTransaction();
            throw $e;
        }
    }

    /**
     * Adds a user to the database.
     *
     * @param string last name
     * @param string first name
     * @param string e-mail address
     * @param string username
     * @param string password
     * @param flag access level
     * @param bool eeo information visible (false)
     * @return new user ID, or -1 on failure.
     */
    public function add($lastName, $firstName, $email, $username, $password,
            $accessLevel, $eeoIsVisible = false, $deskID = null)
    {

        $deskID = (new Desks())->validateAssignment($deskID);
        $hashedPassword = $password == LDAPUSER_PASSWORD ? $password : $this->hashPassword($password);
        $sql = sprintf(
                "INSERT INTO user (
            user_name,
        password,
        access_level,
        can_change_password,
        is_test_user,
        email,
        first_name,
        last_name,
        desk_id,
        can_see_eeo_info
            )
                VALUES (
                    %s,
                    %s,
                    %s,
                    1,
                    0,
                    %s,
                    %s,
                    %s,
                    %s,
                    %s
                    )",
        $this->_db->makeQueryString($username),
        $this->_db->makeQueryString($hashedPassword),
        $this->_db->makeQueryInteger($accessLevel),
        $this->_db->makeQueryString($email),
        $this->_db->makeQueryString($firstName),
        $this->_db->makeQueryString($lastName),
        $this->_db->makeQueryIntegerOrNULL($deskID ?? -1),
        ($eeoIsVisible ? 1 : 0)
            );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        return $this->_db->getLastInsertID();
    }

    /**
     * Updates a user in the database.
     *
     * @param string last name
     * @param string first name
     * @param string e-mail address
     * @param string username
     * @param flag access level
     * @param bool eeo information visible (false)
     * @return boolean True if successful; false otherwise.
     */
    public function update($userID, $lastName, $firstName, $email,
            $username, $accessLevel = -1, $eeoIsVisible = false, $deskID = false)
    {
        $deskSQL = '';
        if ($deskID !== false)
        {
            $current = $this->get($userID);
            if (empty($current)) return false;
            $deskID = (new Desks())->validateAssignment($deskID, $current['deskID']);
            $deskSQL = ', desk_id = ' . $this->_db->makeQueryIntegerOrNULL($deskID ?? -1);
        }
        /* If an access level was specified, make sure the access level is
         * updated by the query.
         */
        if ($accessLevel != -1)
        {
            $accessLevelSQL = sprintf(
                    ", access_level = %s",
                    $this->_db->makeQueryInteger($accessLevel)
                    );
        }
        else
        {
            $accessLevelSQL = '';
        }

        $sql = sprintf(
                "UPDATE
                user
                SET
                last_name        = %s,
                first_name       = %s,
                email            = %s,
                user_name        = %s,
                can_see_eeo_info = %s
                %s
                WHERE
                user_id = %s",
                $this->_db->makeQueryString($lastName),
                $this->_db->makeQueryString($firstName),
                $this->_db->makeQueryString($email),
                $this->_db->makeQueryString($username),
                ($eeoIsVisible ? 1 : 0),
                $accessLevelSQL . $deskSQL,
                $this->_db->makeQueryInteger($userID)
                    );

        return (bool) $this->_db->query($sql);
    }

    /**
     * Updates the current user in the database.
     *
     * @param integer user ID
     * @param string e-mail address
     * @return boolean True if successful; false otherwise.
     */
    public function updateSelfEmail($userID, $email)
    {
        $sql = sprintf(
                "UPDATE
                user
                SET
                email = %s
                WHERE
                user_id = %s",
                $this->_db->makeQueryString($email),
                $this->_db->makeQueryInteger($userID)
                );

        return (bool) $this->_db->query($sql);
    }

    /**
     * Update the categories field of the user table for the specified user.
     *
     * @param integer ID of the user
     * @param string The new categories
     * @return boolean true on sucess
     */
    public function updateCategories($userID, $categories)
    {
        $sql = sprintf(
                "UPDATE
                user
                SET
                categories = %s
                WHERE
                user_id = %s",
                $this->_db->makeQueryString($categories),
                $this->_db->makeQueryInteger($userID)
                );

        return (bool) $this->_db->query($sql);
    }

    /**
     * Removes a user from the system.
     * NOTE: Associated records are not deleted! THIS WILL BREAK THINGS!
     * This is only here for use by the CATS Automated Testing Framework,
     * which will clean up after itself before calling this.
     *
     * @param integer user ID
     * @return void
     */
    public function delete($userID)
    {
        /* Delete the user. */
        $sql = sprintf(
                "DELETE FROM
                user
                WHERE
                user_id = %s",
                $this->_db->makeQueryInteger($userID)
                );
        $this->_db->query($sql);
    }

    /**
     * Returns one user.
     *
     * @param integer user ID
     * @param boolean Current locking read inside the caller transaction (default false).
     * @return array user data
     */
    public function get($userID, $forUpdate = false)
    {
        $sql = sprintf(
                "SELECT
                user.desk_id AS deskID,
                (SELECT name FROM desk WHERE desk.desk_id = user.desk_id) AS deskName,
                user.user_name AS username,
                user.access_level AS accessLevel,
                access_level.short_description AS accessLevelDescription,
                access_level.long_description AS accessLevelLongDescription,
                user.first_name AS firstName,
                user.last_name AS lastName,
                CONCAT(
                    user.first_name, ' ', user.last_name
                    ) AS fullName,
                user.email AS email,
                user.company as company,
                user.city as city,
                user.state as state,
                user.zip_code as zipCode,
                user.country as country,
                user.address as address,
                user.phone_work as phoneWork,
                user.user_id AS userID,
                user.password AS password,
                user.categories AS categories,
                user.session_cookie AS sessionCookie,
                user.can_see_eeo_info AS canSeeEEOInfo,
                DATE_FORMAT(
                        MAX(
                            IF(user_login.successful = 1, user_login.date, NULL)
                           ),
                        '" . DateUtility::getMysqlDateTimeFormat() . "'
                        ) AS successfulDate,
                DATE_FORMAT(
                        MAX(
                            IF(user_login.successful = 0, user_login.date, NULL)
                           ),
                        '" . DateUtility::getMysqlDateTimeFormat() . "'
                        ) AS unsuccessfulDate,
                force_logout as forceLogout
                    FROM
                    user
                    LEFT JOIN access_level
                    ON user.access_level = access_level.access_level_id
                    LEFT JOIN user_login
                    ON user.user_id = user_login.user_id
                    WHERE
                    user.user_id = %s
                    GROUP BY
                    user.user_id",
                $this->_db->makeQueryInteger($userID)
                    );

        // Handover needs a current locking read, not a prior repeatable-read snapshot.
        return $this->_db->getAssoc($sql . ($forUpdate ? ' FOR UPDATE' : ''));
    }

    /**
     * Returns 1 user, ignoring the user's Site ID.
     *
     * @return array user data
     */
    public function getForAdministration($userID, $aspSiteRule)
    {
        $sql = sprintf("SELECT
                user.desk_id AS deskID,
                (SELECT name FROM desk WHERE desk.desk_id = user.desk_id) AS deskName,
                user.user_name AS username,
                user.access_level AS accessLevel,
                access_level.short_description AS accessLevelDescription,
                access_level.long_description AS accessLevelLongDescription,
                user.first_name AS firstName,
                user.last_name AS lastName,
                CONCAT(
                    user.first_name, ' ', user.last_name
                    ) AS fullName,
                user.email AS email,
                user.title AS title,
                user.address AS address,
                user.company as company,
                user.city as city,
                user.state as state,
                user.zip_code as zipCode,
                user.country as country,
                user.phone_work AS phoneWork,
                user.phone_other AS phoneOther,
                user.phone_cell AS phoneCell,
                user.notes AS notes,
                user.user_id AS userID,
                user.password AS password,
                user.categories AS categories,
                user.session_cookie AS sessionCookie,
                user.can_see_eeo_info AS canSeeEEOInfo,
                (SELECT name FROM site LIMIT 1) AS siteName,
                DATE_FORMAT(
                        MAX(
                            IF(user_login.successful = 1, user_login.date, NULL)
                           ),
                        '" . DateUtility::getMysqlDateTimeFormat() . "'
                        ) AS successfulDate,
                DATE_FORMAT(
                        MAX(
                            IF(user_login.successful = 0, user_login.date, NULL)
                           ),
                        '" . DateUtility::getMysqlDateTimeFormat() . "'
                        ) AS unsuccessfulDate,
                force_logout as forceLogout
                    FROM
                    user
                    LEFT JOIN access_level
                    ON user.access_level = access_level.access_level_id
                    LEFT JOIN user_login
                    ON user.user_id = user_login.user_id
                    WHERE
                    %s
                    AND
                    user.user_id = %s
                    GROUP BY
                    user.user_id",
                $aspSiteRule,
                $this->_db->makeQueryInteger($userID)
                    );

        return $this->_db->getAssoc($sql);
    }

    /**
     * TODO: DOCUMENT ME.
     */
    public function updateForAdministration($userID, $firstName, $lastName, $email,
            $title, $phone_work, $phone_cell, $phone_other, $notes, $aspSiteRule)
    {
        $sql = sprintf(
                "UPDATE
                user
                SET
                last_name    = %s,
                first_name   = %s,
                email        = %s,
                title        = %s,
                phone_work   = %s,
                phone_cell   = %s,
                phone_other  = %s,
                notes        = %s
                WHERE
                user_id = %s
                AND
                %s",
                $this->_db->makeQueryString($lastName),
                $this->_db->makeQueryString($firstName),
                $this->_db->makeQueryString($email),
                $this->_db->makeQueryString($title),
                $this->_db->makeQueryString($phone_work),
                $this->_db->makeQueryString($phone_cell),
                $this->_db->makeQueryString($phone_other),
                $this->_db->makeQueryString($notes),
                $this->_db->makeQueryInteger($userID),
                $aspSiteRule
                    );

        return (bool) $this->_db->query($sql);
    }

    /**
     * Returns information pertaining to forced logouts for a user.
     *
     * @param integer user ID
     * @return array user data
     */
    public function getForceLogoutData($userID)
    {
        $sql = sprintf(
                "SELECT
                user.access_level AS accessLevel,
                force_logout as forceLogout
                FROM
                user
                WHERE
                user.user_id = %s",
                $this->_db->makeQueryInteger($userID)
                );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Returns all users.
     *
     * @return array users data
     */
    public function getAll()
    {
        $sql = sprintf(
                "SELECT
                user.user_name AS username,
                user.password AS password,
                user.access_level AS accessLevel,
                access_level.short_description AS accessLevelDescription,
                user.first_name AS firstName,
                user.last_name AS lastName,
                user.email AS email,
                user.company as company,
                user.city as city,
                user.state as state,
                user.zip_code as zipCode,
                user.country as country,
                user.address as address,
                user.phone_work as phoneWork,
                user.user_id AS userID,
                DATE_FORMAT(
                    MAX(
                        IF(user_login.successful = 1, user_login.date, NULL)
                       ),
                    '" . DateUtility::getMysqlDateTimeFormat() . "'
                    ) AS successfulDate,
                DATE_FORMAT(
                        MAX(
                            IF(user_login.successful = 0, user_login.date, NULL)
                           ),
                        '" . DateUtility::getMysqlDateTimeFormat() . "'
                        ) AS unsuccessfulDate
                    FROM
                    user
                    LEFT JOIN access_level
                    ON user.access_level = access_level.access_level_id
                    LEFT JOIN user_login
                    ON user.user_id = user_login.user_id
                    GROUP BY
                    user.user_id
                    ORDER BY
                    user.access_level DESC,
                user.last_name ASC,
                user.first_name ASC"
                    );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Looks up and returns a user's ID by username. If no matching username is
     * found, false is returned.
     *
     * @param string username
     * @return integer user id or boolean false
     */
    public function getIDByUsername($username)
    {
        $sql = sprintf(
                "SELECT
                user_id AS userID
                FROM
                user
                WHERE
                user_name = %s",
                $this->_db->makeQueryString($username)
                );
        $data = $this->_db->getAssoc($sql);

        if (empty($data))
        {
            return false;
        }

        return (int) $data['userID'];
    }

    /**
     * Returns a record set of access levels
     *
     * @return array access levels data
     */
    public function getAccessLevels()
    {
        $sql = sprintf(
                "SELECT
                access_level.access_level_id AS accessID,
                access_level.short_description AS shortDescription,
                access_level.long_description AS longDescription
                FROM
                access_level
                ORDER BY
                access_level.access_level_id ASC"
                );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns $limit most recent attempted logins for the specified user ID.
     *
     * @param integer user ID
     * @param integer entries to return
     * @return array access levels data
     */
    public function getLastLoginAttempts($userID, $limit)
    {
        $sql = sprintf(
                "SELECT
                user_login.ip AS ip,
                user_login.user_agent AS userAgent,
                user_login.date AS date,
                user_login.host AS hostname,
                user_login.successful AS successful
                FROM
                user_login
                WHERE
                user_login.user_id = %s
                ORDER BY
                user_login.date DESC
                LIMIT
                %s",
                $userID,
                $this->_db->makeQueryInteger($limit)
                );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns a minimal record set of all users (for use when creating
     * drop-down lists of users, etc.).
     *
     * @return array users data
     */
    public function getSelectList()
    {
        $sql = sprintf(
                "SELECT
                user.user_name AS username,
                user.first_name AS firstName,
                user.last_name AS lastName,
                user.user_id AS userID,
                user.categories AS categories
                FROM
                user
                WHERE
                user.access_level > %s
                ORDER BY
                user.last_name ASC,
                user.first_name ASC",
                ACCESS_LEVEL_DISABLED
                );

        if (!eval(Hooks::get('USERS_GET_SELECT_SQL'))) return;

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Changes a user's password to the password specified.
     *
     * @param integer user ID
     * @param string current password
     * @param string new password
     * @return flag status
     */
    public function changePassword($userID, $currentPassword, $newPassword)
    {
        if( $this->isUserLDAP($userID))
        {
            /* LDAP user not allowed to change password */
            return LOGIN_CANT_CHANGE_PASSWORD;
        }

        $sql = sprintf(
                "SELECT
                user.password AS password,
                user.access_level AS accessLevel,
                user.can_change_password AS canChangePassword
                FROM
                user
                WHERE
                user.user_id = %s",
                $this->_db->makeQueryInteger($userID)
                );
        $rs = $this->_db->getAssoc($sql);

        /* No results? Shouldn't happen, but it could if the user just got
         * deleted or something.
         */
        if (!$rs || $this->_db->isEOF())
        {
            return LOGIN_INVALID_USER;
        }

        /* Is the user's supplied password correct? */
        /* FIXME: This code relies on verifyAndMigratePassword() to handle both legacy
         * MD5 hashes and modern password_hash() output. If verifyAndMigratePassword()
         * is changed in a future release (for example when removing MD5 support or
         * switching to a different algorithm), this password check must be reviewed
         * and adjusted accordingly.
         *
         * Previous MD5-only implementation for reference:
         * if ($rs['password'] !== md5($currentPassword))
         * {
         *     return LOGIN_INVALID_PASSWORD;
         * }
         */
        if (!$this->verifyAndMigratePassword($userID, $currentPassword, $rs['password']))
        {
            return LOGIN_INVALID_PASSWORD;
        }

        /* Is the user's account disabled? */
        if ($rs['accessLevel'] <= ACCESS_LEVEL_DISABLED)
        {
            return LOGIN_DISABLED;
        }

        /* Is the user allowed to change his/her password? */
        if ($rs['canChangePassword'] != '1')
        {
            return LOGIN_CANT_CHANGE_PASSWORD;
        }

        /* Change the user's password. */
        $newPasswordHash = $this->hashPassword($newPassword);
        $sql = sprintf(
                "UPDATE
                user
                SET
                password = %s
                WHERE
                user.user_id = %s",
                $this->_db->makeQueryString($newPasswordHash),
                $this->_db->makeQueryInteger($userID)
                );
        $this->_db->query($sql);
        // FIXME: Did the above query succeed? If not, fail.

        return LOGIN_SUCCESS;
    }

    /**
     * Resets a user's password to the password specified.
     *
     * @param integer user ID
     * @param string current password
     * @param string new password
     * @return flag status
     */
    public function resetPassword($userID, $newPassword)
    {
        if( $this->isUserLDAP($userID))
        {
            /* LDAP user not allowed to reset password */
            return false;
        }

        $sql = sprintf(
                "SELECT
                user.password AS password,
                user.access_level AS accessLevel,
                user.can_change_password AS canChangePassword
                FROM
                user
                WHERE
                user.user_id = %s",
                $this->_db->makeQueryInteger($userID)
                );
        $rs = $this->_db->getAssoc($sql);

        /* No results? Shouldn't happen, but it could if the user just got
         * deleted or something.
         */
        if (empty($rs))
        {
            return false;
        }

        /* Change the user's password. */
        $newPasswordHash = $this->hashPassword($newPassword);
        $sql = sprintf(
                "UPDATE
                user
                SET
                password = %s
                WHERE
                user.user_id = %s",
                $this->_db->makeQueryString($newPasswordHash),
                $this->_db->makeQueryInteger($userID)
                );
        $this->_db->query($sql);
        // FIXME: Did the above query succeed? If not, fail.

        return true;
    }

    /**
     * Returns a login status flag indicating whether or not the specified
     * password is correct for the specified user and whether or not the
     * account is enabled or disabled.
     *
     *   LOGIN_INVALID_USER     - Invalid username.
     *   LOGIN_INVALID_PASSWORD - Invalid password.
     *   LOGIN_DISABLED         - Account is disabled.
     *   LOGIN_SUCCESS          - Password is valid and account is enabled.
     *   LOGIN_PENDING_APPROVAL - Account is new but disabled and needs to be approved by SA or root.
     *
     * @param string username
     * @param string password
     * @return flag status
     */
    public function isCorrectLogin($username, $password)
    {
        $existsInLDAP = false;
        $existsInDB = false;
        
        if (empty($username))
        {
            return LOGIN_INVALID_USER;
        }

        if (empty($password))
        {
            return LOGIN_INVALID_PASSWORD;
        }

        $sql = sprintf(
                "SELECT
                user.user_name AS username,
                user.password AS password,
                user.access_level AS accessLevel,
                user.user_id AS userID
                FROM
                user
                WHERE
                user.user_name = %s",
                $this->_db->makeQueryString($username)
                );
        $rs = $this->_db->getAssoc($sql);

        /* No results? Invalid user or new LDAP user. */
        if(!$rs || $this->_db->isEOF())
        {
            if(AUTH_MODE == 'sql')
            {
                return LOGIN_INVALID_USER;
            }
        } 
        else 
        {
            $existsInDB = true;
        }

        if((AUTH_MODE == 'ldap' || AUTH_MODE == 'sql+ldap')
            && (($existsInDB && $rs['password'] == LDAPUSER_PASSWORD) || !$existsInDB) ) {
            $this->_ldap = LDAP::getInstance($username, $password);
            if($this->_ldap == NULL)
            {
                return LOGIN_INVALID_USER;
            }
            if(!$this->_ldap->authenticate($username, $password)) 
            {
                return LOGIN_INVALID_PASSWORD;
            } 
            $existsInLDAP = true;
        } else if(AUTH_MODE == 'ldap'){
            /*  incorrect LDAP user in db */
            return LOGIN_INVALID_USER;
        } else {
            /* Is the user's supplied password correct? */
            if (!$this->verifyAndMigratePassword((int) $rs['userID'], $password, $rs['password']))
            {
                return LOGIN_INVALID_PASSWORD;
            }

            $this->rehashPasswordIfNeeded((int) $rs['userID'], $rs['password'], $password);
        }
        
        if (!$existsInDB && $existsInLDAP) {
            /* ldap user not created in local db -> create one as disabled */
            $userInfo = $this->_ldap->getUserInfo($username);
            $userID = $this->add($userInfo[0], $userInfo[1], $userInfo[2], $userInfo[3], LDAPUSER_PASSWORD, '0');
            return LOGIN_PENDING_APPROVAL;
        }

        /* Is the user's account disabled? */
        if ($rs['accessLevel'] <= ACCESS_LEVEL_DISABLED)
        {
            return LOGIN_DISABLED;
        }

        /* If in slave mode, only allow root login. */
        if (CATS_SLAVE && $rs['accessLevel'] < ACCESS_LEVEL_ROOT)
        {
            return LOGIN_ROOT_ONLY;
        }

        return LOGIN_SUCCESS;
    }

    /**
     * Returns an array of license data
     *
     * @return array
     */
    public function getLicenseData()
    {
        $sql = sprintf(
                "SELECT
                COUNT(user.user_id) AS totalUsers,
                user.access_level,
                site.user_licenses AS userLicenses
                FROM
                user
                CROSS JOIN site
                WHERE
                user.access_level > %s",
                ACCESS_LEVEL_READ
                );
        $license = $this->_db->getAssoc($sql);

        if (empty($license))
        {
            $license['totalUsers'] = 0;
            $license['userLicenses'] = 0;
        }

        $license['diff'] = $license['userLicenses'] - $license['totalUsers'];
        $license['unlimited'] = 0;
        $license['canAdd'] = 0;

        if ($license['userLicenses'] == 0)
        {
            $license['unlimited'] = 1;
            $license['canAdd'] = 1;
        }
        else if ($license['diff'] > 0)
        {
            $license['canAdd'] = 1;
        }

        return $license;
    }

    /**
     * Returns true if a user by the specified username already exists; false
     * otherwise.
     *
     * @return boolean user exists
     */
    public function usernameExists($username)
    {
        // FIXME: COUNT() not needed.
        $sql = sprintf(
                "SELECT
                COUNT(user.user_name) AS userExists
                FROM
                user
                WHERE
                user.user_name = %s",
                $this->_db->makeQueryString($username)
                );
        $rs = $this->_db->getAssoc($sql);

        if (!empty($rs) || $rs['userExists'] <= 0)
        {
            return false;
        }

        return true;
    }

    /**
     * Creates a login history entry.
     *
     * @param integer User's User ID.
     * @param integer User's Site ID
     * @param string User's IP address.
     * @param string User's browser user agent string.
     * @param boolean Was the login successful?
     * @return integer This login's User Login ID.
     */
    public function addLoginHistory($userID, $ip, $userAgent,
            $wasSuccessful)
    {
        if (ENABLE_HOSTNAME_LOOKUP)
        {
            $hostname = @gethostbyaddr($ip);
        }
        else
        {
            $hostname = $ip;
        }

        $sql = sprintf(
                "INSERT INTO user_login (
            user_id,
            ip,
            user_agent,
            host,
            date,
            successful,
            date_refreshed
                )
                VALUES (
                    %s,
                    %s,
                    %s,
                    %s,
                    NOW(),
                    %s,
                    NOW()
                    )",
            $userID,
            $this->_db->makeQueryString($ip),
            $this->_db->makeQueryString($userAgent),
            $this->_db->makeQueryString($hostname),
            ($wasSuccessful ? '1' : '0')
                );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        return $this->_db->getLastInsertID();
    }

    /**
     * Updates the user_login table to indicate the user if performing
     * activity. The site name column "page_views" is also updated as a
     * summary column for later logging.
     *
     * @param integer ID for the user login
     * @param integer ID for the site
     */
    public function updateLastRefresh($userLoginID)
    {
        $sql = sprintf(
                "UPDATE
                user_login
                SET
                date_refreshed = NOW()
                WHERE
                user_login_id = %s",
                $this->_db->makeQueryInteger($userLoginID)
                );

        $this->_db->query($sql);

        // FIXME: Don't hit "site" on each request. Lets make a new table.
        $this->_db->query(
                "UPDATE
                site
                SET
                page_views = page_views + 1"
                );
    }

    /**
     * Get information about users that have performed an action
     * in the last 20 seconds. For all intents and purposes, all
     * active users.
     *
     * @return array
     */
    public function getLoadData()
    {
        // FIXME: This definatly has to need some optimization.
        $sql = sprintf(
                "SELECT
                COUNT(DISTINCT user_login.user_id) AS loggedInUsers,
                COUNT(
                    DISTINCT IF(
                        date_refreshed > DATE_SUB(NOW(), INTERVAL 20 SECOND),
                        user_login.user_id,
                        NULL
                        )
                    ) AS activeUsers
                FROM
                user_login
                WHERE
                date_refreshed > DATE_SUB(NOW(), INTERVAL 10 MINUTE)"
                );

        $rs = $this->_db->getAssoc($sql);

        if (!isset($rs['loggedInUsers']) || empty($rs['loggedInUsers']))
        {
            $rs['loggedInUsers'] = 0;
        }

        if (!isset($rs['activeUsers']) || empty($rs['activeUsers']))
        {
            $rs['activeUsers'] = 0;
        }

        return $rs;
    }

    /**
     * Get information about all users that have logged into CATS
     * and performed an action in the last 14 days.
     *
     * @return array
     */
    public function getUsageData()
    {
        // FIXME: This definatly has to need some optimization.
        $sql = sprintf(
                "SELECT
                COUNT(DISTINCT user_login.user_id) AS inUseUsers
                FROM
                user_login
                WHERE
                date_refreshed > DATE_SUB(NOW(), INTERVAL 14 DAY)"
                );

        $rs = $this->_db->getAssoc($sql);

        if (!isset($rs['inUseUsers']) || empty($rs['inUseUsers']))
        {
            $rs['inUseUsers'] = 0;
        }

        return $rs['inUseUsers'];
    }

    /**
     * Get a list of all active users for a given site with an optional
     * imposed limit.
     *
     * @param integer Limit the results to this number of users
     * @param integer ID of the site making the request.
     * @return array
     */
    public function getLoggedInUsers($limit)
    {
        if ($limit > 0)
        {
            $limitSQL = sprintf(
                    'LIMIT %s', $this->_db->makeQueryInteger($limit)
                    );
        }
        else
        {
            $limitSQL = '';
        }

        $sql = sprintf(
                "SELECT
                MAX(user_login.user_login_id) AS userLoginID,
                user.user_id AS userID,
                user.first_name AS firstName,
                user.last_name AS lastName,
                DATE_FORMAT(
                    user_login.date_refreshed, '" . DateUtility::getMysqlTimeFormat() . "'
                    ) AS lastRefresh,
                IF(
                    user_login.date_refreshed > DATE_SUB(NOW(), INTERVAL 20 SECOND),
                    1,
                    0
                  ) AS active
                FROM
                user
                LEFT JOIN user_login
                ON user_login.user_id = user.user_id
                WHERE
                user_login.date_refreshed > DATE_SUB(NOW(), INTERVAL 10 MINUTE)
                GROUP BY
                user_login.user_login_id
                ORDER BY
                user_login.date_refreshed DESC
                %s",
            $limitSQL
                );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns the user ID of the automated user.
     *
     * @param integer user ID
     * @return array user data
     */
    public function getAutomatedUser()
    {
        $sql =
                "SELECT
                user.user_id AS userID
                FROM
                user
                WHERE
                user.user_name = 'cats@rootadmin'";
        $rs = $this->_db->getAssoc($sql);

        if (!isset($rs['userID']))
        {
            $sql =
                    "INSERT INTO user (
                user_name,
                password,
                access_level,
                can_change_password,
                is_test_user,
                email,
                first_name,
                last_name
                    )
                    VALUES (
                        'cats@rootadmin',
                        '',
                        0,
                        0,
                        0,
                        '',
                        'CATS',
                        'Automated'
                        )";
            $this->_db->query($sql);

            $rs = $this->getAutomatedUser();
        }

        return $rs;
    }

    private function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Upgrades an existing password hash to the current PASSWORD_DEFAULT algorithm/options
     * after a successful login, if password_needs_rehash() indicates that an update
     * is required.
     *
     * @param int    $userID     ID of the user whose password hash may be updated.
     * @param string $storedHash Hash value as it was read from the database before verification.
     * @param string $password   Plain-text password that was just successfully verified.
     *
     * @return void
     */
    private function rehashPasswordIfNeeded($userID, $storedHash, $password)
    {
        if ($this->isLegacyPasswordHash($storedHash) || $storedHash === LDAPUSER_PASSWORD)
        {
            return;
        }

        if (password_needs_rehash($storedHash, PASSWORD_DEFAULT))
        {
            $this->updatePasswordHash($userID, $password);
        }
    }

    /* FIXME: The following methods exist only for backwards compatibility with legacy MD5 password hashes.
     * They also perform a one-time lazy migration from MD5 to password_hash().
     * After several releases with the new algorithm in place, this MD5-related code should be removed
     * from the codebase.
     */

    private function isLegacyPasswordHash($hash)
    {
        return (bool) preg_match('/^[0-9a-f]{32}$/i', $hash);
    }

    private function verifyAndMigratePassword($userID, $password, $storedHash)
    {
        if ($this->isLegacyPasswordHash($storedHash))
        {
            if (md5($password) !== $storedHash)
            {
                return false;
            }

            $this->updatePasswordHash($userID, $password);

            return true;
        }

        return password_verify($password, $storedHash);
    }

    private function updatePasswordHash($userID, $password)
    {
        $sql = sprintf(
                "UPDATE
                user
                SET
                password = %s
                WHERE
                user.user_id = %s",
                $this->_db->makeQueryString($this->hashPassword($password)),
                $this->_db->makeQueryInteger($userID)
                );

        $this->_db->query($sql);
    }

    // End of temporary MD5 compatibility and lazy migration code.

    public function isUserLDAP($userID)
    {
        $sql = sprintf(
                "SELECT
                user.password AS password
                FROM
                user
                WHERE
                user.user_id = %s",
                $this->_db->makeQueryString($userID)
                );
        $rs = $this->_db->getAssoc($sql);
        
        return ($rs['password'] == LDAPUSER_PASSWORD);
    }
    
}

?>
