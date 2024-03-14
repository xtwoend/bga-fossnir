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

namespace App\Service;

use App\Service\Telegram;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;

class TelegramFactory
{
    protected $container;

    protected $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
    }

    public function __invoke()
    {
        $config = $this->config->get('telegram');
        return new Telegram($config['token'], $config['type']);
    }
}
