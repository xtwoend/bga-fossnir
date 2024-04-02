<?php

use TelegramSDK\BotAPI\Telegram\Update;
use function Hyperf\Support\env;

return [
    'token' => env('TELEGRAM_TOKEN'),
    'type' => Update::UPDATES_FROM_GET_UPDATES,
];