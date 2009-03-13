<?php
/*
   * OSATS
   *
   *
   *
*/

include_once('./lib/Contacts.php');
include_once('./lib/Hooks.php');
include_once('./lib/i18n.php');

class ContactsListByViewDataGrid extends ContactsDataGrid
{
    public function __construct($siteID, $parameters, $misc)
    {
        /* Pager configuration. */
        $this->_tableWidth = 915;
        $this->_defaultAlphabeticalSortBy = 'lastName';
        $this->ajaxMode = false;
        $this->showExportCheckboxes = true; //BOXES WILL NOT APPEAR UNLESS SQL ROW exportID IS RETURNED!
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;

        $this->defaultSortBy = 'dateCreatedSort';
        $this->defaultSortDirection = 'DESC';

        $this->_defaultColumns = array(
            array('name' => __('Attachments'), 'width' => 10),
            array('name' => __('First Name'), 'width' => 80),
            array('name' => __('Last Name'), 'width' => 80),
            array('name' => __('Company'), 'width' => 135),
            array('name' => __('Title'), 'width' => 135),
            array('name' => __('Work Phone'), 'width' => 85),
            array('name' => __('Owner'), 'width' => 85),
            array('name' => __('Created'), 'width' => 60),
            array('name' => __('Modified'), 'width' => 60),
        );

        parent::__construct("contacts:ContactsListByViewDataGrid",
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

        $html .= $this->getInnerActionAreaItemPopup(__('Add To List'), osatutil::getIndexName().'?m=lists&amp;a=addToListFromDatagridModal&amp;dataItemType='.DATA_ITEM_CONTACT, 450, 350);
        $html .= $this->getInnerActionAreaItem('Export', osatutil::getIndexName().'?m=export&amp;a=exportByDataGrid');

        $html .= parent::getInnerActionArea();

        return $html;

    }
}

class contactSavedListByViewDataGrid extends ContactsDataGrid
{
    public function __construct($siteID, $parameters, $misc)
    {
        /* Pager configuration. */
        $this->_tableWidth = 915;
        $this->_defaultAlphabeticalSortBy = 'lastName';
        $this->ajaxMode = false;
        $this->showExportCheckboxes = true; //BOXES WILL NOT APPEAR UNLESS SQL ROW exportID IS RETURNED!
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;

        $this->defaultSortBy = 'dateCreatedSort';
        $this->defaultSortDirection = 'DESC';

        $this->_defaultColumns = array(
            array('name' => __('Attachments'), 'width' => 10),
            array('name' => __('First Name'), 'width' => 80),
            array('name' => __('Last Name'), 'width' => 80),
            array('name' => __('Company'), 'width' => 135),
            array('name' => __('Title'), 'width' => 135),
            array('name' => __('Work Phone'), 'width' => 85),
            array('name' => __('Owner'), 'width' => 85),
            array('name' => __('Created'), 'width' => 60),
            array('name' => __('Modified'), 'width' => 60),
        );

        parent::__construct("contacts:contactSavedListByViewDataGrid",
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

        $html .= $this->getInnerActionAreaItem(__('Remove From This List'), osatutil::getIndexName().'?m=lists&amp;a=removeFromListDatagrid&amp;dataItemType='.DATA_ITEM_CONTACT.'&amp;savedListID='.$this->getMiscArgument(), false);
        $html .= $this->getInnerActionAreaItem('Export', osatutil::getIndexName().'?m=export&amp;a=exportByDataGrid');

        $html .= parent::getInnerActionArea();

        return $html;

    }
}