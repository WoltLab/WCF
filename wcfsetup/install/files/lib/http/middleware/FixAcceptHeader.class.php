<?php

namespace wcf\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\Helper;

/**
 * Adds a preference for application/json responses for AJAX requests that accept everything.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class FixAcceptHeader implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (Helper::isAjaxRequest($request)) {
            if (!$request->hasHeader('accept') || $request->getHeaderLine('accept') === '*/*') {
                $request = $request->withHeader('accept', 'application/json, */*; q=0.9');
            }
        }

        return $handler->handle($request);
    }
}
