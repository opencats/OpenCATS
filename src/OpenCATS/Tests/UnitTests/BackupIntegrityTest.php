<?php

use PHPUnit\Framework\TestCase;

require_once LEGACY_ROOT . '/constants.php';
require_once LEGACY_ROOT . '/lib/FileCompressor.php';

class BackupIntegrityTest extends TestCase
{
    public function testAttachmentsOnlyBackupExcludesItselfAndUpdatesItsOwnMetadata(): void
    {
        $fixture = sys_get_temp_dir() . '/opencats-backup-' . bin2hex(random_bytes(8));
        mkdir($fixture . '/attachments/archive', 0700, true);
        mkdir($fixture . '/attachments/documents', 0700);
        mkdir($fixture . '/temp', 0700);
        symlink(getcwd() . '/lib', $fixture . '/lib');
        symlink(getcwd() . '/constants.php', $fixture . '/constants.php');
        symlink(getcwd() . '/modules', $fixture . '/modules');
        $archive = $fixture . '/attachments/archive/catsbackup.bak';
        $contents = 'Synthetic attachment for the backup integrity test.';
        file_put_contents($fixture . '/attachments/documents/fixture.txt', $contents);

        try
        {
            $process = proc_open(array(PHP_BINARY, __DIR__ . '/../Support/runAuthorizationAjax.php'),
                array(0 => array('pipe', 'r'), 1 => array('pipe', 'w'), 2 => array('pipe', 'w')), $pipes);
            fwrite($pipes[0], json_encode(array(
                'endpoint' => 'backup', 'level' => ACCESS_LEVEL_SA, 'fixture' => $fixture,
                'backup' => array('attachmentID' => 42, 'contentType' => 'catsbackup', 'dataItemType' => DATA_ITEM_COMPANY,
                    'dataItemID' => 7, 'directoryName' => 'archive/'),
                'rows' => array(
                    array('attachment_id' => 42, 'directory_name' => 'archive/', 'stored_filename' => 'catsbackup.bak'),
                    array('attachment_id' => 73, 'directory_name' => 'documents', 'stored_filename' => 'fixture.txt')
                )
            ), JSON_THROW_ON_ERROR));
            fclose($pipes[0]);
            $output = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            self::assertSame(0, proc_close($process), $stderr . $output);
            $result = json_decode($output, true, 512, JSON_THROW_ON_ERROR);
            self::assertSame(array(), $result['issues']);
            self::assertStringContainsString('Backup Finished', $result['output']);

            $extractor = new ZipFileExtractor($archive);
            self::assertTrue($extractor->open());
            self::assertSame(array('attachments/documents/fixture.txt'),
                array_column($extractor->getMetaData()['centralDirectory'], 'filename'));
            self::assertSame($contents, $extractor->getFile(0));

            $writes = array_values(array_filter($result['writes'],
                fn($sql) => preg_match('/^UPDATE\s+attachment\b/', $sql)));
            self::assertCount(1, $writes);
            self::assertMatchesRegularExpression('/file_size_kb = ' . (int) (filesize($archive) / 1024) .
                ",\\s+md5_sum = '" . md5_file($archive) . "'\\s+WHERE\\s+attachment_id = 42$/", $writes[0]);
        }
        finally
        {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($fixture,
                FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($files as $file)
            {
                if ($file->isDir() && !$file->isLink())
                {
                    rmdir($file->getPathname());
                }
                else
                {
                    unlink($file->getPathname());
                }
            }
            rmdir($fixture);
        }
    }
}
