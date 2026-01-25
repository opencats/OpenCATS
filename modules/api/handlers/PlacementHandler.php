<?php
/**
 * CATS
 * API Placement Handler
 *
 * Handles CRUD operations for Placements (hired candidates).
 * Bullhorn-compatible API for tracking salary, fees, bill/pay rates.
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

if (file_exists('./lib/Placements.php')) {
    include_once('./lib/Placements.php');
}
include_once(dirname(__FILE__) . '/../formatters/EntityFormatter.php');
include_once(dirname(__FILE__) . '/../traits/ApiHelpers.php');

class PlacementHandler
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
     * Handle placements endpoint
     * Supports: GET (list/single), POST (create), PUT (update), DELETE
     */
    public function handle()
    {
        if (!class_exists('Placements')) {
            $this->sendError('Placements module not installed', 501);
            return;
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $method = $_SERVER['REQUEST_METHOD'];

        $placements = new Placements($this->_siteID);

        // Handle main CRUD operations
        switch ($method) {
            case 'GET':
                $this->handleGet($placements, $id);
                break;
            case 'POST':
                $this->handlePost($placements);
                break;
            case 'PUT':
                $this->handlePut($placements, $id);
                break;
            case 'DELETE':
                $this->handleDelete($placements, $id);
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    /**
     * Handle GET requests - list or single placement
     *
     * @param Placements $placements Placements library instance
     * @param int|null $id Placement ID for single fetch
     */
    private function handleGet($placements, $id)
    {
        if ($id) {
            // Get single placement
            $placement = $placements->get($id);
            if ($placement) {
                $this->sendSuccess($this->formatPlacement($placement));
            } else {
                $this->sendError('Placement not found', 404);
            }
        } else {
            // List placements with filters and pagination
            $pagination = $this->getPaginationParams();

            // Get filter parameters
            $status = isset($_GET['status']) ? $_GET['status'] : null;
            $candidateID = isset($_GET['candidate']) ? intval($_GET['candidate']) : null;
            $companyID = isset($_GET['clientCorporation']) ? intval($_GET['clientCorporation']) : null;

            // Validate status if provided
            if ($status !== null && !Placements::isValidStatus($status)) {
                $validStatuses = implode(', ', Placements::getStatuses());
                $this->sendError("Invalid status. Valid values: {$validStatuses}", 400);
                return;
            }

            // Get total count for pagination
            $total = $placements->getCount($status, $candidateID, $companyID);

            // Get placements
            $list = $placements->getAll(
                $pagination['limit'],
                $pagination['offset'],
                $status,
                $candidateID,
                $companyID
            );

            // Format for Bullhorn-compatible response
            $formatted = [];
            foreach ($list as $placement) {
                $formatted[] = $this->formatPlacementListItem($placement);
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
     * Handle POST requests - create new placement
     *
     * @param Placements $placements Placements library instance
     */
    private function handlePost($placements)
    {
        $input = $this->getRequestBody();

        // Validate required fields
        $requiredFields = ['candidateID', 'jobOrderID', 'clientCorporationID', 'startDate'];
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (empty($input[$field])) {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            $this->sendError('Missing required fields: ' . implode(', ', $missingFields), 400);
            return;
        }

        // Validate status if provided
        if (isset($input['status']) && !Placements::isValidStatus($input['status'])) {
            $validStatuses = implode(', ', Placements::getStatuses());
            $this->sendError("Invalid status. Valid values: {$validStatuses}", 400);
            return;
        }

        // Validate date format
        if (!$this->isValidDate($input['startDate'])) {
            $this->sendError('Invalid startDate format. Use YYYY-MM-DD', 400);
            return;
        }

        if (isset($input['endDate']) && $input['endDate'] && !$this->isValidDate($input['endDate'])) {
            $this->sendError('Invalid endDate format. Use YYYY-MM-DD', 400);
            return;
        }

        // Build optional data array
        $optionalData = [];

        if (isset($input['salary'])) {
            $optionalData['salary'] = $input['salary'];
        }
        if (isset($input['salaryType'])) {
            $optionalData['salaryType'] = $input['salaryType'];
        }
        if (isset($input['fee'])) {
            $optionalData['fee'] = $input['fee'];
        }
        if (isset($input['feeType'])) {
            $optionalData['feeType'] = $input['feeType'];
        }
        if (isset($input['billRate'])) {
            $optionalData['billRate'] = $input['billRate'];
        }
        if (isset($input['payRate'])) {
            $optionalData['payRate'] = $input['payRate'];
        }
        if (isset($input['endDate'])) {
            $optionalData['endDate'] = $input['endDate'];
        }
        if (isset($input['clientContact'])) {
            $optionalData['contactID'] = intval($input['clientContact']);
        }
        if (isset($input['notes'])) {
            $optionalData['notes'] = $input['notes'];
        }
        if (isset($input['status'])) {
            $optionalData['status'] = $input['status'];
        }
        if (isset($input['owner'])) {
            $optionalData['ownerID'] = intval($input['owner']);
        }
        if (isset($input['referralFee'])) {
            $optionalData['referralFee'] = $input['referralFee'];
        }

        // Create placement
        $placementID = $placements->add(
            intval($input['candidateID']),
            intval($input['jobOrderID']),
            intval($input['clientCorporationID']),
            $input['startDate'],
            $this->_userID,
            $optionalData
        );

        if ($placementID === -1) {
            $this->sendError('Failed to create placement. A placement may already exist for this candidate and job order.', 400);
            return;
        }

        // Get and return the created placement
        $newPlacement = $placements->get($placementID);
        $this->sendSuccess($this->formatPlacement($newPlacement), 201);
    }

    /**
     * Handle PUT requests - update existing placement
     *
     * @param Placements $placements Placements library instance
     * @param int|null $id Placement ID
     */
    private function handlePut($placements, $id)
    {
        if (!$id) {
            $this->sendError('Placement ID required for update', 400);
            return;
        }

        $existing = $placements->get($id);
        if (!$existing) {
            $this->sendError('Placement not found', 404);
            return;
        }

        $input = $this->getRequestBody();

        // Validate status if provided
        if (isset($input['status']) && !Placements::isValidStatus($input['status'])) {
            $validStatuses = implode(', ', Placements::getStatuses());
            $this->sendError("Invalid status. Valid values: {$validStatuses}", 400);
            return;
        }

        // Validate date formats if provided
        if (isset($input['startDate']) && $input['startDate'] && !$this->isValidDate($input['startDate'])) {
            $this->sendError('Invalid startDate format. Use YYYY-MM-DD', 400);
            return;
        }

        if (isset($input['endDate']) && $input['endDate'] && !$this->isValidDate($input['endDate'])) {
            $this->sendError('Invalid endDate format. Use YYYY-MM-DD', 400);
            return;
        }

        // Build update data array
        $updateData = [];

        if (isset($input['salary'])) {
            $updateData['salary'] = $input['salary'];
        }
        if (isset($input['salaryType'])) {
            $updateData['salaryType'] = $input['salaryType'];
        }
        if (isset($input['fee'])) {
            $updateData['fee'] = $input['fee'];
        }
        if (isset($input['feeType'])) {
            $updateData['feeType'] = $input['feeType'];
        }
        if (isset($input['billRate'])) {
            $updateData['billRate'] = $input['billRate'];
        }
        if (isset($input['payRate'])) {
            $updateData['payRate'] = $input['payRate'];
        }
        if (isset($input['startDate'])) {
            $updateData['startDate'] = $input['startDate'];
        }
        if (array_key_exists('endDate', $input)) {
            $updateData['endDate'] = $input['endDate'];
        }
        if (isset($input['clientContact'])) {
            $updateData['contactID'] = intval($input['clientContact']);
        }
        if (isset($input['notes'])) {
            $updateData['notes'] = $input['notes'];
        }
        if (isset($input['status'])) {
            $updateData['status'] = $input['status'];
        }
        if (isset($input['owner'])) {
            $updateData['ownerID'] = intval($input['owner']);
        }
        if (array_key_exists('referralFee', $input)) {
            $updateData['referralFee'] = $input['referralFee'];
        }

        if (empty($updateData)) {
            $this->sendError('No valid fields provided for update', 400);
            return;
        }

        $success = $placements->update($id, $updateData);

        if (!$success) {
            $this->sendError('Failed to update placement', 500);
            return;
        }

        $updated = $placements->get($id);
        $this->sendSuccess($this->formatPlacement($updated));
    }

    /**
     * Handle DELETE requests - delete placement
     *
     * @param Placements $placements Placements library instance
     * @param int|null $id Placement ID
     */
    private function handleDelete($placements, $id)
    {
        if (!$id) {
            $this->sendError('Placement ID required for delete', 400);
            return;
        }

        $existing = $placements->get($id);
        if (!$existing) {
            $this->sendError('Placement not found', 404);
            return;
        }

        $success = $placements->delete($id);

        if (!$success) {
            $this->sendError('Failed to delete placement', 500);
            return;
        }

        $this->sendSuccess([
            'message' => 'Placement deleted successfully',
            'id' => $id
        ]);
    }

    /**
     * Format placement for full API response (Bullhorn-compatible)
     *
     * @param array $placement Placement data from database
     * @return array Formatted placement
     */
    private function formatPlacement($placement)
    {
        // Format candidate nested object
        $candidate = null;
        if (!empty($placement['candidateID'])) {
            $candidate = [
                'id' => intval($placement['candidateID']),
                'firstName' => $placement['candidateFirstName'] ?? '',
                'lastName' => $placement['candidateLastName'] ?? '',
                'email' => $placement['candidateEmail'] ?? ''
            ];
        }

        // Format job order nested object
        $jobOrder = null;
        if (!empty($placement['jobOrderID'])) {
            $jobOrder = [
                'id' => intval($placement['jobOrderID']),
                'title' => $placement['jobOrderTitle'] ?? ''
            ];
        }

        // Format client corporation nested object
        $clientCorporation = null;
        if (!empty($placement['companyID'])) {
            $clientCorporation = [
                'id' => intval($placement['companyID']),
                'name' => $placement['companyName'] ?? ''
            ];
        }

        // Format client contact nested object (nullable)
        $clientContact = null;
        if (!empty($placement['contactID'])) {
            $clientContact = [
                'id' => intval($placement['contactID']),
                'firstName' => $placement['contactFirstName'] ?? '',
                'lastName' => $placement['contactLastName'] ?? ''
            ];
        }

        // Format owner nested object
        $owner = null;
        if (!empty($placement['ownerID'])) {
            $owner = [
                'id' => intval($placement['ownerID']),
                'firstName' => $placement['ownerFirstName'] ?? '',
                'lastName' => $placement['ownerLastName'] ?? ''
            ];
        }

        return [
            'id' => intval($placement['placementID']),
            'candidate' => $candidate,
            'jobOrder' => $jobOrder,
            'clientCorporation' => $clientCorporation,
            'clientContact' => $clientContact,
            'status' => $placement['status'] ?? '',
            'startDate' => $placement['startDate'] ?? null,
            'endDate' => $placement['endDate'] ?? null,
            'salary' => $placement['salary'] !== null ? floatval($placement['salary']) : null,
            'salaryType' => $placement['salaryType'] ?? 'Yearly',
            'fee' => $placement['fee'] !== null ? floatval($placement['fee']) : null,
            'feeType' => $placement['feeType'] ?? 'Percentage',
            'billRate' => $placement['billRate'] !== null ? floatval($placement['billRate']) : null,
            'payRate' => $placement['payRate'] !== null ? floatval($placement['payRate']) : null,
            'referralFee' => $placement['referralFee'] !== null ? floatval($placement['referralFee']) : null,
            'notes' => $placement['notes'] ?? '',
            'owner' => $owner,
            'dateAdded' => $placement['dateCreated'] ?? '',
            'dateLastModified' => $placement['dateModified'] ?? ''
        ];
    }

    /**
     * Format placement for list response (lighter version)
     *
     * @param array $placement Placement data from database
     * @return array Formatted placement
     */
    private function formatPlacementListItem($placement)
    {
        // Format candidate nested object
        $candidate = null;
        if (!empty($placement['candidateID'])) {
            $candidate = [
                'id' => intval($placement['candidateID']),
                'firstName' => $placement['candidateFirstName'] ?? '',
                'lastName' => $placement['candidateLastName'] ?? ''
            ];
        }

        // Format job order nested object
        $jobOrder = null;
        if (!empty($placement['jobOrderID'])) {
            $jobOrder = [
                'id' => intval($placement['jobOrderID']),
                'title' => $placement['jobOrderTitle'] ?? ''
            ];
        }

        // Format client corporation nested object
        $clientCorporation = null;
        if (!empty($placement['companyID'])) {
            $clientCorporation = [
                'id' => intval($placement['companyID']),
                'name' => $placement['companyName'] ?? ''
            ];
        }

        // Format owner nested object
        $owner = null;
        if (!empty($placement['ownerID'])) {
            $owner = [
                'id' => intval($placement['ownerID']),
                'firstName' => $placement['ownerFirstName'] ?? '',
                'lastName' => $placement['ownerLastName'] ?? ''
            ];
        }

        return [
            'id' => intval($placement['placementID']),
            'candidate' => $candidate,
            'jobOrder' => $jobOrder,
            'clientCorporation' => $clientCorporation,
            'status' => $placement['status'] ?? '',
            'startDate' => $placement['startDate'] ?? null,
            'endDate' => $placement['endDate'] ?? null,
            'salary' => $placement['salary'] !== null ? floatval($placement['salary']) : null,
            'salaryType' => $placement['salaryType'] ?? 'Yearly',
            'fee' => $placement['fee'] !== null ? floatval($placement['fee']) : null,
            'feeType' => $placement['feeType'] ?? 'Percentage',
            'billRate' => $placement['billRate'] !== null ? floatval($placement['billRate']) : null,
            'payRate' => $placement['payRate'] !== null ? floatval($placement['payRate']) : null,
            'owner' => $owner,
            'dateAdded' => $placement['dateCreated'] ?? ''
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
}
