<?php

namespace wcf\http\middleware;

use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\Helper;
use wcf\system\box\BoxHandler;
use wcf\system\exception\AJAXException;
use wcf\system\notice\NoticeHandler;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

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

        return HeaderUtil::withNoCacheHeaders($this->getOfflineResponse($request));
    }

    private function getOfflineResponse(ServerRequestInterface $request): ResponseInterface
    {
        if (Helper::isAjaxRequest($request)) {
            throw new AJAXException(
                WCF::getLanguage()->getDynamicVariable('wcf.ajax.error.permissionDenied'),
                AJAXException::INSUFFICIENT_PERMISSIONS
            );
        } else {
            BoxHandler::disablePageLayout();
            NoticeHandler::disableNotices();

            return new HtmlResponse(
                HeaderUtil::parseOutputStream(WCF::getTPL()->fetchStream(
                    'offline',
                    'wcf',
                    [
                        'templateName' => 'offline',
                        'templateNameApplication' => 'wcf',
                    ]
                )),
                503
            );
        }
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
