<?php
/*
 * CATS
 * Lists Module
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
 * $Id: ListsUI.php 3807 2007-12-05 01:47:41Z will $
 */

include_once(LEGACY_ROOT . '/lib/StringUtility.php');
include_once(LEGACY_ROOT . '/lib/DateUtility.php'); /* Depends on StringUtility. */
include_once(LEGACY_ROOT . '/lib/ResultSetUtility.php');
include_once(LEGACY_ROOT . '/lib/Companies.php');
include_once(LEGACY_ROOT . '/lib/Contacts.php');
include_once(LEGACY_ROOT . '/lib/JobOrders.php');
include_once(LEGACY_ROOT . '/lib/Attachments.php');
include_once(LEGACY_ROOT . '/lib/Export.php');
include_once(LEGACY_ROOT . '/lib/ListEditor.php');
include_once(LEGACY_ROOT . '/lib/FileUtility.php');
include_once(LEGACY_ROOT . '/lib/SavedLists.php');
include_once(LEGACY_ROOT . '/lib/ExtraFields.php');


class ListsUI extends UserInterface
{

    public function __construct()
    {
        parent::__construct();

        $this->_authenticationRequired = true;
        $this->_moduleDirectory = 'lists';
        $this->_moduleName = 'lists';
        $this->_moduleTabText = 'Lists';
        $this->_subTabs = array(
            'Show Lists'     => CATSUtility::getIndexName() . '?m=lists'
           /* 'New Static List' => CATSUtility::getIndexName() . '?m=lists&a=newListStatic*al=' . ACCESS_LEVEL_EDIT  . '@lists.newListStatic', */
           /* 'New Dynamic List' => CATSUtility::getIndexName() . '?m=lists&a=newListDynamic*al=' . ACCESS_LEVEL_EDIT . '@lists.newListDynamic' */
        );
    }


    public function handleRequest()
    {
        $action = $this->getAction();

        if (!eval(Hooks::get('LISTS_HANDLE_REQUEST'))) return;

        switch ($action)
        {
            /* FIXME: function show() undefined
            case 'show':
                $this->show();
                break;
            */

            case 'showList':
                $this->showList();
                break;

            /* Add to list popup. */
            case 'quickActionAddToListModal':
                $this->quickActionAddToListModal();
                break;

            /* Add to list popup via datagrid. */
            case 'addToListFromDatagridModal':
                $this->addToListFromDatagridModal();
                break;

            case 'removeFromListDatagrid':
                $this->removeFromListDatagrid();
                break;

            case 'deleteStaticList':
                $this->onDeleteStaticList();
                break;

            /* Main list page. */
            case 'listByView':
            default:
                $this->listByView();
                break;
        }
    }

    /*
     * Called by handleRequest() to process loading the list / main page.
     */
    private function listByView()
    {
        /* First, if we are operating in HR mode we will never see the
           companies pager.  Immediantly forward to My Company. */

        $dataGridProperties = DataGrid::getRecentParamaters("lists:ListsDataGrid");

        /* If this is the first time we visited the datagrid this session, the recent paramaters will
         * be empty.  Fill in some default values. */
        if ($dataGridProperties == array())
        {
            $dataGridProperties = array('rangeStart'    => 0,
                                        'maxResults'    => 15,
                                        'filterVisible' => false);
        }

        $dataGrid = DataGrid::get("lists:ListsDataGrid", $dataGridProperties);

        $this->_template->assign('active', $this);
        $this->_template->assign('dataGrid', $dataGrid);
        $this->_template->assign('userID', $_SESSION['CATS']->getUserID());

        if (!eval(Hooks::get('LISTS_LIST_BY_VIEW'))) return;

        $this->_template->display('./modules/lists/Lists.tpl');
    }

    /*
     * Called by handleRequest() to process loading the static list display.
     */

    private function showList()
    {
        /* Bail out if we don't have a valid candidate ID. */
        if (!$this->isRequiredIDValid('savedListID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this);
            return;
            //$this->fatalModal('Invalid saved list ID.');
        }

        //$dateAvailable = $this->getTrimmedInput('dateAvailable', $_POST);

        $savedListID = $_GET['savedListID'];

        $savedLists = new SavedLists($this->_siteID);

        $listRS = $savedLists->get($savedListID);

        if ($listRS['isDynamic'] == 0)
        {
            // Handle each kind of static list here:

            switch($listRS['dataItemType'])
            {
                case DATA_ITEM_CANDIDATE:
                    $dataGridInstance = 'candidates:candidatesSavedListByViewDataGrid';
                    break;

                case DATA_ITEM_COMPANY:
                    $dataGridInstance = 'companies:companiesSavedListByViewDataGrid';
                    break;

                case DATA_ITEM_CONTACT:
                    $dataGridInstance = 'contacts:contactSavedListByViewDataGrid';
                    break;

                case DATA_ITEM_JOBORDER:
                    $dataGridInstance = 'joborders:joborderSavedListByViewDataGrid';
                    break;
            }
        }

        $dataGridProperties = DataGrid::getRecentParamaters($dataGridInstance, $savedListID);

        /* If this is the first time we visited the datagrid this session, the recent paramaters will
         * be empty.  Fill in some default values. */
        if ($dataGridProperties == array())
        {
            $dataGridProperties = array('rangeStart'    => 0,
                                        'maxResults'    => 15,
                                        'filterVisible' => false,
                                        'savedListStatic' => true);
        }

        /* Add an MRU entry. */
        $_SESSION['CATS']->getMRU()->addEntry(
            DATA_ITEM_LIST, $savedListID, $listRS['description']
        );

        $dataGrid = DataGrid::get($dataGridInstance, $dataGridProperties, $savedListID);

        $this->_template->assign('active', $this);
        $this->_template->assign('dataGrid', $dataGrid);
        $this->_template->assign('listRS', $listRS);
        $this->_template->assign('userID', $_SESSION['CATS']->getUserID());

        $this->_template->display('./modules/lists/List.tpl');

    }

    /*
     * Called by handleRequest to process loading the add to list popup window.
     */
    private function quickActionAddToListModal()
    {
        /* Bail out if we don't have a valid type. */
        if (!$this->isRequiredIDValid('dataItemType', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this);
            return;
        }

        /* Bail out if we don't have a valid id. */
        if (!$this->isRequiredIDValid('dataItemID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this);
            return;
        }

        $dataItemType = $_GET['dataItemType'];
        $dataItemID = $_GET['dataItemID'];
        $dataItemIDArray = array($dataItemID);

        $savedLists = new SavedLists($this->_siteID);

        $savedListsRS = $savedLists->getAll($dataItemType, STATIC_LISTS);

        $dataItemDesc = TemplateUtility::getDataItemTypeDescription($dataItemType);

        $this->_template->assign('dataItemDesc', $dataItemDesc);
        $this->_template->assign('savedListsRS', $savedListsRS);
        $this->_template->assign('dataItemType', $dataItemType);
        $this->_template->assign('dataItemIDArray', $dataItemIDArray);
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());

        $this->_template->display('./modules/lists/QuickActionAddToListModal.tpl');
    }

    /*
     * Called by handleRequest to process loading the add to list popup window from a datagrid.
     */
    private function addToListFromDatagridModal()
    {
        /* Bail out if we don't have a valid type. */
        if (!$this->isRequiredIDValid('dataItemType', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this);
            return;
        }

        $dataGrid = DataGrid::getFromRequest();

        $dataItemIDArray = $dataGrid->getExportIDs();

        /* Validate each ID */
        foreach ($dataItemIDArray as $index => $dataItemID)
        {
            if (!$this->isRequiredIDValid($index, $dataItemIDArray))
            {
                CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid data item ID.');
                return;
            }
        }

        $dataItemType = $_GET['dataItemType'];

        $savedLists = new SavedLists($this->_siteID);

        $savedListsRS = $savedLists->getAll($dataItemType, STATIC_LISTS);

        $dataItemDesc = TemplateUtility::getDataItemTypeDescription($dataItemType);

        $this->_template->assign('dataItemDesc', $dataItemDesc);
        $this->_template->assign('savedListsRS', $savedListsRS);
        $this->_template->assign('dataItemType', $dataItemType);
        $this->_template->assign('dataItemIDArray', $dataItemIDArray);
        $this->_template->assign('sessionCookie', $_SESSION['CATS']->getCookie());

        $this->_template->display('./modules/lists/QuickActionAddToListModal.tpl');
    }

    /*
     * Called by handleRequest to process the remove items from datagrid popup.
     */
    private function removeFromListDatagrid()
    {
        /* Bail out if we don't have a valid type. */
        if (!$this->isRequiredIDValid('dataItemType', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this);
            return;
        }

        $dataGrid = DataGrid::getFromRequest();

        $dataItemIDArray = $dataGrid->getExportIDs();

        /* Validate each ID */
        foreach ($dataItemIDArray as $index => $dataItemID)
        {
            if (!$this->isRequiredIDValid($index, $dataItemIDArray))
            {
                CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid data item ID.');
                return;
            }
        }

        $dataItemType = $_GET['dataItemType'];

        $dataItemDesc = TemplateUtility::getDataItemTypeDescription($dataItemType);

        if (!$this->isRequiredIDValid('savedListID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this, 'Invalid saved list ID.');
            return;
        }

        $savedListID = $_GET['savedListID'];

        /* Remove the items */
        $savedLists = new SavedLists($this->_siteID);

        $dataItemIDArrayTemp = array();
        foreach ($dataItemIDArray as $dataItemID)
        {
            $dataItemIDArrayTemp[] = $dataItemID;
            /* Because its too slow adding 1 item at a time, we do it in spurts of 200 items. */
            if (count($dataItemIDArrayTemp) > 200)
            {
                $savedLists->removeEntryMany($savedListID, $dataItemIDArrayTemp);
                $dataItemIDArrayTemp = array();
            }
        }
        if (count($dataItemIDArrayTemp) > 0)
        {
            $savedLists->removeEntryMany($savedListID, $dataItemIDArrayTemp);
        }

        /* Redirect to the saved list page we were on. */
        /* FIXME: What if we are on the last page? */
        CATSUtility::transferRelativeURI('m=lists&a=showList&savedListID='.$savedListID);
    }

    /*
     * Called by handleRequest to delete a list.
     */
    private function onDeleteStaticList()
    {
        /* Bail out if we don't have a valid type. */
        if (!$this->isRequiredIDValid('savedListID', $_GET))
        {
            CommonErrors::fatalModal(COMMONERROR_BADINDEX, $this);
            return;
        }

        $savedListID = $_GET['savedListID'];

        $savedLists = new SavedLists($this->_siteID);

        /* Write changes. */
        $savedLists->delete($savedListID);


        CATSUtility::transferRelativeURI('m=lists');
    }
}

?>
