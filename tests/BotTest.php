<?php

namespace Util;

require_once __DIR__.'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;

class BotTest extends TestCase
{
	public function setUp(): void
	{
		$obj = [
			'events' => [
				[
					'type' => 'message',
					'replyToken' => 'replytoken',
					'source' => [
						'userId' => 'userid',
						'type' => 'user',
					],
					'timestamp' => 1556410170998, 'message' => ['type' => 'text', 'id' => '9769174818135', 'text' => '365'],
				],
			],
			'destination' => 'destination',
		];

		$this->bot = new Team365Bot($obj);
	}

	public function testCreateMessage365()
	{
		$this->assertMatchesRegularExpression('/雨の日も晴れの日も/',
							$this->bot->createMessage('foo365bar'),
							'365に反応'
		);
	}

	public function testCreateMessageKR()
	{
		$json = json_encode($this->bot->createMessage('fooKRbar'), JSON_UNESCAPED_UNICODE);
		$this->assertMatchesRegularExpression('/オッス/', $json);
		$this->assertMatchesRegularExpression('/栗林先生参上！/', $json);

		$json = json_encode($this->bot->createMessage('fookrbar'), JSON_UNESCAPED_UNICODE);
		$this->assertMatchesRegularExpression('/オッス/', $json);

		$this->assertEquals(
			'当然ですね',
			$this->bot->createMessage('別にない')
		);
		$this->assertEquals(
			'綾馬場さんがなんとかしてくれますよ',
			$this->bot->createMessage('文句がある')
		);
		$this->assertNull(
			$this->bot->createMessage('栗林')
		);

		$json = json_encode($this->bot->createMessage('綾馬場'), JSON_UNESCAPED_UNICODE);
		$this->assertMatchesRegularExpression('/綾馬場さんの話するときは僕を通してください！/', $json);
	}

	public function testCheckMessageTypeUser()
	{
		$this->bot->msg = [
			'events' => [
				[
					'type' => 'message',
					'replyToken' => 'replytoken',
					'source' => [
						'userId' => 'userid',
						'type' => 'user',
					],
					'timestamp' => 1556410170998, 'message' => ['type' => 'text', 'id' => '9769174818135', 'text' => '365'],
				],
			],
			'destination' => 'destination',
		];

		$messageType= $this->bot->checkMessageType();
		$this->assertEquals('user', $messageType);
	}

	public function testHandlePostbackYES()
	{
        // SomeClass クラスのスタブを作成します
        $cookie = $this->createStub(Cookie::class);

        // スタブの設定を行います
        $cookie->method('isValidInterval')
               ->will($this->returnValue(true));
        // Observer クラスのモックを作成します。
        // pushText() メソッドのみのモックです。

        $sender = $this->createStub(SendLine::class);
        $sender->method('pushText')
               ->will($this->returnValue([
                   'status' => 200,
                   'body' => []
               ]));
        $sender->method('getProfile')
               ->will($this->returnValue(json_encode(["displayName"=>"name"])));

        // pushText() メソッドが一度だけコールされ、その際の
        // パラメータは文字列 'something' となる、
        // ということを期待しています。
        $sender->expects($this->once())
                 ->method('pushText')
                 ->with($this->equalTo('nice name'));

        // モックをアタッチします。
        $this->bot->sender=$sender;
        $this->bot->cookie=$cookie;

		$msg = [
            "type" => "postback",
            'postback' => [
                'data' => 'yes'
            ],
            "webhookEventId" => '01GA2NKE5V6TAX36NEWA5JT19V',
            "deliveryContext" =>       [
                "isRedelivery" => ""
            ],
            "timestamp" => 1660094625831,
            "source" => [
                "type" => "group",
                "groupId" => 'C7d5fe41da5e346435863ef60dc2f8661',
                "userId" => "U11bac06cffe164a45e0dd72c438bb68f",
            ],

            "replyToken" => "af4288e0529a4dd088207beba81c3684",
            "mode" => "active",
		];

        $this->bot->handlePostback($msg, "nice name");
	}

	public function testHandlePostbackNO()
	{
        // SomeClass クラスのスタブを作成します
        $cookie = $this->createStub(Cookie::class);

        // スタブの設定を行います
        $cookie->method('isValidInterval')
               ->will($this->returnValue(true));
        // Observer クラスのモックを作成します。
        // pushText() メソッドのみのモックです。

        $sender = $this->createStub(SendLine::class);
        $sender->method('pushText')
               ->will($this->returnValue([
                   'status' => 200,
                   'body' => []
               ]));
        $sender->method('getProfile')
               ->will($this->returnValue(json_encode(["displayName"=>"name"])));

        // pushText() メソッドが一度だけコールされ、その際の
        // パラメータは文字列 'something' となる、
        // ということを期待しています。
        $sender->expects($this->once())
                 ->method('pushText')
                 ->with($this->equalTo('nice name'));

        // モックをアタッチします。
        $this->bot->sender=$sender;
        $this->bot->cookie=$cookie;

		$msg = [
            "type" => "postback",
            'postback' => [
                'data' => 'no'
            ],
            "webhookEventId" => '01GA2NKE5V6TAX36NEWA5JT19V',
            "deliveryContext" =>       [
                "isRedelivery" => ""
            ],
            "timestamp" => 1660094625831,
            "source" => [
                "type" => "group",
                "groupId" => 'C7d5fe41da5e346435863ef60dc2f8661',
                "userId" => "U11bac06cffe164a45e0dd72c438bb68f",
            ],

            "replyToken" => "af4288e0529a4dd088207beba81c3684",
            "mode" => "active",
		];

        $this->bot->handlePostback($msg, "nice name");
	}

	public function testCheckMessageTypeGroup()
	{
		$this->bot->msg = [
			'events' => [
				[
					'type' => 'message',
					'replyToken' => 'replytoken',
					'source' => [
						'userId' => 'userid',
						'groupId' => 'groupid',
						'type' => 'group',
					],
					'timestamp' => 1556372622825,
					'message' => [
						'type' => 'text',
						'id' => '9767205338329',
						'text' => '久しぶりになったけど辛いぜ。尻から水が1リットル位出てくる',
					],
				],
			],
			'destination' => 'destination',
		];
		$this->assertEquals('group', $this->bot->checkMessageType());
	}
}
