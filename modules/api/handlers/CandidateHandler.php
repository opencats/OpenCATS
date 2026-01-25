<?php
/**
 * CATS
 * API Candidate Handler
 *
 * Handles CRUD operations for Candidates.
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

include_once('./lib/Candidates.php');
include_once(dirname(__FILE__) . '/../formatters/EntityFormatter.php');
include_once(dirname(__FILE__) . '/../traits/ApiHelpers.php');

class CandidateHandler
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
     * Handle candidates endpoint
     * Supports: GET (list/single), POST (create), PUT (update), DELETE
     */
    public function handle()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $method = $_SERVER['REQUEST_METHOD'];

        $candidates = new Candidates($this->_siteID);

        switch ($method) {
            case 'GET':
                $this->handleGet($candidates, $id);
                break;
            case 'POST':
                $this->handlePost($candidates);
                break;
            case 'PUT':
                $this->handlePut($candidates, $id);
                break;
            case 'DELETE':
                $this->handleDelete($candidates, $id);
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    private function handleGet($candidates, $id)
    {
        if ($id) {
            $candidate = $candidates->get($id);
            if ($candidate && is_array($candidate) && count($candidate) > 0) {
                $this->sendSuccess(EntityFormatter::formatCandidate($candidate));
            } else {
                $this->sendError('Candidate not found', 404);
            }
        } else {
            $this->handleList($candidates);
        }
    }

    private function handleList($candidates)
    {
        $search = isset($_GET['search']) ? trim($_GET['search']) : '';
        $skills = isset($_GET['skills']) ? trim($_GET['skills']) : '';
        $isHot = isset($_GET['isHot']) ? filter_var($_GET['isHot'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;

        $pagination = $this->getPaginationParams();

        $allCandidates = $candidates->getAll(false);

        $filtered = [];
        if (is_array($allCandidates)) {
            foreach ($allCandidates as $row) {
                if (!empty($search)) {
                    $nameMatch = stripos(($row['firstName'] ?? '') . ' ' . ($row['lastName'] ?? ''), $search) !== false;
                    $emailMatch = stripos($row['email1'] ?? '', $search) !== false;
                    $skillsMatch = stripos($row['keySkills'] ?? '', $search) !== false;
                    if (!$nameMatch && !$emailMatch && !$skillsMatch) continue;
                }
                if (!empty($skills) && stripos($row['keySkills'] ?? '', $skills) === false) continue;
                if ($isHot !== null && (bool)($row['isHot'] ?? 0) !== $isHot) continue;

                $filtered[] = EntityFormatter::formatCandidate($row);
            }
        }

        $this->sendPaginatedResponse($filtered, $pagination['page'], $pagination['limit']);
    }

    private function handlePost($candidates)
    {
        $input = $this->getRequestBody();

        if (empty($input['firstName']) || empty($input['lastName'])) {
            $this->sendError('Missing required fields: firstName and lastName', 400);
            return;
        }

        $firstName = $input['firstName'];
        $middleName = isset($input['middleName']) ? $input['middleName'] : '';
        $lastName = $input['lastName'];
        $email1 = isset($input['email1']) ? $input['email1'] : '';
        $email2 = isset($input['email2']) ? $input['email2'] : '';
        $phoneHome = isset($input['phone']) ? $input['phone'] : '';
        $phoneCell = isset($input['phoneCell']) ? $input['phoneCell'] : '';
        $phoneWork = isset($input['phoneWork']) ? $input['phoneWork'] : '';
        $address = isset($input['address']) ? $input['address'] : '';
        $city = isset($input['city']) ? $input['city'] : '';
        $state = isset($input['state']) ? $input['state'] : '';
        $zip = isset($input['zip']) ? $input['zip'] : '';
        $source = isset($input['source']) ? $input['source'] : '';
        $keySkills = isset($input['keySkills']) ? $input['keySkills'] : '';
        $dateAvailable = isset($input['dateAvailable']) ? $input['dateAvailable'] : '';
        $currentEmployer = isset($input['currentEmployer']) ? $input['currentEmployer'] : '';
        $canRelocate = isset($input['canRelocate']) ? intval($input['canRelocate']) : 0;
        $currentPay = isset($input['currentPay']) ? $input['currentPay'] : '';
        $desiredPay = isset($input['desiredPay']) ? $input['desiredPay'] : '';
        $notes = isset($input['notes']) ? $input['notes'] : '';
        $webSite = isset($input['webSite']) ? $input['webSite'] : '';
        $bestTimeToCall = isset($input['bestTimeToCall']) ? $input['bestTimeToCall'] : '';
        $owner = isset($input['ownerID']) ? intval($input['ownerID']) : $this->_userID;

        $candidateID = $candidates->add(
            $firstName, $middleName, $lastName, $email1, $email2,
            $phoneHome, $phoneCell, $phoneWork, $address, $city,
            $state, $zip, $source, $keySkills, $dateAvailable,
            $currentEmployer, $canRelocate, $currentPay, $desiredPay,
            $notes, $webSite, $bestTimeToCall, $this->_userID, $owner
        );

        if ($candidateID <= 0) {
            $this->sendError('Failed to create candidate', 500);
            return;
        }

        $newCandidate = $candidates->get($candidateID);
        $this->sendSuccess(EntityFormatter::formatCandidate($newCandidate), 201);
    }

    private function handlePut($candidates, $id)
    {
        if (!$id) {
            $this->sendError('Candidate ID required for update', 400);
            return;
        }

        $existing = $candidates->get($id);
        if (!$existing || empty($existing['candidate_id'])) {
            $this->sendError('Candidate not found', 404);
            return;
        }

        $input = $this->getRequestBody();

        $isActive = isset($input['isActive']) ? intval($input['isActive']) : 1;
        $firstName = isset($input['firstName']) ? $input['firstName'] : $existing['first_name'];
        $middleName = isset($input['middleName']) ? $input['middleName'] : ($existing['middle_name'] ?? '');
        $lastName = isset($input['lastName']) ? $input['lastName'] : $existing['last_name'];
        $email1 = isset($input['email1']) ? $input['email1'] : ($existing['email1'] ?? '');
        $email2 = isset($input['email2']) ? $input['email2'] : ($existing['email2'] ?? '');
        $phoneHome = isset($input['phone']) ? $input['phone'] : ($existing['phone_home'] ?? '');
        $phoneCell = isset($input['phoneCell']) ? $input['phoneCell'] : ($existing['phone_cell'] ?? '');
        $phoneWork = isset($input['phoneWork']) ? $input['phoneWork'] : ($existing['phone_work'] ?? '');
        $address = isset($input['address']) ? $input['address'] : ($existing['address'] ?? '');
        $city = isset($input['city']) ? $input['city'] : ($existing['city'] ?? '');
        $state = isset($input['state']) ? $input['state'] : ($existing['state'] ?? '');
        $zip = isset($input['zip']) ? $input['zip'] : ($existing['zip'] ?? '');
        $source = isset($input['source']) ? $input['source'] : ($existing['source'] ?? '');
        $keySkills = isset($input['keySkills']) ? $input['keySkills'] : ($existing['key_skills'] ?? '');
        $dateAvailable = isset($input['dateAvailable']) ? $input['dateAvailable'] : ($existing['date_available'] ?? '');
        $currentEmployer = isset($input['currentEmployer']) ? $input['currentEmployer'] : ($existing['current_employer'] ?? '');
        $canRelocate = isset($input['canRelocate']) ? intval($input['canRelocate']) : ($existing['can_relocate'] ?? 0);
        $currentPay = isset($input['currentPay']) ? $input['currentPay'] : ($existing['current_pay'] ?? '');
        $desiredPay = isset($input['desiredPay']) ? $input['desiredPay'] : ($existing['desired_pay'] ?? '');
        $notes = isset($input['notes']) ? $input['notes'] : ($existing['notes'] ?? '');
        $webSite = isset($input['webSite']) ? $input['webSite'] : ($existing['web_site'] ?? '');
        $bestTimeToCall = isset($input['bestTimeToCall']) ? $input['bestTimeToCall'] : ($existing['best_time_to_call'] ?? '');
        $owner = isset($input['ownerID']) ? intval($input['ownerID']) : ($existing['owner'] ?? $this->_userID);
        $isHot = isset($input['isHot']) ? intval($input['isHot']) : ($existing['is_hot'] ?? 0);
        $email = 0;
        $emailAddress = '';

        $success = $candidates->update(
            $id, $isActive, $firstName, $middleName, $lastName,
            $email1, $email2, $phoneHome, $phoneCell, $phoneWork,
            $address, $city, $state, $zip, $source,
            $keySkills, $dateAvailable, $currentEmployer, $canRelocate,
            $currentPay, $desiredPay, $notes, $webSite, $bestTimeToCall,
            $owner, $isHot, $email, $emailAddress
        );

        if (!$success) {
            $this->sendError('Failed to update candidate', 500);
            return;
        }

        $updated = $candidates->get($id);
        $this->sendSuccess(EntityFormatter::formatCandidate($updated));
    }

    private function handleDelete($candidates, $id)
    {
        if (!$id) {
            $this->sendError('Candidate ID required for delete', 400);
            return;
        }

        $existing = $candidates->get($id);
        if (!$existing || empty($existing['candidate_id'])) {
            $this->sendError('Candidate not found', 404);
            return;
        }

        $success = $candidates->delete($id);

        if (!$success) {
            $this->sendError('Failed to delete candidate', 500);
            return;
        }

        $this->sendSuccess([
            'message' => 'Candidate deleted successfully',
            'id' => $id
        ]);
    }
}
