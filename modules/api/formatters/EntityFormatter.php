<?php
/**
 * CATS
 * API Entity Formatter
 *
 * Formats entity data for Bullhorn-compatible API responses.
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

class EntityFormatter
{
    /**
     * Format job order for API response
     * @param array $job Job order data
     * @return array Formatted job order
     */
    public static function formatJobOrder($job)
    {
        return [
            'id' => intval($job['jobOrderID'] ?? $job['joborder_id'] ?? 0),
            'title' => $job['title'] ?? '',
            'description' => $job['description'] ?? '',
            'publicDescription' => $job['public_description'] ?? $job['description'] ?? '',
            'status' => $job['status'] ?? '',
            'isOpen' => ($job['status'] ?? '') === 'Active',
            'isPublic' => (bool)($job['is_public'] ?? $job['public'] ?? 0),
            'dateAdded' => $job['dateCreated'] ?? $job['date_created'] ?? '',
            'dateLastModified' => $job['dateModified'] ?? $job['date_modified'] ?? '',
            'address' => [
                'city' => $job['city'] ?? '',
                'state' => $job['state'] ?? '',
                'zip' => $job['zip'] ?? '',
                'country' => $job['country'] ?? ''
            ],
            'salary' => $job['salary'] ?? $job['rate_max'] ?? '',
            'type' => $job['type'] ?? $job['duration'] ?? '',
            'clientCorporation' => [
                'id' => intval($job['companyID'] ?? $job['company_id'] ?? 0),
                'name' => $job['companyName'] ?? $job['company_name'] ?? ''
            ],
            'owner' => [
                'id' => intval($job['recruiterID'] ?? $job['recruiter'] ?? 0),
                'firstName' => $job['recruiterFirstName'] ?? $job['recruiter_first_name'] ?? '',
                'lastName' => $job['recruiterLastName'] ?? $job['recruiter_last_name'] ?? ''
            ],
            'openings' => intval($job['openings'] ?? 1),
            'startDate' => $job['startDate'] ?? $job['start_date'] ?? ''
        ];
    }

    /**
     * Format tearsheet for API response
     * @param array $ts Tearsheet data
     * @return array Formatted tearsheet
     */
    public static function formatTearsheet($ts)
    {
        return [
            'id' => intval($ts['tearsheet_id'] ?? 0),
            'name' => $ts['name'] ?? '',
            'description' => $ts['description'] ?? '',
            'isPublic' => (bool)($ts['is_public'] ?? 0),
            'dateCreated' => $ts['date_created'] ?? '',
            'jobOrders' => [
                'total' => intval($ts['job_count'] ?? 0)
            ],
            'candidates' => [
                'total' => intval($ts['candidate_count'] ?? 0)
            ],
            'owner' => [
                'id' => intval($ts['user_id'] ?? 0)
            ]
        ];
    }

    /**
     * Format candidate for API response
     * @param array $candidate Candidate data
     * @return array Formatted candidate
     */
    public static function formatCandidate($candidate)
    {
        return [
            'id' => intval($candidate['candidateID'] ?? $candidate['candidate_id'] ?? 0),
            'firstName' => $candidate['firstName'] ?? $candidate['first_name'] ?? '',
            'lastName' => $candidate['lastName'] ?? $candidate['last_name'] ?? '',
            'email' => $candidate['email1'] ?? $candidate['email'] ?? '',
            'phone' => $candidate['phoneHome'] ?? $candidate['phone_home'] ?? '',
            'status' => $candidate['status'] ?? '',
            'dateAdded' => $candidate['dateCreated'] ?? $candidate['date_created'] ?? ''
        ];
    }

    /**
     * Format company for API response
     * @param array $company Company data
     * @return array Formatted company
     */
    public static function formatCompany($company)
    {
        return [
            'id' => intval($company['companyID'] ?? $company['company_id'] ?? 0),
            'name' => $company['name'] ?? '',
            'address' => [
                'address1' => $company['address'] ?? '',
                'city' => $company['city'] ?? '',
                'state' => $company['state'] ?? '',
                'zip' => $company['zip'] ?? ''
            ],
            'phone' => $company['phone1'] ?? $company['phone'] ?? '',
            'website' => $company['url'] ?? ''
        ];
    }

    /**
     * Format contact for API response (Bullhorn ClientContact equivalent)
     * @param array $contact Contact data
     * @return array Formatted contact
     */
    public static function formatContact($contact)
    {
        return [
            'id' => intval($contact['contactID'] ?? $contact['contact_id'] ?? 0),
            'firstName' => $contact['firstName'] ?? $contact['first_name'] ?? '',
            'lastName' => $contact['lastName'] ?? $contact['last_name'] ?? '',
            'title' => $contact['title'] ?? '',
            'email1' => $contact['email1'] ?? '',
            'email2' => $contact['email2'] ?? '',
            'phone' => $contact['phoneWork'] ?? $contact['phone_work'] ?? '',
            'phoneCell' => $contact['phoneCell'] ?? $contact['phone_cell'] ?? '',
            'address' => [
                'address1' => $contact['address'] ?? '',
                'city' => $contact['city'] ?? '',
                'state' => $contact['state'] ?? '',
                'zip' => $contact['zip'] ?? ''
            ],
            'clientCorporation' => [
                'id' => intval($contact['companyID'] ?? $contact['company_id'] ?? 0),
                'name' => $contact['companyName'] ?? $contact['company_name'] ?? ''
            ],
            'isHot' => (bool)($contact['isHot'] ?? $contact['is_hot'] ?? 0),
            'notes' => $contact['notes'] ?? '',
            'owner' => [
                'id' => intval($contact['owner'] ?? 0)
            ],
            'dateAdded' => $contact['dateCreated'] ?? $contact['date_created'] ?? ''
        ];
    }

    /**
     * Format placement for API response (Bullhorn-compatible)
     * @param array $placement Placement data
     * @return array Formatted placement
     */
    public static function formatPlacement($placement)
    {
        // Format candidate nested object
        $candidate = null;
        if (!empty($placement['candidateID'])) {
            $candidate = [
                'id' => intval($placement['candidateID']),
                'firstName' => $placement['candidateFirstName'] ?? '',
                'lastName' => $placement['candidateLastName'] ?? '',
                'email' => $placement['candidateEmail'] ?? ''
            ];
        }

        // Format job order nested object
        $jobOrder = null;
        if (!empty($placement['jobOrderID'])) {
            $jobOrder = [
                'id' => intval($placement['jobOrderID']),
                'title' => $placement['jobOrderTitle'] ?? ''
            ];
        }

        // Format client corporation nested object
        $clientCorporation = null;
        if (!empty($placement['companyID'])) {
            $clientCorporation = [
                'id' => intval($placement['companyID']),
                'name' => $placement['companyName'] ?? ''
            ];
        }

        // Format client contact nested object (nullable)
        $clientContact = null;
        if (!empty($placement['contactID'])) {
            $clientContact = [
                'id' => intval($placement['contactID']),
                'firstName' => $placement['contactFirstName'] ?? '',
                'lastName' => $placement['contactLastName'] ?? ''
            ];
        }

        // Format owner nested object
        $owner = null;
        if (!empty($placement['ownerID'])) {
            $owner = [
                'id' => intval($placement['ownerID']),
                'firstName' => $placement['ownerFirstName'] ?? '',
                'lastName' => $placement['ownerLastName'] ?? ''
            ];
        }

        return [
            'id' => intval($placement['placementID'] ?? 0),
            'candidate' => $candidate,
            'jobOrder' => $jobOrder,
            'clientCorporation' => $clientCorporation,
            'clientContact' => $clientContact,
            'status' => $placement['status'] ?? '',
            'startDate' => $placement['startDate'] ?? null,
            'endDate' => $placement['endDate'] ?? null,
            'salary' => isset($placement['salary']) && $placement['salary'] !== null ? floatval($placement['salary']) : null,
            'salaryType' => $placement['salaryType'] ?? 'Yearly',
            'fee' => isset($placement['fee']) && $placement['fee'] !== null ? floatval($placement['fee']) : null,
            'feeType' => $placement['feeType'] ?? 'Percentage',
            'billRate' => isset($placement['billRate']) && $placement['billRate'] !== null ? floatval($placement['billRate']) : null,
            'payRate' => isset($placement['payRate']) && $placement['payRate'] !== null ? floatval($placement['payRate']) : null,
            'referralFee' => isset($placement['referralFee']) && $placement['referralFee'] !== null ? floatval($placement['referralFee']) : null,
            'notes' => $placement['notes'] ?? '',
            'owner' => $owner,
            'dateAdded' => $placement['dateCreated'] ?? '',
            'dateLastModified' => $placement['dateModified'] ?? ''
        ];
    }

    /**
     * Format note for API response (Bullhorn-compatible)
     * @param array $note Note data
     * @return array Formatted note
     */
    public static function formatNote($note)
    {
        // Format commentingPerson (who the note is about)
        $commentingPerson = null;
        if (!empty($note['personType']) && !empty($note['personID'])) {
            $commentingPerson = [
                'type' => $note['personType'],
                'id' => intval($note['personID'])
            ];
        }

        // Format personReference (who entered the note)
        $personReference = null;
        if (!empty($note['enteredBy'])) {
            $personReference = [
                'id' => intval($note['enteredBy']),
                'firstName' => $note['enteredByFirstName'] ?? '',
                'lastName' => $note['enteredByLastName'] ?? '',
                'name' => $note['enteredByName'] ?? ''
            ];
        }

        // Format job order reference (optional)
        $jobOrder = null;
        if (!empty($note['jobOrderID'])) {
            $jobOrder = [
                'id' => intval($note['jobOrderID']),
                'title' => $note['jobOrderTitle'] ?? ''
            ];
        }

        return [
            'id' => intval($note['noteID'] ?? $note['note_id'] ?? 0),
            'action' => $note['action'] ?? '',
            'comments' => $note['comments'] ?? '',
            'commentingPerson' => $commentingPerson,
            'personReference' => $personReference,
            'jobOrder' => $jobOrder,
            'activityType' => [
                'id' => intval($note['activityType'] ?? $note['activity_type'] ?? 400),
                'name' => $note['activityTypeName'] ?? $note['activity_type_name'] ?? 'Other'
            ],
            'dateAdded' => $note['dateCreated'] ?? $note['date_created'] ?? '',
            'dateLastModified' => $note['dateModified'] ?? $note['date_modified'] ?? ''
        ];
    }

    /**
     * Format appointment for API response (Bullhorn-compatible)
     * @param array $appointment Appointment data
     * @return array Formatted appointment
     */
    public static function formatAppointment($appointment)
    {
        // Format associated person/entity
        $associatedPerson = null;
        if (!empty($appointment['personType']) && !empty($appointment['personID'])) {
            $associatedPerson = [
                'type' => $appointment['personType'],
                'id' => intval($appointment['personID'])
            ];
        }

        // Format owner
        $owner = null;
        if (!empty($appointment['owner'])) {
            $owner = [
                'id' => intval($appointment['owner']),
                'firstName' => $appointment['ownerFirstName'] ?? '',
                'lastName' => $appointment['ownerLastName'] ?? '',
                'name' => $appointment['ownerName'] ?? ''
            ];
        }

        // Format job order reference (optional)
        $jobOrder = null;
        if (!empty($appointment['jobOrderID'])) {
            $jobOrder = [
                'id' => intval($appointment['jobOrderID']),
                'title' => $appointment['jobOrderTitle'] ?? ''
            ];
        }

        return [
            'id' => intval($appointment['appointmentID'] ?? $appointment['appointment_id'] ?? 0),
            'title' => $appointment['title'] ?? '',
            'description' => $appointment['description'] ?? '',
            'type' => $appointment['type'] ?? 'Meeting',
            'startDate' => $appointment['startDate'] ?? $appointment['start_date'] ?? '',
            'endDate' => $appointment['endDate'] ?? $appointment['end_date'] ?? '',
            'allDay' => (bool)($appointment['allDay'] ?? $appointment['all_day'] ?? 0),
            'location' => $appointment['location'] ?? '',
            'status' => $appointment['status'] ?? 'Scheduled',
            'reminderMinutes' => isset($appointment['reminderMinutes']) ? intval($appointment['reminderMinutes']) : null,
            'associatedPerson' => $associatedPerson,
            'jobOrder' => $jobOrder,
            'owner' => $owner,
            'dateAdded' => $appointment['dateCreated'] ?? $appointment['date_created'] ?? '',
            'dateLastModified' => $appointment['dateModified'] ?? $appointment['date_modified'] ?? ''
        ];
    }

    /**
     * Format task for API response (Bullhorn-compatible)
     * @param array $task Task data
     * @return array Formatted task
     */
    public static function formatTask($task)
    {
        // Format associated person/entity
        $associatedPerson = null;
        if (!empty($task['personType']) && !empty($task['personID'])) {
            $associatedPerson = [
                'type' => $task['personType'],
                'id' => intval($task['personID'])
            ];
        }

        // Format owner
        $owner = null;
        if (!empty($task['owner'])) {
            $owner = [
                'id' => intval($task['owner']),
                'firstName' => $task['ownerFirstName'] ?? '',
                'lastName' => $task['ownerLastName'] ?? '',
                'name' => $task['ownerName'] ?? ''
            ];
        }

        // Format assigned to (may be different from owner)
        $assignedTo = null;
        if (!empty($task['assignedTo'])) {
            $assignedTo = [
                'id' => intval($task['assignedTo']),
                'firstName' => $task['assignedToFirstName'] ?? '',
                'lastName' => $task['assignedToLastName'] ?? '',
                'name' => $task['assignedToName'] ?? ''
            ];
        }

        // Format job order reference (optional)
        $jobOrder = null;
        if (!empty($task['jobOrderID'])) {
            $jobOrder = [
                'id' => intval($task['jobOrderID']),
                'title' => $task['jobOrderTitle'] ?? ''
            ];
        }

        return [
            'id' => intval($task['taskID'] ?? $task['task_id'] ?? 0),
            'subject' => $task['subject'] ?? '',
            'description' => $task['description'] ?? '',
            'status' => $task['status'] ?? 'Not Started',
            'priority' => $task['priority'] ?? 'Normal',
            'dueDate' => $task['dueDate'] ?? $task['due_date'] ?? null,
            'startDate' => $task['startDate'] ?? $task['start_date'] ?? null,
            'reminderDate' => $task['reminderDate'] ?? $task['reminder_date'] ?? null,
            'associatedPerson' => $associatedPerson,
            'jobOrder' => $jobOrder,
            'owner' => $owner,
            'assignedTo' => $assignedTo,
            'isCompleted' => ($task['status'] ?? '') === 'Completed',
            'dateCompleted' => $task['dateCompleted'] ?? $task['date_completed'] ?? null,
            'dateAdded' => $task['dateCreated'] ?? $task['date_created'] ?? '',
            'dateLastModified' => $task['dateModified'] ?? $task['date_modified'] ?? ''
        ];
    }

    /**
     * Format attachment for API response
     * @param array $attachment Attachment data
     * @return array Formatted attachment
     */
    public static function formatAttachment($attachment)
    {
        // Data item type mapping for human-readable names
        $dataItemTypeNames = [
            100 => 'Candidate',
            200 => 'Company',
            300 => 'Contact',
            400 => 'JobOrder',
            500 => 'BulkResume',
            600 => 'User',
            700 => 'List',
            800 => 'Pipeline',
            900 => 'Duplicate',
            1000 => 'Placement',
            1100 => 'JobSubmission',
            1200 => 'Task',
            1300 => 'Appointment',
            1400 => 'Note'
        ];

        $dataItemType = intval($attachment['dataItemType'] ?? $attachment['data_item_type'] ?? 0);
        $dataItemTypeName = isset($dataItemTypeNames[$dataItemType]) ? $dataItemTypeNames[$dataItemType] : 'Unknown';

        return [
            'id' => intval($attachment['attachmentID'] ?? $attachment['attachment_id'] ?? 0),
            'title' => $attachment['title'] ?? '',
            'originalFilename' => $attachment['originalFilename'] ?? $attachment['original_filename'] ?? '',
            'storedFilename' => $attachment['storedFilename'] ?? $attachment['stored_filename'] ?? '',
            'contentType' => $attachment['contentType'] ?? $attachment['content_type'] ?? 'application/octet-stream',
            'fileSize' => intval($attachment['fileSizeKB'] ?? $attachment['file_size_kb'] ?? 0) * 1024,
            'fileSizeKB' => intval($attachment['fileSizeKB'] ?? $attachment['file_size_kb'] ?? 0),
            'dataItemType' => $dataItemType,
            'dataItemTypeName' => $dataItemTypeName,
            'dataItemId' => intval($attachment['dataItemID'] ?? $attachment['data_item_id'] ?? 0),
            'isResume' => isset($attachment['hasText']) ? (bool)$attachment['hasText'] : false,
            'isProfileImage' => (bool)($attachment['isProfileImage'] ?? $attachment['profile_image'] ?? 0),
            'md5sum' => $attachment['md5sum'] ?? $attachment['md5_sum'] ?? '',
            'dateCreated' => $attachment['dateCreated'] ?? $attachment['date_created'] ?? '',
            'downloadUrl' => sprintf('/api/v1/attachments?id=%d&download=1', intval($attachment['attachmentID'] ?? $attachment['attachment_id'] ?? 0))
        ];
    }
}
