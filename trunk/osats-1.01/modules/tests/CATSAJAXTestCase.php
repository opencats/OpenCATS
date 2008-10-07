<?php
/*
   * OSATS
   *
   *
   *
*/

class CATSAJAXTestCase extends CATSWebTestCase
{
    public function runXMLLoadAssertions($xml, $AJAXErrors = false,
        $noAJAXAssertions = false)
    {
        $this->assertHTTPResponseOk();
        $this->assertNoQueryErrors();
        $this->assertNoPHPErrors();

        if ($noAJAXAssertions)
        {
            return;
        }

        if (!$AJAXErrors)
        {
            $this->assertNoAJAXErrors($xml);
        }
        else
        {
            $this->assertAJAXErrors($xml);
        }
    }

    public function getSimpleXML()
    {
        return simplexml_load_string($this->getRawSource());
    }

    public function assertNoAJAXErrors($xml, $message = '%s')
    {
        $message = sprintf($message, 'No AJAX errors should occur');
        return $this->assertTrue(
            ($xml->errorcode == 0) && ($xml->errormessage == ''),
            $message
        );
    }

    public function assertAJAXErrors($xml, $message = '%s')
    {
        $message = sprintf($message, 'No AJAX errors should occur');
        return $this->assertTrue(
            ($xml->errorcode != 0) && ($xml->errormessage != ''),
            $message
        );
    }
}

?>