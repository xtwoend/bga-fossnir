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
use App\Model\FossnirDir;
use App\Service\Samba;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Paginator\LengthAwarePaginator;
use Hyperf\Resource\Json\AnonymousResourceCollection;
use Hyperf\Resource\Json\ResourceCollection;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Utils\Arr;
use Hyperf\Utils\Codec\Json;
use Psr\EventDispatcher\EventDispatcherInterface;

if (! function_exists('dispatch')) {
    function dispatch($event, int $priority = 1)
    {
        $eventDispatcher = ApplicationContext::getContainer()->get(EventDispatcherInterface::class);
        $eventDispatcher->dispatch($event, $priority);
    }
}

if (! function_exists('response')) {
    function response($data, int $code = 0, array $meta = [])
    {
        $response = ApplicationContext::getContainer()->get(ResponseInterface::class);
        $payload = [
            'error' => $code,
        ];
        if (is_string($data)) {
            $payload['message'] = $data;
            $data = null;
        }

        if ($data || is_array($data)) {
            $payload['data'] = $data;
        }

        if ($meta) {
            $payload['meta'] = $meta;
        }

        if ($data instanceof AnonymousResourceCollection || $data instanceof ResourceCollection) {
            if ($data->resource instanceof LengthAwarePaginator) {
                $payload['meta'] = Arr::except($data->resource->toArray(), [
                    'data',
                    'first_page_url',
                    'last_page_url',
                    'prev_page_url',
                    'next_page_url',
                ]);
            }
        }

        // $payload = mb_convert_encoding($payload, 'UTF-8', 'UTF-8');
        $payload = Json::encode($payload);

        return $response
            ->withStatus(200)
            ->withHeader('content-type', 'application/json')
            ->withBody(new SwooleStream($payload));
    }
}

if (! function_exists('export')) {
    function export(array $head, array $body, string $file_name)
    {
        $head_keys = array_keys($head);
        $head_values = array_values($head);
        $fileData = implode(',', $head_values) . "\n";

        if (strpos($file_name, '.') === false) {
            $file_name .= '.csv';
        }

        foreach ($body as $value) {
            $temp_arr = [];
            $value = json_decode(json_encode($value), true);
            foreach ($head_keys as $key) {
                $temp_arr[] = $value[$key] ?? '';
            }
            $fileData .= implode(',', $temp_arr) . "\n";
        }

        $response = ApplicationContext::getContainer()->get(ResponseInterface::class);
        $content_type = 'text/csv';

        return $response->withHeader('content-description', 'File Transfer')
            ->withHeader('content-type', $content_type)
            ->withHeader('content-disposition', "attachment; filename={$file_name}")
            ->withHeader('content-transfer-encoding', 'binary')
            ->withHeader('pragma', 'public')
            ->withBody(new SwooleStream($fileData));
    }
}

if (! function_exists('fossnir_file')) {
    function fossnir_file($mill_id)
    {
        $dir = FossnirDir::find($mill_id);
        if ($dir) {
            $smb = make(Samba::class);
            $files = $smb->dir($dir->dir_path);
            $count = 1;
            foreach ($files as $file) {
                echo 'filename : ' . $file->getName() . ' date: ' . $file->getMTime() . "\n";
                ++$count;
            }
            echo 'Jumlah File: ' . $count;
        }
    }
}

function tx($lm)
{
    $levels = [0, 1000, 2000, 3000, 4000, 5000, 6000, 7000];
    $temps = [];
    foreach ($levels as $k => $v) {
        if ($lm >= $v) {
            $rand = rand(30, 50);
            echo $rand . "\n";
            $temps[] = $rand;
        }
    }
    echo "----- \n";
    var_dump($temps);
    echo "----- \n";
    return round(array_sum($temps) / count($temps));
}
