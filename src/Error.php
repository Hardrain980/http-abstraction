<?php

namespace Leo\Http;

use \Psr\Http\Message\ResponseInterface;
use \Nyholm\Psr7;

class Error extends \Exception implements ToResponseInterface
{
	private const PHRASES = [
		400 => 'Bad Request',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found',
		405 => 'Method Not Allowed',
		406 => 'Not Acceptable',
		409 => 'Conflict',
		410 => 'Gone',
		411 => 'Length Required',
		412 => 'Precondition Failed',
		413 => 'Payload Too Large',
		414 => 'URI Too Long',
		415 => 'Unsupported Media Type',
		417 => 'Expectation Failed',
		421 => 'Misdirected Request',
		429 => 'Too Many Requests',
		431 => 'Request Header Fields Too Large',
		451 => 'Unavaliable For Legal Reasons',
		500 => 'Internal Server Error',
		501 => 'Not Implemented',
		502 => 'Bad Gateway',
		503 => 'Service Unavaliable',
		504 => 'Gateway Timeout',
		505 => 'HTTP Version Not Supported',
	];

	/**
	 * @var int Status code
	 */
	private int $status;

	/**
	 * @var string Error message
	 */
	private ?string $error;

	/**
	 * @var array<string,string> Additional headers
	 */
	private array $headers;

	public function __construct(
		int $status,
		string $error = null,
		array $headers = []
	)
	{
		if ($status < 400 || $status > 599)
			throw new \UnexpectedValueException("\"{$status}\" is not a valid HTTP Error Code");

		$this->status = $status;
		$this->error = $error;
		$this->headers = $headers;
	}

	public function getRawError():?string
	{
		return $this->error;
	}

	public function toResponse():ResponseInterface
	{
		$phrase = self::PHRASES[$this->status] ?? null;
		$body = strval($this->status);

		if (!is_null($phrase))
			$body .= " {$phrase}";

		if (!is_null($this->error))
			$body .= "\n{$this->error}";

		$response = (new Psr7\Response())
			->withStatus($this->status, strval($phrase))
			->withHeader('Content-Type', 'text/plain')
			->withBody(Psr7\Stream::create($body));

		foreach ($this->headers as $key => $value)
			$response = $response
				->withHeader($key, $value);

		return $response;
	}
}

?>
