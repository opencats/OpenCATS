<?php
/**
 * CATS
 * Display Library
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
 * @version    $Id: Display.php 3831 2007-12-11 23:14:32Z brian $
 */

include_once('./lib/Profile.php');

// Global variable defining profile stylesheet (to prevent double inclusion)
$profileStylesheet = false;

class Display
{
    private $_siteID;
    private $_db;
    private $_profileLib;
    private $_profilePage;

    private $_table;
    private $_rowIndex;
    private $_columnIndex;
    private $_currentColumn;

    public function __construct($siteID, $profileLib, $profilePage)
    {
        $this->_siteID = $siteID;
        $this->_profileLib = $profileLib;
        $this->_profilePage = $profilePage;
        $this->_db = DatabaseConnection::getInstance();
    }

    /**
     * Get the profile's title text for a column name.
     *
     * @param string Column name (i.e.: first_name)
     * @return string Title text (i.e.: First Name)
     */
    public function getTitleText($columnName)
    {
        return $this->_profileLib->getTitleText(false, $columnName);
    }

    /**
     * Get all information about a column name.
     *
     * @param string Column name (i.e.: first_name)
     * @return array
     */
    public function getField($columnName)
    {
        return $this->_profileLib->getField(false, $this->_profilePage['page'], $columnName);
    }

    private function getTemplate($template, $flags = array())
    {
        $templateFile = sprintf(
            './profile/%s/%s.tpl',
            $this->_profileLib->getProfile(),
            $template
        );

        if (@file_exists($templateFile))
        {
            $templateContents = @file_get_contents($templateFile);
            foreach ($flags as $flag => $value)
            {
                $templateContents = str_replace('<'.$flag.'>', $value, $templateContents);
            }
        }
        else
        {
            $templateContents = '';
        }

        return $templateContents;
    }

    public function startTable()
    {
        global $profileStylesheet;
        // Check if the current profile's style has been included, include if it hasn't
        $sheet = $this->_profileLib->getProfileStylesheet();
        if ($profileStylesheet === false || strcmp($profileStylesheet, $sheet))
        {
            echo sprintf('<link rel="stylesheet" type="text/css" href="%s" />', $sheet);
            $profileStylesheet = $sheet;
        }

        $this->_table = array();
        $this->_rowIndex = $this->_columnIndex = 0;
        $this->_currentColumn = false;
    }

    public function endTable()
    {
        $pageContent = $this->getTemplate('page');
        list($pageTopContent, $pageBottomContent) = explode('<sections>', $pageContent);

        $sectionContent = $this->getTemplate('pageSection',
            array('sectionWidth' => $this->_profilePage['columnWidth'])
        );
        list($sectionTopContent, $sectionBottomContent) = explode('<columns>', $sectionContent);

        $columnContent = $this->getTemplate('pageColumn');

        $fields = $this->_profileLib->getPageFields(
            $this->_profileLib->getProfileID(),
            $this->_profilePage['page']
        );

        echo $pageTopContent;

        for ($fieldIndex = 0, $curColumn = -1, $inSection = false;
             $fieldIndex < count($fields);
             $fieldIndex++)
        {
            $field = $fields[$fieldIndex];

            if (!isset($this->_table[$field['columnName']]))
            {
                continue;
            }

            if ($curColumn != $field['xPosition'])
            {
                $curColumn = $field['xPosition'];

                if ($inSection)
                {
                    echo $sectionBottomContent;
                }

                echo $sectionTopContent;
                $inSection = true;
            }

            $data = $columnContent;
            $data = str_replace('<columnLabel>', $this->_table[$field['columnName']]['label'], $data);
            $data = str_replace('<columnLabelID>', sprintf('label_%s_%d', $this->_profilePage['page'], $fieldIndex), $data);
            $data = str_replace('<columnContent>', $this->_table[$field['columnName']]['content'], $data);
            $data = str_replace('<columnContentID>', sprintf('content_%s_%d', $this->_profilePage['page'], $fieldIndex), $data);
            $data = str_replace('<rowID>', sprintf('row_%s_%d', $this->_profilePage['page'], $fieldIndex), $data);

            echo $data;
        }

        echo $sectionBottomContent . $pageBottomContent;

        ?>
        <script type="text/javascript">
/*
        var table1 = document.getElementById('table_<?php echo $this->_profilePage['page']; ?>_1');
        var table2 = document.getElementById('table_<?php echo $this->_profilePage['page']; ?>_2');

        var tableDnD = new TableDnD();
        tableDnD.addTable(table1);
        tableDnD.addTable(table2);
*/

        // Redefine the onDrop so that we can display something
        /*
        tableDnD.onDrop = function(table, row) {
            var rows = this.table.tBodies[0].rows;
            var debugStr = "rows now: ";
            for (var i=0; i<rows.length; i++) {
                debugStr += rows[i].id+" ";
            }
            document.getElementById('debug').innerHTML = 'row['+row.id+'] dropped<br>'+debugStr;
        }
        */

        </script>
        <?php
    }

    public function startColumnLabel($columnName = false)
    {
        if ($columnName !== false)
        {
            $this->_currentColumn = $columnName;
        }
        ob_start();
    }

    public function endColumnLabel()
    {
        $this->_table[$this->_currentColumn]['label'] = ob_get_contents();
        ob_end_clean();
    }

    public function startColumnContent($columnName = false)
    {
        if ($columnName !== false)
        {
            $this->_currentColumn = $columnName;
        }
        ob_start();
    }

    public function endColumnContent()
    {
        echo '</td>';

        $this->_table[$this->_currentColumn]['content'] = ob_get_contents();
        ob_end_clean();
    }
}
