<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Kematjaya\BackupBundle\Factory;

use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Databases\PostgreSql as Dumper;

/**
 * Description of PostgresSQL
 *
 * @author apple
 */
class PostgresSQL implements FactoryInterface 
{
    
    public function create(): DbDumper 
    {
        return Dumper::create();
    }

    public function getName(): string 
    {
        return "postgresql";
    }

}
