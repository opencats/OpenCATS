<?php
/*
 * CATS
 * CATS UnitTestCase Extension for SimpleTest
 *
 * Copyright (C) 2006 - 2007 Cognizo Technologies, Inc.
 * All rights reserved.
 *
 * $Id: CATSUnitTestCase.php 1479 2007-01-17 00:22:21Z will $
 */


class CATSUnitTestCase extends UnitTestCase
{
    public function assertPatternIn($pattern, $subject, $message = "%s")
    {
        return $this->assert(
            new PatternExpectation($pattern),
            $subject,
            $message
        );
    }

    public function assertNoPatternIn($pattern, $subject, $message = "%s")
    {
        return $this->assert(
            new NoPatternExpectation($pattern),
            $subject,
            $message
        );
    }
}

?>
