<?php
echo "START\n";
require '/app/vendor/autoload.php';
use Kematjaya\BackupBundle\Tests\BackupBundleTest;
$kernel = new BackupBundleTest('test', true);
$kernel->boot();
$c = $kernel->getContainer();
echo "has backup param: "; var_dump($c->hasParameter('backup'));
echo "has manager svc: "; var_dump($c->has(\Kematjaya\BackupBundle\Manager\BackupManagerInterface::class));
try {
    $m = $c->get(\Kematjaya\BackupBundle\Manager\BackupManagerInterface::class);
    echo "got: ", get_class($m), "\n";
    try { echo "path: ", $m->getBackupPath(), "\n"; } catch (Throwable $e) { echo "getBackupPath: ", get_class($e), ': ', $e->getMessage(), "\n"; }
} catch (Throwable $e) {
    echo "get FAILED: ", get_class($e), ': ', $e->getMessage(), "\n";
}
echo "backup ext registered: "; var_dump($kernel->getContainer()->hasExtension('backup'));
