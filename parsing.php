<?php

include 'env.php';
include 'db.php';
use mydb\myDB;
use env\Env;


header('Content-type: text/html; charset=utf-8');
require './libs/phpQuery-0.9.5.386-onefile/phpQuery-onefile.php';

$dbase = new myDB(env::class);

//echo '<pre>';
//echo var_dump($dbase->get_all("SELECT * FROM `last_order`")[0][1]);
//echo '</pre>';


$last_order = 3078000;
$last_order = $dbase->get_all("SELECT * FROM `last_order`")[0][1];
$watch_groups = $dbase->get_all("SELECT * FROM `categories_watch`");

$url = 'https://kabanchik.ua/task/'.$last_order;
$file = file_get_contents($url);
$doc = phpQuery::newDocument($file);


function parse_order(){
    global $doc, $url, $last_order;
    $array = [];
    try{
        $title = trim(explode('№', $doc->find('h1.kb-task-details__title')->text())[0]);
        $title = trim($title);
        $array['title'] = $title;
    }catch (Exception $e) {echo 'Title исключение: ',  $e->getMessage(), "\n";}
    try{
        $price = trim($doc->find('span.js-task-cost')->text());
        $price = trim($price);
        $array['price'] = $price;
    }catch (Exception $e) {echo 'Price исключение: ',  $e->getMessage(), "\n";}
    try{
        $was_created = trim(explode('о:', $doc->find('div.kb-sidebar-grid__content:eq(1)')->text())[1]);
        $was_created = trim($was_created);
        $array['was_created'] = $was_created;
    }catch (Exception $e) {echo 'Was_created исключение: ',  $e->getMessage(), "\n";}
    try{
        $deadline = trim($doc->find('span.js-datetime_due')->text());
        $deadline = trim($deadline);
        $array['deadline'] = $deadline;
    }catch (Exception $e) {echo 'Deadline исключение: ',  $e->getMessage(), "\n";}
    try{
        $tasks[0] = trim($doc->find('div.kb-task-details__non-numeric-attribute:eq(0)')->text());
        $tasks[1] = trim($doc->find('div.kb-task-details__non-numeric-attribute:eq(1)')->text());
        array_map(function ($val){return trim($val);}, $tasks);
        $array['tasks'] = $tasks;
    }catch (Exception $e) {echo 'Tasks исключение: ',  $e->getMessage(), "\n";}
    try{
        $comment = trim($doc->find('div.kb-task-details__content:eq(3)')->text());
        $comment = trim($comment);
        $array['comment'] = $comment;
    }catch (Exception $e) {echo 'Comment исключение: ',  $e->getMessage(), "\n";}
    try{
        $city = trim($doc->find('span.kb-execution-place__text')->text());
        $city = trim($city);
        $array['city'] = $city;
    }catch (Exception $e) {echo 'Client исключение: ',  $e->getMessage(), "\n";}
    try{
        $client = trim($doc->find('a.kb-sidebar-profile__name:eq(0)')->text());
        $client = trim($client);
        $array['client'] = $client;
    }catch (Exception $e) {echo 'Client исключение: ',  $e->getMessage(), "\n";}
    try{
        $review = trim($doc->find('span.kb-sidebar-profile__reviews-count:eq(0)')->text());
        $review = trim($review);
        $array['review'] = $review;
    }catch (Exception $e) {echo 'Review исключение: ',  $e->getMessage(), "\n";}
    try{
        $positive = trim($doc->find('div.kb-sidebar-profile__rating:eq(0)')->text());
        $positive = trim($positive);
        $array['positive'] = $positive;
    }catch (Exception $e) {echo 'Positive исключение: ',  $e->getMessage(), "\n";}
    try{
        $categories[1] = trim($doc->find('a.kb-breadcrumb__link:eq(1)')->text());
        $categories[2] = trim($doc->find('a.kb-breadcrumb__link:eq(2)')->text());
        $categories[3] = trim($doc->find('a.kb-breadcrumb__link:eq(3)')->text());
        array_map(function ($val){return trim($val);}, $categories);
        $array['categories'] = $categories;
    }catch (Exception $e) {echo 'Categories исключение: ',  $e->getMessage(), "\n";}

    echo '<pre>';
    echo var_dump($array);
    echo '</pre>';
}

send_php_cl();
function send_php_cl(){
    $botToken= env::$TELEGRAM_BOT_TOKEN;

    $website="https://api.telegram.org/bot".$botToken;
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
    $ch = curl_init($website . '/sendMessage');
    curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);
}


//https://kabanchik-bot.evilcode.space/parsing.php