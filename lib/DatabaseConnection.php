<?php
/**
 * CATS
 * Database Connection Library
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
 * @version    $Id: DatabaseConnection.php 3827 2007-12-11 00:44:43Z andrew $
 */

/**
 *	Database Connector / Database Abstraction Layer
 *	@package    CATS
 *	@subpackage Library
 */
class DatabaseConnection
{
    static private $_instance;
    private $_connection = null;
    private $_queryResult = null;
    private $_timeZone;
    private $_dateDMY;
    private $_inTransaction;


    /**
     * Returns an instance of DatabaseConnection.
     *
     * @return DatabaseConnection Instance of DatabaseConnection.
     */
    public static function getInstance()
    {
        if (self::$_instance == null)
        {
            self::$_instance = new DatabaseConnection();
            self::$_instance->connect();
            self::$_instance->setInTransaction(false);
        }

        // FIXME: Remove Session tight-coupling here.
        if (isset($_SESSION['CATS']) && $_SESSION['CATS']->isLoggedIn())
        {
            self::$_instance->_timeZone = $_SESSION['CATS']->getTimeZoneOffset();
            self::$_instance->_dateDMY = $_SESSION['CATS']->isDateDMY();
        }
        else
        {
            self::$_instance->_timeZone = OFFSET_GMT * -1;
            self::$_instance->_dateDMY = false;
        }

        return self::$_instance;
    }


    /* Prevent this class from being instantiated by any means other
     * than getInstance().
     */
    private function __construct() {}
    private function __clone() {}

    public function setInTransaction($tf)
    {
        return ($this->_inTransaction = $tf);
    }


    /**
     * Returns this instance's connection resource, or null if nonexistant.
     *
     * @return resource This instance's connection resource, or null if
     *                  nonexistant.
     */
    public function getConnection()
    {
        return $this->_connection;
    }

    /**
     * Initiate a connection with the MySQL database. This is called by the
     * constructor.
     *
     * @param string MySQL query or null to operate on the last executed query
     *               for this instance.
     * @return boolean Was the connection successful?
     */
    public function connect()
    {
        $this->_connection = @mysql_connect(
            DATABASE_HOST, DATABASE_USER, DATABASE_PASS
        );
        if (!$this->_connection)
        {
            $error = mysql_error();

            die(
                '<!-- NOSPACEFILTER --><p style="background: #ec3737; padding:'
                . ' 4px; margin-top: 0; font: normal normal bold 12px/130% '
                . 'Arial, Tahoma, sans-serif;">Error Connecting '
                . "to Database</p><pre>\n\n" . $error . "</pre>\n\n"
            );
            return false;
        }
        mysql_set_charset(SQL_CHARACTER_SET, $this->_connection);
        $isDBSelected = @mysql_select_db(DATABASE_NAME, $this->_connection);
        if (!$isDBSelected)
        {
            $error = mysql_error($this->_connection);

            die(
                '<!-- NOSPACEFILTER --><p style="background: #ec3737; '
                . 'padding: 4px; margin-top: 0; font: normal normal bold '
                . '12px/130% Arial, Tahoma, sans-serif;">Error Selecting '
                . "Database</p><pre>\n\n" . $error . "</pre>\n\n"
            );
            return false;
        }

        return true;
    }

    /**
     * Executes a MySQL query against the current connection. Unless
     * $ignoreErrors is true, any failed queies will result in a die().
     *
     * @param string MySQL query or null to operate on the last executed query
     *               for this instance.
     * @return resource MySQL query result. For non-SELECT queries, this will
     *                  return a boolean value indicating whether or not the
     *                  query's execution was successful. SELECT queries can
     *                  also return false indicating a permission error or
     *                  other failure.
     */
    public function query($query, $ignoreErrors = false)
    {
        /* Does our current configuration allow the execution of this query? */
        if (!$this->allowQuery($query))
        {
            return false;
        }

        /* Fix formatted dates and time zones for localization. */
        // FIXME: I don't like rewriting queries....
        $query = $this->_localizationFilter($query);

        if( ini_get('safe_mode') )
        {
			//don't do anything in safe mode
		}
		else
        {
            /* Don't limit the execution time of queries. */
            set_time_limit(0);
        }

        $this->_queryResult = mysql_query($query, $this->_connection);
        if (!$this->_queryResult && !$ignoreErrors)
        {
            $error = mysql_error($this->_connection);

            echo (
                '<!-- NOSPACEFILTER --><p style="background: #ec3737; padding:'
                . ' 4px; margin-top: 0; font: normal normal bold 12px/130%'
                . ' Arial, Tahoma, sans-serif;">Query Error -- Report to System'
                . " Administrator ASAP</p><pre>\n\nMySQL Query Failed: "
                . $error . "\n\n" . $query . "</pre>\n\n"
            );

            echo('<!--');

            trigger_error(
                str_replace("\n", " ", 'MySQL Query Error: ' . $error . " - " . $query)
            );

            echo('-->');

            die();
        }

        return $this->_queryResult;
    }

    /**
     * Executes multiple queries from a string. Each query in the specified
     * string must be terminated with a semicolon (;).
     *
     * @param string MySQL query or null to operate on the last executed query
     *               for this instance.
     * @param string Delimiter to use to split the SQL commands (usually ';')
     * @return void
     */
    public function queryMultiple($string, $delimiter = ';')
    {
        $SQLStatments = explode($delimiter, str_replace("\r\n", "\n", $string));

        foreach ($SQLStatments as $SQL)
        {
            $SQL = trim($SQL);

            if (empty($SQL))
            {
                continue;
            }

            $this->query($SQL);
        }
    }

    /**
     * Returns a single field from a result set, based on the field's row and
     * column number. If a query is not specified, this method will operate on the
     * last executed query for this instance.
     *
     * @param string MySQL query or null to operate on the last executed query
     *               for this instance.
     * @param integer Row number.
     * @param integer Column number.
     * @return array Multi-dimensional associative result set array, or array()
     */
    public function getColumn($query = null, $row, $column)
    {
        if ($query != null)
        {
            $this->query($query);
        }

        $numRows = mysql_num_rows($this->_queryResult);
        if ($numRows === false)
        {
            return false;
        }
        else if ($row >= $numRows)
        {
            return false;
        }
        else if ($row < 0)
        {
            return false;
        }

        return mysql_result($this->_queryResult, $row, $column);
    }

    /**
     * Returns one row from a query's result set in an associative array,
     * starting at the current row pointer. After the call, the row pointer
     * will be incemented by 1 (this is how the mysql_fetch_*() functions
     * work). If a query is not specified, this method will operate on the
     * last executed query for this instance. Specifing a query always resets
     * the row pointer to 0.
     *
     * Example (first call):
     * array(
     *     'firstName'   => 'Will',
     *     'lastName'    => 'Buckner',
     *     'dateCreated' => '05/05/07 4:32 PM'
     * );
     *
     * Example (second call):
     * array(
     *     'firstName'   => 'Asim',
     *     'lastName'    => 'Baig',
     *     'dateCreated' => '05/06/07 3:30 PM'
     * );
     *
     * @param string MySQL query or null to operate on the last executed query
     *               for this instance.
     * @return array Associative result set array, or array() if no records
     *               were returned.
     */
    public function getAssoc($query = null)
    {
        if ($query != null)
        {
            $this->query($query);
        }

        $recordSet = mysql_fetch_assoc($this->_queryResult);

        if (empty($recordSet))
        {
            $recordSet = array();
        }

        return $recordSet;
    }

    /**
     * Returns all rows from a query's result set in a multi-dimensional
     * associative array. If a query is not specified, this method will operate
     * on the last executed query for this instance.
     *
     * Example:
     * array(
     *    0 => array(
     *        'firstName'   => 'Will',
     *        'lastName'    => 'Buckner',
     *        'dateCreated' => '05/05/07 4:32 PM'
     *    ),
     *    1 => array(
     *        'firstName'   => 'Asim',
     *        'lastName'    => 'Baig',
     *        'dateCreated' => '05/06/07 3:30 PM'
     *    ),
     *    ...
     * );
     *
     * @param string MySQL query or null to operate on the last executed query
     *               for this instance.
     * @return array Multi-dimensional associative result set array, or array()
     *               if no records were returned.
     */
    public function getAllAssoc($query = null)
    {
        if ($query != null)
        {
            $this->query($query);
        }

        /* Make sure we always return an array. */
        $recordSetArray = array();

        /* Store all rows in $recordSetArray; */
        while (($recordSet = mysql_fetch_assoc($this->_queryResult)))
        {
            $recordSetArray[] = $recordSet;
        }

        /* Return the multi-dimensional record set array. */
        return $recordSetArray;
    }

    /**
     * Returns the number of rows in a query's result set (regardless of where
     * the current row pointer is).
     *
     * @return integer Total rows in a query's result set.
     */
    public function getNumRows($query = null)
    {
        if ($query != null)
        {
            $this->query($query);
        }

        return mysql_num_rows($this->_queryResult);
    }

    /**
     * Returns true if there are no (more) records in the result set for the
     * last query.
     *
     * @return boolean Are we at the end of the MySQL result set?
     */
    public function isEOF()
    {
        $rowCount = mysql_num_rows($this->_queryResult);
        if (!$rowCount)
        {
            return true;
        }

        return false;
    }

    /**
     * Creates a blocking advisory lock with the specified name. Subsequent
     * calls to this method will block until the previous lock with the same
     * name has been released. THIS DOES NOT ACTUALLY PREVENT READS OR WRITES
     * TO THE DATABASE! This currently only works with MySQL.
     *
     * @param string Name to assign to the lock.
     * @param integer Lock timeout.
     * @return void
     */
    public function getAdvisoryLock($lockName, $timeout = 120)
    {
        $sql = sprintf(
            "SELECT
                GET_LOCK(%s, %s)",
            $this->makeQueryString($lockName),
            $this->makeQueryInteger($timeout)
        );
        $this->query($sql);
    }


    /**
     * Returns true if the blocking advisory lock is free.
     *
     * @param string Name assigned to the lock.
     * @return boolean Has the lock been freed?
     */
    public function isAdvisoryLockFree($lockName)
    {
        $sql = sprintf(
            "SELECT
                IS_FREE_LOCK(%s) AS isFreeLock",
            $this->makeQueryString($lockName)
        );
        $rs = $this->getAssoc($sql);

        if ($rs['isFreeLock'] == 1)
        {
            return true;
        }

        return false;
    }

    /**
     * Releases a blocking advisory lock with the specified name (created with
     * $this->getAdvisoryLock(). This currently only works with MySQL.
     *
     * @param string Name of lock to be released.
     * @return void
     */
    public function releaseAdvisoryLock($lockName)
    {
        $sql = sprintf(
            "SELECT
                RELEASE_LOCK(%s)",
            $this->makeQueryString($lockName)
        );
        $this->query($sql);
    }

    /**
     * Returns the original string escaped for query use.
     *
     * @param string String to process.
     * @return string Original string, escaped for query use.
     */
    public function escapeString($string)
    {
        // FIXME: Security issue, this function is not enough for sanitizing
        // user input. For instance see: 
        // https://johnroach.info/2011/02/17/why-mysql_real_escape_string-isnt-enough-to-stop-sql-injection-attacks/
        // To be replaced with Symfony's stack
        return mysql_real_escape_string($string, $this->_connection);
    }

    /**
     * Returns the original string quoted / escaped for query use.
     *
     * @param string String to process.
     * @return string Original string, escaped / quoted for query use.
     */
    public function makeQueryString($string)
    {
        return "'" . $this->escapeString($string) . "'";
    }

    /**
     * Returns 'NULL' if $string is empty; otherwise, the original string
     * quoted / escaped for query use.
     *
     * @param string String to process.
     * @return string Original string, escaped / quoted for query use, or NULL
     *               for an empty string.
     */
    public function makeQueryStringOrNULL($string)
    {
        $string = trim($string);

        if (empty($string))
        {
            return 'NULL';
        }

        return $this->makeQueryString($string);
    }

    /**
     * Returns 'NULL' if the specified value is equal to -1; otherwise the
     * original value as an integer safe for MySQL. This follows PHP5's integer
     * casting rules. Doubles will be rounded using truncation (1.9999 => 1).
     *
     * @param mixed Value to process.
     * @return integer Value converted to an integer, or 'NULL'.
     */
    public function makeQueryIntegerOrNULL($value)
    {
        if ($value == '-1')
        {
            return 'NULL';
        }

        return (integer) $value;
    }

    /**
     * Returns the original value as an integer safe for MySQL. This follows
     * PHP5's integer casting rules. Doubles will be rounded using truncation
     * (1.9999 => 1).
     *
     * @param mixed Value to process.
     * @return integer Value converted to an integer.
     */
    public function makeQueryInteger($value)
    {
        return (integer) $value;
    }

    /**
     * Returns the original value as a safe MySQL double, rounded to the
     * specified precision. 0.00 is returned for bad values.
     *
     * @param string Double / string value to process.
     * @return string Safe MySQL double, rounded to the specified precision.
     */
    public function makeQueryDouble($value, $precision = false)
    {
        $value = trim($value);

        if (empty($value) || !preg_match('/^-?[0-9]+(?:\.[0-9]+)?$/', $value))
        {
            return '0.0';
        }

        if ($precision !== false)
        {
            $valueAsDouble = round($value, $precision);
            $isAWholeNumber = fmod($valueAsDouble, 1) == 0;
            return number_format($valueAsDouble, $isAWholeNumber ? 0 : 2);
        }

        return (string) $value;
    }

    /**
     * Returns the last error message (value of mysql_error()) for the current
     * MySQL connection.
     *
     * @return string Error message, or '' if no error occurred.
     */
    public function getError()
    {
        return mysql_error($this->_connection);
    }

    /**
     * Returns the last insert's AUTO_INCREMENT key's value for the current
     * database connection connection.
     *
     * @return integer ID generated for an AUTO_INCREMENT column by the
     *         previous INSERT query on success, 0 if the previous query does
     *         not generate an AUTO_INCREMENT value, or false if no database
     *         connection was established.
     */
    public function getLastInsertID()
    {
        return @mysql_insert_id($this->_connection);
    }

    /**
     * Returns the number of rows in the database that were affected by the
     * last query (INSERT / UPDATE / DELETE / etc.).
     *
     * @return integer Number of affected rows by the last executed MySQL
     *                 operation (INSERT / UPDATE / DELETE / etc.).
     */
    public function getAffectedRows()
    {
        return @mysql_affected_rows($this->_connection);
    }

    /**
     * Returns the current RDBMS version, as reported by the RDBMS.
     * The string 'MySQL ' is prepended for MySQL.
     *
     * @return string RDBMS version.
     */
    public function getRDBMSVersion()
    {
        $rs = $this->getAssoc('SELECT VERSION() AS version');
        return 'MySQL ' . $rs['version'];
    }

    /**
     * Returns true if the specified query is allowed by the filter. Currently
     * this is only used to prevent database writes when CATS_SLAVE is enabled.
     *
     * @param string Query to check.
     * @return boolean Is this query allowed by the current configuration?
     */
    public function allowQuery($query)
    {
        if (CATS_SLAVE &&
            preg_match('/^\s*(?:UPDATE|INSERT|DELETE)\s/i', trim($query)))
        {
            return false;
        }

        return true;
    }


    // FIXME: Document me.
    private function _localizationFilter($query)
    {
        /* Fix query to allow time results to be offset by $_timeZone. */
        if (strpos($query , 'SELECT') !== 0)
        {
            return $query;
        }

        // FIXME: This could probably be done better with regexes.
        // FIXME: D M Y support.
        // FIXME: Document this. Any string-manipulation things like this can
        //        get fairly confusing if not documented.
        $newQuery = '';
        while ($query != '')
        {
            /* Does the query contain a DATE_FORMAT()? */
            $dateFormatPosition = strpos($query, 'DATE_FORMAT(');
            if ($dateFormatPosition === false)
            {
                $newQuery .= $query;
                $query = '';
                continue;
            }

            if ($dateFormatPosition > 0)
            {
                $newQuery .= substr($query, 0, strpos($query, 'DATE_FORMAT('));
                $query = substr($query, strpos($query, 'DATE_FORMAT('));
            }

            $working = substr($query, 0, strpos($query, ','));
            $query = substr($query, strpos($query, ','));
            if (strpos(substr($working, 13), '(') === false)
            {
                /* Add or subtract time before the date format depeidng on the
                 * time zone offset. We don't have to do any replacement if the
                 * offset is 0.
                 */
                if ($this->_timeZone > 0)
                {
                    $working = str_replace('DATE_FORMAT(', 'DATE_FORMAT(DATE_ADD(', $working);
                    $working .= ', INTERVAL ' . $this->_timeZone . ' HOUR)';
                }
                else if ($this->_timeZone < 0)
                {
                    $working = str_replace('DATE_FORMAT(', 'DATE_FORMAT(DATE_SUB(', $working);
                    $working .= ', INTERVAL ' . ($this->_timeZone * -1) . ' HOUR)';
                }
            }
            $newQuery .= $working;
        }

        $query = $newQuery;

        /* Replace m-d-y dates with d-m-y dates if we're in dmy mode. */
        if ($this->_dateDMY)
        {
            $query = str_replace('%m-%d-%y', '%d-%m-%y', $query);
            $query = str_replace('%m-%d-%Y', '%d-%m-%Y', $query);
            $query = str_replace('%m/%d/%Y', '%d/%m/%Y', $query);
            $query = str_replace('%m/%d/%y', '%d/%m/%y', $query);
        }

        return $query;
    }

    /**
     * Transaction functions for InnoDB tables.
     */

    public function beginTransaction()
    {
        if (!$this->_inTransaction)
        {
            // Ignore errors (if called for MyISAM, for example)
            $this->query('BEGIN', true);
            return ($this->_inTransaction = true);
        }
        else
        {
            // Already in a transaction
            return false;
        }
    }

    public function commitTransaction()
    {
        if ($this->_inTransaction)
        {
            $this->query('COMMIT', true);
            $this->_inTransaction = false;
            return true;
        }
        else
        {
            // We're not in a transaction
            return false;
        }
    }

    public function rollbackTransaction()
    {
        if ($this->_inTransaction)
        {
            $this->query('ROLLBACK', true);
            $this->_inTransaction = false;
            return true;
        }
        else
        {
            // We're not in a transaction
            return false;
        }
    }
}

?>
