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
    public function getDbName(): string
    {
        return 'test_db';
    }

    public function getHost(): string
    {
        return 'localhost';
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getPort(): int
    {
        return 3306;
    }

    public function getUsername(): string
    {
        return 'root';
    }

}
