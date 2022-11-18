<?php

namespace wcf\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\LegacyPlaceholderResponse;
use wcf\system\background\BackgroundQueueHandler;
use wcf\system\request\RequestHandler;

/**
 * Adds 'woltlab-background-queue-check: yes' to the response
 * whenever a check was requested.
 *
 * @author Alexander Ebert
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Http\Middleware
 * @since 6.0
 */
final class TriggerBackgroundQueue implements MiddlewareInterface
{
    private readonly BackgroundQueueHandler $backgroundQueueHandler;

    private readonly RequestHandler $requestHandler;

    public function __construct()
    {
        $this->backgroundQueueHandler = BackgroundQueueHandler::getInstance();
        $this->requestHandler = RequestHandler::getInstance();
    }

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if ($this->requestHandler->isACPRequest()) {
            return $handler->handle($request);
        }

        $response = $handler->handle($request);

        if ($response instanceof LegacyPlaceholderResponse) {
            return $response;
        }

        if (!$this->backgroundQueueHandler->hasPendingCheck()) {
            return $response;
        }

        return $response->withHeader(
            BackgroundQueueHandler::FORCE_CHECK_HTTP_HEADER_NAME,
            BackgroundQueueHandler::FORCE_CHECK_HTTP_HEADER_VALUE,
        );
    }
}
