<?php

namespace Kematjaya\BackupBundle\Event;

use Spatie\DbDumper\DbDumper;
use Symfony\Contracts\EventDispatcher\Event;

abstract class BackupEvent extends Event
{
    /**
     * 
     * @var DbDumper
     */
    private $dumper;
    
    /**
     * 
     * @var string
     */
    private $fileName;
    
    public function __construct(DbDumper $dumper, string $fileName) 
    {
        $this->dumper = $dumper;
        $this->fileName = $fileName;
    }
    
    public function getDumper(): DbDumper 
    {
        return $this->dumper;
    }

    public function getFileName(): string 
    {
        return $this->fileName;
    }

}
