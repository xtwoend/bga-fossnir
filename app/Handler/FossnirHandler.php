<?php

namespace App\Handler;

use Carbon\Carbon;
use App\Model\FossnirDir;
use App\Model\FossnirData;
use App\Model\FossnirScore;
use App\Model\GroupProduct;
use Hyperf\DbConnection\Db;
use App\Model\FossnirThreshold;

class FossnirHandler
{
    public function count($date, $millId = 'all')
    {
        $hour = (int) $date->format('H');
        if($hour < 5) {
            $date = $date->subDay();
        }
        
        if($millId == 'all'){
            foreach(FossnirDir::orderBy('order')->get() as $mill)
            {
                $this->process($date, $mill);
            }
        }else{
            $mill = FossnirDir::find($millId);
            if($mill) {
                $this->process($date, $mill);
            }
        }
    }


    public function process($date, $mill) {
        var_dump($mill->id);

        $groups = GroupProduct::where('mill_id', $mill->id)->get();
        foreach($groups as $group)
        {
            $thresholds = FossnirThreshold::where('mill_id', $mill->id)
                ->where('group_id', $group->group_id)    
                ->whereIn('parameter', ['owm', 'vm', 'odm', 'nos'])
                ->get();

            $score = FossnirScore::table($mill->id)->updateOrCreate([
                'sample_date' => $date->format('Y-m-d'),
                'mill_id' => $mill->id,
                'product_name' => $group->product_name,
            ],[
                'threshold_owm' => ($thresholds?->where('parameter', 'owm')->first()?->threshold) ?: 0,
                'threshold_vm' => ($thresholds?->where('parameter', 'vm')->first()?->threshold) ?: 0,
                'threshold_odm' => ($thresholds?->where('parameter', 'odm')->first()?->threshold) ?: 0,
                'threshold_nos' => ($thresholds?->where('parameter', 'nos')->first()?->threshold) ?: 0,
            ]);

            $sDate = $score->sample_date->format('Y-m-d');

            // query to read data
            $count = FossnirData::table($mill->id)
                ->select(
                    Db::raw("
                        COUNT(*) as sample_count, 
                        COUNT(IF(owm <= {$score->threshold_owm}, 1, NULL)) AS conconformance_owm, 
                        COUNT(IF(vm <= {$score->threshold_vm}, 1, NULL)) AS conconformance_vm, 
                        COUNT(IF(odm <= {$score->threshold_odm}, 1, NULL)) AS conconformance_odm, 
                        COUNT(IF(nos <= {$score->threshold_nos}, 1, NULL)) AS conconformance_nos,
                        SUM(owm) AS owm,
                        SUM(vm) AS vm,
                        SUM(odm) AS odm,
                        SUM(nos) AS nos"
                ))
                ->where('product_name', $group->product_name)
                ->where('sample_date', '>=', Carbon::parse($sDate . ' 05:00:00')->format('Y-m-d H:i:s'))
                ->where('sample_date', '<', Carbon::parse($sDate . ' 05:00:00')->addDay()->format('Y-m-d H:i:s'))
                ->get()
                ->first();

            var_dump($mill->id, $count->toArray());

            $score->sample_count = $count->sample_count;
            $score->score_owm = $count->conconformance_owm;
            $score->score_vm = $count->conconformance_vm;
            $score->score_odm = $count->conconformance_odm;
            $score->score_nos = $count->conconformance_nos;

            $score->owm = $count->owm ?: 0;
            $score->vm = $count->vm ?: 0;
            $score->odm = $count->odm ?: 0;
            $score->nos = $count->nos ?: 0;
                    
            $score->save();

            usleep(100000);
        }
    }
}