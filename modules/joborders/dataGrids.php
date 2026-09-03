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
 
include_once(LEGACY_ROOT . '/lib/JobOrders.php');
include_once(LEGACY_ROOT . '/lib/Hooks.php');
include_once(LEGACY_ROOT . '/lib/Width.php');

class JobOrdersListByViewDataGrid extends JobOrdersDataGrid
{
    public function __construct($parameters, $misc)
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
                             $parameters, $misc
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
    public function __construct($parameters, $misc)
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
                             $parameters, $misc
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

        $html .= $this->getInnerActionAreaItemPost('Remove From This List', CATSUtility::getIndexName().'?m=lists&amp;a=removeFromListDatagrid&amp;dataItemType='.DATA_ITEM_JOBORDER.'&amp;savedListID='.$this->getMiscArgument(), false);
        $html .= $this->getInnerActionAreaItem('Export', CATSUtility::getIndexName().'?m=export&amp;a=exportByDataGrid');

        $html .= parent::getInnerActionArea();

        return $html;

    }
}


?>
