<?php

use \Leo\Http\Redirect;
use \Leo\Http\Cookie;
use \PHPUnit\Framework\TestCase;

/**
 * @testdox \Leo\Http\Redirect
 */
class RedirectTest extends TestCase
{
	public function testTemporary():void
	{
		$r = (new Redirect('/123', false))->toResponse();

		$this->assertSame(302, $r->getStatusCode());
		$this->assertSame('Found', $r->getReasonPhrase());
	}

	public function testPermanent():void
	{
		$r = (new Redirect('/123', true))->toResponse();

		$this->assertSame(301, $r->getStatusCode());
		$this->assertSame('Moved Permanently', $r->getReasonPhrase());
	}

	/**
	 * @depends CookieTest::testBasicKeyValue
	 */
	public function testAdditonalCookies():void
	{
		$r = (new Redirect(
			'/123',
			false,
			[],
			[new Cookie('k1', 'v1'), new Cookie('k2', 'v2')]
		))->toResponse();

		$this->assertSame(
			'k1=v1, k2=v2',
			$r->getHeaderLine('Set-Cookie')
		);
	}

	public function testAdditionalHeaders():void
	{
		$r = (new Redirect(
			'/123',
			false,
			['X-Foo' => 'bar', 'X-Boo' => 'far']
		))->toResponse();

		$this->assertSame('bar', $r->getHeaderLine('X-Foo'));
		$this->assertSame('far', $r->getHeaderLine('X-Boo'));
	}
}

?>
