<?php

namespace App\Task;

use App\Model\Sample;
use App\Model\FossnirData;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Contract\StdoutLoggerInterface;

#[Crontab(name: "ProcessSample", rule: "*/10 * * * *", callback: "execute", memo: "process samples")]
class ProcessSample
{
    #[Inject]
    private StdoutLoggerInterface $logger;

    public function __construct() {
        // 
    }

    public function execute()
    {
        $date = date('Y-m-d');
        $chunkSize = 500;
        $processed = 0;

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

            $processed += count($samples);
        } while (count($samples) === $chunkSize);

        $this->logger->info("Sample processed: {$processed}");
    }
}
