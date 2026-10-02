<?php

namespace Kematjaya\BackupBundle\Tests;

use Kematjaya\BackupBundle\Manager\BackupManager;
use Kematjaya\BackupBundle\Manager\BackupManagerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Command\Command;

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
        $application = new Application($client->getKernel());
        $command = $application->find('database:dump');
        $this->assertInstanceOf(Command::class, $command);
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
