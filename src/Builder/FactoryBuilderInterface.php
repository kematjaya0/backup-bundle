<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPInterface.php to edit this template
 */

namespace Kematjaya\BackupBundle\Builder;

use Kematjaya\BackupBundle\Exception\FactoryNotFoundException;
use Kematjaya\BackupBundle\Factory\FactoryInterface;

/**
 *
 * @author apple
 */
interface FactoryBuilderInterface 
{
    /**
     * @throws FactoryNotFoundException
     * @param string $name
     * @return FactoryInterface
     */
    public function getFactory(string $name):FactoryInterface;
    
    /**
     * 
     * @param FactoryInterface $factory
     * @return self
     */
    public function addFactory(FactoryInterface $factory):self;
    
    /**
     * 
     * @return array
     */
    public function getAllFactories():array;
}
