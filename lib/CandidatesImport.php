<?php

include_once(LEGACY_ROOT . '/lib/ImportableEntity.php');

class CandidatesImport extends ImportableEntity
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Adds a record to the candidates table.
     *
     * @param array (field => value)
     * @param userID
     * @param importID
     * @return int candidateID
     */
    public function add($dataNamed, $userID, $importID)
    {
        $data = $this->prepareData($dataNamed);

        $sql = sprintf(
            "INSERT INTO candidate (
                %s,
                can_relocate,
                entered_by,
                owner,
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
            0,
            $userID,
            $userID,
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