<?php
/**
 * CATS
 * Seamless MySQL Table Viewer / Editor
 *
 * Copyright (C) 2006 - 2007 Cognizo Technologies, Inc.
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
 * @version    $Id: ControlPanel.php 3705 2007-11-26 23:34:51Z will $
 */

// FIXME: Clean me up!
 
include_once('./lib/WebForm.php');

define('CP_LISTVIEW',       1 << 1); // Predefined section for list view

// Page States
define('CPPS_LISTVIEW',     1 << 1);
define('CPPS_EDIT',         1 << 2);
define('CPPS_VIEW',         1 << 3);
define('CPPS_DELETE',       1 << 4);
define('CPPS_ADD',          1 << 5);

define('CPSTR_EMPTY_FIELD', '<span style="color: #c0c0c0;">(empty)</span>');

// Permissions
define('CPP_ADD',           1 << 1);
define('CPP_EDIT',          1 << 2);
define('CPP_VIEW',          1 << 3);
define('CPP_DELETE',        1 << 4);
define('CPP_SEARCH',        1 << 4);

// Pager
define('CPPAGER_RESULTS_PER_PAGE',      14);

class ControlPanel
{
    private $_fields;
    private $_tables;
    private $_sections;
    private $_db;
    private $_wf;
    private $_primaryKey;
    private $_sortByField;
    private $_linkField;
    private $_permissions;
    private $_selectBoundriesSql;
    private $_insertBoundriesSql;
    private $_deleteBoundriesSql;
    private $_deleteBoundriesTable;
    private $_showCurrencySums;
    private $_callBacks;
    private $_sortDesc;
    private $_truncate;
    private $_truncateID;
    private $_fieldUrls;
    private $_listViewLayout;


    public function __construct()
    {
        $this->_tables = array();
        $this->_db = DatabaseConnection::getInstance();
        $this->_wf = new WebForm();
        $this->_sections = array();
        $this->_primaryKey = '';
        $this->_sortByField = '';
        $this->_permissions = 0;
        $this->_selectBoundriesSql = '';
        $this->_insertBoundriesSql = '';
        $this->_deleteBoundriesSql = '';
        $this->_deleteBoundriesTable = '';
        $this->_linkField = '';
        $this->_showCurrencySums = false;
        $this->_callBacks = array();
        $this->_sortDesc = true;
        $this->_truncate = array();
        $this->_truncateID = 0;
        $this->_fieldUrls = array();
        $this->_listViewLayout = '';
    }


    public function getModal()
    {
        $pageState = intval($this->getPostValue('cpPageState'));
        switch($pageState)
        {
            case CPPS_ADD:
                if ($this->_permissions & CPP_ADD)
                    return $this->getWebForm(true);
                else
                    return $this->getListView();
            case CPPS_VIEW:
                if ($this->_permissions & CPP_VIEW)
                    return $this->getWebForm(false);
                else
                    return $this->getListView();
            case CPPS_EDIT:
                if ($this->_permissions & CPP_EDIT)
                    return $this->getWebForm(false);
                else
                    return $this->getListView();
            case CPPS_DELETE:
                if ($this->_permissions & CPP_DELETE)
                    return $this->getDeleteRow();
                else
                    return $this->getListView();
            default:
                return $this->getListView();
        }
    }

    public function getDeleteRow()
    {
        if ($this->_deleteBoundriesTable == '')
        {
            return $this->getException('Unable to Delete from this Table', 'We\'re sorry, but this table does '
                . 'not support delete operations.');
        }

        $uID = $this->getPostValue('uID');
        $uIDName = $this->getPostValue('uIDName');
        $sql = $this->getTablesSQL(sprintf('%s = %d', addslashes($uIDName), addslashes($uID)));
        $rs = $this->_db->query($sql);
        if ($rs && mysql_num_rows($rs) > 0)
        {
            $row = mysql_fetch_array($rs, MYSQL_ASSOC);
            if (!$row)
            {
                return $this->getException('Bad or expired identifier', 'The operation you attempted cannot complete '
                    . 'because the unique identifier no longer exists. Did you perhaps use your browser\'s <b>back</b> '
                    . 'button?');
            }

            $tableName = $this->_deleteBoundriesTable;
            $sql = 'DELETE FROM ' . $tableName . ' WHERE ';
            if (isset($this->_tables[$tableName]))
            {
                foreach($this->_tables[$tableName]['fields'] as $fieldName => $fieldData)
                {
                    if ($fieldData['primaryKey'])
                    {
                        if (is_numeric($row[$fieldData['uniqueID']])) $keyVal = sprintf('%d', $row[$fieldData['uniqueID']]);
                            else $keyVal = '"' . addslashes($row[$fieldData['uniqueID']]) . '"';
                        $sql .= sprintf('%s = %s', $fieldName, $keyVal);
                    }
                }
            }
            $rs = $this->_db->query($sql);
            if (!$rs)
            {
                return $this->getException('Unable to Delete', 'The operation you attempted cannot complete. '
                    . 'We apologize for the inconvenience and will attempt to solve this issue as soon as '
                    . 'possible.');
            }
        }

        return $this->getListView();
    }

    public function getWebForm($addRecord = false)
    {
        $html = '';
        $infoHtml = '';
        if ($addRecord)
        {
            $this->_wf->setVerifyForm(false);
            $row = array();
        }
        else
        {
            $this->_wf->setVerifyForm(true);

            // This is an edit, lookup information
            $uID = $this->getPostValue('uID');
            $uIDName = $this->getPostValue('uIDName');
            $sql = $this->getTablesSQL(sprintf('%s = %d', addslashes($uIDName), addslashes($uID)));
            $rs = $this->_db->query($sql);
            if (!$rs)
            {
                return $this->getListView();
                return $this->getException('Bad or expired identifier', 'The operation you attempted cannot complete '
                    . 'because the unique identifier no longer exists. Did you perhaps use your browser\'s <b>back</b> '
                    . 'button?');
            }
            $row = mysql_fetch_array($rs, MYSQL_ASSOC);
            if (!$row)
            {
                return $this->getListView();
                return $this->getException('Bad or expired identifier', 'The operation you attempted cannot complete '
                    . 'because the unique identifier no longer exists. Did you perhaps use your browser\'s <b>back</b> '
                    . 'button?');
            }
        }

        $html .= sprintf('<form method="get" action="%s" name="cpBack">',
            substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'))
        );
        foreach($_GET as $name => $value)
        {
            if (!strcmp($name, 'cpPageState'))
                $html .= sprintf('<input type="hidden" name="cpPageState" value="%d" />',
                    CPPS_LISTVIEW
                );
            else if(!strcmp($name, 'a') || !strcmp($name, 'm') || !strcmp($name, 'siteID'))
                $html .= sprintf('<input type="hidden" name="%s" value="%s" />',
                    htmlspecialchars($name), htmlspecialchars($value)
                );
        }
        $html .= '</form>';
        $html .= '<table><tr><td align="left" valign="bottom">';
        $html .= '<img src="images/cp_back.gif" border="0" alt="<-- Back to List" style="cursor: pointer;" onclick="if ((webFormChangesMade==true && '
            . 'confirm(\'You have made changes to this record. Go back to list view without saving?\')) || webFormChangesMade==false) document.cpBack.submit();" /></td>';
        if ($addRecord && $this->_permissions & CPP_ADD)
            $html .= '<td valign="bottom">' . $this->_wf->getImageButton('images/cp_add.gif', 'Add Record', 'cpEditForm') . '</td>';
        else if(!$addRecord && $this->_permissions & CPP_EDIT)
            $html .= '<td valign="bottom">' . $this->_wf->getImageButton('images/cp_save.gif', 'Save Changes', 'cpEditForm') . '</td>';
        $html .= '</tr></table>';

        $html .= sprintf('<form method="get" action="%s" name="cpEditForm">',
            substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'))
        );
        foreach($_GET as $name => $value)
        {
            $html .= sprintf('<input type="hidden" name="%s" value="%s" />',
                htmlspecialchars($name), htmlspecialchars($value)
            );
        }

        // Build the webform
        foreach($this->_tables as $tableName => $tableData)
        {
            foreach($tableData['fields'] as $fieldName => $fieldData)
            {
                if (!isset($fieldData['section']) || count($fieldData['section']) == 0) continue;
                if ($fieldData['section'][0] == CP_LISTVIEW && count($fieldData['section']) == 1) continue;
                $this->_wf->addField($fieldData['webFormParams']['name'],
                    $fieldData['webFormParams']['caption'], $fieldData['webFormParams']['type'],
                    $fieldData['webFormParams']['required'], $fieldData['webFormParams']['size'],
                    $fieldData['webFormParams']['minlen'], $fieldData['webFormParams']['maxlen'],
                    $fieldData['webFormParams']['defaultValue'], $fieldData['webFormParams']['regex_test'],
                    $fieldData['webFormParams']['regex_fail'], $fieldData['webFormParams']['helpBody'],
                    $fieldData['webFormParams']['helpRules']
                );
            }
        }

        if ($this->getPostValue('webFormPostBack') == '1')
        {
            $updateSql = array();
            list($fields, $errors) = $this->_wf->getValidatedFields();
            if (count($errors) > 0)
            {
                $infoHtml = '<div style="padding: 10px; margin: 10px 0px 10px 0px; border: 1px solid #800000;">'
                    . '<table><tr><td valign="top" style="padding-right: 20px;"><img src="images/large_error.gif" border="0" /></td><td>'
                    . '<h2 style="color: #800000;">There are a few problems:</h2>'
                    . '<ul style="padding-left: 25px;"><li>'
                    . '<h3 style="font-size: 10pt; font-weight: normal;">'
                    . implode('</h3></li><li><h3 style="font-size: 10pt; font-weight: normal;">', $errors)
                    . '</h3></li></ul></td></tr></table></div>';
            }
            else
            {
                foreach($fields as $fieldName => $fieldValue)
                {
                    $fieldValue = trim($fieldValue);
                    foreach($this->_tables as $subTableName => $subTableData)
                    {
                        foreach($subTableData['fields'] as $subFieldName => $subFieldData)
                        {
                            if (!strcmp($subFieldData['uniqueID'], $fieldName))
                            {
                                if ($addRecord)
                                {
                                    // this is an addition, build the SQL
                                    if (!isset($updateSql[$subTableName])) $updateSql[$subTableName] = array('', '');
                                    $sqlFields = $updateSql[$subTableName][0];
                                    $sqlValues = $updateSql[$subTableName][1];
                                    if ($sqlFields != '') $sqlFields .= ', ';
                                    $sqlFields .= $subFieldName;
                                    if ($sqlValues != '') $sqlValues .= ', ';
                                    $sqlValues .= $this->getFieldDBText($subFieldData, $fieldValue);

                                    $updateSql[$subTableName][0] = $sqlFields;
                                    $updateSql[$subTableName][1] = $sqlValues;

                                    // populate the row for callbacks
                                    $row[$fieldName] = $fieldValue;
                                }
                                else
                                {
                                    // This is an edit and a field has been changed
                                    if ($this->isFieldChange($row[$fieldName], $fieldValue, $subFieldData))
                                    {
                                        if (!isset($updateSql[$subTableName])) $updateSql[$subTableName] = '';
                                        if ($updateSql[$subTableName] != '') $updateSql[$subTableName] .= ', ';
                                        $updateSql[$subTableName] .= sprintf('%s.%s = %s',
                                            $subTableName, $subFieldName, $this->getFieldDBText($subFieldData, $fieldValue)
                                        );
                                    }
                                }
                            }
                        }
                    }
                }
            }

            if (count($updateSql) > 0)
            {
                $callBack = 0;

                if ($addRecord && (!$this->_permissions & CPP_ADD))
                    return $this->getException('You cannot add records',
                                'This table does not support adding new records.');
                if (!$addRecord && (!$this->_permissions & CPP_EDIT))
                    return $this->getException('You cannot edit records',
                                'This table does not support editting records.');

                $updatedRows = 0;
                foreach($updateSql as $tableName => $updateTableSql)
                {
                    $ruleTableSql = '';
                    $sql = '';
                    if ($addRecord)
                    {
                        // Figure out the primary key to pass to a callback function
                        $callBackPrimaryKey = '';
                        foreach($this->_tables[$tableName]['fields'] as $fieldName => $fieldData)
                        {
                            if ($fieldData['primaryKey'])
                            {
                                $callBackPrimaryKey = $fieldData['uniqueID'];
                            }
                        }

                        if ($this->_insertBoundriesSql != '')
                        {
                            list($fieldName, $fieldValue) = split('=', $this->_insertBoundriesSql);
                            $fieldName = trim($fieldName);
                            $fieldValue = trim($fieldValue);
                        }
                        // This is an addition (INSERT)
                        $sql = sprintf('INSERT INTO %s (%s%s) VALUES (%s%s)',
                            $tableName, $updateTableSql[0],
                            ($this->_insertBoundriesSql ? ', ' . $fieldName : ''),
                            $updateTableSql[1],
                            ($this->_insertBoundriesSql ? ', ' . $fieldValue : '')
                        );
                        if (isset($this->_callBacks[CPP_ADD]))
                        {
                            $callBack = $this->_callBacks[CPP_ADD];
                        }
                    }
                    else
                    {
                        // This is an edit (UPDATE)
                        // Figure out the primary key for this table and set a rule so only the current
                        // row is editted (when in edit mode)
                        foreach($this->_tables[$tableName]['fields'] as $fieldName => $fieldData)
                        {
                            if ($fieldData['primaryKey'])
                            {
                                if (is_numeric($row[$fieldData['uniqueID']])) $keyVal = sprintf('%d', $row[$fieldData['uniqueID']]);
                                else $keyVal = '"' . addslashes($row[$fieldData['uniqueID']]) . '"';
                                $ruleTableSql = sprintf('%s = %s', $fieldName, $keyVal);
                                break;
                            }
                        }

                        if ($ruleTableSql != '')
                        {
                            // attempt to write the changes to the database for this table
                            $sql = sprintf('UPDATE %s SET %s WHERE %s',
                                $tableName, $updateTableSql, $ruleTableSql
                            );
                            if (isset($this->_callBacks[CPP_EDIT]))
                            {
                                $callBack = $this->_callBacks[CPP_EDIT];
                            }
                        }
                    }
                    if ($sql != '')
                    {
                        $rs = $this->_db->query($sql);
                        if (!$rs)
                        {
                            return $this->getException('There was an error saving your changes',
                                'An unexpected error has occured when trying to make the changes '
                                . 'you made permanent. An administrator has been contacted and '
                                . 'the problem will be looked into shortly. We appologize for the '
                                . 'inconvenience.');
                        }
                        else
                        {
                            $updatedRows += mysql_affected_rows();
                            if ($addRecord && $callBackPrimaryKey)
                                $row[$callBackPrimaryKey] = mysql_insert_id();
                            if ($callBack)
                                $callBack($row);
                        }
                    }
                }

                if ($updatedRows > 0)
                {
                    if ($addRecord)
                    {
                        $infoHtml .= "<div id=\"cpInfo\" class=\"cpInfo\">\n";
                        $infoHtml .= "<span class=\"cpInfoHeaderText\">You have added a record.</span>\n<br />";
                        $infoHtml .= "<b>Do not</b> refresh this page as it may result in a duplicate submission.";
                        $infoHtml .= "</div>\n";
                        $infoHtml .= sprintf('<form method="get" action="%s" name="cpBack">',
                            substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'))
                        );
                        foreach($_GET as $name => $value)
                        {
                            if (!strcmp($name, 'cpPageState'))
                                $infoHtml .= sprintf('<input type="hidden" name="cpPageState" value="%d" />',
                                    CPPS_LISTVIEW
                                );
                            else if(!strcmp($name, 'a') || !strcmp($name, 'm') || !strcmp($name, 'siteID'))
                                $infoHtml .= sprintf('<input type="hidden" name="%s" value="%s" />',
                                    htmlspecialchars($name), htmlspecialchars($value)
                                );
                        }
                        $infoHtml .= '</form>';
                        $infoHtml .= '<img src="images/cp_back.gif" border="0" style="cursor: pointer;" alt="<-- Back to List" onclick="document.cpBack.submit();" />';
                        return $infoHtml;
                    }
                    else
                    {
                        $src = substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], '?')+1);
                        CATSUtility::transferRelativeURI($src . '&cpChangesMade=1');
                    }
                }
            }
        }

        if (isset($_GET['cpChangesMade']) && $_GET['cpChangesMade'] == '1')
        {
            $infoHtml .= "<div id=\"cpInfo\" class=\"cpInfo\" style=\"position: absolute; top: 150px; left: 150px; width: 600px; background-color: white;\">\n";
            $infoHtml .= "<span class=\"cpInfoHeaderText\">Your changes have been saved.</span>\n";
            $infoHtml .= "</div>\n";
            $infoHtml .= "<script type=\"text/javascript\">\n";
            $infoHtml .= "var cpInfoHide = window.setTimeout('var obj = document.getElementById(\"cpInfo\"); if (obj) { obj.style.display = \"none\"; }', 2000);\n</script>\n";
        }

        foreach($this->_sections as $sectionName => $sectionData)
        {
            if ($sectionName != CP_LISTVIEW) // Reserved for the list view formatting
            {
                $sectionFields = '';
                $prefillData = array();
                foreach($this->_tables as $tableName => $tableData)
                {
                    foreach($tableData['fields'] as $fieldName => $fieldData)
                    {
                        if (isset($fieldData['section']) && in_array($sectionName, $fieldData['section']))
                        {
                            if ($sectionFields != '') $sectionFields .= '[NL]';
                            $sectionFields .= sprintf('[%s]', $fieldData['uniqueID']);

                            if (!$addRecord)
                            {
                                // prefill the field with existing data for edits
                                $rawData = $this->getFieldInputText($fieldData, $row[$fieldData['uniqueID']], '');
                                $prefillData[$fieldData['uniqueID']] = $rawData;
                            }
                        }
                    }
                }

                if (!$addRecord)
                    $this->_wf->setValidatedFields($prefillData);

                // Display the webform
                if ($sectionData['webFormLayout'] == '')
                    $this->_wf->setLayout($sectionFields);
                else
                    $this->_wf->setLayout($sectionData['webFormLayout']);
                $html .= "<div id=\"cpSection" . $sectionName . "\" class=\"cpSection\">\n";
                $html .= "<span class=\"cpSectionTitle\">" . $sectionData['caption'] . "</span>\n";

                $contentsHtml = $sectionData['sectionLayout'];
                $contentsHtml = str_replace('[WebForm]', $this->_wf->getForm('cellpadding="0" cellspacing="4"'), $contentsHtml);

                // Allow users (on edits) to specify EasyTags<tm>, so they can retrieve Database field values
                // when using [field_name] tags in the sectionLayout
                if (!$addRecord)
                {
                    foreach($this->_tables as $tableName => $tableData)
                    {
                        foreach($tableData['fields'] as $fieldName => $fieldData)
                        {
                            if (isset($row[$fieldData['uniqueID']]))
                                $contentsHtml = str_replace(sprintf('[%s]', $fieldData['uniqueID']), $row[$fieldData['uniqueID']], $contentsHtml);
                        }
                    }
                }

                $html .= $contentsHtml;
                $html .= "\n</div>\n<p />\n";
            }
        }

        if ($addRecord && $this->_permissions & CPP_ADD)
            $html .= $this->_wf->getButton('Add Record', 'cpEditForm');
        else if(!$addRecord && $this->_permissions & CPP_EDIT)
            $html .= $this->_wf->getButton('Save Changes', 'cpEditForm');
        //if ($this->_permissions & CPP_DELETE && $this->_deleteBoundriesTable != '' && !$addRecord)
        //    $html .= $this->_wf->getButton('Delete', 'cpDeleteForm');

        // add css and javascript on-the-fly
        $html = sprintf("<style type=\"text/css\">\n%s\n</style>\n<script type=\"text/javascript\">\n%s\n</script>\n%s%s",
            $this->getCSS(), $this->getWebFormJavaScript(), $infoHtml, $html
        );

        return $html;
    }

    private function isFieldChange($dbText, $newText, $fieldData)
    {
        switch($fieldData['webFormParams']['type'])
        {
            case WFT_BOOLEAN:
                if ((!strcasecmp($newText, 'true') && $dbText) ||
                    (!strcasecmp($newText, 'false') && !$dbText))
                    return false;
                else
                    return true;
                break;
            case WFT_CC_NUMBER:
                // user hasn't changed credit card, it was just masked (not on ssl)
                if (preg_match("/^[X]{4}[\-]?[X]{4}[\-]?[X]{4}[\-]?[0-9]{4}$/", $newText))
                    return false;
                else if (!strlen($dbText) && !strlen($newText))
                {
                    return false;
                }
                else if (strcmp(EncryptionUtility::decryptCreditCardNumber($dbText), $newText))
                {
                    return true;
                }
                else
                {
                    return false;
                }
                break;
            case WFT_CURRENCY:
                $newText = str_replace('$', '', $newText);
                if (floatval($newText) != floatval($dbText))
                    return true;
                else
                    return false;
                break;
            case WFT_CC_EXPIRATION:
                if (strlen(trim($newText)) == 0 && strlen(trim($dbText)) == 0)
                    return false;
                if (strlen(trim($newText)) > 0)
                {
                    list($month, $year) = explode('/', $newText);
                    if (strlen(strval($year)) == 2) $year += 2000;
                    $newTime = strtotime(sprintf('%04d-%02d-01', $year, $month));
                }
                else
                {
                    $newTime = 0;
                }

                if ($newTime != strtotime($dbText))
                    return true;
                else
                    return false;
                break;
            case WFT_DATE:
                if (strtotime($newText) != strtotime($dbText))
                    return true;
                else
                    return false;
                break;
            default:
                if (strcmp($dbText, $newText))
                    return true;
                else
                    return false;
                break;
        }
    }

    public function getWebFormJavaScript()
    {
        return $this->_wf->getJavaScript();
    }

    public function getListViewJavaScript()
    {
        ob_start();
        // Add JavaScript for mouseover on rows
        ?>
        var lockMarking = false;
        var cpHideMouseOverRow = '';
        function cpMouseOverRow(id, tf)
        {
            var obj;

            if (lockMarking == true) return;

            if (tf && cpHideMouseOverRow != id)
            {
                <?php
                foreach($this->_tables as $tableName => $tableData)
                {
                    foreach($tableData['fields'] as $fieldName => $fieldData)
                    {
                        // Check if it's an active field and if it's in the ListView section
                        if (isset($fieldData['activeField']) && $fieldData['activeField'] == true &&
                            isset($fieldData['section']) && is_array($fieldData['section']) &&
                            in_array(CP_LISTVIEW, $fieldData['section']))
                        {
                            ?>
                            obj = document.getElementById('cp_<?php echo $fieldData['uniqueID']; ?>' + id);
                            obj.style.backgroundColor = '#e0e0e0';
                            <?php
                        }
                    }
                }
                ?>
                // for admin.row
                obj = document.getElementById('cp_adminDOTrow' + id);
                obj.style.backgroundColor = '#e0e0e0';
            }
            else
            {
                <?php
                foreach($this->_tables as $tableName => $tableData)
                {
                    foreach($tableData['fields'] as $fieldName => $fieldData)
                    {
                        // Check if it's an active field and if it's in the ListView section
                        if (isset($fieldData['activeField']) && $fieldData['activeField'] == true &&
                            isset($fieldData['section']) && is_array($fieldData['section']) &&
                            in_array(CP_LISTVIEW, $fieldData['section']))
                        {
                            ?>
                            obj = document.getElementById('cp_<?php echo $fieldData['uniqueID']; ?>' + id);
                            if (id % 2)
                                obj.style.backgroundColor = '#f0f0f0';
                            else
                                obj.style.backgroundColor = '#ffffff';
                            <?php
                        }
                    }
                }
                ?>
                obj = document.getElementById('cp_adminDOTrow' + id);
                if (id % 2)
                    obj.style.backgroundColor = '#f0f0f0';
                else
                    obj.style.backgroundColor = '#ffffff';
            }
        }
        function cpMarkForDelete(id, tf)
        {
            var obj;
            if (tf)
            {
                lockMarking = true;
                <?php
                foreach($this->_tables as $tableName => $tableData)
                {
                    foreach($tableData['fields'] as $fieldName => $fieldData)
                    {
                        // Check if it's an active field and if it's in the ListView section
                        if (isset($fieldData['activeField']) && $fieldData['activeField'] == true &&
                            isset($fieldData['section']) && is_array($fieldData['section']) &&
                            in_array(CP_LISTVIEW, $fieldData['section']))
                        {
                            ?>
                            obj = document.getElementById('cp_<?php echo $fieldData['uniqueID']; ?>' + id);
                            obj.style.backgroundColor = '#ffd8d8';
                            <?php
                        }
                    }
                }
                ?>
                // for admin.row
                obj = document.getElementById('cp_adminDOTrow' + id);
                obj.style.backgroundColor = '#e0e0e0';
            }
            else
            {
                lockMarking = false;
                <?php
                foreach($this->_tables as $tableName => $tableData)
                {
                    foreach($tableData['fields'] as $fieldName => $fieldData)
                    {
                        // Check if it's an active field and if it's in the ListView section
                        if (isset($fieldData['activeField']) && $fieldData['activeField'] == true &&
                            isset($fieldData['section']) && is_array($fieldData['section']) &&
                            in_array(CP_LISTVIEW, $fieldData['section']))
                        {
                            ?>
                            obj = document.getElementById('cp_<?php echo $fieldData['uniqueID']; ?>' + id);
                            if (id % 2)
                                obj.style.backgroundColor = '#f0f0f0';
                            else
                                obj.style.backgroundColor = '#ffffff';
                            <?php
                        }
                    }
                }
                ?>
                obj = document.getElementById('cp_adminDOTrow' + id);
                if (id % 2)
                    obj.style.backgroundColor = '#f0f0f0';
                else
                    obj.style.backgroundColor = '#ffffff';
            }
        }
        <?php
        $js = ob_get_contents();
        ob_end_clean();
        return $js;
    }

    public function getCSS()
    {
        return $this->_wf->getCSS();
    }

    public function getListView()
    {
        $currencySumData = array();

        // ******************** SEARCH ***********************
        if (isset($_GET['cpSearchString']) || isset($_POST['cpSearchString']))
        {
            $searchString = $this->getPostValue('cpSearchString');
            $searchSql = '';

            foreach($this->_tables as $tableName => $tableData)
            {
                foreach($tableData['fields'] as $fieldName => $fieldData)
                {
                    // Check if it's an active field and if it's in the ListView section
                    if (isset($fieldData['activeField']) && $fieldData['activeField'] == true &&
                        isset($fieldData['section']) && is_array($fieldData['section']) &&
                        in_array(CP_LISTVIEW, $fieldData['section']))
                    {
                        if ($searchSql != '') $searchSql .= 'OR ';
                        $searchSql .= sprintf('%s.%s LIKE "%%%s%%"',
                            $tableName, $fieldName,
                            addslashes($searchString)
                        );
                    }
                }
            }
            if ($searchSql != '')
            {
                $searchSql = '(' . $searchSql . ')';
            }
        }
        else
        {
            $searchString = '';
            $searchSql = '';
        }


        // ********************** SUMS ***************************
        if ($this->_showCurrencySums)
        {
            $currencySql = '';
            foreach($this->_tables as $tableName => $tableData)
            {
                foreach($tableData['fields'] as $fieldName => $fieldData)
                {
                    // Check if it's an active field and if it's in the ListView section
                    if (isset($fieldData['activeField']) && $fieldData['activeField'] == true &&
                        isset($fieldData['section']) && is_array($fieldData['section']) &&
                        in_array(CP_LISTVIEW, $fieldData['section']) && $fieldData['webFormType'] == WFT_CURRENCY)
                    {
                        if ($currencySql != '') $currencySql .= ' ';
                        $currencySql .= sprintf('SUM(%s.%s) AS %s',
                            $tableName, $fieldName, $fieldData['uniqueID']
                        );
                    }
                }
            }
            if ($currencySql != '')
            {
                $rs = $this->_db->query($sql = $this->getTablesSQL($searchSql, '', $currencySql));
                $currencySums = mysql_fetch_array($rs, MYSQL_ASSOC);
            }
        }

        // ********************** PAGER **************************
        $pager_ResultsPerPage = $this->getPostValue('cp_ResultsPerPage');
        $pager_CurrentPage = $this->getPostValue('cp_CurrentPage');

        if ($pager_ResultsPerPage == '')
            $pager_ResultsPerPage = CPPAGER_RESULTS_PER_PAGE;
        else
            $pager_ResultsPerPage = intval($pager_ResultsPerPage);

        if ($pager_CurrentPage == '')
            $pager_CurrentPage = 0;
        else
            $pager_CurrentPage = intval($pager_CurrentPage) - 1;

        // get the records count
        $rs = $this->_db->query($sql = $this->getTablesSQL($searchSql, '', 'COUNT(*)'));
        $rsCount = intval(mysql_result($rs, 0, 0));
        $numPages = ceil($rsCount / $pager_ResultsPerPage);
        if ($pager_CurrentPage >= $numPages) $pager_CurrentPage = $numPages - 1;
        if ($pager_CurrentPage < 0) $pager_CurrentPage = 0;
        if ($numPages > 1)
            $limitSql = sprintf('%d OFFSET %d', $pager_ResultsPerPage, $pager_CurrentPage * $pager_ResultsPerPage);
        else
            $limitSql = '';


        $sql = $this->getTablesSQL($searchSql, $limitSql);
        $rs = $this->_db->query($sql, $pager_ResultsPerPage, $pager_CurrentPage);
        if (!$rs)
        {
            echo $sql;
            return $this->getException('Unable to view', 'We\'re sorry, but an internal error has occurred and '
                . 'we are unable to show you the information you requested. This will be looked into as soon '
                . 'as possible.');
        }
        $infoHtml = '';
        $headerHtml = '';
        $headerComplete = false;
        $fieldOffset = true;

        $rowNum = 0;
        while ($row = mysql_fetch_array($rs, MYSQL_ASSOC))
        {
            $numColumns = 0;
            $infoHtml .= "<tr>\n";
            if ($headerComplete == false)
                $headerHtml .= "<tr>\n";

            $myRow = array('admin.row' => '');
            $headerRow = array('admin.row' => '');

            // for highlighting of mouseover rows
            $highlightJS = array(
                'onmouseover' => sprintf('cpMouseOverRow(\'%d\', true);', $rowNum),
                'onmouseout' => sprintf('cpMouseOverRow(\'%d\', false);', $rowNum)
            );
            $highlightJSFlat = sprintf('onmouseover="%s" onmouseout="%s"',
                $highlightJS['onmouseover'], $highlightJS['onmouseout']
            );

            // Print each field in the ListView section
            foreach($this->_tables as $tableName => $tableData)
            {
                foreach($tableData['fields'] as $fieldName => $fieldData)
                {
                    if (!strcmp($tableData['primaryKey'], $fieldName))
                    {
                        $uniqueRowIDName = $tableName . '.' . $fieldName;
                        $uniqueRowID = $row[$fieldData['uniqueID']];
                    }
                }

                foreach($tableData['fields'] as $fieldName => $fieldData)
                {
                    // Check if it's an active field and if it's in the ListView section
                    if (isset($fieldData['activeField']) && $fieldData['activeField'] == true &&
                        isset($fieldData['section']) && is_array($fieldData['section']) &&
                        in_array(CP_LISTVIEW, $fieldData['section']))
                    {
                        if (!isset($headerRow[$fieldData['uniqueID']])) $headerRow[$fieldData['uniqueID']] = '';
                        if (!isset($myRow[$fieldData['uniqueID']])) $myRow[$fieldData['uniqueID']] = '';

                        $numColumns++;
                        switch ($fieldData['webFormType'])
                        {
                            case WFT_BOOLEAN:
                                $textAlign = 'center';
                                break;
                            case WFT_DATE: case WFT_CURRENCY:
                                $textAlign = 'right';
                                break;
                            default:
                                $textAlign = 'left';
                                break;
                        }
                        if ($headerComplete == false)
                            $headerRow[$fieldData['uniqueID']] .= sprintf('<td class="cpFieldHeader%s" valign="center" align="%s">'
                                . '<a href="%s%scpSortByField=%s&cpSortDesc=%s" class="cpFieldHeader">%s%s</a></td>',
                                (!strcmp($this->_sortByField, $fieldData['uniqueID']) ? 'Sorted' : ''),
                                $textAlign,
                                $_SERVER['REQUEST_URI'],
                                (strpos($_SERVER['REQUEST_URI'], '?') !== false ? '&' : '?'),
                                $fieldData['uniqueID'],
                                $this->_sortDesc ? 'false' : 'true',
                                $fieldData['caption'],
                                (($this->_sortDesc && !strcmp($this->_sortByField, $fieldData['uniqueID'])) ? '' : '') // for showing desc
                            );

                        $viewUrl = sprintf('%s%scpPageState=%d&uID=%d&uIDName=%s',
                            $_SERVER['REQUEST_URI'],
                            (strpos($_SERVER['REQUEST_URI'], '?') !== false ? '&' : '?'),
                            CPP_EDIT, $uniqueRowID, $uniqueRowIDName
                        );

                        // Build the row display <td>
                        $td = array(
                            'id' => sprintf('cp_%s%d', $fieldData['uniqueID'], $rowNum),
                            'onclick' => sprintf('document.location.href=\'%s\';', $viewUrl),
                            'class' => sprintf('%s', ($fieldOffset ? 'cpField1' : 'cpField2')),
                            'style' => 'cursor: pointer;',
                            'valign' => 'center',
                            'align' => $textAlign
                        );
                        $td = array_merge($td, $highlightJS);
                        $td_text = $this->getFieldHtmlText($fieldData, $row[$fieldData['uniqueID']], CPSTR_EMPTY_FIELD);

                        // Process the row display override (if exists) to modify the row's output
                        if (isset($this->_callBacks['td']) && ($func = $this->_callBacks['td']))
                        {
                            if (is_array($results = $func($fieldData['uniqueID'], $td, $td_text, $row)))
                            {
                                list($td, $td_text) = $results;
                            }
                        }

                        $td_html = sprintf('%s%s%s%s%s',
                            (!strcmp($this->_linkField, $fieldData['uniqueID']) ? '<a href="' . $viewUrl . '">' : ''),
                            (isset($this->_fieldUrls[$fieldData['uniqueID']]) ? '<a class="cpExternalLink" href="'
                                . $this->getFieldLinkText($row, $this->_fieldUrls[$fieldData['uniqueID']]['url']) . '" onmouseover='
                                . '"document.getElementById(\'cpDescribeLink' . $rowNum . '\').style.display=\'\'; cpHideMouseOverRow = \''.$rowNum.'\';" '
                                . 'onmouseout="document.getElementById(\'cpDescribeLink' . $rowNum . '\').style.display=\'none\'; cpHideMouseOverRow = \'\';">' : ''),
                            $td_text,
                            (isset($this->_fieldUrls[$fieldData['uniqueID']]) ? '</a><div id="cpDescribeLink' . $rowNum . '" '
                                . 'style="display: none; position: absolute; '
                                . 'background-color: #d4e3ff; font-size: 11px; padding: 11px; border: 1px solid #87a6de; margin: 20px">'
                                . $this->getFieldLinkText($row, $this->_fieldUrls[$fieldData['uniqueID']]['comments']) . '</div>' : ''),
                            (!strcmp($this->_linkField, $fieldData['uniqueID']) ? '</a>' : '')
                        );

                        $td_final = '';
                        foreach($td as $tag => $val)
                        {
                            if ($td_final != '') $td_final .= ' ';
                            $td_final .= sprintf('%s="%s"', $tag, $val);
                        }
                        $myRow[$fieldData['uniqueID']] .= sprintf('<td %s>%s</td>',
                            $td_final,
                            $td_html
                        );
                    }
                }
            }
            // permission-accessible functions
            if ($this->_permissions & CPP_EDIT || $this->_permissions & CPP_DELETE)
            {
                if ($headerComplete == false)
                    $headerRow['admin.row'] .= '<td class="cpFieldHeader" align="right" valign="center">';
                $myRow['admin.row'] .= sprintf('<td %s id="cp_adminDOTrow%d" class="%s" align="right" valign="center">',
                    $highlightJSFlat,
                    $rowNum,
                    ($fieldOffset ? 'cpField1' : 'cpField2')
                );
                $myRow['admin.row'] .= '<table cellpadding="0" cellspacing="0" border="0"><tr>';
                if ($this->_permissions & CPP_EDIT)
                {
                    if ($headerComplete == false)
                        $headerRow['admin.row'] .= '&nbsp;';
                    $myRow['admin.row'] .= sprintf('<td style="padding-right: 1px;">'
                        . '<a href="%s%scpPageState=%d&uID=%d&uIDName=%s"><img src="images/cp_edit.gif" '
                        . 'border="0" style="cursor: pointer;" /></a></td>',
                        $_SERVER['REQUEST_URI'],
                        (strpos($_SERVER['REQUEST_URI'], '?') !== false ? '&' : '?'),
                        CPP_EDIT, $uniqueRowID, $uniqueRowIDName
                    );
                    $existingSections = true;
                }
                if ($this->_permissions & CPP_DELETE && $this->_deleteBoundriesTable != '')
                {
                    if ($headerComplete == false)
                        $headerRow['admin.row'] .= '&nbsp;';
                    $myRow['admin.row'] .= sprintf('<td style="padding-right: 1px;"><img '
                        . 'src="images/cp_delete.gif" border="0" style="cursor: pointer;" onclick="cpMarkForDelete(%d,true); if '
                        . '(confirm(\'Are you sure you want to delete this record? This action cannot be undone!\')) '
                        . 'document.location.href=\'%s%scpPageState=%d&uID=%d&uIDName=%s\'; else cpMarkForDelete(%d,false);" /></a></td>',
                        $rowNum,
                        $_SERVER['REQUEST_URI'],
                        (strpos($_SERVER['REQUEST_URI'], '?') !== false ? '&' : '?'),
                        CPP_DELETE, $uniqueRowID, $uniqueRowIDName, $rowNum
                    );
                    $existingSections = true;
                }
                if ($this->_permissions & CPP_DELETE)
                {

                }
                $myRow['admin.row'] .= '</tr></table>';
                if ($headerComplete == false)
                    $headerRow['admin.row'] .= '</td>';
                $myRow['admin.row'] .= '</td>';
            }

            if ($headerComplete == false)
            {
                foreach ($this->_sections[CP_LISTVIEW]['fieldOrder'] as $uniqueID)
                    if (isset($headerRow[$uniqueID]))
                        $headerHtml .= $headerRow[$uniqueID];
                $headerHtml .= $headerRow['admin.row'];
            }
            foreach ($this->_sections[CP_LISTVIEW]['fieldOrder'] as $uniqueID)
                if (isset($myRow[$uniqueID]))
                    $infoHtml .= $myRow[$uniqueID];
            $infoHtml .= $myRow['admin.row'];

            $infoHtml .= "</tr>\n";
            if ($headerComplete == false)
                $headerHtml .= "</tr>\n";
            $headerComplete = true;
            $fieldOffset = !$fieldOffset;
            $rowNum++;
        }

        if ($this->_showCurrencySums && $rsCount > 0 && $currencySql != '')
        {
            $infoHtml .= sprintf('<tr><td colspan="%d" valign="center" align="center" '
                . 'class="cpFieldSumRows">%d TOTAL ROWS</td></tr>',
                $numColumns+1, $rsCount
            );

            $infoHtml .= "<tr>\n";
            $myRow = array();
            foreach($this->_tables as $tableName => $tableData)
            {
                foreach($tableData['fields'] as $fieldName => $fieldData)
                {
                    // Check if it's an active field and if it's in the ListView section
                    if (isset($fieldData['activeField']) && $fieldData['activeField'] == true &&
                        isset($fieldData['section']) && is_array($fieldData['section']) &&
                        in_array(CP_LISTVIEW, $fieldData['section']))
                    {
                        if ($fieldData['webFormType'] == WFT_CURRENCY && isset($currencySums[$fieldData['uniqueID']]))
                        {
                            $myRow[$fieldData['uniqueID']] = sprintf('<td align="right" valign="center" class="cpFieldSum">$%s</td>',
                                number_format($currencySums[$fieldData['uniqueID']], 2)
                            );
                        }
                        else
                        {
                        	$myRow[$fieldData['uniqueID']] = '<td class="cpFieldSum">&nbsp;</td>';
                        }
                    }
                }
            }

            foreach ($this->_sections[CP_LISTVIEW]['fieldOrder'] as $uniqueID)
                if (isset($myRow[$uniqueID]))
                    $infoHtml .= $myRow[$uniqueID];
            // for admin.row:
            $infoHtml .= "<td class=\"cpFieldSum\">&nbsp;</td></tr>\n";
        }

        // Display pager if needed
        $pagerHtml = '';
        if ($numPages > 1)
        {
            if (strpos($_SERVER['REQUEST_URI'], '?') !== false)
                $url = $_SERVER['REQUEST_URI'] . '&';
            else
                $url = $_SERVER['REQUEST_URI'] . '?';
            $url .= sprintf('cp_ResultsPerPage=%d&', $pager_ResultsPerPage);

            // next/prev page links
            if ($pager_CurrentPage > 0)
                $pagerHtml .= sprintf('<a href="%s&cp_CurrentPage=%d">Previous Page</a>',
                    $url, $pager_CurrentPage
                );
            if ($pager_CurrentPage < $numPages-1)
            {
                if ($pager_CurrentPage > 0) $pagerHtml .= ' | ';
                $pagerHtml .= sprintf('<a href="%s&cp_CurrentPage=%d">Next Page</a>',
                    $url, $pager_CurrentPage+2
                );
            }
            $pagerHtml .= '<br />Page: ';

            // list of pages
            for($page = 0; $page < $numPages; $page++)
            {
                if ($page != $pager_CurrentPage)
                    $pagerHtml .= sprintf('<a href="%s&cp_CurrentPage=%d">%d</a> ',
                        $url, $page+1, $page+1
                    );
                else
                    $pagerHtml .= sprintf('%d ', $page+1);
            }
        }

        if ($this->_permissions & CPP_ADD)
        {
            $pagerHtml .= sprintf('<p /><a href="%s%scpPageState=%d"><img src="images/cp_add.gif" border="0" alt="Add (+)" /></a>',
                $_SERVER['REQUEST_URI'],
                (strpos($_SERVER['REQUEST_URI'], '?') !== false ? '&' : '?'),
                CPPS_ADD
            );
        }

        $titleHtml = '';
        $searchHtml = '';
        if ($this->_permissions & CPP_SEARCH)
        {
            if ($searchString != '')
                $titleHtml .= '<span class="cpSearchResultsText">Found <b>' . number_format($rsCount,0) . '</b> '
                    . 'result' . ($rsCount != 1 ? 's' : '') . ' for "<i>' . $searchString . '</i>"</span><p />';
            $searchHtml .= '<div id="cpSearch" class="cpSearch">';
            $searchHtml .= $this->getForm('cpSearch');
            $searchHtml .= '<input type="text" name="cpSearchString" value="' .
                ($searchString != '' ? $searchString : 'Enter Search Text' )
                . '" id="cpSearchBox" '
                . 'onfocus="var obj=document.getElementById(\'cpSearchBox\'); if (obj.value==\'Enter Search Text\') '
                . 'obj.value=\'\';" size="50" />';
            $searchHtml .= '<input type="button" value="Search" onclick="document.cpSearch.submit();" /></form></div>';
        }

        $listViewHtml = sprintf("<script>\n%s\n</script>\n%s<table class=\"cpListView\" width=\"100%%\">\n%s\n%s\n</table>\n<div id=\"cpPager\" class=\"cpPager\">\n%s%s</div>",
            $this->getListViewJavaScript(), $titleHtml, $headerHtml, $infoHtml, $pagerHtml, $searchHtml
        );

        return str_replace('[ListView]', $listViewHtml, $this->_listViewLayout);
    }

    private function getForm($name)
    {
        $html = sprintf('<form method="get" action="%s" name="cpSearch">',
            substr($_SERVER['REQUEST_URI'], 0, strpos($_SERVER['REQUEST_URI'], '?'))
        );
        foreach($_GET as $name => $value)
        {
            if (!strcmp($name, 'cpPageState'))
                $html .= sprintf('<input type="hidden" name="cpPageState" value="%d" />',
                    CPPS_LISTVIEW
                );
            else if(!strcmp($name, 'a') || !strcmp($name, 'm') || !strcmp($name, 'siteID'))
                $html .= sprintf('<input type="hidden" name="%s" value="%s" />',
                    htmlspecialchars($name), htmlspecialchars($value)
                );
        }
        return $html;
    }

    private function getFieldDBText($fieldData, $text)
    {
        $text = trim($text);
        if (strlen($text) == 0)
            return 'NULL';
        switch($fieldData['webFormType'])
        {
            case WFT_CC_EXPIRATION:
                list($expireMonth, $expireYear) = split('/', $text);
                return '"' . sprintf('%s-%s-01', $expireYear, $expireMonth) . '"';
            case WFT_CC_NUMBER:
                return '"' . addslashes(EncryptionUtility::encryptCreditCardNumber($text)) . '"';
            case WFT_BOOLEAN:
                return (!strcasecmp($text, 'true') ? '1' : '0');
            case WFT_CURRENCY:
                return sprintf('%.2f', floatval($text));
            case WFT_DATE:
                return '"' . date('c', strtotime($text)) . '"';
            default:
                return '"' . addslashes($text) . '"';
        }
    }

    private function getFieldInputText($fieldData, $rawData)
    {
        if (strlen(trim($rawData)) == 0)
            return '';
        switch($fieldData['webFormType'])
        {
            case WFT_CC_EXPIRATION:
                return date('n/Y', strtotime($rawData));
            case WFT_BOOLEAN:
                if (intval($rawData) != 0) return 'true';
                else return 'false';
            case WFT_CC_NUMBER:
                return EncryptionUtility::decryptCreditCardNumber($rawData);
            case WFT_DATE:
                return date('n/j/Y', strtotime($rawData));
            case WFT_CURRENCY:
                return '$' . number_format(floatval($rawData), 2, '.', ',');
            default:
                return $rawData;
        }
    }

    private function getFieldLinkText($row, $rawData)
    {
        foreach($row as $name => $value)
        {
            $rawData = str_replace(sprintf('[%s]', $name), $value, $rawData);
        }
        return $rawData;
    }

    private function getFieldHtmlText($fieldData, $rawData)
    {
        if (strlen(trim($rawData)) == 0)
            return CPSTR_EMPTY_FIELD;
        switch($fieldData['webFormType'])
        {
            case WFT_EMAIL:
                return sprintf('<a href="mailto:%s">%s</a>', $rawData, $rawData);
            case WFT_BOOLEAN:
                if (intval($rawData) != 0) return 'true';
                else return 'false';
            case WFT_CC_NUMBER:
                return EncryptionUtility::decryptCreditCardNumber($rawData);
            case WFT_DATE:
                return date('n/j/Y', strtotime($rawData));
            case WFT_CURRENCY:
                return '$' . number_format(floatval($rawData), 2, '.', ',');
            default:
                // Check for truncate
                if (isset($this->_truncate[$fieldData['uniqueID']]))
                {
                    $truncated = substr($rawData, 0, $this->_truncate[$fieldData['uniqueID']]);
                    if (strlen($truncated) != strlen($rawData))
                    {
                        $id = $this->_truncateID++;
                        return sprintf('<span onmouseover="document.getElementById(\'%sTruncate%d\').style.display=\'\';" '
                            . 'onmouseout="document.getElementById(\'%sTruncate%d\').style.display=\'none\';">'
                            . '%s<b> ...</b><div id="%sTruncate%d" style="display: none; position: absolute; '
                            . 'background-color: #d4e3ff; padding: 10px; border: 1px solid #87a6de; width: %dpx; margin: 20px;">%s</div>'
                            . '</span>',
                            $fieldData['uniqueID'], $id,
                            $fieldData['uniqueID'], $id,
                            $truncated,
                            $fieldData['uniqueID'], $id,
                            $this->_truncate[$fieldData['uniqueID']] * 7,
                            $rawData
                        );
                    }
                }
                return $rawData;
        }
    }

    public function addSection($name, $caption, $fields, $webFormLayout = '', $sectionLayout = '[WebForm]')
    {
        if ($name == CP_LISTVIEW)
        {
            if (!strcmp($sectionLayout, '[WebForm]'))
                $this->_listViewLayout = '[ListView]';
            else
                $this->_listViewLayout = $sectionLayout;
        }
        $this->_sections[$name] = array(
            'caption' => $caption,
            'webFormLayout' => $webFormLayout,
            'sectionLayout' => $sectionLayout,
            'fieldOrder' => $fields
        );
        foreach($this->_tables as $tableName => $tableData)
        {
            foreach($tableData['fields'] as $fieldName => $fieldData)
            {
                if (in_array($fieldName, $fields) || in_array($fieldData['uniqueID'], $fields))
                {
                    if (isset($this->_tables[$tableName]['fields'][$fieldName]['section']))
                        $this->_tables[$tableName]['fields'][$fieldName]['section'][] = $name;
                    else
                        $this->_tables[$tableName]['fields'][$fieldName]['section'] = array( $name );
                }
            }
        }
    }

    public function addField($name, $caption, $type, $required = false, $size = 16, $minlen = 0, $maxlen = -1,
        $defaultValue = -1, $regex_test = '', $regex_fail = '', $helpBody = -1,
        $helpRules = '')
    {
        foreach($this->_tables as $tableName => $tableData)
        {
            foreach($tableData['fields'] as $fieldName => $fieldData)
            {
                if (!strcmp($fieldName, $name) || (isset($fieldData['uniqueID']) && !strcmp($fieldData['uniqueID'], $name)))
                {
                    // Set some fields automatically using database data
                    if (!$fieldData['allowNull']) $required = true;
                    if (preg_match("/varchar\(([0-9]+)\)/", $fieldData['type'], $matches))
                    {
                        if ($maxlen == -1) $maxlen = intval($matches[1]);
                    }
                    if ($defaultValue == -1)
                        $defaultValue = $fieldData['defaultValue'];
                    if ($helpBody == -1)
                        $helpBody = $fieldData['description'];
                    $this->_tables[$tableName]['fields'][$fieldName]['activeField'] = true;
                    $this->_tables[$tableName]['fields'][$fieldName]['webFormType'] = $type;
                    $this->_tables[$tableName]['fields'][$fieldName]['caption'] = $caption;
                    $this->_tables[$tableName]['fields'][$fieldName]['webFormParams'] = array(
                        'name' => $name, 'caption' => $caption, 'type' => $type, 'required' => $required,
                        'size' => $size, 'minlen' => $minlen, 'maxlen' => $maxlen, 'defaultValue' => $defaultValue,
                        'regex_test' => $regex_test, 'regex_fail' => $regex_fail, 'helpBody' => $helpBody,
                        'helpRules' => $helpRules
                    );
                    return 1;
                }
            }
        }
        return -1;
    }

    /**
     * Builds an SQL query for returning all fields from all the tables
     * in the $_tables which are parsed MySQL tables.
     */
    public function getTablesSQL($relationshipSql = '', $limitSql = '', $selectSql = '')
    {
        $fieldsSql = '';
        $tablesSql = '';
        $relationshipsFound = 1;

        // check for sort-by field being passed by URI
        if (isset($_GET['cpSortByField']) || isset($_POST['cpSortByField']))
        {
            $this->setSortByField($this->getPostValue('cpSortByField'));
        }
        // Sort ASC/DESC
        if (isset($_GET['cpSortDesc']) || isset($_POST['cpSortDesc']))
        {
            $this->_sortDesc = (!strcmp($this->getPostValue('cpSortDesc'), 'false') ? false : true);
        }

        foreach($this->_tables as $tableName => $tableData)
        {
            foreach($tableData['fields'] as $fieldName => $fieldData)
            {
                if ((isset($fieldData['activeField']) && $fieldData['activeField'] == true) || !strcmp($tableData['primaryKey'], $fieldName))
                {
                    if ($fieldsSql != '') $fieldsSql .= ', ';
                    $fieldsSql .= sprintf(' %s.%s as %s',
                        $tableName, $fieldName,
                        $fieldData['uniqueID']
                    );
                }
            }

            foreach ($this->_tables as $subTableName => $subTableData)
            {
                if (!strcmp($subTableName, $tableName)) continue; // do not check the same table!

                foreach ($subTableData['fields'] as $subFieldName => $subFieldData)
                {
                    if (!strcmp($tableData['primaryKey'], $subFieldName))
                    {
                        if ($relationshipSql != '') $relationshipSql .= ' AND ';
                        $relationshipSql .= sprintf(' %s.%s = %s.%s',
                            $subTableName, $subFieldName, $tableName, $tableData['primaryKey']
                        );
                        $relationshipsFound++;
                    }
                }
            }

            if ($tablesSql != '') $tablesSql .= ', ';
            $tablesSql .= $tableName;
        }

        if ($relationshipsFound != count($this->_tables))
        {
            return -1;
        }

        $whereSql = '';
        if ($this->_selectBoundriesSql != '')
        {
            if ($whereSql != '') $whereSql .= ' AND ';
            $whereSql .= $this->_selectBoundriesSql;
        }
        if ($relationshipSql != '')
        {
            if ($whereSql != '') $whereSql .= ' AND ';
            $whereSql .= $relationshipSql;
        }

        if ($this->_sortDesc)
            $sort = ' DESC ';
        else
            $sort = ' ';

        $sql = sprintf('SELECT %s FROM %s %s%s%s%s%s%s%s',
            ($selectSql != '' ? $selectSql : $fieldsSql),
            $tablesSql,
            ($whereSql != '' ? 'WHERE ' : ''),
            $whereSql,
            ($this->_sortByField != '' && $selectSql == '' ? ' ORDER BY ' : ''),
            ($selectSql == '' ? $this->_sortByField : ''),
            ($this->_sortByField != '' && $selectSql == '' ? $sort : ''),
            ($limitSql != '' ? ' LIMIT ' : ''),
            $limitSql
        );

        return $sql;
    }

    /**
     * Adds and parses the format of a MySQL table (obtains primary key, field rules, etc.)
     *
     * @param string $name MySQL table name
     */
    public function addMySQLTable($name)
    {
        $this->_tables[$name] = array();
        $this->_tables[$name]['fields'] = array();
        // Fetch the fields from the table
        $rs = $this->_db->query('SHOW FIELDS FROM ' . $name);
        while ($row = mysql_fetch_array($rs, MYSQL_ASSOC))
        {
            $this->_tables[$name]['fields'][$row['Field']] = array(
                'type' => $row['Type'],
                'allowNull' => (strcmp($row['Null'], 'NO') ? true : false),
                'defaultValue' => $row['Default'],
                'description' => $row['Extra'],
                'uniqueID' => $this->getConvertUnderscoreToCamel($name . '_' . $row['Field']),
                'primaryKey' => (!strcmp($row['Key'], 'PRI') ? true : false)
            );
            if (!strcmp($row['Key'], 'PRI'))
            {
                $uniqueID = $this->_tables[$name]['fields'][$row['Field']]['uniqueID'];
                if ($this->_primaryKey == '')
                    $this->_primaryKey = $uniqueID;
                $this->_tables[$name]['primaryKey'] = $row['Field'];
            }
        }
    }

    public function printTables()
    {
        print_r($this->_tables);
    }

    private function getConvertUnderscoreToCamel($text)
    {
        for ($x = 0, $out = ''; $x < strlen($text); $x++)
        {
            if ($text[$x] == '_')
                $out .= strtoupper($text[++$x]);
            else
                $out .= $text[$x];
        }
        return $out;
    }

    private function getConvertCamelToUnderscore($text)
    {
        for ($x = 0, $out = ''; $x < strlen($text); $x++)
        {
            if (strtoupper($text[$x]) == $text[$x])
            {
                $out .= '_' . strtolower($text[$x]);
                $y = $x;
                while (($x+1) < strlen($text) && strtoupper($text[$x+1]) == $text[$x+1])
                    $out .= strtolower($text[++$x]);
                if ($y != $x)
                {
                    $out = substr($out, 0, -1) . '_' . strtolower($text[$x]);
                }
            }
            else
            {
                $out .= $text[$x];
            }
        }
        return $out;
    }

    public static function getPostValue($name)
    {
        if (isset($_GET[$name])) return $_GET[$name];
        else if(isset($_POST[$name])) return $_POST[$name];
        else return '';
    }

    public function setFieldUrl($field, $url, $comments)
    {
        $this->_fieldUrls[$field] = array( 'url' => $url, 'comments' => $comments );
    }

    public function setTruncate($field, $size)
    {
        $this->_truncate[$field] = $size;
    }

    public function addCallBack($mode, $val)
    {
        $this->_callBacks[$mode] = $val;
    }

    public function setShowCurrencySums($tf)
    {
        return ($this->_showCurrencySums = $tf);
    }

    public function setSelectBoundriesSQL($sql)
    {
        return ($this->_selectBoundriesSql = $sql);
    }

    public function setInsertBoundriesSQL($sql)
    {
        return ($this->_insertBoundriesSql = $sql);
    }

    public function setDeleteBoundriesSQL($sql)
    {
        return ($this->_deleteBoundriesSql = $sql);
    }

    public function setDeleteBoundriesTable($table)
    {
        return ($this->_deleteBoundriesTable = $table);
    }

    public function setPermissions($x)
    {
        $this->_permissions = $x;
    }

    public function setSortByField($name)
    {
        return ($this->_sortByField = $name);
    }

    public function getException($title, $message)
    {
        return sprintf('<b>%s</b><br />%s', $title, $message);
    }

    public function setLinkField($field)
    {
        return ($this->_linkField = $field);
    }
}
?>
