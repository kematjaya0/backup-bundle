<?php

namespace Kematjaya\BackupBundle\Connection;

use Doctrine\Persistence\ManagerRegistry;

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
