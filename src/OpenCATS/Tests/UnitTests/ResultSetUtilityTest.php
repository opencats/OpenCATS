<?php
use PHPUnit\Framework\TestCase;

include_once('./lib/ResultSetUtility.php');

class ResultSetUtilityTest extends TestCase
{
    /* Tests for findRowByColumnValue(). */
    function testFindRowByColumnValue()
    {
        $input = array(
            0   =>  array(
                'ID'    => 100,
                'Name'  => 'Cat',
                'Sound' => 'Meow',
                'Type'  => 'Mammal'
            ),
            1   =>  array(
                'ID'    => 200,
                'Name'  => 'Dog',
                'Sound' => 'Bark',
                'Type'  => 'Mammal'
            ),
            2  =>  array(
                'ID'    => 300,
                'Name'  => 'Wolf',
                'Sound' => 'Howl',
                'Type'  => 'Mammal'
            ),
            3   =>  array(
                'ID'    => 400,
                'Name'  => 'Cow',
                'Sound' => 'Moo',
                'Type'  => 'Mammal'
            ),
            4 =>  array(
                'ID'    => 500,
                'Name'  => 'Snake',
                'Sound' => 'Hiss',
                'Type'  => 'Reptile'
            )
        );

        /* Test simple 'finding' functionality. */
        $this->assertSame(
            ResultSetUtility::findRowByColumnValue($input, 'ID', 100),
            0
            );
        $this->assertSame(
            ResultSetUtility::findRowByColumnValue($input, 'ID', 200),
            1
            );
        $this->assertSame(
            ResultSetUtility::findRowByColumnValue($input, 'ID', 300),
            2
            );
        $this->assertSame(
            ResultSetUtility::findRowByColumnValue($input, 'ID', 400),
            3
            );
        $this->assertSame(
            ResultSetUtility::findRowByColumnValue($input, 'ID', 500),
            4
            );
        $this->assertSame(
            ResultSetUtility::findRowByColumnValue($input, 'ID', 500.0),
            4
            );
        $this->assertSame(
            ResultSetUtility::findRowByColumnValue($input, 'ID', '500'),
            4
            );
        $this->assertSame(
            ResultSetUtility::findRowByColumnValue($input, 'Type', 'Mammal'),
            0
            );
        $this->assertSame(
            ResultSetUtility::findRowByColumnValue($input, 'Sound', 'Hiss'),
            4
            );
        $this->assertSame(
            ResultSetUtility::findRowByColumnValue($input, 'ID', 600),
            false
            );

        /* Test skipping. */
        $this->assertSame(
            ResultSetUtility::findRowByColumnValue($input, 'Type', 'Mammal', 1),
            1
            );
        $this->assertSame(
            ResultSetUtility::findRowByColumnValue($input, 'Type', 'Mammal', 2),
            2
            );


        /* Test strict matching. */
        $this->assertSame(
            ResultSetUtility::findRowByColumnValueStrict($input, 'Sound', 'Hiss'),
            4
            );
        $this->assertSame(
            ResultSetUtility::findRowByColumnValueStrict($input, 'ID', '500'),
            false
            );
        $this->assertSame(
            ResultSetUtility::findRowByColumnValueStrict($input, 'ID', 500.0),
            false
            );

        /* Just in case strict and non-strict functions aren't identical... */
        $this->assertSame(
            ResultSetUtility::findRowByColumnValueStrict($input, 'Type', 'Mammal', 1),
            1
            );
        $this->assertSame(
            ResultSetUtility::findRowByColumnValueStrict($input, 'Type', 'Mammal', 2),
            2
            );
    }
}
?>