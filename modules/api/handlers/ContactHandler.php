<?php
/**
 * CATS
 * API Contact Handler
 *
 * Handles CRUD operations for Contacts (Bullhorn ClientContact equivalent).
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

include_once('./lib/Contacts.php');
include_once(dirname(__FILE__) . '/../formatters/EntityFormatter.php');
include_once(dirname(__FILE__) . '/../traits/ApiHelpers.php');
include_once(dirname(__FILE__) . '/../traits/WebhookTrigger.php');

class ContactHandler
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
     * @param object|null $requestLogger Optional request logger instance
     */
    public function __construct($siteID, $userID, $requestLogger = null)
    {
        $this->_siteID = $siteID;
        $this->_userID = $userID;
        $this->_requestLogger = $requestLogger;
    }

    /**
     * Handle contacts endpoint (Bullhorn ClientContact equivalent)
     * Supports: GET (list/single), POST (create), PUT (update), DELETE
     */
    public function handle()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $method = $_SERVER['REQUEST_METHOD'];

        $contacts = new Contacts($this->_siteID);

        switch ($method) {
            case 'GET':
                if ($id) {
                    $this->handleGetSingle($contacts, $id);
                } else {
                    $this->handleList($contacts);
                }
                break;
            case 'POST':
                $this->handlePost($contacts);
                break;
            case 'PUT':
                $this->handlePut($contacts, $id);
                break;
            case 'DELETE':
                $this->handleDelete($contacts, $id);
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    private function handleGetSingle($contacts, $id)
    {
        $contact = $contacts->get($id);
        if ($contact && is_array($contact) && count($contact) > 0) {
            $this->sendSuccess(EntityFormatter::formatContact($contact));
        } else {
            $this->sendError('Contact not found', 404);
        }
    }

    private function handleList($contacts)
    {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $companyID = isset($_GET['clientCorporation']) ? intval($_GET['clientCorporation']) : null;

        $pagination = $this->getPaginationParams();

        $allContacts = $contacts->getAll();

        $filtered = [];
        if (is_array($allContacts)) {
            foreach ($allContacts as $row) {
                if (!empty($search)) {
                    $nameMatch = stripos(($row['firstName'] ?? '') . ' ' . ($row['lastName'] ?? ''), $search) !== false;
                    $emailMatch = stripos($row['email1'] ?? '', $search) !== false;
                    if (!$nameMatch && !$emailMatch) continue;
                }
                if ($companyID !== null && intval($row['companyID'] ?? 0) !== $companyID) continue;

                $filtered[] = EntityFormatter::formatContact($row);
            }
        }

        $this->sendPaginatedResponse($filtered, $pagination['page'], $pagination['limit']);
    }

    /**
     * Create a new contact
     * POST /contacts
     * Required: firstName, lastName, clientCorporation (companyID)
     * Optional: title, email1, email2, phone, phoneCell, address, city, state, zip, notes, isHot
     */
    private function handlePost($contacts)
    {
        $input = $this->getRequestBody();

        // Validate required fields
        if (empty($input['firstName'])) {
            $this->sendError('Missing required field: firstName', 400);
            return;
        }

        if (empty($input['lastName'])) {
            $this->sendError('Missing required field: lastName', 400);
            return;
        }

        // Support both clientCorporation.id and companyID
        $companyID = null;
        if (!empty($input['clientCorporation']['id'])) {
            $companyID = intval($input['clientCorporation']['id']);
        } elseif (!empty($input['companyID'])) {
            $companyID = intval($input['companyID']);
        } elseif (!empty($input['clientCorporationId'])) {
            $companyID = intval($input['clientCorporationId']);
        }

        if (empty($companyID)) {
            $this->sendError('Missing required field: clientCorporation.id or companyID', 400);
            return;
        }

        // Extract optional fields with defaults
        $firstName = trim($input['firstName']);
        $lastName = trim($input['lastName']);
        $title = isset($input['title']) ? trim($input['title']) : '';
        $department = isset($input['department']) ? trim($input['department']) : '';
        $reportsTo = isset($input['reportsTo']) ? intval($input['reportsTo']) : -1;
        $email1 = isset($input['email1']) ? trim($input['email1']) : (isset($input['email']) ? trim($input['email']) : '');
        $email2 = isset($input['email2']) ? trim($input['email2']) : '';
        $phoneWork = isset($input['phone']) ? trim($input['phone']) : (isset($input['phoneWork']) ? trim($input['phoneWork']) : '');
        $phoneCell = isset($input['phoneCell']) ? trim($input['phoneCell']) : '';
        $phoneOther = isset($input['phoneOther']) ? trim($input['phoneOther']) : '';
        $address = isset($input['address']) ? trim($input['address']) : (isset($input['address1']) ? trim($input['address1']) : '');
        $city = isset($input['city']) ? trim($input['city']) : '';
        $state = isset($input['state']) ? trim($input['state']) : '';
        $zip = isset($input['zip']) ? trim($input['zip']) : '';
        $isHot = isset($input['isHot']) ? (bool)$input['isHot'] : false;
        $notes = isset($input['notes']) ? trim($input['notes']) : '';

        // Create the contact using the Contacts library
        $contactID = $contacts->add(
            $companyID,
            $firstName,
            $lastName,
            $title,
            $department,
            $reportsTo,
            $email1,
            $email2,
            $phoneWork,
            $phoneCell,
            $phoneOther,
            $address,
            $city,
            $state,
            $zip,
            $isHot,
            $notes,
            $this->_userID,  // entered_by
            $this->_userID   // owner
        );

        if ($contactID == -1) {
            $this->sendError('Failed to create contact', 500);
            return;
        }

        // Fetch and return the newly created contact
        $newContact = $contacts->get($contactID);
        if ($newContact && is_array($newContact) && count($newContact) > 0) {
            $formattedContact = EntityFormatter::formatContact($newContact);
            $this->sendSuccess($formattedContact, 201);
            $this->triggerWebhook('contact', 'create', $contactID, $formattedContact);
        } else {
            $this->sendSuccess(['id' => $contactID, 'message' => 'Contact created successfully'], 201);
            $this->triggerWebhook('contact', 'create', $contactID, ['id' => $contactID]);
        }
    }

    /**
     * Update an existing contact
     * PUT /contacts?id={id}
     * All fields are optional
     */
    private function handlePut($contacts, $id)
    {
        if (!$id) {
            $this->sendError('Contact ID required for update', 400);
            return;
        }

        // Get existing contact
        $existing = $contacts->get($id);
        if (!$existing || !is_array($existing) || count($existing) == 0) {
            $this->sendError('Contact not found', 404);
            return;
        }

        $input = $this->getRequestBody();

        // Merge input with existing values
        $companyID = $existing['companyID'];
        if (!empty($input['clientCorporation']['id'])) {
            $companyID = intval($input['clientCorporation']['id']);
        } elseif (isset($input['companyID'])) {
            $companyID = intval($input['companyID']);
        }

        $firstName = isset($input['firstName']) ? trim($input['firstName']) : $existing['firstName'];
        $lastName = isset($input['lastName']) ? trim($input['lastName']) : $existing['lastName'];
        $title = isset($input['title']) ? trim($input['title']) : ($existing['title'] ?? '');
        $department = isset($input['department']) ? trim($input['department']) : ($existing['department'] ?? '');
        $reportsTo = isset($input['reportsTo']) ? intval($input['reportsTo']) : ($existing['reportsTo'] ?? -1);
        $email1 = isset($input['email1']) ? trim($input['email1']) : (isset($input['email']) ? trim($input['email']) : ($existing['email1'] ?? ''));
        $email2 = isset($input['email2']) ? trim($input['email2']) : ($existing['email2'] ?? '');
        $phoneWork = isset($input['phone']) ? trim($input['phone']) : (isset($input['phoneWork']) ? trim($input['phoneWork']) : ($existing['phoneWork'] ?? ''));
        $phoneCell = isset($input['phoneCell']) ? trim($input['phoneCell']) : ($existing['phoneCell'] ?? '');
        $phoneOther = isset($input['phoneOther']) ? trim($input['phoneOther']) : ($existing['phoneOther'] ?? '');
        $address = isset($input['address']) ? trim($input['address']) : ($existing['address'] ?? '');
        $city = isset($input['city']) ? trim($input['city']) : ($existing['city'] ?? '');
        $state = isset($input['state']) ? trim($input['state']) : ($existing['state'] ?? '');
        $zip = isset($input['zip']) ? trim($input['zip']) : ($existing['zip'] ?? '');
        $isHot = isset($input['isHot']) ? (bool)$input['isHot'] : (bool)($existing['isHotContact'] ?? 0);
        $leftCompany = isset($input['leftCompany']) ? (bool)$input['leftCompany'] : (bool)($existing['leftCompany'] ?? 0);
        $notes = isset($input['notes']) ? trim($input['notes']) : ($existing['notes'] ?? '');
        $owner = isset($input['owner']) ? intval($input['owner']) : ($existing['owner'] ?? $this->_userID);

        // Update the contact
        $success = $contacts->update(
            $id,
            $companyID,
            $firstName,
            $lastName,
            $title,
            $department,
            $reportsTo,
            $email1,
            $email2,
            $phoneWork,
            $phoneCell,
            $phoneOther,
            $address,
            $city,
            $state,
            $zip,
            $isHot,
            $leftCompany,
            $notes,
            $owner,
            '',   // email notification message
            ''    // email notification address
        );

        if (!$success) {
            $this->sendError('Failed to update contact', 500);
            return;
        }

        // Fetch and return the updated contact
        $updatedContact = $contacts->get($id);
        if ($updatedContact && is_array($updatedContact) && count($updatedContact) > 0) {
            $formattedContact = EntityFormatter::formatContact($updatedContact);
            $this->sendSuccess($formattedContact);
            $this->triggerWebhook('contact', 'update', $id, $formattedContact);
        } else {
            $this->sendSuccess(['id' => $id, 'message' => 'Contact updated successfully']);
            $this->triggerWebhook('contact', 'update', $id, ['id' => $id]);
        }
    }

    /**
     * Delete a contact
     * DELETE /contacts?id={id}
     */
    private function handleDelete($contacts, $id)
    {
        if (!$id) {
            $this->sendError('Contact ID required for delete', 400);
            return;
        }

        // Verify contact exists
        $existing = $contacts->get($id);
        if (!$existing || !is_array($existing) || count($existing) == 0) {
            $this->sendError('Contact not found', 404);
            return;
        }

        // Delete the contact
        $contacts->delete($id);

        $this->sendSuccess([
            'message' => 'Contact deleted successfully',
            'id' => $id
        ]);
        $this->triggerWebhook('contact', 'delete', $id, ['id' => $id]);
    }
}
