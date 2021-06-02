<?php

openlog('Team365', LOG_PID, LOG_LOCAL7);

require_once __DIR__.'/vendor/autoload.php';

use Dotenv\Dotenv;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Util\Team365Bot;

syslog(LOG_DEBUG, 'START');
//ログにつくprefixを与えてインスタンス作成
$log = new Logger('MONOLOG_TEST');

//ログレベルをDEBUG（最も低い）に設定

$log->pushHandler(new StreamHandler('./logs/app.log', Logger::DEBUG));

// usage of monolog
// $log->addDebug('でばっぐ');
// $log->addInfo('いんふぉ');
// $log->addWarning('わーにんぐ');
// $log->addError('えらー');

// TODO: verify
function verify_signature($sign)
{
	//$log->addDebug("HTTP_X_LINE_SIGNATURE: ".$sign);
	return true;
}

// main
if (PHP_SAPI === 'cli') {	// shell実行、時報
	$bot = new Team365Bot([]);
	$dotenv = Dotenv::create(__DIR__);
	$dotenv->load();

	syslog(LOG_DEBUG, 'send');
	$bot->pushText(
		getenv('TO_ALARM_CHANNEL'),
		'飲んでんとはよ帰れ老人共！さて正解は次のうちどれでしょう'
	);
} else {	// Webhook
	verify_signature($_SERVER['HTTP_X_LINE_SIGNATURE']);
	syslog(LOG_DEBUG, 'LINE HEADER SIGNATURE IS OK');
	$json_string = file_get_contents('php://input');
	$log->addDebug($json_string);
	$obj = json_decode($json_string, true);

	$bot = new Team365Bot($obj);

	$bot->reply();
}

closelog();
exit;
