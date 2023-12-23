<?php

namespace wcf\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\error\ErrorDetail;
use wcf\http\error\NotFoundHandler;
use wcf\http\error\OperationNotPermittedHandler;
use wcf\http\error\PermissionDeniedHandler;
use wcf\http\error\XsrfValidationFailedHandler;
use wcf\system\exception\IllegalLinkException;
use wcf\system\exception\InvalidSecurityTokenException;
use wcf\system\exception\NamedUserException;
use wcf\system\exception\PermissionDeniedException;

/**
 * Catches PermissionDeniedException and IllegalLinkException and delegates
 * to appropriate handlers.
 *
 * @author Tim Duesterhus
 * @copyright 2001-2023 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.0
 */
final class HandleExceptions implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (PermissionDeniedException | IllegalLinkException | InvalidSecurityTokenException | NamedUserException $e) {
            if ($e instanceof PermissionDeniedException) {
                $handler = new PermissionDeniedHandler();
            } elseif ($e instanceof IllegalLinkException) {
                $handler = new NotFoundHandler();
            } elseif ($e instanceof InvalidSecurityTokenException) {
                $handler = new XsrfValidationFailedHandler();
            } elseif ($e instanceof NamedUserException) {
                $handler = new OperationNotPermittedHandler();
            } else {
                throw new \LogicException('Unreachable');
            }

            return $handler->handle(ErrorDetail::fromThrowable($e)->attachToRequest($request));
        }
    }
}
