<?php

namespace wcf\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\system\exception\IllegalLinkException;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Restricts access to certain ACP pages for non-owners.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Http\Middleware
 * @since   5.6
 */
final class CheckForEnterpriseNonOwnerAccess implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestHandler = RequestHandler::getInstance();

        if (
            $requestHandler->isACPRequest()
            && \ENABLE_ENTERPRISE_MODE
            && \defined($requestHandler->getActiveRequest()->getClassName() . '::BLACKLISTED_IN_ENTERPRISE_MODE')
            && \constant($requestHandler->getActiveRequest()->getClassName() . '::BLACKLISTED_IN_ENTERPRISE_MODE')
            && !WCF::getUser()->hasOwnerAccess()
        ) {
            throw new IllegalLinkException();
        }

        return $handler->handle($request);
    }
}
