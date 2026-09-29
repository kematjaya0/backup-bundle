<?php

namespace Kematjaya\BackupBundle\Controller;

use Kematjaya\BackupBundle\Manager\BackupManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class BackupController extends AbstractController
{
    public function viewBackup(Request $request, BackupManagerInterface $backupManager):Response
    {
        $directories = [];
        $path = $request->query->get('q');
        $basePath = $backupManager->getBackupPath();
        $skipped = [".", ".."];
        if (null !== $path && '' !== $path) {
            if (!preg_match('/^[A-Za-z0-9][A-Za-z0-9._-]*$/', $path)) {
                throw $this->createNotFoundException(sprintf('Invalid backup path "%s"', $path));
            }

            $realBase = realpath($backupManager->getBackupPath());
            $realTarget = $realBase === false ? false : realpath($realBase . DIRECTORY_SEPARATOR . $path);
            if ($realTarget === false || !is_dir($realTarget) || !str_starts_with($realTarget, $realBase . DIRECTORY_SEPARATOR)) {
                throw $this->createNotFoundException(sprintf('Invalid backup path "%s"', $path));
            }

            $zipFile = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'backup-' . bin2hex(random_bytes(8)) . '.zip';
            $zip = new \ZipArchive();
            if ($zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                throw new \RuntimeException('Failed to create zip archive.');
            }

            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($realTarget, \FilesystemIterator::SKIP_DOTS),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if ($file->isDir()) {
                    continue;
                }

                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($realTarget) + 1);
                $zip->addFile($filePath, $relativePath);
            }

            $zip->close();

            $response = new BinaryFileResponse($zipFile);
            $response->setContentDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                basename($zipFile)
            );

            $response->deleteFileAfterSend();

            return $response;
        }

        foreach (scandir($basePath, SCANDIR_SORT_DESCENDING) as $content) {
            if (in_array($content, $skipped)) {
                continue;
            }

            $isDir = is_dir($basePath . DIRECTORY_SEPARATOR . $content);
            if (!$isDir) {
                continue;
            }

            $directories[] = [
                "name" => $content,
                "path" => $content
            ];
        }

        return $this->render("@Backup/view-backup.html.twig", [
            "directories" => $directories
        ]);
    }
}