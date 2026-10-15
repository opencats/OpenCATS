<?php

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

require_once __DIR__ . '/../Support/AuthorizationTestCase.php';

#[RunTestsInSeparateProcesses]
#[PreserveGlobalState(false)]
class MRUTest extends AuthorizationTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        require_once './lib/MRU.php';
    }

    public function testGetFormattedEscapesStoredMarkup(): void
    {
        $payload = '<svg/onload=alert()>';
        $this->assertLessThanOrEqual(MRU_ITEM_LENGTH, mb_strlen($payload));
        $this->assertFormattedText($payload, '&lt;svg/onload=alert()&gt;');
    }

    public function testGetFormattedPreservesBenignText(): void
    {
        $this->assertFormattedText('Jane Müller', 'Jane Müller');
    }

    public function testGetFormattedPreservesTextAtLengthLimit(): void
    {
        $boundary = '&' . str_repeat('ü', MRU_ITEM_LENGTH - 1);
        $escaped = '&amp;' . str_repeat('ü', MRU_ITEM_LENGTH - 1);

        $this->assertFormattedText($boundary, $escaped);
    }

    public function testGetFormattedTruncatesBeforeEscaping(): void
    {
        $boundary = '&' . str_repeat('ü', MRU_ITEM_LENGTH - 1);
        $escaped = '&amp;' . str_repeat('ü', MRU_ITEM_LENGTH - 1);

        $this->assertFormattedText($boundary . 'extra', $escaped . '..');
    }

    private function assertFormattedText($text, $expected): void
    {
        $url = 'index.php?m=candidates&amp;a=show&amp;candidateID=42';
        $this->db->method('getAllAssoc')->willReturn(array(
            array('dataItemText' => $text, 'URL' => $url)
        ));

        $mru = new MRU(10);
        $this->assertSame(
            '<a href="' . $url . '" style="text-decoration: none;">' . $expected . '</a>',
            $mru->getFormatted()
        );
        $this->assertSame(array(), $this->queries);
    }
}
