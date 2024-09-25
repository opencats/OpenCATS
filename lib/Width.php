<?php

class Width
{
    private $digit;

    private $unit;

    public function __construct($digit, $unit = "px")
    {
        $this->digit = $digit;
        $this->unit = $unit;
    }

    public function asString($makeLargerThanDisplayableArea = false)
    {
        if ($this->unit == 'px') {
            $out = $this->digit + ($makeLargerThanDisplayableArea ? 10 : 0);
        } elseif ($this->unit == '%') {
            $out = $this->digit;
        } else {
            $out = $this->digit + ($makeLargerThanDisplayableArea ? 1 : 0);
        }
        return $out . $this->unit;
    }

    public function getDigit()
    {
        return $this->digit;
    }
}
