<?php

namespace App\Service;

use App\Service\Samba;
use Hyperf\Contract\ConfigInterface;
use Psr\Container\ContainerInterface;


class SambaFactory
{
    protected $container;
    protected $config;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
        $this->config = $container->get(ConfigInterface::class);
    }

    public function __invoke()
    {
        $config = $this->config->get('smb');
        return (new Samba($config));
    }
}