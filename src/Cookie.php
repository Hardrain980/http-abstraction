<?php

namespace Leo\Http;

class Cookie
{
	/**
	 * @var string Cookie name
	 */
	private string $key;

	/**
	 * @var string Cookie value
	 */
	private string $value;

	/**
	 * @var int Cookie lifespan, nullable
	 */
	private ?int $max_age;

	/**
	 * @var string Applied path of cookie, nullable
	 */
	private ?string $path;

	/**
	 * @var string Applied domain of cookie, nullable
	 */
	private ?string $domain;

	/**
	 * @var bool Applies for HTTPS only
	 */
	private bool $secure;

	/**
	 * @var bool Cookie accessible only from HTTP requests
	 */
	private bool $http_only;

	public function __construct(
		string $key,
		string $value,
		?int $max_age = null,
		?string $path = null,
		?string $domain = null,
		bool $secure = false,
		bool $http_only = false
	)
	{
		// Empty key is not allowed
		if ($key == '')
			throw new \Exception('Cookie key could not be empty.');

		$this->key = $key;
		$this->value = $value;
		$this->max_age = $max_age;
		$this->path = $path;
		$this->domain = $domain;
		$this->secure = $secure;
		$this->http_only = $http_only;
	}

	public function __toString():string
	{
		$cookie = sprintf('%s=%s',
			urlencode($this->key),
			urlencode($this->value)
		);

		if ($this->max_age !== null)
			$cookie .= "; Max-Age={$this->max_age}";

		if ($this->path !== null)
			$cookie .= "; Path={$this->path}";

		if ($this->domain !== null)
			$cookie .= "; Domain={$this->domain}";

		if ($this->secure)
			$cookie .= "; Secure";

		if ($this->http_only)
			$cookie .= "; HttpOnly";

		return $cookie;
	}
}

?>
