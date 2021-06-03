<?php

openlog('Team365', LOG_PID, LOG_LOCAL7);

require_once __DIR__.'/vendor/autoload.php';
use Util\Main;

(new Main())->execute();
