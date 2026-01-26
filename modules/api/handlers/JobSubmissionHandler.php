<?php
/**
 * CATS
 * API JobSubmission Handler
 *
 * Handles CRUD operations for JobSubmissions (candidate-to-job pipeline).
 * Provides Bullhorn-compatible API for managing candidate submissions to job orders.
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

if (file_exists('./lib/JobSubmissions.php')) {
    include_once('./lib/JobSubmissions.php');
}
include_once(dirname(__FILE__) . '/../formatters/EntityFormatter.php');
include_once(dirname(__FILE__) . '/../traits/ApiHelpers.php');
include_once(dirname(__FILE__) . '/../traits/WebhookTrigger.php');

class JobSubmissionHandler
{
    use ApiHelpers;
    use WebhookTrigger;

    private $_siteID;
    private $_userID;
    protected $_requestLogger;

    /**
     * Constructor
     *
     * @param int $siteID Site ID for multi-tenant isolation
     * @param int $userID User ID making the request
     * @param object|null $requestLogger Optional request logger
     */
    public function __construct($siteID, $userID, $requestLogger = null)
    {
        $this->_siteID = $siteID;
        $this->_userID = $userID;
        $this->_requestLogger = $requestLogger;
    }

    /**
     * Handle job submissions endpoint
     * Supports: GET (list/single), POST (create), PUT (update), DELETE
     */
    public function handle()
    {
        if (!class_exists('JobSubmissions')) {
            $this->sendError('JobSubmissions module not installed', 501);
            return;
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $method = $_SERVER['REQUEST_METHOD'];

        $jobSubmissions = new JobSubmissions($this->_siteID);

        switch ($method) {
            case 'GET':
                $this->handleGet($jobSubmissions, $id);
                break;
            case 'POST':
                $this->handlePost($jobSubmissions);
                break;
            case 'PUT':
                $this->handlePut($jobSubmissions, $id);
                break;
            case 'DELETE':
                $this->handleDelete($jobSubmissions, $id);
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    /**
     * Handle GET requests - list or single submission
     *
     * @param JobSubmissions $jobSubmissions JobSubmissions instance
     * @param int|null $id Submission ID (null for list)
     */
    private function handleGet($jobSubmissions, $id)
    {
        if ($id) {
            /* Get single submission */
            $submission = $jobSubmissions->get($id);
            if ($submission) {
                $this->sendSuccess($this->formatSubmission($submission));
            } else {
                $this->sendError('JobSubmission not found', 404);
            }
        } else {
            /* List submissions with filters */
            $this->handleList($jobSubmissions);
        }
    }

    /**
     * Handle list with filters and pagination
     *
     * @param JobSubmissions $jobSubmissions JobSubmissions instance
     */
    private function handleList($jobSubmissions)
    {
        /* Get filter parameters */
        $status = isset($_GET['status']) ? $_GET['status'] : null;
        $jobOrderID = isset($_GET['jobOrder']) ? intval($_GET['jobOrder']) : null;
        $candidateID = isset($_GET['candidate']) ? intval($_GET['candidate']) : null;

        /* Get pagination parameters */
        $pagination = $this->getPaginationParams();

        /* Get total count for pagination metadata */
        $total = $jobSubmissions->getCount($status, $jobOrderID, $candidateID);

        /* Get submissions */
        $submissions = $jobSubmissions->getAll(
            $pagination['limit'],
            $pagination['offset'],
            $status,
            $jobOrderID,
            $candidateID
        );

        /* Format submissions */
        $formatted = [];
        foreach ($submissions as $submission) {
            $formatted[] = $this->formatSubmission($submission);
        }

        $this->sendSuccess([
            'total' => $total,
            'page' => $pagination['page'],
            'limit' => $pagination['limit'],
            'data' => $formatted
        ]);
    }

    /**
     * Handle POST requests - create new submission
     *
     * @param JobSubmissions $jobSubmissions JobSubmissions instance
     */
    private function handlePost($jobSubmissions)
    {
        $input = $this->getRequestBody();

        /* Validate required fields */
        if (empty($input['candidateID'])) {
            $this->sendError('Missing required field: candidateID', 400);
            return;
        }

        if (empty($input['jobOrderID'])) {
            $this->sendError('Missing required field: jobOrderID', 400);
            return;
        }

        $candidateID = intval($input['candidateID']);
        $jobOrderID = intval($input['jobOrderID']);
        $status = isset($input['status']) ? $input['status'] : JobSubmissions::STATUS_SUBMITTED;
        $source = isset($input['source']) ? $input['source'] : '';

        /* Create submission */
        $submissionID = $jobSubmissions->add(
            $candidateID,
            $jobOrderID,
            $this->_userID,
            $status,
            $source
        );

        if (!$submissionID) {
            /* Check if already exists */
            $existing = $jobSubmissions->getByCandidateAndJob($candidateID, $jobOrderID);
            if ($existing) {
                $this->sendError('Submission already exists for this candidate and job order', 409);
            } else {
                $this->sendError('Failed to create submission', 500);
            }
            return;
        }

        /* Return the created submission */
        $newSubmission = $jobSubmissions->get($submissionID);
        $formattedSubmission = $this->formatSubmission($newSubmission);
        $this->sendSuccess($formattedSubmission, 201);
        $this->triggerWebhook('jobsubmission', 'create', $submissionID, $formattedSubmission);
    }

    /**
     * Handle PUT requests - update submission status
     *
     * @param JobSubmissions $jobSubmissions JobSubmissions instance
     * @param int|null $id Submission ID
     */
    private function handlePut($jobSubmissions, $id)
    {
        if (!$id) {
            $this->sendError('Submission ID required for update', 400);
            return;
        }

        $existing = $jobSubmissions->get($id);
        if (!$existing) {
            $this->sendError('JobSubmission not found', 404);
            return;
        }

        $input = $this->getRequestBody();

        /* Build update data */
        $updateData = [];

        if (isset($input['status'])) {
            $updateData['status'] = $input['status'];
            $updateData['userID'] = $this->_userID;
        }

        if (isset($input['source'])) {
            $updateData['source'] = $input['source'];
        }

        if (isset($input['sendToClient'])) {
            $updateData['sendToClient'] = (bool)$input['sendToClient'];
        }

        if (isset($input['ratingValue'])) {
            $updateData['ratingValue'] = intval($input['ratingValue']);
        }

        if (empty($updateData)) {
            $this->sendError('No valid fields provided for update', 400);
            return;
        }

        /* Perform update */
        if (isset($updateData['status'])) {
            /* Status update uses specialized method */
            $success = $jobSubmissions->updateStatus($id, $updateData['status'], $this->_userID);
            if (!$success) {
                $this->sendError('Failed to update submission status. Invalid status value.', 400);
                return;
            }
            unset($updateData['status']);
            unset($updateData['userID']);
        }

        /* Update other fields if present */
        if (!empty($updateData)) {
            $success = $jobSubmissions->update($id, $updateData);
            if (!$success) {
                $this->sendError('Failed to update submission', 500);
                return;
            }
        }

        /* Return updated submission */
        $updated = $jobSubmissions->get($id);
        $formattedSubmission = $this->formatSubmission($updated);
        $this->sendSuccess($formattedSubmission);
        $this->triggerWebhook('jobsubmission', 'update', $id, $formattedSubmission);
    }

    /**
     * Handle DELETE requests - delete submission
     *
     * @param JobSubmissions $jobSubmissions JobSubmissions instance
     * @param int|null $id Submission ID
     */
    private function handleDelete($jobSubmissions, $id)
    {
        if (!$id) {
            $this->sendError('Submission ID required for delete', 400);
            return;
        }

        $existing = $jobSubmissions->get($id);
        if (!$existing) {
            $this->sendError('JobSubmission not found', 404);
            return;
        }

        $success = $jobSubmissions->delete($id);

        if (!$success) {
            $this->sendError('Failed to delete submission', 500);
            return;
        }

        $this->sendSuccess([
            'message' => 'Submission deleted successfully',
            'id' => $id
        ]);
        $this->triggerWebhook('jobsubmission', 'delete', $id, ['id' => $id]);
    }

    /**
     * Format a submission for Bullhorn-compatible API response
     *
     * @param array $submission Raw submission data from JobSubmissions
     * @return array Formatted submission
     */
    private function formatSubmission($submission)
    {
        return [
            'id' => intval($submission['submissionID'] ?? 0),
            'candidate' => [
                'id' => intval($submission['candidateID'] ?? 0),
                'firstName' => $submission['candidateFirstName'] ?? '',
                'lastName' => $submission['candidateLastName'] ?? '',
                'email' => $submission['candidateEmail'] ?? ''
            ],
            'jobOrder' => [
                'id' => intval($submission['jobOrderID'] ?? 0),
                'title' => $submission['jobTitle'] ?? ''
            ],
            'clientCorporation' => [
                'id' => intval($submission['companyID'] ?? 0),
                'name' => $submission['companyName'] ?? ''
            ],
            'status' => $submission['status'] ?? '',
            'source' => $submission['source'] ?? '',
            'dateSubmitted' => $submission['dateCreated'] ?? '',
            'dateInterview' => $submission['dateInterview'] ?? null,
            'dateOffer' => $submission['dateOffer'] ?? null,
            'dateAdded' => $submission['dateCreated'] ?? '',
            'sendingUser' => [
                'id' => intval($submission['addedBy'] ?? 0),
                'firstName' => $submission['addedByFirstName'] ?? '',
                'lastName' => $submission['addedByLastName'] ?? ''
            ]
        ];
    }
}
