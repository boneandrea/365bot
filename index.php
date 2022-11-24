<?php

openlog('Team365', LOG_PID, LOG_LOCAL7);

require_once __DIR__.'/vendor/autoload.php';
use Util\Main;

function e(mixed $msg){
    error_log(print_r($msg, true)."\n");
}

(new Main())->execute();
