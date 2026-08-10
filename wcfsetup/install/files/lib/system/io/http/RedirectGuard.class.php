<?php

namespace wcf\system\io\http;

use GuzzleHttp\Exception\BadResponseException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;

/**
 * The RedirectGuard prevents unsafe redirects from proceeding.
 *
 * Current checks:
 * - Redirects to non-standard ports.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2021 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   5.4
 */
final class RedirectGuard
{
    private readonly ?\Closure $next;

    /**
     * @param ?callable $next The next callback to call after validation succeeds.
     */
    public function __construct(?callable $next = null)
    {
        $this->next = $next !== null ? \Closure::fromCallable($next) : null;
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, UriInterface $uri): ?callable
    {
        if ($uri->getPort() !== null) {
            throw new BadResponseException(
                "Refusing to follow redirects to non-standard ports.",
                $request,
                $response
            );
        }

        if (($next = $this->next) !== null) {
            return $next($request, $response, $uri);
        }

        return null;
    }
}
