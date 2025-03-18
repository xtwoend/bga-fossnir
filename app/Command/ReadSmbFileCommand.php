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

use Throwable;
use Carbon\Carbon;
use App\Service\Samba;
use App\Model\FossnirDir;
use App\Model\ResultFile;
use function Hyperf\Support\make;
use Psr\Container\ContainerInterface;
use Hyperf\Command\Annotation\Command;



use function Hyperf\Collection\collect;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class ReadSmbFileCommand extends HyperfCommand
{
    protected $limit = 200;

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
        $this->limit = $this->input->getArgument('limit', 1000);
        
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
            ['limit', InputArgument::OPTIONAL, 'limi read file']
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

            $con = count($files);
            if($con > 500) {

                throw new \Exception("Telalu banyak files dalam folder di folder {$dir->dir_path} ({$con}) - limit ({$this->limit})", 422);
                
                // $collection =  collect($files);
                // $collection->splice(50);
                // $files = $collection->all();
            }
            
            foreach ($files as $file) {
                if (! $file->isDirectory()) {
                    // jika waktu modifikasi lebih dari 2 hari skip download
                    // if($file->getMTime() < Carbon::now()->subDays(2)->timestamp) {
                    //     continue;
                    // }

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
