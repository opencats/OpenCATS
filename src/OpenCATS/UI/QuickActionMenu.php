<?php
namespace OpenCATS\UI;

class QuickActionMenu
{
    private $dataItemType;
    private $dataItemId;

    public function __construct($dataItemType, $dataItemId, $accessLevel)
    {
        $this->dataItemType = $dataItemType;
        $this->dataItemId = $dataItemId;
        $this->accessLevel = $accessLevel;
    }

    public function getHtml()
    {
        if( $this->accessLevel >= ACCESS_LEVEL_READ) {
            $onClick = 'showHideSingleQuickActionMenu(';
            $onClick .= 'new ' . $this->getMenuType() . '('. implode(', ',  $this->getParameters()) .')';
            $onClick .= ');';
            echo '<a href="javascript:void(0);" onclick="'. $onClick . '"><img src="images/downward.gif" border="0"></a>';
        }
    }

    protected function getParameters()
    {
        $addToPipeline = ($_SESSION['CATS']->getAccessLevel('pipelines.addToPipeline') > ACCESS_LEVEL_READ) ? 1 : 0;
        $editCandidate = ($_SESSION['CATS']->getAccessLevel('candidates.edit') > ACCESS_LEVEL_READ) ? 1 : 0;
        
        return array(
            $this->dataItemType,
            $this->dataItemId,
            'docjslib_getRealLeft(this)-20',
            'docjslib_getRealTop(this)+20',
            '{pipelines_addToPipeline: '.$addToPipeline.'}'
        );
    }

    protected function getMenuType()
    {
        return 'quickAction.DefaultMenu';
    }
}
