<?php
/*
 * CATS
 * CATS AJAXTestCase Extension for SimpleTest
 *
 * Copyright (C) 2005 - 2007 Cognizo Technologies, Inc.
 * All rights reserved.
 *
 * $Id: CATSAJAXTestCase.php 1854 2007-02-19 01:54:55Z will $
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
