<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Kematjaya\BackupBundle\Connection;

use Doctrine\Persistence\ManagerRegistry;

/**
 * Description of DoctrineConnection
 *
 * @author apple
 */
class DoctrineConnection implements ConnectionInterface 
{
    /**
     * 
     * @var array
     */
    private $params;
    
    public function __construct(ManagerRegistry $managerRegistry) 
    {
        $this->params = $managerRegistry->getConnection()->getParams();
    }
    
    public function getDbName(): string 
    {
        return $this->params["dbname"] ?? "default";
    }

    public function getHost(): string 
    {
        return $this->params["host"] ?? "localhost";
    }

    public function getPassword(): ?string 
    {
        return $this->params["password"] ?? "";
    }

    public function getPort(): int 
    {
     return (int)$this->params["port"] ?? 0;   
    }

    public function getUsername(): string 
    {
        return $this->params["user"] ?? "default";
    }

}
