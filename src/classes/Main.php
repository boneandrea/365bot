<?php

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';

class Main
{
	private $bot;
    use JsonMessageTrait;

	public function __construct()
	{
		define('IS_PRD', getenv('MODE') === 'prod');
	}

	// TODO: verify
	public function verify_signature($sign)
	{
		return true;
	}

	/**
	 * 誰に送る.
	 *
	 * @param string $type
	 *
	 * @return string MessagingAPIで使う送信先ID
	 */
	public function getRecipient($type="")
	{
		if (IS_PRD) {
			return ($type === 'user') ? getenv('TO_USER_ID') : getenv('GROUP_ID');
		}

        return getenv('TO_USER_ID');
	}

    // 時報送信: shell実行(by cron)
    public function alarm_message()
    {
        $schedule = new Schedule();
        if (!$schedule->isTimeToSendAlarm()) {
            e('check time... time is not to send');

            return;
        }

        e('send');
        $this->bot = new Team365Bot();

        $to = $this->getRecipient("group");

        $this->bot->pushText(
            $to,
            '飲んでんとはよ帰れ老人共！'
        );

        $content = $this->getMessageJson('hello.json');
        $this->bot->push($to, [
            'type' => 'flex',
            'altText' => 'やあみんな、Botだよ。',
            'contents' => $content,
        ]);

        $schedule->setNextScheduledTime();
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
        // if ($_SERVER['argv'][1] ?? '' === 'alarm') {
        // e("CRON:時報");
        //     $this->send_message();
        //     return;
        // }

        if($_SERVER["REQUEST_METHOD"] === "GET"){
            if ($this->cron_authenticate()){
                e("CRON");
                $this->alarm_message();
            }
            return;
        }

        $data = $this->recv_data();
        $this->reply($data);
    }

    public function cron_authenticate(): bool
    {
        $authHeader = $_SERVER['HTTP_X_TEAM365_AUTH']??"";
        return $authHeader === getenv("TEAM365_ACCESS_KEY");
    }
}
