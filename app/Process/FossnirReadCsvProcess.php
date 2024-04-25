<?php

declare(strict_types=1);

namespace App\Process;

use Throwable;
use Carbon\Carbon;
use App\Model\FossnirDir;
use App\Model\ResultFile;
use App\Model\FossnirData;
use App\Event\NewFossnirData;
use Hyperf\Process\AbstractProcess;
use Hyperf\Process\Annotation\Process;

#[Process(name: 'FossnirReadCsvProcess')]
class FossnirReadCsvProcess extends AbstractProcess
{
    public function handle(): void
    {
        while(true) {
            foreach (FossnirDir::where('auto_read', 1)->get() as $dir) {
                var_dump('On process process data '. $dir->mill_name);
                $files = ResultFile::table($dir->id)->where('processed', 0)->get();
                var_dump($files?->toArray());
                foreach ($files as $file) {
                    $this->readFile($file);
                }
            }
            sleep(900);
        }
    }

    public function isEnable($server): bool
    {
        return true;
    }

    protected function readFile($file)
    {
        $temp_file = $file->download_path;
        var_dump($temp_file);
        if (is_file($temp_file)) {
            $rows = array_map('str_getcsv', file($temp_file));
            $header = array_shift($rows);
            $csv = [];

            $data = [];

            foreach ($rows as $row) {
                try {
                    $regex = '/(\b\d{1,2}\D{0,3})?\b(?:Jan(?:uary)?|Feb(?:ruary)?|Mar(?:ch)?|Apr(?:il)?|May|Jun(?:e)?|Jul(?:y)?|Aug(?:ust)?|Sep(?:tember)?|Oct(?:ober)?|(Nov|Dec)(?:ember)?)\D?(\d{1,2}\D?)?\D?((19[7-9]\d|20\d{2})|\d{2})/';
                    if (strpos($row[1], '/')) {
                        if (strlen($row[2]) > 8) {
                            $dateCombine = Carbon::createFromFormat('m/d/Y g:i:sA', $row[1] . ' ' . $row[2]);
                        } else {
                            $dateCombine = Carbon::createFromFormat('m/d/Y H:i:s', $row[1] . ' ' . $row[2]);
                        }
                    } elseif (preg_match($regex, $row[1])) {
                        if (strlen($row[2]) > 8) {
                            $dateCombine = Carbon::createFromFormat('d-M-y g:i:sA', $row[1] . ' ' . $row[2]);
                        } else {
                            $dateCombine = Carbon::createFromFormat('d-M-y H:i:s', $row[1] . ' ' . $row[2]);
                        }
                    } else {
                        if (strlen($row[2]) > 8) {
                            $dateCombine = Carbon::createFromFormat('d-m-y g:i:sA', $row[1] . ' ' . $row[2]);
                        } else {
                            $dateCombine = Carbon::createFromFormat('d-m-y H:i:s', $row[1] . ' ' . $row[2]);
                        }
                    }

                    if ($dateCombine && $row[6] !== '' && $row[3] !== '') {
                        $data['mill_id'] = $file->mill_id;
                        $data['sample_date'] = date_format($dateCombine, 'Y-m-d H:i:s');
                        $data['instrument_serial'] = $row[4];
                        $data['product_name'] = $row[3];

                        if ($row[5] == 'Oil/WM') {
                            $data['owm'] = $row[6];
                        }

                        if ($row[5] == 'VM') {
                            $data['vm'] = $row[6];
                        }

                        if ($row[5] == 'Oil/DM') {
                            $data['odm'] = $row[6];
                        }

                        if ($row[5] == 'NOS') {
                            $data['nos'] = $row[6];
                        }
                    }
                } catch (Throwable $th) {
                    $file->update(['processed' => 1]);
                    var_dump($th->getMessage());

                    if(file_exists($temp_file)) {
                        unlink($temp_file);
                    }

                    continue;
                }
            }
            var_dump($data);
            if (! empty($data) && isset($data['owm'], $data['vm'], $data['odm'], $data['nos'])) {
                try {  
                    $fossnir_data = FossnirData::table($file->mill_id)->create($data);
                    \dispatch(new NewFossnirData($fossnir_data));
                    $file->update(['processed' => 1]);
                    if(file_exists($temp_file)) {
                        unlink($temp_file);
                    }
                } catch (Throwable $th) {
                    var_dump($th->getMessage());
                    if(file_exists($temp_file)) {
                        unlink($temp_file);
                    }
                }
            }else{
                var_dump('is data empty');
                $file->update(['processed' => 1]);
                if(file_exists($temp_file)) {
                    unlink($temp_file);
                }
            }
        }
    }
}
