<?php

require_once __DIR__.'/vendor/autoload.php';

use Util\Team365Bot;

//for monolog
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

//ログにつくprefixを与えてインスタンス作成
$log = new Logger('MONOLOG_TEST');
//ログレベルをDEBUG（最も低い）に設定

$handler = new StreamHandler('./logs/app.log',Logger::DEBUG);
$log->pushHandler($handler);

//monolog
// $log->addDebug('でばっぐ');
// $log->addInfo('いんふぉ');
// $log->addWarning('わーにんぐ');
// $log->addError('えらー');

if(0)foreach ($_SERVER as $name => $value) {
    //$log->addDebug("$name: $value");
}

// TODO: verify
function verify_signature($sign){
    //$log->addDebug("HTTP_X_LINE_SIGNATURE: ".$sign);
}
verify_signature( $_SERVER["HTTP_X_LINE_SIGNATURE"]);

$json_string = file_get_contents('php://input'); ##今回のキモ
$log->addDebug($json_string);
$obj = json_decode($json_string, true);

$bot=new Team365Bot($obj);

$bot->reply();
