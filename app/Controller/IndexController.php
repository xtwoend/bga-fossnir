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
use App\Model\FossnirData;
use Hyperf\DbConnection\Db;

class IndexController extends AbstractController
{
    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        $date = '2024-03-02';

        $data = (object) [
            'product_name' => 'Press Cake Fibre No. 2',
        ];

        $score = (object) [
            'threshold_owm' => 4,
            'threshold_vm' => 4,
            'threshold_odm' => 3,
            'threshold_nos' => 1,
        ];
        $mill_id = 1;

        $count = FossnirData::table($mill_id)
                ->select(Db::raw("COUNT(*) as sample_count, COUNT(IF(owm <= {$score->threshold_owm}, 1, NULL)) AS conconformance_owm, COUNT(IF(vm <= {$score->threshold_vm}, 1, NULL)) AS conconformance_vm, COUNT(IF(odm <= {$score->threshold_odm}, 1, NULL)) AS conconformance_odm, COUNT(IF(nos <= {$score->threshold_nos}, 1, NULL)) AS conconformance_nos"))
                ->where('product_name', $data->product_name)
                ->where('sample_date', '>=', Carbon::parse($date . ' 05:00:00')->format('Y-m-d H:i:s'))
                ->where('sample_date', '<', Carbon::parse($date . ' 05:00:00')->addDay()->format('Y-m-d H:i:s'))
                ->get()
                ->first();
                
        return [
            'method' => $method,
            'message' => "Hello {$user}.",
        ];
    }
}
