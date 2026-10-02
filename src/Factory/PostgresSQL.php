<?php

namespace Kematjaya\BackupBundle\Factory;

use Spatie\DbDumper\Databases\PostgreSql as Dumper;
use Spatie\DbDumper\DbDumper;

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
