<?php
/**
 * CATS
 * API Association Handler
 *
 * Handles entity-to-entity associations (e.g., tearsheet-joborder, tearsheet-candidate).
 * Provides a generic interface for managing many-to-many relationships between entities.
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
include_once(dirname(__FILE__) . '/../formatters/EntityFormatter.php');

class AssociationHandler
{
    use ApiHelpers;

    private $_siteID;
    private $_userID;
    protected $_requestLogger;
    private $_db;

    /**
     * Association configurations
     * Defines supported parent-child relationships
     */
    private $_associationConfig = array(
        // Tearsheet associations
        'tearsheet' => array(
            'joborder' => array(
                'table' => 'tearsheet_joborder',
                'parentColumn' => 'tearsheet_id',
                'childColumn' => 'joborder_id',
                'childTable' => 'joborder',
                'childIdColumn' => 'joborder_id',
                'formatter' => 'formatJobOrder',
                'additionalColumns' => array('date_added', 'added_by'),
                'hasAddedBy' => true
            ),
            'candidate' => array(
                'table' => 'tearsheet_candidate',
                'parentColumn' => 'tearsheet_id',
                'childColumn' => 'candidate_id',
                'childTable' => 'candidate',
                'childIdColumn' => 'candidate_id',
                'formatter' => 'formatCandidate',
                'additionalColumns' => array('date_added', 'added_by'),
                'hasAddedBy' => true
            )
        ),
        // Job Order associations
        'joborder' => array(
            'candidate' => array(
                'table' => 'candidate_joborder',
                'parentColumn' => 'joborder_id',
                'childColumn' => 'candidate_id',
                'childTable' => 'candidate',
                'childIdColumn' => 'candidate_id',
                'formatter' => 'formatCandidate',
                'additionalColumns' => array('status', 'date_submitted'),
                'hasAddedBy' => false
            ),
            'contact' => array(
                'table' => 'joborder_contact',
                'parentColumn' => 'joborder_id',
                'childColumn' => 'contact_id',
                'childTable' => 'contact',
                'childIdColumn' => 'contact_id',
                'formatter' => 'formatContact',
                'additionalColumns' => array(),
                'hasAddedBy' => false
            )
        ),
        // Company associations
        'company' => array(
            'contact' => array(
                'table' => 'contact',
                'parentColumn' => 'company_id',
                'childColumn' => 'contact_id',
                'childTable' => 'contact',
                'childIdColumn' => 'contact_id',
                'formatter' => 'formatContact',
                'additionalColumns' => array(),
                'hasAddedBy' => false,
                'directRelation' => true
            ),
            'joborder' => array(
                'table' => 'joborder',
                'parentColumn' => 'company_id',
                'childColumn' => 'joborder_id',
                'childTable' => 'joborder',
                'childIdColumn' => 'joborder_id',
                'formatter' => 'formatJobOrder',
                'additionalColumns' => array(),
                'hasAddedBy' => false,
                'directRelation' => true
            )
        ),
        // Candidate associations
        'candidate' => array(
            'joborder' => array(
                'table' => 'candidate_joborder',
                'parentColumn' => 'candidate_id',
                'childColumn' => 'joborder_id',
                'childTable' => 'joborder',
                'childIdColumn' => 'joborder_id',
                'formatter' => 'formatJobOrder',
                'additionalColumns' => array('status', 'date_submitted'),
                'hasAddedBy' => false
            ),
            'attachment' => array(
                'table' => 'attachment',
                'parentColumn' => 'data_item_id',
                'parentType' => DATA_ITEM_CANDIDATE,
                'childColumn' => 'attachment_id',
                'childTable' => 'attachment',
                'childIdColumn' => 'attachment_id',
                'formatter' => null,
                'additionalColumns' => array(),
                'hasAddedBy' => false,
                'directRelation' => true
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
     * Handle association request
     * Routes to appropriate method based on HTTP method
     */
    public function handle()
    {
        // Get parameters
        $parentType = isset($_GET['parentType']) ? strtolower(trim($_GET['parentType'])) : '';
        $parentId = isset($_GET['parentId']) ? intval($_GET['parentId']) : 0;
        $childType = isset($_GET['childType']) ? strtolower(trim($_GET['childType'])) : '';

        // Normalize plural forms
        $parentType = rtrim($parentType, 's');
        $childType = rtrim($childType, 's');

        // Validate required parameters
        if (empty($parentType)) {
            $this->sendError('Missing required parameter: parentType', 400);
            return;
        }
        if (empty($parentId) || $parentId <= 0) {
            $this->sendError('Missing or invalid parameter: parentId', 400);
            return;
        }
        if (empty($childType)) {
            $this->sendError('Missing required parameter: childType', 400);
            return;
        }

        // Validate association type
        if (!isset($this->_associationConfig[$parentType])) {
            $supportedParents = implode(', ', array_keys($this->_associationConfig));
            $this->sendError(
                'Unknown parent type: ' . htmlspecialchars($parentType, ENT_QUOTES, 'UTF-8') .
                '. Supported types: ' . $supportedParents,
                400
            );
            return;
        }

        if (!isset($this->_associationConfig[$parentType][$childType])) {
            $supportedChildren = implode(', ', array_keys($this->_associationConfig[$parentType]));
            $this->sendError(
                'Unknown child type for ' . $parentType . ': ' . htmlspecialchars($childType, ENT_QUOTES, 'UTF-8') .
                '. Supported types: ' . $supportedChildren,
                400
            );
            return;
        }

        // Verify parent exists
        if (!$this->verifyParentExists($parentType, $parentId)) {
            $this->sendError('Parent entity not found: ' . $parentType . ' #' . $parentId, 404);
            return;
        }

        $method = $_SERVER['REQUEST_METHOD'];

        switch ($method) {
            case 'GET':
                $this->getAssociations($parentType, $parentId, $childType);
                break;
            case 'PUT':
            case 'POST':
                $this->addAssociations($parentType, $parentId, $childType);
                break;
            case 'DELETE':
                $this->removeAssociations($parentType, $parentId, $childType);
                break;
            default:
                $this->sendError('Method not allowed', 405);
        }
    }

    /**
     * Get all associations for a parent entity
     *
     * @param string $parentType Parent entity type
     * @param int $parentId Parent entity ID
     * @param string $childType Child entity type
     */
    private function getAssociations($parentType, $parentId, $childType)
    {
        $config = $this->_associationConfig[$parentType][$childType];
        $associations = $this->fetchAssociations($parentType, $parentId, $childType, $config);

        // Apply pagination
        $pagination = $this->getPaginationParams();

        $total = count($associations);
        $pagedAssociations = array_slice($associations, $pagination['offset'], $pagination['limit']);

        $this->sendSuccess(array(
            'parentType' => $parentType,
            'parentId' => $parentId,
            'childType' => $childType,
            'total' => $total,
            'page' => $pagination['page'],
            'limit' => $pagination['limit'],
            'associations' => $pagedAssociations
        ));
    }

    /**
     * Add associations between parent and child entities
     *
     * @param string $parentType Parent entity type
     * @param int $parentId Parent entity ID
     * @param string $childType Child entity type
     */
    private function addAssociations($parentType, $parentId, $childType)
    {
        $config = $this->_associationConfig[$parentType][$childType];

        // Check if this is a direct relation (not many-to-many)
        if (isset($config['directRelation']) && $config['directRelation']) {
            $this->sendError(
                'Cannot add associations for direct relations. Use the entity update endpoint instead.',
                400
            );
            return;
        }

        $input = $this->getRequestBody();
        $childIds = isset($input['ids']) ? array_map('intval', $input['ids']) : array();

        if (empty($childIds)) {
            $this->sendError('Missing required field: ids (array of child IDs)', 400);
            return;
        }

        // Filter valid IDs
        $childIds = array_filter($childIds, function($id) {
            return $id > 0;
        });

        if (empty($childIds)) {
            $this->sendError('No valid IDs provided', 400);
            return;
        }

        // Limit batch size
        $maxBatchSize = 100;
        if (count($childIds) > $maxBatchSize) {
            $this->sendError(
                'Batch size exceeds limit. Maximum ' . $maxBatchSize . ' associations per request.',
                400
            );
            return;
        }

        $result = $this->createAssociations($parentType, $parentId, $childType, $childIds, $config);

        $this->sendSuccess(array(
            'parentType' => $parentType,
            'parentId' => $parentId,
            'childType' => $childType,
            'requested' => count($childIds),
            'added' => $result['added'],
            'skipped' => $result['skipped'],
            'failed' => $result['failed'],
            'errors' => $result['errors']
        ));
    }

    /**
     * Remove associations between parent and child entities
     *
     * @param string $parentType Parent entity type
     * @param int $parentId Parent entity ID
     * @param string $childType Child entity type
     */
    private function removeAssociations($parentType, $parentId, $childType)
    {
        $config = $this->_associationConfig[$parentType][$childType];

        // Check if this is a direct relation
        if (isset($config['directRelation']) && $config['directRelation']) {
            $this->sendError(
                'Cannot remove associations for direct relations. Use the entity update endpoint instead.',
                400
            );
            return;
        }

        $input = $this->getRequestBody();
        $childIds = array();

        // Support both body and query parameter
        if (!empty($input['ids'])) {
            $childIds = array_map('intval', $input['ids']);
        } elseif (!empty($_GET['ids'])) {
            $childIds = array_map('intval', explode(',', $_GET['ids']));
        }

        if (empty($childIds)) {
            $this->sendError('Missing required: ids (array of child IDs)', 400);
            return;
        }

        // Filter valid IDs
        $childIds = array_filter($childIds, function($id) {
            return $id > 0;
        });

        if (empty($childIds)) {
            $this->sendError('No valid IDs provided', 400);
            return;
        }

        $result = $this->deleteAssociations($parentType, $parentId, $childType, $childIds, $config);

        $this->sendSuccess(array(
            'parentType' => $parentType,
            'parentId' => $parentId,
            'childType' => $childType,
            'requested' => count($childIds),
            'removed' => $result['removed'],
            'notFound' => $result['notFound'],
            'errors' => $result['errors']
        ));
    }

    /**
     * Fetch associations from database
     *
     * @param string $parentType Parent entity type
     * @param int $parentId Parent entity ID
     * @param string $childType Child entity type
     * @param array $config Association configuration
     * @return array Array of associated entities
     */
    private function fetchAssociations($parentType, $parentId, $childType, $config)
    {
        $results = array();

        if (isset($config['directRelation']) && $config['directRelation']) {
            // Direct relation - child entities have FK to parent
            $sql = sprintf(
                "SELECT c.*
                 FROM %s c
                 WHERE c.%s = %d
                   AND c.site_id = %d",
                $config['childTable'],
                $config['parentColumn'],
                $parentId,
                $this->_siteID
            );
        } else {
            // Many-to-many via junction table
            $additionalSelect = '';
            if (!empty($config['additionalColumns'])) {
                $additionalSelect = ', a.' . implode(', a.', $config['additionalColumns']);
            }

            $sql = sprintf(
                "SELECT c.*%s
                 FROM %s a
                 INNER JOIN %s c ON a.%s = c.%s
                 WHERE a.%s = %d
                   AND c.site_id = %d",
                $additionalSelect,
                $config['table'],
                $config['childTable'],
                $config['childColumn'],
                $config['childIdColumn'],
                $config['parentColumn'],
                $parentId,
                $this->_siteID
            );
        }

        $sql .= " ORDER BY c." . $config['childIdColumn'] . " DESC";

        $rows = $this->_db->getAllAssoc($sql);

        if (!$rows) {
            return array();
        }

        // Format results if formatter is available
        foreach ($rows as $row) {
            if ($config['formatter'] && method_exists('EntityFormatter', $config['formatter'])) {
                $formatted = call_user_func(array('EntityFormatter', $config['formatter']), $row);
                $results[] = $formatted;
            } else {
                $results[] = $row;
            }
        }

        return $results;
    }

    /**
     * Create associations in database
     *
     * @param string $parentType Parent entity type
     * @param int $parentId Parent entity ID
     * @param string $childType Child entity type
     * @param array $childIds Array of child IDs to associate
     * @param array $config Association configuration
     * @return array Result with counts
     */
    private function createAssociations($parentType, $parentId, $childType, $childIds, $config)
    {
        $added = 0;
        $skipped = 0;
        $failed = 0;
        $errors = array();

        foreach ($childIds as $childId) {
            try {
                // Verify child exists
                $childExists = $this->verifyChildExists($childType, $childId, $config);
                if (!$childExists) {
                    $skipped++;
                    $errors[] = array(
                        'id' => $childId,
                        'error' => 'Child entity not found'
                    );
                    continue;
                }

                // Check if association already exists
                if ($this->associationExists($parentId, $childId, $config)) {
                    $skipped++;
                    continue;
                }

                // Create association
                $columns = array($config['parentColumn'], $config['childColumn']);
                $values = array($parentId, $childId);

                if (in_array('date_added', $config['additionalColumns'])) {
                    $columns[] = 'date_added';
                    $values[] = 'NOW()';
                }

                if ($config['hasAddedBy']) {
                    $columns[] = 'added_by';
                    $values[] = $this->_userID;
                }

                $sql = sprintf(
                    "INSERT IGNORE INTO %s (%s) VALUES (%s)",
                    $config['table'],
                    implode(', ', $columns),
                    $this->buildValuesClause($values)
                );

                if ($this->_db->query($sql)) {
                    $added++;
                } else {
                    $failed++;
                    $errors[] = array(
                        'id' => $childId,
                        'error' => 'Database insert failed'
                    );
                }
            } catch (Exception $e) {
                $failed++;
                $errors[] = array(
                    'id' => $childId,
                    'error' => $e->getMessage()
                );
            }
        }

        return array(
            'added' => $added,
            'skipped' => $skipped,
            'failed' => $failed,
            'errors' => $errors
        );
    }

    /**
     * Delete associations from database
     *
     * @param string $parentType Parent entity type
     * @param int $parentId Parent entity ID
     * @param string $childType Child entity type
     * @param array $childIds Array of child IDs to disassociate
     * @param array $config Association configuration
     * @return array Result with counts
     */
    private function deleteAssociations($parentType, $parentId, $childType, $childIds, $config)
    {
        $removed = 0;
        $notFound = 0;
        $errors = array();

        foreach ($childIds as $childId) {
            try {
                // Check if association exists
                if (!$this->associationExists($parentId, $childId, $config)) {
                    $notFound++;
                    continue;
                }

                $sql = sprintf(
                    "DELETE FROM %s WHERE %s = %d AND %s = %d",
                    $config['table'],
                    $config['parentColumn'],
                    $parentId,
                    $config['childColumn'],
                    $childId
                );

                if ($this->_db->query($sql)) {
                    $removed++;
                } else {
                    $errors[] = array(
                        'id' => $childId,
                        'error' => 'Database delete failed'
                    );
                }
            } catch (Exception $e) {
                $errors[] = array(
                    'id' => $childId,
                    'error' => $e->getMessage()
                );
            }
        }

        return array(
            'removed' => $removed,
            'notFound' => $notFound,
            'errors' => $errors
        );
    }

    /**
     * Verify parent entity exists
     *
     * @param string $parentType Parent entity type
     * @param int $parentId Parent entity ID
     * @return bool True if exists
     */
    private function verifyParentExists($parentType, $parentId)
    {
        $tableMap = array(
            'tearsheet' => array('table' => 'tearsheet', 'idColumn' => 'tearsheet_id'),
            'joborder' => array('table' => 'joborder', 'idColumn' => 'joborder_id'),
            'company' => array('table' => 'company', 'idColumn' => 'company_id'),
            'candidate' => array('table' => 'candidate', 'idColumn' => 'candidate_id'),
            'contact' => array('table' => 'contact', 'idColumn' => 'contact_id')
        );

        if (!isset($tableMap[$parentType])) {
            return false;
        }

        $config = $tableMap[$parentType];

        $sql = sprintf(
            "SELECT COUNT(*) as count FROM %s WHERE %s = %d AND site_id = %d",
            $config['table'],
            $config['idColumn'],
            $parentId,
            $this->_siteID
        );

        $result = $this->_db->getAssoc($sql);
        return intval($result['count']) > 0;
    }

    /**
     * Verify child entity exists
     *
     * @param string $childType Child entity type
     * @param int $childId Child entity ID
     * @param array $config Association configuration
     * @return bool True if exists
     */
    private function verifyChildExists($childType, $childId, $config)
    {
        $sql = sprintf(
            "SELECT COUNT(*) as count FROM %s WHERE %s = %d AND site_id = %d",
            $config['childTable'],
            $config['childIdColumn'],
            $childId,
            $this->_siteID
        );

        $result = $this->_db->getAssoc($sql);
        return intval($result['count']) > 0;
    }

    /**
     * Check if an association already exists
     *
     * @param int $parentId Parent entity ID
     * @param int $childId Child entity ID
     * @param array $config Association configuration
     * @return bool True if association exists
     */
    private function associationExists($parentId, $childId, $config)
    {
        $sql = sprintf(
            "SELECT COUNT(*) as count FROM %s WHERE %s = %d AND %s = %d",
            $config['table'],
            $config['parentColumn'],
            $parentId,
            $config['childColumn'],
            $childId
        );

        $result = $this->_db->getAssoc($sql);
        return intval($result['count']) > 0;
    }

    /**
     * Build VALUES clause for INSERT
     *
     * @param array $values Array of values
     * @return string SQL values clause
     */
    private function buildValuesClause($values)
    {
        $parts = array();
        foreach ($values as $value) {
            if ($value === 'NOW()' || $value === 'NULL') {
                $parts[] = $value;
            } elseif (is_int($value) || is_numeric($value)) {
                $parts[] = intval($value);
            } else {
                $parts[] = $this->_db->makeQueryString($value);
            }
        }
        return implode(', ', $parts);
    }

    /**
     * Get supported association types
     *
     * @return array Configuration of supported associations
     */
    public function getSupportedAssociations()
    {
        $result = array();
        foreach ($this->_associationConfig as $parentType => $children) {
            $result[$parentType] = array(
                'parentType' => $parentType,
                'supportedChildTypes' => array_keys($children)
            );
        }
        return $result;
    }
}
