<?php

include_once(LEGACY_ROOT . '/lib/ImportableEntity.php');

class ContactImport extends ImportableEntity
{
    public function __construct($siteID)
    {
       parent::__construct($siteID);
    }

    /**
     * Adds a record to the contacts table.
     *
     * @param array (field => value)
     * @param userID
     * @param importID
     * @return contactID
     */
    public function add($dataNamed, $userID, $importID)
    {
        $data = $this->prepareData($dataNamed);

        $sql = sprintf(
            "INSERT INTO contact (
                %s,
                entered_by,
                owner,
                site_id,
                date_created,
                date_modified,
                import_id
            )
            VALUES (
                %s,
                %s,
                %s,
                %s,
                NOW(),
                NOW(),
                %s
            )",
            implode(",\n", $data['dataColumns']),
            implode(",\n", $data['data']),
            $userID,
            $userID,
            $this->_siteID,
            $importID
        );
        $queryResult = $this->_db->query($sql);
        if (!$queryResult)
        {
            return -1;
        }

        return $this->_db->getLastInsertID();
    }
}