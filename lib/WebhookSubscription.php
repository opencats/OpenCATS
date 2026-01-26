<?php
/**
 * CATS
 * Webhook Subscription Library
 *
 * Manages webhook subscriptions for Bullhorn-compatible event notifications.
 * Handles subscription CRUD, event matching, delivery logging, and queue management.
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
 * @version    $Id: WebhookSubscription.php 2026-01-25 $
 */

include_once('./lib/DatabaseConnection.php');

/**
 * WebhookSubscription Library
 *
 * Manages webhook subscriptions with full CRUD operations, event matching,
 * delivery logging, and asynchronous queue management.
 *
 * @package    CATS
 * @subpackage Library
 */
class WebhookSubscription
{
    /** @var DatabaseConnection Database connection instance */
    private $_db;

    /** @var int Site ID for multi-tenant support */
    private $_siteID;

    /* Event type constants */
    const EVENT_CREATE = 'create';
    const EVENT_UPDATE = 'update';
    const EVENT_DELETE = 'delete';

    /* Entity type constants */
    const ENTITY_CANDIDATE = 'candidate';
    const ENTITY_JOBORDER = 'joborder';
    const ENTITY_COMPANY = 'company';
    const ENTITY_CONTACT = 'contact';
    const ENTITY_PLACEMENT = 'placement';
    const ENTITY_JOBSUBMISSION = 'jobsubmission';
    const ENTITY_NOTE = 'note';
    const ENTITY_APPOINTMENT = 'appointment';
    const ENTITY_TASK = 'task';
    const ENTITY_TEARSHEET = 'tearsheet';

    /* Delivery status constants */
    const STATUS_PENDING = 'pending';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const STATUS_RETRYING = 'retrying';

    /**
     * Constructor
     *
     * @param int $siteID Site ID for multi-tenant isolation
     */
    public function __construct($siteID)
    {
        $this->_siteID = intval($siteID);
        $this->_db = DatabaseConnection::getInstance();
    }

    // ========================================================================
    // CRUD METHODS
    // ========================================================================

    /**
     * Create a new webhook subscription
     *
     * @param string $name        Friendly name for the subscription
     * @param string $entityType  Entity type (candidate, joborder, etc.)
     * @param array  $eventTypes  Array of event types (create, update, delete)
     * @param string $callbackUrl URL to POST events to
     * @param int    $userID      User ID who created this subscription
     * @param string $secret      Optional secret for HMAC signature verification
     * @return int|false          New subscription ID on success, false on failure
     */
    public function add($name, $entityType, $eventTypes, $callbackUrl, $userID, $secret = null)
    {
        /* Validate entity type */
        if (!$this->isValidEntityType($entityType))
        {
            return false;
        }

        /* Validate event types */
        $validEventTypes = array();
        foreach ($eventTypes as $eventType)
        {
            if ($this->isValidEventType($eventType))
            {
                $validEventTypes[] = $eventType;
            }
        }

        if (empty($validEventTypes))
        {
            return false;
        }

        /* Convert event types array to comma-separated string */
        $eventTypesString = implode(',', $validEventTypes);

        $sql = sprintf(
            "INSERT INTO webhook_subscriptions
             (site_id, name, entity_type, event_types, callback_url, secret, is_active, created_by, date_created)
             VALUES (%d, %s, %s, %s, %s, %s, 1, %d, NOW())",
            $this->_siteID,
            $this->_db->makeQueryString($name),
            $this->_db->makeQueryString($entityType),
            $this->_db->makeQueryString($eventTypesString),
            $this->_db->makeQueryString($callbackUrl),
            $secret !== null ? $this->_db->makeQueryString($secret) : 'NULL',
            intval($userID)
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return false;
        }

        return $this->_db->getLastInsertID();
    }

    /**
     * Get a single subscription by ID
     *
     * @param int $subscriptionID Subscription ID
     * @return array|null         Subscription data or null if not found
     */
    public function get($subscriptionID)
    {
        $sql = sprintf(
            "SELECT
                subscription_id AS subscriptionID,
                site_id AS siteID,
                name,
                entity_type AS entityType,
                event_types AS eventTypes,
                callback_url AS callbackUrl,
                secret,
                is_active AS isActive,
                created_by AS createdBy,
                DATE_FORMAT(date_created, '%%Y-%%m-%%dT%%H:%%i:%%s') AS dateCreated,
                DATE_FORMAT(date_modified, '%%Y-%%m-%%dT%%H:%%i:%%s') AS dateModified
             FROM webhook_subscriptions
             WHERE subscription_id = %d
               AND site_id = %d",
            intval($subscriptionID),
            $this->_siteID
        );

        $result = $this->_db->getAssoc($sql);

        if (!$result || empty($result))
        {
            return null;
        }

        /* Convert event_types string to array */
        $result['eventTypesArray'] = explode(',', $result['eventTypes']);
        $result['isActive'] = (bool) $result['isActive'];

        return $result;
    }

    /**
     * Get all subscriptions with optional filters and pagination
     *
     * @param int         $limit      Maximum number of records to return
     * @param int         $offset     Number of records to skip
     * @param string|null $entityType Filter by entity type (optional)
     * @param bool|null   $isActive   Filter by active status (optional)
     * @return array                  Array of subscription records
     */
    public function getAll($limit = 100, $offset = 0, $entityType = null, $isActive = null)
    {
        $whereClauses = array();
        $whereClauses[] = sprintf("site_id = %d", $this->_siteID);

        if ($entityType !== null)
        {
            $whereClauses[] = sprintf(
                "entity_type = %s",
                $this->_db->makeQueryString($entityType)
            );
        }

        if ($isActive !== null)
        {
            $whereClauses[] = sprintf(
                "is_active = %d",
                $isActive ? 1 : 0
            );
        }

        $whereSQL = implode(' AND ', $whereClauses);

        $sql = sprintf(
            "SELECT
                subscription_id AS subscriptionID,
                site_id AS siteID,
                name,
                entity_type AS entityType,
                event_types AS eventTypes,
                callback_url AS callbackUrl,
                secret,
                is_active AS isActive,
                created_by AS createdBy,
                DATE_FORMAT(date_created, '%%Y-%%m-%%dT%%H:%%i:%%s') AS dateCreated,
                DATE_FORMAT(date_modified, '%%Y-%%m-%%dT%%H:%%i:%%s') AS dateModified
             FROM webhook_subscriptions
             WHERE %s
             ORDER BY date_created DESC
             LIMIT %d OFFSET %d",
            $whereSQL,
            intval($limit),
            intval($offset)
        );

        $results = $this->_db->getAllAssoc($sql);

        /* Process each result to convert event_types to array */
        foreach ($results as &$result)
        {
            $result['eventTypesArray'] = explode(',', $result['eventTypes']);
            $result['isActive'] = (bool) $result['isActive'];
        }

        return $results;
    }

    /**
     * Get count of subscriptions matching the given filters
     *
     * @param string|null $entityType Filter by entity type (optional)
     * @param bool|null   $isActive   Filter by active status (optional)
     * @return int                    Number of matching subscriptions
     */
    public function getCount($entityType = null, $isActive = null)
    {
        $whereClauses = array();
        $whereClauses[] = sprintf("site_id = %d", $this->_siteID);

        if ($entityType !== null)
        {
            $whereClauses[] = sprintf(
                "entity_type = %s",
                $this->_db->makeQueryString($entityType)
            );
        }

        if ($isActive !== null)
        {
            $whereClauses[] = sprintf(
                "is_active = %d",
                $isActive ? 1 : 0
            );
        }

        $whereSQL = implode(' AND ', $whereClauses);

        $sql = sprintf(
            "SELECT COUNT(*) AS totalCount
             FROM webhook_subscriptions
             WHERE %s",
            $whereSQL
        );

        $result = $this->_db->getAssoc($sql);

        if (empty($result))
        {
            return 0;
        }

        return (int) $result['totalCount'];
    }

    /**
     * Update a webhook subscription
     *
     * @param int   $subscriptionID Subscription ID
     * @param array $data           Array of fields to update (name, callbackUrl, eventTypes, isActive, secret)
     * @return bool                 True on success, false on failure
     */
    public function update($subscriptionID, $data)
    {
        /* Verify subscription exists */
        $existing = $this->get($subscriptionID);
        if (empty($existing))
        {
            return false;
        }

        $updates = array();

        if (isset($data['name']))
        {
            $updates[] = sprintf(
                "name = %s",
                $this->_db->makeQueryString($data['name'])
            );
        }

        if (isset($data['callbackUrl']))
        {
            $updates[] = sprintf(
                "callback_url = %s",
                $this->_db->makeQueryString($data['callbackUrl'])
            );
        }

        if (isset($data['eventTypes']))
        {
            /* Validate event types */
            $validEventTypes = array();
            foreach ($data['eventTypes'] as $eventType)
            {
                if ($this->isValidEventType($eventType))
                {
                    $validEventTypes[] = $eventType;
                }
            }

            if (!empty($validEventTypes))
            {
                $updates[] = sprintf(
                    "event_types = %s",
                    $this->_db->makeQueryString(implode(',', $validEventTypes))
                );
            }
        }

        if (isset($data['isActive']))
        {
            $updates[] = sprintf(
                "is_active = %d",
                $data['isActive'] ? 1 : 0
            );
        }

        if (array_key_exists('secret', $data))
        {
            if ($data['secret'] === null)
            {
                $updates[] = "secret = NULL";
            }
            else
            {
                $updates[] = sprintf(
                    "secret = %s",
                    $this->_db->makeQueryString($data['secret'])
                );
            }
        }

        if (empty($updates))
        {
            return true; /* Nothing to update */
        }

        $updates[] = "date_modified = NOW()";

        $sql = sprintf(
            "UPDATE webhook_subscriptions
             SET %s
             WHERE subscription_id = %d
               AND site_id = %d",
            implode(', ', $updates),
            intval($subscriptionID),
            $this->_siteID
        );

        return (bool) $this->_db->query($sql);
    }

    /**
     * Delete a webhook subscription
     *
     * @param int $subscriptionID Subscription ID
     * @return bool               True on success, false on failure
     */
    public function delete($subscriptionID)
    {
        /* CASCADE will handle delivery_log and event_queue cleanup */
        $sql = sprintf(
            "DELETE FROM webhook_subscriptions
             WHERE subscription_id = %d
               AND site_id = %d",
            intval($subscriptionID),
            $this->_siteID
        );

        return (bool) $this->_db->query($sql);
    }

    /**
     * Activate a webhook subscription
     *
     * @param int $subscriptionID Subscription ID
     * @return bool               True on success, false on failure
     */
    public function activate($subscriptionID)
    {
        return $this->update($subscriptionID, array('isActive' => true));
    }

    /**
     * Deactivate a webhook subscription
     *
     * @param int $subscriptionID Subscription ID
     * @return bool               True on success, false on failure
     */
    public function deactivate($subscriptionID)
    {
        return $this->update($subscriptionID, array('isActive' => false));
    }

    // ========================================================================
    // EVENT MATCHING METHODS
    // ========================================================================

    /**
     * Get all active subscriptions that match an entity type and event type
     *
     * @param string $entityType Entity type (candidate, joborder, etc.)
     * @param string $eventType  Event type (create, update, delete)
     * @return array             Array of matching subscription records
     */
    public function getSubscriptionsForEvent($entityType, $eventType)
    {
        $sql = sprintf(
            "SELECT
                subscription_id AS subscriptionID,
                site_id AS siteID,
                name,
                entity_type AS entityType,
                event_types AS eventTypes,
                callback_url AS callbackUrl,
                secret,
                is_active AS isActive,
                created_by AS createdBy,
                DATE_FORMAT(date_created, '%%Y-%%m-%%dT%%H:%%i:%%s') AS dateCreated,
                DATE_FORMAT(date_modified, '%%Y-%%m-%%dT%%H:%%i:%%s') AS dateModified
             FROM webhook_subscriptions
             WHERE site_id = %d
               AND entity_type = %s
               AND is_active = 1
               AND FIND_IN_SET(%s, event_types) > 0
             ORDER BY subscription_id ASC",
            $this->_siteID,
            $this->_db->makeQueryString($entityType),
            $this->_db->makeQueryString($eventType)
        );

        $results = $this->_db->getAllAssoc($sql);

        /* Process each result to convert event_types to array */
        foreach ($results as &$result)
        {
            $result['eventTypesArray'] = explode(',', $result['eventTypes']);
            $result['isActive'] = (bool) $result['isActive'];
        }

        return $results;
    }

    // ========================================================================
    // DELIVERY LOG METHODS
    // ========================================================================

    /**
     * Log a webhook delivery attempt
     *
     * @param int         $subscriptionID Subscription ID
     * @param string      $eventType      Event type
     * @param int         $entityID       Entity ID that triggered the event
     * @param string      $payload        JSON payload sent
     * @param int|null    $responseCode   HTTP response code (optional)
     * @param string|null $responseBody   Response body (optional)
     * @param string      $status         Delivery status (default: pending)
     * @return int|false                  New log ID on success, false on failure
     */
    public function logDelivery($subscriptionID, $eventType, $entityID, $payload, $responseCode = null, $responseBody = null, $status = self::STATUS_PENDING)
    {
        $sql = sprintf(
            "INSERT INTO webhook_delivery_log
             (subscription_id, event_type, entity_id, payload, response_code, response_body, attempt_count, status, date_created)
             VALUES (%d, %s, %d, %s, %s, %s, 1, %s, NOW())",
            intval($subscriptionID),
            $this->_db->makeQueryString($eventType),
            intval($entityID),
            $this->_db->makeQueryString($payload),
            $responseCode !== null ? intval($responseCode) : 'NULL',
            $responseBody !== null ? $this->_db->makeQueryString($responseBody) : 'NULL',
            $this->_db->makeQueryString($status)
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return false;
        }

        return $this->_db->getLastInsertID();
    }

    /**
     * Update a delivery log entry after an attempt
     *
     * @param int    $logID        Log ID
     * @param int    $responseCode HTTP response code
     * @param string $responseBody Response body
     * @param string $status       New status (success, failed, retrying)
     * @return bool                True on success, false on failure
     */
    public function updateDeliveryLog($logID, $responseCode, $responseBody, $status)
    {
        $dateCompleted = '';
        if ($status === self::STATUS_SUCCESS || $status === self::STATUS_FAILED)
        {
            $dateCompleted = ', date_completed = NOW()';
        }

        $sql = sprintf(
            "UPDATE webhook_delivery_log
             SET response_code = %d,
                 response_body = %s,
                 status = %s,
                 attempt_count = attempt_count + 1
                 %s
             WHERE log_id = %d",
            intval($responseCode),
            $this->_db->makeQueryString($responseBody),
            $this->_db->makeQueryString($status),
            $dateCompleted,
            intval($logID)
        );

        return (bool) $this->_db->query($sql);
    }

    /**
     * Get recent delivery logs for a subscription
     *
     * @param int $subscriptionID Subscription ID
     * @param int $limit          Maximum number of records to return (default: 50)
     * @return array              Array of delivery log records
     */
    public function getDeliveryLogs($subscriptionID, $limit = 50)
    {
        $sql = sprintf(
            "SELECT
                log_id AS logID,
                subscription_id AS subscriptionID,
                event_type AS eventType,
                entity_id AS entityID,
                payload,
                response_code AS responseCode,
                response_body AS responseBody,
                attempt_count AS attemptCount,
                status,
                DATE_FORMAT(date_created, '%%Y-%%m-%%dT%%H:%%i:%%s') AS dateCreated,
                DATE_FORMAT(date_completed, '%%Y-%%m-%%dT%%H:%%i:%%s') AS dateCompleted
             FROM webhook_delivery_log
             WHERE subscription_id = %d
             ORDER BY date_created DESC
             LIMIT %d",
            intval($subscriptionID),
            intval($limit)
        );

        return $this->_db->getAllAssoc($sql);
    }

    // ========================================================================
    // QUEUE METHODS
    // ========================================================================

    /**
     * Add an event to the async processing queue
     *
     * @param int         $subscriptionID Subscription ID
     * @param string      $eventType      Event type
     * @param string      $entityType     Entity type
     * @param int         $entityID       Entity ID
     * @param string      $payload        JSON payload to send
     * @param int         $priority       Priority (lower = higher priority, default: 5)
     * @param string|null $scheduledAt    When to attempt delivery (default: NOW)
     * @return int|false                  New queue ID on success, false on failure
     */
    public function queueEvent($subscriptionID, $eventType, $entityType, $entityID, $payload, $priority = 5, $scheduledAt = null)
    {
        $scheduledAtSQL = $scheduledAt !== null
            ? $this->_db->makeQueryString($scheduledAt)
            : 'NOW()';

        $sql = sprintf(
            "INSERT INTO webhook_event_queue
             (subscription_id, event_type, entity_type, entity_id, payload, priority, scheduled_at, date_created)
             VALUES (%d, %s, %s, %d, %s, %d, %s, NOW())",
            intval($subscriptionID),
            $this->_db->makeQueryString($eventType),
            $this->_db->makeQueryString($entityType),
            intval($entityID),
            $this->_db->makeQueryString($payload),
            intval($priority),
            $scheduledAtSQL
        );

        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return false;
        }

        return $this->_db->getLastInsertID();
    }

    /**
     * Get pending events from the queue ordered by scheduled time and priority
     *
     * @param int $limit Maximum number of events to retrieve (default: 100)
     * @return array     Array of queued event records
     */
    public function getQueuedEvents($limit = 100)
    {
        $sql = sprintf(
            "SELECT
                q.queue_id AS queueID,
                q.subscription_id AS subscriptionID,
                q.event_type AS eventType,
                q.entity_type AS entityType,
                q.entity_id AS entityID,
                q.payload,
                q.priority,
                DATE_FORMAT(q.scheduled_at, '%%Y-%%m-%%dT%%H:%%i:%%s') AS scheduledAt,
                DATE_FORMAT(q.date_created, '%%Y-%%m-%%dT%%H:%%i:%%s') AS dateCreated,
                s.callback_url AS callbackUrl,
                s.secret
             FROM webhook_event_queue q
             INNER JOIN webhook_subscriptions s ON q.subscription_id = s.subscription_id
             WHERE q.scheduled_at <= NOW()
               AND s.is_active = 1
               AND s.site_id = %d
             ORDER BY q.scheduled_at ASC, q.priority ASC
             LIMIT %d",
            $this->_siteID,
            intval($limit)
        );

        return $this->_db->getAllAssoc($sql);
    }

    /**
     * Remove an event from the queue after processing
     *
     * @param int $queueID Queue ID
     * @return bool        True on success, false on failure
     */
    public function removeFromQueue($queueID)
    {
        $sql = sprintf(
            "DELETE FROM webhook_event_queue
             WHERE queue_id = %d",
            intval($queueID)
        );

        return (bool) $this->_db->query($sql);
    }

    // ========================================================================
    // VALIDATION HELPER METHODS
    // ========================================================================

    /**
     * Get array of all valid entity types
     *
     * @return array Array of valid entity type strings
     */
    public static function getEntityTypes()
    {
        return array(
            self::ENTITY_CANDIDATE,
            self::ENTITY_JOBORDER,
            self::ENTITY_COMPANY,
            self::ENTITY_CONTACT,
            self::ENTITY_PLACEMENT,
            self::ENTITY_JOBSUBMISSION,
            self::ENTITY_NOTE,
            self::ENTITY_APPOINTMENT,
            self::ENTITY_TASK,
            self::ENTITY_TEARSHEET
        );
    }

    /**
     * Get array of all valid event types
     *
     * @return array Array of valid event type strings
     */
    public static function getEventTypes()
    {
        return array(
            self::EVENT_CREATE,
            self::EVENT_UPDATE,
            self::EVENT_DELETE
        );
    }

    /**
     * Check if an entity type is valid
     *
     * @param string $entityType Entity type to validate
     * @return bool              True if valid, false otherwise
     */
    public function isValidEntityType($entityType)
    {
        return in_array($entityType, self::getEntityTypes());
    }

    /**
     * Check if an event type is valid
     *
     * @param string $eventType Event type to validate
     * @return bool             True if valid, false otherwise
     */
    public function isValidEventType($eventType)
    {
        return in_array($eventType, self::getEventTypes());
    }
}

?>
