<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\News;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

#[Controller]
class NewsController
{

    #[RequestMapping(path: '/news', methods: 'get')]
    public function index(RequestInterface $request)
    {
        $news = News::with('mill');
        if($request->has('mill_id')) {
            $news = $news->where('mill_id', $request->input('mill_id'));
        }
        $news = $news->latest()->first();

        return response($news);
    }
}
