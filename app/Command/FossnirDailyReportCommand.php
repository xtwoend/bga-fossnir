<?php

declare(strict_types=1);

namespace App\Command;

use Carbon\Carbon;
use App\Model\FossnirDir;
use App\Model\FossnirData;
use App\Model\GroupProduct;
use Hyperf\DbConnection\Db;
use App\Model\FossnirThreshold;
use Psr\Container\ContainerInterface;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;

#[Command]
class FossnirDailyReportCommand extends HyperfCommand
{
    public function __construct(protected ContainerInterface $container)
    {
        parent::__construct('fossnir:daily-save');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('Save daily result fossnir per mill');
    }

    public function handle()
    {
        // result
        $now = Carbon::now();
        $from = Carbon::parse($now->format('Y-m-d') . ' 05:00:00')->subDay()->format('Y-m-d H:i:s');
        $to = Carbon::parse($now->format('Y-m-d') . ' 05:00:00')->format('Y-m-d H:i:s');
        $interval = 2;

        foreach(FossnirDir::all() as $dir){
            
            // calculate bedasarkan group fossnir mechines
            $groups = FossnirData::select('product_name')
                ->where('mill_id', $dir->id)
                ->whereBetween('sample_date', [$from, $to])
                ->groupBy('product_name')
                ->get()->pluck('product_name')->toArray();

            $prs = [];
            foreach($groups as $g) {
                $pr = FossnirData::table($dir->id)
                    ->select(Db::raw("count(*) as total, avg(owm) as owm, avg(vm) as vm, avg(odm) as odm, avg(nos) as nos"))
                    ->where('product_name', $g)
                    ->whereBetween('sample_date', [$from, $to])
                    ->groupBy('product_name')
                    ->get()
                    ->first();
                
                if($pr) {  
                    $prs[] = $pr->toArray();
                }
            }
            $collect = collect($prs);
            $max = $collect->max('total') ?? 0;
            
            $tt = $collect->map(function($v) use ($interval) {
                $v['owm_result'] = ($v['total'] * $interval) * $v['owm'];
                $v['vm_result'] = ($v['total'] * $interval) * $v['vm'];
                $v['odm_result'] = ($v['total'] * $interval) * $v['odm'];
                $v['nos_result'] = ($v['total'] * $interval) * $v['nos'];

                $v['total_2'] = $v['total'] * $interval;
                return $v;
            })->all();
            
            $count = collect($tt)->sum('total_2');

            $owm = $count > 0 ? (collect($tt)->sum('owm_result') / $count): null;
            $vm = $count > 0 ? (collect($tt)->sum('vm_result') / $count): null;
            $odm = $count > 0 ? (collect($tt)->sum('odm_result') / $count): null;
            $nos = $count > 0 ? (collect($tt)->sum('nos_result') / $count): null;

            
        }
    }
}
