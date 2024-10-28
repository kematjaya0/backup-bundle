<?php

namespace Kematjaya\BackupBundle\Command;

use Kematjaya\BackupBundle\Manager\BackupManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpCommand extends Command
{
    protected static $defaultName = 'database:dump';
    
    private BackupManagerInterface $backupManager;
    
    public function __construct(mixed $name = null, BackupManagerInterface $backupManager) 
    {
        $this->backupManager = $backupManager;
        parent::__construct($name);
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $io->title("backup database");
        try{
            
            $path = $this->backupManager->run();
            
            $io->success(
                sprintf("backup date %s : '%s'", date('d/m/Y H:i:s'), $path)
            );
            
        } catch (\Exception $ex) {
            
            $io->error(
                sprintf('backup date %s : %s', date('d/m/Y H:i:s'), $ex->getMessage())
            );
            
            return self::FAILURE;
        }
        
        return self::SUCCESS;
    }
}
