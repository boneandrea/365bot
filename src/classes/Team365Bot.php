<?php

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';

use Predis\Client;
use \PDO;

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
	}

	public function reply(): void
    {
        $reply=new Reply($this->msg);
        $reply->say();
	}
}
