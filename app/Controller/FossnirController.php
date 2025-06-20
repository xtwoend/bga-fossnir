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

use App\Model\CSVRead;
use App\Model\FossnirDir;
use App\Model\FossnirProduct;
use App\Resource\FossnirDirResource;
use Carbon\Carbon;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

use function Hyperf\Collection\collect;

#[Controller(prefix: '/fossnir')]
class FossnirController
{
    #[RequestMapping(path: 'mill', methods: 'get')]
    public function index(RequestInterface $request)
    {
        $dirs = FossnirDir::paginate(10);

        return response(FossnirDirResource::collection($dirs));
    }

    #[RequestMapping(path: 'mill/{id}', methods: 'get')]
    public function show($id, RequestInterface $request)
    {
        $dir = FossnirDir::find($id);

        return response(new FossnirDirResource($dir));
    }

    #[RequestMapping(path: 'mill/{id}', methods: 'put')]
    public function update(RequestInterface $request)
    {
        $dir = FossnirDir::find($id);
        $dir->fill($request->all());
        $dir->save();

        return response(new FossnirDirResource($dir));
    }

    #[RequestMapping(path: 'mill/{id}', methods: 'delete')]
    public function destroy($id, RequestInterface $request)
    {
        $dir = FossnirDir::find($id)->delete();
        if ($dir) {
            return response('success');
        }

        return response('failed');
    }

    #[RequestMapping(path: 'report/{id}', methods: 'get')]
    public function report($id, RequestInterface $request)
    {
        $from = $request->input('from', Carbon::now()->subDay()->format('Y-m-d'));
        $to = $request->input('to', Carbon::now()->format('Y-m-d'));

        $mill = FossnirDir::find($id);

        $data = [];

        if ($product = $request->input('product', false)) {
            $result = CSVRead::table($mill->id)
                ->select('id', 'sample_date', 'product_name', 'parameter', 'result')
                ->whereDate('sample_date', '>=', $from)
                ->whereDate('sample_date', '<=', $to)
                ->where('product_name', $product);

            if ($request->input('parameter', false)) {
                $result = $result->where('parameter', $request->input('parameter'));
            }

            $result = $result->latest()->get()->toArray();

            $grouped = collect($result)->groupBy('parameter');

            $data = $grouped->all();
        }

        return response($data);
    }

    #[RequestMapping(path: 'report/{id}/losses', methods: 'get')]
    public function reportParameter($id, RequestInterface $request)
    {
        $from = $request->input('from', Carbon::now()->subDay()->format('Y-m-d'));
        $to = $request->input('to', Carbon::now()->format('Y-m-d'));
        $parameter = $request->input('parameter', 'Oil/WM');
        $mill = FossnirDir::find($id);

        $data = [];

        $result = CSVRead::table($mill->id)
            ->select('id', 'sample_date', 'instrument_serial', 'product_name', 'parameter', 'result')
            ->whereDate('sample_date', '>=', $from)
            ->whereDate('sample_date', '<=', $to)
            ->where('parameter', $parameter)
            ->orderBy('sample_date')
            ->get()
            ->toArray();

        $grouped = collect($result)->groupBy('product_name');
        $data = $grouped->all();
        ksort($data);

        return response($data);
    }

    #[RequestMapping(path: 'products/{id}', methods: 'get')]
    public function products($id, RequestInterface $request)
    {
        $from = $request->input('from', Carbon::now()->subDay()->format('Y-m-d'));
        $to = $request->input('to', Carbon::now()->format('Y-m-d'));

        $mill = FossnirDir::find($id);

        $productNames = CSVRead::table($mill->id)
            ->select('product_name')
            ->whereDate('sample_date', '>=', $from)
            ->whereDate('sample_date', '<=', $to)
            ->groupBy('product_name')
            ->get();

        return response(
            $productNames->pluck('product_name')->toArray()
        );
    }

    #[RequestMapping(path: 'mills', methods: 'get')]
    public function mill(RequestInterface $request)
    {
        $mills = FossnirDir::select('id', 'mill_name', 'order')->orderBy('order')->get();
        $mills->push(['id' => 999, 'mill_name' => 'ALL MILL', 'order' => 0]);

        return response($mills);
    }

    #[RequestMapping(path: 'dailyc', methods: 'get')]
    public function daily(RequestInterface $request)
    {
        $date = $request->input('date', Carbon::now()->format('Y-m-d'));
        $parameter = $request->input('parameter', 'Oil/WM');
        $millId = $request->input('mill_id', 12);

        $paramters = [
            'Oil/WM' => 4,
            'VM' => 4,
            'Oil/DM' => 50,
            'NOS' => 4,
        ];

        $mill = FossnirDir::find($millId);
        $from = Carbon::parse($date . ' 08:00:00')->format('Y-m-d H:i:s');
        $to = Carbon::parse($date . ' 08:00:00')->addDay()->format('Y-m-d H:i:s');

        $data = [];
        if ($mill) {
            $products = CSVRead::table($mill->id)
                ->select('product_name')
                ->whereBetween('sample_date', [$from, $to])
                ->where('parameter', $parameter)
                ->groupBy('product_name')
                ->orderBy('product_name')
                ->get();

            $max = 5;
            foreach ($products as $product) {
                $r = CSVRead::table($mill->id)
                    ->whereBetween('sample_date', [$from, $to])
                    ->where('parameter', $parameter)
                    ->where('product_name', $product->product_name)
                    ->orderBy('sample_date')
                    ->get();

                $avg = CSVRead::table($mill->id)
                    ->selectRaw('AVG(result) as avg_result')
                    ->whereBetween('sample_date', [$from, $to])
                    ->where('parameter', $parameter)
                    ->where('product_name', $product->product_name)
                    ->orderBy('sample_date')
                    ->get()
                    ->first();

                $std = FossnirProduct::where('mill_id', $mill->id)
                    ->where('parameter', $parameter)
                    ->where('product_name', $product->product_name)
                    ->first();

                $data[] = [
                    'product_name' => $product->product_name,
                    'avg' => $avg->avg_result,
                    'std_value' => $std?->std_value ?: ($paramters[$parameter] ?? 4.0),
                    'data' => $r,
                ];

                $max = ($max < count($r)) ? count($r) : $max;
            }
        }

        return response($data, 0, ['data_max' => $max]);
    }
}
