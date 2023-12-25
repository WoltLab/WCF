<?php

namespace wcf\action;

use Laminas\Diactoros\Response\EmptyResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class FileUploadAction implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        // TODO: Dummy response to simulate a successful upload of a chunk.
        return new EmptyResponse();
    }
}
