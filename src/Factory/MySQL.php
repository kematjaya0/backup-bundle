<?php

namespace Kematjaya\BackupBundle\Factory;

use Spatie\DbDumper\DbDumper;
use Spatie\DbDumper\Databases\MySql as Dumper;

class MySQL implements FactoryInterface
{
    public function create(): DbDumper 
    {
        return Dumper::create();
    }

    public function getName(): string 
    {
        return "mysql";
    }

}
