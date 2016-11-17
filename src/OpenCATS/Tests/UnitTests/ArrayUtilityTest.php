<?php
use PHPUnit\Framework\TestCase;

include_once('./lib/ArrayUtility.php');

class ArrayUtilityTest extends TestCase
{
    /* Tests for implodeRange(). */
    function testImplodeRange()
    {
        $pieces = array(
            'Zero',
            'One',
            'Two',
            'Three',
            'Four',
            'Five'
        );

        $result = ArrayUtility::implodeRange(' ', $pieces, 0, 5);
        $this->assertSame($result, 'Zero One Two Three Four Five');

        $result = ArrayUtility::implodeRange(' ', $pieces, 0, 4);
        $this->assertSame($result, 'Zero One Two Three Four');

        $result = ArrayUtility::implodeRange(' ', $pieces, 1, 4);
        $this->assertSame($result, 'One Two Three Four');

        $result = ArrayUtility::implodeRange(' ', $pieces, 1, 3);
        $this->assertSame($result, 'One Two Three');

        $result = ArrayUtility::implodeRange(' ', $pieces, 2, 3);
        $this->assertSame($result, 'Two Three');

        $result = ArrayUtility::implodeRange(' ', $pieces, 2, 2);
        $this->assertSame($result, 'Two');

        $result = ArrayUtility::implodeRange(' ', $pieces, 0, 6);
        $this->assertSame($result, 'Zero One Two Three Four Five');

        $result = ArrayUtility::implodeRange(' ', $pieces, -500, 500);
        $this->assertSame($result, 'Zero One Two Three Four Five');

        $result = ArrayUtility::implodeRange(', ', $pieces, -500, 500);
        $this->assertSame($result, 'Zero, One, Two, Three, Four, Five');
    }
}