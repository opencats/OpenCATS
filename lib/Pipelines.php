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

include_once(LEGACY_ROOT . '/lib/History.php');
include_once(LEGACY_ROOT . '/lib/JobOrderStatuses.php');

/**
 *	Pipelines Library
 *	@package    CATS
 *	@subpackage Library
 */
class Pipelines
{
    private $_db;
    public function __construct()
    {
        $this->_db = DatabaseConnection::getInstance();
    }


    /**
     * Adds a candidate to the pipeline for a job order.
     *
     * @param integer job order ID
     * @param integer candidate ID
     * @return true on success; false otherwise.
     */
    public function add($candidateID, $jobOrderID, $userID = 0)
    {
        if (!is_scalar($jobOrderID) || !ctype_digit((string) $jobOrderID) || (int) $jobOrderID <= 0 ||
            !is_scalar($candidateID) || !ctype_digit((string) $candidateID) || (int) $candidateID <= 0) return false;
        if (!$this->_db->beginTransaction()) return false;
        try
        {
            $this->_db->getAssoc('SELECT joborder_id FROM joborder WHERE joborder_id = ' .
                $this->_db->makeQueryInteger($jobOrderID) . ' FOR UPDATE');
            include_once(LEGACY_ROOT . '/lib/JobOrders.php');
            $jobOrders = new JobOrders();
            if ((string) $userID === '0')
            {
                // Career Portal applications have no recruiter user ID or staff session.
                $job = $jobOrders->get($jobOrderID);
                if (!$job || empty($job['public']) || !empty($job['isAdminHidden']))
                    throw new RuntimeException('Public Job Order unavailable.');
            }
            else
            {
                $job = $jobOrders->getForPipeline($jobOrderID);
            }
            if (JobOrderStatuses::requiresReopening($job['status']))
                throw new RuntimeException('Reopen the Job Order before adding candidates.');
            $sql = sprintf(
                "SELECT
                    COUNT(candidate_id) AS candidateIDCount
                FROM
                    candidate_joborder
                WHERE
                    candidate_id = %s
                AND
                    joborder_id = %s",
                $this->_db->makeQueryInteger($candidateID),
                $this->_db->makeQueryInteger($jobOrderID)
            );
            $rs = $this->_db->getAssoc($sql);

            if (empty($rs))
            {
                $this->_db->rollbackTransaction(); return false;
            }

            $count = $rs['candidateIDCount'];
            if ($count > 0)
            {
                /* Candidate already exists in the pipeline. */
                $this->_db->rollbackTransaction(); return false;
            }

            $extraFields = '';
            $extraValues = '';

            if (!eval(Hooks::get('PIPELINES_ADD_SQL')))
            {
                $this->_db->rollbackTransaction();
                return false;
            }

            $sql = sprintf(
                "INSERT INTO candidate_joborder (
                    joborder_id,
                    candidate_id,
                    status,
                    added_by,
                    date_created,
                    date_modified%s
                )
                VALUES (
                    %s,
                    %s,
                    100,
                    %s,
                    NOW(),
                    NOW()%s
                )",
                $extraFields,
                $this->_db->makeQueryInteger($jobOrderID),
                $this->_db->makeQueryInteger($candidateID),
                $this->_db->makeQueryInteger($userID),
                $extraValues
            );

            $queryResult = $this->_db->query($sql);
            if (!$queryResult)
            {
                $this->_db->rollbackTransaction(); return false;
            }

            if (!$this->_db->commitTransaction()) throw new RuntimeException('Unable to save pipeline addition.');
            return true;
        }
        catch (Throwable $e)
        {
            $this->_db->rollbackTransaction();
            return false;
        }
    }

    /**
     * Removes a candidate from the pipeline for a job order.
     *
     * @param integer candidate ID
     * @param integer job order ID
     * @return void
     */
    public function remove($candidateID, $jobOrderID)
    {
        $sql = sprintf(
            "DELETE FROM
                candidate_joborder
            WHERE
                joborder_id = %s
            AND
                candidate_id = %s",
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_db->makeQueryInteger($candidateID)
        );
        $this->_db->query($sql);

        $sql = sprintf(
            "DELETE FROM
                candidate_joborder_status_history
            WHERE
                joborder_id = %s
            AND
                candidate_id = %s",
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_db->makeQueryInteger($candidateID)
        );
        $this->_db->query($sql);

        $history = new History();
        $history->storeHistoryData(
            DATA_ITEM_CANDIDATE,
            $candidateID,
            'PIPELINE',
            $jobOrderID,
            '(DELETE)',
            '(USER) deleted candidate from pipeline for job order ' . $jobOrderID . '.'
        );
        $history->storeHistoryData(
            DATA_ITEM_JOBORDER,
            $jobOrderID,
            'PIPELINE',
            $candidateID,
            '(DELETE)',
            '(USER) deleted job order from pipeline for candidate ' . $candidateID . '.'
        );
    }

    /**
     * Returns a single pipeline row.
     *
     * @param integer candidate ID
     * @param integer job order ID
     * @return array pipeline data
     */
    public function get($candidateID, $jobOrderID)
    {
        $sql = sprintf(
            "SELECT
                candidate_joborder.candidate_joborder_id as candidateJobOrderID,
                company.company_id AS companyID,
                company.name AS companyName,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.type AS type,
                joborder.duration AS duration,
                joborder.rate_max AS maxRate,
                joborder.status AS jobOrderStatus,
                joborder.salary AS salary,
                joborder.is_hot AS isHot,
                joborder.openings AS openings,
                joborder.openings_available AS openingsAvailable,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS start_date,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                candidate.candidate_id AS candidateID,
                candidate.email1 AS candidateEmail,
                candidate_joborder_status.candidate_joborder_status_id AS statusID,
                candidate_joborder_status.short_description AS status,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                candidate_joborder
            LEFT JOIN candidate
                ON candidate_joborder.candidate_id = candidate.candidate_id
            LEFT JOIN joborder
                ON candidate_joborder.joborder_id = joborder.joborder_id
            LEFT JOIN company
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            LEFT JOIN candidate_joborder_status
                ON candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            WHERE
                candidate.candidate_id = %s
            AND
                joborder.joborder_id = %s
",
            $this->_db->makeQueryInteger($candidateID),
            $this->_db->makeQueryInteger($jobOrderID)
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Returns a pipeline entry's candidate-joborder ID from the specified
     * candidate ID and job order ID; -1 if not found.
     *
     * @param integer candidate ID
     * @param integer job order ID
     * @return integer candidate-joborder ID or -1 if not found
     */
    public function getCandidateJobOrderID($candidateID, $jobOrderID)
    {
        $sql = sprintf(
            "SELECT
                candidate_joborder_id AS candidateJobOrderID
            FROM
                candidate_joborder
            WHERE
                candidate_id = %s
            AND
                joborder_id = %s",
            $this->_db->makeQueryInteger($candidateID),
            $this->_db->makeQueryInteger($jobOrderID)
        );
        $rs = $this->_db->getAssoc($sql);

        if (empty($rs))
        {
            return -1;
        }

        return (int) $rs['candidateJobOrderID'];
    }

    // FIXME: Document me.
    public function setStatus($candidateID, $jobOrderID, $statusID,
                              $emailAddress, $emailText)
    {
        if (!$this->_db->beginTransaction()) throw new RuntimeException('Pipeline status requires its own transaction.');
        try
        {
            /* Get existing status. */
            $sql = sprintf(
                "SELECT
                    status AS oldStatusID,
                    candidate_joborder_id AS candidateJobOrderID
                FROM
                    candidate_joborder
                WHERE
                    joborder_id = %s
                AND
                    candidate_id = %s FOR UPDATE",
                $this->_db->makeQueryInteger($jobOrderID),
                $this->_db->makeQueryInteger($candidateID)
            );
            $rs = $this->_db->getAssoc($sql);

            if (empty($rs))
            {
                if (!$this->_db->commitTransaction()) throw new RuntimeException('Unable to commit pipeline status.');
                return;
            }

            $candidateJobOrderID = $rs['candidateJobOrderID'];
            $oldStatusID         = $rs['oldStatusID'];

            if ($oldStatusID == $statusID)
            {
                /* No need to update the database and scew the history if there is
                 * no actual change.
                 */
                if (!$this->_db->commitTransaction()) throw new RuntimeException('Unable to commit pipeline status.');
                return;
            }

            /* Change status. */
            $sql = sprintf(
                "UPDATE
                    candidate_joborder
                SET
                    status        = %s,
                    date_modified = NOW()
                WHERE
                    candidate_joborder_id = %s",
                $this->_db->makeQueryInteger($statusID),
                $this->_db->makeQueryInteger($candidateJobOrderID)
            );
            if (!$this->_db->query($sql, true)) throw new RuntimeException('Unable to update pipeline status.');

            /* Add history. */
            $historyID = $this->addStatusHistory(
                $candidateID, $jobOrderID, $statusID, $oldStatusID
            );

            if ($historyID < 0) throw new RuntimeException('Unable to record pipeline status history.');

            /* Add auditing history. */
            $historyDescription = '(USER) changed pipeline status of candidate '
                . $candidateID . ' for job order ' . $jobOrderID . '.';
            $history = new History();
            if (!$history->storeHistoryData(
                DATA_ITEM_PIPELINE,
                $candidateJobOrderID,
                'PIPELINE',
                $oldStatusID,
                $statusID,
                $historyDescription
            )) throw new RuntimeException('Unable to record pipeline audit history.');

            if (!$this->_db->commitTransaction()) throw new RuntimeException('Unable to commit pipeline status.');
        }
        catch (Throwable $e)
        {
            $this->_db->rollbackTransaction();
            throw $e;
        }

        if (!empty($emailAddress))
        {
            /* Send e-mail notification. */
            //FIXME: Make subject configurable.
            $mailer = new Mailer();
            $mailerStatus = $mailer->sendToOne(
                array($emailAddress, ''),
                CANDIDATE_STATUSCHANGE_SUBJECT,
                $emailText,
                true
            );
        }
    }

    // FIXME: Document me.
    public function getStatuses()
    {
        $sql =
            "SELECT
                candidate_joborder_status_id AS statusID,
                short_description AS status,
                can_be_scheduled AS canBeScheduled,
                triggers_email AS triggersEmail
            FROM
                candidate_joborder_status
            WHERE
                is_enabled = 1
            ORDER BY
                candidate_joborder_status_id ASC";

        return $this->_db->getAllAssoc($sql);
    }

    // FIXME: Document me.
    // Throws out No Status.
    public function getStatusesForPicking()
    {
        $sql =
            "SELECT
                candidate_joborder_status_id AS statusID,
                short_description AS status,
                can_be_scheduled AS canBeScheduled,
                triggers_email AS triggersEmail
            FROM
                candidate_joborder_status
            WHERE
                is_enabled = 1
            AND
                candidate_joborder_status_id != 0
            ORDER BY
                candidate_joborder_status_id ASC";

        return $this->_db->getAllAssoc($sql);
    }

    // FIXME: Document me.
    public function addStatusHistory($candidateID, $jobOrderID, $statusToID,
                                     $statusFromID)
    {
        $sql = sprintf(
            "INSERT INTO candidate_joborder_status_history (
                joborder_id,
                candidate_id,
                date,
                status_to,
                status_from
            )
            VALUES (
                %s,
                %s,
                NOW(),
                %s,
                %s
            )",
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_db->makeQueryInteger($candidateID),
            $this->_db->makeQueryInteger($statusToID),
            $this->_db->makeQueryInteger($statusFromID)
        );

        $queryResult = $this->_db->query($sql, true);
        if (!$queryResult)
        {
            return -1;
        }

        return $this->_db->getLastInsertID();
    }

    /**
     * Returns a candidate's pipeline.
     *
     * @param integer candidate ID
     * @return array pipeline data
     */
    public function getCandidatePipeline($candidateID)
    {
        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS companyName,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.type AS type,
                joborder.duration AS duration,
                joborder.rate_max AS maxRate,
                joborder.status AS jobOrderStatus,
                joborder.salary AS salary,
                joborder.is_hot AS isHot,
                joborder.client_job_id AS clientJobID,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS start_date,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                candidate.candidate_id AS candidateID,
                candidate.email1 AS candidateEmail,
                candidate_joborder_status.candidate_joborder_status_id AS statusID,
                candidate_joborder_status.short_description AS status,
                candidate_joborder.candidate_joborder_id AS candidateJobOrderID,
                candidate_joborder.rating_value AS ratingValue,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                added_user.first_name AS addedByFirstName,
                added_user.last_name AS addedByLastName
            FROM
                candidate_joborder
            INNER JOIN candidate
                ON candidate_joborder.candidate_id = candidate.candidate_id
            INNER JOIN joborder
                ON candidate_joborder.joborder_id = joborder.joborder_id
            INNER JOIN company
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            LEFT JOIN user AS added_user
                ON candidate_joborder.added_by = added_user.user_id
            INNER JOIN candidate_joborder_status
                ON candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            WHERE
                candidate.candidate_id = %s",
            $this->_db->makeQueryInteger($candidateID)
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns a candidate's non-closed pipeline.
     *
     * @param integer candidate ID
     * @return array pipeline data
     */
    public function getNonClosedCandidatePipeline($candidateID)
    {
        $sql = sprintf(
            "SELECT
                company.company_id AS companyID,
                company.name AS companyName,
                joborder.joborder_id AS jobOrderID,
                joborder.title AS title,
                joborder.type AS type,
                joborder.duration AS duration,
                joborder.rate_max AS maxRate,
                joborder.status AS jobOrderStatus,
                joborder.salary AS salary,
                joborder.is_hot AS isHot,
                joborder.client_job_id AS clientJobID,
                DATE_FORMAT(
                    joborder.start_date, '%%m-%%d-%%y'
                ) AS start_date,
                DATE_FORMAT(
                    joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                candidate.candidate_id AS candidateID,
                candidate.email1 AS candidateEmail,
                candidate_joborder_status.candidate_joborder_status_id AS statusID,
                candidate_joborder_status.short_description AS status,
                candidate_joborder.candidate_joborder_id AS candidateJobOrderID,
                candidate_joborder.rating_value AS ratingValue,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                added_user.first_name AS addedByFirstName,
                added_user.last_name AS addedByLastName
            FROM
                candidate_joborder
            INNER JOIN candidate
                ON candidate_joborder.candidate_id = candidate.candidate_id
            INNER JOIN joborder
                ON candidate_joborder.joborder_id = joborder.joborder_id
            INNER JOIN company
                ON company.company_id = joborder.company_id
            LEFT JOIN user AS owner_user
                ON joborder.owner = owner_user.user_id
            LEFT JOIN user AS added_user
                ON candidate_joborder.added_by = added_user.user_id
            INNER JOIN candidate_joborder_status
                ON candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            WHERE
                candidate.candidate_id = %s
            AND
                joborder.status NOT IN %s",
            $this->_db->makeQueryInteger($candidateID),
            JobOrderStatuses::getClosedStatusSQL()
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Returns a job order's pipeline.
     *
     * @param integer job order ID
     * @return array pipeline data
     */
    public function getJobOrderPipeline($jobOrderID, $orderBy = '')
    {
        /* FIXME: CONCAT() stuff is a very ugly hack, but I don't think there
         * is a way to return multiple values from a subquery.
         */
        $sql = sprintf(
            "SELECT
                IF(attachment_id, 1, 0) AS attachmentPresent,
                IF(old_candidate_id, 1, 0) AS isDuplicateCandidate,
                candidate.candidate_id AS candidateID,
                candidate.first_name AS firstName,
                candidate.is_admin_hidden AS isAdminHidden,
                candidate.last_name AS lastName,
                candidate.state As state,
                candidate.email1 AS candidateEmail,
                candidate_joborder.status AS jobOrderStatus,
                candidate.is_hot AS isHotCandidate,
                DATE_FORMAT(
                    candidate_joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                UNIX_TIMESTAMP(candidate_joborder.date_created) AS dateCreatedInt,
                candidate_joborder_status.short_description AS status,
                candidate_joborder.candidate_joborder_id AS candidateJobOrderID,
                candidate_joborder.rating_value AS ratingValue,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName,
                (
                    SELECT
                        CONCAT(
                            '<strong>',
                            DATE_FORMAT(activity.date_occurred, '%%m-%%d-%%y'),
                            ' (',
                            entered_by_user.first_name,
                            ' ',
                            entered_by_user.last_name,
                            '):</strong> ',
                            IF(
                                ISNULL(activity.notes) OR activity.notes = '',
                                '(No Notes)',
                                activity.notes
                            )
                        )
                    FROM
                        activity
                    LEFT JOIN activity_type
                        ON activity.type = activity_type.activity_type_id
                    LEFT JOIN user AS entered_by_user
                        ON activity.entered_by = entered_by_user.user_id
                    WHERE
                        activity.data_item_id = candidate.candidate_id
                    AND
                        activity.data_item_type = %s
                    AND
                        activity.joborder_id = %s
                    ORDER BY
                        activity.date_occurred DESC
                    LIMIT 1
                ) AS lastActivity,
                IF((
                    SELECT
                        COUNT(*)
                    FROM
                        candidate_joborder_status_history
                    WHERE
                        joborder_id = %s
                    AND
                        candidate_id = candidate.candidate_id
                    AND
                        status_to = %s
                ) >= 1, 1, 0) AS submitted,
                added_user.first_name AS addedByFirstName,
                added_user.last_name AS addedByLastName
            FROM
                candidate_joborder
            LEFT JOIN candidate
                ON candidate_joborder.candidate_id = candidate.candidate_id
            LEFT JOIN user AS owner_user
                ON candidate.owner = owner_user.user_id
            LEFT JOIN user AS added_user
                ON candidate_joborder.added_by = added_user.user_id
            LEFT JOIN attachment
                ON candidate.candidate_id = attachment.data_item_id
            LEFT JOIN candidate_joborder_status
                ON candidate_joborder.status = candidate_joborder_status.candidate_joborder_status_id
            LEFT JOIN candidate_duplicates
                ON candidate_duplicates.new_candidate_id = candidate.candidate_id
            WHERE
                candidate_joborder.joborder_id = %s
            GROUP BY
                candidate_joborder.candidate_id
            %s",
            DATA_ITEM_CANDIDATE,
            $this->_db->makeQueryInteger($jobOrderID),
            $this->_db->makeQueryInteger($jobOrderID),
            PIPELINE_STATUS_SUBMITTED,
            $this->_db->makeQueryInteger($jobOrderID),
            $orderBy
        );

        include_once(LEGACY_ROOT . '/lib/CandidateAuthorization.php');
        $visibleRows = array();
        foreach ($this->_db->getAllAssoc($sql) as $row)
        {
            if (!empty($row['candidateID']) && CandidateAuthorization::canAccessCandidateRecord($row))
            {
                $visibleRows[] = $row;
            }
        }

        return $visibleRows;
    }

    /**
     * Check both parent objects before accepting a pipeline row ID from a request.
     */
    public function canAccess($candidateJobOrderID)
    {
        if ($_SESSION['CATS']->getAccessLevel('candidates.show') < ACCESS_LEVEL_READ ||
            $_SESSION['CATS']->getAccessLevel('joborders.show') < ACCESS_LEVEL_READ)
        {
            return false;
        }

        include_once(LEGACY_ROOT . '/lib/CandidateAuthorization.php');
        $sql = sprintf(
            "SELECT
                candidate.candidate_id AS candidateID,
                candidate.is_admin_hidden AS isAdminHidden,
                joborder.is_admin_hidden AS isJobOrderAdminHidden
            FROM
                candidate_joborder
            INNER JOIN candidate
                ON candidate.candidate_id = candidate_joborder.candidate_id
            INNER JOIN joborder
                ON joborder.joborder_id = candidate_joborder.joborder_id
            WHERE
                candidate_joborder.candidate_joborder_id = %s",
            $this->_db->makeQueryInteger($candidateJobOrderID)
        );
        $record = $this->_db->getAssoc($sql);

        return $this->canAccessRecord($record);
    }

    private function canAccessRecord($record)
    {
        return CandidateAuthorization::canAccessCandidateRecord($record) &&
            ($record['isJobOrderAdminHidden'] != 1 ||
             $_SESSION['CATS']->getAccessLevel('joborders.hidden') >= ACCESS_LEVEL_SA);
    }

    /** Retained milestone attainments, one row per candidate/Job/stage in the window.
     * Uses the same Candidate/Job show and hidden-record rules as getAccessMap().
     * EXISTS avoids multiplying history when legacy duplicate relationships exist.
     */
    public function getOutcomeRows($start, $end, $options = array())
    {
        foreach (array($start, $end) as $date)
            if (!is_string($date) || !DateUtility::isValidDatabaseDateTime($date . ' 00:00:00'))
                throw new InvalidArgumentException('Invalid outcome period.');
        if ($start > $end || DateUtility::getCalendarDaysBetween($start, $end) > 366)
            throw new InvalidArgumentException('Invalid outcome period.');
        if (!isset($_SESSION['CATS']) || !$_SESSION['CATS']->isLoggedIn() ||
            $_SESSION['CATS']->getUserID() <= 0) return;
        foreach (array('reports.show', 'candidates.show', 'joborders.show') as $action)
            if ($_SESSION['CATS']->getAccessLevel($action) < ACCESS_LEVEL_READ) return;
        $sorts = array('date'=>'firstRecorded', 'candidate'=>'title', 'job'=>'jobTitle', 'repeats'=>'repeats');
        $sort = $options['sort'] ?? 'date';
        $direction = $options['direction'] ?? 'desc';
        if (!isset($sorts[$sort]) || !in_array($direction, array('asc','desc'), true))
            throw new InvalidArgumentException('Invalid outcome ordering.');
        foreach (array('milestone','desk','page') as $key)
            if (isset($options[$key]) && (!is_int($options[$key]) || $options[$key] < ($key==='desk' ? 0 : 1)))
                throw new InvalidArgumentException('Invalid outcome selection.');
        if (isset($options['milestone']) && !in_array($options['milestone'], array(400,500,600,800), true))
            throw new InvalidArgumentException('Invalid outcome milestone.');
        $offset = (int) ((float) $_SESSION['CATS']->getTimeZoneOffset() * 3600);
        $from = date('Y-m-d H:i:s', strtotime($start . ' 00:00:00') - $offset);
        $until = date('Y-m-d H:i:s', strtotime($end . ' 00:00:00 +1 day') - $offset);
        $where = 'h.status_to IN (400,500,600,800) AND h.status_from <> h.status_to
            AND h.date >= ' . $this->_db->makeQueryString($from) . '
            AND h.date < ' . $this->_db->makeQueryString($until) . ' AND h.date <= NOW()
            AND EXISTS (SELECT 1 FROM candidate_joborder p WHERE p.candidate_id=h.candidate_id AND p.joborder_id=h.joborder_id)';
        if ($_SESSION['CATS']->getAccessLevel('candidates.hidden') < ACCESS_LEVEL_SA)
            $where .= ' AND COALESCE(c.is_admin_hidden,0) <> 1';
        if ($_SESSION['CATS']->getAccessLevel('joborders.hidden') < ACCESS_LEVEL_SA)
            $where .= ' AND COALESCE(j.is_admin_hidden,0) <> 1';
        if (isset($options['milestone'])) $where .= ' AND h.status_to=' . $options['milestone'];
        if (isset($options['desk'])) $where .= ' AND COALESCE(j.desk_id,0)=' . $options['desk'];
        $summary = ($options['summary'] ?? false) === true;
        $after = array(0,0,0);
        do
        {
            $paged = isset($options['page']);
            $sql = "SELECT h.candidate_id AS candidateID, h.joborder_id AS jobID, h.status_to AS milestone,
                MIN(h.date) AS firstRecorded,
                DATE(DATE_ADD(MIN(h.date), INTERVAL " . $offset . " SECOND)) AS sourceDate,
                COUNT(*) AS repeats, CONCAT_WS(' ',c.first_name,c.last_name) AS title,
                j.title AS jobTitle, j.desk_id AS desk, j.is_speculative AS isSpeculative
                FROM candidate_joborder_status_history h
                INNER JOIN candidate c ON c.candidate_id=h.candidate_id
                INNER JOIN joborder j ON j.joborder_id=h.joborder_id
                WHERE " . $where . ($paged || $summary ? '' : ' AND (h.candidate_id,h.joborder_id,h.status_to) > (' . implode(',', $after) . ')') . '
                GROUP BY h.candidate_id,h.joborder_id,h.status_to,c.first_name,c.last_name,j.title,j.desk_id,j.is_speculative';
            if ($summary)
            {
                // Aggregate the date-bounded pair population once, rather than regrouping it for every page.
                $sql = 'SELECT milestone,desk,sourceDate,COUNT(*) AS pairs FROM (' . $sql .
                    ') AS attained GROUP BY milestone,desk,sourceDate ORDER BY sourceDate,milestone,desk';
                foreach ($this->_db->getAllAssoc($sql) as $row) yield $row;
                return;
            }
            $sql .= ' ORDER BY ' . ($paged ? $sorts[$sort] . ' ' . strtoupper($direction) . ', ' : '') .
                'h.candidate_id,h.joborder_id,h.status_to LIMIT ' . ($paged ? '25 OFFSET ' . (($options['page'] - 1) * 25) : '200');
            $rows = $this->_db->getAllAssoc($sql);
            foreach ($rows as $row)
            {
                $after = array((int) $row['candidateID'],(int) $row['jobID'],(int) $row['milestone']);
                yield $row;
            }
        } while (!$paged && count($rows) === 200);
    }

    /** Batch form of canAccess for bounded model collections; no cached permissions. */
    public function getAccessMap($ids)
    {
        if ($_SESSION['CATS']->getAccessLevel('candidates.show') < ACCESS_LEVEL_READ ||
            $_SESSION['CATS']->getAccessLevel('joborders.show') < ACCESS_LEVEL_READ) return array();
        include_once(LEGACY_ROOT . '/lib/CandidateAuthorization.php');
        $ids = array_unique(array_map('intval', $ids));
        $access = array();
        foreach (array_chunk($ids, 200) as $chunk)
        {
            $rows = $this->_db->getAllAssoc('SELECT candidate_joborder.candidate_joborder_id AS id,
                candidate.candidate_id AS candidateID, candidate.is_admin_hidden AS isAdminHidden,
                joborder.is_admin_hidden AS isJobOrderAdminHidden
                FROM candidate_joborder
                INNER JOIN candidate ON candidate.candidate_id = candidate_joborder.candidate_id
                INNER JOIN joborder ON joborder.joborder_id = candidate_joborder.joborder_id
                WHERE candidate_joborder.candidate_joborder_id IN (' . implode(',', $chunk) . ')');
            foreach ($rows as $record)
                $access[$record['id']] = $this->canAccessRecord($record);
        }
        return $access;
    }

    // FIXME: Document me.
    public function updateRatingValue($candidateJobOrderID, $value)
    {
        $sql = sprintf(
            "UPDATE
                candidate_joborder
            SET
                rating_value = %s
            WHERE
                candidate_joborder.candidate_joborder_id = %s",
            $this->_db->makeQueryInteger($value),
            $this->_db->makeQueryInteger($candidateJobOrderID)
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }
    }

    // FIXME: Document me.
    public function getRatingValue($candidateJobOrderID)
    {
        $sql = sprintf(
            "SELECT
                rating_value AS ratingValue
            FROM
                candidate_joborder
            WHERE
                candidate_joborder_id = %s",
            $this->_db->makeQueryInteger($candidateJobOrderID)
        );
        $rs = $this->_db->getAssoc($sql);

        if (!isset($rs['ratingValue']) || empty($rs['ratingValue']))
        {
            return 0;
        }

        return $rs['ratingValue'];
    }

    //FIXME: Document me.
    public function getPipelineDetails($candidateJobOrderID)
    {
        if (!$this->canAccess($candidateJobOrderID))
        {
            return array();
        }

        $sql = sprintf(
            "SELECT
                candidate.first_name AS firstName,
                candidate.last_name AS lastName,
                candidate.email1 AS candidateEmail,
                candidate_joborder.status AS jobOrderStatus,
                activity.notes AS notes,
                DATE_FORMAT(
                    candidate_joborder.date_created, '%%m-%%d-%%y'
                ) AS dateCreated,
                candidate_joborder.candidate_joborder_id AS candidateJobOrderID,
                candidate_joborder.rating_value AS ratingValue,
                entered_by_user.first_name AS enteredByFirstName,
                entered_by_user.last_name AS enteredByLastName,
                DATE_FORMAT(activity.date_modified, '" . DateUtility::getMysqlDateTimeSecondsFormat() . "') AS dateModified
            FROM
                candidate_joborder
            LEFT JOIN candidate
                ON candidate_joborder.candidate_id = candidate.candidate_id
            INNER JOIN activity
                ON activity.joborder_id = candidate_joborder.joborder_id
            LEFT JOIN user AS entered_by_user
                ON entered_by_user.user_id = activity.entered_by
            WHERE
                candidate_joborder.candidate_joborder_id = %s
            AND
                activity.data_item_type = %s
            AND
                activity.data_item_id = candidate_joborder.candidate_id
            ",
            $this->_db->makeQueryInteger($candidateJobOrderID),
            DATA_ITEM_CANDIDATE
        );

        return $this->_db->getAllAssoc($sql);
    }

}

?>
