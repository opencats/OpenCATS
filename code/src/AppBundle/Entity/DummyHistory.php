<?php
namespace AppBundle\Entity;

include_once (LEGACY_ROOT . '/lib/History.php');
// FIXME: To be removed once we abstract session from history
class DummyHistory extends \History
{
    public function __construct($siteID)
    {
    }

    public function storeHistoryNew($dataItemType, $dataItemID)
    {
    }
}