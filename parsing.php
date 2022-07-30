<?php
header('Content-type: text/html; charset=utf-8');
require './libs/phpQuery-0.9.5.386-onefile/phpQuery-onefile.php';


$last_order = 3055000;
$url = 'https://kabanchik.ua/task/'.$last_order;
$file = file_get_contents($url);


$doc = phpQuery::newDocument($file);

echo $doc;


//https://kabanchik-bot.evilcode.space/parsing.php