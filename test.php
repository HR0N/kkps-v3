<?php
include('vendor/autoload.php');
use Telegram\Bot\Api;
include_once('env.php');
include_once('db.php');
use env\Env as env;
use mydb\myDB;


$iteration_count = 0;

$telegram = new Api(env::$TELEGRAM_BOT_TOKEN);
$dbase = new myDB(env::class);

$result = $telegram->getWebhookUpdates();

$text = strtolower($result['message']['text']);
$chat_id = $result['message']['chat']['id'];
$name = $result['message']['from']['username'];
$first_name = $result['message']['from']['first_name'];
$last_name = $result['message']['from']['last_name'];

$watch_groups = $dbase->get_all("SELECT * FROM `categories_watch`");

echo '<pre>';
echo var_dump($watch_groups);
echo '</pre>';

//echo "\n".date('d.m.y - H:i');


$reply = 'test';
//$telegram->sendMessage(['chat_id' => '-718032249', 'text' => $reply]);
