<?php
/**
 * CATS
 * API Task Handler
 *
 * Handles CRUD operations for Tasks (to-do items).
 * Bullhorn-compatible API for tracking tasks with status workflow and priorities.
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

if (file_exists('./lib/Tasks.php')) {
    include_once('./lib/Tasks.php');
}
include_once(dirname(__FILE__) . '/../formatters/EntityFormatter.php');
include_once(dirname(__FILE__) . '/../traits/ApiHelpers.php');
include_once(dirname(__FILE__) . '/../traits/WebhookTrigger.php');

class TaskHandler
{
    use ApiHelpers;
    use WebhookTrigger;

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
     * Handle tasks endpoint
     * Supports: GET (list/single), POST (create), PUT (update), DELETE
     */
    public function handle()
    {
        if (!class_exists('Tasks')) {
            $this->sendError('Tasks module not installed', 501);
            return;
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $method = $_SERVER['REQUEST_METHOD'];

        $tasks = new Tasks($this->_siteID);

        // Handle main CRUD operations
        switch ($method) {
            case 'GET':
                $this->handleGet($tasks, $id);
                break;
            case 'POST':
                $this->handlePost($tasks);
                break;
            case 'PUT':
                $this->handlePut($tasks, $id);
                break;
            case 'DELETE':
                $this->handleDelete($tasks, $id);
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    /**
     * Handle GET requests - list or single task
     *
     * @param Tasks $tasks Tasks library instance
     * @param int|null $id Task ID for single fetch
     */
    private function handleGet($tasks, $id)
    {
        if ($id) {
            // Get single task
            $task = $tasks->get($id);
            if ($task) {
                $this->sendSuccess($this->formatTask($task));
            } else {
                $this->sendError('Task not found', 404);
            }
        } else {
            // List tasks with filters and pagination
            $pagination = $this->getPaginationParams();

            // Get filter parameters
            $ownerID = isset($_GET['owner']) ? intval($_GET['owner']) : null;
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            $priority = isset($_GET['priority']) ? $_GET['priority'] : null;

            // Validate status if provided
            if ($status !== null && !Tasks::isValidStatus($status)) {
                $validStatuses = implode(', ', Tasks::getStatuses());
                $this->sendError("Invalid status. Valid values: {$validStatuses}", 400);
                return;
            }

            // Validate priority if provided
            if ($priority !== null && !Tasks::isValidPriority($priority)) {
                $validPriorities = implode(', ', Tasks::getPriorities());
                $this->sendError("Invalid priority. Valid values: {$validPriorities}", 400);
                return;
            }

            // Get total count for pagination
            $total = $tasks->getCount($ownerID, $status);

            // Get tasks
            $list = $tasks->getAll(
                $pagination['limit'],
                $pagination['offset'],
                $ownerID,
                $status
            );

            // Filter by priority if specified (done in PHP since getAll doesn't support it)
            if ($priority !== null) {
                $list = array_filter($list, function($task) use ($priority) {
                    return $task['priority'] === $priority;
                });
                $list = array_values($list); // Re-index array
                $total = count($list);
            }

            // Format for Bullhorn-compatible response
            $formatted = [];
            foreach ($list as $task) {
                $formatted[] = $this->formatTaskListItem($task);
            }

            $this->sendSuccess([
                'total' => $total,
                'page' => $pagination['page'],
                'limit' => $pagination['limit'],
                'data' => $formatted
            ]);
        }
    }

    /**
     * Handle POST requests - create new task
     *
     * @param Tasks $tasks Tasks library instance
     */
    private function handlePost($tasks)
    {
        $input = $this->getRequestBody();

        // Validate required fields
        if (empty($input['subject'])) {
            $this->sendError('Missing required field: subject', 400);
            return;
        }

        // Validate status if provided
        if (isset($input['status']) && !Tasks::isValidStatus($input['status'])) {
            $validStatuses = implode(', ', Tasks::getStatuses());
            $this->sendError("Invalid status. Valid values: {$validStatuses}", 400);
            return;
        }

        // Validate priority if provided
        if (isset($input['priority']) && !Tasks::isValidPriority($input['priority'])) {
            $validPriorities = implode(', ', Tasks::getPriorities());
            $this->sendError("Invalid priority. Valid values: {$validPriorities}", 400);
            return;
        }

        // Validate date format if provided
        if (isset($input['dueDate']) && $input['dueDate'] && !$this->isValidDate($input['dueDate'])) {
            $this->sendError('Invalid dueDate format. Use YYYY-MM-DD', 400);
            return;
        }

        // Validate personType if provided
        if (isset($input['personType']) && !$this->isValidPersonType($input['personType'])) {
            $this->sendError('Invalid personType. Valid values: candidate, contact, joborder, company', 400);
            return;
        }

        // Build optional data array
        $optionalData = [];

        if (isset($input['description'])) {
            $optionalData['description'] = $input['description'];
        }
        if (isset($input['status'])) {
            $optionalData['status'] = $input['status'];
        }
        if (isset($input['priority'])) {
            $optionalData['priority'] = $input['priority'];
        }
        if (isset($input['dueDate'])) {
            $optionalData['dueDate'] = $input['dueDate'];
        }
        if (isset($input['personType'])) {
            $optionalData['personType'] = $input['personType'];
        }
        if (isset($input['personId'])) {
            $optionalData['personID'] = intval($input['personId']);
        }

        // Determine owner - use provided owner or current user
        $ownerID = isset($input['owner']) ? intval($input['owner']) : $this->_userID;

        // Create task
        $taskID = $tasks->add(
            $input['subject'],
            $ownerID,
            $optionalData
        );

        if ($taskID === -1) {
            $this->sendError('Failed to create task', 500);
            return;
        }

        // Get and return the created task
        $newTask = $tasks->get($taskID);
        $formattedTask = $this->formatTask($newTask);
        $this->sendSuccess($formattedTask, 201);
        $this->triggerWebhook('task', 'create', $taskID, $formattedTask);
    }

    /**
     * Handle PUT requests - update existing task
     *
     * @param Tasks $tasks Tasks library instance
     * @param int|null $id Task ID
     */
    private function handlePut($tasks, $id)
    {
        if (!$id) {
            $this->sendError('Task ID required for update', 400);
            return;
        }

        $existing = $tasks->get($id);
        if (!$existing) {
            $this->sendError('Task not found', 404);
            return;
        }

        $input = $this->getRequestBody();

        // Validate status if provided
        if (isset($input['status']) && !Tasks::isValidStatus($input['status'])) {
            $validStatuses = implode(', ', Tasks::getStatuses());
            $this->sendError("Invalid status. Valid values: {$validStatuses}", 400);
            return;
        }

        // Validate priority if provided
        if (isset($input['priority']) && !Tasks::isValidPriority($input['priority'])) {
            $validPriorities = implode(', ', Tasks::getPriorities());
            $this->sendError("Invalid priority. Valid values: {$validPriorities}", 400);
            return;
        }

        // Validate date format if provided
        if (isset($input['dueDate']) && $input['dueDate'] && !$this->isValidDate($input['dueDate'])) {
            $this->sendError('Invalid dueDate format. Use YYYY-MM-DD', 400);
            return;
        }

        // Validate personType if provided
        if (isset($input['personType']) && $input['personType'] && !$this->isValidPersonType($input['personType'])) {
            $this->sendError('Invalid personType. Valid values: candidate, contact, joborder, company', 400);
            return;
        }

        // Check if this is a "complete" action
        if (isset($input['status']) && $input['status'] === Tasks::STATUS_COMPLETED) {
            $success = $tasks->complete($id);
            if (!$success) {
                $this->sendError('Failed to complete task', 500);
                return;
            }
            $updated = $tasks->get($id);
            $formattedTask = $this->formatTask($updated);
            $this->sendSuccess($formattedTask);
            $this->triggerWebhook('task', 'update', $id, $formattedTask);
            return;
        }

        // Build update data array
        $updateData = [];

        if (isset($input['subject'])) {
            $updateData['subject'] = $input['subject'];
        }
        if (isset($input['description'])) {
            $updateData['description'] = $input['description'];
        }
        if (isset($input['status'])) {
            $updateData['status'] = $input['status'];
        }
        if (isset($input['priority'])) {
            $updateData['priority'] = $input['priority'];
        }
        if (array_key_exists('dueDate', $input)) {
            $updateData['dueDate'] = $input['dueDate'];
        }
        if (array_key_exists('personType', $input)) {
            $updateData['personType'] = $input['personType'];
        }
        if (array_key_exists('personId', $input)) {
            $updateData['personID'] = $input['personId'] ? intval($input['personId']) : null;
        }
        if (isset($input['owner'])) {
            $updateData['ownerID'] = intval($input['owner']);
        }

        if (empty($updateData)) {
            $this->sendError('No valid fields provided for update', 400);
            return;
        }

        $success = $tasks->update($id, $updateData);

        if (!$success) {
            $this->sendError('Failed to update task', 500);
            return;
        }

        $updated = $tasks->get($id);
        $formattedTask = $this->formatTask($updated);
        $this->sendSuccess($formattedTask);
        $this->triggerWebhook('task', 'update', $id, $formattedTask);
    }

    /**
     * Handle DELETE requests - delete task
     *
     * @param Tasks $tasks Tasks library instance
     * @param int|null $id Task ID
     */
    private function handleDelete($tasks, $id)
    {
        if (!$id) {
            $this->sendError('Task ID required for delete', 400);
            return;
        }

        $existing = $tasks->get($id);
        if (!$existing) {
            $this->sendError('Task not found', 404);
            return;
        }

        $success = $tasks->delete($id);

        if (!$success) {
            $this->sendError('Failed to delete task', 500);
            return;
        }

        $this->sendSuccess([
            'message' => 'Task deleted successfully',
            'id' => $id
        ]);
        $this->triggerWebhook('task', 'delete', $id, ['id' => $id]);
    }

    /**
     * Format task for full API response (Bullhorn-compatible)
     *
     * @param array $task Task data from database
     * @return array Formatted task
     */
    private function formatTask($task)
    {
        // Format owner nested object
        $owner = null;
        if (!empty($task['ownerID'])) {
            $owner = [
                'id' => intval($task['ownerID']),
                'firstName' => $task['ownerFirstName'] ?? '',
                'lastName' => $task['ownerLastName'] ?? ''
            ];
        }

        return [
            'id' => intval($task['taskID']),
            'subject' => $task['subject'] ?? '',
            'description' => $task['description'] ?? '',
            'status' => $task['status'] ?? Tasks::STATUS_NOT_STARTED,
            'priority' => $task['priority'] ?? Tasks::PRIORITY_NORMAL,
            'dueDate' => $task['dueDate'] ?? null,
            'personType' => $task['personType'] ?? null,
            'personId' => $task['personID'] !== null ? intval($task['personID']) : null,
            'owner' => $owner,
            'dateAdded' => $task['dateCreated'] ?? '',
            'dateLastModified' => $task['dateModified'] ?? '',
            'dateCompleted' => $task['dateCompleted'] ?? null
        ];
    }

    /**
     * Format task for list response (lighter version)
     *
     * @param array $task Task data from database
     * @return array Formatted task
     */
    private function formatTaskListItem($task)
    {
        // Format owner nested object
        $owner = null;
        if (!empty($task['ownerID'])) {
            $owner = [
                'id' => intval($task['ownerID']),
                'firstName' => $task['ownerFirstName'] ?? '',
                'lastName' => $task['ownerLastName'] ?? ''
            ];
        }

        return [
            'id' => intval($task['taskID']),
            'subject' => $task['subject'] ?? '',
            'status' => $task['status'] ?? Tasks::STATUS_NOT_STARTED,
            'priority' => $task['priority'] ?? Tasks::PRIORITY_NORMAL,
            'dueDate' => $task['dueDate'] ?? null,
            'personType' => $task['personType'] ?? null,
            'personId' => $task['personID'] !== null ? intval($task['personID']) : null,
            'owner' => $owner,
            'dateAdded' => $task['dateCreated'] ?? '',
            'dateCompleted' => $task['dateCompleted'] ?? null
        ];
    }

    /**
     * Validate date format (YYYY-MM-DD)
     *
     * @param string $date Date string to validate
     * @return bool True if valid date format
     */
    private function isValidDate($date)
    {
        if (empty($date)) {
            return false;
        }

        $d = DateTime::createFromFormat('Y-m-d', $date);
        return $d && $d->format('Y-m-d') === $date;
    }

    /**
     * Validate person type
     *
     * @param string $personType Person type to validate
     * @return bool True if valid person type
     */
    private function isValidPersonType($personType)
    {
        $validTypes = ['candidate', 'contact', 'joborder', 'company'];
        return in_array(strtolower($personType), $validTypes);
    }
}
