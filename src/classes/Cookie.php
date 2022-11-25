<?php

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

define('DEFAULT_INTERVAL', 5);

class Cookie
{
	private $db;
	public $config = [];

	public function __construct(array $config = [])
	{
		$this->config['interval'] = $config['interval'] ?? DEFAULT_INTERVAL;
		// setup log
		$this->log = new Logger('MONOLOG_TEST');
		$handler = new StreamHandler(__DIR__.'/../../logs/app.log', Logger::DEBUG);
		$this->log->pushHandler($handler);

		// setup db acccesor
		$this->db = new MyDB();
	}

	public function isValidInterval(array $data): bool
	{
		$uid = $data['source']['userId'];
		$this->log->addDebug(var_export($data, true));
		$this->log->addDebug($uid);
		if (!$uid) {
			return false;
		}

		$lastAccessTime = $this->getLastAccessTime($uid);

		return $this->_isEnoughInterval($lastAccessTime);
	}

	public function _isEnoughInterval(?\DateTime $datetime): bool
	{
		if ($datetime === null) {
			return true;
		}

		$interval = time() - $datetime->getTimestamp();
		$this->log->addDebug($datetime->getTimestamp().' / '.time().' => '.$interval);

		return $interval > $this->config['interval'];
	}

	public function getLastAccessTime(string $uid): ?\DateTime
	{
		try {
			$stmt = $this->db->pdo->prepare('SELECT stamp FROM drink where user_id=? order by stamp desc limit 1');
			$stmt->execute([$uid]);
			$rows = $stmt->fetchAll();
			if (count($rows) === 0) {
				return null;
			}

			return new \DateTime("@".$rows[0]['stamp']);
		} catch (Exception $e) {
			e($e->getMessage());
		}
		foreach ($rows as $r) {
			$this->log->addDebug(var_export($r, true));
		}
	}
}
