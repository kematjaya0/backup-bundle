<?php

namespace Kematjaya\BackupBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder 
    {
        $treeBuilder = new TreeBuilder('backup');
        $rootNode = $treeBuilder->getRootNode();
        
        $rootNode
            ->children()
                ->scalarNode("name")->defaultValue('mysql')->end()
                ->scalarNode('location')->defaultValue('%kernel.project_dir%/var/backup')->end()
            ->end();
        
        return $treeBuilder;
    }
}
