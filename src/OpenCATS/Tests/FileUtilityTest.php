<?php
use PHPUnit\Framework\TestCase;

include_once('./lib/FileUtility.php');

class FileUtilityTest extends TestCase
{
    function testSizeToHuman()
    {
        $tests = array(
            array(1024,       false, 0, '1 KB'),
            array(2048,       false, 0, '2 KB'),
            array(1048576,    false, 0, '1 MB'),
            array(2097152,    false, 0, '2 MB'),
            array(1073741824, false, 0, '1 GB'),
            array(1024,       2,     0, '1 KB'),
            array(1048576,    2,     0, '1 MB'),
            array(1073741824, 2,     0, '1 GB'),
            array(1536,       1,     0, '1.5 KB'),
            array(1536,       2,     0, '1.5 KB'),
            array(1546,       2,     0, '1.51 KB'),
            array(1024,       false, 0, '1 KB'),
            array(1024,       3,     2, '0.001 MB'),
            array(0,          false, 0, '0 B'),
            array(0,          false, 1, '0 KB'),
            array(0,          false, 2, '0 MB'),
            array(0,          false, 3, '0 GB'),
            array(0,          false, 4, '0 TB'),
            array(0,          false, 5, '0 PB')
        );

        foreach ($tests as $key => $value)
        {
            $this->assertSame(
                FileUtility::sizeToHuman($value[0], $value[1], $value[2]),
                $value[3]
                );
        }
    }

    function testGetUniqueDirectory()
    {
        /* Get two random directory names; one with extra data and one
         * without.
         */
        $directoryA = FileUtility::getUniqueDirectory('attachments');
        $directoryB = FileUtility::getUniqueDirectory('attachments/', 'Extra Data!');

        /* Directories are also unique in time, with randomness added. */
        sleep(1);
        $directoryC = FileUtility::getUniqueDirectory('attachments');
        $directoryD = FileUtility::getUniqueDirectory('attachments');

        /* Make sure all directory names look like md5 strings. */
        $this->assertEquals(
            strlen($directoryA),
            32,
            sprintf("'%s' should be 32 characters long", $directoryA)
            );
        $this->assertEquals(
            strlen($directoryB),
            32,
            sprintf("'%s' should be 32 characters long", $directoryB)
            );
        $this->assertEquals(
            strlen($directoryC),
            32,
            sprintf("'%s' should be 32 characters long", $directoryB)
            );
        $this->assertEquals(
            strlen($directoryD),
            32,
            sprintf("'%s' should be 32 characters long", $directoryB)
            );

        /* Make sure extra data is actually being added (directory names
         * should not be identical).
         */
        $this->assertNotEquals(
            $directoryA,
            $directoryB,
            sprintf("'%s' should not equal '%s'", $directoryA, $directoryB)
            );
        $this->assertNotEquals(
            $directoryA,
            $directoryC,
            sprintf("'%s' should not equal '%s'", $directoryA, $directoryC)
            );
        $this->assertNotEquals(
            $directoryA,
            $directoryD,
            sprintf("'%s' should not equal '%s'", $directoryA, $directoryD)
            );
        $this->assertNotEquals(
            $directoryB,
            $directoryC,
            sprintf("'%s' should not equal '%s'", $directoryB, $directoryC)
            );
        $this->assertNotEquals(
            $directoryB,
            $directoryD,
            sprintf("'%s' should not equal '%s'", $directoryB, $directoryD)
            );
        $this->assertNotEquals(
            $directoryC,
            $directoryD,
            sprintf("'%s' should not equal '%s'", $directoryC, $directoryD)
            );

        /* No directory names should exist. */
        $this->assertFalse(
            file_exists('attachments/' . $directoryA),
            sprintf("'attachments/%s' should not exist", $directoryA)
            );
        $this->assertFalse(
            file_exists('attachments/' . $directoryB),
            sprintf("'attachments/%s' should not exist", $directoryB)
            );
        $this->assertFalse(
            file_exists('attachments/' . $directoryC),
            sprintf("'attachments/%s' should not exist", $directoryB)
            );
        $this->assertFalse(
            file_exists('attachments/' . $directoryD),
            sprintf("'attachments/%s' should not exist", $directoryB)
            );
    }
}
?>