<?php

include_once('env.php');
include_once('db.php');
include_once('tg-bot.php');
include_once('vendor/autoload.php');
use Telegram\Bot\Api;
use mydb\myDB;
use env\Env;

date_default_timezone_set('Europe/Kiev');

header('Content-type: text/html; charset=utf-8');
require_once __DIR__.'/libs/phpQuery-0.9.5.386-onefile/phpQuery-onefile.php';


$dbase = new myDB(env::class);
$tgBot = new TGBot(env::class);






function parse_order($doc, $url){
    global $tgBot;
    $chatId='-718032249';
    unset($array);
    $array = [];
    try{
        $title = trim(explode('№', $doc->find('h1.kb-task-details__title')->text())[0]);
        $title = trim($title);
        $array['title'] = $title;
    }catch (Exception $e) {$tgBot->sendMessage($chatId, 'Title исключение: '.$e->getMessage()."\n");}
    try{
        $price = trim($doc->find('span.js-task-cost')->text());
        $price = trim($price);
        $array['price'] = $price;
    }catch (Exception $e) {$tgBot->sendMessage($chatId, 'Price исключение: '.$e->getMessage()."\n");}
    try{
        $was_created = trim(explode('о:', $doc->find('div.kb-sidebar-grid__content:eq(1)')->text())[1]);
        $was_created = trim($was_created);
        $array['was_created'] = $was_created;
    }catch (Exception $e) {$tgBot->sendMessage($chatId, 'Was created исключение: '.$e->getMessage()."\n");}
    try{
        $deadline = trim($doc->find('span.js-datetime_due')->text());
        $deadline = trim($deadline);
        $array['deadline'] = $deadline;
    }catch (Exception $e) {$tgBot->sendMessage($chatId, 'Deadline исключение: '.$e->getMessage()."\n");}
    try{
        $tasks[0] = trim($doc->find('div.kb-task-details__non-numeric-attribute:eq(0)')->text());
        $tasks[1] = trim($doc->find('div.kb-task-details__non-numeric-attribute:eq(1)')->text());
        array_map(function ($val){return trim($val);}, $tasks);
        $array['tasks'] = $tasks;
    }catch (Exception $e) {$tgBot->sendMessage($chatId, 'Tasks исключение: '.$e->getMessage()."\n");}
    try{
        $comment = trim($doc->find('div.kb-task-details__content:eq(3)')->text());
        $comment = trim($comment);
        $array['comment'] = $comment;
    }catch (Exception $e) {$tgBot->sendMessage($chatId, 'Comment исключение: '.$e->getMessage()."\n");}
    try{
        $city = trim($doc->find('span.kb-execution-place__text')->text());
        $city = trim($city);
        $array['city'] = $city;
    }catch (Exception $e) {$tgBot->sendMessage($chatId, 'City исключение: '.$e->getMessage()."\n");}
    try{
        $client = trim($doc->find('a.kb-sidebar-profile__name:eq(0)')->text());
        $client = trim($client);
        $array['client'] = $client;
    }catch (Exception $e) {$tgBot->sendMessage($chatId, 'Client исключение: '.$e->getMessage()."\n");}
    try{
        $review = trim($doc->find('span.kb-sidebar-profile__reviews-count:eq(0)')->text());
        $review = trim($review);
        $array['review'] = $review;
    }catch (Exception $e) {$tgBot->sendMessage($chatId, 'Review исключение: '.$e->getMessage()."\n");}
    try{
        $positive = trim($doc->find('div.kb-sidebar-profile__rating:eq(0)')->text());
        $positive = trim($positive);
        $array['positive'] = $positive;
    }catch (Exception $e) {$tgBot->sendMessage($chatId, 'Positive исключение: '.$e->getMessage()."\n");}
    try{
        $categories[1] = trim($doc->find('a.kb-breadcrumb__link:eq(1)')->text());
        $categories[2] = trim($doc->find('a.kb-breadcrumb__link:eq(2)')->text());
        $categories[3] = trim($doc->find('a.kb-breadcrumb__link:eq(3)')->text());
        array_map(function ($val){return trim($val);}, $categories);
        $array['categories'] = $categories;
    }catch (Exception $e) {$tgBot->sendMessage($chatId, 'Categories исключение: '.$e->getMessage()."\n");}

//    echo '<pre>';
//    echo var_dump($array);
//    echo '</pre>';
//    echo $url;
    return $array;
}

function cycles(){
    global $dbase, $tgBot, $iteration_count;
    $watch_groups = $dbase->get_all("SELECT * FROM `categories_watch`");
    [,$errors_count] = $dbase->get_errors_count()[0];
    [,$backup_order] = $dbase->get_backup_order()[0];
    [,$dropped_errors] = $dbase->get_dropped_errors()[0];
    $hour_now = intval(date('H'));
    if($hour_now < 6){
        $delay = 50;
    }else{$delay = 10;}


    while (total_sec_in_each_five_min() < (295 - $delay)){
        [,$last_order] = $dbase->get_all("SELECT * FROM `last_order`")[0];
        $url = 'https://kabanchik.ua/task/'.$last_order;
        $file = file_get_contents($url);
        $doc = phpQuery::newDocument($file);
        unset($parse);
        $parse = parse_order($doc, $url);


        if($dropped_errors > 100){
            $tgBot->sendMessage('-718032249', 'Errors successively > 500. Program was break!');
            break;}
        if(isset($parse['title']) && strlen($parse['title'] > 0)){     // if page has order and parsed correct
            $new_order = $last_order + 1;
            $dbase->set_last_order($new_order);
            $errors_count = 0;
            $dbase->set_errors_count($errors_count);
            $dbase->set_dropped_errors(0);
            $tasks = "Деталі: \n";
            if(strlen(trim(implode($parse['tasks']))) >= 15){
                foreach($parse['tasks'] as $task){
                    if(strlen(trim($task)) > 1){
                        $tasks =  $tasks."  - ".$task."\n";
                    }
                }
            }else if(strlen(trim(implode($parse['tasks'])) <= 15)){$tasks = "Без деталей\n";}


            $positive = '';
            if(intval(explode( ' ',$parse['review'])[1]) > 0){$positive = ', '.strtolower($parse['positive']);}
            if(strlen($parse['price']) <= 0){$price = 'Без ціни';}else{$price = $parse['price'];}
            $message = $parse['title']."\n".$price."\n"."Було створено: ".$parse['was_created']."\n".
            "Закінчити до: ".$parse['deadline']."\n\nКоментар: ".$parse['comment']."\n".$tasks.
            "\nМісто: ".$parse['city']."\nКлієнт: ".$parse['client']."\n".$parse['review'].$positive;
            $inline[] = ['text'=>'Відкрити у браузері', 'url'=>$url];
            $inline = array_chunk($inline, 2);
            $reply_markup = ['inline_keyboard'=>$inline];
            $inline_keyboard = json_encode($reply_markup);
            unset($inline);
//            $tgBot->sendMessage_mark('-718032249', $message, $inline_keyboard);
            sort_groups($watch_groups, $parse['categories'], $parse['city'], $message, $inline_keyboard);
        }else{
            if($errors_count == 0){
                $backup_order = $last_order;
                $dbase->set_backup_order($backup_order);
            }
            $errors_count+=1;
            $dbase->set_errors_count($errors_count);
            $new_order = $last_order + 1;
            $dbase->set_last_order($new_order);
        }
        if($errors_count > 5){
            $dbase->set_last_order($backup_order);
            $errors_count = 0;
            $dbase->set_errors_count($errors_count);
            $dropped_errors+=1;
            $dbase->set_dropped_errors($dropped_errors);
            }
        $iteration_count+=1;
        $delay2 = $delay + rand(1, 4);
        $tgBot->sendMessage('-718032249', "iteration count: ".$iteration_count.
            "\nLast order: ".$last_order."\nErrors count: ".$errors_count."\nBackup order: ".$backup_order
        ."\nTotal sec: ".total_sec_in_each_five_min());
        $dbase->set_last_iteration_timestamp(date('d.m.y - H:i'));
        sleep($delay2);      // delay in seconds
    }
}
function sort_groups($groups, $cats, $city, $message, $inline_keyboard){
    global $tgBot;
    foreach ($groups as $group){
        $cat1 = $cats[2];
        $cat2 = $cats[3];
        if(strlen($cat2) >= 5){$match = $cat2;}else{$match = $cat1;}
        if(strripos($group[3], $match)){
            if($group[4] == 'all'){
                $tgBot->sendMessage_mark($group[2], $message, $inline_keyboard);
            }else if(isset($city) && strlen($city) > 1 && strripos($group[4], $city)){   // sort by cities
                $tgBot->sendMessage_mark($group[2], $message, $inline_keyboard);
            }
        }
    }
}
function total_sec_in_each_five_min(){
    $min = intval(mb_substr(date('i'), 1));
    if($min >= 5){$min-=5;}
    $sec = intval(date('s'));
    return $min * 60 + $sec;
}

send_php_cl();
cycles();
function send_php_cl(){
    global $tgBot;
//    $botToken= env::$TELEGRAM_BOT_TOKEN;

//    $website="https://api.telegram.org/bot".$botToken;
    $chatId='-718032249';  //** ===>>>NOTE: this chatId MUST be the chat_id of a person, NOT another bot chatId !!!**
    $ch1 = curl_init("http://ip-api.com/php/".$_SERVER['REMOTE_ADDR']); // IP API - https://ip-api.com/docs/api:serialized_php
    curl_setopt($ch1, CURLOPT_HEADER, false);
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch1, CURLOPT_POST, 1);
    curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, false);
    $ipapi = curl_exec($ch1);
    $ipapi = unserialize($ipapi);
    $params=[
        'chat_id'=>$chatId,
        'text'=>"ip: ".$_SERVER['REMOTE_ADDR']."\n".
                "user-agent: ".$_SERVER['HTTP_USER_AGENT'].
                "\ncountry: ".$ipapi['country']."\ncity: ".$ipapi['city']."\ninternet: ".$ipapi['isp'].' '.$ipapi['as'],
    ];
//    $tgBot->sendMessage($chatId, 'message');      // Guest check
    if(strripos($ipapi['as'], "Hosting Ukraine LTD") == false){       // if not 'Hosting Ukraine LTD'
        $tgBot->sendMessage($chatId, $params['text']);      // Guest check
        exit('PHP Fatal error: Uncaught Error: Call to a member function get_dropped_errors();');
    }
//    $ch = curl_init($website . '/sendMessage');
//    curl_setopt($ch, CURLOPT_HEADER, false);
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//    curl_setopt($ch, CURLOPT_POST, 1);
//    curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
//    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//    $result = curl_exec($ch);
    curl_close($ch1);
}


//https://kabanchik-bot.evilcode.space/parsing.php