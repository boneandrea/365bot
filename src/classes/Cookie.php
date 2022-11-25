<?php

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';

define('DEFAULT_INTERVAL', 5);

class Cookie
{
	private $db;
	public $config = [];

	public function __construct(array $config = [])
	{
		$this->config['interval'] = $config['interval'] ?? DEFAULT_INTERVAL;

		// setup db acccesor
		$this->db = new MyDB();
	}

	public function isValidInterval(array $data): bool
	{
		$uid = $data['source']['userId'];
		if (!$uid) {
			return false;
		}

		$lastAccessTime = $this->getLastAccessTime($uid);
        e($lastAccessTime);
		return $this->_isEnoughInterval($lastAccessTime);
	}

	public function _isEnoughInterval(int $last_timestamp): bool
	{
		$interval = time() - $last_timestamp;
		return $interval > $this->config['interval'];
	}

	public function getLastAccessTime(string $uid): int
	{
		try {
			$stmt = $this->db->pdo->prepare('SELECT stamp FROM drink where user_id=? order by stamp desc limit 1');
			$stmt->execute([$uid]);
			$rows = $stmt->fetchAll();
			if (count($rows) === 0) {
				return null;
			}

			return $rows[0]['stamp'];
		} catch (Exception $e) {
			e($e->getMessage());
		}
		foreach ($rows as $r) {
			e($r);
		}
	}
}
