<?php
/**
 * OSATS
 */

/**
 *	Most Recently Used List Library
 *	@package    CATS
 *	@subpackage Library
 */
class MRU
{
    protected $_userID = -1;
    protected $_siteID = -1;


    public function __construct($userID, $siteID)
    {
        $this->_userID = $userID;
        $this->_siteID = $siteID;
    }


    /**
     * Add an item to the MRU list and prune old entries.
     *
     * @param integer data item type
     * @param integer data item ID
     * @param string text to display next to icon
     * @return new MRU ID, or -1 on failure.
     */
    public function addEntry($dataItemType, $dataItemID, $dataItemText)
    {
        /* Locally initiated because the MRU object is stored in the session,
        and database references can not be stored in the session. */
        $db = DatabaseConnection::getInstance();


        $URL = self::makeMRUURL($dataItemType, $dataItemID);

        /* If this item is already in the MRU, remove it. */
        $this->removeEntry($dataItemType, $dataItemID);

        $sql = sprintf(
            "INSERT INTO mru (
                site_id,
                user_id,
                data_item_type,
                data_item_text,
                url,
                date_created
            )
            VALUES (
                %s,
                %s,
                %s,
                %s,
                %s,
                NOW()
            )",
            $this->_siteID,
            $this->_userID,
            $db->makeQueryInteger($dataItemType),
            $db->makeQueryString($dataItemText),
            $db->makeQueryString($URL)
        );

        $queryResult = $db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        /* Remove old entries. */
        $this->pruneMRU();

        return $db->getLastInsertID();
    }

    /**
     * Builds an HTML block of MRU item links.
     *
     * @return string HTML block of MRU items
     */
    public function getFormatted()
    {
        /* Locally initiated because the MRU object is stored in the session,
        and database references can not be stored in the session. */
        $db = DatabaseConnection::getInstance();

        $HTML = array();

        $sql = sprintf(
            "SELECT
                data_item_text as dataItemText,
                url as URL
            FROM
                mru
            WHERE
                site_id = %s
            AND
                user_id = %s
            ORDER BY
                mru_id DESC",
            $this->_siteID,
            $this->_userID
        );

        $rs = $db->getAllAssoc($sql);

        foreach ($rs as $rowIndex => $row)
        {
            if (strlen($row['dataItemText']) > MRU_ITEM_LENGTH)
            {
                $rs[$rowIndex]['dataItemText'] = substr(
                    $row['dataItemText'], 0, MRU_ITEM_LENGTH
                ) . "..";
            }

            // FIXME: URL is already htmlspecialchars()d... bad design. Id
            //        should be done here.
            $HTML[] = sprintf(
                '<a href="%s" style="text-decoration: none;">%s</a>',
                $row['URL'],
                htmlspecialchars($rs[$rowIndex]['dataItemText'])
            );
        }

        return implode(
            $HTML, '&nbsp;<span style="color: orange;">|</span>&nbsp;'
        );
    }

    /**
     * Removes an existing MRU entry.
     *
     * @param flag data item type
     * @param integer data item ID
     * @return void
     */
     public function removeEntry($dataItemType, $dataItemID)
     {
        /* Locally initiated because the MRU object is stored in the session,
        and database references can not be stored in the session. */
        $db = DatabaseConnection::getInstance();

        $URL = self::makeMRUURL($dataItemType, $dataItemID);

        $sql = sprintf(
            "DELETE FROM
                mru
            WHERE
                url = %s
            AND
                user_id = %s
            AND
                site_id = %s",
            $db->makeQueryString($URL),
            $this->_userID,
            $this->_siteID
        );

        $db->query($sql);
     }

    /**
     * Removes old MRU entries.
     *
     * @return void
     */
    private function pruneMRU()
    {
        /* Locally initiated because the MRU object is stored in the session,
        and database references can not be stored in the session. */
        $db = DatabaseConnection::getInstance();

        $sql = sprintf(
            "SELECT
                COUNT(*) AS count
            FROM
                mru
            WHERE
                site_id = %s
            AND
                user_id = %s",
            $this->_siteID,
            $this->_userID
        );
        $rs = $db->getAssoc($sql);

        $count = $rs['count'];
        /* FIXME: Remove multiple entries at once if we're more than one over?
         * Should be fairly easy; just find how much over we are, order ASC by
         * mruID, limit by how much over, then delete them all.
         */
        while ($count > MRU_MAX_ITEMS)
        {
            /* Remove the least recent entry. */
            $sql = sprintf(
                "SELECT
                    mru_id AS mruID
                FROM
                    mru
                WHERE
                    site_id = %s
                AND
                    user_id = %s
                ORDER BY
                    mru_id ASC
                LIMIT 1",
                $this->_siteID,
                $this->_userID
            );
            $rs = $db->getAssoc($sql);

            /* Should never be empty, but just in case... */
            if (!empty($rs))
            {
                $sql = sprintf(
                    "DELETE FROM
                        mru
                    WHERE
                        mru_id = %s",
                    $rs['mruID']
                );
                $db->query($sql);
            }

            --$count;
        }
    }

    /**
     * Makes an MRU URL.
     *
     * @param string dataItemType (constant describing a data item type)
     * @param string dataItemID (candidateID, companyID etc)
     * @return string URL to be used for an MRU item
     */
    private function makeMRUURL($dataItemType, $dataItemID)
    {
        $URL = osatutil::getIndexName();

        switch ($dataItemType)
        {
            case DATA_ITEM_CANDIDATE:
                $URL .= '?m=candidates&amp;a=show&amp;candidateID=';
                break;

            case DATA_ITEM_JOBORDER:
                $URL .= '?m=joborders&amp;a=show&amp;jobOrderID=';
                break;

            case DATA_ITEM_COMPANY:
                $URL .= '?m=companies&amp;a=show&amp;companyID=';
                break;

            case DATA_ITEM_CONTACT:
                $URL .= '?m=contacts&amp;a=show&amp;contactID=';
                break;

            case DATA_ITEM_LIST:
                $URL .= '?m=lists&amp;a=showList&amp;savedListID=';
                break;

            default:
                return '';
                break;
        }

        $URL .= $dataItemID;
        return $URL;
    }
}
