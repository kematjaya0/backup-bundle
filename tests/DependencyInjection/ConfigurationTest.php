<?php

namespace Kematjaya\BackupBundle\Tests\DependencyInjection;

use Kematjaya\BackupBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends TestCase
{
    public function testDefaultValues(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), []);

        $this->assertSame('mysql', $config['name']);
        $this->assertSame('%kernel.project_dir%/var/backup', $config['location']);
        $this->assertNull($config['keep']);
        $this->assertNull($config['max_age_days']);
    }

    public function testCustomRetentionValuesPassThrough(): void
    {
        $config = (new Processor())->processConfiguration(new Configuration(), [
            ['keep' => 5, 'max_age_days' => 30],
        ]);

        $this->assertSame(5, $config['keep']);
        $this->assertSame(30, $config['max_age_days']);
        $this->assertSame('mysql', $config['name']);
    }

    public function testKeepBelowOneIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        (new Processor())->processConfiguration(new Configuration(), [['keep' => 0]]);
    }

    public function testMaxAgeDaysBelowOneIsRejected(): void
    {
        $this->expectException(InvalidConfigurationException::class);
        (new Processor())->processConfiguration(new Configuration(), [['max_age_days' => 0]]);
    }
}
