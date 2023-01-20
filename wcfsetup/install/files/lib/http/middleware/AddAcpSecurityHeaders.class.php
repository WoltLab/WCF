<?php

namespace wcf\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\LegacyPlaceholderResponse;
use wcf\system\request\RequestHandler;

/**
 * Add various security headers to harden ACP responses.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class AddAcpSecurityHeaders implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!RequestHandler::getInstance()->isACPRequest()) {
            return $handler->handle($request);
        }

        // Also set the headers using the regular `\header()` call, because we might receive a
        // LegacyPlaceholderResponse and we also need to protect requests to legacy controllers.
        // If a proper PSR-7 response is returned the headers will be removed again and set on
        // the response object.
        \header('referrer-policy: same-origin');
        \header('cross-origin-opener-policy: same-origin');
        \header('cross-origin-resource-policy: same-site');

        $response = $handler->handle($request);

        if ($response instanceof LegacyPlaceholderResponse) {
            return $response;
        }

        \header_remove('referrer-policy');
        \header_remove('cross-origin-opener-policy');
        \header_remove('cross-origin-resource-policy');

        return $response->withHeader('referrer-policy', 'same-origin')
            ->withHeader('cross-origin-opener-policy', 'same-origin')
            ->withHeader('cross-origin-resource-policy', 'same-site');
    }
}
