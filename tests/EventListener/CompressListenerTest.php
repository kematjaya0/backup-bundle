<?php

namespace Kematjaya\BackupBundle\Tests\EventListener;

use Kematjaya\BackupBundle\Event\AfterDumpEvent;
use Kematjaya\BackupBundle\EventListener\CompressListener;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Spatie\DbDumper\DbDumper;

class CompressListenerTest extends TestCase
{
    public function testCompressGzipLevelClamping(): void
    {
        $source = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'source.txt';
        file_put_contents($source, 'test');
        $event = $this->createMock(AfterDumpEvent::class);
        $event->method('getFileName')->willReturn($source);
        // invoke private compressGzip via reflection with high level
        $listener = new CompressListener();
        $ref = new \ReflectionMethod(CompressListener::class, 'compressGzip');
        $ref->setAccessible(true);
        $dest = $ref->invoke($listener, $source, null, 99);
        $this->assertFileExists($dest);
        $this->assertStringEndsWith('.gz', $dest);
        unlink($dest);
        unlink($source);
    }

    public function testCompressLogsInfo(): void
    {
        $source = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'source.txt';
        file_put_contents($source, 'test');
        $event = $this->createMock(AfterDumpEvent::class);
        $event->method('getFileName')->willReturn($source);
        $event->expects($this->once())
            ->method('setFileName')
            ->with($this->callback(function($path) use ($source) {
                return is_file($path) && $path !== $source;
            }));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('info');
        $listener = new CompressListener($logger);
        $listener->compress($event);
        // cleanup
        if (file_exists($source . '.gz')) {
            unlink($source . '.gz');
        }
        if (file_exists($source)) {
            unlink($source);
        }
    }
}
