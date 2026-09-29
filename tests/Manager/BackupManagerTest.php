<?php

namespace Kematjaya\BackupBundle\Tests\Manager;

use Kematjaya\BackupBundle\Builder\FactoryBuilderInterface;
use Kematjaya\BackupBundle\Connection\ConnectionInterface;
use Kematjaya\BackupBundle\Manager\BackupManager;
use Kematjaya\BackupBundle\Manager\BackupManagerInterface;
use Kematjaya\BackupBundle\Factory\FactoryInterface;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class BackupManagerTest extends TestCase
{
    private function createConnection(): ConnectionInterface
    {
        return new class implements ConnectionInterface {
            public function getHost(): string { return 'localhost'; }
            public function getDbName(): string { return 'test_db'; }
            public function getPort(): int { return 3306; }
            public function getUsername(): string { return 'root'; }
            public function getPassword(): ?string { return null; }
        };
    }

    private function createFactoryBuilder(): FactoryBuilderInterface
    {
        return new class implements FactoryBuilderInterface {
            public function getFactory(string $name): FactoryInterface {
                return new class implements FactoryInterface {
                    public function create(): \Spatie\DbDumper\DbDumper {
                        return new class extends \Spatie\DbDumper\DbDumper {
                            public function setHost(string $host): self { return $this; }
                            public function setDbName(string $dbName): self { return $this; }
                            public function setPort(int $port): self { return $this; }
                            public function setUserName(string $userName): self { return $this; }
                            public function setPassword(string $password): self { return $this; }
                            public function dumpToFile(string $path): void { file_put_contents($path, "-- stub dump\n"); }
                        };
                    }
                    public function getName(): string { return 'stub'; }
                };
            }
            public function addFactory(FactoryInterface $factory): self { return $this; }
            public function getAllFactories(): array { return []; }
        };
    }

    private function createParameterBag(array $config): ParameterBagInterface
    {
        return new \Symfony\Component\DependencyInjection\ParameterBag\ParameterBag($config);
    }

    private function createLogger(): LoggerInterface
    {
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->any())->method('info');
        return $logger;
    }

    public function testInvalidBackupParametersThrow(): void
    {
        $configs = [
            // missing entirely: bag->has('backup') === false handled by assertMissingConfig
            ['location' => '/tmp', 'keep' => 1], // missing name
            ['name' => 'stub', 'keep' => 1], // missing location
            ['name' => '', 'location' => '/tmp', 'keep' => 1], // empty name
        ];

        // missing backup section entirely
        $bag = $this->createMock(ParameterBagInterface::class);
        $bag->method('has')->with('backup')->willReturn(false);
        try {
            new BackupManager(
                $this->createConnection(),
                new EventDispatcher(),
                $this->createFactoryBuilder(),
                $bag,
                $this->createLogger()
            );
            $this->fail('Expected InvalidArgumentException when backup config is missing');
        } catch (\InvalidArgumentException $e) {
            $this->assertNotEmpty($e->getMessage());
        }

        foreach ($configs as $config) {
            $bag = $this->createMock(ParameterBagInterface::class);
            $bag->method('has')->with('backup')->willReturn(true);
            $bag->method('get')->with('backup')->willReturn($config);
            try {
                new BackupManager(
                    $this->createConnection(),
                    new EventDispatcher(),
                    $this->createFactoryBuilder(),
                    $bag,
                    $this->createLogger()
                );
                $this->fail('Expected InvalidArgumentException for config: ' . json_encode($config));
            } catch (\InvalidArgumentException $e) {
                $this->assertNotEmpty($e->getMessage());
            }
        }
    }

    public function testRunCreatesDumpFileAndPrunesOldBackups(): void
    {
        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'backup_test_' . bin2hex(random_bytes(4));
        mkdir($tmp, 0777, true);
        $backupDir = $tmp . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'backup';
        mkdir($backupDir, 0777, true);
        foreach (['2000-01-01', '2000-01-02', '2000-01-03'] as $d) {
            mkdir($backupDir . DIRECTORY_SEPARATOR . $d, 0777, true);
        }
        $config = ['name' => 'stub', 'location' => $backupDir, 'keep' => 2, 'max_age_days' => null];
        $bag = $this->createMock(ParameterBagInterface::class);
        $bag->method('has')->with('backup')->willReturn(true);
        $bag->method('get')->with('backup')->willReturn($config);
        $manager = new BackupManager(
            $this->createConnection(),
            new EventDispatcher(),
            $this->createFactoryBuilder(),
            $bag,
            $this->createLogger()
        );
        $file = $manager->run();
        $this->assertMatchesRegularExpression('/\d{8}_\d{6}\.sql$/', basename($file));
        $this->assertFileExists($file);
        // after run, only two newest date dirs should remain (keep=2)
        $dirs = array_filter(scandir($backupDir), fn($e) => !in_array($e, ['.', '..']));
        $this->assertCount(2, $dirs);
        $this->removeDirectory($tmp);
    }

    public function testRunPrunesByMaxAge(): void
    {
        $tmp = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'backup_age_' . bin2hex(random_bytes(4));
        mkdir($tmp, 0777, true);
        $backupDir = $tmp . DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'backup';
        mkdir($backupDir, 0777, true);

        $oldDir = $backupDir . DIRECTORY_SEPARATOR . '2000-01-01';
        mkdir($oldDir, 0777, true);
        touch($oldDir, time() - 10 * 86400); // 10 days old
        $freshDir = $backupDir . DIRECTORY_SEPARATOR . '2099-12-31';
        mkdir($freshDir, 0777, true);

        $config = ['name' => 'stub', 'location' => $backupDir, 'keep' => null, 'max_age_days' => 5];
        $bag = $this->createMock(ParameterBagInterface::class);
        $bag->method('has')->with('backup')->willReturn(true);
        $bag->method('get')->with('backup')->willReturn($config);
        $manager = new BackupManager(
            $this->createConnection(),
            new EventDispatcher(),
            $this->createFactoryBuilder(),
            $bag,
            $this->createLogger()
        );
        $manager->run();

        $this->assertDirectoryDoesNotExist($oldDir);
        $this->assertDirectoryExists($freshDir);
        $this->removeDirectory($tmp);
    }


    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) return;
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object !== '.' && $object !== '..') {
                $path = $dir . DIRECTORY_SEPARATOR . $object;
                is_dir($path) ? $this->removeDirectory($path) : unlink($path);
            }
        }
        rmdir($dir);
    }
}
