<?php

namespace Kematjaya\BackupBundle\Manager;

use Kematjaya\BackupBundle\Event\BackupEvents;
use Kematjaya\BackupBundle\Event\BeforeDumpEvent;
use Kematjaya\BackupBundle\Event\AfterDumpEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Kematjaya\BackupBundle\Connection\ConnectionInterface;
use Kematjaya\BackupBundle\Builder\FactoryBuilderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class BackupManager implements BackupManagerInterface
{

    private array $configs;

    public function __construct(private ConnectionInterface $connection, private EventDispatcherInterface $eventDispatcher, private FactoryBuilderInterface $factoryBuilder, ParameterBagInterface $bag)
    {
        $this->configs = $bag->get("backup");
    }

    public function run(): string
    {
        $fileName = sprintf(
            $this->prepareDir() . DIRECTORY_SEPARATOR . '%s.sql', date('Ymd His')
        );

        $dumper = $this->factoryBuilder->getFactory($this->configs["name"])->create();
        $dumper->setHost($this->connection->getHost())
            ->setDbName($this->connection->getDbName())
            ->setPort($this->connection->getPort())
            ->setUserName($this->connection->getUsername())
            ->setPassword($this->connection->getPassword());

        $this->eventDispatcher->dispatch(
            new BeforeDumpEvent($dumper, $fileName),
            BackupEvents::BEFORE_DUMP
        );

        $dumper->dumpToFile($fileName);
        $compressed = $this->compressGzip($fileName);
        if ($compressed) {
            unlink($fileName);
        }

        $this->eventDispatcher->dispatch(
            new AfterDumpEvent($dumper, $compressed),
            BackupEvents::AFTER_DUMP
        );

        return $compressed;
    }

    public function getBackupPath():string
    {
        return $this->configs["location"];
    }
    protected function compressGzip(string $sourcePath, ?string $destinationPath = null, int $level = 9): string
    {
        if (!file_exists($sourcePath)) {
            throw new InvalidArgumentException("File not found: $sourcePath");
        }

        if ($destinationPath === null) {
            $destinationPath = $sourcePath . '.gz';
        }

        $inFile  = fopen($sourcePath, 'rb');
        if (!$inFile) {
            throw new RuntimeException("failed open source file.");
        }

        $outFile = gzopen($destinationPath, 'wb' . $level);
        if (!$outFile) {
            fclose($inFile);
            throw new RuntimeException("create gzip failed.");
        }

        while (!feof($inFile)) {
            gzwrite($outFile, fread($inFile, 1024 * 512)); // 512KB chunk
        }

        fclose($inFile);
        gzclose($outFile);

        return $destinationPath;
    }

    protected function prepareDir(): string
    {
        try{
            $fileSystem = new Filesystem();
            $dumpPath = $this->getBackupPath() . DIRECTORY_SEPARATOR . date('Y-m-d');
            if(!$fileSystem->exists($dumpPath)) {
                $fileSystem->mkdir($dumpPath);
            }

            return $dumpPath;

        } catch (\Exception $ex) {
            throw $ex;
        }
    }

}
