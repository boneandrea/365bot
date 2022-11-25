<?php

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';

class Reply
{
	// $client = new Predis\Client();
	public $msg; // これをパースする
	public $sender;
	public $message;
	public $cookie;
	private $db;

	public function __construct(array $msg)
	{
        if(0)$msg=[
            "destination" => "U31be3e13387f36adb61d34b8899bf88d",
            "events" => [
                [
                    "type" => "postback",
                    "postback" => [
                        "data" => "no",
                    ],
                    "webhookEventId" => "01GJN4TT6ME599BDTF63RP8KNC",
                    "deliveryContext" => [
                        "isRedelivery" =>"",
                    ],
                    "timestamp" => 1669304510175,
                    "source" => [
                        "type" => "group",
                        "groupId" => "C7d5fe41da5e346435863ef60dc2f8661",
                        "userId" => "U11bac06cffe164a45e0dd72c438bb68f",
                    ],
                    "replyToken" => "ea6fa2a9247242c1b7d54f71bc98531e",
                    "mode" => "active",
                ]
            ]
        ];

		$this->msg = $msg;
		$this->sender = new SendLine();
		$this->cookie = new Cookie();

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
		}

        return getenv('TO_USER_ID');
	}

	// なんか考えてリプライする
	public function say(): void
	{
		e("++++++++++++++++++++++++++++++++");
		e($this->msg);
		e($this->checkMessageType());

		$type = $this->checkMessageType();
		$to = $this->setRecipient($type);

		e($this->msg['events'][0]);
		if ($type === 'postback') {
			$msg = $this->handlePostback($this->msg['events'][0], $to);
		} else {
			$text = $this->msg['events'][0]['message']['text'] ?? '';
			$msg = $this->createMessage($text);
		}

		e($msg);

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
	 * @return bool true if 応答した
	 */
	public function handlePostback(array $msg, string $to): bool
	{
		if (!$this->cookie->isValidInterval($msg)) {
			e('TOO MANY CLICKs');

			return false;
		}

		$userInfo = $this->getUserInfo($msg);
		$name = $userInfo['displayName'];

		if ($msg['postback']['data'] === 'yes') {
			$reply = "hey $name, ".$this->patterns['static_words']['GOOD'];
		} elseif ($msg['postback']['data'] === 'no') {
			$reply = "Ohhhhhhhhh Arrrrrghhhhhhh $name, ".$this->patterns['static_words']['NOGOOD'];
		}
		e("A");
		$ret = $this->sender->pushText($to, $reply);

		e("A1");
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
		foreach ($this->patterns['words'] as $w) {
			$regexp = '/'.$w['key'].'/';
			if (preg_match($regexp, $text)) {
				return $w['value'];
			}
		}

		if (preg_match('/KR/i', $text)) {
			$content = $this->getMessageJson('kuri.json');
			$imgs = [
				'356a192b7913b04c54574d18c28d46e6395428ab.png',
				'IMG_20220813_123205.jpg',
			];

			$content['hero']['url'] = 'https://peixe.biz/hook/www/img/'.$imgs[rand(0, count($imgs) - 1)];

			return [
				'type' => 'flex',
				'altText' => $this->patterns['static_words']['KR3'],
				'contents' => $content,
			];
		}

		if (preg_match('/ああああ/', $text)) {
			return [
				'type' => 'flex',
				'altText' => $this->patterns['static_words']['TIME'],
				'contents' => $this->getMessageJson('hello.json'),
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

	public function getMessageJson(string $filename): array
	{
		return json_decode(file_get_contents(__DIR__.'/../../messages/json/'.$filename), true);
	}
}
