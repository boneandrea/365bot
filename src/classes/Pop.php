<?php
//
// グループ内投稿を順に読み取り、処理する
// 処理：応答 or 捨てる
//
// Kick me in crontab:
//
// example:
// * * * * * for i in `seq 0 10 59`;do (sleep ${i}; php www/hook/src/classes/Pop.php) & done
//

namespace Util;

require_once __DIR__.'/../../vendor/autoload.php';

use Util\Reply;

(new Reply())->execute();
