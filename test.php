<?php

namespace Util;

require_once __DIR__.'/vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Util\Team365Bot;

class MyTest extends TestCase
{
    public function setUp()
    {
        $obj=[
            "events"=>[
                [
                    "type"=>"message",
                    "replyToken"=>"replytoken",
                    "source"=>[
                        "userId"=>"userid",
                        "type"=>"user"
                    ],
                    "timestamp"=>1556410170998,"message"=>["type"=>"text","id"=>"9769174818135","text"=>"365"]
                ]
            ],
            "destination"=>"destination"
        ];

        $this->bot=new Team365Bot($obj);
    }

    public function testCreateMessage()
    {
        $this->assertEquals("飲んでんとはよ帰れ老人共！", $this->bot->createMessage("foo365bar"));

        $json=json_encode($this->bot->createMessage("fooKRbar"),JSON_UNESCAPED_UNICODE);
        $this->assertContains("オッス",$json);
        $this->assertArrayHasKey("altText",$this->bot->createMessage("fooKRbar"));
        $this->assertArrayHasKey("contents",$this->bot->createMessage("fooKRbar"));
    }

    public function testCheckMessageTypeUser()
    {
        $this->bot->msg=[
            "events"=>[
                [
                    "type"=>"message",
                    "replyToken"=>"replytoken",
                    "source"=>[
                        "userId"=>"userid",
                        "type"=>"user"
                    ],
                    "timestamp"=>1556410170998,"message"=>["type"=>"text","id"=>"9769174818135","text"=>"365"]
                ]
            ],
            "destination"=>"destination"
        ];
        $this->assertEquals("user", $this->bot->checkMessageType());
    }

    public function testCheckMessageTypeGroup()
    {
        $this->bot->msg=[
            "events"=>[
                [
                    "type"=>"message",
                    "replyToken"=>"replytoken",
                    "source"=>[
                        "userId"=>"userid",
                        "groupId"=>"groupid",
                        "type"=>"group"
                    ],
                    "timestamp"=>1556372622825,
                    "message"=>[
                        "type"=>"text",
                        "id"=>"9767205338329",
                        "text"=>"久しぶりになったけど辛いぜ。尻から水が1リットル位出てくる"
                    ]
                ]
            ],
            "destination"=>"destination"
        ];
        $this->assertEquals("group", $this->bot->checkMessageType());
    }
}
