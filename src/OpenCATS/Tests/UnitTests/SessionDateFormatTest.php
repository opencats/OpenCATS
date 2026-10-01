<?php
use PHPUnit\Framework\TestCase;

include_once(LEGACY_ROOT . '/constants.php');
include_once(LEGACY_ROOT . '/lib/Session.php');

class SessionDateFormatTest extends TestCase
{
    function testSetTimeDateLocalizationAcceptsLegacyBoolean()
    {
        $session = new CATSSession();

        $session->setTimeDateLocalization(0, true);
        $this->assertSame(DATE_FORMAT_DDMMYY, $session->getDateFormat());
        $this->assertTrue($session->isDateDMY());

        $session->setTimeDateLocalization(0, false);
        $this->assertSame(DATE_FORMAT_MMDDYY, $session->getDateFormat());

        $session->setTimeDateLocalization(0, DATE_FORMAT_YYYYMMDD);
        $this->assertSame(DATE_FORMAT_YYYYMMDD, $session->getDateFormat());
    }

    /* A session serialized before the upgrade stores a boolean _dateDMY;
     * _dateFormat then keeps its M-D-Y default until __wakeup() runs. */
    function testLegacySessionKeepsDateFormat()
    {
        foreach (array(1 => DATE_FORMAT_DDMMYY, 0 => DATE_FORMAT_MMDDYY) as $isDMY => $expected)
        {
            $serialized = str_replace(
                '_dateDMY";N;',
                '_dateDMY";b:' . $isDMY . ';',
                serialize(new CATSSession())
            );
            $this->assertStringContainsString('_dateDMY";b:', $serialized);
            $session = unserialize($serialized);

            $this->assertSame($expected, $session->getDateFormat());
            $this->assertStringNotContainsString('_dateDMY";b:', serialize($session));
        }
    }
}
?>
