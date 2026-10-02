<?php

namespace Kematjaya\BackupBundle\Event;

use Spatie\DbDumper\DbDumper;
use Symfony\Contracts\EventDispatcher\Event;

abstract class BackupEvent extends Event
{
    public function __construct(private readonly DbDumper $dumper, private string $fileName) {}

    public function getDumper(): DbDumper
    {
        return $this->dumper;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function setFileName(string $fileName): self
    {
        $this->fileName = $fileName;

        return $this;
    }
}
