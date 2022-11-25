<?php

namespace Util;

trait JsonMessageTrait
{
    public function getMessageJson(string $filename): array
    {
        return json_decode(file_get_contents(__DIR__.'/../../messages/json/'.$filename), true);
    }
}
