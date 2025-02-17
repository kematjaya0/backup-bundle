<?php

namespace Kematjaya\BackupBundle\Command;

use Kematjaya\BackupBundle\Manager\BackupManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: "database:dump")]
class DumpCommand extends Command
{
    public function __construct(private BackupManagerInterface $backupManager, mixed $name = null)
    {
        parent::__construct($name);
    }
    
    protected function execute(InputInterface $input, OutputInterface $output):int
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
