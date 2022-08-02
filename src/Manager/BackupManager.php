<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Kematjaya\BackupBundle\Manager;

use Kematjaya\BackupBundle\Connection\ConnectionInterface;
use Kematjaya\BackupBundle\Builder\FactoryBuilderInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Description of BackupManager
 *
 * @author apple
 */
class BackupManager implements BackupManagerInterface 
{
    /**
     * 
     * @var FactoryBuilderInterface
     */
    private $factoryBuilder;
    
    /**
     * 
     * @var array
     */
    private $configs;
    
    /**
     * 
     * @var ConnectionInterface
     */
    private $connection;
    
    public function __construct(ConnectionInterface $connection, FactoryBuilderInterface $factoryBuilder, ParameterBagInterface $bag) 
    {
        $this->configs = $bag->get("backup");
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
            
        $dumper->dumpToFile($fileName);
        
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
