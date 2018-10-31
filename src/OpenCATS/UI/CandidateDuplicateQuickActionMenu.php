<?php
namespace OpenCATS\UI;

class CandidateDuplicateQuickActionMenu extends QuickActionMenu
{
    private $mergeUrl;
    private $removeUrl;

    public function __construct($dataItemType, $dataItemId, $accessLevel, $mergeUrl, $removeUrl)
    {
        parent::__construct($dataItemType, $dataItemId, $accessLevel);
        $this->mergeUrl = $mergeUrl;
        $this->removeUrl = $removeUrl;
    }

    protected function getMenuType()
    {
        return 'quickAction.CandidateDuplicateMenu';
    }

    protected function getParameters()
    {
        $parameters = parent::getParameters();
        $parameters[] = "'". $this->mergeUrl ."'";
        $parameters[] = "'". $this->removeUrl ."'";
        return $parameters;
    }
}