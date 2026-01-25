<?php
/**
 * CATS
 * API Helper Trait
 *
 * Common helper methods for API module.
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

trait ApiHelpers
{
    /**
     * Get request headers (works on all servers)
     * @return array
     */
    protected function getRequestHeaders()
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
     * @return array
     */
    protected function getRequestBody()
    {
        $json = file_get_contents('php://input');
        return json_decode($json, true) ?: [];
    }

    /**
     * Send success response
     * @param mixed $data Response data
     * @param int $code HTTP status code
     */
    protected function sendSuccess($data, $code = 200)
    {
        // Log successful request if logger is available
        if (isset($this->_requestLogger) && $this->_requestLogger) {
            $this->_requestLogger->logSuccess($code);
        }

        http_response_code($code);
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Send error response
     * @param string $message Error message
     * @param int $code HTTP status code
     */
    protected function sendError($message, $code = 400)
    {
        // Log failed request if logger is available
        if (isset($this->_requestLogger) && $this->_requestLogger) {
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

    /**
     * Get pagination parameters from request
     * @return array ['page' => int, 'limit' => int, 'offset' => int]
     */
    protected function getPaginationParams()
    {
        $page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
        $limit = isset($_GET['limit']) ? min(100, max(1, intval($_GET['limit']))) : 25;
        $offset = ($page - 1) * $limit;

        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    /**
     * Send paginated response
     * @param array $items All items (will be sliced)
     * @param int $page Current page
     * @param int $limit Items per page
     */
    protected function sendPaginatedResponse($items, $page, $limit)
    {
        $total = count($items);
        $offset = ($page - 1) * $limit;
        $pagedItems = array_slice($items, $offset, $limit);

        $this->sendSuccess([
            'total' => $total,
            'page' => $page,
            'limit' => $limit,
            'data' => $pagedItems
        ]);
    }
}
