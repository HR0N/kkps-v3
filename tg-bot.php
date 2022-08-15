<?php

include('vendor/autoload.php');
use Telegram\Bot\Api;
include_once('env.php');
include_once('db.php');
use env\Env as env;
use mydb\myDB;


$iteration_count = 0;

$telegram = new Api(env::$TELEGRAM_BOT_TOKEN);
$tgDbase = new myDB(env::class);
$result = $telegram->getWebhookUpdates();

$text = strtolower($result['message']['text']);
$chat_id = $result['message']['chat']['id'];
$name = $result['message']['from']['username'];
$first_name = $result['message']['from']['first_name'];
$last_name = $result['message']['from']['last_name'];


class TGBot{
    public $telegram;
    public $result;
    public $text;
    public $chat_id;
    public $name;
    public $first_name;
    public $last_name;
    public function __construct($env)
    {
        $this->telegram = new Api($env::$TELEGRAM_BOT_TOKEN);
    }
    function sendMessage($chat_id, $message){
        $this->telegram->sendMessage(['chat_id' => $chat_id, 'text' => $message, 'parse_mode' => 'HTML']);
    }
    function sendMessage_mark($chat_id, $message, $keyboard){
        $this->telegram->sendMessage(['chat_id' => $chat_id, 'text' => $message, 'reply_markup' => $keyboard,
            'parse_mode' => 'HTML']);
    }
}

if($text == 'status'){
    [,$last_iteration] = $tgDbase->get_last_iteration_timestamp()[0];
    [,$dropped_errors] = $tgDbase->get_dropped_errors()[0];
    $reply = "Last iteration was: ".$last_iteration."\nDropped errors: ".$dropped_errors;
    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply, 'parse_mode' => 'HTML']);
}
if($text == '/start_report'){
    $reply = "Last iteration was: ";
    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply, 'parse_mode' => 'HTML']);
}
if($text == '/stop_report'){
    $reply = "Last iteration was: ";
    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply, 'parse_mode' => 'HTML']);
}

//if($text == 'start'){
//    $reply = "Hello world!";
//    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
//}

// composer require irazasyed/telegram-bot-sdk ^2.0
//$ composer require vlucas/phpdotenv
//https://api.telegram.org/bot5591524736:AAGXk3kxgnGrjpIeMvhMM_toBda5NQVTLnQ/setWebHook?url=
//https://kkpsv3.evilcode.space/tg-bot.php