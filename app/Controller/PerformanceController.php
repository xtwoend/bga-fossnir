<?php

declare(strict_types=1);

namespace App\Controller;

use Carbon\Carbon;
use App\Model\ScoreDaily;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

#[Controller]
class PerformanceController
{
    #[RequestMapping(path: '/mill/performance', methods: 'get')]
    public function score(RequestInterface $request)
    {
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

        $score = ScoreDaily::select(Db::raw("sum(`score_total`) stotal, sum(`score_count`) scount, `device_id`, mill_id"))
            ->where('mill_id', $millId)
            ->whereBetween('date', [$from, $to])
            ->groupBy('mill_id')
            ->groupBy('device_id')
            ->get();

        return response($score);
    }
}
