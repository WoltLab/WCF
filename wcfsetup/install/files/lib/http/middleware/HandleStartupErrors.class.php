<?php

namespace wcf\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Intercepts request processing if a startup error is detected that
 * makes it impossible to safely proceed with processing. An example would
 * be 'max_input_vars' silently dropping request parameters.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class HandleStartupErrors implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (\defined('WCF_STARTUP_ERROR')) {
            // TODO: Adjust this to exclude specific (expected) errors if necessary.
            throw new \ErrorException(\WCF_STARTUP_ERROR['message'], 0, \WCF_STARTUP_ERROR['type']);
        }

        return $handler->handle($request);
    }
}
