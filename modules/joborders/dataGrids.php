<?php 
/*
 * CATS
 * Joborder Datagrid
 *
 * CATS Version: 0.9.4 Countach
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the CATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.catsone.com/. Software distributed under the License is
 * distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * rights and limitations under the License.
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
 * $Id: dataGrids.php 3096 2007-09-25 19:27:04Z brian $
 */
 
include_once('lib/JobOrders.php');
include_once('./lib/Hooks.php');
include_once('./lib/Width.php');

class JobOrdersListByViewDataGrid extends JobOrdersDataGrid
{
    public function __construct($siteID, $parameters, $misc)
    {
        /* Pager configuration. */
        $this->_tableWidth = new Width(100, '%');
        $this->_defaultAlphabeticalSortBy = 'title';
        $this->ajaxMode = false;
        $this->showExportCheckboxes = true; //BOXES WILL NOT APPEAR UNLESS SQL ROW exportID IS RETURNED!
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;

        $this->defaultSortBy = 'dateCreatedSort';
        $this->defaultSortDirection = 'DESC';
   
        $this->_defaultColumns = array(
            array('name' => 'Attachments', 'width' => 10),
            array('name' => 'ID', 'width' => 26),   
            array('name' => 'Title', 'width' => 170),
            array('name' => 'Company', 'width' => 135),
            array('name' => 'Type', 'width' => 30),
            array('name' => 'Status', 'width' => 40),
            array('name' => 'Created', 'width' => 55),
            array('name' => 'Age', 'width' => 30),
            array('name' => 'Submitted', 'width' => 18),
            array('name' => 'Pipeline', 'width' => 18),
            array('name' => 'Recruiter', 'width' => 65),
            array('name' => 'Owner', 'width' => 55),
        );
   
        if (!eval(Hooks::get('JOBORDERS_DATAGRID_DEFAULTS'))) return;
   
        parent::__construct("joborders:JobOrdersListByViewDataGrid", 
                             $siteID, $parameters, $misc
                        );
    }
    

    /**
     * Adds more options to the action area on the pager.  Overloads 
     * DataGrid Inner Action Area function.
     *
     * @return html innerActionArea commands.
     */    
    public function getInnerActionArea()
    {
        $html = '';

        $html .= $this->getInnerActionAreaItemPopup('Add To List', CATSUtility::getIndexName().'?m=lists&amp;a=addToListFromDatagridModal&amp;dataItemType='.DATA_ITEM_JOBORDER, 450, 350);
        $html .= $this->getInnerActionAreaItem('Export', CATSUtility::getIndexName().'?m=export&amp;a=exportByDataGrid');

        $html .= parent::getInnerActionArea();

        return $html;

    }
}

class joborderSavedListByViewDataGrid extends JobOrdersDataGrid
{
    public function __construct($siteID, $parameters, $misc)
    {
        /* Pager configuration. */
        $this->_tableWidth = new Width(100, '%');
        $this->_defaultAlphabeticalSortBy = 'title';
        $this->ajaxMode = false;
        $this->showExportCheckboxes = true; //BOXES WILL NOT APPEAR UNLESS SQL ROW exportID IS RETURNED!
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;

        $this->defaultSortBy = 'dateCreatedSort';
        $this->defaultSortDirection = 'DESC';
   
        $this->_defaultColumns = array(
            array('name' => 'Attachments', 'width' => 10),
            array('name' => 'ID', 'width' => 26),   
            array('name' => 'Title', 'width' => 170),
            array('name' => 'Company', 'width' => 135),
            array('name' => 'Type', 'width' => 30),
            array('name' => 'Status', 'width' => 40),
            array('name' => 'Created', 'width' => 55),
            array('name' => 'Age', 'width' => 30),
            array('name' => 'Submitted', 'width' => 18),
            array('name' => 'Pipeline', 'width' => 18),
            array('name' => 'Recruiter', 'width' => 65),
            array('name' => 'Owner', 'width' => 55),
        );
   
        if (!eval(Hooks::get('JOBORDERS_DATAGRID_DEFAULTS'))) return;
   
        parent::__construct("joborders:joborderSavedListByViewDataGrid", 
                             $siteID, $parameters, $misc
                        );
    }
    

    /**
     * Adds more options to the action area on the pager.  Overloads 
     * DataGrid Inner Action Area function.
     *
     * @return html innerActionArea commands.
     */    
    public function getInnerActionArea()
    {
        $html = '';

        $html .= $this->getInnerActionAreaItem('Remove From This List', CATSUtility::getIndexName().'?m=lists&amp;a=removeFromListDatagrid&amp;dataItemType='.DATA_ITEM_JOBORDER.'&amp;savedListID='.$this->getMiscArgument(), false);
        $html .= $this->getInnerActionAreaItem('Export', CATSUtility::getIndexName().'?m=export&amp;a=exportByDataGrid');

        $html .= parent::getInnerActionArea();

        return $html;

    }
}


?>