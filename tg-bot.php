<?php
include('vendor/autoload.php');
use Telegram\Bot\Api;
include('env.php');
use env\Env as env;

$telegram = new Api(env::$TELEGRAM_BOT_TOKEN);
$result = $telegram->getWebhookUpdates();

$text = strtolower($result['message']['text']);
$chat_id = $result['message']['chat']['id'];
$name = $result['message']['from']['username'];
$first_name = $result['message']['from']['first_name'];
$last_name = $result['message']['from']['last_name'];


if($text == '/start'){
    $reply = "Hello world!";
    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
}
if($text == 'test'){
    $reply = "Fuck off bitch!";
    $telegram->sendMessage(['chat_id' => $chat_id, 'text' => $reply]);
}

// composer require irazasyed/telegram-bot-sdk ^2.0
//$ composer require vlucas/phpdotenv
//https://api.telegram.org/bot5591524736:AAGXk3kxgnGrjpIeMvhMM_toBda5NQVTLnQ/setWebHook?url=
//https://kabanchik-bot.evilcode.space/tg-bot.php