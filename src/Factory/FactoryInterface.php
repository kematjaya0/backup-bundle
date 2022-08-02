<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPInterface.php to edit this template
 */

namespace Kematjaya\BackupBundle\Factory;

use Spatie\DbDumper\DbDumper;

/**
 *
 * @author apple
 */
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
