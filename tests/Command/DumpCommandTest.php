<?php

namespace Kematjaya\BackupBundle\Tests\Command;

use Kematjaya\BackupBundle\Command\DumpCommand;
use Kematjaya\BackupBundle\Manager\BackupManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DumpCommandTest extends TestCase
{
    public function testExecuteSuccess(): void
    {
        $backupManager = $this->createMock(BackupManagerInterface::class);
        $backupManager->expects($this->once())
            ->method('run')
            ->willReturn('/path/to/backup.sql');

        $command = new DumpCommand($backupManager);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $commandTester->assertCommandIsSuccessful();
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('backup database', $output);
        $this->assertStringContainsString('/path/to/backup.sql', $output);
    }

    public function testExecuteException(): void
    {
        $backupManager = $this->createMock(BackupManagerInterface::class);
        $backupManager->expects($this->once())
            ->method('run')
            ->willThrowException(new \Exception('Test exception'));

        $command = new DumpCommand($backupManager);

        $commandTester = new CommandTester($command);
        $commandTester->execute([]);

        $this->assertEquals(DumpCommand::FAILURE, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        $this->assertMatchesRegularExpression('/Test\s+exception/', $output);
    }
}
