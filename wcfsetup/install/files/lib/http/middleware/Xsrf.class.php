<?php

namespace wcf\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\attribute\DisableXsrfCheck;
use wcf\http\error\XsrfValidationFailedHandler;
use wcf\system\request\Request;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Attaches attributes for XSRF protection validation to the request.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
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
            !$this->isSafeHttpMethod($request->getMethod())
            && $this->requestHandler->getActiveRequest()
            && !$hasValidXsrfToken
        ) {
            $activeRequest = $this->requestHandler->getActiveRequest();
            if ($this->controllerRequiresXsrfCheck($activeRequest)) {
                return (new XsrfValidationFailedHandler())->handle($request);
            }
        }

        return $handler->handle($request);
    }

    private function isSafeHttpMethod(string $verb): bool
    {
        // HTTP requests using the 'GET' or 'HEAD' verb are safe
        // by design, because those should not alter the state.
        return $verb === 'GET' || $verb === 'HEAD';
    }

    private function controllerRequiresXsrfCheck(Request $request): bool
    {
        if (!\is_subclass_of($request->getClassName(), RequestHandlerInterface::class)) {
            // Skip the XSRF check for legacy controllers.
            return false;
        }

        $reflectionClass = new \ReflectionClass($request->getClassName());
        if ($reflectionClass->getAttributes(DisableXsrfCheck::class) !== []) {
            // Controller has opted out of the XSRF check.
            return false;
        }

        return true;
    }
}
