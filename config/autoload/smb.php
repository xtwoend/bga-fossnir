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
use Icewind\SMB\IOptions;
use function Hyperf\Support\env;

return [
    'workgroup' => 'workgroup',
    'host' => env('SMB_HOST'),
    'sharename' => env('SMB_SHARENAME'),
    'username' => env('SMB_USERNAME'),
    'password' => env('SMB_PASSWORD'),

    'smb_version_min' => IOptions::PROTOCOL_SMB2,
    'smb_version_max' => IOptions::PROTOCOL_SMB3,
    'timeout' => 3600,
];
