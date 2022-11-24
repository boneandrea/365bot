<?php

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';

class Main
{
	private $bot;

	public function __construct()
	{
		define('IS_PRD', getenv('MODE') === 'prod');
		e('START');
	}

	// TODO: verify
	public function verify_signature($sign)
	{
		return true;
	}

	public function getRecipient()
	{
		return getenv('GROUP_ID');
	}

	// shell実行、時報
	public function send_message()
	{
		e('send');

		$this->bot = new Team365Bot();

		$to = $this->getRecipient();
		$this->bot->pushText(
			$to,
			'飲んでんとはよ帰れ老人共！'
		);

		$this->bot->push($to, [
			'type' => 'flex',
			'altText' => 'やあみんな、Botだよ。',
			'contents' => json_decode(file_get_contents('messages/json/hello.json'), true),
		]);
	}

	// Webhook
	public function recv_data(): array
	{
		$this->verify_signature($_SERVER['HTTP_X_LINE_SIGNATURE'] ?? "");
		e('LINE HEADER SIGNATURE IS OK');

		$json_string = file_get_contents('php://input');
        e($json_string);

		return json_decode($json_string, true) ?? [];
	}

	// Webhook
	public function reply(array $data)
	{
		$this->bot = new Team365Bot($data);
		$this->bot->reply();
	}

	public function execute()
	{
		if (PHP_SAPI === 'cli') {
			$this->send_message();
		} else {
			$data = $this->recv_data();
			$this->reply($data);
		}
	}
}
