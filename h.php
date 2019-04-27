<?php

require_once __DIR__.'/vendor/autoload.php';

//for monolog
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

//ログにつくprefixを与えてインスタンス作成
$log = new Logger('MONOLOG_TEST');
//ログレベルをDEBUG（最も低い）に設定

$handler = new StreamHandler('./logs/app.log',Logger::DEBUG);
$log->pushHandler($handler);

//monolog
$log->addDebug('でばっぐ');
$log->addInfo('いんふぉ');
$log->addWarning('わーにんぐ');
$log->addError('えらー');


$log->addDebug(json_encode($_POST));
echo "ok";

if(0)foreach ($_SERVER as $name => $value) {
    //$log->addDebug("$name: $value");
}
$log->addDebug("HTTP_X_LINE_SIGNATURE: ". $_SERVER["HTTP_X_LINE_SIGNATURE"]);


$json_string = file_get_contents('php://input'); ##今回のキモ
$log->addDebug($json_string);
$obj = json_decode($json_string);
