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

		$this->sender = new SendLine();
		$this->cookie = new Cookie();

		// setup message
		$this->patterns = json_decode(file_get_contents(__DIR__.'/message.json'), true);

		// setup db acccesor
        $db=new MyDB();
		$this->db = $db->pdo;
	}

	// キューに投入して終了
	public function reply(): void
    {
        e($this->msg);

        $stmt = $this->db->prepare("INSERT INTO drink (user_id, drink, stamp) VALUES (:user_id, :drink, :stamp)");
        $stmt->bindValue(':user_id', 4, PDO::PARAM_INT);
        $stmt->bindValue(':drink', 8, PDO::PARAM_INT);
        $stamp=time();
        $stmt->bindValue(':stamp', $stamp, PDO::PARAM_INT);
        $stmt->execute();
	}
}
