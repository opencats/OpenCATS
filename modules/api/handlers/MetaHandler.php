<?php
/**
 * CATS
 * API Meta Handler
 *
 * Handles entity schema discovery (Bullhorn /meta equivalent).
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

class MetaHandler
{
    use ApiHelpers;

    protected $_requestLogger;

    public function __construct($requestLogger = null)
    {
        $this->_requestLogger = $requestLogger;
    }

    /**
     * Handle meta endpoint for entity schema discovery
     * Follows Bullhorn /meta pattern
     */
    public function handle()
    {
        $entity = isset($_GET['entity']) ? strtolower(trim($_GET['entity'])) : '';

        $entitySchemas = $this->getEntitySchemas();

        if (empty($entity)) {
            $this->sendEntityList($entitySchemas);
            return;
        }

        // Remove trailing 's' if present (joborders -> joborder)
        $entity = rtrim($entity, 's');

        if (!isset($entitySchemas[$entity])) {
            // Sanitize entity to prevent XSS in error response
            $safeEntity = htmlspecialchars($entity, ENT_QUOTES, 'UTF-8');
            $this->sendError('Entity not found: ' . $safeEntity, 404);
            return;
        }

        $this->sendSuccess($entitySchemas[$entity]);
    }

    private function sendEntityList($entitySchemas)
    {
        $entities = [];
        foreach ($entitySchemas as $key => $schema) {
            $entities[] = [
                'name' => $schema['entity'],
                'label' => $schema['label'],
                'endpoint' => '?m=api&a=' . $key . 's'
            ];
        }
        $this->sendSuccess(['entities' => $entities]);
    }

    private function getEntitySchemas()
    {
        return [
            'joborder' => [
                'entity' => 'JobOrder',
                'label' => 'Job Order',
                'fields' => [
                    ['name' => 'id', 'type' => 'Integer', 'label' => 'ID', 'readOnly' => true],
                    ['name' => 'title', 'type' => 'String', 'label' => 'Title', 'required' => true, 'maxLength' => 255],
                    ['name' => 'description', 'type' => 'Text', 'label' => 'Description', 'required' => false],
                    ['name' => 'publicDescription', 'type' => 'Text', 'label' => 'Public Description', 'required' => false],
                    ['name' => 'status', 'type' => 'String', 'label' => 'Status', 'required' => false, 'options' => ['Active', 'On Hold', 'Closed', 'Filled']],
                    ['name' => 'isOpen', 'type' => 'Boolean', 'label' => 'Is Open', 'required' => false],
                    ['name' => 'isPublic', 'type' => 'Boolean', 'label' => 'Is Public', 'required' => false],
                    ['name' => 'companyID', 'type' => 'Integer', 'label' => 'Company ID', 'associatedEntity' => 'Company', 'required' => true],
                    ['name' => 'contactID', 'type' => 'Integer', 'label' => 'Contact ID', 'associatedEntity' => 'Contact', 'required' => false],
                    ['name' => 'ownerID', 'type' => 'Integer', 'label' => 'Owner ID', 'associatedEntity' => 'User', 'required' => false],
                    ['name' => 'recruiterID', 'type' => 'Integer', 'label' => 'Recruiter ID', 'associatedEntity' => 'User', 'required' => false],
                    ['name' => 'salary', 'type' => 'String', 'label' => 'Salary', 'required' => false],
                    ['name' => 'type', 'type' => 'String', 'label' => 'Employment Type', 'required' => false, 'options' => ['H', 'C2C', 'FL', 'PT']],
                    ['name' => 'city', 'type' => 'String', 'label' => 'City', 'required' => false, 'maxLength' => 64],
                    ['name' => 'state', 'type' => 'String', 'label' => 'State', 'required' => false, 'maxLength' => 64],
                    ['name' => 'openings', 'type' => 'Integer', 'label' => 'Openings', 'required' => false, 'default' => 1],
                    ['name' => 'startDate', 'type' => 'Date', 'label' => 'Start Date', 'required' => false],
                    ['name' => 'duration', 'type' => 'String', 'label' => 'Duration', 'required' => false],
                    ['name' => 'maxRate', 'type' => 'String', 'label' => 'Max Rate', 'required' => false],
                    ['name' => 'notes', 'type' => 'Text', 'label' => 'Notes', 'required' => false],
                    ['name' => 'isHot', 'type' => 'Boolean', 'label' => 'Is Hot', 'required' => false],
                    ['name' => 'dateAdded', 'type' => 'DateTime', 'label' => 'Date Added', 'readOnly' => true],
                    ['name' => 'dateLastModified', 'type' => 'DateTime', 'label' => 'Date Modified', 'readOnly' => true]
                ]
            ],
            'tearsheet' => [
                'entity' => 'Tearsheet',
                'label' => 'Tearsheet',
                'fields' => [
                    ['name' => 'id', 'type' => 'Integer', 'label' => 'ID', 'readOnly' => true],
                    ['name' => 'name', 'type' => 'String', 'label' => 'Name', 'required' => true, 'maxLength' => 128],
                    ['name' => 'description', 'type' => 'Text', 'label' => 'Description', 'required' => false],
                    ['name' => 'isPublic', 'type' => 'Boolean', 'label' => 'Is Public', 'required' => false],
                    ['name' => 'owner', 'type' => 'Association', 'label' => 'Owner', 'associatedEntity' => 'User', 'readOnly' => true],
                    ['name' => 'dateCreated', 'type' => 'DateTime', 'label' => 'Date Created', 'readOnly' => true],
                    ['name' => 'jobOrders', 'type' => 'ToMany', 'label' => 'Job Orders', 'associatedEntity' => 'JobOrder']
                ]
            ],
            'candidate' => [
                'entity' => 'Candidate',
                'label' => 'Candidate',
                'fields' => [
                    ['name' => 'id', 'type' => 'Integer', 'label' => 'ID', 'readOnly' => true],
                    ['name' => 'firstName', 'type' => 'String', 'label' => 'First Name', 'required' => true, 'maxLength' => 64],
                    ['name' => 'middleName', 'type' => 'String', 'label' => 'Middle Name', 'required' => false, 'maxLength' => 64],
                    ['name' => 'lastName', 'type' => 'String', 'label' => 'Last Name', 'required' => true, 'maxLength' => 64],
                    ['name' => 'email1', 'type' => 'String', 'label' => 'Email', 'required' => false, 'maxLength' => 128],
                    ['name' => 'email2', 'type' => 'String', 'label' => 'Email 2', 'required' => false, 'maxLength' => 128],
                    ['name' => 'phone', 'type' => 'String', 'label' => 'Phone (Home)', 'required' => false, 'maxLength' => 32],
                    ['name' => 'phoneCell', 'type' => 'String', 'label' => 'Phone (Cell)', 'required' => false, 'maxLength' => 32],
                    ['name' => 'phoneWork', 'type' => 'String', 'label' => 'Phone (Work)', 'required' => false, 'maxLength' => 32],
                    ['name' => 'address', 'type' => 'String', 'label' => 'Address', 'required' => false],
                    ['name' => 'city', 'type' => 'String', 'label' => 'City', 'required' => false, 'maxLength' => 64],
                    ['name' => 'state', 'type' => 'String', 'label' => 'State', 'required' => false, 'maxLength' => 64],
                    ['name' => 'zip', 'type' => 'String', 'label' => 'Zip', 'required' => false, 'maxLength' => 16],
                    ['name' => 'source', 'type' => 'String', 'label' => 'Source', 'required' => false],
                    ['name' => 'keySkills', 'type' => 'Text', 'label' => 'Key Skills', 'required' => false],
                    ['name' => 'currentEmployer', 'type' => 'String', 'label' => 'Current Employer', 'required' => false],
                    ['name' => 'canRelocate', 'type' => 'Boolean', 'label' => 'Can Relocate', 'required' => false],
                    ['name' => 'isHot', 'type' => 'Boolean', 'label' => 'Is Hot', 'required' => false],
                    ['name' => 'ownerID', 'type' => 'Integer', 'label' => 'Owner ID', 'associatedEntity' => 'User'],
                    ['name' => 'dateAdded', 'type' => 'DateTime', 'label' => 'Date Added', 'readOnly' => true],
                    ['name' => 'dateLastModified', 'type' => 'DateTime', 'label' => 'Date Modified', 'readOnly' => true]
                ]
            ],
            'company' => [
                'entity' => 'Company',
                'label' => 'Company (Client Corporation)',
                'fields' => [
                    ['name' => 'id', 'type' => 'Integer', 'label' => 'ID', 'readOnly' => true],
                    ['name' => 'name', 'type' => 'String', 'label' => 'Name', 'required' => true, 'maxLength' => 128],
                    ['name' => 'address', 'type' => 'String', 'label' => 'Address', 'required' => false],
                    ['name' => 'city', 'type' => 'String', 'label' => 'City', 'required' => false, 'maxLength' => 64],
                    ['name' => 'state', 'type' => 'String', 'label' => 'State', 'required' => false, 'maxLength' => 64],
                    ['name' => 'zip', 'type' => 'String', 'label' => 'Zip', 'required' => false, 'maxLength' => 16],
                    ['name' => 'phone', 'type' => 'String', 'label' => 'Phone', 'required' => false, 'maxLength' => 32],
                    ['name' => 'phone2', 'type' => 'String', 'label' => 'Phone 2', 'required' => false, 'maxLength' => 32],
                    ['name' => 'fax', 'type' => 'String', 'label' => 'Fax', 'required' => false, 'maxLength' => 32],
                    ['name' => 'url', 'type' => 'String', 'label' => 'Website', 'required' => false],
                    ['name' => 'keyTechnologies', 'type' => 'Text', 'label' => 'Key Technologies', 'required' => false],
                    ['name' => 'notes', 'type' => 'Text', 'label' => 'Notes', 'required' => false],
                    ['name' => 'isHot', 'type' => 'Boolean', 'label' => 'Is Hot', 'required' => false],
                    ['name' => 'ownerID', 'type' => 'Integer', 'label' => 'Owner ID', 'associatedEntity' => 'User'],
                    ['name' => 'dateAdded', 'type' => 'DateTime', 'label' => 'Date Added', 'readOnly' => true],
                    ['name' => 'dateLastModified', 'type' => 'DateTime', 'label' => 'Date Modified', 'readOnly' => true]
                ]
            ],
            'contact' => [
                'entity' => 'Contact',
                'label' => 'Contact (Client Contact)',
                'fields' => [
                    ['name' => 'id', 'type' => 'Integer', 'label' => 'ID', 'readOnly' => true],
                    ['name' => 'firstName', 'type' => 'String', 'label' => 'First Name', 'required' => true, 'maxLength' => 64],
                    ['name' => 'lastName', 'type' => 'String', 'label' => 'Last Name', 'required' => true, 'maxLength' => 64],
                    ['name' => 'title', 'type' => 'String', 'label' => 'Title', 'required' => false, 'maxLength' => 64],
                    ['name' => 'email1', 'type' => 'String', 'label' => 'Email', 'required' => false, 'maxLength' => 128],
                    ['name' => 'email2', 'type' => 'String', 'label' => 'Email 2', 'required' => false, 'maxLength' => 128],
                    ['name' => 'phone', 'type' => 'String', 'label' => 'Phone (Work)', 'required' => false, 'maxLength' => 32],
                    ['name' => 'phoneCell', 'type' => 'String', 'label' => 'Phone (Cell)', 'required' => false, 'maxLength' => 32],
                    ['name' => 'clientCorporation', 'type' => 'Integer', 'label' => 'Company ID', 'associatedEntity' => 'Company', 'required' => true],
                    ['name' => 'isHot', 'type' => 'Boolean', 'label' => 'Is Hot', 'required' => false],
                    ['name' => 'notes', 'type' => 'Text', 'label' => 'Notes', 'required' => false],
                    ['name' => 'ownerID', 'type' => 'Integer', 'label' => 'Owner ID', 'associatedEntity' => 'User'],
                    ['name' => 'dateAdded', 'type' => 'DateTime', 'label' => 'Date Added', 'readOnly' => true]
                ]
            ]
        ];
    }
}
