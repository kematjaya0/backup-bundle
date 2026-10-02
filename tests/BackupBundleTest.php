<?php

namespace Kematjaya\BackupBundle\Tests;

use Kematjaya\BackupBundle\BackupBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * @author Nur Hidayatullah <kematjaya0@gmail.com>
 */
class BackupBundleTest extends Kernel
{
    public function registerBundles(): iterable
    {
        return [
            new BackupBundle(),
            new FrameworkBundle(),
        ];
    }

    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(function (ContainerBuilder $container) use ($loader): void {
            $loader->load(__DIR__ . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'config.yml');

            $container->addObjectResource($this);
        });
    }

}
