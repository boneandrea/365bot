<?php

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class Team365Bot
{
	public $msg; // これをパースする
	public $sender;
	private $db;

	public function __construct(array $json)
	{
		$this->msg = $json;

		// setup log
		$this->log = new Logger('MONOLOG_TEST');
		$this->sender = new SendLine($this->log);
		$handler = new StreamHandler(__DIR__.'/../../logs/app.log', Logger::DEBUG);
		$this->log->pushHandler($handler);

		// setup db acccesor
		$this->db = new MyDB();
	}

	/**
	 * 誰に送る.
	 *
	 * @param string $type
	 *
	 * @return string MessagingAPIで使う送信先ID
	 */
	public function setRecipient($type)
	{
		if (IS_PRD) {
			return ($type === 'user') ? getenv('TO_USER_ID') : getenv('GROUP_ID');
		} else {
			return getenv('TO_USER_ID');
		}
	}

	// なんか考えてリプライする
	public function reply()
	{
		$this->log->addDebug($this->checkMessageType());

		$type = $this->checkMessageType();
		$to = $this->setRecipient($type);

		if ($type === 'postback') {
			$msg = $this->handlePostback($this->msg['events'][0], $to);
		} else {
			$text = $this->msg['events'][0]['message']['text'];
			$msg = $this->createMessage($text);
		}

        error_log(print_r($msg, true));

		if (is_array($msg)) {
			$ret = $this->sender->push($to, $msg);
		} elseif (is_string($msg)) {
			$ret = $this->sender->pushText($to, $msg);
		}

        if($ret["status"] === 200){
            syslog(LOG_DEBUG, "送信成功");
        }else{
            error_log(print_r($ret, true));
            syslog(LOG_DEBUG, "送信失敗");
        }
	}

	public function getUserInfo(array $msg): array
	{
		return json_decode($this->sender->getProfile($msg['source']['userId'], getenv('GROUP_ID')), true);
	}

	public function handlePostback(array $msg, string $to)
	{
		$userInfo = $this->getUserInfo($msg);
		$name = $userInfo['displayName'];

		if ($msg['postback']['data'] === 'yes') {
			$reply = "hey $name, でかした";
		} elseif ($msg['postback']['data'] === 'no') {
			$reply = "Ohhhhhhhhh Arrrrrghhhhhhh $name, なんとなさけない";
		}
		$ret = $this->sender->pushText($to, $reply);

		$this->db->insertDrink([
			'user_id' => $msg['source']['userId'],
			'drink' => 1,
		]);
	}

	public function checkMessageType()
	{
		if ($this->msg['events'][0]['type'] === 'postback') {
			return 'postback';
		} elseif (isset($this->msg['events'][0]['source']['groupId'])) {
			return 'group';
		} else {
			return 'user';
		}
	}

	public function createMessage($text)
	{
		if (preg_match('/別にない/', $text)) {
			return '当然ですね';
		} elseif (preg_match('/文句がある/', $text)) {
			return '綾馬場さんがなんとかしてくれますよ';
		} elseif (preg_match('/365/', $text)) {
			return '365日雨の日も晴れの日も薄汚れた居酒屋の片隅で酒を飲むことしか知らない人生の無駄遣いの見本市のような皆さん';
		} elseif (preg_match('/KR/i', $text)) {
			return [
				'type' => 'flex',
				'altText' => '栗林先生参上！',
				'contents' => json_decode(file_get_contents('messages/json/kuri.json'), true),
			];
		} elseif (preg_match('/綾馬場/', $text)) {
			return '綾馬場さんの話するときは僕を通してください！';
		} elseif (preg_match('/ああああ/', $text)) {
			return [
				'type' => 'flex',
				'altText' => '時報でござる。',
				'contents' => json_decode(file_get_contents('messages/json/hello.json'), true),
			];
		}

		return null;
	}

	public function push($to, $msg)
	{
		$this->sender->push($to, $msg);
	}

	public function pushText($to, $text)
	{
		$this->sender->pushText($to, $text);
	}
}
