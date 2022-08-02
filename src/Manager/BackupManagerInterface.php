<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPInterface.php to edit this template
 */

namespace Kematjaya\BackupBundle\Manager;

/**
 *
 * @author apple
 */
interface BackupManagerInterface 
{
    /**
     * @throws Exception
     * @return string file path
     */
    public function run(): string;
}
