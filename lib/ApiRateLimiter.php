<?php
/**
 * CATS
 * API Rate Limiter
 *
 * Provides request rate limiting for REST API endpoints.
 * Uses database-backed storage for distributed environments.
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
 * @version    $Id: ApiRateLimiter.php 2026-01-25 $
 */

include_once('./lib/DatabaseConnection.php');

class ApiRateLimiter
{
    // Default limits
    const DEFAULT_REQUESTS_PER_MINUTE = 60;
    const DEFAULT_REQUESTS_PER_HOUR = 1000;

    private $_db;
    private $_apiKeyID;
    private $_requestsPerMinute;
    private $_requestsPerHour;

    /**
     * Constructor
     *
     * @param int|string $identifier      API Key ID (int) or OAuth identifier (string) to track
     * @param int $requestsPerMinute      Requests allowed per minute
     * @param int $requestsPerHour        Requests allowed per hour
     */
    public function __construct($identifier, $requestsPerMinute = null, $requestsPerHour = null)
    {
        $this->_db = DatabaseConnection::getInstance();

        // Support both integer API key IDs and string OAuth identifiers
        if (is_numeric($identifier)) {
            $this->_apiKeyID = intval($identifier);
        } else {
            // For string identifiers (OAuth), generate a negative hash to avoid collision with API keys
            // crc32 returns unsigned int, we negate it to keep it separate from positive API key IDs
            $this->_apiKeyID = -abs(crc32($identifier));
        }

        $this->_requestsPerMinute = $requestsPerMinute ?: self::DEFAULT_REQUESTS_PER_MINUTE;
        $this->_requestsPerHour = $requestsPerHour ?: self::DEFAULT_REQUESTS_PER_HOUR;
    }

    /**
     * Check if request is allowed under rate limits
     *
     * @return array ['allowed' => bool, 'remaining' => int, 'reset' => timestamp, 'retry_after' => seconds]
     */
    public function checkLimit()
    {
        // Get request counts from last minute and hour
        $minuteAgo = date('Y-m-d H:i:s', time() - 60);
        $hourAgo = date('Y-m-d H:i:s', time() - 3600);

        $sql = sprintf(
            "SELECT
                (SELECT COUNT(*) FROM api_request_log
                 WHERE api_key_id = %d AND request_time > '%s') as minute_count,
                (SELECT COUNT(*) FROM api_request_log
                 WHERE api_key_id = %d AND request_time > '%s') as hour_count",
            $this->_apiKeyID,
            $minuteAgo,
            $this->_apiKeyID,
            $hourAgo
        );

        $result = $this->_db->getAssoc($sql);

        $minuteCount = intval($result['minute_count'] ?? 0);
        $hourCount = intval($result['hour_count'] ?? 0);

        // Check minute limit first (more restrictive)
        if ($minuteCount >= $this->_requestsPerMinute) {
            return [
                'allowed' => false,
                'limit' => $this->_requestsPerMinute,
                'remaining' => 0,
                'reset' => time() + 60,
                'retry_after' => 60 - (time() % 60),
                'reason' => 'Rate limit exceeded: ' . $this->_requestsPerMinute . ' requests per minute'
            ];
        }

        // Check hour limit
        if ($hourCount >= $this->_requestsPerHour) {
            return [
                'allowed' => false,
                'limit' => $this->_requestsPerHour,
                'remaining' => 0,
                'reset' => time() + 3600,
                'retry_after' => 3600 - (time() % 3600),
                'reason' => 'Rate limit exceeded: ' . $this->_requestsPerHour . ' requests per hour'
            ];
        }

        // Request is allowed
        return [
            'allowed' => true,
            'limit' => $this->_requestsPerMinute,
            'remaining' => $this->_requestsPerMinute - $minuteCount - 1,
            'reset' => time() + 60,
            'retry_after' => 0,
            'reason' => null
        ];
    }

    /**
     * Get rate limit headers for response
     *
     * @param array $limitInfo Result from checkLimit()
     * @return array Headers to add to response
     */
    public static function getHeaders($limitInfo)
    {
        $headers = [
            'X-RateLimit-Limit' => $limitInfo['limit'],
            'X-RateLimit-Remaining' => max(0, $limitInfo['remaining']),
            'X-RateLimit-Reset' => $limitInfo['reset']
        ];

        if (!$limitInfo['allowed']) {
            $headers['Retry-After'] = $limitInfo['retry_after'];
        }

        return $headers;
    }
}
