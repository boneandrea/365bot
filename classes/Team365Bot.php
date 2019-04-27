<?php
namespace Util;

require_once __DIR__.'/../vendor/autoload.php';

use Dotenv\Dotenv;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Team365Bot{

    public $msg; // これをパースする

	public function __construct($json){

        $this->msg=$json;

        $dotenv = Dotenv::create(__DIR__."/..");
		$dotenv->load(); //.envが無いとエラーになる
		$this->log = new Logger('MONOLOG_TEST');
		//ログレベルをDEBUG（最も低い）に設定
        $handler = new StreamHandler('./logs/app.log',Logger::DEBUG);
		$this->log->pushHandler($handler);
	}

	public function push($to, $json)
	{
		$payload = [
			'to' => $to,
			'messages' => [$json],
		];

		return $this->_myPost($payload, getenv('LINE_API_PUSH'));
	}

	public function pushText($to, $text)
	{
		$payload = [
			'to' => $to,
			'messages' => [
				[
					'type' => 'text',
					'text' => $text
				]
			]
		];
		return $this->_myPost($payload, getenv('LINE_API_PUSH'));
	}

	public function header()
	{
		return [
			'Content-Type: application/json',
			'Authorization: Bearer '.getenv('LINE_BOT_ACCESS_TOKEN'),
		];
	}

	public function _myPost($payload, $apiUrl)
	{
		$ch = curl_init($apiUrl);
		$options = [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true,
			CURLOPT_HTTPHEADER => $this->header(),
			CURLOPT_POSTFIELDS => json_encode($payload),
		];

		curl_setopt_array($ch, $options);
		$ret = curl_exec($ch);
		curl_close($ch);

        $this->log->addDebug($ret);
		return $ret;
	}
}
