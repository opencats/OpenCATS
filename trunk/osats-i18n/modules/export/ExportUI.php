<?php
/*
 * CATS
 * RSS Feed module
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
 * This example module is meant as a very BASIC guide to the CATS module API.
 * It does not demonstrate every feature, but it should help you get started.
 * This example is also intended for programmers with a working knowledge of
 * PHP, so only aspects of the code specific to the CATS module API are
 * explained.
 *
 *
 * $Id: ExportUI.php 2996 2007-09-06 21:41:18Z brian $
 */

include_once('./lib/Export.php');
include_once('./lib/CommonErrors.php');


class ExportUI extends UserInterface
{
    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = true;
        $this->_moduleDirectory = 'export';
        $this->_moduleName = 'export';
        $this->_moduleTabText = '';
        $this->_subTabs = array();
    }

    public function handleRequest()
    {
        $action = $this->getAction();

        switch ($action)
        {
            case 'exportByDataGrid':
                $this->onExportByDataGrid();
                break;

            case 'export':
            default:
                $this->onExport();
                break;
        }
    }


    /**
     * Sets up export options and exports items
     *
     * @return void
     */
    public function onExport()
    {
        $filename = 'export.csv';

        /* Bail out if we don't have a valid data item type. */
        if (!$this->isRequiredIDValid('dataItemType', $_GET))
        {
            CommonErrors::fatal(COMMONERROR_BADFIELDS, $this, 'Invalid data item type.');
        }

        $dataItemType = $_GET['dataItemType'];

        /* Are we in "Only Selected" mode? */
        if ($this->isChecked('onlySelected', $_GET))
        {
            foreach ($_GET as $key => $value)
            {
                if (!strstr($key, 'checked_'))
                {
                    continue;
                }

                $IDs[] = str_replace('checked_', '', $key);
            }
        }
        else
        {
            /* No; do we have a list of IDs to export (Page Mode)? */
            $tempIDs = $this->getTrimmedInput('ids', $_GET);
            if (!empty($tempIDs))
            {
                $IDs = explode(',', $tempIDs);
            }
            else
            {
                /* No; All Records Mode. */
                $IDs = array();
            }
        }

        $export = new Export($dataItemType, $IDs, ',', $this->_siteID);
        $output = $export->getFormattedOutput();

        if (!eval(Hooks::get('EXPORT'))) return;

        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($output));
        header('Connection: close');
        header('Content-Type: text/x-csv; name=' . $filename);
        echo $output;
    }

    /**
     * Gets a datagrid and calls its drawCSV member function to output a CSV file.
     *
     * @return void
     */

    public function onExportByDataGrid()
    {
        $dataGrid = DataGrid::getFromRequest();

        $dataGrid->drawCSV();
    }

    /**
     * Print a fatal error and die.
     *
     * @param string error message
     * @param string module directory from which to load templates (optional)
     * @return void
     */
    protected function fatal($error, $directoryOverride = '')
    {
        die("Fatal Error\n\n" . $error);
    }
}

?>
