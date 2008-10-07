<?php
/*
   * OSATS
   *
   *
   *
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