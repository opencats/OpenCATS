<?php
/**
 * CATS
 * API Mass Update Handler
 *
 * Handles bulk update operations for multiple entities at once.
 * Supports updating multiple records of the same entity type in a single request.
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

include_once(dirname(__FILE__) . '/../traits/ApiHelpers.php');

class MassUpdateHandler
{
    use ApiHelpers;

    private $_siteID;
    private $_userID;
    protected $_requestLogger;
    private $_db;

    /**
     * Supported entity types and their configurations
     */
    private $_entityConfig = array(
        'joborder' => array(
            'library' => 'JobOrders',
            'libraryPath' => './lib/JobOrders.php',
            'table' => 'joborder',
            'idColumn' => 'joborder_id',
            'allowedFields' => array(
                'status', 'title', 'description', 'notes', 'city', 'state',
                'salary', 'duration', 'type', 'is_hot', 'public', 'openings',
                'openings_available', 'rate_max', 'recruiter', 'owner'
            )
        ),
        'candidate' => array(
            'library' => 'Candidates',
            'libraryPath' => './lib/Candidates.php',
            'table' => 'candidate',
            'idColumn' => 'candidate_id',
            'allowedFields' => array(
                'is_active', 'first_name', 'last_name', 'email1', 'email2',
                'phone_home', 'phone_cell', 'phone_work', 'address', 'city',
                'state', 'zip', 'source', 'key_skills', 'current_employer',
                'can_relocate', 'current_pay', 'desired_pay', 'notes', 'owner'
            )
        ),
        'company' => array(
            'library' => 'Companies',
            'libraryPath' => './lib/Companies.php',
            'table' => 'company',
            'idColumn' => 'company_id',
            'allowedFields' => array(
                'name', 'address', 'city', 'state', 'zip', 'phone1', 'phone2',
                'fax_number', 'url', 'key_technologies', 'is_hot', 'notes', 'owner'
            )
        ),
        'contact' => array(
            'library' => 'Contacts',
            'libraryPath' => './lib/Contacts.php',
            'table' => 'contact',
            'idColumn' => 'contact_id',
            'allowedFields' => array(
                'first_name', 'last_name', 'title', 'email1', 'email2',
                'phone_work', 'phone_cell', 'phone_other', 'address', 'city',
                'state', 'zip', 'is_hot', 'notes', 'owner', 'left_company'
            )
        ),
        'jobsubmission' => array(
            'library' => 'JobSubmissions',
            'libraryPath' => './lib/JobSubmissions.php',
            'table' => 'candidate_joborder',
            'idColumn' => 'candidate_joborder_id',
            'allowedFields' => array(
                'status', 'rating_value', 'source', 'send_to_client'
            )
        ),
        'placement' => array(
            'library' => 'Placements',
            'libraryPath' => './lib/Placements.php',
            'table' => 'placement',
            'idColumn' => 'placement_id',
            'allowedFields' => array(
                'start_date', 'salary', 'bonus', 'fee_percent', 'referral_fee',
                'status', 'comments'
            )
        ),
        'task' => array(
            'library' => 'Tasks',
            'libraryPath' => './lib/Tasks.php',
            'table' => 'task',
            'idColumn' => 'task_id',
            'allowedFields' => array(
                'description', 'priority', 'due_date', 'status', 'completed',
                'assigned_to', 'owner_id'
            )
        ),
        'note' => array(
            'library' => 'Notes',
            'libraryPath' => './lib/Notes.php',
            'table' => 'note',
            'idColumn' => 'note_id',
            'allowedFields' => array(
                'action', 'comments', 'person_type', 'person_id', 'joborder_id',
                'activity_type'
            )
        ),
        'appointment' => array(
            'library' => 'Appointments',
            'libraryPath' => './lib/Appointments.php',
            'table' => 'event',
            'idColumn' => 'event_id',
            'allowedFields' => array(
                'title', 'description', 'start_date', 'end_date', 'all_day',
                'is_public', 'type', 'reminder_enabled', 'reminder_time'
            )
        ),
        'tearsheet' => array(
            'library' => 'Tearsheets',
            'libraryPath' => './lib/Tearsheets.php',
            'table' => 'tearsheet',
            'idColumn' => 'tearsheet_id',
            'allowedFields' => array(
                'name', 'description', 'is_public'
            )
        )
    );

    /**
     * Constructor
     *
     * @param int $siteID Site ID
     * @param int $userID User ID
     * @param object|null $requestLogger Request logger instance
     */
    public function __construct($siteID, $userID, $requestLogger = null)
    {
        $this->_siteID = $siteID;
        $this->_userID = $userID;
        $this->_requestLogger = $requestLogger;
        $this->_db = DatabaseConnection::getInstance();
    }

    /**
     * Handle mass update request
     * Only accepts POST method with JSON body containing:
     * - entityType: string (required)
     * - ids: array of integers (required)
     * - updates: object with field-value pairs (required)
     */
    public function handle()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->sendError('Method not allowed. Use POST.', 405);
            return;
        }

        $input = $this->getRequestBody();

        // Validate required fields
        if (empty($input['entityType'])) {
            $this->sendError('Missing required field: entityType', 400);
            return;
        }
        if (empty($input['ids']) || !is_array($input['ids'])) {
            $this->sendError('Missing or invalid field: ids (must be array)', 400);
            return;
        }
        if (empty($input['updates']) || !is_array($input['updates'])) {
            $this->sendError('Missing or invalid field: updates (must be object)', 400);
            return;
        }

        // Normalize entity type
        $entityType = strtolower(trim($input['entityType']));
        // Handle plural forms
        $entityType = rtrim($entityType, 's');

        // Validate entity type
        if (!isset($this->_entityConfig[$entityType])) {
            $supportedTypes = implode(', ', array_keys($this->_entityConfig));
            $this->sendError(
                'Unknown entity type: ' . htmlspecialchars($entityType, ENT_QUOTES, 'UTF-8') .
                '. Supported types: ' . $supportedTypes,
                400
            );
            return;
        }

        // Sanitize IDs
        $ids = array_filter(array_map('intval', $input['ids']), function($id) {
            return $id > 0;
        });

        if (empty($ids)) {
            $this->sendError('No valid IDs provided', 400);
            return;
        }

        // Limit batch size
        $maxBatchSize = 100;
        if (count($ids) > $maxBatchSize) {
            $this->sendError(
                'Batch size exceeds limit. Maximum ' . $maxBatchSize . ' records per request.',
                400
            );
            return;
        }

        // Perform the mass update
        $result = $this->massUpdate($entityType, $ids, $input['updates']);

        $this->sendSuccess($result);
    }

    /**
     * Perform mass update on entities
     *
     * @param string $entityType Entity type
     * @param array $ids Array of entity IDs
     * @param array $updates Fields to update
     * @return array Result with success/failure counts
     */
    private function massUpdate($entityType, $ids, $updates)
    {
        $config = $this->_entityConfig[$entityType];
        $success = 0;
        $failed = 0;
        $errors = array();
        $skipped = 0;

        // Filter updates to only allowed fields
        $filteredUpdates = $this->filterUpdates($updates, $config['allowedFields']);

        if (empty($filteredUpdates)) {
            return array(
                'entityType' => $entityType,
                'requested' => count($ids),
                'success' => 0,
                'failed' => 0,
                'skipped' => count($ids),
                'errors' => array(),
                'message' => 'No valid fields to update. Allowed fields: ' . implode(', ', $config['allowedFields'])
            );
        }

        // Build SET clause for SQL update
        $setParts = array();
        foreach ($filteredUpdates as $field => $value) {
            $dbField = $this->camelToSnake($field);
            if (is_null($value)) {
                $setParts[] = $dbField . ' = NULL';
            } elseif (is_bool($value)) {
                $setParts[] = $dbField . ' = ' . ($value ? '1' : '0');
            } elseif (is_numeric($value)) {
                $setParts[] = $dbField . ' = ' . $value;
            } else {
                $setParts[] = $dbField . ' = ' . $this->_db->makeQueryString($value);
            }
        }

        $setClause = implode(', ', $setParts);

        // Add date_modified if the table has it
        $tablesWithDateModified = array(
            'joborder', 'candidate', 'company', 'contact', 'tearsheet', 'event'
        );
        if (in_array($config['table'], $tablesWithDateModified)) {
            $setClause .= ', date_modified = NOW()';
        }

        // Process each ID
        foreach ($ids as $id) {
            try {
                // Verify entity exists and belongs to this site
                $exists = $this->verifyEntityExists(
                    $config['table'],
                    $config['idColumn'],
                    $id
                );

                if (!$exists) {
                    $failed++;
                    $errors[] = array(
                        'id' => $id,
                        'error' => 'Entity not found'
                    );
                    continue;
                }

                // Perform update
                $sql = sprintf(
                    "UPDATE %s SET %s WHERE %s = %d AND site_id = %d",
                    $config['table'],
                    $setClause,
                    $config['idColumn'],
                    $id,
                    $this->_siteID
                );

                $result = $this->_db->query($sql);

                if ($result) {
                    $success++;
                } else {
                    $failed++;
                    $errors[] = array(
                        'id' => $id,
                        'error' => 'Database update failed'
                    );
                }
            } catch (Exception $e) {
                $failed++;
                $errors[] = array(
                    'id' => $id,
                    'error' => $e->getMessage()
                );
            }
        }

        return array(
            'entityType' => $entityType,
            'requested' => count($ids),
            'success' => $success,
            'failed' => $failed,
            'skipped' => $skipped,
            'errors' => $errors,
            'fieldsUpdated' => array_keys($filteredUpdates)
        );
    }

    /**
     * Filter updates to only include allowed fields
     *
     * @param array $updates All requested updates
     * @param array $allowedFields List of allowed field names
     * @return array Filtered updates
     */
    private function filterUpdates($updates, $allowedFields)
    {
        $filtered = array();

        foreach ($updates as $field => $value) {
            // Convert camelCase to snake_case for comparison
            $snakeField = $this->camelToSnake($field);

            if (in_array($snakeField, $allowedFields)) {
                $filtered[$snakeField] = $value;
            } elseif (in_array($field, $allowedFields)) {
                $filtered[$field] = $value;
            }
        }

        return $filtered;
    }

    /**
     * Verify an entity exists and belongs to this site
     *
     * @param string $table Table name
     * @param string $idColumn ID column name
     * @param int $id Entity ID
     * @return bool True if exists
     */
    private function verifyEntityExists($table, $idColumn, $id)
    {
        $sql = sprintf(
            "SELECT COUNT(*) as count FROM %s WHERE %s = %d AND site_id = %d",
            $table,
            $idColumn,
            intval($id),
            $this->_siteID
        );

        $result = $this->_db->getAssoc($sql);
        return intval($result['count']) > 0;
    }

    /**
     * Get supported entity types
     *
     * @return array List of supported entity types with their configurations
     */
    public function getSupportedEntityTypes()
    {
        $types = array();
        foreach ($this->_entityConfig as $type => $config) {
            $types[$type] = array(
                'entityType' => $type,
                'allowedFields' => $config['allowedFields']
            );
        }
        return $types;
    }
}
