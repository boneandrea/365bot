<?php

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';

define("QUEUE","USER_POSTS");

use Dotenv\Dotenv;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Predis\Client;

function e($e)
{
	error_log(print_r($e, true));
}

class Reply
{
    //$client = new Predis\Client();
	public $msg; // これをパースする
	public $sender;
	public $message;
	public $cookie;
	private $db;

	public function __construct()
	{
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

    function ll($s)
    {
        $this->log->addDebug(var_export($s, true));
    }

	public function execute(): void
	{
        $this->pop();
    }

    public function pop(){
        $client = new Client();

        while(($post = $client->rpop(QUEUE))){
            e($post);
            if(empty($post)) break;
            $this->reply(json_decode($post, true));
        }
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
	public function reply(array $msg): void
	{
		$this->msg = $msg;
        $this->ll($msg);
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
        if(!$this->cookie->isValidInterval($msg)){
            e("INVALID INTERVAL");
            return false;
        }

		$userInfo = $this->getUserInfo($msg);
		$name = $userInfo['displayName'];

		if ($msg['postback']['data'] === 'yes') {
			$reply = "hey $name, ".$this->patterns["static_words"]['GOOD'];
		} elseif ($msg['postback']['data'] === 'no') {
			$reply = "Ohhhhhhhhh Arrrrrghhhhhhh $name, ".$this->patterns["static_words"]['NOGOOD'];
		}
		$ret = $this->sender->pushText($to, $reply);

		$this->db->insertDrink([
			'user_id' => $msg['source']['userId'],
			'drink' => 1,
		]);
        return true;
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

	public function createMessage($text)
	{
        foreach($this->patterns["words"] as $w){
            $regexp="/".$w["key"]."/";
            if (preg_match($regexp, $text)) {
                return $w["value"];
            }
		}

        if (preg_match('/KR/i', $text)) {
			return [
				'type' => 'flex',
				'altText' => $this->patterns["static_words"]['KR3'],
				'contents' => json_decode(file_get_contents(__DIR__.'/../../messages/json/kuri.json'), true),
			];
		}

        if (preg_match('/ああああ/', $text)) {
			return [
				'type' => 'flex',
				'altText' => $this->patterns["static_words"]['TIME'],
				'contents' => json_decode(file_get_contents(__DIR__.'/../../messages/json/hello.json'), true),
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
