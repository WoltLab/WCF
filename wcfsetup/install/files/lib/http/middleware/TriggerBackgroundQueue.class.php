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
    private const HEADER_NAME = 'woltlab-background-queue-check';
    private const HEADER_VALUE = 'yes';

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
        $response = $handler->handle($request);
        if (
            $this->requestHandler->isACPRequest()
            || !$this->backgroundQueueHandler->hasPendingCheck()
        ) {
            return $response;
        }

        if ($response instanceof LegacyPlaceholderResponse) {
            \header(
                \sprintf('%s: %s', self::HEADER_NAME, self::HEADER_VALUE)
            );

            return $response;
        }

        return $response->withHeader(self::HEADER_NAME, self::HEADER_VALUE);
    }
}
