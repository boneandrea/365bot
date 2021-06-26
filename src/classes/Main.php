<?php

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';

use Dotenv\Dotenv;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Main
{
	private $bot;

	public function __construct()
	{
		$dotenv = Dotenv::create(__DIR__.'/../..');
		$dotenv->load();

		define('IS_PRD', getenv('MODE') === 'prod');

		openlog('Team365', LOG_PID, LOG_LOCAL7);
		syslog(LOG_DEBUG, 'START');
		$this->log = new Logger('MONOLOG_TEST');
		$this->log->pushHandler(new StreamHandler(__DIR__.'/../logs/app.log', Logger::DEBUG)); // DEBUG（最も低い）に設定

		// usage of monolog
		// $log->addDebug('でばっぐ');
		// $log->addInfo('いんふぉ');
		// $log->addWarning('わーにんぐ');
		// $log->addError('えらー');

		$this->log->addDebug('start '.(IS_PRD ? 'PRD' : 'dev'));
	}

	// TODO: verify
	public function verify_signature($sign)
	{
		//$this->log->addDebug("HTTP_X_LINE_SIGNATURE: ".$sign);
		return true;
	}

	public function getRecipient()
	{
		return getenv('GROUP_ID');
	}

	// shell実行、時報
	public function send_message()
	{
		syslog(LOG_DEBUG, 'send');

		$this->bot = new Team365Bot([]);

		$to = $this->getRecipient();
		$this->bot->pushText(
			$to,
			'飲んでんとはよ帰れ老人共！'
		);

		$this->bot->push($to, [
			'type' => 'flex',
			'altText' => 'やあみんな、Botだよ。',
			'contents' => json_decode(file_get_contents('messages/json/hello.json'), true),
		]);
	}

	// Webhook
	public function reply()
	{
		$this->verify_signature($_SERVER['HTTP_X_LINE_SIGNATURE']);
		syslog(LOG_DEBUG, 'LINE HEADER SIGNATURE IS OK');

		$json_string = file_get_contents('php://input');
		$this->log->addDebug($json_string);

		$data = json_decode($json_string, true);
		$this->bot = new Team365Bot($data);
		$this->bot->reply();
	}

	public function execute()
	{
		if (PHP_SAPI === 'cli') {
			$this->send_message();
		} else {
			$this->reply();
		}
		closelog();
	}
}
