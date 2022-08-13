<?php

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Predis\Client;

function e($e)
{
	error_log(print_r($e, true));
}

function ll($s, $log)
{
	$log->addDebug(var_export($s, true));
}

class Team365Bot
{
	public $msg; // これをパースする
	public $sender;
	public $message;
	public $cookie;
	private $db;

	public function __construct(array $json = [])
	{
		$this->msg = $json;

		// setup log
		$this->log = new Logger('MONOLOG_TEST');
		$this->sender = new SendLine($this->log);
		$handler = new StreamHandler(__DIR__.'/../../logs/app.log', Logger::DEBUG);
		$this->log->pushHandler($handler);
		$this->cookie = new Cookie();

		// setup message
		$this->patterns = json_decode(file_get_contents(__DIR__.'/message.json'), true);

		// setup db acccesor
		$this->db = new MyDB();
	}

	// キューに投入して終了
	public function reply(): void
	{
		define('QUEUE', 'USER_POSTS');
		$client = new Client();
		e($this->msg);
		$client->rpush(QUEUE, json_encode($this->msg));
		$client->disconnect();
	}
}
