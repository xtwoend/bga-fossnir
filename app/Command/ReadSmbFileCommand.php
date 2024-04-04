<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Command;

use App\Model\FossnirDir;
use App\Model\ResultFile;
use App\Service\Samba;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Throwable;



use function Hyperf\Support\make;
use function Hyperf\Collection\collect;

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

    public function handle()
    {
        $mill_id = $this->input->getArgument('mill_id');

        if ($mill_id) {
            $dir = FossnirDir::find($mill_id);
            if ($dir) {
                $this->readAndDownload($dir);
            }
        } else {
            foreach (FossnirDir::where('auto_read', 1)->get() as $dir) {
                $this->readAndDownload($dir);
            }
        }
    }

    protected function getArguments()
    {
        return [
            ['mill_id', InputArgument::OPTIONAL, 'mill id'],
        ];
    }

    protected function readAndDownload($dir)
    {
        $smb = make(Samba::class);
        try {
            $files = $smb->dir($dir->dir_path);
            $tempDir = BASE_PATH . '/temp' . strtolower($dir->dir_path);

            if (! is_dir($tempDir)) {
                mkdir($tempDir, 0777);
            }

            if(count($files) > 200) {

                throw new \Exception("Telalu banyak files dalam folder", 422);
                
                // $collection =  collect($files);
                // $collection->splice(100);
                // $files = $collection->all();
            }
            
            foreach ($files as $file) {
                if (! $file->isDirectory()) {
                    $tempFile = $tempDir . '/' . str_replace(' ', '_', $file->getName());

                    $count = ResultFile::table($dir->id)->where('filename', $file->getName())
                        ->where('mill_id', $dir->id)
                        ->where('filesize', $file->getSize())
                        ->count();

                    if ($count == 0) {
                        ResultFile::table($dir->id)->create([
                            'mill_id' => $dir->id,
                            'filename' => $file->getName(),
                            'modified_at' => $file->getMTime(),
                            'filesize' => $file->getSize(),
                            'path' => $file->getPath(),
                            'download_path' => $tempFile,
                            'processed' => 0,
                        ]);

                        // download
                        if ($smb->download($file->getPath(), $tempFile)) {
                            $archivePath = $dir->dir_path . '/ARCHIVES';
                            try {
                                $smb->mkdir($archivePath);
                            } catch (Throwable $th) {
                                // throw $th;
                            }

                            $smb->rename($file->getPath(), $archivePath . '/' . $file->getName());
                        }
                    }
                }
            }
        } catch (Throwable $th) {
            // throw $th;
            var_dump($th->getMessage());
        }
    }
}
