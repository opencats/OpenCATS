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

// Include API libraries
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

// Include handlers
include_once(dirname(__FILE__) . '/handlers/JobOrderHandler.php');
include_once(dirname(__FILE__) . '/handlers/TearsheetHandler.php');
include_once(dirname(__FILE__) . '/handlers/CandidateHandler.php');
include_once(dirname(__FILE__) . '/handlers/CompanyHandler.php');
include_once(dirname(__FILE__) . '/handlers/ContactHandler.php');
include_once(dirname(__FILE__) . '/handlers/MetaHandler.php');
include_once(dirname(__FILE__) . '/traits/ApiHelpers.php');

class ApiUI extends UserInterface
{
    use ApiHelpers;

    protected $_accessLevel = 0;
    private $_authenticated = false;
    private $_apiKeyID = null;
    private $_rateLimiter = null;
    protected $_requestLogger = null;

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
                $this->sendError('Unauthorized. Provide valid API key.', 401);
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
                        $this->sendError($limitInfo['reason'], 429);
                        return;
                    }
                }
            }
        }

        // Route requests to handlers
        $this->_routeRequest($action);
    }

    /**
     * Route request to appropriate handler
     */
    private function _routeRequest($action)
    {
        switch ($action) {
            case 'ping':
                $this->_handlePing();
                break;

            case 'auth':
                $this->_handleAuth();
                break;

            case 'joborders':
            case 'joborder':
                $handler = new JobOrderHandler($this->_siteID, $this->_userID, $this->_requestLogger);
                $handler->handle();
                break;

            case 'tearsheets':
            case 'tearsheet':
                $handler = new TearsheetHandler($this->_siteID, $this->_userID, $this->_requestLogger);
                $handler->handle();
                break;

            case 'candidates':
            case 'candidate':
                $handler = new CandidateHandler($this->_siteID, $this->_userID, $this->_requestLogger);
                $handler->handle();
                break;

            case 'companies':
            case 'company':
                $handler = new CompanyHandler($this->_siteID, $this->_userID, $this->_requestLogger);
                $handler->handle();
                break;

            case 'contacts':
            case 'contact':
                $handler = new ContactHandler($this->_siteID, $this->_userID, $this->_requestLogger);
                $handler->handle();
                break;

            case 'meta':
                $handler = new MetaHandler($this->_requestLogger);
                $handler->handle();
                break;

            default:
                // Sanitize action to prevent XSS in error response
                $safeAction = htmlspecialchars($action, ENT_QUOTES, 'UTF-8');
                $this->sendError('Unknown endpoint: ' . $safeAction, 404);
        }
    }

    /**
     * Simple ping endpoint for health checks
     */
    private function _handlePing()
    {
        $version = defined('API_VERSION') ? API_VERSION : '1.0.0';
        $this->sendSuccess([
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
        $headers = $this->getRequestHeaders();

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
            $this->sendError('Method not allowed. Use POST.', 405);
            return;
        }

        $input = $this->getRequestBody();

        if (!isset($input['api_key']) || !isset($input['api_secret'])) {
            $this->sendError('Missing api_key or api_secret', 400);
            return;
        }

        // Validate credentials against database
        if (class_exists('ApiKeys')) {
            $apiKeys = new ApiKeys($this->_siteID);
            $result = $apiKeys->authenticate($input['api_key'], $input['api_secret']);
            if ($result) {
                $this->sendSuccess([
                    'access_token' => $result['access_token'],
                    'token_type' => 'Bearer',
                    'expires_in' => $result['expires_in'] ?? 3600,
                    'refresh_token' => $result['refresh_token'] ?? null
                ]);
                return;
            }
        }

        $this->sendError('Invalid credentials', 401);
    }
}
