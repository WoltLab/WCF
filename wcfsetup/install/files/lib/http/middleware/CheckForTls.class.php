<?php

namespace wcf\http\middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\system\request\RequestHandler;
use wcf\system\request\RouteHandler;
use wcf\util\HeaderUtil;

/**
 * Checks if the request is for the frontend and originates from an insecure context.
 *
 * @author      Alexander Ebert
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class CheckForTls implements MiddlewareInterface
{
    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (RequestHandler::getInstance()->isACPRequest()) {
            return $handler->handle($request);
        }

        if (RouteHandler::secureContext()) {
            return $handler->handle($request);
        }

        return $this->redirectToHttps($request);
    }

    private function redirectToHttps(ServerRequestInterface $request): ResponseInterface
    {
        $uri = $request->getUri()->withScheme('https');

        return HeaderUtil::withNoCacheHeaders(
            new RedirectResponse($uri)
        );
    }
}
