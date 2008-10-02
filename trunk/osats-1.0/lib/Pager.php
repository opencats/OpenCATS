<?php
/**
 * CATS
 * Pager Library
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
 * @version    $Id: Pager.php 3676 2007-11-21 21:02:15Z brian $
 */

/**
 *	Pager
 *	@package    CATS
 *	@subpackage Library
 */
class Pager
{
    protected $_totalPages = 1;
    protected $_thisPageStartRow = 0;
    protected $_rowsPerPage = 15;
    protected $_currentPage = 1;
    protected $_totalRows;
    protected $_navigationData;
    protected $_sortByFields = array();
    protected $_baseURL = '';
    protected $_sortBy = '';
    protected $_sortDirection = '';
    protected $_cachedNavigation = null;


    public function __construct($totalRows, $rowsPerPage, $currentPage)
    {
        $this->_totalRows = $totalRows;

        /* Find the total number of available pages (we must have a whole
         * number of pages...
         */
        $this->_totalPages = ceil($totalRows / $rowsPerPage);

        /* We must have at least one page. */
        if ($this->_totalPages < 1)
        {
            $this->_totalPages = 1;
        }

        /* The current page must always be greater than zero and no greater
         * than the total number of pages.
         */
        if ($currentPage < 1)
        {
            $this->_currentPage = 1;
        }
        else if ($currentPage > $this->_totalPages)
        {
            $this->_currentPage = $this->_totalPages;
        }
        else
        {
            $this->_currentPage = $currentPage;
        }

        /* Store rows per page. */
        $this->_rowsPerPage = $rowsPerPage;

        /* Figure our our starting row. */
        $this->_thisPageStartRow = (($this->_currentPage - 1) * $this->_rowsPerPage);
    }


    /**
     * Sets parameters for generating sorting links on sortable pagertables.
     *
     * @param string base URL
     * @param string sort-by database field
     * @param string sort direction (ASC or DESC)
     * @return void
     */
    public function setSortByParameters($baseURL, $sortBy, $sortDirection)
    {
        $this->_baseURL       = $baseURL;
        $this->_sortBy        = $sortBy;
        $this->_sortDirection = $sortDirection;
    }

    /**
     * Returns the number of total rows available.
     *
     * @return integer total rows available
     */
    public function getTotalRows()
    {
        return $this->_totalRows;
    }

    /**
     * Returns the number of the current active page.
     *
     * @return integer number of the current active page
     */
    public function getCurrentPage()
    {
        return $this->_currentPage;
    }

    /**
     * Returns the number of total pages available.
     *
     * @return integer total pages available
     */
    public function getTotalPages()
    {
        return $this->_totalPages;
    }

    /**
     * Returns the number of the starting row of the current page.
     *
     * @return integer number of the starting row of the current page
     */
    public function getThisPageStartRow()
    {
        return $this->_thisPageStartRow;
    }

    /**
     * Returns an array of valid sort-by fields.
     *
     * @return array valid sort-by fields
     */
    public function getSortByFields()
    {
        return $this->_sortByFields;
    }

    /**
     * Returns current sort by method.
     *
     * @return array valid sort-by fields
     */
    public function getSortBy()
    {
        return $this->_sortBy;
    }

    /**
     * Returns true if a search direction by the name of $key is equal to 'ASC'
     * or 'DESC' in $request.
     *
     * @param string request key
     * @param array $_GET, $_POST, or $_REQUEST
     * @return boolean is sort-direction valid
     */
    public function isSortDirectionValid($key, $request)
    {
        if (isset($request[$key]) && !empty($request[$key]) &&
            ($request[$key] === 'ASC' || $request[$key] === 'DESC'))
        {
            return true;
        }

        return false;
    }

    /**
     * Returns true if a sort-by criterion is present in $request as $key and
     * in the valid keys array.
     *
     * @param string request key
     * @param array $_GET, $_POST, or $_REQUEST
     * @return boolean is sort-by valid
     */
    public function isSortByValid($key, $request)
    {
        if (isset($request[$key]) && !empty($request[$key]) &&
            in_array($request[$key], $this->_sortByFields))
        {
            return true;
        }

        return false;
    }

    /**
     * Prints a sort link for a sortable table.
     *
     * @param string database field
     * @param string link text
     * @return void
     */
    public function printSortLink($headerField, $headerText)
    {
        /* If this field is not the current sort-by field, or if it is and the
         * current sort direction is DESC, the link will use ASC sort order.
         */
        if ($this->_sortBy !== $headerField || $this->_sortDirection === 'DESC')
        {
            $sortDirection = 'ASC';
        }
        else
        {
            $sortDirection = 'DESC';
        }

        if ($this->_sortBy == $headerField && $this->_sortDirection === 'ASC')
        {
            $sortImage = '&nbsp;<img src="images/downward.gif" style="border: none;" alt="" />';
        }
        else if ($this->_sortBy == $headerField && $this->_sortDirection === 'DESC')
        {
            $sortImage = '&nbsp;<img src="images/upward.gif" style="border: none;" alt="" />';
        }
        else
        {
            $sortImage = '&nbsp;<img src="images/nosort.gif" style="border: none;" alt="" />';
        }

        echo sprintf(
            '<a href="%s?%s&amp;page=%s&amp;sortBy=%s&amp;sortDirection=%s"><nobr>%s%s</nobr></a>',
            CATSUtility::getIndexName(),
            $this->_baseURL,
            $this->_currentPage,
            $headerField,
            $sortDirection,
            $headerText,
            $sortImage
        );
    }


    /**
     * Prints pager navigation HTML.
     *
     * @return void
     */
    public function printNavigation($defaultSortBy = '', $drawAlpha2 = true, $maxPagesSetting = -1)
    {
        static $ID = 0;

        /* Allow multiple navigation bars per page. */
        $ID++;

        /* Don't show pager navigation if there is only one page. */
        if ($this->_totalPages <= 1)
        {
            return;
        }

        $valid = array(
            'name', 'city', 'state', 'clientName', 'title', 'firstName', 'lastName'
        );

        
        /* Try to get default column data if not on an alpha column. */
        if ($defaultSortBy != '' && (method_exists($this, 'getNavigation') || method_exists($this, '_getNavigation')))
        {
            $sortBy = $defaultSortBy;
            $sortDirection = 'ASC';

            if (method_exists($this, '_getNavigation'))
            {
                $this->_cachedNavigation = $this->_getNavigation($sortBy, $sortDirection);
            }
            else
            {
                $this->_cachedNavigation = $this->getNavigation($sortBy, $sortDirection);
            }

            $rsNavAlpha = $this->_cachedNavigation;
            $drawAlpha = true;
        }
        else
        {
            $drawAlpha = false;
            $sortBy = '';
        }

        $rsNav = null;
            
        $indexName = CATSUtility::getIndexName();

        /* If there is a previous page, show "<< Previous" as a link; otherwise
         * just as text.
         */
        if ($this->_currentPage != 1)
        {
            echo sprintf(
                '<a class="pagerPrevNext" href="%s?%s&amp;page=%s&amp;sortBy=%s&amp;sortDirection=%s">&lt;&lt; Previous</a>%s',
                $indexName,
                $this->_baseURL,
                ($this->_currentPage - 1),
                $this->_sortBy,
                $this->_sortDirection,
                "\n"
            );
        }
        else
        {
            echo '<span class="pagerPrevNext">&lt;&lt; Previous</span>', "\n";
        }

        /* Selection drop down menu JavaScript. */
        $javaScript = sprintf(
            'var pageList = document.getElementById(\'pageSelection%s\'); goToURL(\'%s?%s&amp;page=\''
            . ' + pageList[pageList.selectedIndex].value + \'&amp;sortBy=%s&amp;sortDirection=%s\');',
            $ID,
            $indexName,
            $this->_baseURL,
            $this->_sortBy,
            $this->_sortDirection
        );

        /* Selection drop down menu. */
        echo sprintf(
            '<select id="pageSelection%s" style="width: 95px;" onChange="%s" class="selectBox">%s',
            $ID, $javaScript, "\n"
        );

        /* Generate the <option> tags. */
        for ($i = 1; $i <= $this->_totalPages; ++$i)
        {
            if ($maxPagesSetting != -1 && $i > $maxPagesSetting)
            {
                break;
            }

            $navText = '';

            /* Try to generate helper range data for drop down. */
            if ($rsNav != null && isset($rsNav[($i * 2) - 2]) && isset($rsNav[($i * 2) - 1]))
            {
                $navLeftData  = $rsNav[($i * 2) - 2];
                $navRightData = $rsNav[($i * 2) - 1];

                $sortField = str_replace('Sort', '', $this->_sortBy);
                $navLeft = $navLeftData[$sortField];
                $navRight = $navRightData[$sortField];

                if ($navLeft)
                {
                    if ($navRight)
                    {
                        $navText = sprintf('%s(%s - %s)', str_repeat('&nbsp;', 16), $navLeft, $navRight);
                    }
                    else
                    {
                        $navText = sprintf('%s(%s)', str_repeat('&nbsp;', 16), $navLeft);
                    }
                }
            }

            /* This is the actual content. */
            if ($i == $this->_currentPage)
            {
                echo sprintf(
                    '<option selected="selected" value="">Page %s%s</option>',
                    $i, $navText
                );
            }
            else
            {
                echo sprintf(
                    '<option value="%s">Page %s%s</option>',
                    $i, $i, $navText
                );
            }
        }

        echo "\n", '</select>&nbsp;', "\n";


        /* If there is a next page, show "Next >>" as a link; otherwise just
         * as text.
         */
        if ($this->_currentPage != $this->_totalPages)
        {
            echo sprintf(
                '<a class="pagerPrevNext" href="%s?%s&amp;page=%s&amp;sortBy=%s&amp;sortDirection=%s">Next &gt;&gt;</a>%s',
                $indexName,
                $this->_baseURL,
                ($this->_currentPage + 1),
                $this->_sortBy,
                $this->_sortDirection,
                "\n"
            );
        }
        else
        {
            echo '<span class="pagerPrevNext">Next &gt;&gt;</span>', "\n";
        }

        /* If we have any alpha data, draw the alpha bar */
        if ($drawAlpha && $drawAlpha2)
        {
            $rsNav = $rsNavAlpha;

            echo(str_repeat('&nbsp;', 7));

            /* Set the initial value of what letter is being printed based on
             * sort order.
             */
            if ($sortDirection == 'ASC')
            {
                $onChar = ord('A') - 1;
            }
            else
            {
                $onChar = ord('Z') + 1;
            }

            /* $i+2, because every pair of records indicates the first and
             * last entry on a page.
             */
            for ($i = 0; $i < count($rsNav); $i += 2)
            {
                $output = '';

                /* If no corresponding 2nd entry, make one (1 entry on last
                 * page for example).
                 */
                if (count($rsNav) == $i+1)
                {
                    $rsNav[$i + 1] = $rsNav[$i];
                }

                /* Should we keep outputting characters till the last entry? */
                $lastChar = ord(
                    strtoupper(substr($rsNav[$i + 1][$sortBy], 0, 1))
                );

                /* If it is the last entry, go to the first or last letter
                 * in the alphabet.
                 */
                if (count($rsNav) == $i + 2)
                {
                    if ($sortDirection == 'ASC')
                    {
                        $lastChar = ord('Z');
                    }
                    else
                    {
                        $lastChar = ord('A');
                    }
                }

                if (($lastChar > $onChar && $sortDirection == 'ASC') ||
                    ($lastChar < $onChar && $sortDirection == 'DESC'))
                {
                    while ($onChar != $lastChar &&
                           !($onChar == ord('Z') && $sortDirection == 'ASC') &&
                           !($onChar == ord('A') && $sortDirection == 'DESC'))
                    {
                        if ($sortDirection == 'ASC')
                        {
                            $onChar++;
                        }
                        else
                        {
                            $onChar--;
                        }
                        $output = sprintf('%s%s&nbsp;', $output, chr($onChar));
                    }
                }

                /* If any letters output at all, encase them in a hyperlink and
                 * display them.
                 */
                if ($output)
                {
                    echo sprintf(
                        '<a class="pagerPrevNext" href="%s?%s&amp;page=%s&amp;sortBy=%s&amp;sortDirection=%s" title="Page %s (%s - %s)">%s</a>',
                        $indexName,
                        $this->_baseURL,
                        ($i / 2) + 1,
                        $sortBy,
                        $sortDirection,
                        ($i / 2) + 1,
                        $rsNav[$i][$sortBy],
                        $rsNav[$i + 1][$sortBy],
                        $output
                    );
                }
            }
        }
    }
}

?>
