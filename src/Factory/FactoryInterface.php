<?php

namespace Kematjaya\BackupBundle\Factory;

use Spatie\DbDumper\DbDumper;

interface FactoryInterface
{
    const TAG_NAME = "db_dumper.factory";
    
    /**
     * 
     * @return DbDumper
     */
    public function create(): DbDumper;
    
    public function getName():string;
    
}
