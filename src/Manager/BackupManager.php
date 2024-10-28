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
    private FactoryBuilderInterface $factoryBuilder;
    
    private array $configs;
    private ConnectionInterface $connection;
    
    private EventDispatcherInterface $eventDispatcher;
    
    public function __construct(ConnectionInterface $connection, EventDispatcherInterface $eventDispatcher, FactoryBuilderInterface $factoryBuilder, ParameterBagInterface $bag) 
    {
        $this->configs = $bag->get("backup");
        $this->eventDispatcher = $eventDispatcher;
        $this->factoryBuilder = $factoryBuilder;
        $this->connection = $connection;
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
        
        $this->eventDispatcher->dispatch(
            new AfterDumpEvent($dumper, $fileName), 
            BackupEvents::AFTER_DUMP
        );
        
        return $fileName;
    }
    
    public function getBackupPath():string
    {
        return $this->configs["location"];
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
