<?php
/**
 * CATS
 * Appointments Library
 *
 * Provides a Bullhorn-compatible interface for managing appointments/calendar events.
 * Supports scheduling meetings, calls, interviews, and other appointment types
 * with optional association to candidates, contacts, companies, or job orders.
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * Copyright (C) 2024 OpenCATS Community
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
 */

/**
 * Appointments Library
 *
 * Manages appointments with Bullhorn-compatible API support.
 *
 * @package    CATS
 * @subpackage Library
 */
class Appointments
{
    /** @var DatabaseConnection Database connection instance */
    private $_db;

    /** @var int Site ID for multi-tenant support */
    private $_siteID;

    /* Appointment type constants - Bullhorn compatible */
    const TYPE_MEETING = 'Meeting';
    const TYPE_CALL = 'Call';
    const TYPE_INTERVIEW = 'Interview';
    const TYPE_OTHER = 'Other';

    /* Person type constants for association */
    const PERSON_TYPE_CANDIDATE = 'candidate';
    const PERSON_TYPE_CONTACT = 'contact';
    const PERSON_TYPE_LEAD = 'lead';

    /**
     * Constructor
     *
     * @param int $siteID Site ID for multi-tenant isolation
     */
    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }

    /**
     * Adds a new appointment.
     *
     * @param string $title Appointment title/subject
     * @param string $startDate Start date/time (ISO 8601 or MySQL format)
     * @param string $endDate End date/time (ISO 8601 or MySQL format)
     * @param int $ownerID User ID who owns/created the appointment
     * @param array $data Optional additional data:
     *                    - description: Text description
     *                    - type: Meeting, Call, Interview, Other
     *                    - allDay: Boolean for all-day event
     *                    - location: Location string
     *                    - personType: candidate, contact, or lead
     *                    - personID: ID of the associated person
     *                    - jobOrderID: Associated job order ID
     *                    - isPublic: Boolean for public visibility
     * @return int|false Appointment ID on success, false on failure
     */
    public function add($title, $startDate, $endDate, $ownerID, $data = array())
    {
        /* Parse optional fields with defaults */
        $description = isset($data['description']) ? $data['description'] : '';
        $type = isset($data['type']) ? $data['type'] : self::TYPE_OTHER;
        $allDay = isset($data['allDay']) ? ($data['allDay'] ? 1 : 0) : 0;
        $location = isset($data['location']) ? $data['location'] : '';
        $personType = isset($data['personType']) ? $data['personType'] : null;
        $personID = isset($data['personID']) ? intval($data['personID']) : null;
        $jobOrderID = isset($data['jobOrderID']) ? intval($data['jobOrderID']) : null;

        /* Validate type */
        $validTypes = self::getTypes();
        if (!in_array($type, $validTypes))
        {
            $type = self::TYPE_OTHER;
        }

        /* Validate personType if provided */
        if ($personType !== null)
        {
            $validPersonTypes = array(
                self::PERSON_TYPE_CANDIDATE,
                self::PERSON_TYPE_CONTACT,
                self::PERSON_TYPE_LEAD
            );
            if (!in_array($personType, $validPersonTypes))
            {
                $personType = null;
                $personID = null;
            }
        }

        /* Convert date formats to MySQL datetime */
        $startDate = $this->normalizeDateTime($startDate);
        $endDate = $this->normalizeDateTime($endDate);

        $sql = sprintf(
            "INSERT INTO appointment (
                site_id,
                title,
                description,
                type,
                start_date,
                end_date,
                all_day,
                location,
                person_type,
                person_id,
                joborder_id,
                owner,
                date_created,
                date_modified
            )
            VALUES (
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                %s,
                NOW(),
                NOW()
            )",
            $this->_siteID,
            $this->_db->makeQueryString($title),
            $this->_db->makeQueryString($description),
            $this->_db->makeQueryString($type),
            $this->_db->makeQueryString($startDate),
            $this->_db->makeQueryString($endDate),
            $allDay,
            $this->_db->makeQueryString($location),
            $personType !== null ? $this->_db->makeQueryString($personType) : 'NULL',
            $personID !== null ? $this->_db->makeQueryInteger($personID) : 'NULL',
            $jobOrderID !== null ? $this->_db->makeQueryInteger($jobOrderID) : 'NULL',
            $this->_db->makeQueryInteger($ownerID)
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return false;
        }

        return $this->_db->getLastInsertID();
    }

    /**
     * Returns a single appointment with full details.
     *
     * @param int $appointmentID Appointment ID
     * @return array|false Appointment data or false if not found
     */
    public function get($appointmentID)
    {
        $sql = sprintf(
            "SELECT
                appointment.appointment_id AS appointmentID,
                appointment.site_id AS siteID,
                appointment.title AS title,
                appointment.description AS description,
                appointment.type AS type,
                DATE_FORMAT(
                    appointment.start_date, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS startDate,
                DATE_FORMAT(
                    appointment.end_date, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS endDate,
                appointment.all_day AS allDay,
                appointment.location AS location,
                appointment.person_type AS personType,
                appointment.person_id AS personID,
                appointment.joborder_id AS jobOrderID,
                appointment.owner AS ownerID,
                appointment.status AS status,
                DATE_FORMAT(
                    appointment.date_created, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateCreated,
                DATE_FORMAT(
                    appointment.date_modified, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                appointment
            LEFT JOIN user AS owner_user
                ON appointment.owner = owner_user.user_id
            WHERE
                appointment.appointment_id = %s
            AND
                appointment.site_id = %s",
            $this->_db->makeQueryInteger($appointmentID),
            $this->_siteID
        );

        $result = $this->_db->getAssoc($sql);

        if (empty($result))
        {
            return false;
        }

        /* Convert allDay to boolean */
        $result['allDay'] = (bool) $result['allDay'];

        return $result;
    }

    /**
     * Returns appointments for a specific owner within a date range.
     *
     * @param int $ownerID Owner user ID
     * @param string|null $startDate Filter start date (optional)
     * @param string|null $endDate Filter end date (optional)
     * @return array Array of appointments
     */
    public function getByOwner($ownerID, $startDate = null, $endDate = null)
    {
        $dateWhere = '';

        if ($startDate !== null)
        {
            $startDate = $this->normalizeDateTime($startDate);
            $dateWhere .= sprintf(
                " AND appointment.start_date >= %s",
                $this->_db->makeQueryString($startDate)
            );
        }

        if ($endDate !== null)
        {
            $endDate = $this->normalizeDateTime($endDate);
            $dateWhere .= sprintf(
                " AND appointment.end_date <= %s",
                $this->_db->makeQueryString($endDate)
            );
        }

        $sql = sprintf(
            "SELECT
                appointment.appointment_id AS appointmentID,
                appointment.site_id AS siteID,
                appointment.title AS title,
                appointment.description AS description,
                appointment.type AS type,
                DATE_FORMAT(
                    appointment.start_date, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS startDate,
                DATE_FORMAT(
                    appointment.end_date, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS endDate,
                appointment.all_day AS allDay,
                appointment.location AS location,
                appointment.person_type AS personType,
                appointment.person_id AS personID,
                appointment.joborder_id AS jobOrderID,
                appointment.owner AS ownerID,
                appointment.status AS status,
                DATE_FORMAT(
                    appointment.date_created, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateCreated,
                DATE_FORMAT(
                    appointment.date_modified, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                appointment
            LEFT JOIN user AS owner_user
                ON appointment.owner = owner_user.user_id
            WHERE
                appointment.owner = %s
            AND
                appointment.site_id = %s
            %s
            ORDER BY
                appointment.start_date ASC",
            $this->_db->makeQueryInteger($ownerID),
            $this->_siteID,
            $dateWhere
        );

        $results = $this->_db->getAllAssoc($sql);

        /* Convert boolean fields */
        foreach ($results as &$row)
        {
            $row['allDay'] = (bool) $row['allDay'];
        }

        return $results;
    }

    /**
     * Returns appointments for a specific person (candidate, contact, or lead).
     *
     * @param string $personType Person type (candidate, contact, lead)
     * @param int $personID Person ID
     * @param int $limit Maximum records to return
     * @param int $offset Number of records to skip
     * @return array Array of appointments
     */
    public function getByPerson($personType, $personID, $limit = 100, $offset = 0)
    {
        $sql = sprintf(
            "SELECT
                appointment.appointment_id AS appointmentID,
                appointment.site_id AS siteID,
                appointment.title AS title,
                appointment.description AS description,
                appointment.type AS type,
                DATE_FORMAT(
                    appointment.start_date, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS startDate,
                DATE_FORMAT(
                    appointment.end_date, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS endDate,
                appointment.all_day AS allDay,
                appointment.location AS location,
                appointment.person_type AS personType,
                appointment.person_id AS personID,
                appointment.joborder_id AS jobOrderID,
                appointment.owner AS ownerID,
                appointment.status AS status,
                DATE_FORMAT(
                    appointment.date_created, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateCreated,
                DATE_FORMAT(
                    appointment.date_modified, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                appointment
            LEFT JOIN user AS owner_user
                ON appointment.owner = owner_user.user_id
            WHERE
                appointment.person_type = %s
            AND
                appointment.person_id = %s
            AND
                appointment.site_id = %s
            ORDER BY
                appointment.start_date DESC
            LIMIT %s OFFSET %s",
            $this->_db->makeQueryString($personType),
            $this->_db->makeQueryInteger($personID),
            $this->_siteID,
            $this->_db->makeQueryInteger($limit),
            $this->_db->makeQueryInteger($offset)
        );

        $results = $this->_db->getAllAssoc($sql);

        /* Convert boolean fields */
        foreach ($results as &$row)
        {
            $row['allDay'] = (bool) $row['allDay'];
        }

        return $results;
    }

    /**
     * Returns all appointments with optional owner filter and pagination.
     *
     * @param int $limit Maximum records to return
     * @param int $offset Number of records to skip
     * @param int|null $ownerID Filter by owner user ID (optional)
     * @return array Array of appointments
     */
    public function getAll($limit = 100, $offset = 0, $ownerID = null)
    {
        $ownerWhere = '';
        if ($ownerID !== null)
        {
            $ownerWhere = sprintf(
                " AND appointment.owner = %s",
                $this->_db->makeQueryInteger($ownerID)
            );
        }

        $sql = sprintf(
            "SELECT
                appointment.appointment_id AS appointmentID,
                appointment.site_id AS siteID,
                appointment.title AS title,
                appointment.description AS description,
                appointment.type AS type,
                DATE_FORMAT(
                    appointment.start_date, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS startDate,
                DATE_FORMAT(
                    appointment.end_date, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS endDate,
                appointment.all_day AS allDay,
                appointment.location AS location,
                appointment.person_type AS personType,
                appointment.person_id AS personID,
                appointment.joborder_id AS jobOrderID,
                appointment.owner AS ownerID,
                appointment.status AS status,
                DATE_FORMAT(
                    appointment.date_created, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateCreated,
                DATE_FORMAT(
                    appointment.date_modified, '%%Y-%%m-%%dT%%H:%%i:%%s'
                ) AS dateModified,
                owner_user.first_name AS ownerFirstName,
                owner_user.last_name AS ownerLastName
            FROM
                appointment
            LEFT JOIN user AS owner_user
                ON appointment.owner = owner_user.user_id
            WHERE
                appointment.site_id = %s
            %s
            ORDER BY
                appointment.start_date DESC
            LIMIT %s OFFSET %s",
            $this->_siteID,
            $ownerWhere,
            $this->_db->makeQueryInteger($limit),
            $this->_db->makeQueryInteger($offset)
        );

        $results = $this->_db->getAllAssoc($sql);

        /* Convert boolean fields */
        foreach ($results as &$row)
        {
            $row['allDay'] = (bool) $row['allDay'];
        }

        return $results;
    }

    /**
     * Updates an appointment.
     *
     * @param int $appointmentID Appointment ID
     * @param array $data Array of fields to update:
     *                    - title, description, type, startDate, endDate,
     *                    - allDay, location, personType, personID, jobOrderID,
     *                    - isPublic
     * @return bool True on success, false on failure
     */
    public function update($appointmentID, $data)
    {
        /* Verify appointment exists */
        $existing = $this->get($appointmentID);
        if (empty($existing))
        {
            return false;
        }

        $updates = array();

        if (isset($data['title']))
        {
            $updates[] = sprintf(
                "title = %s",
                $this->_db->makeQueryString($data['title'])
            );
        }

        if (isset($data['description']))
        {
            $updates[] = sprintf(
                "description = %s",
                $this->_db->makeQueryString($data['description'])
            );
        }

        if (isset($data['type']))
        {
            $validTypes = self::getTypes();
            if (in_array($data['type'], $validTypes))
            {
                $updates[] = sprintf(
                    "type = %s",
                    $this->_db->makeQueryString($data['type'])
                );
            }
        }

        if (isset($data['startDate']))
        {
            $startDate = $this->normalizeDateTime($data['startDate']);
            $updates[] = sprintf(
                "start_date = %s",
                $this->_db->makeQueryString($startDate)
            );
        }

        if (isset($data['endDate']))
        {
            $endDate = $this->normalizeDateTime($data['endDate']);
            $updates[] = sprintf(
                "end_date = %s",
                $this->_db->makeQueryString($endDate)
            );
        }

        if (isset($data['allDay']))
        {
            $updates[] = sprintf(
                "all_day = %s",
                $data['allDay'] ? '1' : '0'
            );
        }

        if (isset($data['location']))
        {
            $updates[] = sprintf(
                "location = %s",
                $this->_db->makeQueryString($data['location'])
            );
        }

        if (array_key_exists('personType', $data))
        {
            if ($data['personType'] === null)
            {
                $updates[] = "person_type = NULL";
                $updates[] = "person_id = NULL";
            }
            else
            {
                $validPersonTypes = array(
                    self::PERSON_TYPE_CANDIDATE,
                    self::PERSON_TYPE_CONTACT,
                    self::PERSON_TYPE_LEAD
                );
                if (in_array($data['personType'], $validPersonTypes))
                {
                    $updates[] = sprintf(
                        "person_type = %s",
                        $this->_db->makeQueryString($data['personType'])
                    );
                }
            }
        }

        if (array_key_exists('personID', $data))
        {
            if ($data['personID'] === null)
            {
                $updates[] = "person_id = NULL";
            }
            else
            {
                $updates[] = sprintf(
                    "person_id = %s",
                    $this->_db->makeQueryInteger($data['personID'])
                );
            }
        }

        if (array_key_exists('jobOrderID', $data))
        {
            if ($data['jobOrderID'] === null)
            {
                $updates[] = "joborder_id = NULL";
            }
            else
            {
                $updates[] = sprintf(
                    "joborder_id = %s",
                    $this->_db->makeQueryInteger($data['jobOrderID'])
                );
            }
        }

        if (isset($data['status']))
        {
            $updates[] = sprintf(
                "status = %s",
                $this->_db->makeQueryString($data['status'])
            );
        }

        if (empty($updates))
        {
            return true; /* Nothing to update */
        }

        $updates[] = "date_modified = NOW()";

        $sql = sprintf(
            "UPDATE
                appointment
            SET
                %s
            WHERE
                appointment_id = %s
            AND
                site_id = %s",
            implode(', ', $updates),
            $this->_db->makeQueryInteger($appointmentID),
            $this->_siteID
        );

        return (bool) $this->_db->query($sql);
    }

    /**
     * Deletes an appointment.
     *
     * @param int $appointmentID Appointment ID
     * @return bool True on success, false on failure
     */
    public function delete($appointmentID)
    {
        /* Verify appointment exists */
        $existing = $this->get($appointmentID);
        if (empty($existing))
        {
            return false;
        }

        $sql = sprintf(
            "DELETE FROM
                appointment
            WHERE
                appointment_id = %s
            AND
                site_id = %s",
            $this->_db->makeQueryInteger($appointmentID),
            $this->_siteID
        );

        return (bool) $this->_db->query($sql);
    }

    /**
     * Returns the count of appointments matching the given owner filter.
     *
     * @param int|null $ownerID Filter by owner user ID (optional)
     * @return int Number of matching appointments
     */
    public function getCount($ownerID = null)
    {
        $ownerWhere = '';
        if ($ownerID !== null)
        {
            $ownerWhere = sprintf(
                " AND appointment.owner = %s",
                $this->_db->makeQueryInteger($ownerID)
            );
        }

        $sql = sprintf(
            "SELECT
                COUNT(*) AS totalCount
            FROM
                appointment
            WHERE
                appointment.site_id = %s
            %s",
            $this->_siteID,
            $ownerWhere
        );

        $result = $this->_db->getAssoc($sql);

        if (empty($result))
        {
            return 0;
        }

        return (int) $result['totalCount'];
    }

    /**
     * Returns array of all valid appointment types.
     *
     * @return array Array of type strings
     */
    public static function getTypes()
    {
        return array(
            self::TYPE_MEETING,
            self::TYPE_CALL,
            self::TYPE_INTERVIEW,
            self::TYPE_OTHER
        );
    }

    /**
     * Normalizes a date/time string to MySQL datetime format.
     *
     * Supports ISO 8601 format (2026-01-25T10:00:00) and standard MySQL format.
     *
     * @param string $dateTime Input date/time string
     * @return string MySQL-formatted datetime string
     */
    private function normalizeDateTime($dateTime)
    {
        if (empty($dateTime))
        {
            return date('Y-m-d H:i:s');
        }

        /* Replace T separator with space for MySQL */
        $normalized = str_replace('T', ' ', $dateTime);

        /* Remove timezone suffix if present (e.g., +00:00, Z) */
        $normalized = preg_replace('/[+-]\d{2}:\d{2}$/', '', $normalized);
        $normalized = rtrim($normalized, 'Z');

        /* Validate the date format */
        $timestamp = strtotime($normalized);
        if ($timestamp === false)
        {
            return date('Y-m-d H:i:s');
        }

        return date('Y-m-d H:i:s', $timestamp);
    }
}

?>
