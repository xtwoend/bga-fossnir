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
use App\Model\CSVRead;
use App\Model\ResultFile;
use App\Model\FossnirData;
use App\Event\NewFossnirData;
use Psr\Container\ContainerInterface;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class ReadCsvFileCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('fossnir:parse');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Parse csv fossnir file');
    }

    public function handle()
    {
        $mill_id = $this->input->getArgument('mill_id');

        if ($mill_id) {
            $files = ResultFile::table($mill_id)->where('processed', 0)->get();
            foreach ($files as $file) {
                $this->readFile($file);
            }
        } else {
            foreach (FossnirDir::where('auto_read', 1)->get() as $dir) {
                $files = ResultFile::table($dir->mill_id)->where('processed', 0)->get();
                foreach ($files as $file) {
                    $this->readFile($file);
                }
            }
        }
    }

    protected function getArguments()
    {
        return [
            ['mill_id', InputArgument::OPTIONAL, 'mill id'],
        ];
    }

    protected function readFile($file)
    {
        $temp_file = $file->download_path;

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

            if (! empty($data) && isset($data['owm'], $data['vm'], $data['odm'], $data['nos'])) {
                try {  
                    $fossnir_data = FossnirData::table($file->mill_id)->create($data);
                    \dispatch(new NewFossnirData($fossnir_data));
                    $file->update(['processed' => 1]);
                    if(file_exists($temp_file)) {
                        unlink($temp_file);
                    }
                } catch (\Throwable $th) {
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
