<?php
/**
 * CATS
 * API Company Handler
 *
 * Handles CRUD operations for Companies.
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

include_once('./lib/Companies.php');
include_once(dirname(__FILE__) . '/../formatters/EntityFormatter.php');
include_once(dirname(__FILE__) . '/../traits/ApiHelpers.php');

class CompanyHandler
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
     * Handle companies endpoint
     * Supports: GET (list/single), POST (create), PUT (update), DELETE
     */
    public function handle()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $method = $_SERVER['REQUEST_METHOD'];

        $companies = new Companies($this->_siteID);

        switch ($method) {
            case 'GET':
                $this->handleGet($companies, $id);
                break;
            case 'POST':
                $this->handlePost($companies);
                break;
            case 'PUT':
                $this->handlePut($companies, $id);
                break;
            case 'DELETE':
                $this->handleDelete($companies, $id);
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    private function handleGet($companies, $id)
    {
        if ($id) {
            $company = $companies->get($id);
            if ($company && is_array($company) && count($company) > 0) {
                $this->sendSuccess(EntityFormatter::formatCompany($company));
            } else {
                $this->sendError('Company not found', 404);
            }
        } else {
            $this->handleList($companies);
        }
    }

    private function handleList($companies)
    {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $city = isset($_GET['city']) ? trim($_GET['city']) : '';
        $state = isset($_GET['state']) ? trim($_GET['state']) : '';
        $isHot = isset($_GET['isHot']) ? filter_var($_GET['isHot'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;

        $pagination = $this->getPaginationParams();

        $allCompanies = $companies->getAll();

        $filtered = [];
        if (is_array($allCompanies)) {
            foreach ($allCompanies as $row) {
                if (!empty($search)) {
                    $nameMatch = stripos($row['name'] ?? '', $search) !== false;
                    if (!$nameMatch) continue;
                }
                if (!empty($city) && stripos($row['city'] ?? '', $city) === false) continue;
                if (!empty($state) && ($row['state'] ?? '') !== $state) continue;
                if ($isHot !== null && (bool)($row['isHot'] ?? 0) !== $isHot) continue;

                $filtered[] = EntityFormatter::formatCompany($row);
            }
        }

        $this->sendPaginatedResponse($filtered, $pagination['page'], $pagination['limit']);
    }

    private function handlePost($companies)
    {
        $input = $this->getRequestBody();

        if (empty($input['name'])) {
            $this->sendError('Missing required field: name', 400);
            return;
        }

        $name = $input['name'];
        $address = isset($input['address']) ? $input['address'] : '';
        $city = isset($input['city']) ? $input['city'] : '';
        $state = isset($input['state']) ? $input['state'] : '';
        $zip = isset($input['zip']) ? $input['zip'] : '';
        $phone1 = isset($input['phone']) ? $input['phone'] : '';
        $phone2 = isset($input['phone2']) ? $input['phone2'] : '';
        $faxNumber = isset($input['fax']) ? $input['fax'] : '';
        $url = isset($input['url']) ? $input['url'] : '';
        $keyTechnologies = isset($input['keyTechnologies']) ? $input['keyTechnologies'] : '';
        $isHot = isset($input['isHot']) ? intval($input['isHot']) : 0;
        $notes = isset($input['notes']) ? $input['notes'] : '';
        $owner = isset($input['ownerID']) ? intval($input['ownerID']) : $this->_userID;

        $companyID = $companies->add(
            $name, $address, $city, $state, $zip,
            $phone1, $phone2, $faxNumber, $url, $keyTechnologies,
            $isHot, $notes, $this->_userID, $owner
        );

        if ($companyID <= 0) {
            $this->sendError('Failed to create company', 500);
            return;
        }

        $newCompany = $companies->get($companyID);
        $this->sendSuccess(EntityFormatter::formatCompany($newCompany), 201);
    }

    private function handlePut($companies, $id)
    {
        if (!$id) {
            $this->sendError('Company ID required for update', 400);
            return;
        }

        $existing = $companies->get($id);
        if (!$existing || empty($existing['company_id'])) {
            $this->sendError('Company not found', 404);
            return;
        }

        $input = $this->getRequestBody();

        $name = isset($input['name']) ? $input['name'] : $existing['name'];
        $address = isset($input['address']) ? $input['address'] : ($existing['address'] ?? '');
        $city = isset($input['city']) ? $input['city'] : ($existing['city'] ?? '');
        $state = isset($input['state']) ? $input['state'] : ($existing['state'] ?? '');
        $zip = isset($input['zip']) ? $input['zip'] : ($existing['zip'] ?? '');
        $phone1 = isset($input['phone']) ? $input['phone'] : ($existing['phone1'] ?? '');
        $phone2 = isset($input['phone2']) ? $input['phone2'] : ($existing['phone2'] ?? '');
        $faxNumber = isset($input['fax']) ? $input['fax'] : ($existing['fax_number'] ?? '');
        $url = isset($input['url']) ? $input['url'] : ($existing['url'] ?? '');
        $keyTechnologies = isset($input['keyTechnologies']) ? $input['keyTechnologies'] : ($existing['key_technologies'] ?? '');
        $isHot = isset($input['isHot']) ? intval($input['isHot']) : ($existing['is_hot'] ?? 0);
        $notes = isset($input['notes']) ? $input['notes'] : ($existing['notes'] ?? '');
        $owner = isset($input['ownerID']) ? intval($input['ownerID']) : ($existing['owner'] ?? $this->_userID);
        $billingContact = isset($input['billingContact']) ? intval($input['billingContact']) : 0;
        $email = 0;
        $emailAddress = '';

        $success = $companies->update(
            $id, $name, $address, $city, $state, $zip,
            $phone1, $phone2, $faxNumber, $url, $keyTechnologies,
            $isHot, $notes, $owner, $billingContact, $email, $emailAddress
        );

        if (!$success) {
            $this->sendError('Failed to update company', 500);
            return;
        }

        $updated = $companies->get($id);
        $this->sendSuccess(EntityFormatter::formatCompany($updated));
    }

    private function handleDelete($companies, $id)
    {
        if (!$id) {
            $this->sendError('Company ID required for delete', 400);
            return;
        }

        $existing = $companies->get($id);
        if (!$existing || empty($existing['company_id'])) {
            $this->sendError('Company not found', 404);
            return;
        }

        $success = $companies->delete($id);

        if (!$success) {
            $this->sendError('Failed to delete company', 500);
            return;
        }

        $this->sendSuccess([
            'message' => 'Company deleted successfully',
            'id' => $id
        ]);
    }
}
