<?php

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';
use PDO;

class MyDB
{
	public $dbname = 'db/t365.db';
	public $dbh;

	public function __construct()
	{
		try {        // 接続
            $MYSQLHOST=getenv("MYSQLHOST");
            $MYSQLPORT=getenv("MYSQLPORT");
            $MYSQLUSER=getenv("MYSQLUSER");
            $MYSQLPASSWORD=getenv("MYSQLPASSWORD");
            $MYSQLDATABASE=getenv("MYSQLDATABASE");
            $db_url="mysql://{$MYSQLUSER}:{$MYSQLPASSWORD}@{$MYSQLHOST}:{$MYSQLPORT}/{$MYSQLDATABASE}";
            error_log($db_url);
            $pdo=new PDO($db_url);

			// SQL実行時にもエラーの代わりに例外を投げるように設定
			// (毎回if文を書く必要がなくなる)
			$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

			// デフォルトのフェッチモードを連想配列形式に設定
			// (毎回PDO::FETCH_ASSOCを指定する必要が無くなる)
			$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
			$this->pdo = $pdo;
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
	}

	public function insertDrink($data)
	{
		try {
			// 挿入（プリペアドステートメント）
			$stmt = $this->pdo->prepare('INSERT INTO drink(user_id, drink, stamp) VALUES (?, ?, ?)');
			$stmt->execute([$data['user_id'], $data['drink'], date('Y-m-d H:i:s')]);
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
	}

	public function selectDrink($data): array
	{
		return [];
		try {
			// 選択 (プリペアドステートメント)
			$stmt = $this->pdo->prepare('SELECT * FROM drink where user_id=?');
			$stmt->execute(['200']);
			$r1 = $stmt->fetchAll();
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
	}
}
