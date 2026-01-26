<?php
/**
 * CATS
 * API Tearsheet Handler
 *
 * Handles CRUD operations for Tearsheets.
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

if (file_exists('./lib/Tearsheets.php')) {
    include_once('./lib/Tearsheets.php');
}
include_once(dirname(__FILE__) . '/../formatters/EntityFormatter.php');
include_once(dirname(__FILE__) . '/../traits/ApiHelpers.php');

class TearsheetHandler
{
    use ApiHelpers;

    private $_siteID;
    private $_userID;
    protected $_requestLogger;

    /**
     * Constructor
     *
     * @param int $siteID Site ID for multi-tenant isolation
     * @param int $userID User ID making the request
     * @param object|null $requestLogger Optional request logger instance
     */
    public function __construct($siteID, $userID, $requestLogger = null)
    {
        $this->_siteID = $siteID;
        $this->_userID = $userID;
        $this->_requestLogger = $requestLogger;
    }

    /**
     * Handle tearsheets endpoint
     * Supports: GET (list/single), POST (create), PUT (update), DELETE
     * Sub-actions: addjobs, removejobs, addcandidates, removecandidates, joborders, candidates
     */
    public function handle()
    {
        if (!class_exists('Tearsheets')) {
            $this->sendError('Tearsheets module not installed', 501);
            return;
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $subAction = isset($_GET['sub']) ? strtolower($_GET['sub']) : null;
        $method = $_SERVER['REQUEST_METHOD'];

        $tearsheets = new Tearsheets($this->_siteID);

        // Handle job association sub-actions
        if ($id && $subAction === 'addjobs' && $method === 'PUT') {
            $this->handleAddJobs($tearsheets, $id);
            return;
        }

        if ($id && $subAction === 'removejobs' && $method === 'DELETE') {
            $this->handleRemoveJobs($tearsheets, $id);
            return;
        }

        // Handle candidate association sub-actions
        if ($id && $subAction === 'addcandidates' && $method === 'PUT') {
            $this->handleAddCandidates($tearsheets, $id);
            return;
        }

        if ($id && $subAction === 'removecandidates' && $method === 'DELETE') {
            $this->handleRemoveCandidates($tearsheets, $id);
            return;
        }

        // Handle main CRUD operations
        switch ($method) {
            case 'GET':
                $this->handleGet($tearsheets, $id, $subAction);
                break;
            case 'POST':
                $this->handlePost($tearsheets);
                break;
            case 'PUT':
                $this->handlePut($tearsheets, $id);
                break;
            case 'DELETE':
                $this->handleDelete($tearsheets, $id);
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    private function handleGet($tearsheets, $id, $subAction)
    {
        if ($id) {
            if ($subAction === 'joborders') {
                $jobs = $tearsheets->getJobOrders($id);
                $formatted = [];
                foreach ($jobs as $job) {
                    $formatted[] = EntityFormatter::formatJobOrder($job);
                }
                $this->sendSuccess([
                    'total' => count($formatted),
                    'data' => $formatted
                ]);
            } elseif ($subAction === 'candidates') {
                $candidates = $tearsheets->getCandidates($id);
                $formatted = [];
                foreach ($candidates as $candidate) {
                    $formatted[] = EntityFormatter::formatCandidate($candidate);
                }
                $this->sendSuccess([
                    'total' => count($formatted),
                    'data' => $formatted
                ]);
            } else {
                $tearsheet = $tearsheets->get($id);
                if ($tearsheet) {
                    $response = EntityFormatter::formatTearsheet($tearsheet);

                    // Include candidates if requested
                    $include = isset($_GET['include']) ? strtolower($_GET['include']) : '';
                    if (strpos($include, 'candidates') !== false) {
                        $candidates = $tearsheets->getCandidates($id);
                        $formattedCandidates = [];
                        foreach ($candidates as $candidate) {
                            $formattedCandidates[] = EntityFormatter::formatCandidate($candidate);
                        }
                        $response['candidates'] = [
                            'total' => count($formattedCandidates),
                            'data' => $formattedCandidates
                        ];
                    }

                    // Include job orders if requested
                    if (strpos($include, 'joborders') !== false) {
                        $jobs = $tearsheets->getJobOrders($id);
                        $formattedJobs = [];
                        foreach ($jobs as $job) {
                            $formattedJobs[] = EntityFormatter::formatJobOrder($job);
                        }
                        $response['jobOrders'] = [
                            'total' => count($formattedJobs),
                            'data' => $formattedJobs
                        ];
                    }

                    $this->sendSuccess($response);
                } else {
                    $this->sendError('Tearsheet not found', 404);
                }
            }
        } else {
            $list = $tearsheets->getAll($this->_userID);
            $formatted = [];
            foreach ($list as $ts) {
                $formatted[] = EntityFormatter::formatTearsheet($ts);
            }
            $this->sendSuccess([
                'total' => count($formatted),
                'data' => $formatted
            ]);
        }
    }

    private function handlePost($tearsheets)
    {
        $input = $this->getRequestBody();

        if (empty($input['name'])) {
            $this->sendError('Missing required field: name', 400);
            return;
        }

        $description = isset($input['description']) ? $input['description'] : '';
        $isPublic = isset($input['isPublic']) ? (bool)$input['isPublic'] : false;

        $tearsheetID = $tearsheets->create(
            $this->_userID,
            $input['name'],
            $description,
            $isPublic
        );

        if (!$tearsheetID) {
            $this->sendError('Failed to create tearsheet', 500);
            return;
        }

        $newTearsheet = $tearsheets->get($tearsheetID);
        $this->sendSuccess(EntityFormatter::formatTearsheet($newTearsheet), 201);
    }

    private function handlePut($tearsheets, $id)
    {
        if (!$id) {
            $this->sendError('Tearsheet ID required for update', 400);
            return;
        }

        $existing = $tearsheets->get($id);
        if (!$existing) {
            $this->sendError('Tearsheet not found', 404);
            return;
        }

        $input = $this->getRequestBody();

        $name = isset($input['name']) ? $input['name'] : $existing['name'];
        $description = isset($input['description']) ? $input['description'] : $existing['description'];
        $isPublic = isset($input['isPublic']) ? (bool)$input['isPublic'] : (bool)$existing['is_public'];

        $success = $tearsheets->update($id, $name, $description, $isPublic);

        if (!$success) {
            $this->sendError('Failed to update tearsheet', 500);
            return;
        }

        $updated = $tearsheets->get($id);
        $this->sendSuccess(EntityFormatter::formatTearsheet($updated));
    }

    private function handleDelete($tearsheets, $id)
    {
        if (!$id) {
            $this->sendError('Tearsheet ID required for delete', 400);
            return;
        }

        $existing = $tearsheets->get($id);
        if (!$existing) {
            $this->sendError('Tearsheet not found', 404);
            return;
        }

        $success = $tearsheets->delete($id);

        if (!$success) {
            $this->sendError('Failed to delete tearsheet', 500);
            return;
        }

        $this->sendSuccess([
            'message' => 'Tearsheet deleted successfully',
            'id' => $id
        ]);
    }

    private function handleAddJobs($tearsheets, $tearsheetID)
    {
        $existing = $tearsheets->get($tearsheetID);
        if (!$existing) {
            $this->sendError('Tearsheet not found', 404);
            return;
        }

        $input = $this->getRequestBody();

        if (empty($input['jobOrderIds']) || !is_array($input['jobOrderIds'])) {
            $this->sendError('Missing required field: jobOrderIds (array)', 400);
            return;
        }

        $added = 0;
        $failed = [];

        foreach ($input['jobOrderIds'] as $jobId) {
            $jobId = intval($jobId);
            if ($tearsheets->addJobOrder($tearsheetID, $jobId, $this->_userID)) {
                $added++;
            } else {
                $failed[] = $jobId;
            }
        }

        $this->sendSuccess([
            'tearsheetId' => $tearsheetID,
            'added' => $added,
            'failed' => $failed,
            'message' => $added . ' job order(s) added to tearsheet'
        ]);
    }

    private function handleRemoveJobs($tearsheets, $tearsheetID)
    {
        $existing = $tearsheets->get($tearsheetID);
        if (!$existing) {
            $this->sendError('Tearsheet not found', 404);
            return;
        }

        $input = $this->getRequestBody();
        $jobIds = [];

        if (!empty($input['jobOrderIds'])) {
            $jobIds = $input['jobOrderIds'];
        } elseif (!empty($_GET['jobOrderIds'])) {
            $jobIds = explode(',', $_GET['jobOrderIds']);
        }

        if (empty($jobIds)) {
            $this->sendError('Missing required: jobOrderIds', 400);
            return;
        }

        $removed = 0;
        $failed = [];

        foreach ($jobIds as $jobId) {
            $jobId = intval($jobId);
            if ($tearsheets->removeJobOrder($tearsheetID, $jobId)) {
                $removed++;
            } else {
                $failed[] = $jobId;
            }
        }

        $this->sendSuccess([
            'tearsheetId' => $tearsheetID,
            'removed' => $removed,
            'failed' => $failed,
            'message' => $removed . ' job order(s) removed from tearsheet'
        ]);
    }

    /**
     * Handle adding candidates to a tearsheet
     * PUT /tearsheets?id={id}&sub=addcandidates
     * Body: {"candidateIds": [1, 2, 3]} or {"ids": [1, 2, 3]}
     */
    private function handleAddCandidates($tearsheets, $tearsheetID)
    {
        $existing = $tearsheets->get($tearsheetID);
        if (!$existing) {
            $this->sendError('Tearsheet not found', 404);
            return;
        }

        $input = $this->getRequestBody();

        // Support both "candidateIds" and "ids" field names
        $candidateIds = [];
        if (!empty($input['candidateIds']) && is_array($input['candidateIds'])) {
            $candidateIds = $input['candidateIds'];
        } elseif (!empty($input['ids']) && is_array($input['ids'])) {
            $candidateIds = $input['ids'];
        }

        if (empty($candidateIds)) {
            $this->sendError('Missing required field: candidateIds or ids (array)', 400);
            return;
        }

        $added = 0;
        $failed = [];

        foreach ($candidateIds as $candidateId) {
            $candidateId = intval($candidateId);
            if ($tearsheets->addCandidate($tearsheetID, $candidateId, $this->_userID)) {
                $added++;
            } else {
                $failed[] = $candidateId;
            }
        }

        $this->sendSuccess([
            'tearsheetId' => $tearsheetID,
            'added' => $added,
            'failed' => $failed,
            'message' => $added . ' candidate(s) added to tearsheet'
        ]);
    }

    /**
     * Handle removing candidates from a tearsheet
     * DELETE /tearsheets?id={id}&sub=removecandidates
     * Body: {"candidateIds": [1, 2, 3]} or {"ids": [1, 2, 3]}
     */
    private function handleRemoveCandidates($tearsheets, $tearsheetID)
    {
        $existing = $tearsheets->get($tearsheetID);
        if (!$existing) {
            $this->sendError('Tearsheet not found', 404);
            return;
        }

        $input = $this->getRequestBody();
        $candidateIds = [];

        // Support both "candidateIds" and "ids" field names
        if (!empty($input['candidateIds'])) {
            $candidateIds = $input['candidateIds'];
        } elseif (!empty($input['ids'])) {
            $candidateIds = $input['ids'];
        } elseif (!empty($_GET['candidateIds'])) {
            $candidateIds = explode(',', $_GET['candidateIds']);
        } elseif (!empty($_GET['ids'])) {
            $candidateIds = explode(',', $_GET['ids']);
        }

        if (empty($candidateIds)) {
            $this->sendError('Missing required: candidateIds or ids', 400);
            return;
        }

        $removed = 0;
        $failed = [];

        foreach ($candidateIds as $candidateId) {
            $candidateId = intval($candidateId);
            if ($tearsheets->removeCandidate($tearsheetID, $candidateId)) {
                $removed++;
            } else {
                $failed[] = $candidateId;
            }
        }

        $this->sendSuccess([
            'tearsheetId' => $tearsheetID,
            'removed' => $removed,
            'failed' => $failed,
            'message' => $removed . ' candidate(s) removed from tearsheet'
        ]);
    }
}
