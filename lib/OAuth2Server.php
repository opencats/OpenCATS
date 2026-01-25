<?php
/**
 * CATS
 * OAuth 2.0 Server Library
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * Copyright (C) 2025 OpenCATS Contributors
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
 * OAuth 2.0 Implementation added by OpenCATS Contributors, 2025.
 *
 * @package    CATS
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @copyright Copyright (C) 2025 OpenCATS Contributors
 */

include_once('./lib/DatabaseConnection.php');

/**
 * OAuth 2.0 Server Implementation
 *
 * Provides OAuth 2.0 authentication and authorization functionality
 * supporting Authorization Code, Client Credentials, and Refresh Token grants.
 *
 * @package    CATS
 * @subpackage Library
 */
class OAuth2Server
{
    /** @var int Access token lifetime in seconds (1 hour) */
    const ACCESS_TOKEN_LIFETIME = 3600;

    /** @var int Refresh token lifetime in seconds (14 days) */
    const REFRESH_TOKEN_LIFETIME = 1209600;

    /** @var int Authorization code lifetime in seconds (10 minutes) */
    const AUTH_CODE_LIFETIME = 600;

    /** @var DatabaseConnection Database connection instance */
    private $_db;

    /** @var int Site ID for multi-tenant support */
    private $_siteID;


    /**
     * Constructor.
     *
     * @param int $siteID Site ID for multi-tenant support.
     */
    public function __construct($siteID = 1)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }


    /**
     * Creates a new OAuth 2.0 client.
     *
     * @param string $clientName Human-readable name for the client.
     * @param string|null $redirectUri Redirect URI for authorization code flow.
     * @param int|null $userId User ID to associate with the client.
     * @param bool $isConfidential Whether the client is confidential (can keep secret).
     * @return array Array containing client_id, client_secret (unhashed), client_name.
     */
    public function createClient($clientName, $redirectUri = null, $userId = null, $isConfidential = true)
    {
        $clientId = $this->_generateToken(32);
        $clientSecret = $this->_generateToken(64);
        $clientSecretHash = password_hash($clientSecret, PASSWORD_DEFAULT);

        $sql = sprintf(
            "INSERT INTO oauth_clients (
                client_id,
                client_secret,
                client_name,
                redirect_uri,
                user_id,
                is_confidential,
                site_id,
                date_created
            )
            VALUES (
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                NOW()
            )",
            $this->_db->makeQueryString($clientId),
            $this->_db->makeQueryString($clientSecretHash),
            $this->_db->makeQueryString($clientName),
            $redirectUri !== null ? $this->_db->makeQueryString($redirectUri) : 'NULL',
            $userId !== null ? $this->_db->makeQueryInteger($userId) : 'NULL',
            $isConfidential ? 1 : 0,
            $this->_db->makeQueryInteger($this->_siteID)
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return false;
        }

        return array(
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'client_name'   => $clientName
        );
    }


    /**
     * Validates a client's credentials.
     *
     * @param string $clientId Client ID to validate.
     * @param string|null $clientSecret Client secret (required for confidential clients).
     * @return array|false Client data array on success, false on failure.
     */
    public function validateClient($clientId, $clientSecret = null)
    {
        $sql = sprintf(
            "SELECT
                oauth_client_id,
                client_id,
                client_secret,
                client_name,
                redirect_uri,
                user_id,
                is_confidential,
                is_active,
                site_id
            FROM
                oauth_clients
            WHERE
                client_id = %s
                AND site_id = %s
                AND is_active = 1",
            $this->_db->makeQueryString($clientId),
            $this->_db->makeQueryInteger($this->_siteID)
        );

        $client = $this->_db->getAssoc($sql);

        if (empty($client))
        {
            return false;
        }

        /* Confidential clients must provide and verify their secret. */
        if ($client['is_confidential'] == 1)
        {
            if ($clientSecret === null)
            {
                return false;
            }

            if (!password_verify($clientSecret, $client['client_secret']))
            {
                return false;
            }
        }

        /* Remove the hashed secret from the returned data for security. */
        unset($client['client_secret']);

        return $client;
    }


    /**
     * Creates an authorization code for the authorization code grant flow.
     *
     * @param string $clientId Client ID requesting authorization.
     * @param int $userId User ID granting authorization.
     * @param string $redirectUri Redirect URI to verify during exchange.
     * @param string $scope Requested scope (default: 'read').
     * @return string|false The authorization code on success, false on failure.
     */
    public function createAuthorizationCode($clientId, $userId, $redirectUri, $scope = 'read')
    {
        $code = $this->_generateToken(40);
        $expiresAt = date('Y-m-d H:i:s', time() + self::AUTH_CODE_LIFETIME);

        $sql = sprintf(
            "INSERT INTO oauth_auth_codes (
                code,
                client_id,
                user_id,
                redirect_uri,
                scope,
                expires_at,
                site_id,
                date_created
            )
            VALUES (
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                NOW()
            )",
            $this->_db->makeQueryString($code),
            $this->_db->makeQueryString($clientId),
            $this->_db->makeQueryInteger($userId),
            $this->_db->makeQueryString($redirectUri),
            $this->_db->makeQueryString($scope),
            $this->_db->makeQueryString($expiresAt),
            $this->_db->makeQueryInteger($this->_siteID)
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return false;
        }

        return $code;
    }


    /**
     * Exchanges an authorization code for access and refresh tokens.
     *
     * @param string $code Authorization code to exchange.
     * @param string $clientId Client ID making the request.
     * @param string $clientSecret Client secret for validation.
     * @param string $redirectUri Redirect URI to verify (must match original).
     * @return array|false Token response array on success, false on failure.
     */
    public function exchangeAuthorizationCode($code, $clientId, $clientSecret, $redirectUri)
    {
        /* Validate the client credentials. */
        $client = $this->validateClient($clientId, $clientSecret);
        if ($client === false)
        {
            return false;
        }

        /* Look up the authorization code. */
        $sql = sprintf(
            "SELECT
                oauth_auth_code_id,
                code,
                client_id,
                user_id,
                redirect_uri,
                scope,
                expires_at,
                is_used
            FROM
                oauth_auth_codes
            WHERE
                code = %s
                AND client_id = %s
                AND site_id = %s
                AND is_used = 0",
            $this->_db->makeQueryString($code),
            $this->_db->makeQueryString($clientId),
            $this->_db->makeQueryInteger($this->_siteID)
        );

        $authCode = $this->_db->getAssoc($sql);

        if (empty($authCode))
        {
            return false;
        }

        /* Check if the code has expired. */
        if (strtotime($authCode['expires_at']) < time())
        {
            $this->_deleteAuthorizationCode($authCode['oauth_auth_code_id']);
            return false;
        }

        /* Verify redirect URI matches. */
        if ($authCode['redirect_uri'] !== $redirectUri)
        {
            return false;
        }

        /* Mark the code as used (single-use). */
        $this->_deleteAuthorizationCode($authCode['oauth_auth_code_id']);

        /* Create and return tokens. */
        return $this->createTokens($clientId, $authCode['user_id'], $authCode['scope']);
    }


    /**
     * Issues tokens using the client credentials grant.
     *
     * @param string $clientId Client ID.
     * @param string $clientSecret Client secret.
     * @param string $scope Requested scope (default: 'read').
     * @return array|false Token response array on success, false on failure.
     */
    public function clientCredentialsGrant($clientId, $clientSecret, $scope = 'read')
    {
        /* Validate the client credentials. */
        $client = $this->validateClient($clientId, $clientSecret);
        if ($client === false)
        {
            return false;
        }

        /* Client credentials grant does not have a user, use client's user_id if set. */
        $userId = isset($client['user_id']) ? $client['user_id'] : null;

        /* Create and return tokens. */
        return $this->createTokens($clientId, $userId, $scope);
    }


    /**
     * Issues new tokens using a refresh token.
     *
     * @param string $refreshToken Refresh token to use.
     * @param string $clientId Client ID making the request.
     * @param string|null $clientSecret Client secret (optional for public clients).
     * @return array|false Token response array on success, false on failure.
     */
    public function refreshTokenGrant($refreshToken, $clientId, $clientSecret = null)
    {
        /* Validate client if secret is provided. */
        if ($clientSecret !== null)
        {
            $client = $this->validateClient($clientId, $clientSecret);
            if ($client === false)
            {
                return false;
            }
        }
        else
        {
            /* For public clients, just verify the client exists and is active. */
            $sql = sprintf(
                "SELECT
                    oauth_client_id,
                    client_id,
                    is_confidential
                FROM
                    oauth_clients
                WHERE
                    client_id = %s
                    AND site_id = %s
                    AND is_active = 1",
                $this->_db->makeQueryString($clientId),
                $this->_db->makeQueryInteger($this->_siteID)
            );

            $client = $this->_db->getAssoc($sql);
            if (empty($client))
            {
                return false;
            }

            /* Confidential clients MUST provide a secret. */
            if ($client['is_confidential'] == 1)
            {
                return false;
            }
        }

        /* Look up the refresh token. */
        $sql = sprintf(
            "SELECT
                oauth_refresh_token_id,
                token,
                client_id,
                user_id,
                scope,
                expires_at
            FROM
                oauth_refresh_tokens
            WHERE
                token = %s
                AND client_id = %s
                AND site_id = %s",
            $this->_db->makeQueryString($refreshToken),
            $this->_db->makeQueryString($clientId),
            $this->_db->makeQueryInteger($this->_siteID)
        );

        $tokenData = $this->_db->getAssoc($sql);

        if (empty($tokenData))
        {
            return false;
        }

        /* Check if the refresh token has expired. */
        if (strtotime($tokenData['expires_at']) < time())
        {
            $this->_deleteRefreshToken($tokenData['oauth_refresh_token_id']);
            return false;
        }

        /* Delete the old refresh token (rotation). */
        $this->_deleteRefreshToken($tokenData['oauth_refresh_token_id']);

        /* Create and return new tokens. */
        return $this->createTokens($clientId, $tokenData['user_id'], $tokenData['scope']);
    }


    /**
     * Creates access and refresh tokens.
     *
     * @param string $clientId Client ID the tokens are for.
     * @param int|null $userId User ID the tokens are for (null for client credentials).
     * @param string $scope Token scope.
     * @return array|false OAuth 2.0 token response array on success, false on failure.
     */
    public function createTokens($clientId, $userId, $scope = 'read')
    {
        $accessToken = $this->_generateToken(40);
        $refreshToken = $this->_generateToken(40);
        $accessTokenExpiry = date('Y-m-d H:i:s', time() + self::ACCESS_TOKEN_LIFETIME);
        $refreshTokenExpiry = date('Y-m-d H:i:s', time() + self::REFRESH_TOKEN_LIFETIME);

        /* Insert access token. */
        $sql = sprintf(
            "INSERT INTO oauth_access_tokens (
                token,
                client_id,
                user_id,
                scope,
                expires_at,
                site_id,
                date_created
            )
            VALUES (
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                NOW()
            )",
            $this->_db->makeQueryString($accessToken),
            $this->_db->makeQueryString($clientId),
            $userId !== null ? $this->_db->makeQueryInteger($userId) : 'NULL',
            $this->_db->makeQueryString($scope),
            $this->_db->makeQueryString($accessTokenExpiry),
            $this->_db->makeQueryInteger($this->_siteID)
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return false;
        }

        /* Insert refresh token. */
        $sql = sprintf(
            "INSERT INTO oauth_refresh_tokens (
                token,
                client_id,
                user_id,
                scope,
                expires_at,
                site_id,
                date_created
            )
            VALUES (
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                NOW()
            )",
            $this->_db->makeQueryString($refreshToken),
            $this->_db->makeQueryString($clientId),
            $userId !== null ? $this->_db->makeQueryInteger($userId) : 'NULL',
            $this->_db->makeQueryString($scope),
            $this->_db->makeQueryString($refreshTokenExpiry),
            $this->_db->makeQueryInteger($this->_siteID)
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return false;
        }

        /* Return standard OAuth 2.0 token response format. */
        return array(
            'access_token'  => $accessToken,
            'token_type'    => 'Bearer',
            'expires_in'    => self::ACCESS_TOKEN_LIFETIME,
            'refresh_token' => $refreshToken,
            'scope'         => $scope
        );
    }


    /**
     * Validates an access token.
     *
     * @param string $token Access token to validate.
     * @return array|false Token info on success (user_id, client_id, scope, expires), false on failure.
     */
    public function validateAccessToken($token)
    {
        $sql = sprintf(
            "SELECT
                oauth_access_token_id,
                token,
                client_id,
                user_id,
                scope,
                expires_at
            FROM
                oauth_access_tokens
            WHERE
                token = %s
                AND site_id = %s",
            $this->_db->makeQueryString($token),
            $this->_db->makeQueryInteger($this->_siteID)
        );

        $tokenData = $this->_db->getAssoc($sql);

        if (empty($tokenData))
        {
            return false;
        }

        /* Check if the token has expired. */
        if (strtotime($tokenData['expires_at']) < time())
        {
            return false;
        }

        return array(
            'user_id'   => $tokenData['user_id'],
            'client_id' => $tokenData['client_id'],
            'scope'     => $tokenData['scope'],
            'expires'   => $tokenData['expires_at']
        );
    }


    /**
     * Revokes all tokens for a specific user.
     *
     * @param int $userId User ID whose tokens should be revoked.
     * @return bool True on success.
     */
    public function revokeUserTokens($userId)
    {
        /* Delete all access tokens for the user. */
        $sql = sprintf(
            "DELETE FROM
                oauth_access_tokens
            WHERE
                user_id = %s
                AND site_id = %s",
            $this->_db->makeQueryInteger($userId),
            $this->_db->makeQueryInteger($this->_siteID)
        );
        $this->_db->query($sql);

        /* Delete all refresh tokens for the user. */
        $sql = sprintf(
            "DELETE FROM
                oauth_refresh_tokens
            WHERE
                user_id = %s
                AND site_id = %s",
            $this->_db->makeQueryInteger($userId),
            $this->_db->makeQueryInteger($this->_siteID)
        );
        $this->_db->query($sql);

        return true;
    }


    /**
     * Cleans up expired tokens from all OAuth tables.
     * This is a static method that can be called without instantiation.
     *
     * @return void
     */
    public static function cleanup()
    {
        $db = DatabaseConnection::getInstance();

        /* Delete expired access tokens. */
        $db->query("DELETE FROM oauth_access_tokens WHERE expires_at < NOW()");

        /* Delete expired refresh tokens. */
        $db->query("DELETE FROM oauth_refresh_tokens WHERE expires_at < NOW()");

        /* Delete expired and used authorization codes. */
        $db->query("DELETE FROM oauth_auth_codes WHERE expires_at < NOW() OR is_used = 1");
    }


    /**
     * Generates a cryptographically secure random token.
     *
     * @param int $length Length of the token in characters (must be even).
     * @return string Hexadecimal token string.
     */
    private function _generateToken($length = 40)
    {
        return bin2hex(random_bytes($length / 2));
    }


    /**
     * Deletes an authorization code by ID.
     *
     * @param int $authCodeId Authorization code ID to delete.
     * @return void
     */
    private function _deleteAuthorizationCode($authCodeId)
    {
        $sql = sprintf(
            "DELETE FROM
                oauth_auth_codes
            WHERE
                oauth_auth_code_id = %s",
            $this->_db->makeQueryInteger($authCodeId)
        );
        $this->_db->query($sql);
    }


    /**
     * Deletes a refresh token by ID.
     *
     * @param int $refreshTokenId Refresh token ID to delete.
     * @return void
     */
    private function _deleteRefreshToken($refreshTokenId)
    {
        $sql = sprintf(
            "DELETE FROM
                oauth_refresh_tokens
            WHERE
                oauth_refresh_token_id = %s",
            $this->_db->makeQueryInteger($refreshTokenId)
        );
        $this->_db->query($sql);
    }
}
