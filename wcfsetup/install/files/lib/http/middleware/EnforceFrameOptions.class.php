<?php

namespace wcf\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\LegacyPlaceholderResponse;

/**
 * Adds 'x-frame-options: SAMEORIGIN' to the response.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Http\Middleware
 * @since   6.0
 */
final class EnforceFrameOptions implements MiddlewareInterface
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
        \header('x-frame-options: SAMEORIGIN');

        $response = $handler->handle($request);

        if ($response instanceof LegacyPlaceholderResponse) {
            return $response;
        }

        \header_remove('x-frame-options');

        return $response->withHeader('x-frame-options', 'SAMEORIGIN');
    }
}
