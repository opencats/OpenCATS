<?php
/**
 * CATS
 * Login Activity Library
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
 * @version    $Id: LoginActivity.php 3587 2007-11-13 03:55:57Z will $
 */

include_once('./lib/Pager.php');
include_once('./lib/BrowserDetection.php');

/**
 *	Login Activity Pager
 *	@package    CATS
 *	@subpackage Library
 */
class LoginActivityPager extends Pager
{
    private $_siteID;
    private $_db;
    private $_successful;


    public function __construct($rowsPerPage, $currentPage, $siteID, $successful = true)
    {
        $this->_db = DatabaseConnection::getInstance();
        $this->_siteID = $siteID;
        $this->_successful = $successful;

        $this->_sortByFields = array(
            'firstName',
            'lastName',
            'ip',
            'shortUserAgent',
            'dateSort'
        );

        /* How many entries do we have? */
        $sql = sprintf(
            "SELECT
                COUNT(*) AS count
            FROM
                user_login
            LEFT JOIN user
                ON user_login.user_id = user.user_id
            WHERE
                user_login.successful = %s
            AND
                user.is_test_user = 0
            AND
                user_login.site_id = %s",
            ($this->_successful ? '1' : '0'),
            $siteID
        );
        $rs = $this->_db->getAssoc($sql);

        /* Pass "Login Activity By Most Recent"-specific parameters to Pager
         * constructor.
         */
        parent::__construct($rs['count'], $rowsPerPage, $currentPage);
    }

    /**
     * Updates hostname for a user.
     *
     * @param userLoginID
     * @param hostName
     * @return array contacts data
     */
     public function updateHostName($userLoginID, $hostName)
     {
        $sql = sprintf(
            "UPDATE
                user_login
             SET
                user_login.host = %s
             WHERE
                user_login.user_login_id = %s
             AND
                user_login.site_id = %s
             ",
             $this->_db->makeQueryString($hostName),
             $userLoginID,
             $this->_siteID
          );

          $this->_db->query($sql);
     }

    /**
     * Returns the current page of login activity.
     *
     * @return array contacts data
     */
    public function getPage()
    {
        $sql = sprintf(
            "SELECT
                user_login.user_login_id AS userLoginID,
                user_login.user_id AS userID,
                user_login.ip AS ip,
                user_login.user_agent AS shortUserAgent,
                DATE_FORMAT(
                    user_login.date, '%%m-%%d-%%y (%%h:%%i %%p)'
                ) AS date,
                user_login.date AS dateSort,
                user_login.host AS hostname,
                user.first_name AS firstName,
                user.last_name AS lastName
            FROM
                user_login
            LEFT JOIN user
                ON user_login.user_id = user.user_id
            WHERE
                user_login.successful = %s
            AND
                user.is_test_user = 0
            AND
                user_login.site_id = %s
            AND
                user.site_id = %s
            ORDER BY
                %s %s
            LIMIT %s, %s",
            ($this->_successful ? '1' : '0'),
            $this->_siteID,
            $this->_siteID,
            $this->_sortBy,
            $this->_sortDirection,
            $this->_thisPageStartRow,
            $this->_rowsPerPage
        );

        $rs = $this->_db->getAllAssoc($sql);

        foreach ($rs as $rowIndex => $row)
        {
            if (empty($row['hostname']))
            {
                if (ENABLE_HOSTNAME_LOOKUP)
                {
                    $rs[$rowIndex]['hostname'] = @gethostbyaddr($row['ip']);
                    if (empty($rs[$rowIndex]['hostname']))
                    {
                        $rs[$rowIndex]['hostname'] = '(unresolvable)';
                    }

                    $this->updateHostName($row['userLoginID'], $row['hostname']);
                }
                else
                {
                    $rs[$rowIndex]['hostname'] = $row['ip'];
                }
            }

            if ($row['hostname'] == '(unresolvable)')
            {
               $rs[$rowIndex]['hostname'] = '';
            }

            $rs[$rowIndex]['shortUserAgent'] = implode(
                ' ', BrowserDetection::detect($row['shortUserAgent'])
            );
        }

        return $rs;
    }
}
?>
