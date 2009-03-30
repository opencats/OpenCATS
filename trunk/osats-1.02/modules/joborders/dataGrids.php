<?php 
/*
 * OSATS
 * Joborder Datagrid
 * Need to put in GNU info...
 * OSATS 
 */
 
include_once('lib/JobOrders.php');
include_once('./lib/Hooks.php');
include_once('./lib/i18n.php');

class JobOrdersListByViewDataGrid extends JobOrdersDataGrid
{
    public function __construct($siteID, $parameters, $misc)
    {
        /* Pager configuration. */
        $this->_tableWidth = 915;
        $this->_defaultAlphabeticalSortBy = 'title';
        $this->ajaxMode = false;
        $this->showExportCheckboxes = true; //BOXES WILL NOT APPEAR UNLESS SQL ROW exportID IS RETURNED!
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;

        $this->defaultSortBy = 'dateCreatedSort';
        $this->defaultSortDirection = 'DESC';
   
        $this->_defaultColumns = array(
            array('name' => __('Attachments'), 'width' => 10),
            array('name' => __('ID'), 'width' => 26),   
            array('name' => __('Title'), 'width' => 170),
            array('name' => __('Company'), 'width' => 135),
            array('name' => __('Type'), 'width' => 30),
            array('name' => __('Status'), 'width' => 40),
            array('name' => __('Created'), 'width' => 55),
            array('name' => __('Age'), 'width' => 30),
            array('name' => __('Submitted'), 'width' => 18),
            array('name' => __('Pipeline'), 'width' => 18),
            array('name' => __('Recruiter'), 'width' => 65),
            array('name' => __('Owner'), 'width' => 55),
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

        $html .= $this->getInnerActionAreaItemPopup(__('Add To List'), osatutil::getIndexName().'?m=lists&amp;a=addToListFromDatagridModal&amp;dataItemType='.DATA_ITEM_JOBORDER, 450, 350);
        $html .= $this->getInnerActionAreaItem('Export', osatutil::getIndexName().'?m=export&amp;a=exportByDataGrid');

        $html .= parent::getInnerActionArea();

        return $html;

    }
}

class joborderSavedListByViewDataGrid extends JobOrdersDataGrid
{
    public function __construct($siteID, $parameters, $misc)
    {
        /* Pager configuration. */
        $this->_tableWidth = 915;
        $this->_defaultAlphabeticalSortBy = 'title';
        $this->ajaxMode = false;
        $this->showExportCheckboxes = true; //BOXES WILL NOT APPEAR UNLESS SQL ROW exportID IS RETURNED!
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;

        $this->defaultSortBy = 'dateCreatedSort';
        $this->defaultSortDirection = 'DESC';
   
        $this->_defaultColumns = array(
            array('name' => __('Attachments'), 'width' => 10),
            array('name' => __('ID'), 'width' => 26),   
            array('name' => __('Title'), 'width' => 170),
            array('name' => __('Company'), 'width' => 135),
            array('name' => __('Type'), 'width' => 30),
            array('name' => __('Status'), 'width' => 40),
            array('name' => __('Created'), 'width' => 55),
            array('name' => __('Age'), 'width' => 30),
            array('name' => __('Submitted'), 'width' => 18),
            array('name' => __('Pipeline'), 'width' => 18),
            array('name' => __('Recruiter'), 'width' => 65),
            array('name' => __('Owner'), 'width' => 55),
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

        $html .= $this->getInnerActionAreaItem(__('Remove From This List'), osatutil::getIndexName().'?m=lists&amp;a=removeFromListDatagrid&amp;dataItemType='.DATA_ITEM_JOBORDER.'&amp;savedListID='.$this->getMiscArgument(), false);
        $html .= $this->getInnerActionAreaItem('Export', osatutil::getIndexName().'?m=export&amp;a=exportByDataGrid');

        $html .= parent::getInnerActionArea();

        return $html;

    }
}