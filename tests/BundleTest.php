<?php

namespace Kematjaya\BackupBundle\Tests;

use Kematjaya\BackupBundle\Exception\FactoryNotFoundException;
use Kematjaya\BackupBundle\Manager\BackupManager;
use Kematjaya\BackupBundle\Manager\BackupManagerInterface;
use Kematjaya\BackupBundle\Tests\BackupBundleTest;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @author Nur Hidayatullah <kematjaya0@gmail.com>
 */
class BundleTest extends WebTestCase
{
    public static function getKernelClass(): string 
    {
        return BackupBundleTest::class;
    }
    
    public function testDiCommandRegistered(): void
    {
        $client = parent::createClient();
        $container = $client->getContainer();
        $application = new \Symfony\Bundle\FrameworkBundle\Console\Application($client->getKernel());
        $command = $application->find('database:dump');
        $this->assertInstanceOf(\Symfony\Component\Console\Command\Command::class, $command);
        $this->assertSame('database:dump', $command->getName());
    }

    public function testLoadBundle(): BackupManagerInterface
    {
        $client = parent::createClient();
        $container = $client->getContainer();

        $this->assertInstanceOf(BackupManager::class, $container->get(BackupManagerInterface::class));

        return $container->get(BackupManagerInterface::class);
    }
}
