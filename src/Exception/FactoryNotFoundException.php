<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Kematjaya\BackupBundle\Exception;

/**
 * Description of FactoryNotFoundException
 *
 * @author apple
 */
class FactoryNotFoundException extends \Exception
{
    public function __construct(string $name, int $code = 0, \Throwable $previous = null) 
    {
        $message = sprintf("factory with name %s not found.", $name);
        
        parent::__construct($message, $code, $previous);
    }
}
