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
        $path = $request->query->get("q", current($request->request->all())['q'] ?? null);
        $basePath = $backupManager->getBackupPath();
        $skipped = [".", ".."];
        if (null != $path) {
            $zip = new \ZipArchive();
            $zipFile = $this->getParameter("kernel.project_dir") . DIRECTORY_SEPARATOR . "var" . DIRECTORY_SEPARATOR . $path.'.zip';
            $zip->open($zipFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            $realPath = $backupManager->getBackupPath() . DIRECTORY_SEPARATOR . $path;
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($realPath),
                \RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach($files as $file) {
                if ($file->isDir()) {
                    continue;
                }

                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($realPath) + 1);
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