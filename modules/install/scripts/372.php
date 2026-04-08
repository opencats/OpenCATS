<?php
/*
 * CATS
 * Update 372 - decode HTML entities until stable for text fields
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
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
 * $Id: 372.php $
 */

function update_372($db)
{
    $tables = array(
        'joborder' => array(
            'primaryKey' => 'joborder_id',
            'columns' => array('title', 'description', 'notes', 'city', 'state', 'duration', 'rate_max', 'salary', 'client_job_id')
        ),
        'company' => array(
            'primaryKey' => 'company_id',
            'columns' => array('name', 'address', 'city', 'state', 'zip', 'url', 'key_technologies', 'notes')
        ),
        'contact' => array(
            'primaryKey' => 'contact_id',
            'columns' => array('first_name', 'last_name', 'title', 'email1', 'email2', 'address', 'city', 'state', 'zip', 'notes')
        ),
        'candidate' => array(
            'primaryKey' => 'candidate_id',
            'columns' => array('first_name', 'middle_name', 'last_name', 'email1', 'email2', 'address', 'city', 'state', 'notes', 'key_skills', 'current_employer', 'current_position', 'source', 'web_site', 'best_time_to_call', 'desired_pay', 'current_pay')
        ),
        'activity' => array(
            'primaryKey' => 'activity_id',
            'columns' => array('notes')
        ),
        'calendar_event' => array(
            'primaryKey' => 'calendar_event_id',
            'columns' => array('title', 'description', 'location', 'reminder_email')
        ),
        'history' => array(
            'primaryKey' => 'history_id',
            'columns' => array('previous_value', 'new_value', 'description')
        ),
        'email_history' => array(
            'primaryKey' => 'email_history_id',
            'columns' => array('text', 'recipients')
        ),
        'email_template' => array(
            'primaryKey' => 'email_template_id',
            'columns' => array('text', 'title', 'possible_variables')
        ),
        'extra_field' => array(
            'primaryKey' => 'extra_field_id',
            'columns' => array('value')
        ),
        'attachment' => array(
            'primaryKey' => 'attachment_id',
            'columns' => array('text')
        ),
        'feedback' => array(
            'primaryKey' => 'feedback_id',
            'columns' => array('feedback', 'subject')
        )
    );

    foreach ($tables as $tableName => $tableData)
    {
        $tableExists = $db->getAllAssoc(
            'SHOW TABLES LIKE ' . $db->makeQueryString($tableName)
        );

        if (empty($tableExists))
        {
            continue;
        }

        $columnsToUpdate = array();
        foreach ($tableData['columns'] as $columnName)
        {
            $columnExists = $db->getAllAssoc(
                'SHOW COLUMNS FROM `' . $tableName . '` LIKE ' . $db->makeQueryString($columnName)
            );
            if (!empty($columnExists))
            {
                $columnsToUpdate[] = $columnName;
            }
        }

        if (empty($columnsToUpdate))
        {
            continue;
        }

        $selectColumns = array_merge(array($tableData['primaryKey']), $columnsToUpdate);
        $selectParts = array();
        foreach ($selectColumns as $columnName)
        {
            $selectParts[] = '`' . $columnName . '`';
        }

        $whereParts = array();
        foreach ($columnsToUpdate as $columnName)
        {
            $whereParts[] = '`' . $columnName . "` LIKE '%&%'";
        }

        $rs = $db->getAllAssoc(
            'SELECT ' . implode(', ', $selectParts) . ' FROM `' . $tableName . '` WHERE ' . implode(' OR ', $whereParts)
        );

        foreach ($rs as $rowIndex => $row)
        {
            $updates = array();
            foreach ($columnsToUpdate as $columnName)
            {
                if (!isset($row[$columnName]))
                {
                    continue;
                }

                $originalValue = $row[$columnName];
                $decodedValue = $originalValue;
                $maxDecodePasses = 10;
                for ($i = 0; $i < $maxDecodePasses; $i++)
                {
                    $nextValue = html_entity_decode($decodedValue, ENT_QUOTES, HTML_ENCODING);
                    if ($nextValue === $decodedValue)
                    {
                        break;
                    }
                    $decodedValue = $nextValue;
                }

                if ($decodedValue !== $originalValue)
                {
                    $updates[] = '`' . $columnName . '` = ' . $db->makeQueryString($decodedValue);
                }
            }

            if (!empty($updates))
            {
                $db->query(
                    'UPDATE `' . $tableName . '` SET ' . implode(', ', $updates)
                    . ' WHERE `' . $tableData['primaryKey'] . '` = '
                    . $db->makeQueryInteger($row[$tableData['primaryKey']])
                );
            }
        }
    }
}


?>
