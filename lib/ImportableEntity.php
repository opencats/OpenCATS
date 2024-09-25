<?php

abstract class ImportableEntity
{
    protected $_db;

    protected $_siteID;

    abstract protected function add($dataNamed, $userID, $importID);

    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }

    public function prepareData($dataNamed)
    {
        $dataColumns = [];
        $data = [];

        foreach ($dataNamed as $dataColumn => $value) {
            $dataColumns[] = $dataColumn;
            $data[] = $this->_db->makeQueryStringOrNULL($value);
        }
        return [
            'data' => $data,
            'dataColumns' => $dataColumns,
        ];
    }
}
