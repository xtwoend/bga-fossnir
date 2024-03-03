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
return [
    'default' => 'bga',
    'servers' => [
        'bga' => [
            'host' => env('MQTT1_HOST', 'http://localhost'),
            'port' => (int) env('MQTT1_PORT', 1883),
            'username' => env('MQTT1_USER', null),
            'password' => env('MQTT1_PASS', null),
        ],
        'hivemq' => [
            'host' => env('MQTT2_HOST', 'broker.hivemq.com'),
            'port' => (int) env('MQTT2_PORT', 1883),
            'username' => env('MQTT2_USER', null),
            'password' => env('MQTT2_PASS', null),
        ],
    ],
];
