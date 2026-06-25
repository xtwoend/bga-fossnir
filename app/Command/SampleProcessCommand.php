<?php

declare(strict_types=1);

namespace App\Command;

use App\Model\Sample;
use App\Model\FossnirData;
use Hyperf\DbConnection\Db;
use Psr\Container\ContainerInterface;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;

#[Command]
class SampleProcessCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('foss:sample');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Process sample to fossnir data');
    }

    public function handle()
    {
        $date = date('Y-m-d');
        $chunkSize = 500;
        $processed = 0;
        $batch = 1;
        
        $this->line('Sample on processing', 'info');

        do {
            $samples = Sample::byDate($date)
                ->select(Db::raw("device_id as mill_id, 
                    sample_date, 
                    product_name, 
                    MAX(IF(parameter='Oil/WM', result, 0)) as owm,
                    MAX(IF(parameter='VM', result, 0)) as vm,
                    MAX(IF(parameter='Oil/DM', result, 0)) as odm,
                    MAX(IF(parameter='NOS', result, 0)) as nos"))
                ->where('status', 0)
                ->groupBy('device_id', 'sample_date', 'product_name')
                ->limit($chunkSize)
                ->get();

            foreach($samples as $sample) {
                FossnirData::table($sample->mill_id)->updateOrCreate([
                    'mill_id' => $sample->mill_id,
                    'sample_date' => $sample->sample_date,
                    'product_name' => $sample->product_name
                ],[
                    'instrument_serial' => 'N/A',
                    'owm' => $sample->owm,
                    'vm' => $sample->vm,
                    'odm' => $sample->odm,
                    'nos' => $sample->nos
                ]);

                Sample::byDate($date)
                    ->where([
                        'device_id' => $sample->mill_id, 
                        'sample_date' => $sample->sample_date,
                        'product_name' => $sample->product_name,
                    ])->update(['status' => 1]);
            }
            
            $count = count($samples);
            $processed += $count;
            $batch++;

            $this->line("Batch {$batch} total sample {$count}");

        } while (count($samples) === $chunkSize);

        $this->line("Sample processed: {$processed}", 'info');
    }
}
