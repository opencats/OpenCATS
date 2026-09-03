<?php
/*
 * OpenCATS
 *
 * Portions Copyright (C) 2005-2007 Cognizo Technologies, Inc.
 * Originally released as part of CATS Standard Edition under the
 * CATS Public License 1.1a.
 *
 * See LICENSE.md.
 */

function update_372($db)
{
    $tables = array(
        'joborder' => array(
            'primaryKey' => 'joborder_id',
            'columns' => array(
                'title',
                'description',
                'notes',
                'city',
                'state',
                'duration',
                'rate_max',
                'salary',
                'client_job_id'
            )
        ),
        'company' => array(
            'primaryKey' => 'company_id',
            'columns' => array(
                'name',
                'address',
                'city',
                'state',
                'zip',
                'url',
                'key_technologies',
                'notes'
            )
        ),
        'contact' => array(
            'primaryKey' => 'contact_id',
            'columns' => array(
                'first_name',
                'last_name',
                'title',
                'email1',
                'email2',
                'address',
                'city',
                'state',
                'zip',
                'notes'
            )
        ),
        'candidate' => array(
            'primaryKey' => 'candidate_id',
            'columns' => array(
                'first_name',
                'middle_name',
                'last_name',
                'email1',
                'email2',
                'address',
                'city',
                'state',
                'notes',
                'key_skills',
                'current_employer',
                'current_position',
                'source',
                'web_site',
                'best_time_to_call',
                'desired_pay',
                'current_pay'
            )
        ),
        'activity' => array(
            'primaryKey' => 'activity_id',
            'columns' => array('notes')
        ),
        'calendar_event' => array(
            'primaryKey' => 'calendar_event_id',
            'columns' => array(
                'title',
                'description',
                'location',
                'reminder_email'
            )
        ),
        'history' => array(
            'primaryKey' => 'history_id',
            'columns' => array(
                'previous_value',
                'new_value',
                'description'
            )
        ),
        'email_history' => array(
            'primaryKey' => 'email_history_id',
            'columns' => array(
                'text',
                'recipients'
            )
        ),
        'email_template' => array(
            'primaryKey' => 'email_template_id',
            'columns' => array(
                'text',
                'title',
                'possible_variables'
            )
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
            'columns' => array(
                'feedback',
                'subject'
            )
        )
    );

    $batchSize = 100;

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
                'SHOW COLUMNS FROM `' . $tableName . '` LIKE '
                . $db->makeQueryString($columnName)
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

        $selectColumns = array_merge(
            array($tableData['primaryKey']),
                                     $columnsToUpdate
        );

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

        $lastID = 0;

        while (true)
        {
            $rs = $db->getAllAssoc(
                'SELECT ' . implode(', ', $selectParts)
                . ' FROM `' . $tableName . '`'
                . ' WHERE `' . $tableData['primaryKey'] . '` > '
                . $db->makeQueryInteger($lastID)
                . ' AND (' . implode(' OR ', $whereParts) . ')'
                . ' ORDER BY `' . $tableData['primaryKey'] . '` ASC'
                . ' LIMIT ' . $batchSize
            );

            if (empty($rs))
            {
                break;
            }

            foreach ($rs as $row)
            {
                /*
                 * Always advance the keyset position, even when this row
                 * contains an ampersand that cannot be decoded.
                 */
                $lastID = $row[$tableData['primaryKey']];

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
                        $nextValue = html_entity_decode(
                            $decodedValue,
                            ENT_QUOTES,
                            HTML_ENCODING
                        );

                        if ($nextValue === $decodedValue)
                        {
                            break;
                        }

                        $decodedValue = $nextValue;
                    }

                    if ($decodedValue !== $originalValue)
                    {
                        $updates[] = '`' . $columnName . '` = '
                        . $db->makeQueryString($decodedValue);
                    }
                }

                if (!empty($updates))
                {
                    $db->query(
                        'UPDATE `' . $tableName . '` SET '
                        . implode(', ', $updates)
                        . ' WHERE `' . $tableData['primaryKey'] . '` = '
                        . $db->makeQueryInteger(
                            $row[$tableData['primaryKey']]
                        )
                    );
                }
            }
        }
    }
}

?>
