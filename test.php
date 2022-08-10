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

echo $res;
echo "\n";
echo $res2;
echo "\n";

echo '<pre>';
echo var_dump($mydb->get_errors_count());
echo '</pre>';
echo '<pre>';
echo var_dump($mydb->get_backup_order());
echo '</pre>';

$reply = 'test';
//$telegram->sendMessage(['chat_id' => '-718032249', 'text' => $reply]);