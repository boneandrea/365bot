<?php

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';

class Schedule
{
    public function __construct()
    {
        e("cron");
    }

    /**
     * 時報の時間か？
     *
     * @return bull|true if 送信時刻
     */
    public function isTimeToSendAlarm(): bool
    {
        $client = new Client();
        $scheduled = $client->get('ALARM');

        e(date('Y-m-d H:i', $scheduled));
        e(date('Y-m-d H:i', time()));

        return time() > (int) $scheduled;
    }

    public function setNextScheduledTime(): bool
    {
        $date = (new \DateTime())->setTime(0, 0, 0);
        // e($date->format('Y-m-d H:i'));

        $date->modify('+1 day');
        $date->setTime(21, 30, 0);
        $r = rand(0, 60);
        $date->modify("+{$r} minutes");
        // e($date->format('Y-m-d H:i'));

        $client = new Client();
        $client->set('ALARM', $date->getTimestamp());
        $client->save();

        return true;
    }
}
