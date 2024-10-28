<?php

namespace Kematjaya\BackupBundle\Factory;

use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Databases\PostgreSql as Dumper;

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
