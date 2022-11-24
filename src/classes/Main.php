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
		error_log('START');
		$this->log = new Logger('MONOLOG_TEST');
		$this->log->pushHandler(new StreamHandler(__DIR__.'/../../logs/app.log', Logger::DEBUG)); // DEBUG（最も低い）に設定

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
		// $this->log->addDebug("HTTP_X_LINE_SIGNATURE: ".$sign);
		return true;
	}

	public function getRecipient()
	{
		return getenv('GROUP_ID');
	}

	// shell実行、時報
	public function send_message()
	{
		error_log('send');

		$this->bot = new Team365Bot();

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
	public function recv_data(): array
	{
		$this->verify_signature($_SERVER['HTTP_X_LINE_SIGNATURE'] ?? "");
		error_log('LINE HEADER SIGNATURE IS OK');

		$json_string = file_get_contents('php://input');
        error_log($json_string);
		$this->log->addDebug($json_string);

		return json_decode($json_string, true) ?? [];
	}

	// Webhook
	public function reply(array $data)
	{
		$this->bot = new Team365Bot($data);
		$this->bot->reply();
	}

	public function execute()
	{
		if (PHP_SAPI === 'cli') {
			$this->send_message();
		} else {
			$data = $this->recv_data();
			$this->reply($data);
		}
	}
}
