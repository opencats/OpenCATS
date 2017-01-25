<?php

class Width 
{
    private $digit;
    private $unit;
    
    function __construct($digit, $unit = "px") {
        $this->digit = $digit;
        $this->unit = $unit;
    }
    
    function asString($makeLargerThanDisplayableArea = false) {
        if ($this->unit == 'px') {
            $out = $this->digit + ($makeLargerThanDisplayableArea ? 10 : 0);
        } else if ($this->unit == '%') {
            $out = $this->digit;
        } else {
            $out = $this->digit + ($makeLargerThanDisplayableArea ? 1 : 0);
        }
        return $out . $this->unit;
    }
    
    function getDigit() {
        return $this->digit;
    }
    
}
?>
