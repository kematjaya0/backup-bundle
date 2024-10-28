<?php

namespace Kematjaya\BackupBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class BackupExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container) 
    {
        $loader = new YamlFileLoader($container, new FileLocator(dirname(__DIR__).'/Resources/config'));
        $loader->load('services.yml');
        
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $container->setParameter($this->getAlias(), $config);
    }
}
