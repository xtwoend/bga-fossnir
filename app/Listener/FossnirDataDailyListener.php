<?php

declare(strict_types=1);

namespace App\Listener;

use Carbon\Carbon;
use App\Model\FossnirScore;
use App\Model\GroupProduct;
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
                'threshold_owm' => $thresholds?->where('parameter', 'owm')->first()?->threshold,
                'threshold_vm' => $thresholds?->where('parameter', 'vm')->first()?->threshold,
                'threshold_odm' => $thresholds?->where('parameter', 'odm')->first()?->threshold,
                'threshold_nos' => $thresholds?->where('parameter', 'nos')->first()?->threshold,
            ]);

            $owm = ($score->threshold_owm >= $data->owm)? 1 : 0;
            $vm = ($score->threshold_vm >= $data->vm)? 1 : 0;
            $odm = ($score->threshold_odm >= $data->odm) ? 1 : 0;
            $nos = ($score->threshold_nos >= $data->nos) ? 1 : 0;

            $score->sample_count = $score->sample_count + 1;
            $score->score_owm = $score->score_owm + $owm;
            $score->score_vm = $score->score_vm + $vm;
            $score->score_odm = $score->score_odm + $odm;
            $score->score_nos = $score->score_nos + $nos;
            $score->owm = $score->owm + $data->owm;
            $score->vm = $score->vm + $data->vm;
            $score->odm = $score->odm + $data->odm;
            $score->nos = $score->nos + $data->nos;

            $score->save();
        }
    }
}
