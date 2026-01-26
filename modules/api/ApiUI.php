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
include_once(dirname(__FILE__) . '/handlers/OAuthHandler.php');
include_once(dirname(__FILE__) . '/handlers/JobSubmissionHandler.php');
include_once(dirname(__FILE__) . '/handlers/PlacementHandler.php');
include_once(dirname(__FILE__) . '/handlers/NoteHandler.php');
include_once(dirname(__FILE__) . '/handlers/AppointmentHandler.php');
include_once(dirname(__FILE__) . '/handlers/TaskHandler.php');
include_once(dirname(__FILE__) . '/handlers/AttachmentHandler.php');
include_once(dirname(__FILE__) . '/handlers/MassUpdateHandler.php');
include_once(dirname(__FILE__) . '/handlers/AssociationHandler.php');
include_once(dirname(__FILE__) . '/handlers/SubscriptionHandler.php');
include_once(dirname(__FILE__) . '/traits/ApiHelpers.php');

class ApiUI extends UserInterface
{
    use ApiHelpers;

    protected $_accessLevel = 0;
    private $_authenticated = false;
    private $_apiKeyID = null;
    private $_authType = null;
    private $_rateLimiter = null;
    protected $_requestLogger = null;

    /**
     * Constructor
     *
     * Initializes the API module with default settings.
     */
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

    /**
     * Handle incoming API request
     *
     * Routes requests to appropriate handlers based on action.
     * Handles CORS, authentication, and rate limiting.
     *
     * @return void
     */
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

        // Auth and OAuth endpoints don't require authentication
        if ($action !== 'auth' && $action !== 'ping' && $action !== 'oauth') {
            if (!$this->_authenticate()) {
                $this->sendError('Unauthorized. Provide valid API key.', 401);
                return;
            }

            // Check rate limits after authentication (supports both API key and OAuth)
            $rateLimitIdentifier = $this->_apiKeyID ?: ($this->_authType === 'oauth' && $this->_userID ? 'oauth_user_' . $this->_userID : null);
            if (class_exists('ApiRateLimiter') && $rateLimitIdentifier) {
                $rateEnabled = !defined('API_RATE_LIMIT_ENABLED') || API_RATE_LIMIT_ENABLED;
                if ($rateEnabled) {
                    $ratePerMinute = defined('API_RATE_LIMIT_PER_MINUTE') ? API_RATE_LIMIT_PER_MINUTE : 60;
                    $ratePerHour = defined('API_RATE_LIMIT_PER_HOUR') ? API_RATE_LIMIT_PER_HOUR : 1000;

                    $this->_rateLimiter = new ApiRateLimiter($rateLimitIdentifier, $ratePerMinute, $ratePerHour);
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

            case 'oauth':
                // OAuth endpoints don't require prior auth
                $handler = new OAuthHandler($this->_requestLogger);
                $handler->handle();
                break;

            case 'jobsubmissions':
            case 'jobsubmission':
                $handler = new JobSubmissionHandler($this->_siteID, $this->_userID, $this->_requestLogger);
                $handler->handle();
                break;

            case 'placements':
            case 'placement':
                $handler = new PlacementHandler($this->_siteID, $this->_userID, $this->_requestLogger);
                $handler->handle();
                break;

            case 'notes':
            case 'note':
                $handler = new NoteHandler($this->_siteID, $this->_userID, $this->_requestLogger);
                $handler->handle();
                break;

            case 'appointments':
            case 'appointment':
                $handler = new AppointmentHandler($this->_siteID, $this->_userID, $this->_requestLogger);
                $handler->handle();
                break;

            case 'tasks':
            case 'task':
                $handler = new TaskHandler($this->_siteID, $this->_userID, $this->_requestLogger);
                $handler->handle();
                break;

            case 'attachments':
            case 'attachment':
                $handler = new AttachmentHandler($this->_siteID, $this->_userID, $this->_requestLogger);
                $handler->handle();
                break;

            case 'massupdate':
            case 'mass-update':
            case 'bulkupdate':
            case 'bulk-update':
                $handler = new MassUpdateHandler($this->_siteID, $this->_userID, $this->_requestLogger);
                $handler->handle();
                break;

            case 'associations':
            case 'association':
            case 'entitytomanyassociation':
                $handler = new AssociationHandler($this->_siteID, $this->_userID, $this->_requestLogger);
                $handler->handle();
                break;

            case 'subscriptions':
            case 'subscription':
            case 'webhooks':
            case 'webhook':
            case 'eventsubscription':
                $handler = new SubscriptionHandler($this->_siteID, $this->_userID, $this->_requestLogger);
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
     *
     * Supports both API Key authentication and OAuth 2.0 Bearer tokens.
     * Authentication methods tried in order:
     * 1. X-Api-Key header (API Key auth)
     * 2. Authorization: Bearer header (OAuth 2.0 or API Key)
     * 3. api_key query parameter (API Key auth)
     * 4. access_token query parameter (OAuth 2.0)
     */
    private function _authenticate()
    {
        // Check for API key in headers
        $headers = $this->getRequestHeaders();

        $apiKey = null;
        $bearerToken = null;

        // Try X-Api-Key header first (API Key auth)
        if (isset($headers['X-Api-Key'])) {
            $apiKey = $headers['X-Api-Key'];
        }

        // Try Authorization: Bearer header
        if (isset($headers['Authorization'])) {
            if (preg_match('/Bearer\s+(.+)/i', $headers['Authorization'], $matches)) {
                $bearerToken = $matches[1];
            }
        }

        // Try query parameters (less secure, for testing)
        if (isset($_GET['api_key'])) {
            $apiKey = $apiKey ?: $_GET['api_key'];
        }
        if (isset($_GET['access_token'])) {
            $bearerToken = $bearerToken ?: $_GET['access_token'];
        }

        // First try OAuth 2.0 Bearer token validation
        if ($bearerToken) {
            if ($this->_authenticateOAuth($bearerToken)) {
                return true;
            }
        }

        // Fall back to API Key authentication
        if ($apiKey) {
            if ($this->_authenticateApiKey($apiKey)) {
                return true;
            }
        }

        // If bearer token was provided but no API key, also try bearer as API key
        // This maintains backward compatibility where Bearer tokens were treated as API keys
        if ($bearerToken && !$apiKey) {
            if ($this->_authenticateApiKey($bearerToken)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Authenticate using OAuth 2.0 access token
     *
     * @param string $token The OAuth access token
     * @return bool True if authentication successful
     */
    private function _authenticateOAuth($token)
    {
        // Include OAuth2Server library if not already loaded
        if (!class_exists('OAuth2Server')) {
            $oauthPath = './lib/OAuth2Server.php';
            if (file_exists($oauthPath)) {
                include_once($oauthPath);
            } else {
                return false;
            }
        }

        if (!class_exists('OAuth2Server')) {
            return false;
        }

        try {
            $oauth = new OAuth2Server($this->_siteID);
            $result = $oauth->validateAccessToken($token);

            if ($result && isset($result['user_id'])) {
                $this->_authenticated = true;
                $this->_userID = $result['user_id'];
                $this->_authType = 'oauth';

                // OAuth tokens may have scope-based access levels
                // Default to full access if user_id is valid
                $this->_accessLevel = ACCESS_LEVEL_SA;

                // Update request logger
                if ($this->_requestLogger) {
                    $this->_requestLogger->setApiKeyID(null);
                }

                return true;
            }
        } catch (Exception $e) {
            // OAuth validation failed, continue to API key auth
            error_log('OAuth authentication error: ' . $e->getMessage());
        }

        return false;
    }

    /**
     * Authenticate using API Key
     *
     * @param string $apiKey The API key
     * @return bool True if authentication successful
     */
    private function _authenticateApiKey($apiKey)
    {
        if (!class_exists('ApiKeys')) {
            return false;
        }

        $apiKeys = new ApiKeys($this->_siteID);
        $result = $apiKeys->validate($apiKey);

        if ($result) {
            $this->_authenticated = true;
            $this->_userID = $result['user_id'];
            $this->_accessLevel = $result['access_level'];
            $this->_apiKeyID = $result['api_key_id'];
            $this->_authType = 'apikey';

            // Update request logger with authenticated API key
            if ($this->_requestLogger) {
                $this->_requestLogger->setApiKeyID($this->_apiKeyID);
            }

            return true;
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
