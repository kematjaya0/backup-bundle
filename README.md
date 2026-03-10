# backup-bundle for symfony 5
- base on https://github.com/spatie/db-dumper

1. installation
```bash
composer require kematjaya/backup-bundle
```
2. setting
```yaml
## config/packages/backup.yaml

backup:
    name: postgresql
    location: '%kernel.project_dir%/var/backup'
```
3. add route 
   ```yaml
   # config/routes/annotations.yaml
   backup:
        resource: '@BackupBundle/Resources/config/routes.yaml'
   ```
   /view-backup-file.html to view and download backup file
4. usage
    ```bash
    php bin/console database:dump
    ```
5. insert event 
    ```php
    
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