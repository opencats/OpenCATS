<?php
/**
 * CATS
 * REST API Module
 *
 * Provides RESTful API access to OpenCATS data.
 * Designed to be compatible with Bullhorn API patterns.
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * Copyright (C) 2026 Space-O Technologies (https://www.spaceotechnologies.com/)
 *
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
 * The Original Code is "CATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 *
 * @package    CATS
 * @subpackage Modules
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: ApiUI.php 2026-01-25 $
 */

include_once('./lib/JobOrders.php');
include_once('./lib/Candidates.php');
include_once('./lib/Companies.php');
include_once('./lib/Contacts.php');

// Include API libraries
if (file_exists('./lib/Tearsheets.php')) {
    include_once('./lib/Tearsheets.php');
}
if (file_exists('./lib/ApiKeys.php')) {
    include_once('./lib/ApiKeys.php');
}
if (file_exists('./lib/ApiConfig.php')) {
    include_once('./lib/ApiConfig.php');
}
if (file_exists('./lib/ApiRateLimiter.php')) {
    include_once('./lib/ApiRateLimiter.php');
}
if (file_exists('./lib/ApiRequestLogger.php')) {
    include_once('./lib/ApiRequestLogger.php');
}

class ApiUI extends UserInterface
{
    // $_siteID and $_userID are inherited from UserInterface (protected)
    protected $_accessLevel = 0;
    private $_authenticated = false;
    private $_apiKeyID = null;
    private $_rateLimiter = null;
    private $_requestLogger = null;

    public function __construct()
    {
        parent::__construct();
        $this->_moduleDirectory = 'api';
        $this->_moduleName = 'api';
        // Use site_id=1 for API access (matches admin user's site)
        $this->_siteID = 1;
    }

    /**
     * API module handles its own authentication via API keys.
     * This tells OpenCATS not to require session-based login.
     *
     * @return boolean false - API handles its own auth
     */
    public function requiresAuthentication()
    {
        return false;
    }

    public function handleRequest()
    {
        // Set JSON headers
        header('Content-Type: application/json; charset=utf-8');

        // CORS settings (configurable)
        $corsOrigin = defined('API_CORS_ALLOWED_ORIGINS') ? API_CORS_ALLOWED_ORIGINS : '*';
        header('Access-Control-Allow-Origin: ' . $corsOrigin);
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Api-Key');

        // Handle CORS preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $action = $this->getAction();

        // Initialize request logger (even for unauthenticated requests)
        if (class_exists('ApiRequestLogger') && (!defined('API_LOG_ENABLED') || API_LOG_ENABLED)) {
            $this->_requestLogger = new ApiRequestLogger(
                null,
                $action,
                $_SERVER['REQUEST_METHOD'] ?? 'GET'
            );
        }

        // Auth endpoint doesn't require authentication
        if ($action !== 'auth' && $action !== 'ping') {
            if (!$this->_authenticate()) {
                $this->_sendError('Unauthorized. Provide valid API key.', 401);
                return;
            }

            // Check rate limits after authentication
            if (class_exists('ApiRateLimiter') && $this->_apiKeyID) {
                $rateEnabled = !defined('API_RATE_LIMIT_ENABLED') || API_RATE_LIMIT_ENABLED;
                if ($rateEnabled) {
                    $ratePerMinute = defined('API_RATE_LIMIT_PER_MINUTE') ? API_RATE_LIMIT_PER_MINUTE : 60;
                    $ratePerHour = defined('API_RATE_LIMIT_PER_HOUR') ? API_RATE_LIMIT_PER_HOUR : 1000;

                    $this->_rateLimiter = new ApiRateLimiter($this->_apiKeyID, $ratePerMinute, $ratePerHour);
                    $limitInfo = $this->_rateLimiter->checkLimit();

                    // Add rate limit headers to all responses
                    foreach (ApiRateLimiter::getHeaders($limitInfo) as $header => $value) {
                        header("{$header}: {$value}");
                    }

                    if (!$limitInfo['allowed']) {
                        $this->_sendError($limitInfo['reason'], 429);
                        return;
                    }
                }
            }
        }

        // Route requests
        switch ($action) {
            case 'ping':
                $this->_handlePing();
                break;

            case 'auth':
                $this->_handleAuth();
                break;

            case 'joborders':
            case 'joborder':
                $this->_handleJobOrders();
                break;

            case 'tearsheets':
            case 'tearsheet':
                $this->_handleTearsheets();
                break;

            case 'candidates':
            case 'candidate':
                $this->_handleCandidates();
                break;

            case 'companies':
            case 'company':
                $this->_handleCompanies();
                break;

            case 'contacts':
            case 'contact':
                $this->_handleContacts();
                break;

            case 'meta':
                $this->_handleMeta();
                break;

            default:
                // Sanitize action to prevent XSS in error response
                $safeAction = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
                $this->_sendError('Unknown endpoint: ' . $safeAction, 404);
        }
    }

    /**
     * Simple ping endpoint for health checks
     */
    private function _handlePing()
    {
        $version = defined('API_VERSION') ? API_VERSION : '1.0.0';
        $this->_sendSuccess([
            'status' => 'ok',
            'version' => $version,
            'timestamp' => date('c')
        ]);
    }

    /**
     * Authenticate the request
     */
    private function _authenticate()
    {
        // Check for API key in headers
        $headers = $this->_getRequestHeaders();
        
        $apiKey = null;
        
        // Try X-Api-Key header first
        if (isset($headers['X-Api-Key'])) {
            $apiKey = $headers['X-Api-Key'];
        }
        // Then try Authorization: Bearer token
        elseif (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.+)/i', $headers['Authorization'], $matches)) {
                $apiKey = $matches[1];
            }
        }
        // Finally try query parameter (less secure, for testing)
        elseif (isset($_GET['api_key'])) {
            $apiKey = $_GET['api_key'];
        }

        if (!$apiKey) {
            return false;
        }

        // Check database for API key
        if (class_exists('ApiKeys')) {
            $apiKeys = new ApiKeys($this->_siteID);
            $result = $apiKeys->validate($apiKey);
            if ($result) {
                $this->_authenticated = true;
                $this->_userID = $result['user_id'];
                $this->_accessLevel = $result['access_level'];
                $this->_apiKeyID = $result['api_key_id'];

                // Update request logger with authenticated API key
                if ($this->_requestLogger) {
                    $this->_requestLogger->setApiKeyID($this->_apiKeyID);
                }

                return true;
            }
        }

        return false;
    }

    /**
     * Handle authentication endpoint
     */
    private function _handleAuth()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->_sendError('Method not allowed. Use POST.', 405);
            return;
        }

        $input = $this->_getRequestBody();
        
        if (!isset($input['api_key']) || !isset($input['api_secret'])) {
            $this->_sendError('Missing api_key or api_secret', 400);
            return;
        }

        // Validate credentials against database
        if (class_exists('ApiKeys')) {
            $apiKeys = new ApiKeys($this->_siteID);
            $result = $apiKeys->authenticate($input['api_key'], $input['api_secret']);
            if ($result) {
                $this->_sendSuccess([
                    'access_token' => $result['access_token'],
                    'token_type' => 'Bearer',
                    'expires_in' => $result['expires_in'] ?? 3600,
                    'refresh_token' => $result['refresh_token'] ?? null
                ]);
                return;
            }
        }

        $this->_sendError('Invalid credentials', 401);
    }

    /**
     * Handle job orders endpoint
     * Supports: GET (list/single), POST (create), PUT (update), DELETE
     */
    private function _handleJobOrders()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $method = $_SERVER['REQUEST_METHOD'];

        $jobOrders = new JobOrders($this->_siteID);

        switch ($method) {
            case 'GET':
                if ($id) {
                    // GET single job order
                    $job = $jobOrders->get($id);
                    if ($job && is_array($job) && count($job) > 0) {
                        $this->_sendSuccess($this->_formatJobOrder($job));
                    } else {
                        $this->_sendError('Job order not found', 404);
                    }
                } else {
                    // GET list of job orders with optional search params
                    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                    $status = isset($_GET['status']) ? trim($_GET['status']) : '';
                    $city = isset($_GET['city']) ? trim($_GET['city']) : '';
                    $state = isset($_GET['state']) ? trim($_GET['state']) : '';

                    // Pagination
                    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 25;

                    $jobsData = $jobOrders->getAll(
                        JOBORDERS_STATUS_ALL,
                        -1,
                        -1
                    );

                    $jobs = [];
                    if (is_array($jobsData)) {
                        foreach ($jobsData as $row) {
                            // Apply filters
                            if (!empty($search)) {
                                $titleMatch = stripos($row['title'] ?? '', $search) !== false;
                                $descMatch = stripos($row['description'] ?? '', $search) !== false;
                                if (!$titleMatch && !$descMatch) continue;
                            }
                            if (!empty($status) && ($row['status'] ?? '') !== $status) continue;
                            if (!empty($city) && stripos($row['city'] ?? '', $city) === false) continue;
                            if (!empty($state) && ($row['state'] ?? '') !== $state) continue;

                            $jobs[] = $this->_formatJobOrder($row);
                        }
                    }

                    // Apply pagination
                    $total = count($jobs);
                    $offset = ($page - 1) * $limit;
                    $pagedJobs = array_slice($jobs, $offset, $limit);

                    $this->_sendSuccess([
                        'total' => $total,
                        'page' => $page,
                        'limit' => $limit,
                        'data' => $pagedJobs
                    ]);
                }
                break;

            case 'POST':
                // Create new job order
                $input = $this->_getRequestBody();

                if (empty($input['title'])) {
                    $this->_sendError('Missing required field: title', 400);
                    return;
                }
                if (empty($input['companyID'])) {
                    $this->_sendError('Missing required field: companyID', 400);
                    return;
                }

                // Map input to JobOrders::add() parameters
                $title = $input['title'];
                $companyID = intval($input['companyID']);
                $contactID = isset($input['contactID']) ? intval($input['contactID']) : 0;
                $description = isset($input['description']) ? $input['description'] : '';
                $notes = isset($input['notes']) ? $input['notes'] : '';
                $duration = isset($input['duration']) ? $input['duration'] : '';
                $maxRate = isset($input['maxRate']) ? $input['maxRate'] : '';
                $type = isset($input['type']) ? $input['type'] : 'H';
                $isHot = isset($input['isHot']) ? intval($input['isHot']) : 0;
                $public = isset($input['isPublic']) ? intval($input['isPublic']) : 0;
                $openings = isset($input['openings']) ? intval($input['openings']) : 1;
                $companyJobID = isset($input['companyJobID']) ? $input['companyJobID'] : '';
                $salary = isset($input['salary']) ? $input['salary'] : '';
                $city = isset($input['city']) ? $input['city'] : '';
                $state = isset($input['state']) ? $input['state'] : '';
                $startDate = isset($input['startDate']) ? $input['startDate'] : '';
                $recruiter = isset($input['recruiterID']) ? intval($input['recruiterID']) : $this->_userID;
                $owner = isset($input['ownerID']) ? intval($input['ownerID']) : $this->_userID;
                $department = isset($input['department']) ? $input['department'] : '';

                $jobOrderID = $jobOrders->add(
                    $title,
                    $companyID,
                    $contactID,
                    $description,
                    $notes,
                    $duration,
                    $maxRate,
                    $type,
                    $isHot,
                    $public,
                    $openings,
                    $companyJobID,
                    $salary,
                    $city,
                    $state,
                    $startDate,
                    $this->_userID,  // enteredBy
                    $recruiter,
                    $owner,
                    $department
                );

                if ($jobOrderID <= 0) {
                    $this->_sendError('Failed to create job order', 500);
                    return;
                }

                $newJob = $jobOrders->get($jobOrderID);
                $this->_sendSuccess($this->_formatJobOrder($newJob), 201);
                break;

            case 'PUT':
                // Update existing job order
                if (!$id) {
                    $this->_sendError('Job Order ID required for update', 400);
                    return;
                }

                $existing = $jobOrders->get($id);
                if (!$existing || empty($existing['joborder_id'])) {
                    $this->_sendError('Job Order not found', 404);
                    return;
                }

                $input = $this->_getRequestBody();

                // Merge input with existing values
                $title = isset($input['title']) ? $input['title'] : $existing['title'];
                $companyJobID = isset($input['companyJobID']) ? $input['companyJobID'] : ($existing['client_job_id'] ?? '');
                $companyID = isset($input['companyID']) ? intval($input['companyID']) : $existing['company_id'];
                $contactID = isset($input['contactID']) ? intval($input['contactID']) : ($existing['contact_id'] ?? 0);
                $description = isset($input['description']) ? $input['description'] : $existing['description'];
                $notes = isset($input['notes']) ? $input['notes'] : ($existing['notes'] ?? '');
                $duration = isset($input['duration']) ? $input['duration'] : ($existing['duration'] ?? '');
                $maxRate = isset($input['maxRate']) ? $input['maxRate'] : ($existing['rate_max'] ?? '');
                $type = isset($input['type']) ? $input['type'] : ($existing['type'] ?? 'H');
                $isHot = isset($input['isHot']) ? intval($input['isHot']) : ($existing['is_hot'] ?? 0);
                $openings = isset($input['openings']) ? intval($input['openings']) : ($existing['openings'] ?? 1);
                $openingsAvailable = isset($input['openingsAvailable']) ? intval($input['openingsAvailable']) : ($existing['openings_available'] ?? $openings);
                $salary = isset($input['salary']) ? $input['salary'] : ($existing['salary'] ?? '');
                $city = isset($input['city']) ? $input['city'] : ($existing['city'] ?? '');
                $state = isset($input['state']) ? $input['state'] : ($existing['state'] ?? '');
                $startDate = isset($input['startDate']) ? $input['startDate'] : ($existing['start_date'] ?? '');
                $status = isset($input['status']) ? $input['status'] : ($existing['status'] ?? 'Active');
                $recruiter = isset($input['recruiterID']) ? intval($input['recruiterID']) : ($existing['recruiter'] ?? $this->_userID);
                $owner = isset($input['ownerID']) ? intval($input['ownerID']) : ($existing['owner'] ?? $this->_userID);
                $public = isset($input['isPublic']) ? intval($input['isPublic']) : ($existing['public'] ?? 0);
                $email = 0;  // Email notification flag
                $emailAddress = '';
                $department = isset($input['department']) ? $input['department'] : '';

                $success = $jobOrders->update(
                    $id,
                    $title,
                    $companyJobID,
                    $companyID,
                    $contactID,
                    $description,
                    $notes,
                    $duration,
                    $maxRate,
                    $type,
                    $isHot,
                    $openings,
                    $openingsAvailable,
                    $salary,
                    $city,
                    $state,
                    $startDate,
                    $status,
                    $recruiter,
                    $owner,
                    $public,
                    $email,
                    $emailAddress,
                    $department
                );

                if (!$success) {
                    $this->_sendError('Failed to update job order', 500);
                    return;
                }

                $updated = $jobOrders->get($id);
                $this->_sendSuccess($this->_formatJobOrder($updated));
                break;

            case 'DELETE':
                // Delete job order
                if (!$id) {
                    $this->_sendError('Job Order ID required for delete', 400);
                    return;
                }

                $existing = $jobOrders->get($id);
                if (!$existing || empty($existing['joborder_id'])) {
                    $this->_sendError('Job Order not found', 404);
                    return;
                }

                $success = $jobOrders->delete($id);

                if (!$success) {
                    $this->_sendError('Failed to delete job order', 500);
                    return;
                }

                $this->_sendSuccess([
                    'message' => 'Job Order deleted successfully',
                    'id' => $id
                ]);
                break;

            default:
                $this->_sendError('Method not allowed', 405);
        }
    }

    /**
     * Handle tearsheets endpoint
     * Supports: GET (list/single), POST (create), PUT (update), DELETE
     */
    private function _handleTearsheets()
    {
        if (!class_exists('Tearsheets')) {
            $this->_sendError('Tearsheets module not installed', 501);
            return;
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $subAction = isset($_GET['sub']) ? strtolower($_GET['sub']) : null;
        $method = $_SERVER['REQUEST_METHOD'];

        $tearsheets = new Tearsheets($this->_siteID);

        // Handle job association sub-actions
        if ($id && $subAction === 'addjobs' && $method === 'PUT') {
            $this->_handleTearsheetAddJobs($tearsheets, $id);
            return;
        }

        if ($id && $subAction === 'removejobs' && $method === 'DELETE') {
            $this->_handleTearsheetRemoveJobs($tearsheets, $id);
            return;
        }

        // Handle main CRUD operations
        switch ($method) {
            case 'GET':
                if ($id) {
                    if ($subAction === 'joborders') {
                        // Get jobs in this tearsheet
                        $jobs = $tearsheets->getJobOrders($id);
                        $formatted = [];
                        foreach ($jobs as $job) {
                            $formatted[] = $this->_formatJobOrder($job);
                        }
                        $this->_sendSuccess([
                            'total' => count($formatted),
                            'data' => $formatted
                        ]);
                    } else {
                        // Get single tearsheet
                        $tearsheet = $tearsheets->get($id);
                        if ($tearsheet) {
                            $this->_sendSuccess($this->_formatTearsheet($tearsheet));
                        } else {
                            $this->_sendError('Tearsheet not found', 404);
                        }
                    }
                } else {
                    // List all tearsheets
                    $list = $tearsheets->getAll($this->_userID);
                    $formatted = [];
                    foreach ($list as $ts) {
                        $formatted[] = $this->_formatTearsheet($ts);
                    }
                    $this->_sendSuccess([
                        'total' => count($formatted),
                        'data' => $formatted
                    ]);
                }
                break;

            case 'POST':
                // Create new tearsheet
                $input = $this->_getRequestBody();

                if (empty($input['name'])) {
                    $this->_sendError('Missing required field: name', 400);
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
                    $this->_sendError('Failed to create tearsheet', 500);
                    return;
                }

                $newTearsheet = $tearsheets->get($tearsheetID);
                $this->_sendSuccess($this->_formatTearsheet($newTearsheet), 201);
                break;

            case 'PUT':
                // Update existing tearsheet
                if (!$id) {
                    $this->_sendError('Tearsheet ID required for update', 400);
                    return;
                }

                $existing = $tearsheets->get($id);
                if (!$existing) {
                    $this->_sendError('Tearsheet not found', 404);
                    return;
                }

                $input = $this->_getRequestBody();

                $name = isset($input['name']) ? $input['name'] : $existing['name'];
                $description = isset($input['description']) ? $input['description'] : $existing['description'];
                $isPublic = isset($input['isPublic']) ? (bool)$input['isPublic'] : (bool)$existing['is_public'];

                $success = $tearsheets->update($id, $name, $description, $isPublic);

                if (!$success) {
                    $this->_sendError('Failed to update tearsheet', 500);
                    return;
                }

                $updated = $tearsheets->get($id);
                $this->_sendSuccess($this->_formatTearsheet($updated));
                break;

            case 'DELETE':
                // Delete tearsheet
                if (!$id) {
                    $this->_sendError('Tearsheet ID required for delete', 400);
                    return;
                }

                $existing = $tearsheets->get($id);
                if (!$existing) {
                    $this->_sendError('Tearsheet not found', 404);
                    return;
                }

                $success = $tearsheets->delete($id);

                if (!$success) {
                    $this->_sendError('Failed to delete tearsheet', 500);
                    return;
                }

                $this->_sendSuccess([
                    'message' => 'Tearsheet deleted successfully',
                    'id' => $id
                ]);
                break;

            default:
                $this->_sendError('Method not allowed', 405);
        }
    }

    /**
     * Handle adding jobs to a tearsheet
     */
    private function _handleTearsheetAddJobs($tearsheets, $tearsheetID)
    {
        $existing = $tearsheets->get($tearsheetID);
        if (!$existing) {
            $this->_sendError('Tearsheet not found', 404);
            return;
        }

        $input = $this->_getRequestBody();

        if (empty($input['jobOrderIds']) || !is_array($input['jobOrderIds'])) {
            $this->_sendError('Missing required field: jobOrderIds (array)', 400);
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

        $this->_sendSuccess([
            'tearsheetId' => $tearsheetID,
            'added' => $added,
            'failed' => $failed,
            'message' => $added . ' job order(s) added to tearsheet'
        ]);
    }

    /**
     * Handle removing jobs from a tearsheet
     */
    private function _handleTearsheetRemoveJobs($tearsheets, $tearsheetID)
    {
        $existing = $tearsheets->get($tearsheetID);
        if (!$existing) {
            $this->_sendError('Tearsheet not found', 404);
            return;
        }

        // Support both JSON body and query parameter
        $input = $this->_getRequestBody();
        $jobIds = [];

        if (!empty($input['jobOrderIds'])) {
            $jobIds = $input['jobOrderIds'];
        } elseif (!empty($_GET['jobOrderIds'])) {
            $jobIds = explode(',', $_GET['jobOrderIds']);
        }

        if (empty($jobIds)) {
            $this->_sendError('Missing required: jobOrderIds', 400);
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

        $this->_sendSuccess([
            'tearsheetId' => $tearsheetID,
            'removed' => $removed,
            'failed' => $failed,
            'message' => $removed . ' job order(s) removed from tearsheet'
        ]);
    }

    /**
     * Handle candidates endpoint
     * Supports: GET (list/single), POST (create), PUT (update), DELETE
     */
    private function _handleCandidates()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $method = $_SERVER['REQUEST_METHOD'];

        $candidates = new Candidates($this->_siteID);

        switch ($method) {
            case 'GET':
                if ($id) {
                    $candidate = $candidates->get($id);
                    if ($candidate && is_array($candidate) && count($candidate) > 0) {
                        $this->_sendSuccess($this->_formatCandidate($candidate));
                    } else {
                        $this->_sendError('Candidate not found', 404);
                    }
                } else {
                    // Get all candidates with optional filtering
                    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                    $skills = isset($_GET['skills']) ? trim($_GET['skills']) : '';
                    $isHot = isset($_GET['isHot']) ? filter_var($_GET['isHot'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;

                    // Pagination
                    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 25;

                    // Get all candidates
                    $allCandidates = $candidates->getAll(false);

                    $filtered = [];
                    if (is_array($allCandidates)) {
                        foreach ($allCandidates as $row) {
                            // Apply filters
                            if (!empty($search)) {
                                $nameMatch = stripos(($row['firstName'] ?? '') . ' ' . ($row['lastName'] ?? ''), $search) !== false;
                                $emailMatch = stripos($row['email1'] ?? '', $search) !== false;
                                $skillsMatch = stripos($row['keySkills'] ?? '', $search) !== false;
                                if (!$nameMatch && !$emailMatch && !$skillsMatch) continue;
                            }
                            if (!empty($skills) && stripos($row['keySkills'] ?? '', $skills) === false) continue;
                            if ($isHot !== null && (bool)($row['isHot'] ?? 0) !== $isHot) continue;

                            $filtered[] = $this->_formatCandidate($row);
                        }
                    }

                    // Apply pagination
                    $total = count($filtered);
                    $offset = ($page - 1) * $limit;
                    $pagedCandidates = array_slice($filtered, $offset, $limit);

                    $this->_sendSuccess([
                        'total' => $total,
                        'page' => $page,
                        'limit' => $limit,
                        'data' => $pagedCandidates
                    ]);
                }
                break;

            case 'POST':
                // Create new candidate
                $input = $this->_getRequestBody();

                if (empty($input['firstName']) || empty($input['lastName'])) {
                    $this->_sendError('Missing required fields: firstName and lastName', 400);
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
                    $firstName,
                    $middleName,
                    $lastName,
                    $email1,
                    $email2,
                    $phoneHome,
                    $phoneCell,
                    $phoneWork,
                    $address,
                    $city,
                    $state,
                    $zip,
                    $source,
                    $keySkills,
                    $dateAvailable,
                    $currentEmployer,
                    $canRelocate,
                    $currentPay,
                    $desiredPay,
                    $notes,
                    $webSite,
                    $bestTimeToCall,
                    $this->_userID,
                    $owner
                );

                if ($candidateID <= 0) {
                    $this->_sendError('Failed to create candidate', 500);
                    return;
                }

                $newCandidate = $candidates->get($candidateID);
                $this->_sendSuccess($this->_formatCandidate($newCandidate), 201);
                break;

            case 'PUT':
                // Update existing candidate
                if (!$id) {
                    $this->_sendError('Candidate ID required for update', 400);
                    return;
                }

                $existing = $candidates->get($id);
                if (!$existing || empty($existing['candidate_id'])) {
                    $this->_sendError('Candidate not found', 404);
                    return;
                }

                $input = $this->_getRequestBody();

                // Merge input with existing values
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
                    $id,
                    $isActive,
                    $firstName,
                    $middleName,
                    $lastName,
                    $email1,
                    $email2,
                    $phoneHome,
                    $phoneCell,
                    $phoneWork,
                    $address,
                    $city,
                    $state,
                    $zip,
                    $source,
                    $keySkills,
                    $dateAvailable,
                    $currentEmployer,
                    $canRelocate,
                    $currentPay,
                    $desiredPay,
                    $notes,
                    $webSite,
                    $bestTimeToCall,
                    $owner,
                    $isHot,
                    $email,
                    $emailAddress
                );

                if (!$success) {
                    $this->_sendError('Failed to update candidate', 500);
                    return;
                }

                $updated = $candidates->get($id);
                $this->_sendSuccess($this->_formatCandidate($updated));
                break;

            case 'DELETE':
                // Delete candidate
                if (!$id) {
                    $this->_sendError('Candidate ID required for delete', 400);
                    return;
                }

                $existing = $candidates->get($id);
                if (!$existing || empty($existing['candidate_id'])) {
                    $this->_sendError('Candidate not found', 404);
                    return;
                }

                $success = $candidates->delete($id);

                if (!$success) {
                    $this->_sendError('Failed to delete candidate', 500);
                    return;
                }

                $this->_sendSuccess([
                    'message' => 'Candidate deleted successfully',
                    'id' => $id
                ]);
                break;

            default:
                $this->_sendError('Method not allowed', 405);
        }
    }

    /**
     * Handle companies endpoint
     * Supports: GET (list/single with search and pagination)
     */
    private function _handleCompanies()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $method = $_SERVER['REQUEST_METHOD'];

        $companies = new Companies($this->_siteID);

        switch ($method) {
            case 'GET':
                if ($id) {
                    $company = $companies->get($id);
                    if ($company && is_array($company) && count($company) > 0) {
                        $this->_sendSuccess($this->_formatCompany($company));
                    } else {
                        $this->_sendError('Company not found', 404);
                    }
                } else {
                    // Get all companies with optional filtering
                    $search = isset($_GET['search']) ? trim($_GET['search']) : '';
                    $city = isset($_GET['city']) ? trim($_GET['city']) : '';
                    $state = isset($_GET['state']) ? trim($_GET['state']) : '';
                    $isHot = isset($_GET['isHot']) ? filter_var($_GET['isHot'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) : null;

                    // Pagination
                    $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
                    $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 25;

                    // Get all companies
                    $allCompanies = $companies->getAll();

                    $filtered = [];
                    if (is_array($allCompanies)) {
                        foreach ($allCompanies as $row) {
                            // Apply filters
                            if (!empty($search)) {
                                $nameMatch = stripos($row['name'] ?? '', $search) !== false;
                                if (!$nameMatch) continue;
                            }
                            if (!empty($city) && stripos($row['city'] ?? '', $city) === false) continue;
                            if (!empty($state) && ($row['state'] ?? '') !== $state) continue;
                            if ($isHot !== null && (bool)($row['isHot'] ?? 0) !== $isHot) continue;

                            $filtered[] = $this->_formatCompany($row);
                        }
                    }

                    // Apply pagination
                    $total = count($filtered);
                    $offset = ($page - 1) * $limit;
                    $pagedCompanies = array_slice($filtered, $offset, $limit);

                    $this->_sendSuccess([
                        'total' => $total,
                        'page' => $page,
                        'limit' => $limit,
                        'data' => $pagedCompanies
                    ]);
                }
                break;

            case 'POST':
                // Create new company
                $input = $this->_getRequestBody();

                if (empty($input['name'])) {
                    $this->_sendError('Missing required field: name', 400);
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
                    $name,
                    $address,
                    $city,
                    $state,
                    $zip,
                    $phone1,
                    $phone2,
                    $faxNumber,
                    $url,
                    $keyTechnologies,
                    $isHot,
                    $notes,
                    $this->_userID,
                    $owner
                );

                if ($companyID <= 0) {
                    $this->_sendError('Failed to create company', 500);
                    return;
                }

                $newCompany = $companies->get($companyID);
                $this->_sendSuccess($this->_formatCompany($newCompany), 201);
                break;

            case 'PUT':
                // Update existing company
                if (!$id) {
                    $this->_sendError('Company ID required for update', 400);
                    return;
                }

                $existing = $companies->get($id);
                if (!$existing || empty($existing['company_id'])) {
                    $this->_sendError('Company not found', 404);
                    return;
                }

                $input = $this->_getRequestBody();

                // Merge input with existing values
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
                    $id,
                    $name,
                    $address,
                    $city,
                    $state,
                    $zip,
                    $phone1,
                    $phone2,
                    $faxNumber,
                    $url,
                    $keyTechnologies,
                    $isHot,
                    $notes,
                    $owner,
                    $billingContact,
                    $email,
                    $emailAddress
                );

                if (!$success) {
                    $this->_sendError('Failed to update company', 500);
                    return;
                }

                $updated = $companies->get($id);
                $this->_sendSuccess($this->_formatCompany($updated));
                break;

            case 'DELETE':
                // Delete company
                if (!$id) {
                    $this->_sendError('Company ID required for delete', 400);
                    return;
                }

                $existing = $companies->get($id);
                if (!$existing || empty($existing['company_id'])) {
                    $this->_sendError('Company not found', 404);
                    return;
                }

                $success = $companies->delete($id);

                if (!$success) {
                    $this->_sendError('Failed to delete company', 500);
                    return;
                }

                $this->_sendSuccess([
                    'message' => 'Company deleted successfully',
                    'id' => $id
                ]);
                break;

            default:
                $this->_sendError('Method not allowed', 405);
        }
    }

    /**
     * Handle contacts endpoint (Bullhorn ClientContact equivalent)
     * Supports: GET (list/single with search and pagination)
     */
    private function _handleContacts()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $method = $_SERVER['REQUEST_METHOD'];

        if ($method !== 'GET') {
            $this->_sendError('Method not allowed. Only GET is currently supported for contacts.', 405);
            return;
        }

        $contacts = new Contacts($this->_siteID);

        if ($id) {
            $contact = $contacts->get($id);
            if ($contact && is_array($contact) && count($contact) > 0) {
                $this->_sendSuccess($this->_formatContact($contact));
            } else {
                $this->_sendError('Contact not found', 404);
            }
        } else {
            // Get all contacts with optional filtering
            $search = isset($_GET['search']) ? trim($_GET['search']) : '';
            $companyID = isset($_GET['clientCorporation']) ? intval($_GET['clientCorporation']) : null;

            // Pagination
            $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
            $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 25;

            // Get all contacts
            $allContacts = $contacts->getAll();

            $filtered = [];
            if (is_array($allContacts)) {
                foreach ($allContacts as $row) {
                    // Apply filters
                    if (!empty($search)) {
                        $nameMatch = stripos(($row['firstName'] ?? '') . ' ' . ($row['lastName'] ?? ''), $search) !== false;
                        $emailMatch = stripos($row['email1'] ?? '', $search) !== false;
                        if (!$nameMatch && !$emailMatch) continue;
                    }
                    if ($companyID !== null && intval($row['companyID'] ?? 0) !== $companyID) continue;

                    $filtered[] = $this->_formatContact($row);
                }
            }

            // Apply pagination
            $total = count($filtered);
            $offset = ($page - 1) * $limit;
            $pagedContacts = array_slice($filtered, $offset, $limit);

            $this->_sendSuccess([
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'data' => $pagedContacts
            ]);
        }
    }

    /**
     * Handle meta endpoint for entity schema discovery
     * Follows Bullhorn /meta pattern
     */
    private function _handleMeta()
    {
        $entity = isset($_GET['entity']) ? strtolower(trim($_GET['entity'])) : '';

        // Entity schemas
        $entitySchemas = [
            'joborder' => [
                'entity' => 'JobOrder',
                'label' => 'Job Order',
                'fields' => [
                    ['name' => 'id', 'type' => 'Integer', 'label' => 'ID', 'readOnly' => true],
                    ['name' => 'title', 'type' => 'String', 'label' => 'Title', 'required' => true, 'maxLength' => 255],
                    ['name' => 'description', 'type' => 'Text', 'label' => 'Description', 'required' => false],
                    ['name' => 'publicDescription', 'type' => 'Text', 'label' => 'Public Description', 'required' => false],
                    ['name' => 'status', 'type' => 'String', 'label' => 'Status', 'required' => false, 'options' => ['Active', 'On Hold', 'Closed', 'Filled']],
                    ['name' => 'isOpen', 'type' => 'Boolean', 'label' => 'Is Open', 'required' => false],
                    ['name' => 'isPublic', 'type' => 'Boolean', 'label' => 'Is Public', 'required' => false],
                    ['name' => 'companyID', 'type' => 'Integer', 'label' => 'Company ID', 'associatedEntity' => 'Company', 'required' => true],
                    ['name' => 'contactID', 'type' => 'Integer', 'label' => 'Contact ID', 'associatedEntity' => 'Contact', 'required' => false],
                    ['name' => 'ownerID', 'type' => 'Integer', 'label' => 'Owner ID', 'associatedEntity' => 'User', 'required' => false],
                    ['name' => 'recruiterID', 'type' => 'Integer', 'label' => 'Recruiter ID', 'associatedEntity' => 'User', 'required' => false],
                    ['name' => 'salary', 'type' => 'String', 'label' => 'Salary', 'required' => false],
                    ['name' => 'type', 'type' => 'String', 'label' => 'Employment Type', 'required' => false, 'options' => ['H', 'C2C', 'FL', 'PT']],
                    ['name' => 'city', 'type' => 'String', 'label' => 'City', 'required' => false, 'maxLength' => 64],
                    ['name' => 'state', 'type' => 'String', 'label' => 'State', 'required' => false, 'maxLength' => 64],
                    ['name' => 'openings', 'type' => 'Integer', 'label' => 'Openings', 'required' => false, 'default' => 1],
                    ['name' => 'startDate', 'type' => 'Date', 'label' => 'Start Date', 'required' => false],
                    ['name' => 'duration', 'type' => 'String', 'label' => 'Duration', 'required' => false],
                    ['name' => 'maxRate', 'type' => 'String', 'label' => 'Max Rate', 'required' => false],
                    ['name' => 'notes', 'type' => 'Text', 'label' => 'Notes', 'required' => false],
                    ['name' => 'isHot', 'type' => 'Boolean', 'label' => 'Is Hot', 'required' => false],
                    ['name' => 'dateAdded', 'type' => 'DateTime', 'label' => 'Date Added', 'readOnly' => true],
                    ['name' => 'dateLastModified', 'type' => 'DateTime', 'label' => 'Date Modified', 'readOnly' => true]
                ]
            ],
            'tearsheet' => [
                'entity' => 'Tearsheet',
                'label' => 'Tearsheet',
                'fields' => [
                    ['name' => 'id', 'type' => 'Integer', 'label' => 'ID', 'readOnly' => true],
                    ['name' => 'name', 'type' => 'String', 'label' => 'Name', 'required' => true, 'maxLength' => 128],
                    ['name' => 'description', 'type' => 'Text', 'label' => 'Description', 'required' => false],
                    ['name' => 'isPublic', 'type' => 'Boolean', 'label' => 'Is Public', 'required' => false],
                    ['name' => 'owner', 'type' => 'Association', 'label' => 'Owner', 'associatedEntity' => 'User', 'readOnly' => true],
                    ['name' => 'dateCreated', 'type' => 'DateTime', 'label' => 'Date Created', 'readOnly' => true],
                    ['name' => 'jobOrders', 'type' => 'ToMany', 'label' => 'Job Orders', 'associatedEntity' => 'JobOrder']
                ]
            ],
            'candidate' => [
                'entity' => 'Candidate',
                'label' => 'Candidate',
                'fields' => [
                    ['name' => 'id', 'type' => 'Integer', 'label' => 'ID', 'readOnly' => true],
                    ['name' => 'firstName', 'type' => 'String', 'label' => 'First Name', 'required' => true, 'maxLength' => 64],
                    ['name' => 'middleName', 'type' => 'String', 'label' => 'Middle Name', 'required' => false, 'maxLength' => 64],
                    ['name' => 'lastName', 'type' => 'String', 'label' => 'Last Name', 'required' => true, 'maxLength' => 64],
                    ['name' => 'email1', 'type' => 'String', 'label' => 'Email', 'required' => false, 'maxLength' => 128],
                    ['name' => 'email2', 'type' => 'String', 'label' => 'Email 2', 'required' => false, 'maxLength' => 128],
                    ['name' => 'phone', 'type' => 'String', 'label' => 'Phone (Home)', 'required' => false, 'maxLength' => 32],
                    ['name' => 'phoneCell', 'type' => 'String', 'label' => 'Phone (Cell)', 'required' => false, 'maxLength' => 32],
                    ['name' => 'phoneWork', 'type' => 'String', 'label' => 'Phone (Work)', 'required' => false, 'maxLength' => 32],
                    ['name' => 'address', 'type' => 'String', 'label' => 'Address', 'required' => false],
                    ['name' => 'city', 'type' => 'String', 'label' => 'City', 'required' => false, 'maxLength' => 64],
                    ['name' => 'state', 'type' => 'String', 'label' => 'State', 'required' => false, 'maxLength' => 64],
                    ['name' => 'zip', 'type' => 'String', 'label' => 'Zip', 'required' => false, 'maxLength' => 16],
                    ['name' => 'source', 'type' => 'String', 'label' => 'Source', 'required' => false],
                    ['name' => 'keySkills', 'type' => 'Text', 'label' => 'Key Skills', 'required' => false],
                    ['name' => 'currentEmployer', 'type' => 'String', 'label' => 'Current Employer', 'required' => false],
                    ['name' => 'canRelocate', 'type' => 'Boolean', 'label' => 'Can Relocate', 'required' => false],
                    ['name' => 'isHot', 'type' => 'Boolean', 'label' => 'Is Hot', 'required' => false],
                    ['name' => 'ownerID', 'type' => 'Integer', 'label' => 'Owner ID', 'associatedEntity' => 'User'],
                    ['name' => 'dateAdded', 'type' => 'DateTime', 'label' => 'Date Added', 'readOnly' => true],
                    ['name' => 'dateLastModified', 'type' => 'DateTime', 'label' => 'Date Modified', 'readOnly' => true]
                ]
            ],
            'company' => [
                'entity' => 'Company',
                'label' => 'Company (Client Corporation)',
                'fields' => [
                    ['name' => 'id', 'type' => 'Integer', 'label' => 'ID', 'readOnly' => true],
                    ['name' => 'name', 'type' => 'String', 'label' => 'Name', 'required' => true, 'maxLength' => 128],
                    ['name' => 'address', 'type' => 'String', 'label' => 'Address', 'required' => false],
                    ['name' => 'city', 'type' => 'String', 'label' => 'City', 'required' => false, 'maxLength' => 64],
                    ['name' => 'state', 'type' => 'String', 'label' => 'State', 'required' => false, 'maxLength' => 64],
                    ['name' => 'zip', 'type' => 'String', 'label' => 'Zip', 'required' => false, 'maxLength' => 16],
                    ['name' => 'phone', 'type' => 'String', 'label' => 'Phone', 'required' => false, 'maxLength' => 32],
                    ['name' => 'phone2', 'type' => 'String', 'label' => 'Phone 2', 'required' => false, 'maxLength' => 32],
                    ['name' => 'fax', 'type' => 'String', 'label' => 'Fax', 'required' => false, 'maxLength' => 32],
                    ['name' => 'url', 'type' => 'String', 'label' => 'Website', 'required' => false],
                    ['name' => 'keyTechnologies', 'type' => 'Text', 'label' => 'Key Technologies', 'required' => false],
                    ['name' => 'notes', 'type' => 'Text', 'label' => 'Notes', 'required' => false],
                    ['name' => 'isHot', 'type' => 'Boolean', 'label' => 'Is Hot', 'required' => false],
                    ['name' => 'ownerID', 'type' => 'Integer', 'label' => 'Owner ID', 'associatedEntity' => 'User'],
                    ['name' => 'dateAdded', 'type' => 'DateTime', 'label' => 'Date Added', 'readOnly' => true],
                    ['name' => 'dateLastModified', 'type' => 'DateTime', 'label' => 'Date Modified', 'readOnly' => true]
                ]
            ],
            'contact' => [
                'entity' => 'Contact',
                'label' => 'Contact (Client Contact)',
                'fields' => [
                    ['name' => 'id', 'type' => 'Integer', 'label' => 'ID', 'readOnly' => true],
                    ['name' => 'firstName', 'type' => 'String', 'label' => 'First Name', 'required' => true, 'maxLength' => 64],
                    ['name' => 'lastName', 'type' => 'String', 'label' => 'Last Name', 'required' => true, 'maxLength' => 64],
                    ['name' => 'title', 'type' => 'String', 'label' => 'Title', 'required' => false, 'maxLength' => 64],
                    ['name' => 'email1', 'type' => 'String', 'label' => 'Email', 'required' => false, 'maxLength' => 128],
                    ['name' => 'email2', 'type' => 'String', 'label' => 'Email 2', 'required' => false, 'maxLength' => 128],
                    ['name' => 'phone', 'type' => 'String', 'label' => 'Phone (Work)', 'required' => false, 'maxLength' => 32],
                    ['name' => 'phoneCell', 'type' => 'String', 'label' => 'Phone (Cell)', 'required' => false, 'maxLength' => 32],
                    ['name' => 'clientCorporation', 'type' => 'Integer', 'label' => 'Company ID', 'associatedEntity' => 'Company', 'required' => true],
                    ['name' => 'isHot', 'type' => 'Boolean', 'label' => 'Is Hot', 'required' => false],
                    ['name' => 'notes', 'type' => 'Text', 'label' => 'Notes', 'required' => false],
                    ['name' => 'ownerID', 'type' => 'Integer', 'label' => 'Owner ID', 'associatedEntity' => 'User'],
                    ['name' => 'dateAdded', 'type' => 'DateTime', 'label' => 'Date Added', 'readOnly' => true]
                ]
            ]
        ];

        if (empty($entity)) {
            // Return list of available entities
            $entities = [];
            foreach ($entitySchemas as $key => $schema) {
                $entities[] = [
                    'name' => $schema['entity'],
                    'label' => $schema['label'],
                    'endpoint' => '?m=api&a=' . $key . 's'
                ];
            }
            $this->_sendSuccess(['entities' => $entities]);
            return;
        }

        // Remove trailing 's' if present (joborders -> joborder)
        $entity = rtrim($entity, 's');

        if (!isset($entitySchemas[$entity])) {
            // Sanitize entity to prevent XSS in error response
            $safeEntity = htmlspecialchars($entity, ENT_QUOTES, 'UTF-8');
            $this->_sendError('Entity not found: ' . $safeEntity, 404);
            return;
        }

        $this->_sendSuccess($entitySchemas[$entity]);
    }

    // =========================================
    // Data Formatting (Bullhorn-compatible)
    // =========================================

    /**
     * Format job order for API response
     */
    private function _formatJobOrder($job)
    {
        return [
            'id' => intval($job['jobOrderID'] ?? $job['joborder_id'] ?? 0),
            'title' => $job['title'] ?? '',
            'description' => $job['description'] ?? '',
            'publicDescription' => $job['public_description'] ?? $job['description'] ?? '',
            'status' => $job['status'] ?? '',
            'isOpen' => ($job['status'] ?? '') === 'Active',
            'isPublic' => (bool)($job['is_public'] ?? $job['public'] ?? 0),
            'dateAdded' => $job['dateCreated'] ?? $job['date_created'] ?? '',
            'dateLastModified' => $job['dateModified'] ?? $job['date_modified'] ?? '',
            'address' => [
                'city' => $job['city'] ?? '',
                'state' => $job['state'] ?? '',
                'zip' => $job['zip'] ?? '',
                'country' => $job['country'] ?? ''
            ],
            'salary' => $job['salary'] ?? $job['rate_max'] ?? '',
            'type' => $job['type'] ?? $job['duration'] ?? '',
            'clientCorporation' => [
                'id' => intval($job['companyID'] ?? $job['company_id'] ?? 0),
                'name' => $job['companyName'] ?? $job['company_name'] ?? ''
            ],
            'owner' => [
                'id' => intval($job['recruiterID'] ?? $job['recruiter'] ?? 0),
                'firstName' => $job['recruiterFirstName'] ?? $job['recruiter_first_name'] ?? '',
                'lastName' => $job['recruiterLastName'] ?? $job['recruiter_last_name'] ?? ''
            ],
            'openings' => intval($job['openings'] ?? 1),
            'startDate' => $job['startDate'] ?? $job['start_date'] ?? ''
        ];
    }

    /**
     * Format tearsheet for API response
     */
    private function _formatTearsheet($ts)
    {
        return [
            'id' => intval($ts['tearsheet_id'] ?? 0),
            'name' => $ts['name'] ?? '',
            'description' => $ts['description'] ?? '',
            'isPublic' => (bool)($ts['is_public'] ?? 0),
            'dateCreated' => $ts['date_created'] ?? '',
            'jobOrders' => [
                'total' => intval($ts['job_count'] ?? 0)
            ],
            'owner' => [
                'id' => intval($ts['user_id'] ?? 0)
            ]
        ];
    }

    /**
     * Format candidate for API response
     */
    private function _formatCandidate($candidate)
    {
        return [
            'id' => intval($candidate['candidateID'] ?? $candidate['candidate_id'] ?? 0),
            'firstName' => $candidate['firstName'] ?? $candidate['first_name'] ?? '',
            'lastName' => $candidate['lastName'] ?? $candidate['last_name'] ?? '',
            'email' => $candidate['email1'] ?? $candidate['email'] ?? '',
            'phone' => $candidate['phoneHome'] ?? $candidate['phone_home'] ?? '',
            'status' => $candidate['status'] ?? '',
            'dateAdded' => $candidate['dateCreated'] ?? $candidate['date_created'] ?? ''
        ];
    }

    /**
     * Format company for API response
     */
    private function _formatCompany($company)
    {
        return [
            'id' => intval($company['companyID'] ?? $company['company_id'] ?? 0),
            'name' => $company['name'] ?? '',
            'address' => [
                'address1' => $company['address'] ?? '',
                'city' => $company['city'] ?? '',
                'state' => $company['state'] ?? '',
                'zip' => $company['zip'] ?? ''
            ],
            'phone' => $company['phone1'] ?? $company['phone'] ?? '',
            'website' => $company['url'] ?? ''
        ];
    }

    /**
     * Format contact for API response (Bullhorn ClientContact equivalent)
     */
    private function _formatContact($contact)
    {
        return [
            'id' => intval($contact['contactID'] ?? $contact['contact_id'] ?? 0),
            'firstName' => $contact['firstName'] ?? $contact['first_name'] ?? '',
            'lastName' => $contact['lastName'] ?? $contact['last_name'] ?? '',
            'title' => $contact['title'] ?? '',
            'email1' => $contact['email1'] ?? '',
            'email2' => $contact['email2'] ?? '',
            'phone' => $contact['phoneWork'] ?? $contact['phone_work'] ?? '',
            'phoneCell' => $contact['phoneCell'] ?? $contact['phone_cell'] ?? '',
            'address' => [
                'address1' => $contact['address'] ?? '',
                'city' => $contact['city'] ?? '',
                'state' => $contact['state'] ?? '',
                'zip' => $contact['zip'] ?? ''
            ],
            'clientCorporation' => [
                'id' => intval($contact['companyID'] ?? $contact['company_id'] ?? 0),
                'name' => $contact['companyName'] ?? $contact['company_name'] ?? ''
            ],
            'isHot' => (bool)($contact['isHot'] ?? $contact['is_hot'] ?? 0),
            'notes' => $contact['notes'] ?? '',
            'owner' => [
                'id' => intval($contact['owner'] ?? 0)
            ],
            'dateAdded' => $contact['dateCreated'] ?? $contact['date_created'] ?? ''
        ];
    }

    // =========================================
    // Helper Methods
    // =========================================

    /**
     * Get request headers (works on all servers)
     */
    private function _getRequestHeaders()
    {
        if (function_exists('getallheaders')) {
            return getallheaders();
        }

        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) === 'HTTP_') {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))));
                $headers[$headerName] = $value;
            }
        }
        return $headers;
    }

    /**
     * Get JSON request body
     */
    private function _getRequestBody()
    {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?: [];
    }

    /**
     * Send success response
     */
    private function _sendSuccess($data, $code = 200)
    {
        // Log successful request
        if ($this->_requestLogger) {
            $this->_requestLogger->logSuccess($code);
        }

        http_response_code($code);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send error response
     */
    private function _sendError($message, $code = 400)
    {
        // Log failed request
        if ($this->_requestLogger) {
            $this->_requestLogger->logError($code, $message);
        }

        http_response_code($code);
        echo json_encode([
            'error' => true,
            'message' => $message,
            'code' => $code
        ], JSON_PRETTY_PRINT);
        exit;
    }
}
