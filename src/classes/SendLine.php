<?php

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';

class SendLine
{
	public function __construct()
	{
	}

	public function push(string $to, array $msg)
	{
		$payload = [
            'to' => $to,
            'messages' => [$msg],
        ];

		return $this->_myPost($payload, getenv('LINE_API_PUSH'));
	}

	public function pushText($to, $text)
	{
		return $this->push($to, [
            'type' => 'text',
            'text' => $text,
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
        e($payload);
		$ch = curl_init($apiUrl);
		$options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => $this->header(),
            CURLOPT_POSTFIELDS => json_encode($payload),
        ];

		curl_setopt_array($ch, $options);
		$ret = curl_exec($ch);
		$httpcode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
		curl_close($ch);

		e($httpcode.':'.$ret);

		if ($httpcode) {
			e('送信成功');
		} else {
			e('送信失敗');
		}

		return ['status' => $httpcode, 'body' => $ret];
	}

	public function _myGet($apiUrl)
	{
		$ch = curl_init($apiUrl);
		$options = [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $this->header(),
        ];

		curl_setopt_array($ch, $options);
		$ret = curl_exec($ch);
		curl_close($ch);

		return $ret;
	}

	public function getProfile($userId, $groupId)
	{
		return $this->_myGet("https://api.line.me/v2/bot/group/$groupId/member/$userId");
	}
}
