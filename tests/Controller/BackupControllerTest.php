<?php

namespace Kematjaya\BackupBundle\Tests\Controller;

use Kematjaya\BackupBundle\Controller\BackupController;
use Kematjaya\BackupBundle\Manager\BackupManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class BackupControllerTest extends TestCase
{
    private $projectDir;
    private $backupBaseDir;

    protected function setUp(): void
    {
        $this->projectDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'project_dir';
        @mkdir($this->projectDir . DIRECTORY_SEPARATOR . 'var', 0777, true);
        
        $this->backupBaseDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'backup_dir';
        @mkdir($this->backupBaseDir, 0777, true);
    }

    protected function tearDown(): void
    {
        $this->removeDirectory($this->projectDir);
        $this->removeDirectory($this->backupBaseDir);
    }

    private function removeDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object))
                    $this->removeDirectory($dir. DIRECTORY_SEPARATOR .$object);
                else
                    unlink($dir. DIRECTORY_SEPARATOR .$object);
            }
        }
        rmdir($dir);
    }

    public function testViewBackupListDirectories(): void
    {
        @mkdir($this->backupBaseDir . DIRECTORY_SEPARATOR . '2026-03-10');
        @mkdir($this->backupBaseDir . DIRECTORY_SEPARATOR . '2026-03-11');
        // create a file to ensure it gets ignored
        file_put_contents($this->backupBaseDir . DIRECTORY_SEPARATOR . 'should_be_ignored.txt', 'test');

        $backupManager = $this->createMock(BackupManagerInterface::class);
        $backupManager->expects($this->once())
            ->method('getBackupPath')
            ->willReturn($this->backupBaseDir);

        $request = new Request();

        $controller = clone $this->getMockBuilder(BackupController::class)
            ->onlyMethods(['render'])
            ->getMock();

        $controller->expects($this->once())
            ->method('render')
            ->with(
                '@Backup/view-backup.html.twig',
                $this->callback(function ($data) {
                    return isset($data['directories']) && 
                           count($data['directories']) === 2 && 
                           $data['directories'][0]['name'] === '2026-03-11' &&
                           $data['directories'][1]['name'] === '2026-03-10';
                })
            )
            ->willReturn(new Response());

        $response = $controller->viewBackup($request, $backupManager);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testViewBackupInvalidPaths(): void
    {
        $backupManager = $this->createMock(BackupManagerInterface::class);
        $backupManager->method('getBackupPath')->willReturn($this->backupBaseDir);
        $controller = new BackupController();

        $invalidQueries = [
            '../etc',
            '..',
            'foo/bar',
            'no-such-dir-123',
        ];

        foreach ($invalidQueries as $q) {
            $request = new Request(['q' => $q]);
            try {
                $controller->viewBackup($request, $backupManager);
                $this->fail(sprintf('Expected NotFoundHttpException for q="%s"', $q));
            } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
                $this->assertNotEmpty($e->getMessage());
            }
        }

        // empty string leads to list view
        $request = new Request(['q' => '']);
        $controller = $this->getMockBuilder(BackupController::class)
            ->onlyMethods(['render'])
            ->getMock();
        $controller->expects($this->once())
            ->method('render')
            ->with('@Backup/view-backup.html.twig', $this->anything())
            ->willReturn(new Response());
        $response = $controller->viewBackup($request, $backupManager);
        $this->assertInstanceOf(Response::class, $response);
    }

    public function testViewBackupDownloadZip(): void
    {
        $queryPath = '2026-03-11';
        $realPath = $this->backupBaseDir . DIRECTORY_SEPARATOR . $queryPath;
        @mkdir($realPath, 0777, true);
        file_put_contents($realPath . DIRECTORY_SEPARATOR . 'test.sql', 'dump data');

        $backupManager = $this->createMock(BackupManagerInterface::class);
        $backupManager->expects($this->any())
            ->method('getBackupPath')
            ->willReturn($this->backupBaseDir);

        $request = new Request(['q' => $queryPath]);
        $controller = new BackupController();

        $response = $controller->viewBackup($request, $backupManager);

        $this->assertInstanceOf(BinaryFileResponse::class, $response);

        $zipFilePattern = '/^' . preg_quote(sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'backup-', '/') . '[0-9a-f]{16}\.zip$/';
        $this->assertMatchesRegularExpression($zipFilePattern, $response->getFile()->getPathname());
        $this->assertTrue($response->headers->has('content-disposition'));

        @unlink($response->getFile()->getPathname());
    }
}
