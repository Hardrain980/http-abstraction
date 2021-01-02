<?php

use \Leo\Http\Error;
use \PHPUnit\Framework\TestCase;

/**
 * @testdox \Leo\Http\Error
 */
class ErrorTest extends TestCase
{
	public function testInvalidErrorCode():void
	{
		$this->expectException(Exception::class);
		$this->expectExceptionMessage('"200" is not a valid HTTP Error Code');

		new Error(200);
	}

	public function testGetRawErrorMessage():void
	{
		$he = new Error(403, 'Permission Denied.');

		$this->assertSame('Permission Denied.', $he->getRawError());
	}

	public function testCastResponseWithKnownStatusCode():void
	{
		$he = new Error(403, 'Permission Denied.', ['X-Section' => 'API']);
		$r = $he->toResponse();

		$this->assertSame(403, $r->getStatusCode());
		$this->assertSame('Forbidden', $r->getReasonPhrase());
		$this->assertSame('API', $r->getHeaderLine('X-Section'));
		$this->assertSame("403 Forbidden\nPermission Denied.", strval($r->getBody()));
	}

	public function testCastResponseWithUnknownStatusCode():void
	{
		$he = new Error(459, 'Something goes wrong.');
		$r = $he->toResponse();

		$this->assertSame(459, $r->getStatusCode());
		$this->assertSame('', $r->getReasonPhrase());
		$this->assertSame("459\nSomething goes wrong.", strval($r->getBody()));
	}
}

?>
