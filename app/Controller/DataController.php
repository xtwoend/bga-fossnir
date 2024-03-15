<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\Controller;

use Carbon\Carbon;
use App\Model\Group;
use App\Model\FossnirDir;
use App\Model\FossnirData;
use App\Model\FossnirScore;
use App\Model\GroupProduct;
use Hyperf\DbConnection\Db;
use App\Model\FossnirThreshold;
use App\Model\FossnirReportDaily;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
class DataController
{
    protected array $parameters = [
        'owm' => 'Oil/WM',
        'vm' => 'VM',
        'odm' => 'Oil/DM',
        'nos' => 'NOS',
    ];

    #[RequestMapping(path: '/fossnir/stations', methods: 'get')]
    public function stations(RequestInterface $request)
    {
        $groups = Group::all();
    
        return response($groups);
    }

    #[RequestMapping(path: '/fossnir/data', methods: 'get')]
    public function index(RequestInterface $request)
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $groupId = $request->input('group_id', 4);
        $resultName = $request->input('parameter', 'owm');
        $interval = 2;
        $divinterval = intdiv(24, $interval);
        
        $mill_id = $request->input('mill_id');
       
        if($mill_id && $mill_id !== '999') {
            return $this->dataInMachine($request);
        }

        // bedasarkan cutoff tiap jam 5 pagi
        $from = Carbon::parse($date . ' 05:00:00')->format('Y-m-d H:i:s');
        $to = Carbon::parse($date . ' 05:00:00')->addDay()->format('Y-m-d H:i:s');

        $data = [];
        foreach (FossnirDir::orderBy('order')->get() as $dir) {
            $threshold = FossnirThreshold::where('mill_id', $dir->id)->where('group_id', $groupId)->where('parameter', $resultName)->first();

            $groups = GroupProduct::where('group_id', $groupId)->where('mill_id', $dir->id)->get()->pluck('product_name')->toArray();

            if (! empty($groups)) {
                $inParams = implode("','", $groups);
                $tableName = FossnirData::table($dir->id)->getTable();

                // proses data dari jam 05 - 05 esok hari
                $queries = [];
                for ($i = 0; $i < $divinterval; ++$i) {
                    $cFrom = Carbon::parse($from)->addHour($interval * $i)->format('Y-m-d H:i:s');
                    $cTo = Carbon::parse($from)->addHour($interval * ($i + 1))->format('Y-m-d H:i:s');
                    $hour = Carbon::parse($cTo)->format('H:i');
                    $queries[] = "SELECT
                                '{$hour}' as cycle_time,
                                count({$resultName}) as count_file,
                                avg({$resultName}) as result
                            FROM {$tableName} 
                            WHERE
                                sample_date BETWEEN '{$cFrom}' AND '{$cTo}'
                            AND product_name in ('{$inParams}')";
                }

                $query = implode(' UNION ', $queries);
                $data_results = Db::select($query);
                
                $count_sample = collect($data_results)->filter(function($v) {
                    return $v->result !== null;
                });
                $sample_count = count($count_sample);

                // result today
                $result = FossnirScore::table($dir->id)
                    ->select(Db::raw("sum(`{$resultName}`) as result, sum(`sample_count`) as count"))
                    ->where('sample_date', $date)
                    ->whereIn('product_name', $groups)
                    ->first();

                $data[] = [
                    'mill' => $dir->mill_name,
                    'parameter' => $this->parameters[$resultName] ?: '',
                    'threshold' => $threshold?->threshold,
                    'total_sample' => $sample_count,
                    'today' => ($result->count > 0) ? $result->result / $result->count : null,
                    'data' => $data_results,
                ];
            } else {
                $data_results = [];
                for ($j = 0; $j < $divinterval; ++$j) {
                    $cFrom = Carbon::parse($from)->addHour($interval * $j)->format('Y-m-d H:i:s');
                    $cTo = Carbon::parse($from)->addHour($interval * ($j + 1))->format('Y-m-d H:i:s');
                    $hour = Carbon::parse($cTo)->format('H:i');
                    $data_results[] = [
                        'cycle_time' => $hour,
                        'count_file' => 0,
                        'result' => null,
                    ];
                }
                $data[] = [
                    'mill' => $dir->mill_name,
                    'parameter' => $this->parameters[$resultName] ?: '',
                    'threshold' => $threshold?->threshold,
                    'total_sample' => 0,
                    'today' => null,
                    'data' => $data_results,
                ];
            }
        }

        return response($data);
    }

    private function dataInMachine(RequestInterface $request)
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $groupId = $request->input('group_id', 4);
        $resultName = $request->input('parameter', 'owm');
        $mill_id = $request->input('mill_id');

        // bedasarkan cutoff tiap jam 5 pagi
        $from = Carbon::parse($date . ' 05:00:00')->format('Y-m-d H:i:s');
        $to = Carbon::parse($date . ' 05:00:00')->addDay()->format('Y-m-d H:i:s');
        
        $threshold = FossnirThreshold::where('mill_id', $mill_id)->where('group_id', $groupId)->where('parameter', $resultName)->first();
        $groups = GroupProduct::where('group_id', $groupId)->where('mill_id', $mill_id)->get()->pluck('product_name')->toArray();
        $data = FossnirData::table($mill_id)
            ->whereBetween('sample_date', [$from, $to])
            ->whereIn('product_name', $groups)
            ->orderBy('sample_date', 'asc')
            ->get();
        $data->map(function($r) use ($threshold) {
            $r['threshold'] = $threshold?->threshold;
            return $r;
        });
        $data = $data->sortBy('sample_date')->groupBy('product_name');
        $data = $data->all();

        $arr = [];
        $max = 0;
        foreach($data as $r => $v) {
            $max = $max < count($v) ? count($v): $max;
            $c = collect($v);
            $arr[] = [
                'product_name' => $r,
                'threshold' => $threshold?->threshold,
                'avg' => $c->sum($resultName) / count($v),
                'data' => $v
            ];
        }

        return response($arr, 0, ['max' => $max]);
    }

    #[RequestMapping(path: '/fossnir/daily', methods: 'get')]
    public function daily(RequestInterface $request)
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $groupId = $request->input('group_id', 4);
        $resultName = $request->input('parameter', 'owm');
        $interval = 2;
        $divinterval = intdiv(24, $interval);

        $from = Carbon::parse($date . ' 05:00:00')->format('Y-m-d H:i:s');
        $to = Carbon::parse($date . ' 05:00:00')->addDay()->format('Y-m-d H:i:s');

        $data = [];
        foreach (FossnirDir::orderBy('order')->get() as $dir) {
            $threshold = FossnirThreshold::where('mill_id', $dir->id)->where('group_id', $groupId)->where('parameter', $resultName)->first();
            $groups = GroupProduct::where('group_id', $groupId)->where('mill_id', $dir->id)->get()->pluck('product_name')->toArray();
            
            // last & before last
            $inParams = implode("','", $groups);
            $tableName = FossnirData::table($dir->id)->getTable();

            // proses data dari jam 05 - 05 esok hari
            $queries = [];
            for ($i = 0; $i < $divinterval; ++$i) {
                $cFrom = Carbon::parse($from)->addHour($interval * $i)->format('Y-m-d H:i:s');
                $cTo = Carbon::parse($from)->addHour($interval * ($i + 1))->format('Y-m-d H:i:s');
                $hour = Carbon::parse($cTo)->format('H:i');
                $queries[] = "SELECT
                            '{$hour}' as cycle_time,
                            count({$resultName}) as count_file,
                            avg({$resultName}) as result,
                            max(sample_date) as sample_date
                        FROM {$tableName} 
                        WHERE
                            sample_date BETWEEN '{$cFrom}' AND '{$cTo}'
                        AND product_name in ('{$inParams}')";
            }

            $query = implode(' UNION ', $queries);
            $data_results = Db::select($query);
            $data_fil = array_filter($data_results, function($v) {
                return $v->result !== null;
            });
            $count_sample = count($data_fil);
            $coll = collect($data_fil)->sortByDesc('sample_date');
            $last = $coll->shift();
            $before_last = collect($coll->all())->first();

            // result today
            $result = FossnirScore::table($dir->id)
                ->select(Db::raw("sum(`{$resultName}`) as result, sum(`sample_count`) as count"))
                ->where('sample_date', $date)
                ->whereIn('product_name', $groups)
                ->first();

            $data[] = [
                'id' => $dir->id,
                'mill' => $dir->mill_name,
                'parameter' => $this->parameters[$resultName] ?: '',
                'result' => ($result->count > 0) ? $result->result / $result->count : null,
                'count' => $count_sample,
                'threshold' => $threshold?->threshold,
                'last_result' => $last ? $last->result : null,
                'last_time' => $last ? $last->cycle_time : null,
                'before_last_result' => $before_last ? $before_last->result: null,
                'before_last_time' => $before_last ? $before_last->cycle_time: null
            ];
        }

        return response($data);
    }

    #[RequestMapping(path: '/fossnir/grapic/daily', methods: 'get')]
    public function graficDaily(RequestInterface $request)
    {
        $date = $request->input('date', null);
        $date = $date ? Carbon::createFromDate($date['year'], ($date['month'] + 1), 1)->format('Y-m-d') : Carbon::now()->format('Y-m-d');

        $millId = $request->input('mill_id', 1);
        $groupId = $request->input('group_id', 4);
        $resultName = $request->input('parameter', 'owm');
        
        $interval = 2;
        $divinterval = intdiv(24, $interval);
        $data_results = [];

        // bedasarkan cutoff tiap jam 5 pagi
        $from = Carbon::parse($date . ' 05:00:00')->format('Y-m-d H:i:s');
        $to = Carbon::parse($date . ' 05:00:00')->addDay()->format('Y-m-d H:i:s');

        $threshold = FossnirThreshold::where('mill_id', $millId)->where('group_id', $groupId)->where('parameter', $resultName)->first();
        $groups = GroupProduct::where('group_id', $groupId)->where('mill_id', $millId)->get()->pluck('product_name')->toArray();

        if (! empty($groups)) {
            $inParams = implode("','", $groups);
            $tableName = FossnirData::table($millId)->getTable();

            // proses data dari jam 05 - 05 esok hari
            $queries = [];
            for ($i = 0; $i < $divinterval; ++$i) {
                $cFrom = Carbon::parse($from)->addHour($interval * $i)->format('Y-m-d H:i:s');
                $cTo = Carbon::parse($from)->addHour($interval * ($i + 1))->format('Y-m-d H:i:s');
                $hour = Carbon::parse($cTo)->format('H:i');
                $queries[] = "SELECT
                            '{$hour}' as cycle_time,
                            count({$resultName}) as count_file,
                            avg({$resultName}) as result
                        FROM {$tableName} 
                        WHERE
                            sample_date BETWEEN '{$cFrom}' AND '{$cTo}'
                        AND product_name in ('{$inParams}')";
            }

            $query = implode(' UNION ', $queries);
            $data_results = Db::select($query);

            $data = collect($data_results);

            // $data->map(function($row) use ($date) {
            //     $row->result = $row->result ?: 0;
            //     return $row;
            // });
        }
    

        return response($data, 0, [
            'threshold' => $threshold?->threshold,
            'parameter' => $resultName,
            'start_date' => $from
        ]);
    }

    #[RequestMapping(path: '/fossnir/grapic/monthly', methods: 'get')]
    public function graficMonthly(RequestInterface $request)
    {
        $date = $request->input('date', null);
        $millId = $request->input('mill_id', 1);
        $groupId = $request->input('group_id', 4);
        $resultName = $request->input('parameter', 'owm');

        $threshold = FossnirThreshold::where('mill_id', $millId)->where('group_id', $groupId)->where('parameter', $resultName)->first();
        $groups = GroupProduct::where('group_id', $groupId)->where('mill_id', $millId)->get()->pluck('product_name')->toArray();

        $from = $date ? Carbon::createFromDate($date['year'], ($date['month'] + 1), 1)->format('Y-m-d') : Carbon::now()->format('Y-m-01');
        $to = Carbon::parse($from)->endOfMonth()->format('Y-m-d');

        $data = FossnirData::table($millId)
            ->select(Db::raw("COUNT(IF(`$resultName` > 0, 1, NULL)) as total, sum(`{$resultName}`) as sum_result,  sum(`{$resultName}`)  / COUNT(IF(`$resultName` > 0, 1, NULL)) as result, DATE(sample_date) as tgl"))
            ->whereIn('product_name', $groups)
            ->whereBetween('sample_date', [$from, $to])
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get();
        
        return response($data, 0, [
            'threshold' => $threshold?->threshold,
            'parameter' => $resultName,
            'start_date' => $from
        ]);
    }

    #[RequestMapping(path: '/fossnir/grapic/yearly', methods: 'get')]
    public function graficYearly(RequestInterface $request)
    {
        $date = $request->input('date', null);
        $millId = $request->input('mill_id', 1);
        $groupId = $request->input('group_id', 4);
        $resultName = $request->input('parameter', 'owm');

        $threshold = FossnirThreshold::where('mill_id', $millId)->where('group_id', $groupId)->where('parameter', $resultName)->first();

        $date = $date ? Carbon::createFromDate($date['year'], ($date['month'] + 1), 1) : Carbon::now();
        $from = $date->copy()->startOfYear();
        $to   = $date->copy()->endOfYear();

        $data = FossnirReportDaily::select(Db::raw("SUM(`$resultName`) as result_sum, COUNT(IF(`$resultName` > 0, 1, NULL)) as sam_count, SUM(`$resultName`) / COUNT(IF(`$resultName` > 0, 1, NULL)) as result , YEAR(sample_date) as year, MONTH(sample_date) as month"))
            ->where('mill_id', $millId)
            ->where('group_id', $groupId)
            ->whereBetween('sample_date', [$from, $to])
            ->groupBy('month', 'year')
            ->orderBy('month')
            ->get();

        $data->map(function($row) {
            $row['tgl'] = Carbon::createFromDate($row->year, $row->month, 1)->format('Y-m-d');
        });

        return response($data, 0, [
            'threshold' => $threshold?->threshold,
            'parameter' => $resultName,
            'start_date' => $from
        ]);
    }

    #[RequestMapping(path: '/fossnir/score', methods: 'get')]
    public function score(RequestInterface $request)
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));

        $resultName = $request->input('parameter', 'owm');
        $sc = "score_{$resultName}";

        $data = [];
        foreach (FossnirDir::orderBy('order')->get() as $dir) {
            // result today
            $result = FossnirScore::table($dir->id)
                ->select(Db::raw("sum(`{$sc}`) as result, sum(`sample_count`) as count"))
                ->where('sample_date', $date)
                ->first();

            $data[] = [
                'id' => $dir->id,
                'mill' => $dir->mill_name,
                'score' => ($result->count > 0) ? ($result->result / $result->count) * 100 : 0,
            ];
        }

        return response($data);
    }


    #[RequestMapping(path: '/fossnir/total-score', methods: 'get')]
    public function scoreAll(RequestInterface $request)
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));

        $resultName = $request->input('parameter', 'owm');
        $sc = "score_{$resultName}";

        $data = [];
        foreach (FossnirDir::orderBy('order')->get() as $dir) {
            $result = FossnirScore::table($dir->id)
                ->select(Db::raw("sum(`{$sc}`) as result, sum(`sample_count`) as count"))
                ->where('sample_date', $date)
                ->first();

            $data[] = [
                'id' => $dir->id,
                'mill' => $dir->mill_name,
                'result' => $result->result,
                'count' => $result->count
            ];
        }

        $collection = collect($data);
        $result = $collection->sum('result');
        $count = $collection->sum('count');

        $score  = $count > 0 ? $result / $count : 0;
        $score  = (int) ($score * 100);

        return response([
            'score' => $score,
        ]);
    }

    #[RequestMapping(path: '/fossnir/score-mill', methods: 'get')]
    public function scoreMill(RequestInterface $request)
    {
        $resultName = $request->input('parameter', 'owm');
        $sc = "score_{$resultName}";
        
        $millId = $request->input('mill_id');
        $type = $request->input('type');
        $pDate = $request->input('date', null);

        $date = $pDate ? Carbon::parse($pDate): Carbon::now();

        $from = $date->copy()->format('Y-m-d');
        $to = $date->copy()->format('Y-m-d');
  
        if($type == 'month'){
            $from = $date->copy()->startOfMonth()->format('Y-m-d');
            $to = $date->copy()->endOfMonth()->format('Y-m-d H:i:s');
        }

        if($type == 'year'){
            $from = $date->copy()->firstOfYear()->format('Y-m-d');
            $to = $date->copy()->endOfYear()->format('Y-m-d');
        }

       
        // result today
        $result = FossnirScore::table($millId)
            ->select(Db::raw("sum(`{$sc}`) as result, sum(`sample_count`) as count"))
            ->whereBetween('sample_date', [$from, $to])
            ->first();

        $data = [
            'score' => ($result->count > 0) ? ($result->result / $result->count) * 100 : 0,
        ];

        return response($data);
    }
}
