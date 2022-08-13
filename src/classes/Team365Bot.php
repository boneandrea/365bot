<?php

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Predis\Client;

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
	public $cookie;
	private $db;

	public function __construct(array $json=[])
	{
		$this->msg = $json;

		// setup log
		$this->log = new Logger('MONOLOG_TEST');
		$this->sender = new SendLine($this->log);
		$handler = new StreamHandler(__DIR__.'/../../logs/app.log', Logger::DEBUG);
		$this->log->pushHandler($handler);
        $this->cookie=new Cookie();

		// setup message
		$this->patterns = json_decode(file_get_contents(__DIR__.'/message.json'), true);

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

		$type = $this->checkMessageType();
		$this->log->addDebug($type);
		$to = $this->setRecipient($type);

        define("QUEUE","USER_POSTS");
        $client = new Client();
        e($this->msg);
        $client->rpush(QUEUE, json_encode($this->msg));
	}

	public function getUserInfo(array $msg): array
	{
		return json_decode($this->sender->getProfile($msg['source']['userId'], getenv('GROUP_ID')), true);
	}

	public function checkMessageType(): string
	{
		if ($this->msg['events'][0]['type'] === 'postback') {
			return 'postback';
		}

        if (isset($this->msg['events'][0]['source']['groupId'])) {
			return 'group';
		}

        return 'user';
	}
}
