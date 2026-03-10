<?php

namespace Kematjaya\BackupBundle\Tests\EventListener;

use Kematjaya\BackupBundle\Event\AfterDumpEvent;
use Kematjaya\BackupBundle\EventListener\CompressListener;
use PHPUnit\Framework\TestCase;
use Spatie\DbDumper\DbDumper;

class CompressListenerTest extends TestCase
{
    public function testCompress(): void
    {
        $dumper = $this->createMock(DbDumper::class);
        $tempFile = tempnam(sys_get_temp_dir(), 'test_dump_');
        file_put_contents($tempFile, 'dummy content');

        $event = new AfterDumpEvent($dumper, $tempFile);
        
        $listener = new CompressListener();
        $listener->compress($event);

        if (class_exists(\ZipArchive::class)) {
            $expectedFile = $tempFile . '.zip';
        } else {
            $expectedFile = $tempFile . '.gz';
        }

        $this->assertEquals($expectedFile, $event->getFileName());
        $this->assertFileExists($expectedFile);
        $this->assertFileDoesNotExist($tempFile);

        if (file_exists($expectedFile)) {
            unlink($expectedFile);
        }
    }
}
