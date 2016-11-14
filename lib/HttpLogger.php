<?php
/**
 * CATS
 * HTTPLogger (like Apache logs, but per client with typing).
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
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
 * @version    $Id: HttpLogger.php 3587 2007-11-13 03:55:57Z will $
 */

 /**
 *	HTTPLogger Library
 *	@package    CATS
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
            $db->makeQueryString(date("Y-m-d H:i:s"))
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

?>
