<?php
namespace Util;

require_once __DIR__.'/../vendor/autoload.php';

use Dotenv\Dotenv;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Team365Bot
{
    public $msg; // これをパースする

    public function __construct($json)
    {
        $this->msg=$json;

        $dotenv = Dotenv::create(__DIR__."/..");
        $dotenv->load(); //.envが無いとエラーになる
        $this->log = new Logger('MONOLOG_TEST');
        //ログレベルをDEBUG（最も低い）に設定
        $handler = new StreamHandler('./logs/app.log', Logger::DEBUG);
        $this->log->pushHandler($handler);
    }

    // なんか考えてリプライする
    public function reply()
    {
        $text=$this->msg["events"][0]["message"]["text"];
        $this->log->addDebug($text);

        $to= ($this->checkMessageType() === "user") ? getenv("TO_USER_ID"): getenv("GROUP_ID");

        $msg=$this->createMessage($text);
        $this->log->addDebug($msg, ["additional"]);
        if (is_array($msg)) {
            $ret=$this->push($to, $msg);
        } elseif (is_string($msg)) {
            $ret=$this->pushText($to, $msg);
        }
    }

    public function checkMessageType()
    {
        return (isset($this->msg["events"][0]["source"]["groupId"])) ? "group":"user";
    }

    public function createMessage($text)
    {
        if (preg_match("/別にない/", $text)) {
            return "当然ですね";
        } elseif (preg_match("/文句がある/", $text)) {
            return "綾馬場さんがなんとかしてくれますよ";
        } elseif (preg_match("/365/", $text)) {
            return "365日雨の日も晴れの日も薄汚れた居酒屋の片隅で酒を飲むことしか知らない人生の無駄遣いの見本市のような皆さん";
        } elseif (preg_match("/KR/i", $text)) {
            $str=file_get_contents("kuri.json");
            return [
                "type"=> "flex",
                "altText"=> "This is a Flex Message",
                "contents"=>json_decode($str, true)
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

        return $this->_myPost($payload, getenv('LINE_API_PUSH'));
    }

    public function pushText($to, $text)
    {
        $this->push($to, [
            'type' => 'text',
            'text' => $text
        ]);
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

        $this->log->addDebug($ret, ["additional"]);
        return $ret;
    }
}
