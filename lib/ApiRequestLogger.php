<?php
/**
 * CATS
 * API Request Logger
 *
 * Logs API requests to database for debugging and analytics.
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
 * @version    $Id: ApiRequestLogger.php 2026-01-25 $
 */

include_once('./lib/DatabaseConnection.php');

class ApiRequestLogger
{
    private $_db;
    private $_startTime;
    private $_apiKeyID;
    private $_endpoint;
    private $_method;
    private $_ipAddress;

    /**
     * Constructor - starts timing the request
     *
     * @param int|null $apiKeyID  API Key ID (null for unauthenticated)
     * @param string   $endpoint  API endpoint being called
     * @param string   $method    HTTP method (GET, POST, etc.)
     */
    public function __construct($apiKeyID = null, $endpoint = '', $method = 'GET')
    {
        $this->_db = DatabaseConnection::getInstance();
        $this->_startTime = microtime(true);
        $this->_apiKeyID = $apiKeyID ? intval($apiKeyID) : null;
        $this->_endpoint = substr($endpoint, 0, 100);
        $this->_method = substr($method, 0, 10);
        $this->_ipAddress = $this->_getClientIP();
    }

    /**
     * Update the API key ID (called after authentication)
     *
     * @param int $apiKeyID API Key ID
     */
    public function setApiKeyID($apiKeyID)
    {
        $this->_apiKeyID = intval($apiKeyID);
    }

    /**
     * Log the completed request
     *
     * @param int         $statusCode   HTTP status code
     * @param string|null $errorMessage Error message if failed
     * @return bool                     Success
     */
    public function log($statusCode, $errorMessage = null)
    {
        $responseTimeMs = intval((microtime(true) - $this->_startTime) * 1000);

        $sql = sprintf(
            "INSERT INTO api_request_log
             (api_key_id, endpoint, method, status_code, request_time, response_time_ms, ip_address, error_message)
             VALUES (%s, %s, %s, %d, NOW(), %d, %s, %s)",
            $this->_apiKeyID ? $this->_apiKeyID : 'NULL',
            $this->_db->makeQueryString($this->_endpoint),
            $this->_db->makeQueryString($this->_method),
            intval($statusCode),
            $responseTimeMs,
            $this->_db->makeQueryString($this->_ipAddress),
            $errorMessage ? $this->_db->makeQueryString(substr($errorMessage, 0, 1000)) : 'NULL'
        );

        return $this->_db->query($sql);
    }

    /**
     * Log a successful request
     *
     * @param int $statusCode HTTP status code (default 200)
     * @return bool           Success
     */
    public function logSuccess($statusCode = 200)
    {
        return $this->log($statusCode, null);
    }

    /**
     * Log a failed request
     *
     * @param int    $statusCode   HTTP status code
     * @param string $errorMessage Error description
     * @return bool                Success
     */
    public function logError($statusCode, $errorMessage)
    {
        return $this->log($statusCode, $errorMessage);
    }

    /**
     * Get client IP address
     *
     * @return string
     */
    private function _getClientIP()
    {
        // Check for proxy headers
        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_CLIENT_IP',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (!empty($_SERVER[$header])) {
                $ips = explode(',', $_SERVER[$header]);
                $ip = trim($ips[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return substr($ip, 0, 45);
                }
            }
        }

        return '0.0.0.0';
    }

    /**
     * Clean up old log entries (older than specified days)
     *
     * @param int $daysToKeep Number of days of logs to retain
     * @return int            Number of deleted rows
     */
    public static function cleanup($daysToKeep = 30)
    {
        $db = DatabaseConnection::getInstance();
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$daysToKeep} days"));

        $sql = sprintf(
            "DELETE FROM api_request_log WHERE request_time < '%s'",
            $cutoff
        );

        $db->query($sql);
        return $db->getAffectedRows();
    }

    /**
     * Get usage statistics for an API key
     *
     * @param int    $apiKeyID API Key ID
     * @param string $period   Period: 'hour', 'day', 'week', 'month'
     * @return array           Statistics
     */
    public static function getStats($apiKeyID, $period = 'day')
    {
        $db = DatabaseConnection::getInstance();

        $intervals = [
            'hour' => '1 HOUR',
            'day' => '1 DAY',
            'week' => '1 WEEK',
            'month' => '1 MONTH'
        ];

        $interval = $intervals[$period] ?? $intervals['day'];

        $sql = sprintf(
            "SELECT
                COUNT(*) as total_requests,
                SUM(CASE WHEN status_code >= 200 AND status_code < 300 THEN 1 ELSE 0 END) as successful,
                SUM(CASE WHEN status_code >= 400 THEN 1 ELSE 0 END) as failed,
                AVG(response_time_ms) as avg_response_ms,
                MAX(response_time_ms) as max_response_ms,
                MIN(request_time) as first_request,
                MAX(request_time) as last_request
             FROM api_request_log
             WHERE api_key_id = %d
               AND request_time > DATE_SUB(NOW(), INTERVAL %s)",
            intval($apiKeyID),
            $interval
        );

        return $db->getAssoc($sql);
    }
}
