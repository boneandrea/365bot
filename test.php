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

    public function testCreateMessage365()
    {
        $this->assertContains(
            "雨の日も晴れの日も",
            $this->bot->createMessage("foo365bar"),
            "365に反応"
        );
    }

    public function testCreateMessageKR()
    {
        $json=json_encode($this->bot->createMessage("fooKRbar"), JSON_UNESCAPED_UNICODE);
        $this->assertContains("オッス", $json);

        $json=json_encode($this->bot->createMessage("fookrbar"), JSON_UNESCAPED_UNICODE);
        $this->assertContains("オッス", $json);

        $this->assertEquals(
            "当然ですね",
            $this->bot->createMessage("別にない")
        );
        $this->assertEquals(
            "綾馬場さんがなんとかしてくれますよ",
            $this->bot->createMessage("文句がある")
        );
        $this->assertNull(
            $this->bot->createMessage("栗林")
        );
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
