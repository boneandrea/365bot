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
        // e($this->msg);

        // $stmt = $this->db->prepare("INSERT INTO drink (user_id, drink, stamp) VALUES (:user_id, :drink, :stamp)");

        // $user_id="U11bac06cffe164a45e0dd72c438bb68f";
        // $drink=2;
        // $stamp=time();

        // $stmt->bindValue(':user_id', $user_id, PDO::PARAM_STR);
        // $stmt->bindValue(':drink', $drink, PDO::PARAM_INT);
        // $stmt->bindValue(':stamp', $stamp, PDO::PARAM_INT);

        // $stmt->execute();

        $reply=new Reply($this->msg);
        $reply->say();
	}
}
