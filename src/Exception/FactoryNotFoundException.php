<?php

namespace Kematjaya\BackupBundle\Exception;

class FactoryNotFoundException extends \Exception
{
    public function __construct(string $name, int $code = 0, \Throwable $previous = null) 
    {
        $message = sprintf("factory with name %s not found.", $name);
        
        parent::__construct($message, $code, $previous);
    }
}
