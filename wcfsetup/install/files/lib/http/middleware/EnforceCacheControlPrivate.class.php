<?php

namespace wcf\http\middleware;

use GuzzleHttp\Psr7\Header;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\LegacyPlaceholderResponse;

/**
 * Adds 'private' to the 'cache-control' response header and removes 'public'.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Http\Middleware
 * @since   6.0
 */
final class EnforceCacheControlPrivate implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $response = $handler->handle($request);

        if ($response instanceof LegacyPlaceholderResponse) {
            return $response;
        }

        // Storing responses in a shared cache is unsafe, because they all contain session specific information.
        // Add the 'private' value to the cache-control header and remove any 'public' value.
        $cacheControl = [
            'private',
        ];
        foreach (Header::splitList($response->getHeader('cache-control')) as $value) {
            [$field] = \explode('=', $value, 2);

            // Prevent duplication of the 'private' field.
            if ($field === 'private') {
                continue;
            }

            // Drop the 'public' field.
            if ($field === 'public') {
                continue;
            }

            $cacheControl[] = $value;
        }

        return $response->withHeader(
            'cache-control',
            // Manually imploding the fields is not required as per strict reading of the HTTP standard,
            // but having duplicate 'cache-control' headers in the response certainly looks odd.
            \implode(', ', $cacheControl)
        );
    }
}
