<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPInterface.php to edit this template
 */

namespace Kematjaya\BackupBundle\Connection;

/**
 *
 * @author apple
 */
interface ConnectionInterface 
{
    public function getHost():string;
    
    public function getDbName():string;
    
    public function getPort():int;
    
    public function getUsername():string;
    
    public function getPassword():?string;
}
