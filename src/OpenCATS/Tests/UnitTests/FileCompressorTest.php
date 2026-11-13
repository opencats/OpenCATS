<?php

use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

require_once LEGACY_ROOT . '/constants.php';
require_once LEGACY_ROOT . '/lib/FileUtility.php';
require_once LEGACY_ROOT . '/lib/FileCompressor.php';

class FileCompressorTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testArchiveRoundTripsAFileFromDisk(): void
    {
        $directory = sys_get_temp_dir() . '/opencats-zip-' . bin2hex(random_bytes(8));
        self::assertTrue(mkdir($directory, 0700));
        define('CATS_TEMP_DIR', $directory);
        $source = $directory . '/source.txt';
        $archive = $directory . '/backup.zip';
        $contents = "Backup fixture\nSecond line\n";

        try
        {
            self::assertSame(strlen($contents), file_put_contents($source, $contents));
            $creator = new ZipFileCreator($archive);
            self::assertTrue($creator->open());
            self::assertTrue($creator->addFileFromDisk('fixture.txt', $source));
            self::assertTrue($creator->finalize());

            $extractor = new ZipFileExtractor($archive);
            self::assertTrue($extractor->open());
            self::assertSame($contents, $extractor->getFile(0));
        }
        finally
        {
            foreach (array($source, $archive) as $path)
            {
                if (is_file($path))
                {
                    unlink($path);
                }
            }
            rmdir($directory);
        }
    }
}
