<?php

namespace Util;

require_once __DIR__.'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Util\Cookie;
use \DateTime;

class CookieTest extends TestCase
{
	public function setUp(): void
	{
		$this->obj = new Cookie([]);
	}

	public function testFirstAccess()
	{
		$accessTime=$this->obj->getLastAccessTime("NOT_EXIST_USER");
        $this->assertNull($accessTime);

	}

	public function test2ndAccessInTerm()
	{
		$accessTime=$this->obj->getLastAccessTime("U11bac06cffe164a45e0dd72c438bb68f");
        // 6 > 5 (5=DEFAULT_INTERVAL)
		$accessTime=(new DateTime("now"))->sub(new \DateInterval('PT6S'));
        $this->assertTrue($this->obj->isEnoughInterval($accessTime));
	}

	public function test2ndAccessAfterTermPassed()
	{
		$accessTime=(new DateTime("now"))->sub(new \DateInterval('PT2S'));

        $this->assertFalse($this->obj->isEnoughInterval($accessTime));
	}
}
