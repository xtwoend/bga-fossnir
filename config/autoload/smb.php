<?php

return [
    'workgroup' => 'workgroup',
    'host' => env('SMB_HOST'),
    'sharename' => env('SMB_SHARENAME'),
    'username' => env('SMB_USERNAME'),
    'password' => env('SMB_PASSWORD'),

    'smb_version_min' => \Icewind\SMB\IOptions::PROTOCOL_SMB2,
    'smb_version_max' => \Icewind\SMB\IOptions::PROTOCOL_SMB3,
    'timeout' => 3600,
];