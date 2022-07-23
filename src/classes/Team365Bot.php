<?php

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

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
	private $db;

	public function __construct(array $json=[])
	{
		$this->msg = $json;

		// setup log
		$this->log = new Logger('MONOLOG_TEST');
		$this->sender = new SendLine($this->log);
		$handler = new StreamHandler(__DIR__.'/../../logs/app.log', Logger::DEBUG);
		$this->log->pushHandler($handler);

		// setup message
		$this->message = json_decode(file_get_contents(__DIR__.'/message.json'), true);

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
	public function reply(): void
	{
		$this->log->addDebug($this->checkMessageType());

		$type = $this->checkMessageType();
		$to = $this->setRecipient($type);

		e($this->msg['events'][0]);
		if ($type === 'postback') {
			$msg = $this->handlePostback($this->msg['events'][0], $to);
		} else {
			$text = $this->msg['events'][0]['message']['text'] ?? '';
			$msg = $this->createMessage($text);
		}

		e(print_r($msg, true));

		if (is_array($msg)) {
			$ret = $this->sender->push($to, $msg);
		} elseif (is_string($msg)) {
			$ret = $this->sender->pushText($to, $msg);
		} else {
			$ret = ['status' => 400];
		}
	}

	public function getUserInfo(array $msg): array
	{
		return json_decode($this->sender->getProfile($msg['source']['userId'], getenv('GROUP_ID')), true);
	}

	/**
     *
     * @return bool true if 応答した
     */
    public function handlePostback(array $msg, string $to): bool
	{
        $cookie=new Cookie();
        if(!$cookie->isValidInterval($msg)){
            ll("INVALID INTERVAL");
            return false;
        }

		$userInfo = $this->getUserInfo($msg);
		$name = $userInfo['displayName'];

		if ($msg['postback']['data'] === 'yes') {
			$reply = "hey $name, ".$this->message['GOOD'];
		} elseif ($msg['postback']['data'] === 'no') {
			$reply = "Ohhhhhhhhh Arrrrrghhhhhhh $name, ".$this->message['NOGOOD'];
		}
		$ret = $this->sender->pushText($to, $reply);

		$this->db->insertDrink([
			'user_id' => $msg['source']['userId'],
			'drink' => 1,
		]);
        return true;
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
			return $this->message['KR1'];
		} elseif (preg_match('/文句がある/', $text)) {
			return $this->message['KR2'];
		} elseif (preg_match('/365/', $text)) {
			return $this->message['DEF365'];
		} elseif (preg_match('/KR/i', $text)) {
			return [
				'type' => 'flex',
				'altText' => $this->message['KR3'],
				'contents' => json_decode(file_get_contents('messages/json/kuri.json'), true),
			];
		} elseif (preg_match('/綾馬場/', $text)) {
			return $this->message['AYB1'];
		} elseif (preg_match('/ああああ/', $text)) {
			return [
				'type' => 'flex',
				'altText' => $this->message['TIME'],
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
