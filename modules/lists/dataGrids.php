<?php 
/*
 * CATS
 * Companies Datagrid
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
 * $Id: dataGrids.php 3566 2007-11-12 09:46:35Z will $
 */
 
include_once('./lib/Companies.php');
include_once('./lib/Hooks.php');
include_once('./lib/Width.php');

class ListsDataGrid extends DataGrid
{   
    // FIXME: Fix ugly indenting - ~400 character lines = bad.
    public function __construct($siteID, $parameters, $misc)
    {
        /* Pager configuration. */
        $this->_tableWidth = new Width(100, '%');
        $this->_defaultAlphabeticalSortBy = 'description';
        $this->ajaxMode = false;
        $this->showExportCheckboxes = true; //BOXES WILL NOT APPEAR UNLESS SQL ROW exportID IS RETURNED!
        $this->showActionArea = true;
        $this->showChooseColumnsBox = true;
        $this->allowResizing = true;

        $this->defaultSortBy = 'description';
        $this->defaultSortDirection = 'DESC';
   
        $this->_defaultColumns = array( 
            array('name' => 'Attachments', 'width' => 10),
            array('name' => 'Name', 'width' => 255),
            array('name' => 'Jobs', 'width' => 40),
            array('name' => 'City', 'width' => 90),
            array('name' => 'State', 'width' => 50),
            array('name' => 'Phone', 'width' => 85),
            array('name' => 'Owner', 'width' => 65),
            array('name' => 'Created', 'width' => 60),
            array('name' => 'Modified', 'width' => 60),
        );
   
        $this->_classColumns = array(
            'Count' =>         array ('select'          => 'number_entries as numberEntries',
                                      'pagerRender'     => 'return $rsData[\'numberEntries\'];',
                                      'pagerWidth'      => 45,
                                      'alphaNavigation' => false,
                                      'pagerOptional'   => true,
                                      'sortableColumn'  => 'number_entries',
                                      'filter'          => 'number_entries',
                                      'filterTypes'     => '===>=<'),
            'Description' =>   array('select'         => '', 
                                      'pagerRender'    => 'return \'<a href="'.CATSUtility::getIndexName().'?m=lists&amp;a=showList&amp;savedListID=\'.$rsData[\'savedListID\'].\'">\'.htmlspecialchars($rsData[\'description\']).\'</a>\';',
                                      'sortableColumn' => 'description',
                                      'pagerWidth'     => 355,
                                      'pagerOptional'  => false,
                                      'filter'         => 'saved_list.description'),
            'List Type' =>      array('pagerRender'    => 'return ($rsData[\'isDynamic\']==1?\'Dynamic\':\'Static\');',
                                      'sortableColumn' => 'isDynamic',
                                      'pagerWidth'     => 75,
                                      'pagerOptional'  => true,
                                      'filter'         => 'data_item_type.short_description'),
            'Data Type' =>      array('select'         => 'data_item_type.short_description AS dataItemTypeSortDesc', 
                                      'join'           => 'LEFT JOIN data_item_type on data_item_type.data_item_type_id = saved_list.data_item_type',
                                      'pagerRender'    => 'return ($rsData[\'dataItemTypeSortDesc\']);',
                                      'sortableColumn' => 'dataItemTypeSortDesc',
                                      'pagerWidth'     => 75,
                                      'pagerOptional'  => true,
                                      'filter'         => 'data_item_type.short_description'),
            'Owner' =>          array('select'   => 'owner_user.first_name AS ownerFirstName,' .
                                                    'owner_user.last_name AS ownerLastName,' .
                                                    'CONCAT(owner_user.last_name, owner_user.first_name) AS ownerSort',
                                      'join'     => 'LEFT JOIN user AS owner_user ON saved_list.created_by = owner_user.user_id',
                                      'pagerRender'      => 'return StringUtility::makeInitialName($rsData[\'ownerFirstName\'], $rsData[\'ownerLastName\'], false, LAST_NAME_MAXLEN);',
                                      'exportRender'     => 'return $rsData[\'ownerFirstName\'] . " " .$rsData[\'ownerLastName\'];',
                                      'sortableColumn'     => 'ownerSort',
                                      'pagerWidth'    => 75,
                                      'alphaNavigation' => true,
                                      'filter'         => 'CONCAT(owner_user.first_name, owner_user.last_name)'),
            'Created' =>        array('select'   => 'DATE_FORMAT(saved_list.date_created, \'%m-%d-%y\') AS dateCreated',
                                      'pagerRender'      => 'return $rsData[\'dateCreated\'];',
                                      'sortableColumn'     => 'dateCreatedSort',
                                      'pagerWidth'    => 60,
                                      'filterHaving' => 'DATE_FORMAT(saved_list.date_created, \'%m-%d-%y\')'),
            'Modified' =>       array('select'   => 'DATE_FORMAT(saved_list.date_modified, \'%m-%d-%y\') AS dateModified',
                                      'pagerRender'      => 'return $rsData[\'dateModified\'];',
                                      'sortableColumn'     => 'dateModifiedSort',
                                      'pagerWidth'    => 60,
                                      'pagerOptional' => true,
                                      'filterHaving' => 'DATE_FORMAT(saved_list.date_modified, \'%m-%d-%y\')')
        );

        $this->_defaultColumns = array(
            array('name' => 'Count', 'width' => 45),
            array('name' => 'Description', 'width' => 355),
            array('name' => 'Data Type', 'width' => 75),
            array('name' => 'List Type', 'width' => 75),
            array('name' => 'Owner', 'width' => 75),
            array('name' => 'Created', 'width' => 60),
            array('name' => 'Modified', 'width' => 60),
        );
   
        parent::__construct("lists:ListsDataGrid", $parameters, $misc);
    }
    
    /**
     * Returns the sql statment for the pager.
     *
     * @return array clients data
     */
    public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
    {   
        $sql = sprintf(
            "SELECT SQL_CALC_FOUND_ROWS %s
                saved_list_id as savedListID,
                description as description,
                data_item_type as dataItemType,
                is_dynamic as isDynamic,
                datagrid_instance as datagridInstance,
                parameters as parameters,
                created_by as createdBy,
            %s
            FROM
                saved_list
            LEFT JOIN user
                ON user.user_id = saved_list.created_by
            %s
            WHERE
                saved_list.site_id = %s
            %s
            GROUP BY saved_list.saved_list_id
            %s
            %s
            %s",
            $distinct,
            $selectSQL,
            $joinSQL,
            $_SESSION['CATS']->getSiteID(),
            (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
            (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : '',
            $orderSQL,
            $limitSQL
        );

        return $sql;
    }

    /**
     * Adds more options to the action area on the pager.  Overloads 
     * DataGrid Inner Action Area function.
     *
     * @return html innerActionArea commands.
     */    
    public function getInnerActionArea()
    {
        $html = parent::getInnerActionArea();
        
        $newParameterArray = $this->_parameters;
        
        $newParameterArray['exportIDs'] = '<dynamic>';

        $html .= sprintf(
            '<a href="javascript:void(0);" onclick="window.location.href=\'%s?m=export&amp;a=exportByDataGrid&amp;i=%s&amp;p=%s&amp;&dynamicArgument%s=\' + urlEncode(serializeArray(exportArray%s));">Export Selected</a><br />',
            CATSUtility::getIndexName(),
            urlencode($this->_instanceName),
            urlencode(serialize($newParameterArray)),
            urlencode($this->_instanceName),
            md5($this->_instanceName)
        );
         
        //$html .= sprintf(
        //            '<a href="">Delete Selected</a><br />'
        //         );       
        
        return $html;
    }
}

?>