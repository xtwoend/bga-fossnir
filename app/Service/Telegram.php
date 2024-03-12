<?php

namespace App\Service;


class Telegram
{
    protected $key;
    protected $bot_username;

    public function __construct() {
        $this->key = env('TELEGRAM_KEY');
        $this->bot_username = env('TELEGRAM_USERNAME');
    }

    public function send($chatId, $text)
    {
        
    }

    public function listen()
    {
        $config = config('databases.default');
        try {
            $telegram = new \Longman\TelegramBot\Telegram($this->key, $this->bot_username);
            $telegram->enableMySql([
                'host'     => $config->host,
                'port'     => $config->port, // optional
                'user'     => $config->username,
                'password' => $config->password,
                'database' => $config->database,
            ]);
            $telegram->handleGetUpdates();
        } catch (\Longman\TelegramBot\Exception\TelegramException $e) {
            // log telegram errors
            // echo $e->getMessage();
        }
    }
}