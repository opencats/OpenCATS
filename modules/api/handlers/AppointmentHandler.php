<?php
/**
 * CATS
 * API Appointment Handler
 *
 * Handles CRUD operations for Appointments (calendar events).
 * Provides Bullhorn-compatible API for managing appointments with
 * support for associating appointments with candidates, contacts, and job orders.
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

if (file_exists('./lib/Appointments.php')) {
    include_once('./lib/Appointments.php');
}
include_once(dirname(__FILE__) . '/../formatters/EntityFormatter.php');
include_once(dirname(__FILE__) . '/../traits/ApiHelpers.php');

class AppointmentHandler
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
     * @param object|null $requestLogger Optional request logger
     */
    public function __construct($siteID, $userID, $requestLogger = null)
    {
        $this->_siteID = $siteID;
        $this->_userID = $userID;
        $this->_requestLogger = $requestLogger;
    }

    /**
     * Handle appointments endpoint
     * Supports: GET (list/single), POST (create), PUT (update), DELETE
     */
    public function handle()
    {
        if (!class_exists('Appointments')) {
            $this->sendError('Appointments module not installed', 501);
            return;
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $method = $_SERVER['REQUEST_METHOD'];

        $appointments = new Appointments($this->_siteID);

        switch ($method) {
            case 'GET':
                $this->handleGet($appointments, $id);
                break;
            case 'POST':
                $this->handlePost($appointments);
                break;
            case 'PUT':
                $this->handlePut($appointments, $id);
                break;
            case 'DELETE':
                $this->handleDelete($appointments, $id);
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    /**
     * Handle GET requests - list or single appointment
     *
     * @param Appointments $appointments Appointments instance
     * @param int|null $id Appointment ID (null for list)
     */
    private function handleGet($appointments, $id)
    {
        if ($id) {
            /* Get single appointment */
            $appointment = $appointments->get($id);
            if ($appointment) {
                $this->sendSuccess($this->formatAppointment($appointment));
            } else {
                $this->sendError('Appointment not found', 404);
            }
        } else {
            /* List appointments with filters */
            $this->handleList($appointments);
        }
    }

    /**
     * Handle list with filters and pagination
     *
     * @param Appointments $appointments Appointments instance
     */
    private function handleList($appointments)
    {
        /* Get filter parameters */
        $ownerID = isset($_GET['owner']) ? intval($_GET['owner']) : null;
        $startDate = isset($_GET['startDate']) ? $_GET['startDate'] : null;
        $endDate = isset($_GET['endDate']) ? $_GET['endDate'] : null;
        $personType = isset($_GET['personType']) ? $_GET['personType'] : null;
        $personID = isset($_GET['personId']) ? intval($_GET['personId']) : null;

        /* Get pagination parameters */
        $pagination = $this->getPaginationParams();

        /* Determine which query method to use based on filters */
        if ($personType !== null && $personID !== null) {
            /* Get appointments for a specific person */
            $allAppointments = $appointments->getByPerson(
                $personType,
                $personID,
                $pagination['limit'],
                $pagination['offset']
            );
            $total = count($allAppointments);
        } elseif ($ownerID !== null && ($startDate !== null || $endDate !== null)) {
            /* Get appointments for owner in date range */
            $allAppointments = $appointments->getByOwner($ownerID, $startDate, $endDate);
            $total = count($allAppointments);
            /* Apply pagination manually for this query type */
            $allAppointments = array_slice($allAppointments, $pagination['offset'], $pagination['limit']);
        } else {
            /* Get total count for pagination metadata */
            $total = $appointments->getCount($ownerID);

            /* Get appointments with pagination */
            $allAppointments = $appointments->getAll(
                $pagination['limit'],
                $pagination['offset'],
                $ownerID
            );
        }

        /* Format appointments */
        $formatted = [];
        foreach ($allAppointments as $appointment) {
            $formatted[] = $this->formatAppointment($appointment);
        }

        $this->sendSuccess([
            'total' => $total,
            'page' => $pagination['page'],
            'limit' => $pagination['limit'],
            'data' => $formatted
        ]);
    }

    /**
     * Handle POST requests - create new appointment
     *
     * @param Appointments $appointments Appointments instance
     */
    private function handlePost($appointments)
    {
        $input = $this->getRequestBody();

        /* Validate required fields */
        if (empty($input['title'])) {
            $this->sendError('Missing required field: title', 400);
            return;
        }

        if (empty($input['startDate'])) {
            $this->sendError('Missing required field: startDate', 400);
            return;
        }

        if (empty($input['endDate'])) {
            $this->sendError('Missing required field: endDate', 400);
            return;
        }

        $title = $input['title'];
        $startDate = $input['startDate'];
        $endDate = $input['endDate'];

        /* Build optional data array */
        $data = [];

        if (isset($input['description'])) {
            $data['description'] = $input['description'];
        }

        if (isset($input['type'])) {
            $data['type'] = $input['type'];
        }

        if (isset($input['allDay'])) {
            $data['allDay'] = (bool)$input['allDay'];
        }

        if (isset($input['location'])) {
            $data['location'] = $input['location'];
        }

        if (isset($input['personType'])) {
            $data['personType'] = $input['personType'];
        }

        if (isset($input['personId'])) {
            $data['personID'] = intval($input['personId']);
        }

        if (isset($input['jobOrderId'])) {
            $data['jobOrderID'] = intval($input['jobOrderId']);
        }

        /* Create appointment with current user as owner */
        $appointmentID = $appointments->add(
            $title,
            $startDate,
            $endDate,
            $this->_userID,
            $data
        );

        if (!$appointmentID) {
            $this->sendError('Failed to create appointment', 500);
            return;
        }

        /* Return the created appointment */
        $newAppointment = $appointments->get($appointmentID);
        $this->sendSuccess($this->formatAppointment($newAppointment), 201);
    }

    /**
     * Handle PUT requests - update appointment
     *
     * @param Appointments $appointments Appointments instance
     * @param int|null $id Appointment ID
     */
    private function handlePut($appointments, $id)
    {
        if (!$id) {
            $this->sendError('Appointment ID required for update', 400);
            return;
        }

        $existing = $appointments->get($id);
        if (!$existing) {
            $this->sendError('Appointment not found', 404);
            return;
        }

        $input = $this->getRequestBody();

        /* Build update data */
        $updateData = [];

        if (isset($input['title'])) {
            $updateData['title'] = $input['title'];
        }

        if (isset($input['description'])) {
            $updateData['description'] = $input['description'];
        }

        if (isset($input['type'])) {
            $updateData['type'] = $input['type'];
        }

        if (isset($input['startDate'])) {
            $updateData['startDate'] = $input['startDate'];
        }

        if (isset($input['endDate'])) {
            $updateData['endDate'] = $input['endDate'];
        }

        if (isset($input['allDay'])) {
            $updateData['allDay'] = (bool)$input['allDay'];
        }

        if (isset($input['location'])) {
            $updateData['location'] = $input['location'];
        }

        if (array_key_exists('personType', $input)) {
            $updateData['personType'] = $input['personType'];
        }

        if (array_key_exists('personId', $input)) {
            $updateData['personID'] = $input['personId'] !== null ? intval($input['personId']) : null;
        }

        if (array_key_exists('jobOrderId', $input)) {
            $updateData['jobOrderID'] = $input['jobOrderId'] !== null ? intval($input['jobOrderId']) : null;
        }

        if (isset($input['status'])) {
            $updateData['status'] = $input['status'];
        }

        if (empty($updateData)) {
            $this->sendError('No valid fields provided for update', 400);
            return;
        }

        /* Perform update */
        $success = $appointments->update($id, $updateData);

        if (!$success) {
            $this->sendError('Failed to update appointment', 500);
            return;
        }

        /* Return updated appointment */
        $updated = $appointments->get($id);
        $this->sendSuccess($this->formatAppointment($updated));
    }

    /**
     * Handle DELETE requests - delete appointment
     *
     * @param Appointments $appointments Appointments instance
     * @param int|null $id Appointment ID
     */
    private function handleDelete($appointments, $id)
    {
        if (!$id) {
            $this->sendError('Appointment ID required for delete', 400);
            return;
        }

        $existing = $appointments->get($id);
        if (!$existing) {
            $this->sendError('Appointment not found', 404);
            return;
        }

        $success = $appointments->delete($id);

        if (!$success) {
            $this->sendError('Failed to delete appointment', 500);
            return;
        }

        $this->sendSuccess([
            'message' => 'Appointment deleted successfully',
            'id' => $id
        ]);
    }

    /**
     * Format an appointment for Bullhorn-compatible API response
     *
     * @param array $appointment Raw appointment data from Appointments
     * @return array Formatted appointment
     */
    private function formatAppointment($appointment)
    {
        return [
            'id' => intval($appointment['appointmentID'] ?? 0),
            'title' => $appointment['title'] ?? '',
            'description' => $appointment['description'] ?? '',
            'type' => $appointment['type'] ?? 'Other',
            'startDate' => $appointment['startDate'] ?? '',
            'endDate' => $appointment['endDate'] ?? '',
            'allDay' => (bool)($appointment['allDay'] ?? false),
            'location' => $appointment['location'] ?? '',
            'personType' => $appointment['personType'] ?? null,
            'personId' => isset($appointment['personID']) && $appointment['personID'] !== null
                ? intval($appointment['personID']) : null,
            'jobOrderId' => isset($appointment['jobOrderID']) && $appointment['jobOrderID'] !== null
                ? intval($appointment['jobOrderID']) : null,
            'status' => $appointment['status'] ?? 'Scheduled',
            'owner' => [
                'id' => intval($appointment['ownerID'] ?? 0),
                'firstName' => $appointment['ownerFirstName'] ?? '',
                'lastName' => $appointment['ownerLastName'] ?? ''
            ],
            'dateAdded' => $appointment['dateCreated'] ?? '',
            'dateLastModified' => $appointment['dateModified'] ?? ''
        ];
    }
}
