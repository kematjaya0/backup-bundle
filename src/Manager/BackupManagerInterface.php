<?php

namespace Kematjaya\BackupBundle\Manager;

/**
 *
 * @author apple
 */
interface BackupManagerInterface 
{
    public function run(): string;
    public function getBackupPath():string;
}
