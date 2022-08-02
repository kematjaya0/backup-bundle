<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Kematjaya\BackupBundle\Builder;

use Kematjaya\BackupBundle\Exception\FactoryNotFoundException;
use Doctrine\Common\Collections\ArrayCollection;
use Kematjaya\BackupBundle\Factory\FactoryInterface;

/**
 * Description of FactoryBuilder
 *
 * @author apple
 */
class FactoryBuilder implements FactoryBuilderInterface 
{
    /**
     * 
     * @var ArrayCollection
     */
    private $factories;
    
    public function __construct() 
    {
        $this->factories = new ArrayCollection();
    }
    
    public function addFactory(FactoryInterface $factory): FactoryBuilderInterface 
    {
        if (!$this->factories->contains($factory)) {
            $this->factories->add($factory);
        }
        
        return $this;
    }

    public function getAllFactories(): array 
    {
        return $this->factories->toArray();
    }

    public function getFactory(string $name): FactoryInterface 
    {
        $factories = $this->factories->filter(function (FactoryInterface $factory) use ($name) {
            return $factory->getName() === $name;
        });
        
        if ($factories->isEmpty()) {
            throw new FactoryNotFoundException($name);
        }
        
        return $factories->first();
    }

}
