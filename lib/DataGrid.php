<?php
/**
 * CATS
 * Data Grid Library
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
 * @version    $Id: DataGrid.php 3829 2007-12-11 21:17:46Z brian $
 */

include_once('./lib/StringUtility.php');
include_once('./lib/Width.php');


/**
 *  Data Grid Library
 *  @package    CATS
 *  @subpackage Library
 */
class DataGrid
{
    /* DataGrid gets extended by classes which must be kept in modules/modulename/dataGrids.php.  Each
     * datagrid class has an instance name which must be equal to modulename:classname.  So, for example,
     * a listByView class in modules/candidates/dataGrids.php which extends DataGrid would have the instance
     * name candidates:candidatesListByView.
     *
     * A datagrid must be configured in its child constructor.  Specifically, each one of these
     * class variables must be set:
     *
     *   $this->_tableWidth;                              - Table width (Width object).
     *   $this->_defaultAlphabeticalSortBy = 'lastName';  - Default SQL column that the table is sorted by.
     *                                                      This MUST match a sortableColumn property of a
     *                                                      non optional class column.
     *   $this->ajaxMode = false;                         - Set to true to make the pager ajax enabled.
     *   $this->showExportCheckboxes = true;              - Set to true to show checkboxes in the first column
     *                                                      for mass actions.  SQL MUST return column exportID
     *                                                      in order for the checkbox to appear.
     *   $this->showActionArea = true;                    - Set to true to show the action option.  Under action
     *                                                      by default is Export Current Page and Export All Pages.
     *   $this->showChooseColumnsBox = true;              - Set to true to show the choose columns box in
     *                                                      the upper left of the datagrid.
     *   $this->allowResizing = true;                     - Set to true to allow the user to reorder and
     *                                                      resize the datagrid.
     *   $this->_defaultColumns = array()                 - Defines the default layout of the columns.  The
     *                                                      the columns wont change if showChooseColumnsBox and
     *                                                      allowResizing are set to false.  Read below for
     *                                                      details on the values.
     *   $this->_classColumns = array()                   - Defines all possible columns this datagrid can use.
     *                                                      Read below for details on the values.
     *   $this->_dataItemIDColumn = array()               - SQL column used in WHERE clause for retriving exportID.
     *   $this->globalStyle = ''                          - Optional style modifier to all HTML elements on the grid.
     *   $this->showExportColumn = true                   - If set to false, does not render the leftmost column.
     *
     * In addition, your class should define the functions:
     *
     * public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
     *      - This function describes how to query the database to get the result set to display on the table.
     *        datagrid will provide all of the appropriate strings to optiomally retrieve what data should
     *        be retrieved to render the page - the primary purpose of the function is to define the table
     *        and any important constraints (such as site id) that aren't mentioned elsewhere.  Make sure the
     *        option SQL_CALC_FOUND_ROWS is specified immediantly after the SELECT keyword.  Here is an
     *        example framework for a candidates getSQL function:
     *
     *       public function getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL, $distinct = '')
     *       {
     *          $sql = sprintf(
     *              "SELECT SQL_CALC_FOUND_ROWS %s
     *                  candidate.candidate_id AS candidateID,
     *                  candidate.candidate_id AS exportID,
     *                  candidate.is_hot AS isHot,
     *                  candidate.date_modified AS dateModifiedSort,
     *              %s
     *              FROM
     *                  candidate
     *              %s
     *              WHERE
     *                  candidate.site_id = %s
     *              %s
     *              %s
     *              GROUP BY candidate.candidate_id
     *              %s
     *              %s
     *              %s",
     *              $distinct,
     *              $selectSQL,
     *              $joinSQL,
     *              $this->_siteID,
     *              (strlen($whereSQL) > 0) ? ' AND ' . $whereSQL : '',
     *              $this->_assignedCriterion,
     *              (strlen($havingSQL) > 0) ? ' HAVING ' . $havingSQL : '',
     *              $orderSQL,
     *              $limitSQL
     *          );
     *
     *          return $sql;
     *      }
     *
     *
     * public function getInnerActionArea()
     *      - This function returns HTML containing the options to put in the export menu.  Call
     *        parent::getInnerActionArea() to access the parents built in actions (export page).
     *        Example function:
     *
     *    public function getInnerActionArea()
     *      {
     *          $html = parent::getInnerActionArea();
     *
     *          // More options go here.
     *
     *          return $html;
     *      }
     *
     * $this->_defaultColumns layout:
     *
     *      array(array('name' => 'column title', 'width' = column width),
     *           array('name' => 'column title', 'width' = column width),
     *            ....,
     *      );
     *
     *      Where column title is the name of the column presented to the user (for example "First Name"),
     *      and column width is the width of the column in pixels.
     *
     * $this->_classColumns  layout:
     *
     *      array('column title' => array('setting' => 'value',
     *                                    'setting' => 'value',
     *                                    ...
     *                              ),
     *            'column title' => array('setting' => 'value',
     *                                    'setting' => 'value',
     *                                    ...
     *                              ),
     *            ...
     *      );
     *
     *      Where column title is the name of the column presented to the user (for example "First Name"),
     *      and setting => value is pairs of properties for each column.  These properties could be:
     *
     *      'select'           - The select part of an SQL query which is necessary to retrieve this column.
     *                           Any indentical select queries will only be included once.  Example:
     *                           'candidate.email1 AS email1'
     *      'join'             - The join part of an SQL query which is necessary to retrieve this column.
     *                           Any indentical join queries will only be included once.  Example:
     *                           'LEFT JOIN user AS owner_user ON candidate.owner = owner_user.user_id'
     *      'sortableColumn'   - A column in the SQL result set which can be sorted.  If this is set
     *                           and pagerRender is not set, then this SQL column also contains the data
     *                           that will be displayed in the cell.
     *      'pagerRender'      - Evaluated code that returns the raw HTML to display this column.  If
     *                           property sortableColumn is set, this is unnecessary.  Example:
     *                           'return \'<a href="\'.htmlspecialchars($rsData[\'webSite\']).\'">\'.htmlspecialchars($rsData[\'webSite\']).\'</a>\';'
     *      'exportRender'     - Evaluated code that returns the raw text to export this column.  If
     *                           property sortableColumn is set, this is unnecessary.  Example:
     *                           'return $rsData[\'ownerFirstName\'] . " " .$rsData[\'ownerLastName\'];'
     *      'exportable'       - If set to false, this column will not be exported.  Defaults to true.
     *      'pagerWidth'       - Integer. Default size of the column if the column is added from the users
     *                           Column Selector box.
     *      'alphaNavigation'  - If set to true, this column can be filtered using the A-Z navigation bar.
     *                           If false, pressing the A-Z list sorts Defaults to sorting _defaultAlphabeticalSortBy.
     *                           Defaults to false.
     *      'sizable'          - If set to false, the column can not be resized by the user.  Defaults to true.
     *      'pagerNoTitle'     - If set to true, the pager will not display the columns title in the header.
     *                           Defaults to false.
     *      'pagerOptional'    - If set to false, the pager will not allow you to remove this column.  The
     *                           defaultSortBy column should always be set to false.  Defaults to true.
     *      'filter'           - Column to evaluate filters against.  This uses the having clause.  Ex:  candidate.candidate_id
     *      'filterHaving'     - Column to evaluate filters againts.  This uses the where clause.  Ex: candidateID
     *      'filterable'       - If set to false, will not show up in the DHTML filter area.  Defaults to true.
     *      'filterInList'     - Uses a %s IN (['filter']) clause rather than ['filter'] = %s clause.
     *      'columnHeaderText' - If set, the column header will display this text rather than the column name. Ex:  'P' instead of 'Placed'.
     *      'exportColumnHeaderText' - If set, the column header will display this text rather than the column name
     *                           during CSV export.  Ex: 'id' instead of 'ID' corrects http://support.microsoft.com/kb/215591 .
     *      'where'            - Arbritraty WHERE constraint to add upon the inclusion of this column.
     *      'having'           - Arbritraty HAVING constraint to add upon the inclusion of this column.
     *
     *    To get a pager object, call $pager->get(instance name, parameters) where parameters are the pagers
     *    current parameters.  If you are calling the pager for the first time, you must set some parameters.
     *    These are an array of setting => value pairs which are:
     *
     *    'defaultSortBy'      - Set to the SQL column which is the default column to sort by.  For example,
     *                           setting to 'lastName' will show the pager sorted by lastName when viewed for
     *                           the first time.
     *    'sortDirection'      - Default direction to sort the pager.  Could be 'ASC' or 'DESC'.
     *    'rangeStart'         - Integer of the number of the first row to display from the result set.  For example,
     *                           setting to 10 will show the tenth row returned from SQL in the first displayed row.
     *    'maxResults'         - Integer of the maximum number of entries per page.  Usually 15.
     *    'noSaveParameters'   - If set, the data grid will not store the filter you applied to the datagrid for the session.
     *                           Use this whenever you are selecting individual row elements from a DG for mass action.
     *
     *    So, to get a candidatesListByViewPager datagrid, you might:
     *
     *           $dataGridProperties = array('defaultSortBy' => 'lastName',
     *                              'sortDirection' => 'ASC',
     *                              'rangeStart'    => 0,
     *                              'maxResults'    => 15);
     *
     *    $dataGrid = DataGrid::get("candidates:candidatesListByViewDataGrid", $dataGridProperties);
     *
     *    To draw a datagrid, call $dataGrid->draw() on the template.  To draw navigation controls,
     *    call $dataGrid->printNavigation() on the template.  Call $dataGrid->printNavigation(true)
     *    to draw navigation controls with an A-Z alpha navigation list.  Call
     *    echo($dataGrid->getCurrentPageHTML()) to get the current page number.
     *
     */

     private $_rs;

     protected $_parameters;
     protected $_instanceName;
     protected $_currentColumns;
     protected $_defaultColumns;

    /**
     * Static function returns an object to the DataGrid that is indicated by $indentifier
     * and has the specified parameters.
     *
     * @param string dataGrid indentifier.
     * @param array dataGrid parameters.
     * @param integer dataGrid miscalaneous ID
     * @return object data grid
     */
    public static function get($indentifier, $parameters, $misc = 0)
    {
        /* This deals with loading a datagrid that was selected by use of the action / export box. */
        if (isset($_REQUEST['dynamicArgument' . md5($indentifier)]))
        {
            foreach ($parameters as $index => $data)
            {
                if ($data !== '<dynamic>')
                {
                    continue;
                }

                $parameters[$index] = $_REQUEST['dynamicArgument' . md5($indentifier)];
                
                if ($index = 'exportIDs')
                {
                   $parameters['exportIDs'] = unserialize(urldecode($parameters['exportIDs']));
                }
            }
        }
        
        /* Split function parameter into module name and function name. */
        $indentifierParts = explode(':', $indentifier, 3);

        $module = preg_replace("[^A-Za-z0-9]", "", $indentifierParts[0]);
        $class = preg_replace("[^A-Za-z0-9]", "", $indentifierParts[1]);

        if (isset($indentifierParts[2]))
        {
            $misc = unserialize($indentifierParts[2]);
        }

        if (!file_exists(sprintf('modules/%s/dataGrids.php', $module)))
        {
            trigger_error('No datagrid named: '.$indentifier);
        }

        include_once (sprintf('modules/%s/dataGrids.php', $module));

        $dg = new $class($_SESSION['CATS']->getSiteID(), $parameters, $misc);

        return $dg;
    }
    
    /**
     * Static function returns an object to the DataGrid that is indicated by the request
     * variables i and p.
     *
     * @return object data grid
     */
    public static function getFromRequest()
    {
        if (!isset($_REQUEST['i']) || !isset($_REQUEST['p']))
        {
            trigger_error('getFromRequest datagrid failed : no request variables i or p set.');
        }
        
        $indentifier = $_REQUEST['i'];
        $parameters = unserialize($_REQUEST['p']);

        return self::get($indentifier, $parameters);
    }
    
    public function getInstanceName(){
    	return $this->_instanceName;
    }

    /**
     * Static function retrieves the most recent parameter array for a datagrid from the database.
     *
     * @param string dataGrid indentifier.
     * @param variant misc value
     * @return array paramater array, or empty array if none set.
     */
    public static function getRecentParamaters($indentifier, $misc = 0)
    {
        if ($misc != 0)
        {
            $indentifier .= ':' . serialize($misc);
        }
        
        return $_SESSION['CATS']->getDataGridParameters($indentifier);
    }
    
    // TODO:  Document me.
    protected function getParamater($paramater)
    {
        if (isset($this->_parameters[$paramater]))
        {
            return $this->_parameters[$paramater];
        }
        
        return '';
    }
    
    /**
     * A datagrid which is called with a serialized parameter (such as an
     * integer to specify the saved list ID) follows the instance naming format:
     * moduleName:dataGridName:serializedMiscArgument.  This returns the
     * unrealized misc argument.
     *
     * RETURNED VALUE MUST BE VALIDATED!
     *
     * @return variant the misc argument on the instance name
     */
    public function getMiscArgument()
    {
        /* Split function parameter into module name and function name. */
        $instanceParts = explode(':', $this->_instanceName, 3);

        if (isset($instanceParts[2]))
        {
            return unserialize($instanceParts[2]);
        }
        else
        {
            return 0;
        }
    }

    /**
     * Creates and configures the datagrid based off of the supplied parameters.
     * The supplied parameters could be sent by a browser, so they need to be validated before they
     * are used for any important features.
     *
     * @return void
     */
     public function __construct($instanceName, $parameters, $misc = 0)
     {
         $this->_rs = false;

         $this->_instanceName = $instanceName;
         
         if ($misc != 0)
         {
            $this->_instanceName .= ':'.serialize($misc);
         }

         /* Allow _GET to override the supplied parameters array */
         if (isset($_GET['parameters' . $this->_instanceName]))
         {
             $this->_parameters = unserialize($_GET['parameters' . $this->_instanceName]);
         }
         else
         {
             $this->_parameters = $parameters;
         }
         
         /* Allow _GET['dynamicArgument'.instance] to override <dynamic> */
         if (isset($_GET['dynamicArgument' . $this->_instanceName]))
         {
            foreach ($this->_parameters as $index => $data)
            {
                if ($data === '<dynamic>')
                {
                    $this->_parameters[$index] = $_REQUEST['dynamicArgument' . $this->_instanceName];
                }
            }
         }

         /* ------ VALIDATION PART 1 ----- */
         //DefaultSortBy - should be set, should equal a sortable column.  If it doesn't, fatal.
         if (!isset($this->defaultSortBy))
         {
             die ('defaultSortBy not set.');
         }

         $found = false;
         foreach ($this->_classColumns as $index => $data)
         {
             if (isset($data['sortableColumn']) && $data['sortableColumn'] == $this->defaultSortBy)
             {
                 $found = true;
             }
         }
         if (!$found)
         {
             die ('Parameter defaultSortBy is not a valid sortable column.');
         }

         //sortBy - If not set, set to defaultSortBy.  Should equal a sortable column.  If it doesn't, fatal.
         if (!isset($this->_parameters['sortBy']))
         {
             $this->_parameters['sortBy'] = $this->defaultSortBy;
             $this->_parameters['sortDirection'] = $this->defaultSortDirection;
         }

         $found = false;
         foreach ($this->_classColumns as $index => $data)
         {
             if (isset($data['sortableColumn']) && $data['sortableColumn'] == $this->_parameters['sortBy'])
             {
                $found = true;
             }
         }
         if (!$found)
         {
             die ('Parameter sortBy is not a valid sortable column.');
         }

         //rangeStart - should be an integer or a character between A and Z.  If not set, set to 0.
         if (!isset($this->_parameters['rangeStart']))
         {
             $this->_parameters['rangeStart'] = 0;
         }
         else
         {
             $this->_parameters['rangeStart'] = (int) $this->_parameters['rangeStart'] * 1;
         }

         //maxResults - Should be an integer.  If not set, set to 15.
         if (!isset($this->_parameters['maxResults']))
         {
             $this->_parameters['maxResults'] = 15;
         }
         else
         {
             $this->_parameters['maxResults'] = (int) $this->_parameters['maxResults'] * 1;
             if ($this->_parameters['maxResults'] == 0)
             {
                 $this->_parameters['maxResults'] = 15;
             }
         }

         // If clicked on the alphabet pager, filterAlpha is set.  Make sure it is valid.
         if (isset($this->_parameters['filterAlpha']))
         {
             if (ord($this->_parameters['filterAlpha']) < ord('A') ||
                 ord($this->_parameters['filterAlpha']) > ord('Z') ||
              strlen($this->_parameters['filterAlpha']) != 1)
             {
                unset ($this->_parameters['filterAlpha']);
            }
         }

         //If exportIDs is set, make sure it is an array and each array value is an integer.
         if (isset($this->_parameters['exportIDs']))
         {
             if (!isset($this->_dataItemIDColumn))
             {
                 die ('$this->_dataItemIDColumn is not set (required for parameter exportIDs');
             }

             if (!is_array($this->_parameters['exportIDs']))
             {
                 unset ($this->_parameters['exportIDs']);
             }
             else
             {
                 foreach($this->_parameters['exportIDs'] as $index => $data)
                 {
                     $this->_parameters['exportIDs'][$index] = (int) $data;
                 }
             }
         }

         //If filterVisible is set it must be boolean.
         //if (isset($this->_parameters['filterVisible']))
         //{
         //    $this->_parameters['filterVisible'] = (boolean) $this->_parameters['filterVisible'];
         //}

         /* Set some properties and get column preferences. */
         $this->buildColumns();

         //If a column is being sorted, it MUST be visible.
         $sortByVisible = false;
         foreach ($this->_currentColumns as $index => $data)
         {
             if (isset($data['data']['sortableColumn']) && $data['data']['sortableColumn'] == $this->_parameters['sortBy'])
             {
                 $sortByVisible = true;
             }
         }

         if (!$sortByVisible)
         {
             $this->_parameters['sortBy'] = $this->defaultSortBy;

             if (isset($this->_parameters['filterAlpha']))
             {
                 unset ($this->_parameters['filterAlpha']);
             }
         }

         /* ---------- GET DATA (also populates total entries) -------- */
         $this->_getData();
         
         /* If current page < 1 or current page > total pages, move around current page and get data again. */
         /* Set properties */
         $this->_currentPage = $this->getCurrentPage();
         $this->_totalPages = floor($this->_totalEntries / $this->_parameters['maxResults']) + ($this->_totalEntries % $this->_parameters['maxResults'] == 0 ? 0 : 1);
         
         if ($this->_currentPage < 1)
         {
             $this->_currentPage = 1;
         }
         
         if ($this->_currentPage > $this->_totalPages)
         {
             $this->_currentPage = $this->_totalPages;
         }

         if ($this->_currentPage != $this->getCurrentPage())
         {
             $this->_parameters['rangeStart'] = ($this->_currentPage - 1) * $this->_parameters['maxResults'];
             
             $this->_rs = false;
             $this->_getData();
             
             /* Reset properties */
             $this->_currentPage = $this->getCurrentPage();
             $this->_totalPages = floor($this->_totalEntries / $this->_parameters['maxResults']) + ($this->_totalEntries % $this->_parameters['maxResults'] == 0 ? 0 : 1);
         }


         /* Save current parameter array to session. */
         if (!isset($this->_parameters['noSaveParameters']))
         {
             $_SESSION['CATS']->setDataGridParameters($this->_instanceName, $this->_parameters);
         }
         
         /* If no globalStyle set, set one. */
         if (!isset($this->globalStyle))
         {
             $this->globalStyle = '';
         }
     }

    /**
     * Retruns the current page number.
     *
     * @return integer page number
     */
    public function getCurrentPage()
    {
        return (int)($this->_parameters['rangeStart'] / $this->_parameters['maxResults']) + 1;
    }

    /**
     * Returns JS to remove a column from the filter.  Intended for being accessed by the template.
     *
     * @param string column name
     * @return string javascript
     */
    public function getJSRemoveFilter($columnName)
    {
        return 'removeColumnFromFilter(\'filterArea'.md5($this->_instanceName).'\', urlDecode(\''.urlencode($columnName).'\')); submitFilter'. md5($this->_instanceName) .'();';
    }

    /**
     * Returns JS to remove a column from the filter.  Intended for being accessed by the template.
     *
     * @param string column name
     * @param string operator ((==), (=~), etc)
     * @param string javascript object or string ('1', or this.value, etc)
     * @param string submitFilter argument. '' = filter visible, 'false' = filter invisible, 'true' = retain previous setting
     * @return string javascript
     */
    public function getJSAddFilter($columnName, $operator, $value, $submitFilterArgument = '')
    {
        return 'addColumnToFilter(\'filterArea'.md5($this->_instanceName).'\', urlDecode(\''.urlencode($columnName).'\'), \''.$operator.'\', '.$value.'); submitFilter'. md5($this->_instanceName) .'('.$submitFilterArgument.');';
    }

    /**
     * Returns JS to add or remove a column from the filter based on if this.checked is set.
     *
     * @param string column name
     * @param string operator ((==), (=~), etc)
     * @param javascript object or string ('1', or this.value, etc)
     * @return string javascript
     */
    public function getJSAddRemoveFilterFromCheckbox($columnName, $operator, $value)
    {
        return 'if (this.checked) { '.
                   'addColumnToFilter(\'filterArea'.md5($this->_instanceName).'\', urlDecode(\''.urlencode($columnName).'\'), \''.$operator.'\', '.$value.'); '.
                   'submitFilter'. md5($this->_instanceName) .'(true); '.
                '} '.
                'else '.
                '{ '.
                    'removeColumnFromFilter(\'filterArea'.md5($this->_instanceName).'\', urlDecode(\''.urlencode($columnName).'\')); '.
                    'submitFilter'. md5($this->_instanceName) .'(true);'.
                '}';
    }

    /**
     * Returns JS to apply filter immediantly.
     *
     * @param string column name
     * @param string operator ((==), (=~), etc)
     * @param javascript object or string ('1', or this.value, etc)
     * @return string javascript
     */
    public function getJSApplyFilter()
    {
        return 'submitFilter'. md5($this->_instanceName) .'();';
    }

    /**
     * Returns the current value of a filter column (or empty string if no filter is set)
     *
     * @param string column name
     * @return string filter value
     */
    public function getFilterValue($columnName)
    {
        if (isset($this->_parameters['filter']))
        {
            $filterStrings = explode(',', $this->_parameters['filter']);

            foreach ($filterStrings as $index => $data)
            {
                if (strpos($data, '=') === false)
                {
                    continue;
                }

                $dataColumnName = urldecode(substr($data, 0, strpos($data, '=')));

                if ($columnName == $dataColumnName)
                {
                    return urldecode(substr($data, strpos($data, '=') + 2));
                }
            }
        }
        return '';
    }

    /**
     * Returns the current operator of a filter column (or empty string if no filter is set)
     *
     * @param string column name
     * @return string filter opertaor
     */
    public function getFilterOperator($columnName)
    {
        if (isset($this->_parameters['filter']))
        {
            $filterStrings = explode(',', $this->_parameters['filter']);

            foreach ($filterStrings as $index => $data)
            {
                if (strpos($data, '=') === false)
                {
                    continue;
                }

                $dataColumnName = urldecode(substr($data, 0, strpos($data, '=')));

                if ($columnName == $dataColumnName)
                {
                    return urldecode(substr($data, strpos($data, '='), 2));
                }
            }
        }
        return '';
    }

    /**
     * Prints out a dropdown letting the user pick rows per page.
     * ONLY WORKS WITH GETBACK. (Not Ajax)
     *
     * @return string html
     */
    public function drawRowsPerPageSelector()
    {
        echo '<a href="javascript:void(0);" class="button" style="text-decoration: none;" onclick="'.
                  'var rowsPerPageSelector = document.getElementById(\'rowsPerPageSelectorFrame', md5($this->_instanceName), '\'); '.
                  'if(rowsPerPageSelector.style.display==\'none\') { '.
                    'rowsPerPageSelector.style.display=\'\'; '.
                    'rowsPerPageSelector.style.left = (docjslib_getRealLeft(this) - 20) + \'px\'; '.
                    'rowsPerPageSelector.style.top = (docjslib_getRealTop(this) + 17) + \'px\'; '.
                  '} else '.
                    'rowsPerPageSelector.style.display=\'none\'; '.
             '">Rows Per Page</a>';

        echo '<span style="position: absolute; text-align:left; display:none;" id="rowsPerPageSelectorFrame', md5($this->_instanceName), '">';
        $this->_getData();

        $md5InstanceName = md5($this->_instanceName);

        $newParameterArray = $this->_parameters;
        $newParameterArray['rangeStart'] = 0;
        $newParameterArray['maxResults'] = '<dynamic>';

        $requestString = $this->_getUnrelatedRequestString();
        $requestString .= '&' . urlencode('parameters' . $this->_instanceName) . '=' . urlencode(serialize($newParameterArray));

        echo sprintf(
            '<select id="rowsPerPageSelector%s" onchange="document.location.href=\'%s?%s&dynamicArgument%s=\' + this.value;" class="selectBox">%s',
            $md5InstanceName,      //Select Box ID
            CATSUtility::getIndexName(),
            $requestString,
            urlencode($this->_instanceName),
            "\n"
        );

        foreach (array('15', '30', '50', '100') as $maxResults)
        {
            if ($this->_parameters['maxResults'] == $maxResults)
            {
                echo sprintf(
                    '<option selected="selected" value="%s">%s entries per page</option>',
                    $maxResults, $maxResults
                );
            }
            else
            {
                echo sprintf(
                    '<option value="%s">%s entries per page</option>',
                    $maxResults, $maxResults
                );

            }
        }

        echo '</select>&nbsp;';

        echo '</span>';
    }

    /**
     * Prints out an image to hide or show the filter area.
     *
     * @return string html
     */
    public function drawShowFilterControl()
    {
        echo '<a href="javascript:void(0);" class="button" style="text-decoration: none;" onclick="var filterArea = document.getElementById(\'filterResultsArea', md5($this->_instanceName), '\'); if(filterArea.style.display==\'none\') {filterArea.style.display=\'\'; if (newFilterCounter', md5($this->_instanceName),' == 0){ showNewFilter', md5($this->_instanceName), '();}}else filterArea.style.display=\'none\';">Filter</a>';
    }

    /**
     * Prints out the area to modify the applied filters on to the page.  Omitting this
     * will not affect the functionality of the page.  Is intended to be called by the
     * template.
     *
     * @return string html
     */
    public function drawFilterArea()
    {
        $md5InstanceName = md5($this->_instanceName);

        $filterableColumns = array_keys($this->_classColumns);

        if (isset($this->_parameters['filter']))
        {
            $currentFilterString = $this->_parameters['filter'];
        }
        else
        {
            $currentFilterString = '';
        }

        $filtersApplied = false;
        foreach ($this->_classColumns as $index => $data)
        {
            if (!$filtersApplied && $this->getFilterValue($index))
            {
                $filtersApplied = true;
            }
        }

        echo '<fieldset class="filterAreaFieldSet" id="filterResultsArea', $md5InstanceName, '" ';
        if (!$filtersApplied || (isset($this->_parameters['filterVisible']) && $this->_parameters['filterVisible'] == false))
        {
            echo 'style="display:none;"';
        }
        echo '><legend class="filterAreaLegend">Filter</legend>';

        echo '<table style="border-collapse: collapse;"><tr><td width="100%" style="vertical-align:top;" id="filterResultsAreaTable', $md5InstanceName, '">';

        $counterFilters = 0;

        foreach ($this->_classColumns as $index => $data)
        {
            $filterValue = $this->getFilterValue($index);

            if ($filterValue != '')
            {
                $counterFilters++;

                /* You can not apply another filter to a column already being filtered. */
                if (array_search($index, $filterableColumns) !== false)
                {
                    unset ($filterableColumns[array_search($index, $filterableColumns)]);
                }

                $filterOperator = $this->getFilterOperator($index);
                $filterOperatorHuman = '';
                switch ($filterOperator)
                {
                    case '==':
                        $filterOperatorHuman = ' is equal to';
                        break;

                    case '=~':
                        $filterOperatorHuman = ' contains';
                        break;

                    case '=>':
                        $filterOperatorHuman = ' is greater than';
                        break;

                    case '=<':
                        $filterOperatorHuman = ' is less than';
                        break;

                    case '=#':
                        $filterOperatorHuman = ' has element';
                        break;
                }

                echo '<span class="filterArea">';
                echo '<a href="javascript:void(0);" onclick="this.parentNode.style.display=\'none\'; ', $this->getJSRemoveFilter($index), '">';
                echo '<img src="images/actions/delete_small.gif" style="padding:0px; margin:0px;" border="0" alt="" title="Remove this Filter" />';
                echo '</a>&nbsp;';

                if (!isset($data['filterDescription']))
                {
                    echo '\'', $index, '\'', $filterOperatorHuman,': ';
                    echo '<input class="inputbox" style="width:180px;" value="', htmlspecialchars($filterValue), '" onChange="addColumnToFilter(\'filterArea', $md5InstanceName, '\', urlDecode(\'', urlencode($index), '\'), \'', $filterOperator, '\', this.value);" />';
                }
                else
                {
                    echo ($data['filterDescription']);
                }
                echo '</span>';
            }
        }

        /* Remove columns we can not apply a filter to, and set what kind of filters can be applied to each column. */
        foreach ($filterableColumns as $index => $value)
        {
            /* Remove column? */
            if (isset($this->_classColumns[$value]['filterable']) && $this->_classColumns[$value]['filterable'] == false)
            {
                unset ($filterableColumns[$index]);
            }
            /* Set filter types */
            else
            {
                if (isset($this->_classColumns[$value]['filterTypes']))
                {
                    $filterableColumns[$index] .= '!@!' . $this->_classColumns[$value]['filterTypes'];
                }
                else
                {
                    $filterableColumns[$index] .= '!@!' . '===~';
                }
            }
        }
        $template = new Template();
        $template->assign('md5InstanceName', $md5InstanceName);
        $template->assign('arrayKeysString', json_encode(array_values($filterableColumns)));
        $template->assign('counterFilters', $counterFilters);
        $template->display('./lib/datagrid/FilterArea.tpl');
    }

    /**
     * Gets the current layout for this datagrid's columns.  If no layout is defined, uses default layout.
     * Also handles add/remove/reset columns.
     *
     * @return void
     */
    protected function buildColumns()
    {
        /* Get the current preferences from SESSION. */
        if (!isset($this->ignoreSavedColumnLayouts) || $this->ignoreSavedColumnLayouts == false)
        {
            $this->_currentColumns = $_SESSION['CATS']->getColumnPreferences($this->_instanceName);   
        }
        else
        {
            $this->_currentColumns = array();
        }

        /* Do we need to reset the columns?  This has to be first. */
        if ($this->_currentColumns == array() || (isset($this->_parameters['resetColumns']) && $this->_parameters['resetColumns'] == true))
        {
            $this->_currentColumns = $this->_defaultColumns;
            $this->saveColumns();

            if (isset($this->_parameters['resetColumns']))
            {
                unset ($this->_parameters['resetColumns']);
            }
        }

        /* Do we need to remove a column? */
        if (isset($this->_parameters['removeColumn']))
        {
            foreach ($this->_classColumns as $index => $data)
            {
                if ($index == $this->_parameters['removeColumn'])
                {
                    foreach ($this->_currentColumns as $index2 => $data2)
                    {
                        if ($data2['name'] == $index)
                        {
                            unset ($this->_currentColumns[$index2]);
                        }
                    }
                }
            }
            $this->saveColumns();
            unset ($this->_parameters['removeColumn']);
        }

        /* Do we need to add a column? */
        if (isset($this->_parameters['addColumn']))
        {
            /* Make sure the column isn't already added. */
            foreach ($this->_currentColumns as $index => $data)
            {
                if ($data['name'] == $this->_parameters['addColumn'])
                {
                    unset($this->_currentColumns[$index]);
                }
            }

            foreach ($this->_classColumns as $index => $data)
            {
                if ($index == $this->_parameters['addColumn'])
                {
                    $this->_currentColumns[] = array('name' => $index, 'width' => $data['pagerWidth']);
                }
            }
            $this->saveColumns();
            unset ($this->_parameters['addColumn']);
        }

        /* Do we need to reorder the columns?. */
        if (isset($this->_parameters['reorderColumns']))
        {
           $reorderColumns = explode(',', $this->_parameters['reorderColumns']);

            /* Parse input */
            $reorderColumns[0] = (int) substr(urldecode($reorderColumns[0]), 4 + strlen(md5($this->_instanceName)));

            if (urldecode($reorderColumns[1]) !== 'moveToEnd')
            {
                $reorderColumns[1] = (int) substr(urldecode($reorderColumns[1]), 4 + strlen(md5($this->_instanceName)));
            }

            /* Sort the array so indexes match. */
            ksort($this->_currentColumns, SORT_NUMERIC);

            /* Remove source column from list. */
            $columnMoving = $this->_currentColumns[$reorderColumns[0]];
            unset($this->_currentColumns[$reorderColumns[0]]);
            ksort($this->_currentColumns, SORT_NUMERIC);

            /* Move to end? */
            if (urldecode($reorderColumns[1]) === 'moveToEnd')
            {
                $reorderColumns[1] = sizeof($this->_currentColumns);
            }

            /* Insert column back into list. */
            array_splice($this->_currentColumns, $reorderColumns[1], 0, array($columnMoving));

            /* Write changes to database. */
            $this->saveColumns();

            unset ($this->_parameters['reorderColumns']);
        }

        /* Make sure each column we are getting data for is a valid coulumn.  Also set the 'data' property for each column. */
        foreach ($this->_currentColumns as $index => $data)
        {
            if (isset($this->_classColumns[$data['name']]))
            {
                $this->_currentColumns[$index]['data'] = $this->_classColumns[$data['name']];
            }
            else
            {
                unset($this->_currentColumns[$index]);
            }
        }
    }

    /**
     * Saves the current column layout to session (and ultimatly to database through session).
     *
     * @return void
     */
    protected function saveColumns()
    {
        $_SESSION['CATS']->setColumnPreferences($this->_instanceName, $this->_currentColumns);
    }

    /**
     * Populates $this->_rs with data based on the current dataGrid settings if $this->_rs is
     * not already populated.
     *
     * @return void
     */
    private function _getData()
    {
        if ($this->_rs !== false)
        {
            return;
        }

        //getColumn is set to the only column we want to populate if it is set.

        $db = DatabaseConnection::getInstance();

        // Using MD5 hashing to detect duplicates.
        $selectSQL = array();
        $joinSQL = array();
        $whereSQL = array();
        $havingSQL = array();

        /* Get SELECT and JOIN paramaters for each column we want to collect data on. */
        foreach ($this->_currentColumns as $index => $data)
        {
            if (isset($data['data']['select']) && !empty($data['data']['select']))
            {
                $selectSQL[md5($data['data']['select'])] = $data['data']['select'];
            }

            if (isset($data['data']['join']) && !empty($data['data']['join']))
            {
                $joinSQL[md5($data['data']['join'])] = $data['data']['join'];
            }
        }

        /* Build filter logic. */
        if (isset($this->_parameters['filter']))
        {
            $filterStrings = explode(',', $this->_parameters['filter']);
            $columnName = '';

            foreach ($filterStrings as $index => $data)
            {
                if (strpos($data, '=') === false)
                {
                    continue;
                }

                $columnName = urldecode(substr($data, 0, strpos($data, '=')));
                $argument = urldecode(substr($data, strpos($data, '=') + 2));

                /* Is this a valid column? */
                if (!isset($this->_classColumns[$columnName]))
                {
                    continue;
                }

                if ($argument == '')
                {
                    continue;
                }

                /* Add select and join columns for this column. */
                $this->_classColumns[$columnName];
                if (isset($this->_classColumns[$columnName]['select']) && !empty($this->_classColumns[$columnName]['select']))
                {
                    $selectSQL[md5($this->_classColumns[$columnName]['select'])] = $this->_classColumns[$columnName]['select'];
                }

                if (isset($this->_classColumns[$columnName]['join']) && !empty($this->_classColumns[$columnName]['join']))
                {
                    $joinSQL[md5($this->_classColumns[$columnName]['join'])] = $this->_classColumns[$columnName]['join'];
                }

                /* The / character works as an OR clause for filters, exclude url and web_site */
                if((strpos($this->_classColumns[$columnName]['filter'], 'web_site') !== false) ||
                   (strpos($this->_classColumns[$columnName]['filter'], 'url') !== false))
                {
                    $arguments = array($argument);
                }
                else
                {
                    $argument = str_replace(' or ', '/', $argument);
                    $argument = str_replace(' OR ', '/', $argument);
                    $arguments = explode('/', $argument);
                }

                $whereSQL_or = array();
                $havingSQL_or = array();
                foreach ($arguments as $argument)
                {
                    $argument = trim($argument);

                    /* Is equal to (==) */
                    if (strpos($data, '==') !== false)
                    {
                        if (isset($this->_classColumns[$columnName]['filterInList']) && $this->_classColumns[$columnName]['filterInList'] == true)
                        {
                            if (isset($this->_classColumns[$columnName]['filter']))
                            {
                                $whereSQL_or[] =   $db->makeQueryString($argument) . ' IN (' .$this->_classColumns[$columnName]['filter'] . ')';
                            }

                            if (isset($this->_classColumns[$columnName]['filterHaving']))
                            {
                                $havingSQL_or[] = $this->_classColumns[$columnName]['filterHaving'] . ' = ' . $db->makeQueryString($argument) . ' ';
                            }
                        }
                        else
                        {                            
                            if (isset($this->_classColumns[$columnName]['filter']))
                            {
                                $whereSQL_or[] = $this->_classColumns[$columnName]['filter'] . ' = ' . $db->makeQueryString($argument) . ' ';
                            }

                            if (isset($this->_classColumns[$columnName]['filterHaving']))
                            {
                                $havingSQL_or[] = $this->_classColumns[$columnName]['filterHaving'] . ' = ' . $db->makeQueryString($argument) . ' ';
                            }
                        }
                    }

                    /* Contains (=~) */
                    if (strpos($data, '=~') !== false)
                    {
                        if (isset($this->_classColumns[$columnName]['filter']))
                        {
                            $whereSQL_or[] = $this->_classColumns[$columnName]['filter'] . ' LIKE ' . $db->makeQueryString('%' . $argument . '%') .' ';
                        }

                        if (isset($this->_classColumns[$columnName]['filterHaving']))
                        {
                            $havingSQL_or[] = $this->_classColumns[$columnName]['filterHaving'] . ' LIKE ' . $db->makeQueryString('%' . $argument . '%') .' ';
                        }
                    }

                    /* Is less than (=<) */
                    if (strpos($data, '=<') !== false)
                    {
                        if (isset($this->_classColumns[$columnName]['filter']))
                        {
                            $whereSQL_or[] = $this->_classColumns[$columnName]['filter'] . ' <= ' . $db->makeQueryInteger($argument) .' ';
                        }

                        if (isset($this->_classColumns[$columnName]['filterHaving']))
                        {
                            $havingSQL_or[] = $this->_classColumns[$columnName]['filterHaving'] . ' <= ' . $db->makeQueryInteger($argument)  .' ';
                        }
                    }

                    /* Is greater than (=>) */
                    if (strpos($data, '=>') !== false)
                    {
                        if (isset($this->_classColumns[$columnName]['filter']))
                        {
                            $whereSQL_or[] = $this->_classColumns[$columnName]['filter'] . ' >= ' . $db->makeQueryInteger($argument) .' ';
                        }

                        if (isset($this->_classColumns[$columnName]['filterHaving']))
                        {
                            $havingSQL_or[] = $this->_classColumns[$columnName]['filterHaving'] . ' >= ' . $db->makeQueryInteger($argument)  .' ';
                        }
                    }

                    /* Contains in list (=#) */
                    if (strpos($data, '=#') !== false)
                    {
                        /* This is in case you need to use eval to build a where or having clause with subselects. */
                        if (isset($this->_classColumns[$columnName]['filterRender=#']))
                        {
                            $whereSQL_or[] = eval($this->_classColumns[$columnName]['filterRender=#']);
                        }

                        if (isset($this->_classColumns[$columnName]['filterHavingRender=#']))
                        {
                            $havingSQL_or[] = eval($this->_classColumns[$columnName]['filterHavingRender=#']);
                        }

                        /* Standard filtering happens here. */
                        if (isset($this->_classColumns[$columnName]['filter']))
                        {
                            $whereSQL_or[] = $this->_classColumns[$columnName]['filter'] . ' = ' . $db->makeQueryString($argument) . ' ';
                        }

                        if (isset($this->_classColumns[$columnName]['filterHaving']))
                        {
                            $havingSQL_or[] = $this->_classColumns[$columnName]['filterHaving'] . ' = ' . $db->makeQueryString($argument) . ' ';
                        }
                    }
                    
                    /* Near Zipcode (=@) */
                    if (strpos($data, '=@') !== false)
                    {
                        /* Try to determine lat/lng of provided zipcode, if can't find abort. */
                        $parts = explode(',', $argument);
                        
                        if (count($parts) != 2)
                        {
                            continue;
                        }
                        
                        $zipcode = (int) $parts[0];
                        $distance = (int) $parts[1];
                        
                        $zipcodeData = $db->getAssoc('SELECT * FROM zipcodes WHERE zipcode = '. $db->makeQueryInteger($zipcode));
                        
                        if (!isset($zipcodeData['lat']))
                        {
                            continue;
                        }
                        
                        $zipcodeLat = $zipcodeData['lat'];
                        $zipcodeLng = $zipcodeData['lng'];
                        
                        $joinSQL['zipsearching'] = 'LEFT JOIN zipcodes AS zipcode_search ON zipcode_search.zipcode = '.$this->_classColumns[$columnName]['filter'];
                        
                        // Boundaries
                        $whereSQL[] = 'zipcode_search.lat > '.($zipcodeLat - (float) $distance / MILES_PER_LATLNG);
                        $whereSQL[] = 'zipcode_search.lat < '.($zipcodeLat + (float) $distance / MILES_PER_LATLNG);
                        $whereSQL[] = 'zipcode_search.lng > '.($zipcodeLng - (float) $distance / MILES_PER_LATLNG);
                        $whereSQL[] = 'zipcode_search.lng < '.($zipcodeLng + (float) $distance / MILES_PER_LATLNG);
                        
                        // Abs Distance
                        $whereSQL[] = 'sqrt(pow((zipcode_search.lng - '.$zipcodeLng.'),2) + pow((zipcode_search.lat - '.$zipcodeLat.'),2)) < '.((float) $distance / MILES_PER_LATLNG);
                        
                        // TODO:  Actual geographic search?
                    }

                }
                if (count($whereSQL_or) > 0)
                {
                    $whereSQL[] = '(' . implode(' OR ', $whereSQL_or) . ')';
                }
                if (count($havingSQL_or) > 0)
                {
                    $havingSQL[] = '(' . implode(' OR ', $havingSQL_or) . ')';
                }
            }
        }

        /* Get WHERE and HAVING paramaters for each column we want to collect data on. */
        foreach ($this->_currentColumns as $index => $data)
        {
            if (isset($data['data']['where']) && !empty($data['data']['where']))
            {
                $whereSQL[] = '(' . $data['data']['where'] . ')';
            }

            if (isset($data['data']['having']) && !empty($data['data']['having']))
            {
                $havingSQL[] = '(' . $data['data']['having'] . ')';
            }
        }

        if (count($selectSQL) > 0)
        {
            $selectSQL = '' . implode($selectSQL, ','."\n");
        }
        else
        {
            $selectSQL = '0 as __nothing';
        }

        $joinSQL = implode($joinSQL, "\n");
        if ($this->_parameters['maxResults'] != -1)
        {
            if ($this->_parameters['rangeStart'] < 0)
            {
                $this->_parameters['rangeStart'] = 0;
            }
            
            $limitSQL = 'LIMIT ' . $this->_parameters['rangeStart'] . ', ' . $this->_parameters['maxResults'];
        }
        else
        {
            $limitSQL = '';
        }

        /* Alpha navigation set? */
        if (isset($this->_parameters['filterAlpha']))
        {
            $havingSQL[] = 'ORD(UPPER('.$this->_parameters['sortBy'].')) = ORD(UPPER(\''.$this->_parameters['filterAlpha'].'\'))';
        }

        if (isset($this->_parameters['exportIDs']) && isset($this->_dataItemIDColumn))
        {
            $whereSQL[] = $this->_dataItemIDColumn .' IN ('.implode(',', $this->_parameters['exportIDs']).')';

            //Make sure we do not apply the page results limit to this query.
            $limitSQL = '';
        }

        $whereSQL = implode($whereSQL, ' AND '."\n");
        $havingSQL = implode($havingSQL, ' AND '."\n");
        $orderSQL = 'ORDER BY ' . $this->_parameters['sortBy'] . ' ' . $this->_parameters['sortDirection'];

        $sql = $this->getSQL($selectSQL, $joinSQL, $whereSQL, $havingSQL, $orderSQL, $limitSQL);

        $this->_rs = $db->getAllAssoc($sql);

        /* Get total number of results before limit. */
        $rs2 = $db->getAssoc("SELECT FOUND_ROWS() as rowCount");
        $this->_totalEntries = $rs2['rowCount'];
    }

    /**
     * Outputs the number of rows using the current filters.
     *
     * @return integer number of rows
     */
    public function getNumberOfRows()
    {
        $this->_getData();
        
        return $this->_totalEntries;
    }

    /**
     * Outputs an array of all of the exportable ID's.
     *
     * @return array id's
     */
    public function getExportIDs()
    {
        // TODO:  Is this going to be too memory intensive?
        $this->_getData();
        
        $exportableIDs = array();
        
        foreach ($this->_rs as $rowIndex => $rsData)
        {
            $exportableIDs[] = $rsData['exportID'];
        }
        
        /* Free up the potentially massive result set. */
        unset($this->_rs);
        
        return $exportableIDs;
    }

    /**
     * Outputs the current data in CSV format.
     *
     * @return void
     */
    public function drawCSV()
    {
        /* Get data. */
        $this->_getData();

        /* Figure out what columns we can export. */
        $exportableColumns = array();
        foreach ($this->_classColumns as $index => $data)
        {
            $exportableColumns[] = array('name' => $index, 'data' => $data);
        }
        $this->_currentColumns = $exportableColumns;

        /* Reload data. */
        $this->_rs = false;
        $this->_getData();

        $exportableHeaders = array();

        foreach ($exportableColumns as $index => $data)
        {
            if ($data['name'] == '' ||
                (isset($data['data']['exportable']) &&
                $data['data']['exportable'] == false
                ) || (!isset($data['data']['sortableColumn']) &&
                 !isset($data['data']['exportRender'])
                )
            )
            {
                unset($exportableColumns[$index]);
            }
            else
            {
                if (isset($data['data']['exportColumnHeaderText']))
                {
                    $exportableHeaders[] = str_replace('"', '""', $data['data']['exportColumnHeaderText']);
                }
                else
                {
                    $exportableHeaders[] = str_replace('"', '""', $data['name']);
                }
            }
        }

        $headerRow = implode(
            ',', $exportableHeaders
        ) . "\r\n";

        $length = strlen($headerRow);
        foreach ($this->_rs as $rowIndex => $rsData)
        {
            $rowColumns = array();

            foreach ($exportableColumns as $index => $data)
            {
                /* Populate $value for this column. */
                $value = "";

                if (isset($data['data']['exportRender']))
                {
                    $value = eval($data['data']['exportRender']);
                }
                else if (isset($data['data']['sortableColumn']))
                {
                    $value = @$rsData[$data['data']['sortableColumn']];
                }

                /* Escape any double-quotes and place the value inside
                 * double quotes.
                 */
                $rowColumns[] = '"' . str_replace('"', '""', $value) . '"';
            }

            $this->_rs[$rowIndex] = implode(
                ',', $rowColumns
            ) . "\r\n";
            $length += strlen($this->_rs[$rowIndex]);
        }

        header('Content-Disposition: attachment; filename="export.csv"');
        header('Content-Length: ' . $length);
        header('Connection: close');
        header('Content-Type: text/x-csv; name=export.csv; charset=utf-8');

        if (defined('INSERT_BOM_CSV_LENGTH') && (INSERT_BOM_CSV_LENGTH > 0))
        {
            echo chr(INSERT_BOM_CSV_1);
            if (INSERT_BOM_CSV_LENGTH > 1)
            {
                echo chr(INSERT_BOM_CSV_2);
            }
            if (INSERT_BOM_CSV_LENGTH > 2)
            {
                echo chr(INSERT_BOM_CSV_3);
            }
            if (INSERT_BOM_CSV_LENGTH > 3)
            {
                echo chr(INSERT_BOM_CSV_4);
            }
        }

        echo $headerRow;

        foreach ($this->_rs as $rowIndex => $row)
        {
            echo $row;
            unset($this->_rs[$rowIndex]);
        }

        die();
    }


    /**
     * Outputs the current data in an HTML list.
     *
     * @return void
     */
    public function drawHTML()
    {
        /* Get data. */
        $this->_getData();

        $md5InstanceName = md5($this->_instanceName);

        $this->_totalColumnWidths = 0;

        /* Build cell indexes for cell headers. */
        $cellIndexes = array();
        foreach ($this->_currentColumns as $index => $data)
        {
            $cellIndexes[] = $index;
        }

        foreach ($this->_rs as $rowIndex => $rsData)
        {
            $rowColumns = array();

            foreach ($this->_currentColumns as $index => $data)
            {
                /* Populate $value for this column. */
                $value = "";

                if (!isset($data['data']['pagerRender']))
                {
                    $value = ($rsData[$data['data']['sortableColumn']]);
                }
                else
                {
                    $value = (eval($data['data']['pagerRender']));
                }

                /* Escape any double-quotes and place the value inside
                 * double quotes.
                 */
                $rowColumns[] = $value;
            }

            $this->_rs[$rowIndex] = implode(
                '&nbsp;&nbsp;', $rowColumns
            ) . "<br />";
        }

        foreach ($this->_rs as $rowIndex => $row)
        {
            echo '<span style="'.$this->globalStyle.'">'.$row.'</span>';
            unset($this->_rs[$rowIndex]);
        }
    }


    /**
     * Draws the pager onto the current template (meant to be invoked from template or ajax
     * function).
     *
     * @param boolean don't draw the overflow contaner.
     * @return void
     */
    public function draw($noOverflow = false)
    {
        /* Get data. */
        $this->_getData();

        $md5InstanceName = md5($this->_instanceName);

        $this->_totalColumnWidths = 0;

        /* Build cell indexes for cell headers. */
        $cellIndexes = array();
        foreach ($this->_currentColumns as $index => $data)
        {
            $cellIndexes[] = $index;
        }

        /* Do not draw elements that exist outside of the OverflowDiv object (in the case of being called by the ajax redraw function) */
        if (!$noOverflow)
        {
             /* Filters */
            if (isset($this->_parameters['filter']))
            {
                $currentFilterString = $this->_parameters['filter'];
            }
            else
            {
                $currentFilterString = '';
            }

            echo '<input type="hidden" id="filterArea'.$md5InstanceName.'" value="', htmlspecialchars($currentFilterString), '" />';
            echo '<script type="text/javascript">', $this->_getApplyFilterFunctionDefinition(), '</script>';

            /* This makes the table able to be wider then the displayable area. */
            echo '<div id="OverflowDiv'.$md5InstanceName.'" style="overflow: auto; width: ' , ($this->getTableWidth(true)) , 'px; padding-left: 1px; overflow-y: hidden; overflow-x: none; padding-bottom: expression(this.scrollWidth > this.offsetWidth ? 14 : 4); ' . $this->globalStyle . '">', "\n";
        }

        /* IE fix for floating dialog boxes not floating over controls like dropdown lists. */
        echo '<iframe id="helpShim'.$md5InstanceName.'" src="lib/IFrameBlank.html" scrolling="no" frameborder="0" style="position:absolute; display:none;"></iframe>', "\n";

        /* Definition for the cell which appears to be showing when dragging a column into a new position (not resizing). */
        echo ('<div class="moveableCell" style="cursor: move; position:absolute; width:100px; border:1px solid gray; display:none; zIndex:10000; filter:alpha(opacity=75);-moz-opacity:.75;opacity:.75; ' . $this->globalStyle . '" id="moveColumn'.$md5InstanceName.'"></div>' . "\n");

        /* Actuall definition for the table. */
        if (isset($this->listStyle) && $this->listStyle == true)
        {
            echo ('<table class="sortable" width="'. $this->getTableWidth(true) .'" onmouseover="javascript:trackTableHighlight(event)" id="table'.$md5InstanceName.'" style="border:none;">' . "\n");
            echo ('<thead style="-moz-user-select:none; -khtml-user-select:none; user-select:none; display:none; ' . $this->globalStyle . '">' . "\n");
        }
        else
        {
            echo ('<table class="sortable" width="'. $this->getTableWidth(true) .'" onmouseover="javascript:trackTableHighlight(event)" id="table'.$md5InstanceName.'">' . "\n");
            echo ('<thead style="-moz-user-select:none; -khtml-user-select:none; user-select:none; ' . $this->globalStyle . '">' . "\n");
        }
        echo ('<tr>' . "\n");

        if (!isset($this->showExportColumn) || $this->showExportColumn)
        {
            /* Column selector icon */ /**/
            echo ('<th style="width:10px; border-right:1px solid gray; ' . $this->globalStyle . '" align="center" id="cellHideShow'.$md5InstanceName.'"><div style="width:10px;">' . "\n");

            /* Choose column box */
            if (isset($this->showChooseColumnsBox) && $this->showChooseColumnsBox == true)
            {
                echo ('<a href="javascript:void(0);" id="exportBoxLink'.$md5InstanceName.'" onclick="toggleHideShowControls(\''.$md5InstanceName.'\'); return false;">' . "\n");
                echo ('<img src="images/tab_add.gif" border="0" alt="" />' . "\n");
                echo ('</a></div>' . "\n");

                /* Dropdown selector to choose which columns are visible. */
                echo ('<div class="ajaxSearchResults" id="ColumnBox'.$md5InstanceName.'" align="left" onclick="toggleHideShowControls(\''.$md5InstanceName.'\');" style="width:200px; ' . $this->globalStyle . '">' . "\n");
                echo ('<span style="font-weight:bold; color:#000000;">Show Columns:</span><br/><br />' . "\n");

                /* Contents of dropdown menu. */
                foreach ($this->_classColumns as $index => $data)
                {
                    $selected = false;
                    foreach ($this->_currentColumns as $index2 => $data2)
                    {
                        if ($data2['name'] == $index)
                        {
                            $selected = true;
                        }
                    }

                    /* Add / remove columns */
                    if (!isset($data['pagerOptional']) || $data['pagerOptional'] == true)
                    {
                        if ($selected)
                        {
                            $newParameterArray = $this->_parameters;
                            $newParameterArray['removeColumn'] = $index;

                            echo ('<span style="font-weight:normal;">' . $this->_makeControlLink($newParameterArray) . '<img src="images/checkbox.gif" border="0" alt="" />&nbsp;&nbsp;&nbsp;&nbsp;'. $index . '</a></span><br />' . "\n");
                        }
                        else
                        {
                            $newParameterArray = $this->_parameters;
                            $newParameterArray['addColumn'] = $index;

                            echo ('<span style="font-weight:normal;">' . $this->_makeControlLink($newParameterArray) . '<img src="images/checkbox_blank.gif" border="0" alt="" />&nbsp;&nbsp;&nbsp;&nbsp;'. $index . '</a></span><br />' . "\n");
                        }
                    }
                }

                /* Single option to reset the column sizes / contents. */
                echo ('<br />');
                $newParameterArray = $this->_parameters;
                $newParameterArray['resetColumns'] = true;
                echo ('<span style="font-weight:bold;">' . $this->_makeControlLink($newParameterArray) . '<img src="images/checkbox_blank.gif" alt="" border="0" />&nbsp;&nbsp;&nbsp;&nbsp;Reset to Default Columns</a></span><br />' . "\n");

                echo ('</div>');
            }

            /* Ajax indicator. */
            echo ('<span style="display:none;" id="ajaxTableIndicator'.$md5InstanceName.'"><img src="images/indicator_small.gif" alt="" /></span>');

            /* Selected Export ID's Array */
            echo ('<script type="text/javascript">exportArray'.$md5InstanceName.' = new Array();</script>');

            echo ('</th>');
        }
        else
        {
            /* Ajax indicator. */
            echo ('<span style="display:none;" id="ajaxTableIndicator'.$md5InstanceName.'"></span>');   
        }

        /* Column headers */
        foreach ($this->_currentColumns as $index => $data)
        {
            /* Is the column sizable?  If it is, then we need to make a second column to resize that appears to be part of the first column. */
            if ((!isset($data['data']['sizable']) || $data['data']['sizable'] == true) &&
                (isset($this->allowResizing) && $this->allowResizing == true))
            {
                $sizable = true;
                $this->_totalColumnWidths += $data['width'] + 1;
            }
            else
            {
                $sizable = false;
                $this->_totalColumnWidths += $data['width'];
            }

           /* Opening of header cell. */
           echo ('<th align="left" style="width:'.$data['width'].'px; border-collapse: collapse; ' . $this->globalStyle);
           
	   $currentColumnsKeys = array_keys($this->_currentColumns);
           if (end($currentColumnsKeys) != $index && !$sizable)
           {
                   //Uncomment for gray resize bars
                   echo 'border-right:1px solid gray;';
           }

           $newParameterArray = $this->_parameters;
           $newParameterArray['reorderColumns'] = '<dynamic>';

           /* If $this->allowResizing is not set, prevent moving.  Otherwise, write the code to make the cell movable. */
           if (isset($this->allowResizing) && $this->allowResizing == true)
           {

                $formatString = '" id="cell%s%s" onmouseover="style.cursor = '
                    . '\'move\'" onmousedown="startMove(\'cell%s%s\', '
                    . '\'table%s\', \'cell%s%s\', \'%s\', \'%s\', \'%s\', '
                    . '\'moveColumn%s\', \'OverflowDiv%s\', \'%s\', urlDecode(\'%s\'));">';

                echo sprintf(
                    $formatString,
                    $md5InstanceName, $index,
                    $md5InstanceName, $index,
                    $md5InstanceName,
                    $md5InstanceName, end($currentColumnsKeys),
                    urlencode($this->_instanceName),
                    $_SESSION['CATS']->getCookie(),
                    $data['name'],
                    $md5InstanceName,
                    $md5InstanceName,
                    urlencode(serialize($newParameterArray)),
                    urlencode($this->_getUnrelatedRequestString())
                );
            }
            else
            {
               echo '" id="cell', $md5InstanceName, $index, '">';
            }

            echo ('<div id="cell'.$md5InstanceName.$index.'div" style="width:'.$data['width'].'px;">' . "\n");

            /* Header cell contents. */
            if (isset($data['data']['pagerNoTitle']) && $data['data']['pagerNoTitle'] == true)
            {
                /* Do nothing */
            }
            else if (isset($data['data']['sortableColumn']))
            {
                /* If this field is not the current sort-by field, or if it is and the
                 * current sort direction is DESC, the link will use ASC sort order.
                 */
                if ($this->_parameters['sortBy'] !== $data['data']['sortableColumn'] || $this->_parameters['sortDirection'] === 'DESC')
                {
                    $sortDirection = 'ASC';
                }
                else
                {
                    $sortDirection = 'DESC';
                }

                if ($this->_parameters['sortBy'] == $data['data']['sortableColumn'] && $this->_parameters['sortDirection'] === 'ASC')
                {
                    $sortImage = '&nbsp;<img src="images/downward.gif" style="border: none;" alt="" />';
                }
                else if ($this->_parameters['sortBy'] == $data['data']['sortableColumn'] && $this->_parameters['sortDirection'] === 'DESC')
                {
                    $sortImage = '&nbsp;<img src="images/upward.gif" style="border: none;" alt="" />';
                }
                else
                {
                    $sortImage = '&nbsp;<img src="images/nosort.gif" style="border: none;" alt="" />';
                }


                $newParameterArray = $this->_parameters;
                $newParameterArray['sortBy'] = $data['data']['sortableColumn'];
                $newParameterArray['sortDirection'] = $sortDirection;

                if (isset($newParameterArray['filterAlpha']))
                {
                    unset($newParameterArray['filterAlpha']);
                }

                if (isset($this->allowSorting) && $this->allowSorting == false)
                {
                    echo sprintf(
                        '<nobr>%s</nobr>',
                        (!isset($data['data']['columnHeaderText']) ?
                            $data['name'] :
                            $data['data']['columnHeaderText'])
                    );
                }
                else if (isset($data['data']['columnHeaderText']))
                {
                    echo sprintf(
                        '%s<nobr>%s%s</nobr></a>',
                        $this->_makeControlLink($newParameterArray),
                        $data['data']['columnHeaderText'],
                        $sortImage
                    );
                }
                else
                {
                    echo sprintf(
                        '%s<nobr>%s%s</nobr></a>',
                        $this->_makeControlLink($newParameterArray),
                        $data['name'],
                        $sortImage
                    );
                }
            }
            else
            {
                echo '<span style="font-weight:bold;"><nobr>',
                    $data['name'], '</nobr></span>';
            }

            /* Draw the closing part of the cell. */
            echo '</div></th>', "\n";

            /* If this cell can be resized, make a cell next to it to move around. */
            if ($sizable)
            {
                $formatString = '<th align="left" class="resizeableCell" '
                    . 'style="width:5px; border-collapse: collapse; '
                    . '-moz-user-select: none; -khtml-user-select: none; ' . $this->globalStyle;

				$_keys_current_columns = array_keys($this->_currentColumns);
				if (end($_keys_current_columns) != $index)
               {
                   //Uncomment for gray resize bars
                   $formatString .= 'border-right:1px solid gray;';
               }

                $formatString .=
                      'user-select: none;" onmouseover="style.cursor = '
                    . '\'e-resize\'" onmousedown="startResize(\'cell%s%s\', '
                    . '\'table%s\', \'cell%s%s\', \'%s\', \'%s\', \'%s\', '
                    . '\'%s\', \'%s\', this.offsetWidth);">';

                echo sprintf(
                    $formatString,
                    $md5InstanceName, $index,
                    $md5InstanceName,
                    $md5InstanceName, end($_keys_current_columns),
                    $this->getTableWidth(),
                    urlencode($this->_instanceName),
                    $_SESSION['CATS']->getCookie(),
                    $data['name'],
                    implode(',', $cellIndexes)
                );

                echo '<div class="dataGridResizeAreaInnerDiv"></div></th>', "\n";
            }
        }
        echo '</tr>', "\n";
        echo '</thead>', "\n";

        /* Table Data */
        foreach ($this->_rs as $rsIndex => $rsData)
        {
            if (isset($this->listStyle) && $this->listStyle == true)
            {
                echo ('<tr>' . "\n");
            }
            else
            {
                echo ('<tr class="' . TemplateUtility::getAlternatingRowClass($rsIndex) . '">' . "\n");
            }

            if (!isset($this->showExportColumn) || $this->showExportColumn)
            {
                /* Action/Export */
                echo ('<td style="' . $this->globalStyle . '">');
                if (isset($rsData['exportID']) && isset($this->showExportCheckboxes) && $this->showExportCheckboxes == true)
                {
                    echo ('<input type="checkbox" id="checked_' . $rsData['exportID'] . '" name="checked_' . $rsData['exportID'] . '" onclick="addRemoveFromExportArray(exportArray'.$md5InstanceName.', '.$rsData['exportID'].');" />');
                }
                echo ('</td>');
            }

            /* 1 Column of data */
            foreach ($this->_currentColumns as $index => $data)
            {
                if (isset($data['data']['pagerAlign']))
                {
                    echo ('<td valign="top" style="' . $this->globalStyle . '" align="' . $data['data']['pagerAlign'] . '"');
                }
                else
                {
                    echo ('<td valign="top" style="' . $this->globalStyle . '" align="left"');
                }

                if (isset($data['data']['sizable']) && $data['data']['sizable'] == false || (!isset($this->allowResizing) || $this->allowResizing == false))
                {
                     echo ('>');
                }
                else
                {
                    echo (' colspan="2">');
                }

                if (!isset($data['data']['pagerRender']))
                {
                    echo ($rsData[$data['data']['sortableColumn']]);
                }
                else
                {
                    echo (eval($data['data']['pagerRender']));
                }

                echo ('</td>' . "\n");
            }

            echo ('</tr>' . "\n");
        }

        echo ('</table>' . "\n");

        /* If the table is smaller than the maximum width, JS will extend out the last cell so the table takes up all of its allocated space. */
echo ('<script type="text/javascript">setTableWidth("table'.$md5InstanceName.'", '.$this->_totalColumnWidths.', document.getElementById(\'cell'.$md5InstanceName.end($_keys_current_columns).'\'), document.getElementById(\'cell'.$md5InstanceName.end($_keys_current_columns).'div\'), \'' . $this->getTableWidth() . '\');</script>' . "\n");

        /* Close overflowdiv */
        if (!$noOverflow)
        {
            echo ('</div>');
        }
    }
    
    /**
     * echos the action area.
     * @return void
     */
    public function printActionArea()
    {
        echo '&nbsp;<input type="checkbox" name="allBox" title="Select All" onclick="toggleChecksAllDataGrid'.md5($this->_instanceName).'(this.checked);" />&nbsp;&nbsp;&nbsp;';

        echo '<script type="text/javascript">', $this->_getCheckAllDefinition(), '</script>';

        if (!isset($this->showActionArea) || $this->showActionArea == false)
        {
            return;
        }

        echo '<a href="javascript:void(0);" onclick="toggleHideShowAction(\''.md5($this->_instanceName).'\');">Action</a>';

        echo '<div class="ajaxSearchResults" id="ActionArea'.md5($this->_instanceName).'" align="left" onclick="toggleHideShowAction(\''.md5($this->_instanceName).'\');" style="width:270px;">';

        echo $this->getInnerActionArea();

        echo '</div>';
    }

    /**
     * Returns HTML to render an action under the action menu.
     *
     * @param string action title
     * @param string action URL
     * @param boolean (true) action can be applied to all items across every page
     * @return string generated HTML
     */
    public function getInnerActionAreaItem($actionTitle, $actionURL, $allowAll = true)
    {
        //TODO:  If nothing is selected, display an error popup.
        
        $newParameterArraySelected = $this->_parameters;
        $newParameterArraySelected['rangeStart'] = 0;
        $newParameterArraySelected['maxResults'] = 100000000;
        $newParameterArraySelected['exportIDs'] = '<dynamic>';
        $newParameterArraySelected['noSaveParameters'] = true;

        $newParameterArrayAll = $this->_parameters;
        $newParameterArrayAll['rangeStart'] = 0;
        $newParameterArrayAll['maxResults'] = 100000000;
        $newParameterArrayAll['noSaveParameters'] = true;

        if ($allowAll)
        {
            $html = sprintf(
                '<div><div style="float:left; width:170px;">%s</div><div style="float:right; width:95px;"><a href="javascript:void(0);" onclick="if (exportArray%s.length>0) window.location.href=\'%s&i=%s&p=%s&dynamicArgument%s=\' + urlEncode(serializeArray(exportArray%s)); else dataGridNoSelected();">Selected</a>&nbsp;|&nbsp;<a href="%s&i=%s&p=%s">All</a></div></div>',
                htmlspecialchars($actionTitle),
                md5($this->_instanceName),
                $actionURL,
                urlencode($this->_instanceName),
                urlencode(serialize($newParameterArraySelected)),
                md5($this->_instanceName),
                md5($this->_instanceName),
                $actionURL,
                urlencode($this->_instanceName),
                urlencode(serialize($newParameterArrayAll))
            );        
        }
        else
        {
            $html = sprintf(
                '<div><div style="float:left; width:170px;">%s</div><div style="float:right; width:95px;"><a href="javascript:void(0);" onclick="if (exportArray%s.length>0) window.location.href=\'%s&i=%s&p=%s&dynamicArgument%s=\' + urlEncode(serializeArray(exportArray%s)); else dataGridNoSelected();">Selected</a></div></div>',
                htmlspecialchars($actionTitle),
                md5($this->_instanceName),
                $actionURL,
                urlencode($this->_instanceName),
                urlencode(serialize($newParameterArraySelected)),
                md5($this->_instanceName),
                md5($this->_instanceName)
            );        
        }
        
        return $html;
    }
    
    /**
     * Returns HTML to render an action under the action menu which generates
     * a popup rather than a new page.
     *
     * @param string action title
     * @param string action URL
     * @param integer width
     * @param integer height
     * @param boolean (true) action can be applied to all items across every page
     * @return string generated HTML
     */
    public function getInnerActionAreaItemPopup($actionTitle, $actionURL, $width, $height, $allowAll = true)
    {   
        //TODO:  If nothing is selected, display an error popup.
        
        $newParameterArraySelected = $this->_parameters;
        $newParameterArraySelected['rangeStart'] = 0;
        $newParameterArraySelected['maxResults'] = 100000000;
        $newParameterArraySelected['exportIDs'] = '<dynamic>';
        $newParameterArraySelected['noSaveParameters'] = true;

        $newParameterArrayAll = $this->_parameters;
        $newParameterArrayAll['rangeStart'] = 0;
        $newParameterArrayAll['maxResults'] = 100000000;
        $newParameterArrayAll['noSaveParameters'] = true;

        if ($allowAll)
        {
            $html = sprintf(
                '<div><div style="float:left; width:170px;">%s</div><div style="float:right; width:95px;"><a href="javascript:void(0);" onclick="if (exportArray%s.length>0) showPopWin(\'%s&i=%s&p=%s&dynamicArgument%s=\' + urlEncode(serializeArray(exportArray%s)), %s, %s); else dataGridNoSelected();">Selected</a>&nbsp;|&nbsp;<a href="javascript:void(0);" onclick="showPopWin(\'%s&i=%s&p=%s\', %s, %s);">All</a></div></div>',
                htmlspecialchars($actionTitle),
                md5($this->_instanceName),
                $actionURL,
                urlencode($this->_instanceName),
                urlencode(serialize($newParameterArraySelected)),
                md5($this->_instanceName),
                md5($this->_instanceName),
                $width,
                $height,
                $actionURL,
                urlencode($this->_instanceName),
                urlencode(serialize($newParameterArrayAll)),
                $width,
                $height
            );        
        }
        else
        {
            $html = sprintf(
                '<div><div style="float:left; width:170px;">%s</div><div style="float:right; width:95px;"><a href="javascript:void(0);" onclick="if (exportArray%s.length>0) showPopWin(\'%s&i=%s&p=%s&dynamicArgument%s=\' + urlEncode(serializeArray(exportArray%s)), %s, %s); else dataGridNoSelected();">Selected</a></div></div>',
                htmlspecialchars($actionTitle),
                md5($this->_instanceName),
                $actionURL,
                urlencode($this->_instanceName),
                urlencode(serialize($newParameterArraySelected)),
                md5($this->_instanceName),
                md5($this->_instanceName),
                $width,
                $height
            );      
        }
        
        return $html;
    }

    /**
     * This is an empty function which is overloaded by child classes.  It 
     * outputs extra actions for each specific datagrid.
     *
     * @return string generated HTML
     */
    public function getInnerActionArea()
    {
        /* Todo:  Add predefined actions here. */
        $html = '';

        return $html;
    }

    /**
     * Outputs the javascript necessary to change all the page navigation elements that aren't immediantly changed
     * by redrawing the Ajax Table (such as next / previous links).
     *
     * @return void
     */
    public function drawUpdatedNavigation()
    {
        echo '<script type=\"text/javascript\">';

        $this->_drawUpdatedNavigationSet('prevLink', $this->_getPreviousLink());
        $this->_drawUpdatedNavigationSet('nextLink', $this->_getNextLink());
        $this->_drawUpdatedNavigationSet('pageSelection', $this->_currentPage, "value");
        $this->_drawUpdatedNavigationSet('pageNumberHTML', $this->_currentPage);

        echo sprintf(
            'if (document.getElementById(\'ActionArea%s\') != null) document.getElementById(\'ActionArea%s\').innerHTML = urlDecode(\'%s\');',
            urlencode($this->_instanceName),
            urlencode($this->_instanceName),
            urlencode($this->getInnerActionArea())
        );
        
        /*if ($this->_totalPages == 1)
        {
            $this->_drawUpdatedNavigationSet('pageInputArea', '');
        }*/

        //getInnerActionArea

        echo $this->_getApplyFilterFunctionDefinition();
        echo $this->_getCheckAllDefinition();

        echo '</script>';
    }

    /**
     * Returns the Current Page formatted with HTML that can be modified by Ajax.
     *
     * @return void
     */
    public function getCurrentPageHTML()
    {
        return '<span id="pageNumberHTML1' . md5($this->_instanceName)
            . '">' . $this->getCurrentPage() . '</span>';
    }

    /**
     * Prints pager navigation HTML.
     *
     * @param boolean Draw A-Z list?
     * @return void
     */
    public function printNavigation($alphaNavigation = false)
    {
        static $ID = 0;

        /* Allow multiple navigation bars per page. */
        $ID++;

        $this->_getData();

        $md5InstanceName = md5($this->_instanceName);

        /* << PREV */
        echo sprintf(
            '<span id="prevLink%s%s">%s</span>',
            $ID, $md5InstanceName, $this->_getPreviousLink()
        );

        /* Selection drop down menu. */
        /* Because we can not change the serialized parameter array from javascript, we can
         * set one of the fields to be a 'dynamic' field.  When the datagrid class is
         * loading the datagrid, it will replace any field with the flag <dynamic> with
         * the value provided in $_REQUEST['dynamicArgument'].
         */
        $newParameterArray = $this->_parameters;
        $newParameterArray['rangeStart'] = '<dynamic>';

        if ($this->_totalPages > 1)
        {

            if (isset($this->ajaxMode) && ($this->ajaxMode))
            {
                echo sprintf(
                    '<span style="%s" id="pageInputArea%s%s">Page <input id="pageSelection%s%s" style="width: 32px;" onChange="populateAjaxPager(&quot;%s&quot;, \'%s\', &quot;%s&quot;, (this.value - 1) * %s);" value="%s"/> of %s%s</span>',
                    $this->globalStyle,
                    $ID, $md5InstanceName,
                    $ID, $md5InstanceName,      //Select Box ID
                    urlencode($this->_instanceName),           //Instance name for ajax function itself
                    urlencode(serialize($newParameterArray)),  //New parameter array
                    $_SESSION['CATS']->getCookie(),            //Cookie
                    $newParameterArray['maxResults'],          //Used to help determine how many rows per page when changing pages
                    $this->_currentPage,
                    $this->_totalPages,
                    "\n"
                );  
            }
            else
            {
                $requestString = $this->_getUnrelatedRequestString();
                $requestString .= '&' . urlencode('parameters' . $this->_instanceName) . '=' . urlencode(serialize($newParameterArray));

                echo sprintf(
                    '<span style="%s">Page <input id="pageSelection%s%s" style="width: 32px;" value="%s" onkeypress="document.getElementById(\'pageSelectionButton%s%s\').style.display=\'\';"/> of %s&nbsp;<input id="pageSelectionButton%s%s" type="button"  class="button" style="display:none;" value="Go" onclick="document.location.href=\'%s?%s&dynamicArgument%s=\' + ((document.getElementById(\'pageSelection%s%s\').value -1 ) * %s);">%s</span>',
                    $this->globalStyle,
                    $ID, $md5InstanceName,      //Select Box ID
                    $this->_currentPage,
                    $ID, $md5InstanceName,
                    $this->_totalPages,
                    $ID, $md5InstanceName,
                    CATSUtility::getIndexName(),
                    $requestString,
                    urlencode($this->_instanceName),
                    $ID, $md5InstanceName,
                    $newParameterArray['maxResults'],
                    "\n"
                );
            }
        }

        /* NEXT >> */
        echo sprintf(
            '<span id="nextLink%s%s">%s</span>',
            $ID, $md5InstanceName, $this->_getNextLink()
        );

        /* A-Z list */
        if ($alphaNavigation)
        {
            if (isset($this->ajaxMode) && ($this->ajaxMode))
            {
                die ('Alpha navigation not supported under AJAX mode.');
            }

            /* Find which column is currently being sorted. */
            $validAlphabeticalSort = false;
            foreach ($this->_classColumns as $index => $data)
            {
                if (isset($data['sortableColumn']) &&
                    $data['sortableColumn'] == $this->_parameters['sortBy'] &&
                    isset($data['alphaNavigation']) &&
                    $data['alphaNavigation'] == true)
                {
                    $validAlphabeticalSort = true;
                }
            }

            /* If we are not currently sorted by a column with alphabetical results,
             * use the default column. */
            if (!$validAlphabeticalSort)
            {
                $newParameterArray['sortBy'] = $this->_defaultAlphabeticalSortBy;
                $newParameterArray['sortDirection'] = 'ASC';
            }

            /* Draw the characters. */
            if ($newParameterArray['sortDirection'] == 'DESC')
            {
                for ($i = ord('Z'); $i >= ord('A'); $i--)
                {
                    $newParameterArray['filterAlpha'] = chr($i);
                    $newParameterArray['rangeStart'] = 0;

                    $link = $this->_makeControlLink($newParameterArray);

                    if (isset($this->_parameters['filterAlpha']) && $this->_parameters['filterAlpha'] == chr($i))
                    {
                        echo $link, '&nbsp;<span style="font-weight:bold;">', chr($i), '</span></a>';
                    }
                    else
                    {
                        echo $link, '&nbsp;', chr($i), '</a>';
                    }
                }
            }
            else
            {
                for ($i = ord('A'); $i <= ord('Z'); $i++)
                {
                    $newParameterArray['filterAlpha'] = chr($i);
                    $newParameterArray['rangeStart'] = 0;

                    $link = $this->_makeControlLink($newParameterArray);

                    if (isset($this->_parameters['filterAlpha']) && $this->_parameters['filterAlpha'] == chr($i))
                    {
                        echo $link, '&nbsp;<span style="font-weight:bold;">', chr($i), '</span></a>';
                    }
                    else
                    {
                        echo $link, '&nbsp;', chr($i), '</a>';
                    }
                }
            }

            /* Print ALL link. */
            $newParameterArray = $this->_parameters;

            if (isset($newParameterArray['filterAlpha']))
            {
                unset($newParameterArray['filterAlpha']);
            }

            $link = $this->_makeControlLink($newParameterArray);

            if (!isset($this->_parameters['filterAlpha']))
            {
                echo $link . '&nbsp;&nbsp;<span style="font-weight:bold;">ALL</span></a>';
            }
            else
            {
                echo $link . '&nbsp;&nbsp;ALL</a>';
            }
        }
    }


    /**
     * Prints a link to show all entries on the table.
     *
     * @return void
     */
    public function printShowAll()
    {
        if ($this->_totalPages <= 1)
        {
            return;
        }

        $newParameterArray = $this->_parameters;
        $newParameterArray['rangeStart'] = 0;
        $newParameterArray['maxResults'] = 1000;

        $newParameterArrayPagenate = $this->_parameters;
        $newParameterArrayPagenate['rangeStart'] = 0;
        $newParameterArrayPagenate['maxResults'] = 15;

        echo sprintf(
            '%sShow All</a>%sPagenate</a>%s',
            $this->_makeControlLink($newParameterArray, '', 'showAll'.md5($this->_instanceName), 'this.style.display=\'none\'; document.getElementById(\'pagenate'.md5($this->_instanceName).'\').style.display=\'\';'),
            $this->_makeControlLink($newParameterArrayPagenate, '', 'pagenate'.md5($this->_instanceName), 'this.style.display=\'none\'; document.getElementById(\'showAll'.md5($this->_instanceName).'\').style.display=\'\';', 'display:none;'),
            "\n"
        );
    }


    /**
     * Returns all GET variables except for the serialized parameter array.  If
     * unrelatedRequestString is set on a POST (this happens when called by AJAX)
     * the unrelatedRequestString provided by POST is returned.
     *
     * This is necessary because the AJAX function does not know what page it is on
     * when it is rewriting a getback pager when moving a column.  Without knowledge
     * of what page it is on, the newly generated table could not have column headers
     * that do getback sorting (because the sorting links also contain the other
     * unrelated parameters.
     *
     * @return string URI of all request variables except for 'parameters'.$this->_instanceName
     */
    private function _getUnrelatedRequestString()
    {
        if (isset($_REQUEST['unrelatedRequestString']))
        {
            return $_REQUEST['unrelatedRequestString'];
        }
        else
        {
            $getVars = $_GET;
            if (isset($getVars['parameters' . $this->_instanceName]))
            {
                unset($getVars['parameters' . $this->_instanceName]);
            }

            if (isset($getVars['dynamicArgument' . $this->_instanceName]))
            {
                unset($getVars['dynamicArgument' . $this->_instanceName]);
            }

            $getStrings = array();

            foreach ($getVars as $index => $data)
            {
                $getStrings[] = urlencode($index) . '=' . urlencode($data);
            }

            return implode('&', $getStrings);
        }
    }

    /**
     * Returns the HTML necessary to create an A tag which reloads the current view while applying
     * the new parameter array.  Makes a AjaxPager link or Getback link based on what kind of pager
     * you are using.
     *
     * @param array parameters
     * @param string optional classname
     * @param string optional id
     * @return void
     */
    private function _makeControlLink($newParameterArray, $className = "", $id = "", $javascript="", $style="")
    {
        if (isset($this->ajaxMode) && ($this->ajaxMode))
        {
            return sprintf(
                '<a href="javascript:void(0);" style="%s%s" onclick="%s populateAjaxPager(&quot;%s&quot;, &quot;%s&quot;, &quot;%s&quot;);" %s %s>',
                $this->globalStyle,
                $style,
                $javascript,
                urlencode($this->_instanceName),
                urlencode(serialize($newParameterArray)),
                $_SESSION['CATS']->getCookie(),
                ($className != '' ? 'class="'.$className.'"' : ''),
                ($id != '' ? 'id="'.$id.'"' : '')
            );
        }
        else
        {
            $requestString = $this->_getUnrelatedRequestString();
            $requestString .= '&' . urlencode('parameters' . $this->_instanceName) . '=' . urlencode(serialize($newParameterArray));

            return sprintf(
                '<a href="%s?%s" style="%s%s" onclick="%s" %s %s>',
                CATSUtility::getIndexName(),
                $requestString,
                $this->globalStyle,
                $style,
                $javascript,
                ($className != '' ? 'class="'.$className.'"' : ''),
                ($id != '' ? 'id="'.$id.'"' : '')
            );
        }
    }

    /**
     * Outputs the javascript necessary to change a navigation element when AJAX repopulates the table.
     * Navigation elements always follow the pattern: element#instance where element is a string,
     * # is either 1 or 2, and instance is the current dataGrid isnatnce.
     *
     * @param string element
     * @param string value to set the element to
     * @param string dom property to set (defaults to innerHTML, could be value in the case of select elements)
     * @return void
     */
    private function _drawUpdatedNavigationSet($element, $value, $type = 'innerHTML')
    {
        $md5InstanceName = md5($this->_instanceName);

        echo sprintf(
            'if (document.getElementById(\'%s%s%s\') != null) { document.getElementById(\'%s%s%s\').%s = urlDecode(\'%s\'); }',
            $element, 1, $md5InstanceName,
            $element, 1, $md5InstanceName,
            $type, urlencode($value)
        );

        echo sprintf(
            'if (document.getElementById(\'%s%s%s\') != null) { document.getElementById(\'%s%s%s\').%s = urlDecode(\'%s\'); }',
            $element, 2, $md5InstanceName,
            $element, 2, $md5InstanceName,
            $type, urlencode($value)
        );
    }

    /**
     * Returns HTML for next-page navigation link.
     *
     * @return string Next-page navigation link HTML.
     */
    private function _getNextLink()
    {
        if ($this->_totalPages <= 1)
        {
            return '';
        }

        /* If this is the last page, don't make a link; just text. */
        if ($this->_currentPage == $this->_totalPages)
        {
            return '<span class="pagerPrevNext" style="' . $this->globalStyle . '">Next &gt;&gt;</span>&nbsp;&nbsp;<span class="pagerPrevNext" style="' . $this->globalStyle . '">Last &gt;</span>' . "\n";
        }

        $newParameterArray = $this->_parameters;
        $newParameterArray['rangeStart'] += $newParameterArray['maxResults'];
        
        $newParameterArray2 = $this->_parameters;
        $newParameterArray2['rangeStart'] = ($this->_totalPages - 1) * $newParameterArray2['maxResults'];
        if ($newParameterArray2['rangeStart'] < 0)
        {
            $newParameterArray2['rangeStart'] = 0;
        }

        return sprintf(
            '%sNext &gt;&gt;</a>&nbsp;&nbsp;%sLast &gt;</a>%s',
            $this->_makeControlLink($newParameterArray, 'pagerPrevNext'),
            $this->_makeControlLink($newParameterArray2, 'pagerPrevNext'),
            "\n"
        );
    }

    /**
     * Returns HTML for previous-page navigation link.
     *
     * @return string Previous-page navigation link HTML.
     */
    public function _getPreviousLink()
    {
        if ($this->_totalPages <= 1)
        {
            return '';
        }

        /* If this is the first page, don't make a link; just text. */
        if ($this->_currentPage == 1)
        {
            return '<span class="pagerPrevNext" style="' . $this->globalStyle . '">&lt; First</span>&nbsp;&nbsp;<span class="pagerPrevNext" style="' . $this->globalStyle . '">&lt;&lt; Prev</span>' . "\n";
        }

        $newParameterArray = $this->_parameters;
        $newParameterArray['rangeStart'] -= $newParameterArray['maxResults'];

        $newParameterArray2 = $this->_parameters;
        $newParameterArray2['rangeStart'] = 0;

        return sprintf(
            '%s&lt; First</a>&nbsp;&nbsp;%s&lt;&lt; Prev</a>%s',
            $this->_makeControlLink($newParameterArray2, 'pagerPrevNext'),
            $this->_makeControlLink($newParameterArray, 'pagerPrevNext'),
            "\n"
        );
    }

    /**
     * Returns the javascript for the apply filter function for the table.
     * The generated JS function is submitFilter[MD5](boolean).  If the
     * argument is true, the property filterVisible is retained rather than
     * forced to true.  If the argument is false, the property filterVisible is
     * set to false.  If it is omitted, filterVisible is set to true.
     *
     * @return string Javascript
     */

     public function _getApplyFilterFunctionDefinition()
     {
        $md5InstanceName = md5($this->_instanceName);

        $newParameterArray = $this->_parameters;
        $newParameterArray['rangeStart'] = 0;
        $newParameterArray['filter'] = '<dynamic>';
        $newParameterArray['filterVisible'] = true;

        echo 'submitFilter', $md5InstanceName, ' = function(retainFilterVisible) { ';

        if (isset($this->ajaxMode) && ($this->ajaxMode))
        {
           echo sprintf(
                'populateAjaxPager(\'%s\', \'%s\', \'%s\', document.getElementById(\'filterArea%s\').value);',
                urlencode($this->_instanceName),
                urlencode(serialize($newParameterArray)),  //New parameter array
                $_SESSION['CATS']->getCookie(),            //Cookie
                $md5InstanceName
            );
        }
        else
        {
            $requestString = $this->_getUnrelatedRequestString();
            $requestString .= '&' . urlencode('parameters' . $this->_instanceName) . '=' . urlencode(serialize($newParameterArray));
            echo 'if (typeof(retainFilterVisible) == \'undefined\') {';

                echo sprintf(
                    'document.location.href=\'%s?%s&dynamicArgument%s=\' + urlEncode(document.getElementById(\'filterArea%s\').value);',
                    CATSUtility::getIndexName(),
                    $requestString,
                    urlencode($this->_instanceName),
                    $md5InstanceName
                );

            echo '} else if (typeof(retainFilterVisible) != \'undefined\' && retainFilterVisible == false) {';

                $newParameterArray = $this->_parameters;
                $newParameterArray['rangeStart'] = 0;
                $newParameterArray['filter'] = '<dynamic>';
                $newParameterArray['filterVisible'] = false;

                $requestString = $this->_getUnrelatedRequestString();
                $requestString .= '&' . urlencode('parameters' . $this->_instanceName) . '=' . urlencode(serialize($newParameterArray));

                echo sprintf(
                    'document.location.href=\'%s?%s&dynamicArgument%s=\' + urlEncode(document.getElementById(\'filterArea%s\').value);',
                    CATSUtility::getIndexName(),
                    $requestString,
                    urlencode($this->_instanceName),
                    $md5InstanceName
                );

            echo '} else {';

                $newParameterArray = $this->_parameters;
                $newParameterArray['rangeStart'] = 0;
                $newParameterArray['filter'] = '<dynamic>';

                $requestString = $this->_getUnrelatedRequestString();
                $requestString .= '&' . urlencode('parameters' . $this->_instanceName) . '=' . urlencode(serialize($newParameterArray));

                echo sprintf(
                    'document.location.href=\'%s?%s&dynamicArgument%s=\' + urlEncode(document.getElementById(\'filterArea%s\').value);',
                    CATSUtility::getIndexName(),
                    $requestString,
                    urlencode($this->_instanceName),
                    $md5InstanceName
                );

            echo '}';
        }
        echo '}';
    }

    /**
     * Returns the javascript for the check all checkbox.
     * The generated JS function is submitFilter[MD5]
     *
     * @return string Javascript
     */

     public function _getCheckAllDefinition()
     {
        $md5InstanceName = md5($this->_instanceName);

        $newParameterArray = $this->_parameters;
        $newParameterArray['rangeStart'] = 0;
        $newParameterArray['filter'] = '<dynamic>';
        $newParameterArray['filterVisible'] = true;

        echo "\n";
        echo 'toggleChecksAllDataGrid', $md5InstanceName, ' = function(newValue) { ';

        foreach ($this->_rs as $rsIndex => $rsData)
        {
            if (isset($rsData['exportID']))
            {
                echo ('if (document.getElementById("checked_' . $rsData['exportID'] . '").checked != newValue) {'.
                'addRemoveFromExportArray(exportArray'.$md5InstanceName.', '.$rsData['exportID'].');'.
                'document.getElementById("checked_' . $rsData['exportID'] . '").checked = newValue;'.
                '}');
            }
        }

        echo '}';
    }

    protected function getTableWidth($makeLargerThanDisplayableArea = false)
    {
        return $this->_tableWidth->asString($makeLargerThanDisplayableArea);
    }
 }




 ?>
