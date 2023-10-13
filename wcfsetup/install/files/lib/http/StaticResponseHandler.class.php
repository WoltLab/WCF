<?php

namespace wcf\http;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Always returns the given response.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class StaticResponseHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly ResponseInterface $response
    ) {
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        return $this->response;
    }
}
