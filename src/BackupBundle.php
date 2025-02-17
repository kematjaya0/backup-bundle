<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

namespace Kematjaya\BackupBundle;

use Kematjaya\BackupBundle\Factory\FactoryInterface;
use Kematjaya\BackupBundle\CompilerPass\DatabaseFactoryCompilerPass;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Description of BackupBundle
 *
 * @author apple
 */
class BackupBundle extends Bundle
{
    public function build(ContainerBuilder $container) :void
    {
        $container->registerForAutoconfiguration(FactoryInterface::class)
                ->addTag(FactoryInterface::TAG_NAME);
        
        $container->addCompilerPass(new DatabaseFactoryCompilerPass());
        
        parent::build($container);
    }
}
