<?php
/**
 * CATS
 * API Contact Handler
 *
 * Handles read operations for Contacts (Bullhorn ClientContact equivalent).
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

class ContactHandler
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
     * Handle contacts endpoint (Bullhorn ClientContact equivalent)
     * Supports: GET (list/single with search and pagination)
     */
    public function handle()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method !== 'GET') {
            $this->sendError('Method not allowed. Only GET is currently supported for contacts.', 405);
            return;
        }

        $contacts = new Contacts($this->_siteID);

        if ($id) {
            $this->handleGetSingle($contacts, $id);
        } else {
            $this->handleList($contacts);
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
}
