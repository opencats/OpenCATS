<?php 
/*
 * OSATS
 * Companies Datagrid
 *
 * OSATS Version: 0.8.0 (Jhelum)
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 *
 *
 * The contents of this file are subject to the OSATS Public License
 * Version 1.1a (the "License"); you may not use this file except in
 * compliance with the License. You may obtain a copy of the License at
 * http://www.OSATSone.com/. Software distributed under the License is
 * distributed on an "AS IS" basis, WITHOUT WARRANTY OF ANY KIND, either
 * express or implied. See the License for the specific language governing
 * rights and limitations under the License.
 *
 * Software distributed under the License is distributed on an "AS IS"
 * basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See the
 * License for the specific language governing rights and limitations
 * under the License.
 *
 * The Original Code is "OSATS Standard Edition".
 *
 * The Initial Developer of the Original Code is Cognizo Technologies, Inc.
 * Portions created by the Initial Developer are Copyright (C) 2005 - 2007
 * (or from the year in which this file was created to the year 2007) by
 * Cognizo Technologies, Inc. All Rights Reserved.
 *
 * $Id: dataGrids.php 3096 2007-09-25 19:27:04Z brian $
 */
 
include_once('./lib/Companies.php');
include_once('./lib/Hooks.php');
include_once('./lib/i18n.php');

class CompaniesListByViewDataGrid extends CompaniesDataGrid
{
    public function __construct($siteID, $parameters, $misc)
    {
        /* Pager configuration. */
        $this->_tableWidth = 915;
        $this->_defaultAlphabeticalSortBy = 'name';
        $this->ajaxMode = false;
        $this->showExportCheckboxes = true; //BOXES WILL NOT APPEAR UNLESS SQL ROW exportID IS RETURNED!
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;

        $this->defaultSortBy = 'dateCreatedSort';
        $this->defaultSortDirection = 'DESC';
   
        $this->_defaultColumns = array( 
            array('name' => __('Attachments'), 'width' => 10),
            array('name' => __('Name'), 'width' => 255),
            array('name' => __('Jobs'), 'width' => 40),
            array('name' => __('City'), 'width' => 90),
            array('name' => __('State'), 'width' => 50),
            array('name' => __('Phone'), 'width' => 85),
            array('name' => __('Owner'), 'width' => 65),
            array('name' => __('Created'), 'width' => 60),
            array('name' => __('Modified'), 'width' => 60),
        );
   
        parent::__construct("companies:CompaniesListByViewDataGrid", 
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
        //TODO: Add items:
        //  - Add to List
        //  - Add to Pipeline
        //  - Mass set rank (depends on each candidate having their own personal rank - are we going to do this?)
        $html = '';

        $html .= $this->getInnerActionAreaItemPopup(__('Add To List'), osatutil::getIndexName().'?m=lists&amp;a=addToListFromDatagridModal&amp;dataItemType='.DATA_ITEM_COMPANY, 450, 350);
        $html .= $this->getInnerActionAreaItem(__('Export'), osatutil::getIndexName().'?m=export&amp;a=exportByDataGrid');

        $html .= parent::getInnerActionArea();

        return $html;
    }
}

class companiesSavedListByViewDataGrid extends CompaniesDataGrid
{
    public function __construct($siteID, $parameters, $misc)
    {
        /* Pager configuration. */
        $this->_tableWidth = 915;
        $this->_defaultAlphabeticalSortBy = 'name';
        $this->ajaxMode = false;
        $this->showExportCheckboxes = true; //BOXES WILL NOT APPEAR UNLESS SQL ROW exportID IS RETURNED!
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;

        $this->defaultSortBy = 'dateCreatedSort';
        $this->defaultSortDirection = 'DESC';
   
        $this->_defaultColumns = array( 
            array('name' => __('Attachments'), 'width' => 10),
            array('name' => __('Name'), 'width' => 255),
            array('name' => __('Jobs'), 'width' => 40),
            array('name' => __('City'), 'width' => 90),
            array('name' => __('State'), 'width' => 50),
            array('name' => __('Phone'), 'width' => 85),
            array('name' => __('Owner'), 'width' => 65),
            array('name' => __('Created'), 'width' => 60),
            array('name' => __('Modified'), 'width' => 60),
        );
   
        parent::__construct("companies:companiesSavedListByViewDataGrid", 
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
        //TODO: Add items:
        //  - Add to List
        //  - Add to Pipeline
        //  - Mass set rank (depends on each candidate having their own personal rank - are we going to do this?)
        $html = '';

        $html .= $this->getInnerActionAreaItem(__('Remove From This List'), osatutil::getIndexName().'?m=lists&amp;a=removeFromListDatagrid&amp;dataItemType='.DATA_ITEM_COMPANY.'&amp;savedListID='.$this->getMiscArgument(), false);
        $html .= $this->getInnerActionAreaItem('Export', osatutil::getIndexName().'?m=export&amp;a=exportByDataGrid');

        $html .= parent::getInnerActionArea();

        return $html;
    }
}