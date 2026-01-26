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
     * Send success response with optional field filtering
     * @param mixed $data Response data
     * @param int $code HTTP status code
     */
    protected function sendSuccess($data, $code = 200)
    {
        // Log successful request if logger is available
        if (isset($this->_requestLogger) && $this->_requestLogger) {
            $this->_requestLogger->logSuccess($code);
        }

        // Apply field selection if requested
        $fields = $this->getFieldSelection();
        if ($fields !== null && is_array($data)) {
            // Handle paginated responses
            if (isset($data['data']) && is_array($data['data'])) {
                $data['data'] = $this->filterFields($data['data'], $fields);
            } else {
                $data = $this->filterFields($data, $fields);
            }
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

    /**
     * Get field selection from request
     * Allows clients to request specific fields via ?fields=id,title,status
     * @return array|null Array of requested fields or null for all fields
     */
    protected function getFieldSelection()
    {
        if (!isset($_GET['fields']) || empty($_GET['fields'])) {
            return null;
        }

        $fields = explode(',', $_GET['fields']);
        return array_map('trim', $fields);
    }

    /**
     * Filter response to only include requested fields
     * @param array $data The data to filter
     * @param array|null $fields Fields to include (null = all)
     * @return array Filtered data
     */
    protected function filterFields($data, $fields)
    {
        if ($fields === null) {
            return $data;
        }

        // Handle single item (associative array)
        if (!isset($data[0])) {
            return $this->filterSingleItem($data, $fields);
        }

        // Handle array of items
        return array_map(function($item) use ($fields) {
            return $this->filterSingleItem($item, $fields);
        }, $data);
    }

    /**
     * Filter a single item to include only specified fields
     * Supports nested fields like "candidate.firstName"
     * @param array $item Single data item
     * @param array $fields Fields to include
     * @return array Filtered item
     */
    private function filterSingleItem($item, $fields)
    {
        $result = [];
        foreach ($fields as $field) {
            // Support nested fields like "candidate.firstName"
            if (strpos($field, '.') !== false) {
                list($parent, $child) = explode('.', $field, 2);
                if (isset($item[$parent]) && is_array($item[$parent])) {
                    if (!isset($result[$parent])) {
                        $result[$parent] = [];
                    }
                    if (isset($item[$parent][$child])) {
                        $result[$parent][$child] = $item[$parent][$child];
                    }
                }
            } else {
                if (array_key_exists($field, $item)) {
                    $result[$field] = $item[$field];
                }
            }
        }
        return $result;
    }

    /**
     * Get sort parameters from request
     * Allows clients to sort via ?sort=dateAdded&order=DESC
     * @param array $allowedFields Fields that can be sorted (for validation)
     * @param string $defaultField Default sort field
     * @param string $defaultOrder Default sort order
     * @return array ['field' => string, 'order' => 'ASC'|'DESC', 'sql' => string]
     */
    protected function getSortParams($allowedFields = [], $defaultField = 'date_created', $defaultOrder = 'DESC')
    {
        $field = isset($_GET['sort']) ? trim($_GET['sort']) : $defaultField;
        $order = isset($_GET['order']) ? strtoupper(trim($_GET['order'])) : $defaultOrder;

        // Validate order
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = $defaultOrder;
        }

        // Convert camelCase to snake_case for database
        $dbField = $this->camelToSnake($field);

        // Validate field if allowedFields provided
        if (!empty($allowedFields) && !in_array($dbField, $allowedFields) && !in_array($field, $allowedFields)) {
            $dbField = $this->camelToSnake($defaultField);
        }

        return [
            'field' => $dbField,
            'order' => $order,
            'sql' => "ORDER BY {$dbField} {$order}"
        ];
    }

    /**
     * Convert camelCase to snake_case
     * Useful for converting API field names to database column names
     * @param string $input camelCase string
     * @return string snake_case string
     */
    protected function camelToSnake($input)
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $input));
    }

    /**
     * Parse query string into SQL WHERE clause
     * Supports: field=value, field>value, field<value, field:value (LIKE)
     * Multiple conditions joined with AND
     *
     * Example: ?query=status:Active,salary>50000,city=Austin
     *
     * @param array $allowedFields Whitelist of queryable fields
     * @return array ['where' => 'AND field = value...', 'params' => [...]]
     */
    protected function parseQueryParams($allowedFields = [])
    {
        if (!isset($_GET['query']) || empty($_GET['query'])) {
            return ['where' => '', 'params' => []];
        }

        $query = $_GET['query'];
        $conditions = explode(',', $query);
        $whereParts = [];
        $params = [];

        foreach ($conditions as $condition) {
            $condition = trim($condition);
            if (empty($condition)) continue;

            // Parse operator and value
            $parsed = $this->parseCondition($condition);
            if (!$parsed) continue;

            $field = $this->camelToSnake($parsed['field']);

            // Validate field if whitelist provided
            if (!empty($allowedFields) && !in_array($field, $allowedFields) && !in_array($parsed['field'], $allowedFields)) {
                continue;
            }

            // Build WHERE clause based on operator
            switch ($parsed['operator']) {
                case '=':
                    $whereParts[] = "{$field} = " . $this->escapeValue($parsed['value']);
                    break;
                case '>':
                    $whereParts[] = "{$field} > " . $this->escapeValue($parsed['value']);
                    break;
                case '<':
                    $whereParts[] = "{$field} < " . $this->escapeValue($parsed['value']);
                    break;
                case '>=':
                    $whereParts[] = "{$field} >= " . $this->escapeValue($parsed['value']);
                    break;
                case '<=':
                    $whereParts[] = "{$field} <= " . $this->escapeValue($parsed['value']);
                    break;
                case ':':  // LIKE search
                    $whereParts[] = "{$field} LIKE " . $this->escapeValue('%' . $parsed['value'] . '%');
                    break;
                case '!=':
                    $whereParts[] = "{$field} != " . $this->escapeValue($parsed['value']);
                    break;
            }
        }

        if (empty($whereParts)) {
            return ['where' => '', 'params' => []];
        }

        return [
            'where' => 'AND ' . implode(' AND ', $whereParts),
            'conditions' => $whereParts
        ];
    }

    /**
     * Parse a single condition like "field>value" or "field:value"
     * @param string $condition The condition string to parse
     * @return array|null Parsed condition with field, operator, value or null if invalid
     */
    private function parseCondition($condition)
    {
        // Try each operator in order (longer operators first)
        $operators = ['>=', '<=', '!=', '>', '<', '=', ':'];

        foreach ($operators as $op) {
            $pos = strpos($condition, $op);
            if ($pos !== false) {
                return [
                    'field' => substr($condition, 0, $pos),
                    'operator' => $op,
                    'value' => substr($condition, $pos + strlen($op))
                ];
            }
        }

        return null;
    }

    /**
     * Escape value for SQL (use database connection if available)
     * @param string $value The value to escape
     * @return string Escaped and quoted value
     */
    private function escapeValue($value)
    {
        // Try to use DatabaseConnection if available
        if (class_exists('DatabaseConnection')) {
            $db = DatabaseConnection::getInstance();
            return $db->makeQueryString($value);
        }

        // Fallback to basic escaping
        return "'" . addslashes($value) . "'";
    }
}
