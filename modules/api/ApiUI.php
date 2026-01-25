<?php
/**
 * OpenCATS REST API Module
 *
 * Provides RESTful API access to OpenCATS data.
 * Designed to be compatible with Bullhorn API patterns.
 *
 * @package    OpenCATS
 * @subpackage API
 * @license    CPAL-1.0
 * @version    1.0.0
 */

include_once('./lib/JobOrders.php');
include_once('./lib/Candidates.php');
include_once('./lib/Companies.php');

// Include new libraries (create these)
if (file_exists('./lib/Tearsheets.php')) {
    include_once('./lib/Tearsheets.php');
}
if (file_exists('./lib/ApiKeys.php')) {
    include_once('./lib/ApiKeys.php');
}

class ApiUI extends UserInterface
{
    // $_siteID and $_userID are inherited from UserInterface (protected)
    protected $_accessLevel = 0;
    private $_authenticated = false;

    public function __construct()
    {
        parent::__construct();
        $this->_moduleDirectory = 'api';
        $this->_moduleName = 'api';
        $this->_siteID = CATS_ADMIN_SITE;
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
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Api-Key');

        // Handle CORS preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }

        $action = $this->getAction();

        // Auth endpoint doesn't require authentication
        if ($action !== 'auth' && $action !== 'ping') {
            if (!$this->_authenticate()) {
                $this->_sendError('Unauthorized. Provide valid API key.', 401);
                return;
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

            default:
                $this->_sendError('Unknown endpoint: ' . $action, 404);
        }
    }

    /**
     * Simple ping endpoint for health checks
     */
    private function _handlePing()
    {
        $this->_sendSuccess([
            'status' => 'ok',
            'version' => '1.0.0',
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

        // For development/testing: accept a hardcoded dev key
        // In production, validate against api_keys table
        if ($apiKey === 'dev-test-key-12345') {
            $this->_authenticated = true;
            $this->_userID = 1;
            $this->_accessLevel = ACCESS_LEVEL_SA;
            return true;
        }

        // Check database for API key
        if (class_exists('ApiKeys')) {
            $apiKeys = new ApiKeys($this->_siteID);
            $result = $apiKeys->validate($apiKey);
            if ($result) {
                $this->_authenticated = true;
                $this->_userID = $result['user_id'];
                $this->_accessLevel = $result['access_level'];
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

        // For development: simple validation
        if ($input['api_key'] === 'dev-test-key-12345' && 
            $input['api_secret'] === 'dev-test-secret') {
            $this->_sendSuccess([
                'access_token' => 'dev-test-key-12345',
                'token_type' => 'Bearer',
                'expires_in' => 3600,
                'refresh_token' => 'dev-refresh-token'
            ]);
            return;
        }

        $this->_sendError('Invalid credentials', 401);
    }

    /**
     * Handle job orders endpoint
     */
    private function _handleJobOrders()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;

        $jobOrders = new JobOrders($this->_siteID);

        if ($id) {
            // GET single job order
            $job = $jobOrders->get($id);
            if ($job && is_array($job) && count($job) > 0) {
                $this->_sendSuccess($this->_formatJobOrder($job));
            } else {
                $this->_sendError('Job order not found', 404);
            }
        } else {
            // GET list of job orders - getAll() returns an array
            $jobsData = $jobOrders->getAll(
                JOBORDERS_STATUS_ALL,  // All job orders for authenticated user
                -1,  // No limit
                -1   // No offset
            );

            $jobs = [];
            if (is_array($jobsData)) {
                foreach ($jobsData as $row) {
                    $jobs[] = $this->_formatJobOrder($row);
                }
            }

            $this->_sendSuccess([
                'total' => count($jobs),
                'data' => $jobs
            ]);
        }
    }

    /**
     * Handle tearsheets endpoint
     */
    private function _handleTearsheets()
    {
        if (!class_exists('Tearsheets')) {
            $this->_sendError('Tearsheets module not installed', 501);
            return;
        }

        $id = isset($_GET['id']) ? intval($_GET['id']) : null;
        $subAction = isset($_GET['sub']) ? $_GET['sub'] : null;

        $tearsheets = new Tearsheets($this->_siteID);

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
    }

    /**
     * Handle candidates endpoint
     */
    private function _handleCandidates()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;

        $candidates = new Candidates($this->_siteID);

        if ($id) {
            $candidate = $candidates->get($id);
            if ($candidate && is_array($candidate) && count($candidate) > 0) {
                $this->_sendSuccess($this->_formatCandidate($candidate));
            } else {
                $this->_sendError('Candidate not found', 404);
            }
        } else {
            // For now, just return empty - implement search later
            $this->_sendSuccess([
                'total' => 0,
                'data' => [],
                'message' => 'Use search parameters to find candidates'
            ]);
        }
    }

    /**
     * Handle companies endpoint
     */
    private function _handleCompanies()
    {
        $id = isset($_GET['id']) ? intval($_GET['id']) : null;

        $companies = new Companies($this->_siteID);

        if ($id) {
            $company = $companies->get($id);
            if ($company && is_array($company) && count($company) > 0) {
                $this->_sendSuccess($this->_formatCompany($company));
            } else {
                $this->_sendError('Company not found', 404);
            }
        } else {
            $this->_sendSuccess([
                'total' => 0,
                'data' => [],
                'message' => 'Use search parameters to find companies'
            ]);
        }
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
        http_response_code($code);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send error response
     */
    private function _sendError($message, $code = 400)
    {
        http_response_code($code);
        echo json_encode([
            'error' => true,
            'message' => $message,
            'code' => $code
        ], JSON_PRETTY_PRINT);
        exit;
    }
}
