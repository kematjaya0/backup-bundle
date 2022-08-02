<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Kematjaya\BackupBundle\CompilerPass;

use Kematjaya\BackupBundle\Factory\FactoryInterface;
use Kematjaya\BackupBundle\Builder\FactoryBuilderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Description of DatabaseFactoryCompilerPass
 *
 * @author apple
 */
class DatabaseFactoryCompilerPass implements CompilerPassInterface 
{
    //put your code here
    public function process(ContainerBuilder $container):void 
    {
        $definition = $container->findDefinition(FactoryBuilderInterface::class);
        $taggedServices = $container->findTaggedServiceIds(FactoryInterface::TAG_NAME);
        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addFactory', [new Reference($id)]);
        }
    }

}
