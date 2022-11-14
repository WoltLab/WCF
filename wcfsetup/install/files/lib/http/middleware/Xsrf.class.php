<?php

namespace wcf\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\system\exception\InvalidSecurityTokenException;
use wcf\system\request\Request;
use wcf\system\request\RequestHandler;
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

    private readonly RequestHandler $requestHandler;

    public function __construct()
    {
        $this->requestHandler = RequestHandler::getInstance();
    }

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

        $hasValidXsrfToken = \hash_equals($xsrfToken, $request->getHeaderLine('x-xsrf-token'));

        $request = $request->withAttribute(
            self::HAS_VALID_HEADER_ATTRIBUTE,
            $hasValidXsrfToken
        );

        if (
            $request->getMethod() !== 'GET'
            && $request->getMethod() !== 'HEAD'
            && $this->requestHandler->getActiveRequest()
            && \is_subclass_of($this->requestHandler->getActiveRequest()->getClassName(), RequestHandlerInterface::class)
        ) {
            if (!$this->validateXsrfToken($this->requestHandler->getActiveRequest(), $hasValidXsrfToken)) {
                throw new InvalidSecurityTokenException();
            }
        }

        return $handler->handle($request);
    }

    private function validateXsrfToken(Request $request, $hasValidXsrfToken): bool
    {
        return $hasValidXsrfToken;
    }
}
