<?php

namespace wcf\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\system\WCF;

/**
 * Attaches attributes for XSRF protection validation to the request.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Http\Middleware
 * @since   6.0
 */
final class Xsrf implements MiddlewareInterface
{
    public const TOKEN_ATTRIBUTE = self::class . "\0token";

    public const HAS_VALID_HEADER_ATTRIBUTE = self::class . "\0hasValidHeader";

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $xsrfToken = WCF::getSession()->getSecurityToken();

        $request = $request->withAttribute(
            self::TOKEN_ATTRIBUTE,
            $xsrfToken
        );
        $request = $request->withAttribute(
            self::HAS_VALID_HEADER_ATTRIBUTE,
            \hash_equals($xsrfToken, $request->getHeaderLine('x-xsrf-token'))
        );

        return $handler->handle($request);
    }
}
