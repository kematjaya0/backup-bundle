# backup-bundle for symfony 5
- base on https://github.com/spatie/db-dumper

1. installation
```
composer require kematjaya/backup-bundle
```
2. setting
```
## config/packages/backup.yaml

backup:
    name: postgresql
    location: '%kernel.project_dir%/var/backup'
```
3. usage
```
php bin/console database:dump
```
4. insert event 
```
// save log to database

namespace App\EventListener;

use App\Repository\BackupRepository;
use Kematjaya\BackupBundle\Event\AfterDumpEvent;
use Kematjaya\BackupBundle\Event\BackupEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Description of BackupEventListener
 *
 * @author apple
 */
class BackupEventListener implements EventSubscriberInterface 
{
    
    private BackupRepository $backupRepository;
    
    public function __construct(BackupRepository $backupRepository) 
    {
        $this->backupRepository = $backupRepository;
    }
    
    public static function getSubscribedEvents():array 
    {
        return [
            BackupEvents::AFTER_DUMP => "saveLog"
        ];
    }

    public function saveLog(AfterDumpEvent $evt):void
    {
        $this->backupRepository->create($evt->getFileName());
    }
}

```