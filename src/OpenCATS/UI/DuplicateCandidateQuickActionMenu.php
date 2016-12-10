<?php
namespace OpenCATS\UI;

class DuplicateCandidateQuickActionMenu extends QuickActionMenu
{
    private $mergeUrl;
    private $removeUrl;

    public function __construct($dataItemType, $dataItemId, $mergeUrl, $removeUrl)
    {
        parent::__construct($dataItemType, $dataItemId);
        $this->mergeUrl = $mergeUrl;
        $this->removeUrl = $removeUrl;
    }

    protected function getMenuType()
    {
        return 'quickAction.DuplicateCandidateMenu';
    }

    protected function getParameters()
    {
        $parameters = parent::getParameters();
        $parameters[] = "'". $this->mergeUrl .";'";
        $parameters[] = "'". $this->removeUrl .";'";
        return $parameters;
    }
}