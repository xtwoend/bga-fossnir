<?php

namespace App\Task;

use App\Model\Sample;
use App\Model\FossnirData;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Crontab\Annotation\Crontab;
use Hyperf\Contract\StdoutLoggerInterface;

#[Crontab(name: "ProcessSample", rule: "* * * * *", callback: "execute", memo: "process samples")]
class ProcessSample
{
    #[Inject]
    private StdoutLoggerInterface $logger;

    protected $client;
    protected $username;
    protected $password;

    public function __construct() {
        // 
    }

    public function execute()
    {
        $this->logger->info(date('Y-m-d H:i:s', time()));

        $samples = Sample::byDate(date('Y-m-d'))
            ->select(Db::raw("device_id as mill_id, 
                sample_date, 
                product_name, 
                MAX(IF(parameter='Oil/WM', result, 0)) as owm,
                MAX(IF(parameter='VM', result, 0)) as vm,
                MAX(IF(parameter='Oil/DM', result, 0)) as odm,
                MAX(IF(parameter='NOS', result, 0)) as nos"))
            ->where('status', 0)
            ->groupBy('device_id', 'sample_date', 'product_name')
            ->get();

        foreach($samples as $sample) {
            $fossnir_data = FossnirData::table($sample->mill_id)->updateOrCreate([
                'mill_id' => $sample->mill_id,
                'sample_date' => $sample->sample_date,
                'instrument_serial' => '--',
                'product_name' => $sample->product_name
            ],[
                'owm' => $sample->owm,
                'vm' => $sample->vm,
                'odm' => $sample->odm,
                'nos' => $sample->nos
            ]);

            $s = Sample::byDate(date('Y-m-d'))
            ->where([
                'device_id' => $sample->mill_id, 
                'sample_date' => $sample->sample_date,
                'product_name' => $sample->product_name,
            ])->update(['status' => 1]);
        }
    }
}