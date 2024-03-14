<?php

use TelegramSDK\BotAPI\Telegram\Update;

return [
    'token' => env('TELEGRAM_TOKEN'),
    'type' => Update::UPDATES_FROM_GET_UPDATES,
];