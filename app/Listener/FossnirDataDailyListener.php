<?php

declare(strict_types=1);

namespace App\Listener;

use Carbon\Carbon;
use App\Model\FossnirData;
use App\Model\FossnirScore;
use App\Model\GroupProduct;
use Hyperf\DbConnection\Db;
use App\Event\NewFossnirData;
use App\Model\FossnirThreshold;
use Hyperf\Event\Annotation\Listener;
use Psr\Container\ContainerInterface;
use Hyperf\Event\Contract\ListenerInterface;

#[Listener]
class FossnirDataDailyListener implements ListenerInterface
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function listen(): array
    {
        return [
            NewFossnirData::class,
        ];
    }

    public function process(object $event): void
    {
        $data = $event->data;
        $mill_id = $data->mill_id;
        $date = Carbon::parse($data->sample_date);
        $hour = (int) $date->format('H');
        if($hour < 5) {
            $date = $date->subDay();
        }

        $group = GroupProduct::where('mill_id', $mill_id)->where('product_name', $data->product_name)->first();

        if($group) {
            
            $thresholds = FossnirThreshold::where('mill_id', $mill_id)
                ->where('group_id', $group->group_id)    
                ->whereIn('parameter', ['owm', 'vm', 'odm', 'nos'])
                ->get();

            $score = FossnirScore::table($mill_id)->firstOrCreate([
                'sample_date' => $date->format('Y-m-d'),
                'mill_id' => $mill_id,
                'product_name' => $group->product_name,
            ],[
                'threshold_owm' => $thresholds?->where('parameter', 'owm')->first()?->threshold,
                'threshold_vm' => $thresholds?->where('parameter', 'vm')->first()?->threshold,
                'threshold_odm' => $thresholds?->where('parameter', 'odm')->first()?->threshold,
                'threshold_nos' => $thresholds?->where('parameter', 'nos')->first()?->threshold,
            ]);

            // query to read data
            $count = FossnirData::table($mill_id)
                ->select(Db::raw("COUNT(*) as sample_count, COUNT(IF(owm <= {$score->threshold_owm}, 1, NULL)) AS conconformance_owm, COUNT(IF(vm <= {$score->threshold_vm}, 1, NULL)) AS conconformance_vm, COUNT(IF(odm <= {$score->threshold_odm}, 1, NULL)) AS conconformance_odm, COUNT(IF(nos <= {$score->threshold_nos}, 1, NULL)) AS conconformance_nos"))
                ->where('product_name', $data->product_name)
                ->where('sample_date', '>=', Carbon::parse($date . ' 05:00:00')->format('Y-m-d H:i:s'))
                ->where('sample_date', '<', Carbon::parse($date . ' 05:00:00')->addDay()->format('Y-m-d H:i:s'))
                ->get()
                ->first();

            $score->sample_count = $count->sample_count;
            $score->score_owm = $count->conconformance_owm;
            $score->score_vm = $count->conconformance_vm;
            $score->score_odm = $count->conconformance_odm;
            $score->score_nos = $count->conconformance_nos;

            $score->save();
        }
    }
}
