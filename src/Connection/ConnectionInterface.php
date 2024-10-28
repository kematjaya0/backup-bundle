<?php

namespace Kematjaya\BackupBundle\Connection;

interface ConnectionInterface
{
    public function getHost():string;
    
    public function getDbName():string;
    
    public function getPort():int;
    
    public function getUsername():string;
    
    public function getPassword():?string;
}
