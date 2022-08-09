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



$reply = '';
$telegram->sendMessage(['chat_id' => '-718032249', 'text' => $reply]);