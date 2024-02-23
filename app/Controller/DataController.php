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
        foreach(FossnirDir::all() as $dir){
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

        $data = [];
        foreach(FossnirDir::all() as $dir){
            $data[] = [
                'id' => $dir->id,
                'mill' => $dir->mill_name,
                'result' => 4.1,
                'count' => 4,
                'threshold' => 4.0,
                'last_result' => 3.6,
                'last_time' => '13:01',
                'before_last_result' => null,
                'before_last_time' => null
            ];
        }

        return response($data);
    }
}
