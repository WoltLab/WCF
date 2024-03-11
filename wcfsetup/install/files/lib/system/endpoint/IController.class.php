<?php

namespace wcf\system\endpoint;

use CuyZ\Valinor\Mapper\MappingError;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

interface IController
{
    /**
     * @param array<string, string> $variables
     * @throws MappingError
     */
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface;
}
