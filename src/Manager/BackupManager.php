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
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class BackupManager implements BackupManagerInterface 
{
    private FactoryBuilderInterface $factoryBuilder;
    
    private array $configs;
    private ConnectionInterface $connection;
    
    private EventDispatcherInterface $eventDispatcher;
    
    private LoggerInterface $logger;
    
    public function __construct(ConnectionInterface $connection, EventDispatcherInterface $eventDispatcher, FactoryBuilderInterface $factoryBuilder, ParameterBagInterface $bag, ?LoggerInterface $logger = null) 
    {
        $configs = $bag->has('backup') ? $bag->get('backup') : null;
        if (!is_array($configs)) {
            throw new \InvalidArgumentException('Missing "backup" configuration. Add a "backup" section to your Symfony config.');
        }
        foreach (["name", "location"] as $requiredKey) {
            if (!isset($configs[$requiredKey]) || !is_string($configs[$requiredKey]) || $configs[$requiredKey] === '') {
                throw new \InvalidArgumentException(sprintf('Invalid or missing "backup.%s" configuration value.', $requiredKey));
            }
        }
        $this->configs = $configs;
        $this->eventDispatcher = $eventDispatcher;
        $this->factoryBuilder = $factoryBuilder;
        $this->connection = $connection;
        $this->logger = $logger ?? new NullLogger();
    }

    
    public function run(): string 
    {
        $start = microtime(true);
        $fileName = sprintf(
            $this->prepareDir() . DIRECTORY_SEPARATOR . '%s.sql', date('Ymd_His')
        );

        $dumper = $this->factoryBuilder->getFactory($this->configs["name"])->create();
        $dumper->setHost($this->connection->getHost())
                ->setDbName($this->connection->getDbName())
                ->setPort($this->connection->getPort())
                ->setUserName($this->connection->getUsername())
                ->setPassword($this->connection->getPassword() ?? '');

        $this->logger->info('Starting database dump', ['database' => $this->configs['name'], 'file' => $fileName]);

        $this->eventDispatcher->dispatch(
            new BeforeDumpEvent($dumper, $fileName), 
            BackupEvents::BEFORE_DUMP
        );

        try {
            $dumper->dumpToFile($fileName);
        } catch (\Throwable $e) {
            $this->logger->error('Database dump failed: {message}', ['message' => $e->getMessage(), 'exception' => $e]);
            throw $e;
        }

        $evt = new AfterDumpEvent($dumper, $fileName);
        $this->logger->info('Database dump completed', [
            'file' => $evt->getFileName(),
            'duration_ms' => (int) round((microtime(true) - $start) * 1000),
            'size_bytes' => is_file($evt->getFileName()) ? (int) filesize($evt->getFileName()) : null
        ]);

        $this->eventDispatcher->dispatch(
            $evt,
            BackupEvents::AFTER_DUMP
        );

        $this->pruneOldBackups();

        return $evt->getFileName();
    }
    
    public function getBackupPath():string
    {
        return $this->configs["location"];
    }
    
    protected function prepareDir(): string
    {
        $fileSystem = new Filesystem();
        $dumpPath = $this->getBackupPath() . DIRECTORY_SEPARATOR . date('Y-m-d');
        if (!$fileSystem->exists($dumpPath)) {
            $fileSystem->mkdir($dumpPath);
        }

        return $dumpPath;
    }

    private function pruneOldBackups(): void
    {
        $keep = $this->configs['keep'] ?? null;
        $maxAgeDays = $this->configs['max_age_days'] ?? null;
        if (null === $keep && null === $maxAgeDays) {
            return;
        }

        try {
            $backupPath = $this->getBackupPath();
            $fileSystem = new Filesystem();
            if (!is_dir($backupPath) || !$fileSystem->exists($backupPath)) {
                return;
            }
            $entries = scandir($backupPath, SCANDIR_SORT_NONE);
            if (false === $entries) {
                return;
            }
            $dirs = [];
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..') {
                    continue;
                }
                $full = $backupPath . DIRECTORY_SEPARATOR . $entry;
                if (!is_dir($full)) {
                    continue;
                }
                $dirs[] = $entry;
            }
            rsort($dirs, SORT_STRING);
            // keep most recent $keep directories
            if (null !== $keep) {
                $keep = (int) $keep;
                foreach (array_slice($dirs, $keep) as $oldDir) {
                    $fileSystem->remove($backupPath . DIRECTORY_SEPARATOR . $oldDir);
                    $this->logger->info('Pruned old backup {dir}', ['dir' => $oldDir]);
                }
                // after keep pruning, recalc dirs for age filter
                $dirs = array_slice($dirs, 0, $keep);
            }
            if (null !== $maxAgeDays) {
                $threshold = time() - ((int) $maxAgeDays) * 86400;
                foreach ($dirs as $dir) {
                    $full = $backupPath . DIRECTORY_SEPARATOR . $dir;
                    if (filemtime($full) < $threshold) {
                        $fileSystem->remove($full);
                        $this->logger->info('Pruned old backup {dir}', ['dir' => $dir]);
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->logger->warning('Backup pruning failed: {message}', ['message' => $e->getMessage()]);
        }
    }
}
