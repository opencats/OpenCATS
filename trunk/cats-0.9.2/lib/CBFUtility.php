<?php
include_once('./lib/DatabaseConnection.php');

define('CBF_HANDSHAKE', 'C');

// Only legacy changes should be incrementing the version
define('CBF_VERSION', 1000);

// The tables that are included in CBF backup archives (tables w/out site_id ignored)
define('CBF_TABLES', 'access_level, activity, activity_type, attachment, calendar_event, '
    . 'calendar_event_type, candidate, '
    . 'candidate_joborder, candidate_joborder_status, candidate_joborder_status_history, '
    . 'candidate_source, '
    . 'career_portal_template, career_portal_template_site, company, company_department, '
    . 'contact, data_item_type, eeo_ethnic_type, eeo_veteran_type, email_history, '
    . 'email_template, extra_field, extra_field_settings, joborder, '
    . 'saved_list, saved_list_entry, saved_search, settings, site, user'
);

// Use swap for GUID caching. Slower, but has a much smaller memory footprint.
define('CBF_GUID_SWAP_ENABLED', false);


class CBFUtility
{
    private $_db;
    private $_siteID;
    private $_structure;
    private $_keys;
    private $_GUID;
    private $_GUIDs;
    private $_GUIDRestores;
    private $_GUIDSwap;
    private $_GUIDSwapEnabled;
    private $_dataOverwrite;


    public function __construct($siteID = 1, $dataOverwrite = true)
    {
        $siteID = $_SESSION['CATS']->getSiteID();
        $this->_db = DatabaseConnection::getInstance();
        $this->_structure = array();
        $this->_keys = array();
        $this->_siteID = $siteID;
        $this->_GUID = 0;
        $this->_GUIDs = array();
        $this->_GUIDRestores = array();
        $this->_dataOverwrite = $dataOverwrite;
    }

    public function into()
    {
        //$this->deleteSiteData();
        $this->doCreateBackup('TMP');
        $this->doRestoreBackup('TMP');
    }

    // FIXME: Document me.
    public function doBuildStructure()
    {
        $tables = preg_split('/[\,\;\-\t ]+/', CBF_TABLES);
        foreach ($tables as $tableName)
        {
            $this->doScanTable(strtolower($tableName));
        }
    }

    // FIXME: Document me.
    public function doBuildAssociations()
    {
        foreach ($this->_structure as $tableName => $tableData)
        {
            foreach ($tableData as $fieldName => $fieldData)
            {
                if ($fieldData['PRI'])
                {
                    continue;
                }

                foreach ($this->_keys as $keyFieldName => $keyTableName)
                {
                    if (!strcmp($keyFieldName, $fieldName))
                    {
                        $this->_structure[$tableName][$fieldName]['foreign'] = $keyTableName;
                    }
                }
            }
        }
    }

    public function doScanTable($tableName)
    {
        $hasSiteID = false;
        $tableStructure = array();

        $sql = sprintf(
            "SHOW COLUMNS
             FROM
                %s",
            $tableName
        );

        $rs = $this->_db->getAllAssoc($sql);
        if (empty($rs))
        {
            return false;
        }

        foreach ($rs as $row)
        {
            $tableStructure[$row['Field']] = array(
                'Type' => $row['Type'],
                'PRI'  => ($row['Key'] == 'PRI') ? true : false,
                'Null' => ($row['Null'] == 'NO') ? false : true
            );

            if ($row['Key'] == 'PRI')
            {
                $primaryKey = $row['Field'];
            }

            // Prevent tables with no site_id from being backed up
            if (!strcmp($row['Field'], 'site_id'))
            {
                $hasSiteID = true;
            }
        }

        if ($hasSiteID)
        {
            $this->_structure[$tableName] = $tableStructure;
            $this->_keys[$primaryKey] = $tableName;
        }

        return true;
    }

    // FIXME: Document me.
    public function getForeignKeys($tableData)
    {
        $result = array();
        foreach ($tableData as $fieldName => $fieldData)
        {
            if (isset($fieldData[$id = 'foreign']))
            {
                $result[] = $fieldData[$id];
            }
        }
        return $result;
    }

    // FIXME: Document me.
    public function getTablesByForeignKeys($allowedKeys)
    {
        $result = array();
        foreach ($this->_structure as $tableName => $tableData)
        {
            $foreignKeys = $this->getForeignKeys($tableData);
            if (count($foreignKeys) == $allowedKeys)
            {
                $result[$tableName] = $tableData;
            }
        }

        return $result;
    }

    // FIXME: Document me.
    private function isTableSiteRestricted($tableData)
    {
        foreach ($tableData as $fieldName => $fieldData)
        {
            if (!strcmp($fieldName, 'site_id'))
            {
                return true;
            }
        }

        return false;
    }

    // FIXME: Document me.
    private function setGUID($fieldName, $id)
    {
        $id = intval($id);
        if (!$this->_GUIDSwapEnabled)
        {
            if (!isset($this->_GUIDs[$fieldName]))
            {
                return ($this->_GUIDs[$fieldName] = array( $id => ($this->_GUID++) ));
            }

            else if (isset($this->_GUIDs[$fieldName][$id]))
            {
                // already exists
                return $this->_GUIDs[$fieldName][$id];
            }

            else return ($this->_GUIDs[$fieldName][$id] = ($this->_GUID++));
        }

        /* If $this->_GUIDSwapEnabled is set to true, the GUIDs are stored in
         * a swap file and not in memory.
         */
        fseek($this->_GUIDSwap, 0, SEEK_END);
        fwrite($this->_GUIDSwap, sprintf('%30s', $fieldName), 30);
        fwrite($this->_GUIDSwap, pack('N1', $id));
        fwrite($this->_GUIDSwap, pack('N1', $this->_GUID));

        return $this->_GUID++;
    }

    // FIXME: Document me.
    private function getGUID($fieldName, $id)
    {
        $id = intval($id);
        if (!$this->_GUIDSwapEnabled)
        {
            if (!isset($this->_GUIDs[$fieldName]) || !isset($this->_GUIDs[$fieldName][$id]))
            {
                // Record points to a non-existent row
                return false;
            }
            return $this->_GUIDs[$fieldName][$id];
        }

        /* If $this->_GUIDSwapEnabled is set to true, the GUIDs are stored in
         * a swap file and not in memory.
         */
        rewind($this->_GUIDSwap);
        for ($guidIndex = 0; $guidIndex < $this->_GUID; $guidIndex++)
        {
            $rName = fread($this->_GUIDSwap, 30);
            $rIdBin = fread($this->_GUIDSwap, 4);
            $rGUIDBin = fread($this->_GUIDSwap, 4);

            /* Decode binary data. */
            $rId = array_pop(unpack('N1', $rIdBin));
            $rGUID = array_pop(unpack('N1', $rGUIDBin));

            if (!strcasecmp($rName, $fieldName) && $id == $rId)
            {
                return $rGUID;
            }
        }

        return false;
    }

    // FIXME: Document me.
    private function doBuildGUIDs()
    {
        foreach ($this->_structure as $tableName => $tableData)
        {
            foreach ($tableData as $fieldName => $fieldData)
            {
                if (!$fieldData['PRI'])
                {
                    continue;
                }

                if ($this->isTableSiteRestricted($tableData))
                {
                    $siteRestrictedCriterion = sprintf(
                        "WHERE
                            %s.site_id = %s",
                        $tableName,
                        $this->_db->makeQueryInteger($this->_siteID)
                    );
                }
                else
                {
                    $siteRestrictedCriterion = '';
                }

                $sql = sprintf(
                    "SELECT
                        %s
                     FROM
                        %s
                     %s",
                    $fieldName,
                    $tableName,
                    $siteRestrictedCriterion
                );

                if ($rs = $this->_db->query($sql))
                {
                    while (($row = $this->_db->getAssoc()))
                    {
                        $this->setGUID($fieldName, $row[$fieldName]);
                    }
                }
            }
        }
    }

    private function getFieldType($type)
    {
        if (strpos(strtolower($type), 'int') !== false) return 'N';
        else if (strpos(strtolower($type), 'float') !== false) return 'N';
        else return 'S';
    }

    private function getTableInfoBackup($tableName, $tableData)
    {
        $info = sprintf('%s,', $tableName);
        $rowCnt = 0;
        foreach ($tableData as $fieldName => $fieldData)
        {
            if (($rowCnt++) > 0) $info .= ',';
            $info .= sprintf('%s', $fieldName);
        }
        return $info;
    }

    private function restoreTableInfoBackup($tableData)
    {
        $mp = explode(',', $tableData);
        $tableName = $mp[0];
        $tableFields = array_slice($mp, 1);

        // Check if table exists in current schema
        if (!isset($this->_structure[$tableName])) return false;

        $tableStructure = array();
        foreach ($tableFields as $newFieldName)
        {
            $exists = false;
            foreach ($this->_structure[$tableName] as $fieldName => $fieldData)
            {
                if (!strcasecmp($newFieldName, $fieldName))
                {
                    $exists = true;
                }
            }

            $tableStructure[] = array('name' => $newFieldName, 'exists' => $exists);
        }

        return array($tableName, $tableStructure);
    }

    // FIXME: Document me.
    private function getTableDataBackup($tableName, $tableData)
    {
        $foreignKeys = $this->getForeignKeys($tableData);
        $data = false;

        if ($this->isTableSiteRestricted($tableData))
        {
            $siteRestrictedCriterion = sprintf(
                "WHERE
                    %s.site_id = %s",
                $tableName,
                $this->_db->makeQueryString($this->_siteID)
            );
        }
        else
        {
            $siteRestrictedCriterion = '';
        }

        $sql = sprintf(
            "SELECT
                 *
             FROM
                %s
             %s",
            $tableName,
            $siteRestrictedCriterion
        );

        if ($rs = $this->_db->query($sql))
        {
            $data = pack('N1', $this->_db->getNumRows());

            while ($row = $this->_db->getAssoc())
            {
                foreach ($row as $columnField => $columnData)
                {
                    if ($tableData[$columnField]['PRI'] || isset($tableData[$columnField]['foreign']))
                    {
                        if (($guid = $this->getGUID($columnField, $columnData)) === false)
                        {
                            // id points to a row that doesn't exist, maintain invalidity
                            $data .= pack('C1', ord('D'));
                            $data .= pack('C1', ord($this->getFieldType($tableData[$columnField]['Type'])));
                            $data .= pack('N1', strlen($columnData));
                            $data .= $columnData;
                        }
                        else
                        {
                            $data .= pack('C1', ord('G'));
                            $data .= pack('N1', $guid);
                        }
                    }
                    else
                    {
                        $data .= pack('C1', ord('D'));
                        $data .= pack('C1', ord($this->getFieldType($tableData[$columnField]['Type'])));
                        $data .= pack('N1', strlen($columnData));
                        $data .= $columnData;
                    }
                }
            }
        }

        return $data;
    }

    private function restoreTableDataBackup($tableName, $tableStructure, $tableData)
    {
        $numFields = count($tableStructure);
        $numRows = array_pop(unpack('N1', substr($tableData, 0, $size = 4)));
        $tableData = substr($tableData, $size);

        $sqlInserts = array();

        for ($rowIndex=0; $rowIndex < $numRows; $rowIndex++)
        {
            $sqlPre = '';
            $sqlValues = array();
            $primaryGUID = false;

            for ($fieldIndex=0; $fieldIndex < $numFields; $fieldIndex++)
            {
                $fieldName = $tableStructure[$fieldIndex]['name'];

                $recordType = chr(array_pop(unpack('C1', substr($tableData, 0, $size = 1))));
                $tableData = substr($tableData, $size);
                $GUID = false;

                if ($recordType == 'G')
                {
                    $GUID = array_pop(unpack('N1', substr($tableData, 0, $size = 4)));
                    $data = sprintf('GUID{%d}', $GUID);
                    $tableData = substr($tableData, $size);
                    $dataType = 'N';

                    if (!strcmp($fieldName, 'site_id'))
                    {
                        // replace occurances of site_id with the current site_id
                        $dataType = 'N';
                        $data = $this->_siteID;
                        $this->setRestoreGUID($GUID, $this->_siteID);
                    }
                }
                else if ($recordType == 'D')
                {
                    $dataType = chr(array_pop(unpack('C1', substr($tableData, 0, $size = 1))));
                    $tableData = substr($tableData, $size);

                    $dataSize = array_pop(unpack('N1', substr($tableData, 0, $size = 4)));
                    $tableData = substr($tableData, $size);

                    $data = substr($tableData, 0, $dataSize);
                    $tableData = substr($tableData, $dataSize);
                }

                if ($dataType == 'S')
                {
                    $data = $this->_db->makeQueryString($data);
                }
                else
                {
                    if (!strlen($data))
                    {
                        $data = 'NULL';
                    }
                    // Prevent sql injection
                    $data = addslashes($data);
                }

                if ($tableStructure[$fieldIndex]['exists'])
                {
                    if ($this->_structure[$tableName][$fieldName]['PRI'])
                    {
                        $primaryGUID = $GUID;
                    }
                    else
                    {
                        $sqlValues[$fieldName] = $data;
                    }
                }
            }

            // Do not insert site records
            if (strcasecmp($tableName, 'site'))
            {
                // build the insertion query
                $sql = sprintf(
                    'INSERT INTO %s (%s) VALUES (%s)',
                    $tableName, // table name verified against current schema (no injection)
                    implode(', ', array_keys($sqlValues)),
                    implode(', ', array_values($sqlValues))
                );

                // If there are no untranslated GUIDs, insert the query
                $sqlInserts[] = array('GUID' => $primaryGUID, 'SQL' => $sql);
            }
        }

        return $sqlInserts;
    }

    private function setRestoreGUID($GUID, $id)
    {
        // FIXME: add swap
        $this->_GUIDRestores[intval($GUID)] = $id;
    }

    private function getRestoreGUID($GUID)
    {
        if (isset($this->_GUIDRestores[intval($GUID)])) return $this->_GUIDRestores[intval($GUID)];
        else return false;
    }

    public function doCreateBackup($fileName)
    {
        @ini_set('memory_limit', '256M');

        /* Create a swap file for GUIDs if necessary. */
        if (CBF_GUID_SWAP_ENABLED)
        {
            // FIXME: tmpfile() might fail under Windows. Look at FileUtility temp file code.
            if (($this->_GUIDSwap = tmpfile()) === false)
            {
                $this->_GUIDSwapEnabled = false;
            }
            else
            {
                $this->_GUIDSwapEnabled = true;
            }
        }

        $fp = fopen($fileName, 'w');
        fwrite($fp, pack('C1', ord(CBF_HANDSHAKE)), 1);
        fwrite($fp, pack('N1', CBF_VERSION), 4);

        $this->doBuildStructure();
        $this->doBuildAssociations();
        $this->doBuildGUIDs();

        /* Up to 10 foreign keys per table maximum. */
        // FIXME: Why?
        for ($keys = 0; $keys < 10; $keys++)
        {
            $tables = $this->getTablesByForeignKeys($keys);
            if (empty($tables))
            {
                continue;
            }

            foreach ($tables as $tableName => $tableData)
            {
                $info = $this->getTableInfoBackup($tableName, $tableData);
                fwrite($fp, pack('N1', strlen($info)), 4);
                fwrite($fp, $info);

                $data = $this->getTableDataBackup($tableName, $tableData);
                fwrite($fp, pack('N1', strlen($data)), 4);
                fwrite($fp, $data);
            }
        }

        if ($this->_GUIDSwapEnabled)
        {
            fclose($this->_GUIDSwap);
        }

        fclose($fp);
    }

    public function deleteSiteData()
    {
        if (!count($this->_structure))
        {
            $this->doBuildStructure();
            $this->doBuildAssociations();
        }

        // Remove all existing site data
        foreach ($this->_structure as $tableName => $tableData)
        {
            // Maintain the existing site table
            if (!strcmp($tableName, 'site')) continue;

            $sql = sprintf(
                "DELETE FROM
                    %s
                 WHERE
                    %s.site_id = %s",
                $tableName,
                $tableName,
                $this->_db->makeQueryString($this->_siteID)
            );

            $this->_db->query($sql);
        }

        return true;
    }

    public function doRestoreBackup($fileName)
    {
        $restoredRows = 0;

        $fileSize = filesize($fileName);
        $fp = fopen($fileName, 'r');
        $handshake = chr(array_pop(unpack('C1', fread($fp, 1))));
        $version = array_pop(unpack('N1', fread($fp, 4)));

        if (strcmp($handshake, CBF_HANDSHAKE) || $version != CBF_VERSION)
        {
            return false;
        }

        $this->doBuildStructure();
        $this->doBuildAssociations();

        if ($this->_dataOverwrite)
        {
            $this->deleteSiteData();
        }

        $sqlInserts = array();

        while (!feof($fp) && ftell($fp) != $fileSize)
        {
            // Get info for the first table
            $tableInfoSize = array_pop(unpack('N1', fread($fp, 4)));
            $data = fread($fp, $tableInfoSize);
            list($tableName, $tableStructure) = $this->restoreTableInfoBackup($data);

            $tableDataSize = array_pop(unpack('N1', fread($fp, 4)));
            $data = fread($fp, $tableDataSize);
            $sqlInserts = array_merge($sqlInserts,
                $this->restoreTableDataBackup($tableName, $tableStructure, $data)
            );
        }

        while (count($sqlInserts) > 0)
        {
            $tmp = array();
            for ($sqlIndex=0; $sqlIndex < count($sqlInserts); $sqlIndex++)
            {
                $sqlInsert = $sqlInserts[$sqlIndex];
                if (strpos($sqlInsert['SQL'], 'GUID{') === false)
                {
                    $this->_db->query($sqlInsert['SQL']);
                    {
                        $this->setRestoreGUID($sqlInsert['GUID'], $id = $this->_db->getLastInsertID());
                        $searchString = sprintf('GUID{%d}', $sqlInsert['GUID']);
                        $replaceString = $id;

                        // Replace remaining links to the new GUID
                        for ($sqlIndex2 = $sqlIndex + 1; $sqlIndex2 < count($sqlInserts); $sqlIndex2++)
                        {
                            $sqlInserts[$sqlIndex2]['SQL'] =
                                str_replace(
                                    $searchString,
                                    $replaceString,
                                    $sqlInserts[$sqlIndex2]['SQL']
                            );
                        }

                        // Replace links to the GUID we've already tried to process for the next pass
                        for ($sqlIndex2 = 0; $sqlIndex2 < count($tmp); $sqlIndex2++)
                        {
                            $tmp[$sqlIndex2]['SQL'] =
                                str_replace(
                                    $searchString,
                                    $replaceString,
                                    $sqlInserts[$sqlIndex2]['SQL']
                            );
                        }
                    }
                }
                else
                {
                    $tmp[] = $sqlInsert;
                }
            }
            if (count($tmp) == count($sqlInserts))
            {
                // no work was done, can't continue, prevent infinate loop
                break;
            }
            else
            {
                $sqlInserts = $tmp;
            }
        }

        if (count($sqlInserts) > 0)
        {
            echo "<BR><BR><BR><BR><BR><BR><BR><BR><BR><BR>";
            htmlentities(print_r($sqlInserts));
            echo count($sqlInserts) . ' rows were unable to be inserted.';


            foreach ($sqlInserts as $mp)
            {
                echo "Unable to translate " . substr($mp['SQL'], strpos($mp['SQL'], 'GUID{'), 10) . "... <BR>\n";
            }
        }

        fclose($fp);

        return $restoredRows;
    }
}

?>
