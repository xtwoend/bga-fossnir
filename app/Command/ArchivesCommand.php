<?php

declare(strict_types=1);

namespace App\Command;

use Carbon\Carbon;
use App\Service\Samba;
use App\Model\FossnirDir;
use Psr\Container\ContainerInterface;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class ArchivesCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('fossnir:archives');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Auto Archive files');
    }

    protected function getArguments()
    {
        return [
            ['mill_id', InputArgument::OPTIONAL, 'mill id']
        ];
    }
    
    public function handle()
    {
        $lastest = Carbon::now()->format('Y-m-d H:i:s');
        

        if($mill_id) {
            $dir = FossnirDir::find($mill_id);
            if($dir){
                $this->moveFile($dir);
            }
        }else{
            foreach(FossnirDir::where('auto_read', 1)->get() as $dir) {
                $this->moveFile($dir);
            }
        }
    }

    protected function rename($dir) {
        $smb = make(Samba::class);
        $files = $smb->dir($dir->dir_path);
        $dir_path = $dir->dir_path;
        foreach($files as $file) {
            if(! $file->isDirectory()) {
                // process file

                // move file 
                $smb->rename($file->getPath(), "/{$dir_path}/archives/{$file->getName()}");
            }
        }
    }
}
