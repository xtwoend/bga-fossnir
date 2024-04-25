<?php

namespace App\Service;

use App\Model\FossnirDir;
use App\Model\TelegramUser;
use TelegramSDK\BotAPI\Telegram\Bot;
use TelegramSDK\BotAPI\Telegram\Update;
use TelegramSDK\BotAPI\Exception\TelegramException;


class Telegram
{
    protected $bot;

    public function __construct(string $token, $type = null) {
        $type = $type ?: Update::UPDATES_FROM_GET_UPDATES;
        $this->bot = new Bot($token, $type);
    }

    public function send($chatId, $text)
    {
        return $this->bot->sendMessage([
            'chat_id' => $chatId,
            'text' => $text
        ]);
    }

    public function listen()
    {
        for ( ; ; sleep(3)) {
            $updates = $this->bot->updates(isset($updates) ? $updates->getLastUpdateId() : null);
            foreach($updates->result as $update){
               
                if(isset($update->message)){
                    $chat = $update->message->chat;
                    $message = $update->message;
                    
                    $exists = TelegramUser::where('chat_id', $chat->id)->count();
                    
                    $text = $exists > 0 ? 'Non Aktifkan Notifikasi' : 'Aktifkan Notifikasi';

                    if(property_exists($message, 'text')) {
                        
                        $messageText = $message->text;
                        if($messageText == '/start'){
                            $this->bot->sendMessage([
                                'chat_id' => $chat->id,
                                'text' => 'Selamat Datang Di PT Bumitama Gunajaya Agro.',
                                'reply_markup' => json_encode([
                                    'inline_keyboard' => [
                                        [
                                            [
                                                'text' => $text, 'callback_data' => $exists > 0 ? 'non_active' : 'mill'
                                            ]
                                        ]
                                    ]
                                ]),
                            ]);
                        }
                        if($messageText == '/notifikasi'){
                            $this->bot->sendMessage([
                                'chat_id' => $chat->id,
                                'reply_markup' => json_encode([
                                    'inline_keyboard' => [
                                        [
                                            [
                                                'text' => $text, 'callback_data' => 'notif'
                                            ]
                                        ]
                                    ]
                                ]),
                            ]);
                        }
                    }
                }

                if(isset($update->callback_query)) {
                    $message = $update->callback_query;
                    $chat = $update->callback_query->message->chat;
                    
                    if($message?->data == 'mill') {
                        $mills_button = [];
                        foreach(FossnirDir::orderBy('order')->get() as $mill) {
                            $mills_button[] = [
                                [
                                    'text' => $mill->mill_name,
                                    'callback_data' => (int) $mill->id,
                                ]
                            ];
                        }
                        $this->bot->sendMessage([
                            'chat_id' => $chat->id,
                            'text' => 'Pilih mill yang akan di monitor',
                            'reply_markup' => json_encode([
                                'inline_keyboard' => $mills_button,
                            ]),
                        ]);
                    }

                    if($message?->data == 'non_active') {
                        $exists = TelegramUser::where('chat_id', $chat->id)->count();
                        if($exists > 0) {
                            TelegramUser::where('chat_id', $chat->id)->delete();
                            $this->bot->sendMessage([
                                'chat_id' => $chat->id,
                                'text' => 'Notifikasi telah di non aktifkan',
                                'reply_markup' => json_encode([
                                    'inline_keyboard' => [
                                        [
                                            [
                                                'text' => 'Aktifkan Notifikasi', 'callback_data' => 'mill'
                                            ]
                                        ]
                                    ]
                                ]),
                            ]);
                        }
                    }
                    // 
                    $millId = intval($message->data);
                    if(is_numeric($millId) && $millId !== 0) {
                        $exists = TelegramUser::where('chat_id', $chat->id)->count();
                        if($exists > 0) {
                            TelegramUser::where('chat_id', $chat->id)->delete();
                            $this->bot->sendMessage([
                                'chat_id' => $chat->id,
                                'text' => 'Notifikasi telah di non aktifkan',
                                'reply_markup' => json_encode([
                                    'inline_keyboard' => [
                                        [
                                            [
                                                'text' => 'Aktifkan Notifikasi', 'callback_data' => 'mill'
                                            ]
                                        ]
                                    ]
                                ]),
                            ]);
                        }else{
                            
                            TelegramUser::create([
                                'chat_id' => $chat->id,
                                'first_name' => $chat->first_name ?? null,
                                'last_name' => $chat->last_name ?? null,
                                'username' => $chat->username ?? null,
                                'mill_id' => $message->data,
                            ]);

                            $mill = FossnirDir::find($message->data);

                            $this->bot->sendMessage([
                                'chat_id' => $chat->id,
                                'text' => 'Notifikasi telah di aktifkan pada mill ' . $mill?->mill_name,
                                'reply_markup' => json_encode([
                                    'inline_keyboard' => [
                                        [
                                            [
                                                'text' => 'Non Aktifkan Notifikasi', 'callback_data' => 'non_active'
                                            ]
                                        ]
                                    ]
                                ]),
                            ]);
                        }
                    }
                }
            }
        }
    }
}