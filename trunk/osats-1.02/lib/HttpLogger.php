<?php
/**
 * OSATS
 */

 /**
 *	HTTPLogger Library
 *	@package    OSATS
 *	@subpackage Library
 */
class HTTPLogger
{
    /* Prevent this class from being instantiated. */
    // FIXME: Make me not static.
    private function __construct() {}
    private function __clone() {}


    /**
     * Adds an entry to the HTTP access log table.
     *
     * @param integer Request / log entry type ID.
     * @param integer Site ID.
     * @return boolean Did the query execute successfully?
     */
    public static function addHTTPLog($type, $siteID)
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "INSERT INTO http_log (
                remote_addr,
                http_user_agent,
                script_filename,
                request_method,
                query_string,
                request_uri,
                script_name,
                log_type,
                site_id,
                date
            )
            VALUES(
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s
            )",
            $db->makeQueryString(@$_SERVER['REMOTE_ADDR']),
            $db->makeQueryString(@$_SERVER['HTTP_USER_AGENT']),
            $db->makeQueryString(@$_SERVER['SCRIPT_FILENAME']),
            $db->makeQueryString(@$_SERVER['REQUEST_METHOD']),
            $db->makeQueryString(@$_SERVER['QUERY_STRING']),
            $db->makeQueryString(@$_SERVER['REQUEST_URI']),
            $db->makeQueryString(@$_SERVER['SCRIPT_NAME']),
            $db->makeQueryInteger($type),
            $db->makeQueryInteger($siteID),
            $db->makeQueryString(date('c'))
        );

        return (boolean) $db->query($sql);
    }

    /**
     * Returns an HTTP log type ID from a specified type name.
     *
     * @param string HTTP log type name.
     * @return integer HTTP log type ID or -1 if not found.
     */
    public static function getHTTPLogTypeIDByName($logTypeName)
    {
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "SELECT
                log_type_id
            FROM
                http_log_types
            WHERE
            (
                name = %s
                OR default_log_type = 1
            )
            ORDER BY
                default_log_type DESC",
            $db->makeQueryString($logTypeName)
        );

        $result = $db->getColumn($sql, 0, 0);
        if ($result === false)
        {
            return -1;
        }

        return $result;
    }
}