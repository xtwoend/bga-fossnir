<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\Samba;
use App\Model\FossnirDir;
use App\Model\ResultFile;
use Psr\Container\ContainerInterface;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class ReadSmbFileCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('fossnir:read-file');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Read Fossnir new uploaded files');
    }

    protected function getArguments()
    {
        return [
            ['mill_id', InputArgument::OPTIONAL, 'mill id']
        ];
    }

    public function handle()
    {   
        $mill_id = $this->input->getArgument('mill_id');

        if($mill_id) {
            $dir = FossnirDir::find($mill_id);
            if($dir){
                $this->readAndDownload($dir);
            }
        }else{
            foreach(FossnirDir::where('auto_read', 1)->get() as $dir) {
                $this->readAndDownload($dir);
            }
        }
    }

    protected function readAndDownload($dir) {
        $smb = make(Samba::class);
        try {
            $files = $smb->dir($dir->dir_path);
            $tempDir = BASE_PATH . '/temp'. strtolower($dir->dir_path);

            if(! is_dir($tempDir)) {
                mkdir($tempDir, 0777);
            }
            foreach($files as $file) {
                if(! $file->isDirectory()) {
                    $tempFile = $tempDir . '/' . str_replace(' ', '_', $file->getName());
                    
                    $count = ResultFile::where('filename', $file->getName())
                        ->where('mill_id', $dir->id)
                        // ->where('filesize', $file->getSize())
                        ->count();

                    if($count == 0) {
                        ResultFile::create([
                            'mill_id' => $dir->id,
                            'filename' => $file->getName(),
                            'modified_at' => $file->getMTime(),
                            'filesize' => $file->getSize(),
                            'path' => $file->getPath(),
                            'download_path' => $tempFile
                        ]);
                        
                        // download
                        if($smb->download($file->getPath(), $tempFile)) {
            
                            try {
                                $smb->mkdir('ARCHIVES' . $dir->dir_path);
                            } catch (\Throwable $th) {
                                //throw $th;
                            }
                           
                            $smb->rename( $file->getPath(),  'ARCHIVES'. $file->getPath());
                        }
                    }
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            var_dump($th->getMessage());
        }
    }
}
