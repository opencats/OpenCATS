<?php
use PHPUnit\Framework\TestCase;

include_once('./lib/AJAXInterface.php');

class AJAXInterfaceTest extends TestCase
{
    function testIsRequiredIDValid()
    {
        $AJAXInterface = new AJAXInterface();

        /* Make sure an unset key does not pass. */
        $random = md5('random' . time());
        $this->assertFalse(
            $AJAXInterface->isRequiredIDValid($random, true, true),
            sprintf("\$_POST['%s'] should not exist and should not be a valid required ID", $random)
            );

        /* Make sure -0, non-numeric strings, and symbols never pass. */
        $invalidIDs = array('-0', 'test', '0abc', '1abc', '-abc', '$');
        foreach ($invalidIDs as $ID)
        {
            $_REQUEST['isRequiredIDValidTest'] = $ID;
            $this->assertFalse(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', true, true),
                sprintf("'%s' should not be a valid required ID", $ID)
                );
            $this->assertFalse(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', true, false),
                sprintf("'%s' should not be a valid required ID", $ID)
                );
            $this->assertFalse(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', false, true),
                sprintf("'%s' should not be a valid required ID", $ID)
                );
            $this->assertFalse(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', false, false),
                sprintf("'%s' should not be a valid required ID", $ID)
                );
        }

        /* Make sure we don't allow '0' if $allowZero is false. */
        $invalidIDs = array(0, '0');
        foreach ($invalidIDs as $ID)
        {
            $_REQUEST['isRequiredIDValidTest'] = $ID;
            $this->assertFalse(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', false, true),
                sprintf("'%s' should not be a valid required ID with \$allowZero false", $ID)
                );
            $this->assertFalse(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', false, false),
                sprintf("'%s' should not be a valid required ID with \$allowZero false", $ID)
                );
        }

        /* Make sure we don't allow negatives if $allowNegative is false. */
        $invalidIDs = array(-1, -100, '-1', '-100');
        foreach ($invalidIDs as $ID)
        {
            $_REQUEST['isRequiredIDValidTest'] = $ID;
            $this->assertFalse(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', true, false),
                sprintf("'%s' should not be a valid required ID with \$allowNegative false", $ID)
                );
            $this->assertFalse(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', false, false),
                sprintf("'%s' should not be a valid required ID with \$allowNegative false", $ID)
                );
        }

        /* Make sure any positive, negative, or 0 number passes valid ID checks
         * if $allowZero and $allowNegative are true.
         */
        $validIDs = array(1, 100, -1, -100, 0, '0', '-100', '1', '65535');
        foreach ($validIDs as $ID)
        {
            $_REQUEST['isRequiredIDValidTest'] = $ID;
            $this->assertTrue(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', true, true),
                sprintf("'%s' should be a valid required ID", $ID)
                );
        }

        /* Make sure any positive number always passes valid ID checks
         * regardless of $allowZero and $allowNegative.
         */
        $validIDs = array(1, 100, '1', '65535');
        foreach ($validIDs as $ID)
        {
            $_REQUEST['isRequiredIDValidTest'] = $ID;
            $this->assertTrue(
                $AJAXInterface->isRequiredIDValid('isRequiredIDValidTest', false, false),
                sprintf("'%s' should be a valid required ID", $ID)
                );
        }
    }

    function testIsOptionalIDValid()
    {
        $AJAXInterface = new AJAXInterface();

        /* Make sure an unset key does not pass. */
        $random = md5('random' . time());
        $this->assertFalse(
            $AJAXInterface->isOptionalIDValid($random),
            sprintf("\$_POST['%s'] should not exist and should not be a valid optional ID", $random)
            );

        /* Make sure 0, -0, negative numbers, non-numeric strings, and symbols
         * never pass.
         */
        $invalidIDs = array(
            0, -1, -100, '0', '-0', '-1', '-100',
            'test', '0abc', '1abc', '-abc', '$'
        );
        foreach ($invalidIDs as $ID)
        {
            $_REQUEST['isRequiredIDValidTest'] = $ID;
            $this->assertFalse(
                $AJAXInterface->isOptionalIDValid('isOptionalIDValidTest'),
                sprintf("'%s' should not be a valid optional ID", $ID)
                );
        }

        /* Make sure any positive number always passes. */
        $validIDs = array(1, 100, '1', '65535');
        foreach ($validIDs as $ID)
        {
            $_REQUEST['isOptionalIDValidValidTest'] = $ID;
            $this->assertTrue(
                $AJAXInterface->isOptionalIDValid('isOptionalIDValidValidTest'),
                sprintf("'%s' should be a valid optional ID", $ID)
                );
        }

        /* Make sure 'NULL' always passes. */
        $_REQUEST['isOptionalIDValidValidTest'] = 'NULL';
        $this->assertTrue(
            $AJAXInterface->isOptionalIDValid('isOptionalIDValidValidTest'),
            "'NULL' should be a valid optional ID"
            );
    }
}
?>