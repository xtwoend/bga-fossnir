<?php

declare(strict_types=1);

namespace App\Process;

use Throwable;
use App\Service\Samba;
use App\Model\FossnirDir;
use App\Model\ResultFile;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: 'FossnirReadProcess')]
class FossnirReadProcess extends AbstractProcess
{
    public function handle(): void
    {
        while (true) {
            foreach (FossnirDir::where('auto_read', 1)->get() as $dir) {
                var_dump('On process check folder '. $dir->mill_name);
                $this->readAndDownload($dir);
            }
            sleep(1200);
        }
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

            if(count($files) > 1000) {

                throw new \Exception("Telalu banyak files dalam folder", 422);
                
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
