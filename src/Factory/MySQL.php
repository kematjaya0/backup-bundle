<?php

namespace Kematjaya\BackupBundle\Factory;

use Spatie\DbDumper\Databases\MySql as Dumper;
use Spatie\DbDumper\DbDumper;

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
