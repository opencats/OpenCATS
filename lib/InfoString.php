<?php
/**
 * CATS
 * Data Item Info String Library
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
 * @version    $Id: InfoString.php 3587 2007-11-13 03:55:57Z will $
 */

/**
 *	Data Item Info String Library
 *	@package    CATS
 *	@subpackage Library
 */
class InfoString
{
    /* Prevent this class from being instantiated. */
    private function __construct() {}
    private function __clone() {}


    /**
     * Generates a string of info used for the popup tooltips
     *
     * @param flag data item type
     * @param integer data item ID
     * @param integer site ID
     * @return string info string or false
     */
    public static function make($dataItemType, $dataItemID, $siteID)
    {
        switch ($dataItemType)
        {
            case DATA_ITEM_CANDIDATE:
                $infoString = self::_candidate($dataItemID, $siteID);
                break;

            case DATA_ITEM_CONTACT:
                $infoString = self::_contact($dataItemID, $siteID);
                break;

            case DATA_ITEM_JOBORDER:
                $infoString = self::_joborder($dataItemID, $siteID);
                break;

            case DATA_ITEM_COMPANY:
                $infoString = self::_company($dataItemID, $siteID);
                break;

            default:
                return false;
                break;
        }

        return $infoString;
    }

    /**
     * Generates a string of contact info used for the popup tooltips.
     *
     * @param integer contact ID
     * @param integer site ID
     * @return string info string
     */
    private static function _contact($contactID, $siteID)
    {
        $contacts = new Contacts($siteID);
        $infoRS = $contacts->get($contactID);

        if (empty($infoRS))
        {
            return 'The specified contact could not be found.';
        }

        $infoString = sprintf(
            '<span class="bold">Contact:</span>&nbsp;%s %s',
            htmlspecialchars($infoRS['firstName']),
            htmlspecialchars($infoRS['lastName'])
        );

        if (!empty($infoRS['title']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Title:</span>&nbsp;%s',
                htmlspecialchars($infoRS['title'])
            );
        }

        if (!empty($infoRS['companyName']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Company:</span>&nbsp;%s',
                htmlspecialchars($infoRS['companyName'])
            );
        }

        if (!empty($infoRS['department']))
        {
             $infoString .= sprintf(
                 '<br /><span class="bold">Department:</span>&nbsp;%s',
                 htmlspecialchars($infoRS['department'])
             );
        }

        if (!empty($infoRS['email1']))
        {
             $infoString .= sprintf(
                 '<br /><span class="bold">Primary Email:</span>&nbsp;%s',
                 htmlspecialchars($infoRS['email1'])
             );
        }

        if (!empty($infoRS['email2']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Secondary Email:</span>&nbsp;%s',
                htmlspecialchars($infoRS['email2'])
            );
        }

        if (!empty($infoRS['phoneWork']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Work Phone:</span>&nbsp;%s',
                htmlspecialchars($infoRS['phoneWork'])
            );
        }

        if (!empty($infoRS['phoneCell']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Cell Phone:</span>&nbsp;%s',
                htmlspecialchars($infoRS['phoneCell'])
            );
        }

        if (!empty($infoRS['phoneOther']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Other Phone:</span>&nbsp;%s',
                htmlspecialchars($infoRS['phoneOther'])
            );
        }

        if (!empty($infoRS['address']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Address:</span><br />&nbsp;&nbsp;%s',
                htmlspecialchars($infoRS['address'])
            );

            if (!empty($infoRS['city']))
            {
                $infoString .= sprintf(
                    '&nbsp;%s',
                    htmlspecialchars($infoRS['city'])
                );
            }

            if (!empty($infoRS['state']))
            {
                $infoString .= sprintf(
                    '&nbsp;%s',
                    htmlspecialchars($infoRS['state'])
                );
            }

            if (!empty($infoRS['zip']))
            {
                $infoString .= sprintf(
                    '&nbsp;%s',
                    htmlspecialchars($infoRS['zip'])
                );
            }
        }
        return $infoString;
    }

    /**
     * Generates a string of Candidate info used for the popup tooltips.
     *
     * @param integer candidate ID
     * @param integer site ID
     * @return string info string
     */
    private static function _candidate($candidateID, $siteID)
    {
        $candidates = new Candidates($siteID);
        $infoRS = $candidates->get($candidateID);

        if (empty($infoRS))
        {
            return 'The specified candidate could not be found.';
        }

        $infoString = sprintf(
            '<span class="bold">Candidate:</span>&nbsp;%s %s',
            htmlspecialchars($infoRS['firstName']),
            htmlspecialchars($infoRS['lastName'])
        );

        if (!empty($infoRS['currentEmployer']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Current Employer:</span>&nbsp;%s',
                htmlspecialchars($infoRS['currentEmployer'])
            );
        }

        if (!empty($infoRS['email1']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Primary Email:</span>&nbsp;%s',
                htmlspecialchars($infoRS['email1'])
            );
        }

        if (!empty($infoRS['email2']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Secondary Email:</span>&nbsp;%s',
                htmlspecialchars($infoRS['email2'])
            );
        }

        if (!empty($infoRS['phoneHome']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Home Phone:</span>&nbsp;%s',
                htmlspecialchars($infoRS['phoneHome'])
            );
        }

        if (!empty($infoRS['phoneWork']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Work Phone:</span>&nbsp;%s',
                htmlspecialchars($infoRS['phoneWork'])
            );
        }

        if (!empty($infoRS['phoneCell']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Cell Phone:</span>&nbsp;%s',
                htmlspecialchars($infoRS['phoneCell'])
            );
        }

        if (!empty($infoRS['address']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Address:</span><br />&nbsp;&nbsp;%s',
                htmlspecialchars($infoRS['address'])
            );

            if (!empty($infoRS['city']))
            {
                $infoString .= sprintf(
                    '&nbsp;%s',
                    htmlspecialchars($infoRS['city'])
                );
            }

            if (!empty($infoRS['state']))
            {
                $infoString .= sprintf(
                    '&nbsp;%s',
                    htmlspecialchars($infoRS['state'])
                );
            }

            if (!empty($infoRS['zip']))
            {
                $infoString .= sprintf(
                    '&nbsp;%s',
                    htmlspecialchars($infoRS['zip'])
                );
            }
        }

        return $infoString;
    }

    /**
     * Generates a string of Job Order info used for the popup tooltips.
     *
     * @param integer job order ID
     * @param integer site ID
     * @return string info string
     */
    private static function _joborder($jobOrderID, $siteID)
    {
        $jobOrders = new JobOrders($siteID);
        $infoRS = $jobOrders->get($jobOrderID);

        if (empty($infoRS))
        {
            return 'The specified job order could not be found.';
        }

        $infoString = sprintf(
            '<span class="bold">Job Order:</span>&nbsp;%s',
            htmlspecialchars($infoRS['title'])
        );

        if (!empty($infoRS['type']))
        {
            $infoRS['type'] = $jobOrders->typeCodeToString($infoRS['type']);

            $infoString .= sprintf(
                '<br /><span class="bold">Type:</span>&nbsp;%s',
                htmlspecialchars($infoRS['type'])
            );
        }

        if (!empty($infoRS['openings']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Openings:</span>&nbsp;%s',
                htmlspecialchars($infoRS['openings'])
            );
        }

        if (!empty($infoRS['salary']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Salary:</span>&nbsp;%s',
                htmlspecialchars($infoRS['salary'])
            );
        }

        if (!empty($infoRS['maxRate']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Max Rate:</span>&nbsp;%s',
                htmlspecialchars($infoRS['maxRate'])
            );
        }

        if (!empty($infoRS['recruiterFullName']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Recruiter:</span>&nbsp;%s',
                htmlspecialchars($infoRS['recruiterFullName'])
            );
        }

        if (!empty($infoRS['startDate']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Start Date:</span>&nbsp;%s',
                htmlspecialchars($infoRS['startDate'])
            );
        }

        return $infoString;
    }

    /**
     * Generates a string of Company info used for the popup tooltips.
     *
     * @param integer company ID
     * @param integer site ID
     * @return string info string
     */
    private static function _company($companyID, $siteID)
    {
        $companies = new Companies($siteID);
        $infoRS = $companies->get($companyID);

        if (empty($infoRS))
        {
            return 'The specified company could not be found.';
        }

        $infoString = sprintf(
            '<span class="bold">Company:</span>&nbsp;%s',
            htmlspecialchars($infoRS['name'])
        );

        if (!empty($infoRS['billingContactFullName']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Billing Contact:</span>&nbsp;%s',
                htmlspecialchars($infoRS['billingContactFullName'])
            );
        }

        if (!empty($infoRS['phone1']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Primary Phone:</span>&nbsp;%s',
                htmlspecialchars($infoRS['phone1'])
            );
        }

        if (!empty($infoRS['phone2']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Secondary Phone:</span>&nbsp;%s',
                htmlspecialchars($infoRS['phone2'])
            );
        }

        if (!empty($infoRS['faxNumber']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Fax Number:</span>&nbsp;%s',
                htmlspecialchars($infoRS['faxNumber'])
            );
        }

        if (!empty($infoRS['keyTechnologies']))
        {
            $infoString .= sprintf(
                '<br /><span class="bold">Key Technologies:</span>&nbsp;%s',
                htmlspecialchars($infoRS['keyTechnologies'])
            );
        }

        return $infoString;
    }
}

?>
