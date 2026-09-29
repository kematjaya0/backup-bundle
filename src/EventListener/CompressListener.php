<?php

namespace Kematjaya\BackupBundle\EventListener;

use Kematjaya\BackupBundle\Event\AfterDumpEvent;
use Kematjaya\BackupBundle\Event\BackupEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class CompressListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents():array
    {
        return [
            BackupEvents::AFTER_DUMP => "compress"
        ];
    }

    private LoggerInterface $logger;

    public function __construct(?LoggerInterface $logger = null)
    {
        $this->logger = $logger ?? new NullLogger();
    }

    public function compress(AfterDumpEvent $evt):void
    {
        $sourcePath = $evt->getFileName();
        if (!file_exists($sourcePath)) {
            return;
        }

        if (class_exists(\ZipArchive::class)) {
            $destinationPath = $this->compressZip($sourcePath);
        } else {
            $destinationPath = $this->compressGzip($sourcePath);
        }

        $evt->setFileName($destinationPath);

        if (file_exists($sourcePath)) {
            $this->logger->info('Compressed dump to {file}', ['file' => $destinationPath]);
            if (!@unlink($sourcePath)) {
                $this->logger->warning('Failed to remove source dump file: {file}', ['file' => $sourcePath]);
            }
        }
    }

    protected function compressZip(string $sourcePath, ?string $destinationPath = null): string
    {
        if (!file_exists($sourcePath)) {
            throw new \InvalidArgumentException("File not found: $sourcePath");
        }

        if ($destinationPath === null) {
            $destinationPath = $sourcePath . '.zip';
        }

        $zip = new \ZipArchive();
        if ($zip->open($destinationPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("create zip failed.");
        }

        $zip->addFile($sourcePath, basename($sourcePath));
        $zip->close();

        return $destinationPath;
    }

    protected function compressGzip(string $sourcePath, ?string $destinationPath = null, int $level = 9): string
    {
        if (!file_exists($sourcePath)) {
            throw new \InvalidArgumentException("File not found: $sourcePath");
        }

        if ($destinationPath === null) {
            $destinationPath = $sourcePath . '.gz';
        }

        $inFile = fopen($sourcePath, 'rb');
        if (!$inFile) {
            throw new \RuntimeException("failed open source file.");
        }

        $outFile = gzopen($destinationPath, sprintf('wb%d', max(0, min(9, $level))));
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