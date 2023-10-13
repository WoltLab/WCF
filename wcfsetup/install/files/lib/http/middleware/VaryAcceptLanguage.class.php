<?php

namespace wcf\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\LegacyPlaceholderResponse;
use wcf\system\WCF;

/**
 * Adds 'vary: accept-language' to the response for guests.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class VaryAcceptLanguage implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Do not set the header with a regular `\header()` call, because:
        // (a) Any 'vary' in a controller is likely going to override the header,
        //     as $replace defaults to 'true', whereas 'vary' contains a *list*
        //     of items.
        // (b) For the same reason we cannot use `\header_remove()` to delegate
        //     the logic to the PSR-7 response emitter, as we might also
        //     remove unrelated items.
        //
        // This is different to the other middlewares, because they use `withHeader`,
        // thus overriding any existing header, instead of `withAddedHeader` to
        // add a single item.
        //
        // Furthermore adding the `vary: accept-language` is not super necessary,
        // because caching for responses might already be disabled.
        //
        // Thus we attach it to any PSR-7 responses on a best effort basis, the number
        // of controllers returning a PSR-7 response is expected to grow over time.

        $response = $handler->handle($request);

        if ($response instanceof LegacyPlaceholderResponse) {
            return $response;
        }

        if (!WCF::getUser()->userID) {
            return $response->withAddedHeader('vary', 'accept-language');
        } else {
            return $response;
        }
    }
}
