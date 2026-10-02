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
            ->integerNode('keep')->defaultNull()->min(1)->info('Maximum number of daily backup folders to keep (null = unlimited).')->end()
            ->integerNode('max_age_days')->defaultNull()->min(1)->info('Delete backup folders older than N days (null = never delete).')->end()
        ->end();

        return $treeBuilder;
    }
}
