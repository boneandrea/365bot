<?php

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';

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

    public function push($to, array $msg)
    {
        $this->sender->push($to, $msg);
    }

    public function pushText($to, string $text)
    {
        $this->sender=new SendLine();
        $this->sender->pushText($to, $text);
    }
}
