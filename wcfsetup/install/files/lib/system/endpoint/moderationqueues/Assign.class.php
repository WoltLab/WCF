<?php

namespace wcf\system\endpoint\moderationqueues;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;

final class Assign implements IController
{
    #[PostRequest('/core/moderationqueues/:id/assign')]
    public function assign(int $id, ServerRequestInterface $request): ResponseInterface
    {
        return new JsonResponse([
            'id' => $id,
            'request' => $request,
        ]);
    }
}
