<?php

namespace Kematjaya\BackupBundle\CompilerPass;

use Kematjaya\BackupBundle\Factory\FactoryInterface;
use Kematjaya\BackupBundle\Builder\FactoryBuilderInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

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
