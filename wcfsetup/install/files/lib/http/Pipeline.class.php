<?php

namespace wcf\http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Represents a middleware pipeline that sends the ServerRequestInterface
 * through all MiddlewareInterfaces in the order they were given. The last
 * MiddlewareInterface will be connected to the RequestHandlerInterface.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class Pipeline implements MiddlewareInterface
{
    /**
     * @var MiddlewareInterface[]
     */
    private array $middlewares;

    /**
     * @param MiddlewareInterface[] $middlewares
     */
    public function __construct(array $middlewares)
    {
        $this->middlewares = $middlewares;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        foreach (\array_reverse($this->middlewares) as $middleware) {
            $handler = new RequestHandlerMiddleware($middleware, $handler);
        }

        return $handler->handle($request);
    }
}
