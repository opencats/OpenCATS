<?php
namespace OpenCATS\UI;

class QuickActionMenu
{
    private $dataItemType;
    private $dataItemId;

    public function __construct($dataItemType, $dataItemId)
    {
        $this->dataItemType = $dataItemType;
        $this->dataItemId = $dataItemId;
    }

    public function getHtml()
    {
        $onClick = 'showHideSingleQuickActionMenu(';
        $onClick .= 'new ' . $this->getMenuType() . '('. implode(', ',  $this->getParameters()) .')';
        $onClick .= ');';
        echo '<a href="javascript:void(0);" onclick="'. $onClick . '"><img src="images/downward.gif" border="0"></a>';
    }

    protected function getParameters()
    {
        return array(
            $this->dataItemType,
            $this->dataItemId,
            'docjslib_getRealLeft(this)-20',
            'docjslib_getRealTop(this)+20'
        );
    }

    protected function getMenuType()
    {
        return 'quickAction.DefaultMenu';
    }
}
