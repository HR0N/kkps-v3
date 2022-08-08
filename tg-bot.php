<?php
include('vendor/autoload.php');
use Telegram\Bot\Api;
include_once('env.php');
use env\Env as env;


$iteration_count = 0;

$telegram = new Api(env::$TELEGRAM_BOT_TOKEN);
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
    $reply = "Итераций: ".$iteration_count."\nРаботает: вроде да.";
    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
}

//if($text == 'start'){
//    $reply = "Hello world!";
//    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
//}

// composer require irazasyed/telegram-bot-sdk ^2.0
//$ composer require vlucas/phpdotenv
//https://api.telegram.org/bot5591524736:AAGXk3kxgnGrjpIeMvhMM_toBda5NQVTLnQ/setWebHook?url=
//https://kabanchik-bot.evilcode.space/tg-bot.php