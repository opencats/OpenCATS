<?php

//TODO: License

include_once('lib/Candidates.php');
include_once('./lib/i18n.php');

class candidatesListByViewDataGrid extends CandidatesDataGrid
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

        $this->defaultSortBy = 'dateModifiedSort';
        $this->defaultSortDirection = 'DESC';

        $this->_defaultColumns = array(
            array('name' => __('Attachments'), 'width' => 31),
            array('name' => __('First Name'), 'width' => 75),
            array('name' => __('Last Name'), 'width' => 85),
            array('name' => __('City'), 'width' => 75),
            array('name' => __('State'), 'width' => 50),
            array('name' => __('Key Skills'), 'width' => 215),
            array('name' => __('Owner'), 'width' => 65),
            array('name' => __('Created'), 'width' => 60),
            array('name' => __('Modified'), 'width' => 60),
        );

         parent::__construct("candidates:candidatesListByViewDataGrid",
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

        $html .= $this->getInnerActionAreaItemPopup(__('Add To List'), osatutil::getIndexName().'?m=lists&amp;a=addToListFromDatagridModal&amp;dataItemType='.DATA_ITEM_CANDIDATE, 450, 350);
        $html .= $this->getInnerActionAreaItemPopup(__('Add To Pipeline'), osatutil::getIndexName().'?m=candidates&amp;a=considerForJobSearch', 750, 460);
        if(MAIL_MAILER != 0)
        {
            $html .= $this->getInnerActionAreaItem(__('Send E-Mail'), osatutil::getIndexName().'?m=candidates&amp;a=emailCandidates');
        }
        $html .= $this->getInnerActionAreaItem(__('Export'), osatutil::getIndexName().'?m=export&amp;a=exportByDataGrid');

        $html .= parent::getInnerActionArea();

        return $html;
    }
}

class candidatesSavedListByViewDataGrid extends CandidatesDataGrid
{
    public function __construct($siteID, $parameters, $misc)
    {
        $this->_tableWidth = 915;
        $this->_defaultAlphabeticalSortBy = 'lastName';
        $this->ajaxMode = false;
        $this->showExportCheckboxes = true; //BOXES WILL NOT APPEAR UNLESS SQL ROW exportID IS RETURNED!
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;

        $this->defaultSortBy = 'dateModifiedSort';
        $this->defaultSortDirection = 'DESC';

        $this->_defaultColumns = array(
            array('name' => __('Attachments'), 'width' => 31),
            array('name' => __('First Name'), 'width' => 75),
            array('name' => __('Last Name'), 'width' => 85),
            array('name' => __('City'), 'width' => 75),
            array('name' => __('State'), 'width' => 50),
            array('name' => __('Key Skills'), 'width' => 200),
            array('name' => __('Owner'), 'width' => 65),
            array('name' => __('Modified'), 'width' => 60),
            array('name' => __('Added To List'), 'width' => 75),
        );

         parent::__construct("candidates:candidatesSavedListByViewDataGrid",
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

        $html .= $this->getInnerActionAreaItem(__('Remove From This List'), osatutil::getIndexName().'?m=lists&amp;a=removeFromListDatagrid&amp;dataItemType='.DATA_ITEM_CANDIDATE.'&amp;savedListID='.$this->getMiscArgument(), false);
        $html .= $this->getInnerActionAreaItemPopup(__('Add To Pipeline'), osatutil::getIndexName().'?m=candidates&amp;a=considerForJobSearch', 750, 460);
        if(MAIL_MAILER != 0)
        {
            $html .= $this->getInnerActionAreaItem(__('Send E-Mail'), osatutil::getIndexName().'?m=candidates&amp;a=emailCandidates');
        }
        $html .= $this->getInnerActionAreaItem(__('Export'), osatutil::getIndexName().'?m=export&amp;a=exportByDataGrid');

        $html .= parent::getInnerActionArea();

        return $html;
    }
}