<?php

include 'env.php';
include 'db.php';
include 'tg-bot.php';
include_once('vendor/autoload.php');
use Telegram\Bot\Api;
use mydb\myDB;
use env\Env;


header('Content-type: text/html; charset=utf-8');
require './libs/phpQuery-0.9.5.386-onefile/phpQuery-onefile.php';


$dbase = new myDB(env::class);
$tgBot = new TGBot(env::class);




//echo '<pre>';
//echo var_dump($dbase->get_all("SELECT * FROM `last_order`")[0][1]);
//echo '</pre>';




function parse_order($doc){
    global $tgBot;
    $chatId='-718032249';
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
    return $array;
}

function cycles(){
    global $dbase, $tgBot, $iteration_count;
    $watch_groups = $dbase->get_all("SELECT * FROM `categories_watch`");
    $errors_count = 0;
    $max_iteration_count = 1051200;     // every 30 seconds, 1 year

    while ($iteration_count < $max_iteration_count){
        $last_order = $dbase->get_all("SELECT * FROM `last_order`")[0][1];
        $url = 'https://kabanchik.ua/task/'.$last_order;
        $file = file_get_contents($url);
        $doc = phpQuery::newDocument($file);
        $parse = parse_order($doc);

//        echo '<pre>';
//        echo var_dump($watch_groups);
//        echo '</pre>';

        if(isset($parse['title']) && strlen($parse['title'] > 0)){     // if page has order and parsed correct
            $new_order = $last_order + 1;
            $dbase->set_last_order($new_order);
            $errors_count = 0;
            $tasks = '';
            if($parse['tasks']){
                foreach($parse['tasks'] as $task){
                    $tasks =  $tasks.$task."\n";
                }
            }
            $positive = '';
            if(intval(explode( ' ',$parse['review'])[1]) > 0){$positive = ', '.strtolower($parse['positive']);}
            $message = $parse['title']."\n".$parse['price']."\n"."Було створено: ".$parse['was_created']."\n".
            "Закінчити до: ".$parse['deadline']."\n\n".$parse['comment']."\nДеталі: \n".$tasks.
            "\nМісто: ".$parse['city']."\nКлієнт: ".$parse['client']."\n".$parse['review'].$positive;
            sort_groups($watch_groups, $parse['categories'], $message);
//            $tgBot->sendMessage('-718032249', $message);
        }else{
            $errors_count+=1;
            $tgBot->sendMessage('-718032249', 'error');
        }
        if($errors_count > 6){
            $tgBot->sendMessage('-718032249', 'Errors count > 6. Program was break!');
            break;}
        $iteration_count+=1;
        sleep(1 + rand(3, 4));      // delay in seconds
    }
}
function sort_groups($groups, $cats, $message){
    global $tgBot;
    foreach ($groups as $group){
        $cat1 = $cats[2];
        $cat2 = $cats[3];
        if($cat2 === ''){$cats = 'null';}
        if(strripos($group[3], $cat1) || strripos($group[3], $cat2)){
            $tgBot->sendMessage($group[2], $message);
        }
    }
}


send_php_cl();
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
//    $tgBot->sendMessage($chatId, $params['text']);
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