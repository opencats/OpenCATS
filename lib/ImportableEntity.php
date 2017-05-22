<?php

abstract class ImportableEntity
{
    protected $_db;
    protected $_siteID;

    abstract protected function add($dataNamed, $userID, $importID, $encoding);

    public function __construct($siteID)
    {
        $this->_siteID = $siteID;
        $this->_db = DatabaseConnection::getInstance();
    }

    public function prepareData($dataNamed, $encoding)
    {
        $dataColumns = array();
        $data = array();

        foreach ($dataNamed AS $dataColumn => $d) {
            $dataColumns[] = $dataColumn;
            if ($encoding != "") {
                $data[] = iconv($encoding, 'UTF-8', $this->_db->makeQueryStringOrNULL($d));
            } else {
                $data[] = $this->_db->makeQueryStringOrNULL($d);
            }
        }
        return array('data' => $data, 'dataColumns' => $dataColumns);
    }
}

