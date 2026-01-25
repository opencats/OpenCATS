<?php
/**
 * CATS
 * API Keys Library
 *
 * Manages API keys for REST API authentication.
 * Allows administrators to create "sandbox accounts" for developers.
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
 * @subpackage Library
 * @copyright Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * @version    $Id: ApiKeys.php 2026-01-25 $
 */

include_once('./lib/DatabaseConnection.php');

class ApiKeys
{
    private $_siteID;
    private $_db;

    // Token expiry in seconds (default: 1 hour)
    const TOKEN_EXPIRY = 3600;
    
    // API key length
    const KEY_LENGTH = 32;
    const SECRET_LENGTH = 48;

    /**
     * Constructor
     *
     * @param int $siteID Site ID
     */
    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }

    // =========================================
    // API KEY MANAGEMENT (Admin Functions)
    // =========================================

    /**
     * Create a new API key (Sandbox Account)
     *
     * @param int    $userID      User ID to associate with this key
     * @param string $description Human-readable description
     * @return array              ['api_key' => '...', 'api_secret' => '...', 'api_key_id' => ...]
     */
    public function create($userID, $description = '')
    {
        // Generate cryptographically secure random keys
        $apiKey = $this->_generateRandomKey(self::KEY_LENGTH);
        $apiSecret = $this->_generateRandomKey(self::SECRET_LENGTH);
        
        // Hash the secret for storage (we'll return the plaintext once)
        $secretHash = password_hash($apiSecret, PASSWORD_DEFAULT);

        $sql = sprintf(
            "INSERT INTO api_keys 
             (site_id, user_id, api_key, api_secret, description, is_active, created_date)
             VALUES (%d, %d, %s, %s, %s, 1, NOW())",
            $this->_siteID,
            intval($userID),
            $this->_db->makeQueryString($apiKey),
            $this->_db->makeQueryString($secretHash),
            $this->_db->makeQueryString($description)
        );

        $this->_db->query($sql);
        $apiKeyID = $this->_db->getLastInsertID();

        // Return the credentials (secret shown only once!)
        return [
            'api_key_id' => $apiKeyID,
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,  // Only time this is shown in plaintext!
            'description' => $description,
            'message' => 'IMPORTANT: Save the api_secret now. It cannot be retrieved later.'
        ];
    }

    /**
     * Create a simple API key (no hashing - for development/testing)
     * WARNING: Less secure, use only for development
     *
     * @param int    $userID      User ID
     * @param string $description Description
     * @param string $customKey   Optional custom API key
     * @param string $customSecret Optional custom secret
     * @return array
     */
    public function createSimple($userID, $description = '', $customKey = null, $customSecret = null)
    {
        $apiKey = $customKey ?: $this->_generateRandomKey(self::KEY_LENGTH);
        $apiSecret = $customSecret ?: $this->_generateRandomKey(self::SECRET_LENGTH);

        $sql = sprintf(
            "INSERT INTO api_keys 
             (site_id, user_id, api_key, api_secret, description, is_active, created_date)
             VALUES (%d, %d, %s, %s, %s, 1, NOW())",
            $this->_siteID,
            intval($userID),
            $this->_db->makeQueryString($apiKey),
            $this->_db->makeQueryString($apiSecret),  // Stored in plaintext for dev
            $this->_db->makeQueryString($description)
        );

        $this->_db->query($sql);
        
        return [
            'api_key_id' => $this->_db->getLastInsertID(),
            'api_key' => $apiKey,
            'api_secret' => $apiSecret,
            'description' => $description
        ];
    }

    /**
     * Get all API keys for a site (admin view)
     *
     * @return array
     */
    public function getAll()
    {
        $sql = sprintf(
            "SELECT ak.api_key_id, ak.api_key, ak.description, ak.is_active,
                    ak.created_date, ak.last_used,
                    u.user_id, u.first_name, u.last_name, u.user_name
             FROM api_keys ak
             LEFT JOIN user u ON ak.user_id = u.user_id
             WHERE ak.site_id = %d
             ORDER BY ak.created_date DESC",
            $this->_siteID
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Get a single API key by ID
     *
     * @param int $apiKeyID
     * @return array|null
     */
    public function get($apiKeyID)
    {
        $sql = sprintf(
            "SELECT ak.*, u.first_name, u.last_name, u.user_name
             FROM api_keys ak
             LEFT JOIN user u ON ak.user_id = u.user_id
             WHERE ak.api_key_id = %d AND ak.site_id = %d",
            intval($apiKeyID),
            $this->_siteID
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Deactivate an API key
     *
     * @param int $apiKeyID
     * @return bool
     */
    public function deactivate($apiKeyID)
    {
        $sql = sprintf(
            "UPDATE api_keys SET is_active = 0 WHERE api_key_id = %d AND site_id = %d",
            intval($apiKeyID),
            $this->_siteID
        );
        return $this->_db->query($sql);
    }

    /**
     * Activate an API key
     *
     * @param int $apiKeyID
     * @return bool
     */
    public function activate($apiKeyID)
    {
        $sql = sprintf(
            "UPDATE api_keys SET is_active = 1 WHERE api_key_id = %d AND site_id = %d",
            intval($apiKeyID),
            $this->_siteID
        );
        return $this->_db->query($sql);
    }

    /**
     * Delete an API key permanently
     *
     * @param int $apiKeyID
     * @return bool
     */
    public function delete($apiKeyID)
    {
        $sql = sprintf(
            "DELETE FROM api_keys WHERE api_key_id = %d AND site_id = %d",
            intval($apiKeyID),
            $this->_siteID
        );
        return $this->_db->query($sql);
    }

    /**
     * Regenerate secret for an existing API key
     *
     * @param int $apiKeyID
     * @return array ['api_secret' => '...'] or false
     */
    public function regenerateSecret($apiKeyID)
    {
        $newSecret = $this->_generateRandomKey(self::SECRET_LENGTH);
        
        $sql = sprintf(
            "UPDATE api_keys SET api_secret = %s WHERE api_key_id = %d AND site_id = %d",
            $this->_db->makeQueryString($newSecret),
            intval($apiKeyID),
            $this->_siteID
        );

        if ($this->_db->query($sql)) {
            return [
                'api_secret' => $newSecret,
                'message' => 'Secret regenerated. Save it now - it cannot be retrieved later.'
            ];
        }
        return false;
    }

    // =========================================
    // AUTHENTICATION (Runtime Functions)
    // =========================================

    /**
     * Validate API key (simple - just check key exists and is active)
     *
     * @param string $apiKey
     * @return array|false  User info if valid, false if not
     */
    public function validate($apiKey)
    {
        $sql = sprintf(
            "SELECT ak.*, u.access_level
             FROM api_keys ak
             LEFT JOIN user u ON ak.user_id = u.user_id
             WHERE ak.api_key = %s 
               AND ak.site_id = %d 
               AND ak.is_active = 1",
            $this->_db->makeQueryString($apiKey),
            $this->_siteID
        );

        $result = $this->_db->getAssoc($sql);
        
        if ($result && !empty($result)) {
            // Update last used timestamp
            $this->_updateLastUsed($result['api_key_id']);
            return $result;
        }

        return false;
    }

    /**
     * Authenticate with API key and secret
     *
     * @param string $apiKey
     * @param string $apiSecret
     * @return array|false
     */
    public function authenticate($apiKey, $apiSecret)
    {
        $sql = sprintf(
            "SELECT ak.*, u.access_level
             FROM api_keys ak
             LEFT JOIN user u ON ak.user_id = u.user_id
             WHERE ak.api_key = %s 
               AND ak.site_id = %d 
               AND ak.is_active = 1",
            $this->_db->makeQueryString($apiKey),
            $this->_siteID
        );

        $result = $this->_db->getAssoc($sql);
        
        if (!$result || empty($result)) {
            return false;
        }

        // Check secret (support both hashed and plaintext for dev)
        $storedSecret = $result['api_secret'];
        $secretValid = false;

        // Try password_verify first (for hashed secrets)
        if (password_verify($apiSecret, $storedSecret)) {
            $secretValid = true;
        }
        // Fall back to direct comparison (for dev/plaintext secrets)
        elseif ($apiSecret === $storedSecret) {
            $secretValid = true;
        }

        if ($secretValid) {
            $this->_updateLastUsed($result['api_key_id']);
            return $result;
        }

        return false;
    }

    /**
     * Generate a session token for authenticated requests
     *
     * @param int $apiKeyID
     * @return string Session token
     */
    public function generateSessionToken($apiKeyID)
    {
        $token = $this->_generateRandomKey(64);
        $expiresAt = date('Y-m-d H:i:s', time() + self::TOKEN_EXPIRY);

        $sql = sprintf(
            "INSERT INTO api_sessions (api_key_id, session_token, created_date, expires_date)
             VALUES (%d, %s, NOW(), %s)",
            intval($apiKeyID),
            $this->_db->makeQueryString($token),
            $this->_db->makeQueryString($expiresAt)
        );

        $this->_db->query($sql);
        return $token;
    }

    /**
     * Validate a session token
     *
     * @param string $token
     * @return array|false
     */
    public function validateSessionToken($token)
    {
        $sql = sprintf(
            "SELECT s.*, ak.user_id, ak.site_id, u.access_level
             FROM api_sessions s
             INNER JOIN api_keys ak ON s.api_key_id = ak.api_key_id
             LEFT JOIN user u ON ak.user_id = u.user_id
             WHERE s.session_token = %s 
               AND s.expires_date > NOW()
               AND ak.is_active = 1",
            $this->_db->makeQueryString($token)
        );

        return $this->_db->getAssoc($sql);
    }

    /**
     * Revoke a session token
     *
     * @param string $token
     * @return bool
     */
    public function revokeSessionToken($token)
    {
        $sql = sprintf(
            "DELETE FROM api_sessions WHERE session_token = %s",
            $this->_db->makeQueryString($token)
        );
        return $this->_db->query($sql);
    }

    /**
     * Clean up expired sessions
     *
     * @return int Number of deleted sessions
     */
    public function cleanupExpiredSessions()
    {
        $sql = "DELETE FROM api_sessions WHERE expires_date < NOW()";
        $this->_db->query($sql);
        return $this->_db->getAffectedRows();
    }

    // =========================================
    // HELPER FUNCTIONS
    // =========================================

    /**
     * Generate a cryptographically secure random key
     *
     * @param int $length
     * @return string
     */
    private function _generateRandomKey($length)
    {
        // Use random_bytes if available (PHP 7+)
        if (function_exists('random_bytes')) {
            return bin2hex(random_bytes($length / 2));
        }
        // Fallback to openssl
        if (function_exists('openssl_random_pseudo_bytes')) {
            return bin2hex(openssl_random_pseudo_bytes($length / 2));
        }
        // Last resort (less secure)
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $key;
    }

    /**
     * Update last_used timestamp for an API key
     *
     * @param int $apiKeyID
     */
    private function _updateLastUsed($apiKeyID)
    {
        $sql = sprintf(
            "UPDATE api_keys SET last_used = NOW() WHERE api_key_id = %d",
            intval($apiKeyID)
        );
        $this->_db->query($sql);
    }

    /**
     * Get usage statistics for an API key
     *
     * @param int $apiKeyID
     * @return array
     */
    public function getUsageStats($apiKeyID)
    {
        $key = $this->get($apiKeyID);
        
        // Count active sessions
        $sql = sprintf(
            "SELECT COUNT(*) as active_sessions 
             FROM api_sessions 
             WHERE api_key_id = %d AND expires_date > NOW()",
            intval($apiKeyID)
        );
        $sessions = $this->_db->getAssoc($sql);

        return [
            'api_key_id' => $apiKeyID,
            'created_date' => $key['created_date'] ?? null,
            'last_used' => $key['last_used'] ?? null,
            'is_active' => $key['is_active'] ?? 0,
            'active_sessions' => $sessions['active_sessions'] ?? 0
        ];
    }
}


// =========================================
// CLI TOOL FOR MANAGING API KEYS
// =========================================
// Run from command line: php lib/ApiKeys.php create 1 "My API Key"

if (php_sapi_name() === 'cli' && basename(__FILE__) === basename($_SERVER['PHP_SELF'])) {
    
    // Bootstrap OpenCATS
    if (file_exists('./config.php')) {
        include_once('./config.php');
    } else {
        // Assume we're in lib/ directory
        include_once('../config.php');
    }
    
    $siteID = defined('CATS_ADMIN_SITE') ? CATS_ADMIN_SITE : 1;
    $apiKeys = new ApiKeys($siteID);
    
    $command = isset($argv[1]) ? $argv[1] : 'help';
    
    switch ($command) {
        case 'create':
            $userID = isset($argv[2]) ? intval($argv[2]) : 1;
            $description = isset($argv[3]) ? $argv[3] : 'API Key created via CLI';
            
            $result = $apiKeys->createSimple($userID, $description);
            
            echo "\n";
            echo "========================================\n";
            echo "  NEW API KEY CREATED (Sandbox Account)\n";
            echo "========================================\n";
            echo "\n";
            echo "  API Key ID:    " . $result['api_key_id'] . "\n";
            echo "  API Key:       " . $result['api_key'] . "\n";
            echo "  API Secret:    " . $result['api_secret'] . "\n";
            echo "  Description:   " . $result['description'] . "\n";
            echo "\n";
            echo "  ⚠️  SAVE THESE CREDENTIALS NOW!\n";
            echo "  The secret cannot be retrieved later.\n";
            echo "\n";
            echo "========================================\n";
            echo "\n";
            break;
            
        case 'list':
            $keys = $apiKeys->getAll();
            echo "\n";
            echo "API Keys:\n";
            echo str_repeat("-", 80) . "\n";
            printf("%-5s %-34s %-20s %-10s\n", "ID", "API Key", "Description", "Status");
            echo str_repeat("-", 80) . "\n";
            foreach ($keys as $key) {
                $status = $key['is_active'] ? 'Active' : 'Inactive';
                printf("%-5s %-34s %-20s %-10s\n", 
                    $key['api_key_id'],
                    $key['api_key'],
                    substr($key['description'], 0, 20),
                    $status
                );
            }
            echo "\n";
            break;
            
        case 'deactivate':
            $apiKeyID = isset($argv[2]) ? intval($argv[2]) : 0;
            if ($apiKeyID && $apiKeys->deactivate($apiKeyID)) {
                echo "API Key $apiKeyID deactivated.\n";
            } else {
                echo "Failed to deactivate API Key.\n";
            }
            break;
            
        case 'activate':
            $apiKeyID = isset($argv[2]) ? intval($argv[2]) : 0;
            if ($apiKeyID && $apiKeys->activate($apiKeyID)) {
                echo "API Key $apiKeyID activated.\n";
            } else {
                echo "Failed to activate API Key.\n";
            }
            break;
            
        case 'delete':
            $apiKeyID = isset($argv[2]) ? intval($argv[2]) : 0;
            if ($apiKeyID && $apiKeys->delete($apiKeyID)) {
                echo "API Key $apiKeyID deleted.\n";
            } else {
                echo "Failed to delete API Key.\n";
            }
            break;
            
        default:
            echo "\n";
            echo "OpenCATS API Key Management Tool\n";
            echo "================================\n";
            echo "\n";
            echo "Usage:\n";
            echo "  php lib/ApiKeys.php create [user_id] [description]  - Create new API key\n";
            echo "  php lib/ApiKeys.php list                            - List all API keys\n";
            echo "  php lib/ApiKeys.php deactivate [api_key_id]         - Deactivate an API key\n";
            echo "  php lib/ApiKeys.php activate [api_key_id]           - Activate an API key\n";
            echo "  php lib/ApiKeys.php delete [api_key_id]             - Delete an API key\n";
            echo "\n";
            echo "Examples:\n";
            echo "  php lib/ApiKeys.php create 1 \"JobPulse Development\"\n";
            echo "  php lib/ApiKeys.php create 1 \"Testing Sandbox\"\n";
            echo "  php lib/ApiKeys.php list\n";
            echo "\n";
            break;
    }
}
