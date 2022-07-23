<?php

namespace Util;

require_once __DIR__.'/../vendor/autoload.php';

use PHPUnit\Framework\TestCase;
use Util\Cookie;
use \DateTime;

class CookieTest extends TestCase
{
    public $db;

    public function readJson(string $filename):array{
        return json_decode(file_get_contents(dirname(__FILE__)."/".$filename), true);
    }

	public function setUp(): void
	{
		$this->db = new MyDB();
		$this->obj = new Cookie([]);
	}

	public function testFirstAccess()
	{
		$accessTime=$this->obj->getLastAccessTime("NOT_EXIST_USER");
        $this->assertNull($accessTime);

	}

	public function test2ndAccessInTerm()
	{
        // 6 > 5 (5=DEFAULT_INTERVAL)
		$accessTime=(new DateTime("now"))->sub(new \DateInterval('PT6S'));
        $this->assertTrue($this->obj->_isEnoughInterval($accessTime));
	}

	public function test2ndAccessAfterTermPassed()
	{
		$accessTime=(new DateTime("now"))->sub(new \DateInterval('PT2S'));

        $this->assertFalse($this->obj->_isEnoughInterval($accessTime));
	}

	public function testMessage1()
	{
        $this->markTestSkipped();
        $data=$this->readJson("msg1.json");
        $this->assertTrue($this->obj->isValidInterval($data));
	}

	public function testMessage2()
	{
        $data=$this->readJson("msg2.json");
        $this->assertTrue($this->obj->isValidInterval($data));
	}

    public function provideInterval(){
        return [
            [0, false],
            [1, false],
            [5, false],
            [6, true],
        ];
    }
	/**
     * @dataProvider provideInterval
     *
     */
    public function testMessage3(int $interval, bool $status)
	{
        $this->markTestSkipped();
		try {
			$stmt = $this->db->pdo->prepare('UPDATE drink set stamp=? where id=381');
            $stmt->execute([date('Y-m-d H:i:s', time()-$interval)]);
		} catch (Exception $e) {
			var_dump($e->getMessage());
		}

		$accessTime=$this->obj->getLastAccessTime("U11bac06cffe164a45e0dd72c438bb68f");
        $this->assertSame($status, $this->obj->_isEnoughInterval($accessTime));
	}
}
