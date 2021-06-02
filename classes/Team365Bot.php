<?php

namespace Util;

require_once __DIR__.'/../vendor/autoload.php';

use Dotenv\Dotenv;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Team365Bot
{
	public $msg; // これをパースする
    public $sender;

	public function __construct($json)
	{
		$this->msg = $json;

		$dotenv = Dotenv::create(__DIR__.'/..');
		$dotenv->load(); //.envが無いとエラーになる
		$this->log = new Logger('MONOLOG_TEST');
        $this->sender=new SendLine($this->log);
		//ログレベルをDEBUG（最も低い）に設定
		$handler = new StreamHandler('./logs/app.log', Logger::DEBUG);
		$this->log->pushHandler($handler);
	}

	// なんか考えてリプライする
	public function reply()
	{
		error_log(print_r($this->msg,true));
		$this->log->addDebug($this->checkMessageType());

		$type = $this->checkMessageType();
		$to = ($type=== 'user' || $type==="postback") ? getenv('TO_USER_ID') : getenv('GROUP_ID');

        if($type==="postback"){
            return $this->handlePostback($this->msg['events'][0], $to);
        }

		$text = $this->msg['events'][0]['message']['text'];
		$msg = $this->createMessage($text);

		if (is_array($msg)) {
			$ret = $this->push($to, $msg);
		} elseif (is_string($msg)) {
			$ret = $this->pushText($to, $msg);
		}
	}

	public function handlePostback(array $msg, string $to){

        if($msg["postback"]["data"] === "yes"){
            $msg="でかした";
        }elseif($msg["postback"]["data"] === "no"){
            $msg="なんとなさけない";
        }
        $ret = $this->pushText($to, $msg);
    }

	public function checkMessageType()
	{
		if($this->msg['events'][0]['type']==="postback"){
            return "postback";
        }elseif (isset($this->msg['events'][0]['source']['groupId'])){
            return 'group';
        }else{
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
				'altText' => 'message',
				'contents' => json_decode(file_get_contents('messages/json/hello.json'), true),
			];
		}

		return null;
	}

	public function push($to, $json)
	{
		$payload = [
			'to' => $to,
			'messages' => [$json],
		];

		return $this->sender->_myPost($payload, getenv('LINE_API_PUSH'));
	}

	public function pushText($to, $text)
	{
		$this->push($to, [
			'type' => 'text',
			'text' => $text,
		]);
	}
}
