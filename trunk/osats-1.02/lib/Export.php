<?php
/**
 * OSATS
 */

include_once('./lib/Candidates.php');
include_once('./lib/Contacts.php');
include_once('./lib/Companies.php');
include_once('./lib/JobOrders.php');

/**
 *	Data Export Utility Library
 *	@package    CATS
 *	@subpackage Library
 */
class ExportUtility
{
    /**
     * Generates HTML code for export forms / menus.
     *
     * @param flag data item type being exported
     * @param string comma-separated list of data item IDs
     * @param integer number of pixles right the export box should be displayed
     * @return array containing parts of html code for the export form.
     */
    public static function getForm($dataItemType, $IDs, $popUpOffset = 35, $linkOffset = 5)
    {
        $indexName = osatutil::getIndexName();

        /* Build form header. */
        $header = '<form name="selectedObjects" action="'
            . $indexName . '" method="get">' . "\n"
            . '<input type="hidden" name="m" value="export" />' . "\n"
            . '<input type="hidden" name="onlySelected" value="true" />' . "\n"
            . '<input type="hidden" name="dataItemType" value="'. $dataItemType . '" />' . "\n";

        /* Build form menu. */
        $allRecordsURL = sprintf(
            '%s?m=export&amp;a=export&amp;dataItemType=%s',
            $indexName,
            $dataItemType
        );

        $currentPageURL = sprintf(
            '%s?m=export&amp;a=export&amp;dataItemType=%s&amp;ids=%s',
            $indexName,
            $dataItemType,
            $IDs
        );

        $menu =
              '<div style="float: left; margin-left: 4px; margin-right: ' . $linkOffset . 'px;">'
            . '<form name="selectAll" action="#">'
            . '<input type="checkbox" name="allBox" title="Select All" onclick="toggleChecksAll();" />'
            . '</form>'
            . '</div>'
            . '<a href="#" id="exportBoxLink" onclick="showBox(\'ExportBox\'); return false;">Export</a><br />'
            . '<div class="exportPopup" id="ExportBox" align="left" onmouseover="showBox(\'ExportBox\');" onmouseout="hideBox(\'ExportBox\');">'
            . '<a href="' . $allRecordsURL . '">Export All Records</a><br />'
            . '<a href="' . $currentPageURL . '">Export Current Page</a><br />'
            . '<a href="#" onclick="checkSelected(); return false;">Export Selected Records</a>'
            . '</div>';

        $footer = '</form>';

        return array(
            'header' => $header,
            'footer' => $footer,
            'menu'   => $menu
        );
    }
}


/**
 *	Data Export Library
 *	@package    CATS
 *	@subpackage Library
 */
class Export
{
    private $_siteID;
    private $_dataItemType;
    private $_separator;
    private $_rs;
    private $_IDs;


    public function __construct($dataItemType, $IDs, $separator, $siteID)
    {
        $this->_siteID = $siteID;
        $this->_dataItemType = $dataItemType;
        $this->_separator = $separator;
        $this->_IDs = $IDs;
    }


    /**
     * Creates and returns output to be written to a CSV / etc. file.
     *
     * @return string formatted output
     */
    public function getFormattedOutput()
    {
        switch ($this->_dataItemType)
        {
            case DATA_ITEM_CANDIDATE:
                $dataItem = new Candidates($this->_siteID);
                break;

            default:
                return false;
                break;
        }

        $this->_rs = $dataItem->getExport($this->_IDs);
        if (empty($this->_rs))
        {
            return false;
        }

        /* Column names. */
        $outputString = implode(
            $this->_separator, array_keys($this->_rs[0])
        ) . "\r\n";

        foreach ($this->_rs as $rowIndex => $row)
        {
            foreach ($row as $key => $value)
            {
                /* Escape any double-quotes and place the value inside
                 * double quotes.
                 */
                $this->_rs[$rowIndex][$key] = '"' . str_replace('"', '""', $value) . '"';
            }

            $outputString .= implode(
                $this->_separator, $this->_rs[$rowIndex]
            ) . "\r\n";
        }

        return $outputString;
    }
}
