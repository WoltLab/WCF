<?php

namespace wcf\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\error\OfflineHandler;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Checks whether the offline mode is enabled and the request must be intercepted.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class CheckForOfflineMode implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (
            !$this->offlineModeEnabled()
            || RequestHandler::getInstance()->isACPRequest()
            || $this->userCanBypassOfflineMode()
        ) {
            return $handler->handle($request);
        }

        if (RequestHandler::getInstance()->getActiveRequest()->isAvailableDuringOfflineMode()) {
            return $handler->handle($request);
        }

        return (new OfflineHandler())->handle($request);
    }

    private function offlineModeEnabled(): bool
    {
        return \defined('OFFLINE') && OFFLINE;
    }

    private function userCanBypassOfflineMode(): bool
    {
        return WCF::getSession()->getPermission('admin.general.canViewPageDuringOfflineMode');
    }
}
