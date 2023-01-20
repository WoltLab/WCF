<?php

namespace wcf\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\LegacyPlaceholderResponse;

/**
 * Adds 'x-content-type-options: nosniff' to the response.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class PreventMimeSniffing implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Also set the headers using the regular `\header()` call, because we might receive a
        // LegacyPlaceholderResponse and we also need to protect requests to legacy controllers.
        // If a proper PSR-7 response is returned the headers will be removed again and set on
        // the response object.
        \header('x-content-type-options: nosniff');

        $response = $handler->handle($request);

        if ($response instanceof LegacyPlaceholderResponse) {
            return $response;
        }

        \header_remove('x-content-type-options');

        return $response->withHeader('x-content-type-options', 'nosniff');
    }
}
