<?php
/**
 * CATS
 * API Job Order Handler
 *
 * Handles CRUD operations for Job Orders.
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * Copyright (C) 2026 Space-O Technologies (https://www.spaceotechnologies.com/)
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * @package    CATS
 * @subpackage API
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 */

include_once('./lib/JobOrders.php');
include_once(dirname(__FILE__) . '/../formatters/EntityFormatter.php');
include_once(dirname(__FILE__) . '/../traits/ApiHelpers.php');

class JobOrderHandler
{
    use ApiHelpers;

    private $_siteID;
    private $_userID;
    protected $_requestLogger;

    public function __construct($siteID, $userID, $requestLogger = null)
    {
        $this->_siteID = $siteID;
        $this->_userID = $userID;
        $this->_requestLogger = $requestLogger;
    }

    /**
     * Handle job orders endpoint
     * Supports: GET (list/single), POST (create), PUT (update), DELETE
     */
    public function handle()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $method = $_SERVER['REQUEST_METHOD'];

        $jobOrders = new JobOrders($this->_siteID);

        switch ($method) {
            case 'GET':
                $this->handleGet($jobOrders, $id);
                break;
            case 'POST':
                $this->handlePost($jobOrders);
                break;
            case 'PUT':
                $this->handlePut($jobOrders, $id);
                break;
            case 'DELETE':
                $this->handleDelete($jobOrders, $id);
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    private function handleGet($jobOrders, $id)
    {
        if ($id) {
            $job = $jobOrders->get($id);
            if ($job && is_array($job) && count($job) > 0) {
                $this->sendSuccess(EntityFormatter::formatJobOrder($job));
            } else {
                $this->sendError('Job order not found', 404);
            }
        } else {
            $this->handleList($jobOrders);
        }
    }

    private function handleList($jobOrders)
    {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $status = isset($_GET['status']) ? trim($_GET['status']) : '';
        $city = isset($_GET['city']) ? trim($_GET['city']) : '';
        $state = isset($_GET['state']) ? trim($_GET['state']) : '';

        $pagination = $this->getPaginationParams();

        $jobsData = $jobOrders->getAll(JOBORDERS_STATUS_ALL, -1, -1);

        $jobs = [];
        if (is_array($jobsData)) {
            foreach ($jobsData as $row) {
                if (!empty($search)) {
                    $titleMatch = stripos($row['title'] ?? '', $search) !== false;
                    $descMatch = stripos($row['description'] ?? '', $search) !== false;
                    if (!$titleMatch && !$descMatch) continue;
                }
                if (!empty($status) && ($row['status'] ?? '') !== $status) continue;
                if (!empty($city) && stripos($row['city'] ?? '', $city) === false) continue;
                if (!empty($state) && ($row['state'] ?? '') !== $state) continue;

                $jobs[] = EntityFormatter::formatJobOrder($row);
            }
        }

        $this->sendPaginatedResponse($jobs, $pagination['page'], $pagination['limit']);
    }

    private function handlePost($jobOrders)
    {
        $input = $this->getRequestBody();

        if (empty($input['title'])) {
            $this->sendError('Missing required field: title', 400);
            return;
        }
        if (empty($input['companyID'])) {
            $this->sendError('Missing required field: companyID', 400);
            return;
        }

        $title = $input['title'];
        $companyID = intval($input['companyID']);
        $contactID = isset($input['contactID']) ? intval($input['contactID']) : 0;
        $description = isset($input['description']) ? $input['description'] : '';
        $notes = isset($input['notes']) ? $input['notes'] : '';
        $duration = isset($input['duration']) ? $input['duration'] : '';
        $maxRate = isset($input['maxRate']) ? $input['maxRate'] : '';
        $type = isset($input['type']) ? $input['type'] : 'H';
        $isHot = isset($input['isHot']) ? intval($input['isHot']) : 0;
        $public = isset($input['isPublic']) ? intval($input['isPublic']) : 0;
        $openings = isset($input['openings']) ? intval($input['openings']) : 1;
        $companyJobID = isset($input['companyJobID']) ? $input['companyJobID'] : '';
        $salary = isset($input['salary']) ? $input['salary'] : '';
        $city = isset($input['city']) ? $input['city'] : '';
        $state = isset($input['state']) ? $input['state'] : '';
        $startDate = isset($input['startDate']) ? $input['startDate'] : '';
        $recruiter = isset($input['recruiterID']) ? intval($input['recruiterID']) : $this->_userID;
        $owner = isset($input['ownerID']) ? intval($input['ownerID']) : $this->_userID;
        $department = isset($input['department']) ? $input['department'] : '';

        $jobOrderID = $jobOrders->add(
            $title, $companyID, $contactID, $description, $notes,
            $duration, $maxRate, $type, $isHot, $public,
            $openings, $companyJobID, $salary, $city, $state,
            $startDate, $this->_userID, $recruiter, $owner, $department
        );

        if ($jobOrderID <= 0) {
            $this->sendError('Failed to create job order', 500);
            return;
        }

        $newJob = $jobOrders->get($jobOrderID);
        $this->sendSuccess(EntityFormatter::formatJobOrder($newJob), 201);
    }

    private function handlePut($jobOrders, $id)
    {
        if (!$id) {
            $this->sendError('Job Order ID required for update', 400);
            return;
        }

        $existing = $jobOrders->get($id);
        if (!$existing || empty($existing['joborder_id'])) {
            $this->sendError('Job Order not found', 404);
            return;
        }

        $input = $this->getRequestBody();

        $title = isset($input['title']) ? $input['title'] : $existing['title'];
        $companyJobID = isset($input['companyJobID']) ? $input['companyJobID'] : ($existing['client_job_id'] ?? '');
        $companyID = isset($input['companyID']) ? intval($input['companyID']) : $existing['company_id'];
        $contactID = isset($input['contactID']) ? intval($input['contactID']) : ($existing['contact_id'] ?? 0);
        $description = isset($input['description']) ? $input['description'] : $existing['description'];
        $notes = isset($input['notes']) ? $input['notes'] : ($existing['notes'] ?? '');
        $duration = isset($input['duration']) ? $input['duration'] : ($existing['duration'] ?? '');
        $maxRate = isset($input['maxRate']) ? $input['maxRate'] : ($existing['rate_max'] ?? '');
        $type = isset($input['type']) ? $input['type'] : ($existing['type'] ?? 'H');
        $isHot = isset($input['isHot']) ? intval($input['isHot']) : ($existing['is_hot'] ?? 0);
        $openings = isset($input['openings']) ? intval($input['openings']) : ($existing['openings'] ?? 1);
        $openingsAvailable = isset($input['openingsAvailable']) ? intval($input['openingsAvailable']) : ($existing['openings_available'] ?? $openings);
        $salary = isset($input['salary']) ? $input['salary'] : ($existing['salary'] ?? '');
        $city = isset($input['city']) ? $input['city'] : ($existing['city'] ?? '');
        $state = isset($input['state']) ? $input['state'] : ($existing['state'] ?? '');
        $startDate = isset($input['startDate']) ? $input['startDate'] : ($existing['start_date'] ?? '');
        $status = isset($input['status']) ? $input['status'] : ($existing['status'] ?? 'Active');
        $recruiter = isset($input['recruiterID']) ? intval($input['recruiterID']) : ($existing['recruiter'] ?? $this->_userID);
        $owner = isset($input['ownerID']) ? intval($input['ownerID']) : ($existing['owner'] ?? $this->_userID);
        $public = isset($input['isPublic']) ? intval($input['isPublic']) : ($existing['public'] ?? 0);
        $email = 0;
        $emailAddress = '';
        $department = isset($input['department']) ? $input['department'] : '';

        $success = $jobOrders->update(
            $id, $title, $companyJobID, $companyID, $contactID,
            $description, $notes, $duration, $maxRate, $type,
            $isHot, $openings, $openingsAvailable, $salary, $city,
            $state, $startDate, $status, $recruiter, $owner,
            $public, $email, $emailAddress, $department
        );

        if (!$success) {
            $this->sendError('Failed to update job order', 500);
            return;
        }

        $updated = $jobOrders->get($id);
        $this->sendSuccess(EntityFormatter::formatJobOrder($updated));
    }

    private function handleDelete($jobOrders, $id)
    {
        if (!$id) {
            $this->sendError('Job Order ID required for delete', 400);
            return;
        }

        $existing = $jobOrders->get($id);
        if (!$existing || empty($existing['joborder_id'])) {
            $this->sendError('Job Order not found', 404);
            return;
        }

        $success = $jobOrders->delete($id);

        if (!$success) {
            $this->sendError('Failed to delete job order', 500);
            return;
        }

        $this->sendSuccess([
            'message' => 'Job Order deleted successfully',
            'id' => $id
        ]);
    }
}
