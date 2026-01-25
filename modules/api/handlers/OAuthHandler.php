<?php
/**
 * CATS
 * OAuth 2.0 Handler
 *
 * Handles OAuth 2.0 authentication endpoints for the REST API.
 * Implements authorization, token exchange, revocation, and client management.
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

include_once(dirname(__FILE__) . '/../traits/ApiHelpers.php');
include_once(dirname(__FILE__) . '/../../../lib/OAuth2Server.php');

class OAuthHandler
{
    use ApiHelpers;

    /**
     * @var OAuth2Server OAuth2Server instance
     */
    private $_oauth;

    /**
     * @var mixed Request logger (optional)
     */
    protected $_requestLogger;

    /**
     * Constructor
     *
     * @param mixed $requestLogger Optional request logger instance
     * @throws Exception If OAuth2Server cannot be instantiated
     */
    public function __construct($requestLogger = null)
    {
        $this->_requestLogger = $requestLogger;

        if (!class_exists('OAuth2Server')) {
            throw new Exception('OAuth2Server class not found. Check include path.');
        }

        try {
            $this->_oauth = new OAuth2Server();
        } catch (Exception $e) {
            throw new Exception('Failed to initialize OAuth2Server: ' . $e->getMessage());
        }
    }

    /**
     * Main handler - routes to appropriate endpoint based on $_GET['oauth']
     */
    public function handle()
    {
        $endpoint = isset($_GET['oauth']) ? strtolower($_GET['oauth']) : '';

        switch ($endpoint) {
            case 'authorize':
                $this->handleAuthorize();
                break;
            case 'token':
                $this->handleToken();
                break;
            case 'revoke':
                $this->handleRevoke();
                break;
            case 'clients':
                $this->handleClients();
                break;
            default:
                $this->sendError('Unknown OAuth endpoint', 404);
        }
    }

    /**
     * Handle GET /oauth/authorize
     *
     * Authorization endpoint - validates client and returns authorization info.
     * For API-first approach, returns info about the authorization request.
     *
     * Query Parameters:
     * - client_id (required): The OAuth client identifier
     * - redirect_uri (required): Where to redirect after authorization
     * - response_type (required): Must be 'code'
     * - scope (optional): Requested scopes (space-separated)
     * - state (optional): Client state to pass through
     */
    private function handleAuthorize()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->sendError('Method not allowed', 405);
            return;
        }

        // Get required parameters
        $clientId = isset($_GET['client_id']) ? trim($_GET['client_id']) : '';
        $redirectUri = isset($_GET['redirect_uri']) ? trim($_GET['redirect_uri']) : '';
        $responseType = isset($_GET['response_type']) ? trim($_GET['response_type']) : '';
        $scope = isset($_GET['scope']) ? trim($_GET['scope']) : '';
        $state = isset($_GET['state']) ? trim($_GET['state']) : '';

        // Validate response_type
        if ($responseType !== 'code') {
            $this->sendError('Invalid response_type. Only "code" is supported.', 400);
            return;
        }

        // Validate client_id is provided
        if (empty($clientId)) {
            $this->sendError('Missing required parameter: client_id', 400);
            return;
        }

        // Validate client exists
        $client = $this->_oauth->getClient($clientId);
        if (!$client) {
            $this->sendError('Invalid client_id', 400);
            return;
        }

        // Validate redirect_uri if provided in client registration
        if (!empty($client['redirect_uri']) && !empty($redirectUri)) {
            if ($client['redirect_uri'] !== $redirectUri) {
                $this->sendError('redirect_uri does not match registered URI', 400);
                return;
            }
        }

        // Parse and validate scopes
        $requestedScopes = !empty($scope) ? explode(' ', $scope) : [];
        $availableScopes = $this->_oauth->getAvailableScopes();

        foreach ($requestedScopes as $requestedScope) {
            if (!in_array($requestedScope, $availableScopes)) {
                $this->sendError('Invalid scope: ' . $requestedScope, 400);
                return;
            }
        }

        // Return authorization info (API-first approach)
        // In a web flow, this would render a consent form
        $this->sendSuccess([
            'authorization_request' => [
                'client_id' => $clientId,
                'client_name' => $client['client_name'],
                'redirect_uri' => $redirectUri ?: $client['redirect_uri'],
                'response_type' => $responseType,
                'scope' => $scope ?: 'default',
                'state' => $state,
                'available_scopes' => $availableScopes
            ],
            'message' => 'Authorization request is valid. Use POST /oauth/token with grant_type=authorization_code to exchange code for tokens.'
        ]);
    }

    /**
     * Handle POST /oauth/token
     *
     * Token endpoint - exchanges authorization codes or credentials for tokens.
     *
     * Supports both JSON and form-encoded input.
     *
     * Grant Types:
     * - authorization_code: Exchange auth code for tokens
     * - client_credentials: Direct client authentication (for confidential clients)
     * - refresh_token: Exchange refresh token for new access token
     */
    private function handleToken()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Method not allowed', 405);
            return;
        }

        // Support both JSON and form-encoded input
        $input = $this->parseTokenInput();

        $grantType = isset($input['grant_type']) ? $input['grant_type'] : '';

        if (empty($grantType)) {
            $this->sendOAuthError('invalid_request', 'Missing required parameter: grant_type');
            return;
        }

        switch ($grantType) {
            case 'authorization_code':
                $this->handleAuthorizationCodeGrant($input);
                break;
            case 'client_credentials':
                $this->handleClientCredentialsGrant($input);
                break;
            case 'refresh_token':
                $this->handleRefreshTokenGrant($input);
                break;
            default:
                $this->sendOAuthError('unsupported_grant_type', 'Grant type not supported: ' . $grantType);
        }
    }

    /**
     * Parse token request input (supports JSON and form-encoded)
     *
     * @return array Parsed input parameters
     */
    private function parseTokenInput()
    {
        $contentType = isset($_SERVER['CONTENT_TYPE']) ? $_SERVER['CONTENT_TYPE'] : '';

        // Check for JSON content type
        if (strpos($contentType, 'application/json') !== false) {
            return $this->getRequestBody();
        }

        // Form-encoded (application/x-www-form-urlencoded)
        $input = [];
        $rawInput = file_get_contents('php://input');
        parse_str($rawInput, $input);

        // Also check POST data
        if (!empty($_POST)) {
            $input = array_merge($input, $_POST);
        }

        return $input;
    }

    /**
     * Handle authorization_code grant type
     *
     * @param array $input Request input
     */
    private function handleAuthorizationCodeGrant($input)
    {
        $code = isset($input['code']) ? $input['code'] : '';
        $clientId = isset($input['client_id']) ? $input['client_id'] : '';
        $clientSecret = isset($input['client_secret']) ? $input['client_secret'] : '';
        $redirectUri = isset($input['redirect_uri']) ? $input['redirect_uri'] : '';

        // Check for Basic auth credentials
        $authHeader = $this->getBasicAuthCredentials();
        if ($authHeader) {
            $clientId = $clientId ?: $authHeader['client_id'];
            $clientSecret = $clientSecret ?: $authHeader['client_secret'];
        }

        if (empty($code)) {
            $this->sendOAuthError('invalid_request', 'Missing required parameter: code');
            return;
        }

        if (empty($clientId)) {
            $this->sendOAuthError('invalid_request', 'Missing required parameter: client_id');
            return;
        }

        $result = $this->_oauth->exchangeAuthorizationCode(
            $code,
            $clientId,
            $clientSecret,
            $redirectUri
        );

        if (isset($result['error'])) {
            $this->sendOAuthError($result['error'], $result['error_description']);
            return;
        }

        $this->sendTokenResponse($result);
    }

    /**
     * Handle client_credentials grant type
     *
     * @param array $input Request input
     */
    private function handleClientCredentialsGrant($input)
    {
        $clientId = isset($input['client_id']) ? $input['client_id'] : '';
        $clientSecret = isset($input['client_secret']) ? $input['client_secret'] : '';
        $scope = isset($input['scope']) ? trim($input['scope']) : '';

        // Check for Basic auth credentials
        $authHeader = $this->getBasicAuthCredentials();
        if ($authHeader) {
            $clientId = $clientId ?: $authHeader['client_id'];
            $clientSecret = $clientSecret ?: $authHeader['client_secret'];
        }

        if (empty($clientId) || empty($clientSecret)) {
            $this->sendOAuthError('invalid_request', 'Missing client credentials');
            return;
        }

        // Validate scopes if provided
        if (!empty($scope)) {
            $requestedScopes = explode(' ', $scope);
            $availableScopes = $this->_oauth->getAvailableScopes();

            foreach ($requestedScopes as $requestedScope) {
                if (!in_array($requestedScope, $availableScopes)) {
                    $this->sendOAuthError('invalid_scope', 'Invalid scope: ' . $requestedScope);
                    return;
                }
            }
        }

        $result = $this->_oauth->clientCredentialsGrant(
            $clientId,
            $clientSecret,
            $scope
        );

        if (isset($result['error'])) {
            $this->sendOAuthError($result['error'], $result['error_description']);
            return;
        }

        $this->sendTokenResponse($result);
    }

    /**
     * Handle refresh_token grant type
     *
     * @param array $input Request input
     */
    private function handleRefreshTokenGrant($input)
    {
        $refreshToken = isset($input['refresh_token']) ? $input['refresh_token'] : '';
        $clientId = isset($input['client_id']) ? $input['client_id'] : '';
        $clientSecret = isset($input['client_secret']) ? $input['client_secret'] : '';
        $scope = isset($input['scope']) ? trim($input['scope']) : '';

        // Check for Basic auth credentials
        $authHeader = $this->getBasicAuthCredentials();
        if ($authHeader) {
            $clientId = $clientId ?: $authHeader['client_id'];
            $clientSecret = $clientSecret ?: $authHeader['client_secret'];
        }

        if (empty($refreshToken)) {
            $this->sendOAuthError('invalid_request', 'Missing required parameter: refresh_token');
            return;
        }

        if (empty($clientId)) {
            $this->sendOAuthError('invalid_request', 'Missing required parameter: client_id');
            return;
        }

        // Validate scopes if provided
        if (!empty($scope)) {
            $requestedScopes = explode(' ', $scope);
            $availableScopes = $this->_oauth->getAvailableScopes();

            foreach ($requestedScopes as $requestedScope) {
                if (!in_array($requestedScope, $availableScopes)) {
                    $this->sendOAuthError('invalid_scope', 'Invalid scope: ' . $requestedScope);
                    return;
                }
            }
        }

        $result = $this->_oauth->refreshAccessToken(
            $refreshToken,
            $clientId,
            $clientSecret,
            $scope
        );

        if (isset($result['error'])) {
            $this->sendOAuthError($result['error'], $result['error_description']);
            return;
        }

        $this->sendTokenResponse($result);
    }

    /**
     * Handle POST /oauth/revoke
     *
     * Token revocation endpoint (RFC 7009).
     * Always returns success even if token is invalid (per spec).
     */
    private function handleRevoke()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Method not allowed', 405);
            return;
        }

        $input = $this->parseTokenInput();

        $token = isset($input['token']) ? $input['token'] : '';
        $tokenTypeHint = isset($input['token_type_hint']) ? $input['token_type_hint'] : '';
        $clientId = isset($input['client_id']) ? $input['client_id'] : '';
        $clientSecret = isset($input['client_secret']) ? $input['client_secret'] : '';

        // Check for Basic auth credentials
        $authHeader = $this->getBasicAuthCredentials();
        if ($authHeader) {
            $clientId = $clientId ?: $authHeader['client_id'];
            $clientSecret = $clientSecret ?: $authHeader['client_secret'];
        }

        if (empty($token)) {
            $this->sendOAuthError('invalid_request', 'Missing required parameter: token');
            return;
        }

        // Attempt to revoke the token
        // Per RFC 7009, we always return success regardless of outcome
        $this->_oauth->revokeToken($token, $tokenTypeHint, $clientId);

        // Return empty 200 response per RFC 7009
        http_response_code(200);
        echo '';
        exit;
    }

    /**
     * Handle POST /oauth/clients
     *
     * Client registration endpoint - creates new OAuth clients.
     *
     * Request Body:
     * - client_name (required): Human-readable name for the client
     * - redirect_uri (optional): Registered redirect URI
     * - user_id (optional): User ID to associate with client
     * - is_confidential (optional): Whether client is confidential (default: true)
     */
    private function handleClients()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Method not allowed', 405);
            return;
        }

        $input = $this->getRequestBody();

        $clientName = isset($input['client_name']) ? trim($input['client_name']) : '';
        $redirectUri = isset($input['redirect_uri']) ? trim($input['redirect_uri']) : '';
        $userId = isset($input['user_id']) ? intval($input['user_id']) : null;
        $isConfidential = isset($input['is_confidential']) ? (bool)$input['is_confidential'] : true;

        if (empty($clientName)) {
            $this->sendError('Missing required field: client_name', 400);
            return;
        }

        // Validate user_id if provided
        if ($userId !== null && $userId <= 0) {
            $this->sendError('Invalid user_id: must be a positive integer', 400);
            return;
        }

        // Create new client
        $result = $this->_oauth->createClient(
            $clientName,
            $redirectUri,
            $userId,
            $isConfidential
        );

        if (isset($result['error'])) {
            $this->sendError($result['error_description'], 500);
            return;
        }

        $this->sendSuccess([
            'client_id' => $result['client_id'],
            'client_secret' => $result['client_secret'],
            'client_name' => $clientName,
            'redirect_uri' => $redirectUri,
            'is_confidential' => $isConfidential,
            'created_at' => date('c'),
            'message' => 'OAuth client created successfully. Store the client_secret securely - it cannot be retrieved again.'
        ], 201);
    }

    /**
     * Get client credentials from Basic auth header
     *
     * @return array|null ['client_id' => string, 'client_secret' => string] or null
     */
    private function getBasicAuthCredentials()
    {
        $headers = $this->getRequestHeaders();
        $authHeader = '';

        // Check various header formats
        if (isset($headers['Authorization'])) {
            $authHeader = $headers['Authorization'];
        } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['HTTP_AUTHORIZATION'];
        } elseif (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $authHeader = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        if (empty($authHeader) || strpos($authHeader, 'Basic ') !== 0) {
            return null;
        }

        $encoded = substr($authHeader, 6);
        $decoded = base64_decode($encoded);

        if ($decoded === false || strpos($decoded, ':') === false) {
            return null;
        }

        list($clientId, $clientSecret) = explode(':', $decoded, 2);

        return [
            'client_id' => $clientId,
            'client_secret' => $clientSecret
        ];
    }

    /**
     * Send OAuth 2.0 error response
     *
     * @param string $error Error code (e.g., invalid_request, invalid_client)
     * @param string $description Human-readable error description
     * @param int $statusCode HTTP status code (default 400)
     */
    private function sendOAuthError($error, $description, $statusCode = 400)
    {
        // Log error if logger is available
        if (isset($this->_requestLogger) && $this->_requestLogger) {
            $this->_requestLogger->logError($statusCode, $error . ': ' . $description);
        }

        http_response_code($statusCode);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        header('Pragma: no-cache');

        echo json_encode([
            'error' => $error,
            'error_description' => $description
        ], JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Send OAuth 2.0 token response
     *
     * @param array $tokenData Token response data
     */
    private function sendTokenResponse($tokenData)
    {
        // Log success if logger is available
        if (isset($this->_requestLogger) && $this->_requestLogger) {
            $this->_requestLogger->logSuccess(200);
        }

        http_response_code(200);
        header('Content-Type: application/json; charset=UTF-8');
        header('Cache-Control: no-store');
        header('Pragma: no-cache');

        echo json_encode($tokenData, JSON_PRETTY_PRINT);
        exit;
    }
}
