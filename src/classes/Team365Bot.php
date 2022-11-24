<?php

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';

use Predis\Client;

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

		$this->sender = new SendLine();
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
		error_log($this->msg);
		$client->rpush(QUEUE, json_encode($this->msg));
		$client->disconnect();
	}
}
