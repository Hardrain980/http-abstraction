<?php

namespace Leo\Http;

use \Psr\Http\Message\ResponseInterface;
use \GuzzleHttp\Psr7;

class Redirect extends \Exception implements ToResponseInterface
{
	/**
	 * @var bool True for 301, otherwise 302.
	 */
	private bool $permanent;

	/**
	 * @var array<string, string> Additional headers
	 */
	private array $headers;

	/**
	 * @var array<\Leo\Http\Cookie> Additional cookies
	 */
	private array $cookies;

	public function __construct(
		string $location,
		bool $permanent = false,
		array $headers = [],
		array $cookies = []
	)
	{
		$this->permanent = $permanent;
		$this->headers = $headers;
		$this->cookies = $cookies;

		$this->headers['Location'] = $location;
	}

	public function toResponse():ResponseInterface
	{
		$response = (new Psr7\Response())
			->withStatus(
				$this->permanent ? 301 : 302,
				$this->permanent ? 'Moved Permanently' : 'Found'
			);

		foreach ($this->headers as $key => $value)
			$response = $response
				->withHeader($key, $value);

		foreach ($this->cookies as $cookie)
			$response = $response
				->withAddedHeader('Set-Cookie', strval($cookie));

		return $response;
	}
}

?>
