<?php
/**
 * CATS
 * API Note Handler
 *
 * Handles CRUD operations for Notes via REST API.
 * Provides Bullhorn-compatible note management.
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

include_once('./lib/Notes.php');
include_once(dirname(__FILE__) . '/../formatters/EntityFormatter.php');
include_once(dirname(__FILE__) . '/../traits/ApiHelpers.php');

class NoteHandler
{
    use ApiHelpers;

    private $_siteID;
    private $_userID;
    protected $_requestLogger;

    /**
     * Constructor
     *
     * @param int $siteID Site ID
     * @param int $userID User ID
     * @param object $requestLogger Optional request logger
     */
    public function __construct($siteID, $userID, $requestLogger = null)
    {
        $this->_siteID = $siteID;
        $this->_userID = $userID;
        $this->_requestLogger = $requestLogger;
    }

    /**
     * Handle notes endpoint
     * Supports: GET (list/single), POST (create), PUT (update), DELETE
     */
    public function handle()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $method = $_SERVER['REQUEST_METHOD'];

        $notes = new Notes($this->_siteID);

        switch ($method) {
            case 'GET':
                $this->handleGet($notes, $id);
                break;
            case 'POST':
                $this->handlePost($notes);
                break;
            case 'PUT':
                $this->handlePut($notes, $id);
                break;
            case 'DELETE':
                $this->handleDelete($notes, $id);
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    /**
     * Handle GET requests
     *
     * @param Notes $notes Notes instance
     * @param int|null $id Note ID for single record
     */
    private function handleGet($notes, $id)
    {
        if ($id) {
            // Get single note
            $note = $notes->get($id);
            if ($note && !empty($note['noteID'])) {
                $this->sendSuccess(EntityFormatter::formatNote($note));
            } else {
                $this->sendError('Note not found', 404);
            }
        } else {
            // Get list with optional filters
            $this->handleList($notes);
        }
    }

    /**
     * Handle list request with filters
     *
     * @param Notes $notes Notes instance
     */
    private function handleList($notes)
    {
        // Check for entity filter
        $personType = isset($_GET['personType']) ? trim($_GET['personType']) : '';
        $personID = isset($_GET['personID']) ? intval($_GET['personID']) : 0;
        $userID = isset($_GET['userID']) ? intval($_GET['userID']) : 0;
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';

        $pagination = $this->getPaginationParams();

        // Determine which retrieval method to use
        if (!empty($personType) && $personID > 0) {
            // Filter by entity
            $validTypes = ['candidate', 'contact', 'joborder', 'company'];
            if (!in_array($personType, $validTypes)) {
                $this->sendError('Invalid personType. Must be: candidate, contact, joborder, or company', 400);
                return;
            }
            $allNotes = $notes->getByPerson($personType, $personID);
        } elseif ($userID > 0) {
            // Filter by user
            $allNotes = $notes->getByUser($userID);
        } elseif (!empty($search)) {
            // Search notes
            $allNotes = $notes->search($search, 1000, 0); // Get all matching, paginate later
        } else {
            // Get all notes
            $allNotes = $notes->getAll();
        }

        // Format notes
        $formatted = [];
        if (is_array($allNotes)) {
            foreach ($allNotes as $note) {
                $formatted[] = EntityFormatter::formatNote($note);
            }
        }

        $this->sendPaginatedResponse($formatted, $pagination['page'], $pagination['limit']);
    }

    /**
     * Handle POST request (create note)
     *
     * @param Notes $notes Notes instance
     */
    private function handlePost($notes)
    {
        $input = $this->getRequestBody();

        // Validate required fields
        if (empty($input['action'])) {
            $this->sendError('Missing required field: action', 400);
            return;
        }

        if (empty($input['personType'])) {
            $this->sendError('Missing required field: personType', 400);
            return;
        }

        if (empty($input['personID'])) {
            $this->sendError('Missing required field: personID', 400);
            return;
        }

        // Validate personType
        $validTypes = ['candidate', 'contact', 'joborder', 'company'];
        if (!in_array($input['personType'], $validTypes)) {
            $this->sendError('Invalid personType. Must be: candidate, contact, joborder, or company', 400);
            return;
        }

        // Extract fields
        $action = $input['action'];
        $comments = isset($input['comments']) ? $input['comments'] : '';
        $personType = $input['personType'];
        $personID = intval($input['personID']);
        $jobOrderID = isset($input['jobOrderID']) ? intval($input['jobOrderID']) : null;
        $activityType = isset($input['activityType']) ? intval($input['activityType']) : Notes::DEFAULT_ACTIVITY_TYPE;

        // Create the note
        $noteID = $notes->add($action, $comments, $personType, $personID, $this->_userID, $jobOrderID, $activityType);

        if ($noteID <= 0) {
            $this->sendError('Failed to create note', 500);
            return;
        }

        // Return the created note
        $newNote = $notes->get($noteID);
        $this->sendSuccess(EntityFormatter::formatNote($newNote), 201);
    }

    /**
     * Handle PUT request (update note)
     *
     * @param Notes $notes Notes instance
     * @param int|null $id Note ID
     */
    private function handlePut($notes, $id)
    {
        if (!$id) {
            $this->sendError('Note ID required for update', 400);
            return;
        }

        // Check if note exists
        $existing = $notes->get($id);
        if (!$existing || empty($existing['noteID'])) {
            $this->sendError('Note not found', 404);
            return;
        }

        $input = $this->getRequestBody();

        // Build update data array
        $updateData = [];

        if (isset($input['action'])) {
            $updateData['action'] = $input['action'];
        }

        if (isset($input['comments'])) {
            $updateData['comments'] = $input['comments'];
        }

        if (isset($input['personType'])) {
            $validTypes = ['candidate', 'contact', 'joborder', 'company'];
            if (!in_array($input['personType'], $validTypes)) {
                $this->sendError('Invalid personType. Must be: candidate, contact, joborder, or company', 400);
                return;
            }
            $updateData['personType'] = $input['personType'];
        }

        if (isset($input['personID'])) {
            $updateData['personID'] = intval($input['personID']);
        }

        if (array_key_exists('jobOrderID', $input)) {
            $updateData['jobOrderID'] = $input['jobOrderID'] !== null ? intval($input['jobOrderID']) : null;
        }

        if (isset($input['activityType'])) {
            $updateData['activityType'] = intval($input['activityType']);
        }

        if (empty($updateData)) {
            $this->sendError('No update fields provided', 400);
            return;
        }

        // Perform update
        $success = $notes->update($id, $updateData);

        if (!$success) {
            $this->sendError('Failed to update note', 500);
            return;
        }

        // Return updated note
        $updatedNote = $notes->get($id);
        $this->sendSuccess(EntityFormatter::formatNote($updatedNote));
    }

    /**
     * Handle DELETE request
     *
     * @param Notes $notes Notes instance
     * @param int|null $id Note ID
     */
    private function handleDelete($notes, $id)
    {
        if (!$id) {
            $this->sendError('Note ID required for delete', 400);
            return;
        }

        // Check if note exists
        $existing = $notes->get($id);
        if (!$existing || empty($existing['noteID'])) {
            $this->sendError('Note not found', 404);
            return;
        }

        // Perform delete
        $success = $notes->delete($id);

        if (!$success) {
            $this->sendError('Failed to delete note', 500);
            return;
        }

        $this->sendSuccess([
            'message' => 'Note deleted successfully',
            'id' => $id
        ]);
    }
}
