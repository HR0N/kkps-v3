<?php
include('vendor/autoload.php');
use Telegram\Bot\Api;
include_once('env.php');
include_once('db.php');
use env\Env as env;
use mydb\myDB;


$iteration_count = 0;

$telegram = new Api(env::$TELEGRAM_BOT_TOKEN);
$mydb = new myDB(env::class);

$result = $telegram->getWebhookUpdates();

$text = strtolower($result['message']['text']);
$chat_id = $result['message']['chat']['id'];
$name = $result['message']['from']['username'];
$first_name = $result['message']['from']['first_name'];
$last_name = $result['message']['from']['last_name'];

[,$res] = $mydb->get_errors_count()[0];
[,$res2] = $mydb->get_backup_order()[0];

function separate_time(){
    $min = intval(mb_substr(date('i'), 1));
    if($min >= 5){$min-=5;}
    $sec = intval(date('s'));
    return $min * 60 + $sec;
}

echo separate_time();


//echo "\n".date('d.m.y - H:i');


$reply = 'test';
//$telegram->sendMessage(['chat_id' => '-718032249', 'text' => $reply]);
