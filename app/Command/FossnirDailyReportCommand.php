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

namespace App\Command;

use App\Model\FossnirData;
use App\Model\FossnirDir;
use App\Model\FossnirReportDaily;
use App\Model\Group;
use App\Model\GroupProduct;
use Carbon\Carbon;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\DbConnection\Db;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Input\InputArgument;

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
        $date = $this->input->getArgument('date');
        $now = $date ? Carbon::parse($date) : Carbon::now();
        $from = Carbon::parse($now->format('Y-m-d') . ' 05:00:00')->format('Y-m-d H:i:s');
        $to = Carbon::parse($now->format('Y-m-d') . ' 05:00:00')->addDay()->format('Y-m-d H:i:s');
        $interval = 2;

        foreach (FossnirDir::all() as $dir) {
            // calculate bedasarkan group fossnir mechines
            $groups = Group::all();
            foreach ($groups as $group) {
                $groupProducts = GroupProduct::where('mill_id', $dir->id)->where('group_id', $group->id)->get();

                $prs = [];
                foreach ($groupProducts as $g) {
                    $pr = FossnirData::table($dir->id)
                        ->select(Db::raw('count(*) as total, avg(owm) as owm, avg(vm) as vm, avg(odm) as odm, avg(nos) as nos'))
                        ->where('product_name', $g->product_name)
                        ->whereBetween('sample_date', [$from, $to])
                        ->groupBy('product_name')
                        ->get()
                        ->first();

                    if ($pr) {
                        $prs[] = $pr->toArray();
                    }
                }

                $collect = collect($prs);
                $max = $collect->max('total') ?? 0;

                $tt = $collect->map(function ($v) use ($interval) {
                    $v['owm_result'] = ($v['total'] * $interval) * $v['owm'];
                    $v['vm_result'] = ($v['total'] * $interval) * $v['vm'];
                    $v['odm_result'] = ($v['total'] * $interval) * $v['odm'];
                    $v['nos_result'] = ($v['total'] * $interval) * $v['nos'];

                    $v['total_2'] = $v['total'] * $interval;
                    return $v;
                })->all();

                $count = collect($tt)->sum('total_2');

                $owm = $count > 0 ? (collect($tt)->sum('owm_result') / $count) : 0;
                $vm = $count > 0 ? (collect($tt)->sum('vm_result') / $count) : 0;
                $odm = $count > 0 ? (collect($tt)->sum('odm_result') / $count) : 0;
                $nos = $count > 0 ? (collect($tt)->sum('nos_result') / $count) : 0;

                FossnirReportDaily::updateOrCreate([
                    'mill_id' => $dir->id,
                    'group_id' => $group->id,
                    'sample_date' => $now->format('Y-m-d'),
                ], [
                    'owm' => $owm,
                    'vm' => $vm,
                    'odm' => $odm,
                    'nos' => $nos,
                ]);
            }
        }
    }

    protected function getArguments()
    {
        return [
            ['date', InputArgument::OPTIONAL, 'date'],
        ];
    }
}
