<?php

declare(strict_types=1);

namespace App\Controller;

use Carbon\Carbon;
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
    #[RequestMapping(path: "/fossnir/data", methods: "get")]
    public function index(RequestInterface $request)
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $groupId = $request->input('group_id', 4);
        $resultName = 'owm';

        $from = Carbon::parse($date . ' 05:00:00')->format('Y-m-d H:i:s');
        $to = Carbon::parse($date . ' 05:00:00')->addDay()->format('Y-m-d H:i:s');

        $data = [];
        $threshold = (float) 4.0;
        foreach(FossnirDir::all() as $dir){
            $threshold = FossnirThreshold::where('mill_id', $dir->id)->where('group_id', $groupId)->first();

            $groups = GroupProduct::where('group_id', $groupId)->where('mill_id', $dir->id)->get()->pluck('product_name')->toArray();
            if($groups) {

                $inParams = implode("','", $groups);
                $tableName = FossnirData::table($dir->id)->getTable();

                // proses data dari jam 05 - 05 esok hari
                $queries = [];
                $interval = 2;
                for($i = 0; $i<12; $i++) {
                    $cFrom = Carbon::parse($from)->addHour($interval * $i)->format('Y-m-d H:i:s');
                    $cTo = Carbon::parse($from)->addHour($interval * ($i + 1))->format('Y-m-d H:i:s');
                    $hour = Carbon::parse($cTo)->format('H:i');
                    $queries[] = "SELECT 
                                -- STR_TO_DATE('{$cFrom}', '%Y-%m-%d %H:%i:%s') as startdate, 
                                -- STR_TO_DATE('{$cTo}', '%Y-%m-%d %H:%i:%s') as finishdate,
                                '{$hour}' as cycle_time,
                                avg({$resultName}) as result
                            FROM fossnir_data_8 
                            WHERE
                                sample_date BETWEEN '{$cFrom}' AND '{$cTo}'
                            AND product_name in ('{$inParams}')";
                }

                $query = implode(" UNION ", $queries);
                $data_results = Db::select($query);

                $data[$dir->mill_name] = [
                    "threshold" => $threshold?->threshold,
                    "data" => $data_results
                ];
            }
        }
        
        return response($data);
    }
}
