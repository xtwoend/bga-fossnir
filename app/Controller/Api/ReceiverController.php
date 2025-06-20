<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Model\FossnirData;
use App\Model\Sample;

use function Hyperf\Collection\collect;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

#[Controller]
class ReceiverController
{

    #[RequestMapping(path: '/api/oil/loses', methods: 'POST')]
    public function __invoke(RequestInterface $request, ResponseInterface $response)
    {

        $data = collect($request->input('data', []));

        $samples = [];
        $data->each(function($row) use (&$samples) {
            $rw = (object) $row;
            
            $samples[] = Sample::byDate($rw->sample_date)->updateOrCreate([
                'sample_date' => $rw->sample_date,
                'device_id' => $rw->device_id,
                'product_name' => $rw->product_name,
                'parameter' => $rw->parameter
            ], [
                'result' => (float) $rw->result
            ]);
        });

        
        // Trigger the event
       

        return $response->json([
            'error' => 0, 
            'message' => 'data successfuly record',
            'count' => count($samples)
        ], 200);
    }

    #[RequestMapping(path: '/api/samples', methods: 'GET')]
    public function last(RequestInterface $request, ResponseInterface $response)
    {
        $samples = Sample::byDate(date('Y-m-d'))->latest()->limit(50);

        return $response->json([
            'error' => 0, 
            'data' => $samples
        ], 200);
    }
}
