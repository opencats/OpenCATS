<?php
/**
 * CATS
 * API Response Helper
 *
 * Provides consistent JSON response formatting for REST API.
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
 * @version    $Id: ApiResponse.php 2026-01-25 $
 */

class ApiResponse
{
    /**
     * Send success response
     *
     * @param mixed $data    Response data
     * @param int   $code    HTTP status code
     */
    public static function success($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send error response
     *
     * @param string $message Error message
     * @param int    $code    HTTP status code
     * @param array  $details Additional error details
     */
    public static function error($message, $code = 400, $details = null)
    {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        $response = [
            'error' => true,
            'message' => $message,
            'code' => $code
        ];
        if ($details !== null) {
            $response['details'] = $details;
        }
        echo json_encode($response, JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Send paginated response
     *
     * @param array $data   Items array
     * @param int   $total  Total count
     * @param int   $offset Current offset
     * @param int   $limit  Items per page
     */
    public static function paginated($data, $total, $offset = 0, $limit = 100)
    {
        self::success([
            'total' => $total,
            'count' => count($data),
            'offset' => $offset,
            'limit' => $limit,
            'data' => $data
        ]);
    }
}
