<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Kematjaya\BackupBundle\Tests;

use Kematjaya\BackupBundle\Connection\ConnectionInterface;

/**
 * Description of TestConnection
 *
 * @author apple
 */
class TestConnection implements ConnectionInterface 
{
    //put your code here
    public function getDbName(): string {
        
    }

    public function getHost(): string {
        
    }

    public function getPassword(): ?string {
        
    }

    public function getPort(): int {
        
    }

    public function getUsername(): string {
        
    }

}
