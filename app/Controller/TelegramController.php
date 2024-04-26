<?php

declare(strict_types=1);

namespace App\Controller;

use App\Model\TelegramUser;
use App\Resource\DataResource;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
class TelegramController
{
    #[RequestMapping(path: '/telegram/users', methods: 'get')]
    public function users(RequestInterface $request)
    {
        $users = TelegramUser::with('mill')->paginate(25);

        return response(DataResource::collection($users));
    }
}
