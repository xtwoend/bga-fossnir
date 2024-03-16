<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\FossnirData;
use App\Model\GroupProduct;
use App\Resource\DataResource;
use App\Model\FossnirThreshold;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
class ThresholdController
{
    #[RequestMapping(path: 'thresholds', methods: 'get')]
    public function threshold(RequestInterface $request)
    {
        $millId = $request->input('mill_id');
        $prameter = $request->input('parameter', null);
        $groupId = $request->input('group_id', null);

        $data = FossnirThreshold::with(['mill', 'group'])->where('mill_id', $millId);
        // if($prameter) {
        //     $data = $data->where('parameter', $prameter);
        // }
        // if($groupId) {
        //     $data = $data->where('group_id', $groupId);
        // }

        $data = $data->paginate(200);

        return response(DataResource::collection($data));
    }

    #[RequestMapping(path: 'threshold-update', methods: 'post')]
    public function thresholdUpdate(RequestInterface $request)
    {
        $millId = $request->input('mill_id');
        $parameter = $request->input('parameter', null);
        $groupId = $request->input('group_id', null);
        
        $data = FossnirThreshold::updateOrCreate([
            'mill_id' => $millId,
            'group_id' => $groupId,
            'parameter' => $parameter
        ], [
            'threshold' => $request->input('threshold')
        ]);
       
        return response(new DataResource($data));
    }

    #[RequestMapping(path: '/threshold-delete/{id}', methods: 'post')]
    public function thresholdDelete($id, RequestInterface $request)
    {
        $data = FossnirThreshold::find($id)->delete();
       
        return response('Success');
    }

    #[RequestMapping(path: 'groups', methods: 'get')]
    public function groups(RequestInterface $request)
    {
        $millId = $request->input('mill_id', null);
        $groupId = $request->input('group_id', null);

        $data = GroupProduct::with(['mill', 'group']);

        if($millId) {
            $data = $data->where('mill_id', $millId);
        }

        $data = $data->paginate(200);

        return response(DataResource::collection($data));
    }

    #[RequestMapping(path: 'group-update', methods: 'post')]
    public function groupUpdate(RequestInterface $request)
    {
        $millId = $request->input('mill_id', null);
        $groupId = $request->input('group_id', null);

        $data = GroupProduct::updateOrCreate([
            'mill_id' => $millId,
            'group_id' => $groupId,
            'product_name' => $request->input('product_name')
        ]);

        return response(new DataResource($data));
    }

    #[RequestMapping(path: '/group-delete/{id}', methods: 'post')]
    public function groupDelete($id, RequestInterface $request)
    {
        $data = GroupProduct::find($id)->delete();

        return response('success');
    }

    #[RequestMapping(path: 'products', methods: 'get')]
    public function products(RequestInterface $request)
    {
        $millId = $request->input('mill_id', null);

        $data = FossnirData::table($millId)
            ->select('product_name')
            ->groupBy('product_name')
            ->get()
            ->pluck('product_name')
            ->toArray();

        return response($data);
    }
}
