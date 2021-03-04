<?php

namespace Leo\Http;

use \FastRoute\Dispatcher;
use \FastRoute\Dispatcher\GroupCountBased as Router;
use \FastRoute\RouteCollector as Routes;
use \Psr\Log\LoggerInterface;
use \Psr\Http\Message\ServerRequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Psr\Http\Server\RequestHandlerInterface;
use \Nyholm\Psr7;

class Handler implements RequestHandlerInterface
{
	/**
	 * @var \FastRoute\RouteCollector Routes collector
	 */
	private Routes $routes;

	/**
	 * @var \FastRoute\Dispatcher\GroupCountBased Routes dispatcher
	 */
	private Router $router;

	/**
	 * @var string Path prefix
	 */
	private string $prefix;

	/**
	 * @var \Psr\Log\LoggerInterface Logger, nullable
	 */
	private ?LoggerInterface $logger;

	public function __construct(
		string $prefix,
		LoggerInterface $logger = null
	)
	{
		$this->routes = new Routes(
			new \FastRoute\RouteParser\Std(),
			new \FastRoute\DataGenerator\GroupCountBased()
		);
		$this->router = new Router($this->routes->getData());
		$this->prefix = $prefix;
		$this->logger = $logger;
	}

	public function __invoke(ServerRequestInterface $request):ResponseInterface
	{
		$response = null;

		try {
			$response = $this->routeRequest($request);
		}
		catch (Redirect | Error $e) {
			$response = $e->toResponse();
		}
		catch (\Exception | \Error $e) {
			$request_id = $request->getAttribute('REQUEST_ID');

			if (!is_null($this->logger)) {
				$time = date("Y-m-d H:i:s");
				$msg = is_null($request_id)
					? "{$time} \n{$e}\n"
					: "{$time} [{$request_id}] \n{$e}\n";

				$this->logger->error($msg);
			}

			$body = 
				"500 Internal Server Error\n".
				"The server was unable to handle your request ".
				"due to internal error.";

			if (!is_null($request_id))
				$body .= "\nRequest ID: {$request_id}";

			$response = (new Psr7\Response())
				->withStatus(500, 'Internal Server Error')
				->withHeader('Content-Type', 'text/plain')
				->withBody(Psr7\Stream::create($body));
		}

		return $response;
	}

	public function handle(ServerRequestInterface $request):ResponseInterface
	{
		return ($this)($request);
	}

	public function addRoute(
		string $path,
		callable $handler,
		array $methods = ['GET']
	):self
	{
		// Do not alter the existing handler,
		// clone a new one instead.
		// Also allows method chaining.
		$self = clone $this;

		$self->routes->addRoute(
			array_unique($methods),
			$self->prefix . $path,
			$handler
		);

		// Recreate router
		$self->router = new Router($self->routes->getData());

		return $self;
	}

	public function routeRequest(
		ServerRequestInterface $request
	):ResponseInterface
	{
		$path = $request->getUri()->getPath();
		$method = $request->getMethod();
		$route = $this->router->dispatch($method, $path);
		$response = null;

		switch ($route[0]) {
			case Dispatcher::FOUND:
				$response = $route[1]($request, $route[2]);
				break;

			case Dispatcher::METHOD_NOT_ALLOWED:
				throw new Error(405, "Method \"{$method}\" is not allowed.");

			case Dispatcher::NOT_FOUND:
				throw new Error(404);
		}

		return $response;
	}
}

?>