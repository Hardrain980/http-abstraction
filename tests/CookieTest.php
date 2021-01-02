<?php

use \Leo\Http\Cookie;
use \PHPUnit\Framework\TestCase;

/**
 * @testdox \Leo\Http\Cookie
 */
class CookieTest extends TestCase
{
	public function testBasicKeyValue():void
	{
		$c = new Cookie('k/key', 'v/value');

		$this->assertSame(
			'k%2Fkey=v%2Fvalue',
			strval($c)
		);
	}

	public function testWithoutKey():void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Cookie key could not be empty.');
		$c = new Cookie('', 'value');
	}

	public function testWithMaxAge():void
	{
		$c = new Cookie('key', 'value', 180);

		$this->assertSame(
			'key=value; Max-Age=180',
			strval($c)
		);
	}

	public function testWithPath():void
	{
		$c = new Cookie('key', 'value', null, '/path/to/app/');

		$this->assertSame(
			'key=value; Path=/path/to/app/',
			strval($c)
		);
	}

	public function testWithDomain():void
	{
		$c = new Cookie('key', 'value', null, null, 'domain.tld');

		$this->assertSame(
			'key=value; Domain=domain.tld',
			strval($c)
		);
	}

	public function testSecure():void
	{
		$c = new Cookie('key', 'value', null, null, null, true);

		$this->assertSame(
			'key=value; Secure',
			strval($c)
		);
	}

	public function testHttpOnly():void
	{
		$c = new Cookie('key', 'value', null, null, null, false, true);

		$this->assertSame(
			'key=value; HttpOnly',
			strval($c)
		);
	}
}

?>
