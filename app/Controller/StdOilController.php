<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\StdOilLoss;
use App\Model\FossnirProduct;
use App\Resource\StdOilResource;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

#[Controller(prefix: "/std/oil")]
class StdOilController
{
    #[RequestMapping(path: "losses", methods: "get")]
    public function index(RequestInterface $request)
    {
        $perPage = (int) $request->input('rowsPerPage', 15);
        $millId = $request->input('mill_id', null);
    
        
        $rows = FossnirProduct::with('mill');
        if($millId) {
            $rows = $rows->where('mill_id', $millId);
        }
        $rows = $rows->paginate($perPage);

        return response(StdOilResource::collection($rows));
    }
    
    #[RequestMapping(path: "losses/{id}", methods: "get")]
    public function show($id, RequestInterface $request)
    {
        $row = FossnirProduct::find($id);
        return response(new StdOilResource($row));
    }

    #[RequestMapping(path: "losses", methods: "post")]
    public function create(RequestInterface $request)
    {   
        $productName = $request->input('product_name');
        if(is_array($productName)) {
            $productName = $productName['product_name'];
        }
        $row = FossnirProduct::updateOrCreate([
            'mill_id' => $request->input('mill_id'),
            'parameter' =>  ltrim(rtrim($request->input('parameter'))),
            'product_name' => ltrim(rtrim($productName))
        ], [
            'std_value' => $request->input('std_value'),
        ]);

        return response(new StdOilResource($row));
    }

    #[RequestMapping(path: "losses/{id}", methods: "delete")]
    public function destroy($id, RequestInterface $request)
    {
        $row = FossnirProduct::find($id);
        $message = $row->delete()? 'Success': 'Failed'; 
        return response($message);
    }


    #[RequestMapping(path: "products/{id}", methods: "get")]
    public function products($id, RequestInterface $request)
    {
        $rows = FossnirProduct::select('product_name')->where('mill_id', $id)->groupBy('product_name')->get()->pluck('product_name')->toArray();
        return response($rows);
    }
}
