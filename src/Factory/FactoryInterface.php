<?php

namespace Kematjaya\BackupBundle\Factory;

use Spatie\DbDumper\DbDumper;

interface FactoryInterface
{
    public const TAG_NAME = "db_dumper.factory";

    public function create(): DbDumper;

    public function getName(): string;

}
