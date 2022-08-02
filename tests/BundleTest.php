<?php

namespace Kematjaya\UploadBundle\Tests;

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
    public static function getKernelClass() 
    {
        return BackupBundleTest::class;
    }
    
    public function testLoadBundle(): BackupManagerInterface
    {
        $client = parent::createClient();
        $container = $client->getContainer();
        
        $this->assertInstanceOf(BackupManager::class, $container->get(BackupManagerInterface::class));
        
        return $container->get(BackupManagerInterface::class);
    }
}
