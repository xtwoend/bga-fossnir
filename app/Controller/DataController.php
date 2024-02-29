<?php

declare(strict_types=1);

namespace App\Controller;

use Carbon\Carbon;
use App\Model\Group;
use App\Model\FossnirDir;
use App\Model\FossnirData;
use App\Model\GroupProduct;
use Hyperf\DbConnection\Db;
use App\Model\FossnirThreshold;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

#[Controller]
class DataController
{
    protected array $parameters = [
        'owm' => 'Oil/WM',
        'vm' => 'VM',
        'odm' => 'Oil/DM',
        'nos' => 'NOS'
    ];

    #[RequestMapping(path: "/fossnir/stations", methods: "get")]
    public function stations(RequestInterface $request)
    {
        $groups = Group::all();

        return response($groups);
    }

    #[RequestMapping(path: "/fossnir/data", methods: "get")]
    public function index(RequestInterface $request)
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $groupId = $request->input('group_id', 4);
        $resultName = $request->input('parameter', 'owm');
        $interval = 2;
        $divinterval = intdiv(24, $interval);

        // bedasarkan cutoff tiap jam 5 pagi
        $from = Carbon::parse($date . ' 05:00:00')->format('Y-m-d H:i:s');

        $data = [];
        foreach(FossnirDir::orderBy('order')->get() as $dir){
            $threshold = FossnirThreshold::where('mill_id', $dir->id)->where('group_id', $groupId)->where('parameter', $resultName)->first();

            $groups = GroupProduct::where('group_id', $groupId)->where('mill_id', $dir->id)->get()->pluck('product_name')->toArray();
            
            if(! empty($groups)) {

                $inParams = implode("','", $groups);
                $tableName = FossnirData::table($dir->id)->getTable();

                // proses data dari jam 05 - 05 esok hari
                $queries = [];
                for($i = 0; $i < $divinterval; $i++) {
                    $cFrom = Carbon::parse($from)->addHour($interval * $i)->format('Y-m-d H:i:s');
                    $cTo = Carbon::parse($from)->addHour($interval * ($i + 1))->format('Y-m-d H:i:s');
                    $hour = Carbon::parse($cTo)->format('H:i');
                    $queries[] = "SELECT
                                '{$hour}' as cycle_time,
                                avg({$resultName}) as result
                            FROM {$tableName} 
                            WHERE
                                sample_date BETWEEN '{$cFrom}' AND '{$cTo}'
                            AND product_name in ('{$inParams}')";
                }

                $query = implode(" UNION ", $queries);
                $data_results = Db::select($query);

                $avg = collect($data_results)->avg('result');
                
                $data[] = [
                    "mill" => $dir->mill_name,
                    "parameter" => $this->parameters[$resultName] ?: '',
                    "threshold" => $threshold?->threshold,
                    "today" => $avg,
                    "data" => $data_results
                ];
            }else{
                $data_results = [];
                for($j = 0; $j < $divinterval; $j++) {
                    $cFrom = Carbon::parse($from)->addHour($interval * $j)->format('Y-m-d H:i:s');
                    $cTo = Carbon::parse($from)->addHour($interval * ($j + 1))->format('Y-m-d H:i:s');
                    $hour = Carbon::parse($cTo)->format('H:i');
                    $data_results[] = [
                        'cycle_time' => $hour,
                        'result' => null,
                    ];
                }
                $data[] = [
                    "mill" => $dir->mill_name,
                    "parameter" => $this->parameters[$resultName] ?: '',
                    "threshold" => $threshold?->threshold,
                    "today" => null,
                    "data" => $data_results
                ];
            }
        }
        
        return response($data);
    }

    #[RequestMapping(path: "/fossnir/daily", methods: "get")]
    public function daily(RequestInterface $request)
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $groupId = $request->input('group_id', 4);
        $resultName = $request->input('parameter', 'owm');
        $interval = 2;

        $from = Carbon::parse($date . ' 05:00:00')->format('Y-m-d H:i:s');
        $to = Carbon::parse($date . ' 05:00:00')->addDay()->format('Y-m-d H:i:s');
       
        $data = [];
        foreach(FossnirDir::orderBy('order')->get() as $dir){
            $threshold = FossnirThreshold::where('mill_id', $dir->id)->where('group_id', $groupId)->where('parameter', $resultName)->first();
            $groups = GroupProduct::where('group_id', $groupId)->where('mill_id', $dir->id)->get()->pluck('product_name')->toArray();
            $getLastDate = FossnirData::table($dir->id)
                ->whereIn('product_name', $groups)
                ->whereBetween('sample_date', [$from, $to])
                ->orderBy('sample_date', 'desc')
                ->first()?->sample_date;

            // last
            $fromLastDate = Carbon::parse($getLastDate)->subHour($interval)->format('Y-m-d H:i:s');
            $last = FossnirData::table($dir->id)
                ->whereIn('product_name', $groups)
                ->whereBetween('sample_date', [$fromLastDate, $getLastDate])
                ->avg($resultName);

            // before last
            $beforeLast = FossnirData::table($dir->id)
                ->whereIn('product_name', $groups)
                ->whereBetween('sample_date', [Carbon::parse($fromLastDate)->subHour($interval)->format('Y-m-d H:i:s'), $fromLastDate])
                ->avg($resultName);

            // result
            $prs = [];
            foreach($groups as $g) {
                $pr = FossnirData::table($dir->id)
                    ->select(Db::raw("count(*) as total, avg({$resultName}) as avg"))
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
                $v['sum'] = ($v['total'] * $interval) * $v['avg'];
                $v['total_2'] = $v['total'] * $interval;
                return $v;
            })->all();

            $total = collect($tt)->sum('sum');
            $count = collect($tt)->sum('total_2');

            $result = $count > 0 ? ($total / $count) : null;

            $data[] = [
                'id' => $dir->id,
                'mill' => $dir->mill_name,
                'parameter' => $this->parameters[$resultName] ?: '',
                'result' => $result,
                'count' => $max,
                'threshold' => $threshold?->threshold,
                'last_result' => $last,
                'last_time' => $getLastDate?->format('H:00'),
                'before_last_result' => $beforeLast,
                'before_last_time' => $beforeLast? Carbon::parse($fromLastDate)->format('H:00') : null,
            ];
        }

        return response($data);
    }

    #[RequestMapping(path: "/fossnir/grapic/monthly", methods: "get")]
    public function graficMonthly(RequestInterface $request)
    {
        $now = Carbon::now();

        $data = [];

        return response($data);
    }
}
