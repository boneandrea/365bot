<?php

openlog('Team365', LOG_PID, LOG_LOCAL7);

require_once __DIR__.'/vendor/autoload.php';
use Util\Main;

function e(mixed $msg){
    $trace=debug_backtrace();
    error_log($trace[1]["file"] ." line ".$trace[1]["line"] ."\n");
    error_log(print_r($msg, true)."\n");
}

(new Main())->execute();
