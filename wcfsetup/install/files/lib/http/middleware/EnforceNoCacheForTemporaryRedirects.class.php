<?php

namespace wcf\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\LegacyPlaceholderResponse;
use wcf\util\HeaderUtil;

/**
 * Sets headers disabling caching if the response status code
 * indicates a temporary redirect.
 *
 * This avoids some issues with misconfigured HTTP servers or CDNs.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Http\Middleware
 * @since   6.0
 */
final class EnforceNoCacheForTemporaryRedirects implements MiddlewareInterface
{
    private const TEMPORARY_REDIRECT = [
        302, // Found
        303, // See Other
        307, // Temporary Redirect
    ];

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ($response instanceof LegacyPlaceholderResponse) {
            return $response;
        }

        if (\in_array($response->getStatusCode(), self::TEMPORARY_REDIRECT, true)) {
            return HeaderUtil::withNoCacheHeaders($response);
        } else {
            return $response;
        }
    }
}
