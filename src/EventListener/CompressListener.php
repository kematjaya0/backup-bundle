<?php

namespace Kematjaya\BackupBundle\EventListener;

use Kematjaya\BackupBundle\Event\AfterDumpEvent;
use Kematjaya\BackupBundle\Event\BackupEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CompressListener implements EventSubscriberInterface
{

    public static function getSubscribedEvents():array
    {
        return [
            BackupEvents::AFTER_DUMP => "compress"
        ];
    }

    public function compress(AfterDumpEvent $evt):void
    {
        $sourcePath = $evt->getFileName();
        if (!file_exists($sourcePath)) {
            return;
        }

        $destinationPath = $this->compressGzip($sourcePath);
        $evt->setFileName($destinationPath);

        if (file_exists($sourcePath)) {
            @unlink($sourcePath);
        }
    }

    protected function compressGzip(string $sourcePath, ?string $destinationPath = null, int $level = 9): string
    {
        if (!file_exists($sourcePath)) {
            throw new \InvalidArgumentException("File not found: $sourcePath");
        }

        if ($destinationPath === null) {
            $destinationPath = $sourcePath . '.gz';
        }

        $inFile  = fopen($sourcePath, 'rb');
        if (!$inFile) {
            throw new \RuntimeException("failed open source file.");
        }

        $outFile = gzopen($destinationPath, 'wb' . $level);
        if (!$outFile) {
            fclose($inFile);
            throw new \RuntimeException("create gzip failed.");
        }

        while (!feof($inFile)) {
            gzwrite($outFile, fread($inFile, 1024 * 512)); // 512KB chunk
        }

        fclose($inFile);
        gzclose($outFile);

        return $destinationPath;
    }
}