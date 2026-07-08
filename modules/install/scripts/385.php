<?php
/*
 * CATS
 * Update 385 - cleanup orphaned entity records and references
 */

function update_385($db)
{
    /* Collect orphaned attachment directories before deleting metadata rows. */
    $orphanAttachmentDirectories = array();

    $orphanAttachmentDirectoriesRS = $db->query(
        "SELECT DISTINCT
            site_id,
            directory_name
         FROM attachment
         WHERE (
                data_item_type = 100
            AND NOT EXISTS (
                    SELECT 1
                    FROM candidate
                    WHERE candidate.candidate_id = attachment.data_item_id
                      AND candidate.site_id = attachment.site_id
                )
         ) OR (
                data_item_type = 200
            AND NOT EXISTS (
                    SELECT 1
                    FROM company
                    WHERE company.company_id = attachment.data_item_id
                      AND company.site_id = attachment.site_id
                )
         ) OR (
                data_item_type = 300
            AND NOT EXISTS (
                    SELECT 1
                    FROM contact
                    WHERE contact.contact_id = attachment.data_item_id
                      AND contact.site_id = attachment.site_id
                )
         ) OR (
                data_item_type = 400
            AND NOT EXISTS (
                    SELECT 1
                    FROM joborder
                    WHERE joborder.joborder_id = attachment.data_item_id
                      AND joborder.site_id = attachment.site_id
                )
         )"
    );

    while ($directoryData = mysqli_fetch_assoc($orphanAttachmentDirectoriesRS))
    {
        $directoryName = trim($directoryData['directory_name']);
        if ($directoryName == '')
        {
            continue;
        }

        $siteID = (int) $directoryData['site_id'];
        $orphanAttachmentDirectories[$siteID . ':' . $directoryName] = array(
            'siteID' => $siteID,
            'directoryName' => $directoryName
        );
    }

    $queries = array(
        /* Remove orphaned entity-owned activity rows. */
        "DELETE FROM activity
         WHERE data_item_type = 100
           AND NOT EXISTS (
                SELECT 1
                FROM candidate
                WHERE candidate.candidate_id = activity.data_item_id
                  AND candidate.site_id = activity.site_id
           )",
        "DELETE FROM activity
         WHERE data_item_type = 200
           AND NOT EXISTS (
                SELECT 1
                FROM company
                WHERE company.company_id = activity.data_item_id
                  AND company.site_id = activity.site_id
           )",
        "DELETE FROM activity
         WHERE data_item_type = 300
           AND NOT EXISTS (
                SELECT 1
                FROM contact
                WHERE contact.contact_id = activity.data_item_id
                  AND contact.site_id = activity.site_id
           )",
        "DELETE FROM activity
         WHERE data_item_type = 400
           AND NOT EXISTS (
                SELECT 1
                FROM joborder
                WHERE joborder.joborder_id = activity.data_item_id
                  AND joborder.site_id = activity.site_id
           )",

        /* Remove orphaned entity-owned calendar rows. */
        "DELETE FROM calendar_event
         WHERE data_item_type = 100
           AND NOT EXISTS (
                SELECT 1
                FROM candidate
                WHERE candidate.candidate_id = calendar_event.data_item_id
                  AND candidate.site_id = calendar_event.site_id
           )",
        "DELETE FROM calendar_event
         WHERE data_item_type = 200
           AND NOT EXISTS (
                SELECT 1
                FROM company
                WHERE company.company_id = calendar_event.data_item_id
                  AND company.site_id = calendar_event.site_id
           )",
        "DELETE FROM calendar_event
         WHERE data_item_type = 300
           AND NOT EXISTS (
                SELECT 1
                FROM contact
                WHERE contact.contact_id = calendar_event.data_item_id
                  AND contact.site_id = calendar_event.site_id
           )",
        "DELETE FROM calendar_event
         WHERE data_item_type = 400
           AND NOT EXISTS (
                SELECT 1
                FROM joborder
                WHERE joborder.joborder_id = calendar_event.data_item_id
                  AND joborder.site_id = calendar_event.site_id
           )",

        /* Remove orphaned entity-owned attachment rows. */
        "DELETE FROM attachment
         WHERE data_item_type = 100
           AND NOT EXISTS (
                SELECT 1
                FROM candidate
                WHERE candidate.candidate_id = attachment.data_item_id
                  AND candidate.site_id = attachment.site_id
           )",
        "DELETE FROM attachment
         WHERE data_item_type = 200
           AND NOT EXISTS (
                SELECT 1
                FROM company
                WHERE company.company_id = attachment.data_item_id
                  AND company.site_id = attachment.site_id
           )",
        "DELETE FROM attachment
         WHERE data_item_type = 300
           AND NOT EXISTS (
                SELECT 1
                FROM contact
                WHERE contact.contact_id = attachment.data_item_id
                  AND contact.site_id = attachment.site_id
           )",
        "DELETE FROM attachment
         WHERE data_item_type = 400
           AND NOT EXISTS (
                SELECT 1
                FROM joborder
                WHERE joborder.joborder_id = attachment.data_item_id
                  AND joborder.site_id = attachment.site_id
           )",

        /* Remove orphaned candidate-related rows. */
        "DELETE FROM candidate_tag
         WHERE NOT EXISTS (
                SELECT 1
                FROM candidate
                WHERE candidate.candidate_id = candidate_tag.candidate_id
                  AND candidate.site_id = candidate_tag.site_id
           )",
        "DELETE FROM career_portal_questionnaire_history
         WHERE NOT EXISTS (
                SELECT 1
                FROM candidate
                WHERE candidate.candidate_id = career_portal_questionnaire_history.candidate_id
                  AND candidate.site_id = career_portal_questionnaire_history.site_id
           )",
        "DELETE FROM candidate_duplicates
         WHERE NOT EXISTS (
                SELECT 1
                FROM candidate
                WHERE candidate.candidate_id = candidate_duplicates.old_candidate_id
                  AND candidate.site_id = candidate_duplicates.site_id
           )
            OR NOT EXISTS (
                SELECT 1
                FROM candidate
                WHERE candidate.candidate_id = candidate_duplicates.new_candidate_id
                  AND candidate.site_id = candidate_duplicates.site_id
           )",

        /* Remove orphaned company-related rows. */
        "DELETE FROM company_department
         WHERE NOT EXISTS (
                SELECT 1
                FROM company
                WHERE company.company_id = company_department.company_id
                  AND company.site_id = company_department.site_id
           )",

        /* Remove orphaned pipeline rows. */
        "DELETE FROM candidate_joborder
         WHERE NOT EXISTS (
                SELECT 1
                FROM candidate
                WHERE candidate.candidate_id = candidate_joborder.candidate_id
                  AND candidate.site_id = candidate_joborder.site_id
           )
            OR NOT EXISTS (
                SELECT 1
                FROM joborder
                WHERE joborder.joborder_id = candidate_joborder.joborder_id
                  AND joborder.site_id = candidate_joborder.site_id
           )",
        "DELETE FROM candidate_joborder_status_history
         WHERE NOT EXISTS (
                SELECT 1
                FROM candidate
                WHERE candidate.candidate_id = candidate_joborder_status_history.candidate_id
                  AND candidate.site_id = candidate_joborder_status_history.site_id
           )
            OR NOT EXISTS (
                SELECT 1
                FROM joborder
                WHERE joborder.joborder_id = candidate_joborder_status_history.joborder_id
                  AND joborder.site_id = candidate_joborder_status_history.site_id
           )",

        /* Remove orphaned saved list entries by site-scoped data item. */
        "DELETE FROM saved_list_entry
         WHERE data_item_type = 100
           AND NOT EXISTS (
                SELECT 1
                FROM candidate
                WHERE candidate.candidate_id = saved_list_entry.data_item_id
                  AND candidate.site_id = saved_list_entry.site_id
           )",
        "DELETE FROM saved_list_entry
         WHERE data_item_type = 200
           AND NOT EXISTS (
                SELECT 1
                FROM company
                WHERE company.company_id = saved_list_entry.data_item_id
                  AND company.site_id = saved_list_entry.site_id
           )",
        "DELETE FROM saved_list_entry
         WHERE data_item_type = 300
           AND NOT EXISTS (
                SELECT 1
                FROM contact
                WHERE contact.contact_id = saved_list_entry.data_item_id
                  AND contact.site_id = saved_list_entry.site_id
           )",
        "DELETE FROM saved_list_entry
         WHERE data_item_type = 400
           AND NOT EXISTS (
                SELECT 1
                FROM joborder
                WHERE joborder.joborder_id = saved_list_entry.data_item_id
                  AND joborder.site_id = saved_list_entry.site_id
           )",

        /* Reset orphaned references to the current no-link value. */
        "UPDATE activity
         SET joborder_id = NULL
         WHERE joborder_id IS NOT NULL
           AND NOT EXISTS (
                SELECT 1
                FROM joborder
                WHERE joborder.joborder_id = activity.joborder_id
                  AND joborder.site_id = activity.site_id
           )",
        "UPDATE calendar_event
         SET joborder_id = -1
         WHERE joborder_id IS NOT NULL
           AND NOT EXISTS (
                SELECT 1
                FROM joborder
                WHERE joborder.joborder_id = calendar_event.joborder_id
                  AND joborder.site_id = calendar_event.site_id
           )",
        "UPDATE joborder
         SET contact_id = -1
         WHERE NOT EXISTS (
                SELECT 1
                FROM contact
                WHERE contact.contact_id = joborder.contact_id
                  AND contact.site_id = joborder.site_id
           )",
        "UPDATE company
         SET billing_contact = -1
         WHERE NOT EXISTS (
                SELECT 1
                FROM contact
                WHERE contact.contact_id = company.billing_contact
                  AND contact.site_id = company.site_id
           )",
        "UPDATE contact
         SET reports_to = -1
         WHERE NOT EXISTS (
                SELECT 1
                FROM contact AS reports_to_contact
                WHERE reports_to_contact.contact_id = contact.reports_to
                  AND reports_to_contact.site_id = contact.site_id
           )"
    );

    foreach ($queries as $query)
    {
        $db->query($query);
    }

    /* Remove orphaned attachment directories if no metadata still references them. */
    foreach ($orphanAttachmentDirectories as $directoryData)
    {
        $sql = sprintf(
            "SELECT
                attachment_id
            FROM
                attachment
            WHERE
                site_id = %s
            AND
                directory_name = %s
            LIMIT 1",
            $db->makeQueryInteger($directoryData['siteID']),
            $db->makeQueryString($directoryData['directoryName'])
        );
        $stillReferenced = $db->getAssoc($sql);
        if (!empty($stillReferenced))
        {
            continue;
        }

        $directoryName = trim($directoryData['directoryName']);
        if ($directoryName == '' || $directoryName == '.')
        {
            continue;
        }

        $attachmentsRoot = @realpath('attachments');
        if ($attachmentsRoot === false)
        {
            continue;
        }

        $directory = @realpath('attachments/' . $directoryName);
        if ($directory === false || $directory == $attachmentsRoot)
        {
            continue;
        }

        if (strpos($directory, $attachmentsRoot . DIRECTORY_SEPARATOR) !== 0)
        {
            continue;
        }

        $stack = array(array($directory, false));
        while (!empty($stack))
        {
            $item = array_pop($stack);
            $path = $item[0];
            $visited = $item[1];

            if ($visited)
            {
                @rmdir($path);
                continue;
            }

            $stack[] = array($path, true);

            $handle = @opendir($path);
            if (!$handle)
            {
                continue;
            }

            while (($file = readdir($handle)) !== false)
            {
                if ($file == '.' || $file == '..')
                {
                    continue;
                }

                $childPath = $path . '/' . $file;
                if (is_dir($childPath))
                {
                    $stack[] = array($childPath, false);
                }
                else
                {
                    @unlink($childPath);
                }
            }

            closedir($handle);
        }
    }
}

?>
