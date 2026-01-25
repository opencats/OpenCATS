<?php
/**
 * API Response Helper
 *
 * Provides consistent JSON response formatting for REST API.
 *
 * @package    OpenCATS
 * @subpackage API
 * @license    CPAL-1.0
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
