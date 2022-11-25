<?php

openlog('Team365', LOG_PID, LOG_LOCAL7);

require_once __DIR__.'/vendor/autoload.php';
use Util\Main;

function e(mixed $msg){
    $trace=debug_backtrace()[0];
    error_log($trace["file"] ." line ".$trace["line"] ."\n");
    error_log(print_r($msg, true)."\n");
}

(new Main())->execute();
