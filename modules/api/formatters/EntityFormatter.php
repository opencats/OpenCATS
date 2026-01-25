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
}
