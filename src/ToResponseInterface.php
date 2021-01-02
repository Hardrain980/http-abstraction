<?php

namespace Leo\Http;

use \Psr\Http\Message\ResponseInterface;

interface ToResponseInterface
{
	public function toResponse():ResponseInterface;
}

?>
