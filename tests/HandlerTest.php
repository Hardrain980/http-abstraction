<?php

use \Leo\Http\Handler;
use \Leo\Fixtures\DummyLogger;
use \PHPUnit\Framework\TestCase;
use \GuzzleHttp\Psr7;

/**
 * @testdox \Leo\Http\Handler
 */
class HandlerTest extends TestCase
{
	protected Handler $handler;

	public function setUp():void
	{
		$this->handler = (new Handler('', new DummyLogger()))
			->addRoute('/200', function ($r) {
				return (new Psr7\Response())
					->withStatus(200, 'OK')
					->withHeader('Content-Type', 'text/plain')
					->withBody(Psr7\Utils::streamFor('Hello'));
			})
			->addRoute('/405', function ($r) {
				return (new Psr7\Response())
					->withStatus(200, 'OK')
					->withHeader('Content-Type', 'text/plain')
					->withBody(Psr7\Utils::streamFor('Hello, to a POST request.'));
			}, ['POST'])
			->addRoute('/500', function ($r) {
				throw new Exception('Ooops...');
			});
	}

	public function testFound():void
	{
		$r = $this->handler->__invoke(new Psr7\ServerRequest('GET', '/200'));

		$this->assertSame(200, $r->getStatusCode());
		$this->assertSame('Hello', strval($r->getBody()));
	}

	public function testMethodNotAllowed():void
	{
		$r = $this->handler->__invoke(new Psr7\ServerRequest('GET', '/405'));

		$this->assertSame(405, $r->getStatusCode());
	}

	public function testNotFound():void
	{
		$r = $this->handler->__invoke(new Psr7\ServerRequest('GET', '/not-exist'));

		$this->assertSame(404, $r->getStatusCode());
	}

	public function testErrorResponse():void
	{
		ob_start();
		$r = $this->handler->__invoke(new Psr7\ServerRequest('GET', '/500'));
		ob_end_clean();

		$this->assertSame(500, $r->getStatusCode());
	}

	public function testErrorLogging():void
	{
		$this->expectOutputRegex('/.*req_id.*?Ooops.*?/s');

		$r = $this->handler->__invoke(
			(new Psr7\ServerRequest('GET', '/500'))
				->withAttribute('REQUEST_ID', 'req_id')
		);
	}

	public function testPsr15Compatibility():void
	{
		$r = $this->handler->handle(new Psr7\ServerRequest('GET', '/200'));
		$this->assertTrue($r instanceof Psr7\Response);
	}
}

?>
