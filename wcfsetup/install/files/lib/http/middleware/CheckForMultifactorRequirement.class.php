<?php

namespace wcf\http\middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\form\MultifactorManageForm;
use wcf\http\Helper;
use wcf\page\AccountSecurityPage;
use wcf\system\request\LinkHandler;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;

/**
 * Checks whether the user is required to set up the multi-factor authentication.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class CheckForMultifactorRequirement implements MiddlewareInterface
{
    private const ALLOWED_CONTROLLERS = [
        AccountSecurityPage::class,
        MultifactorManageForm::class,
    ];

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (
            $this->multifactorRequired()
            && !$this->requestCanBypassMultifactor($request)
        ) {
            return new RedirectResponse(
                LinkHandler::getInstance()->getControllerLink(AccountSecurityPage::class)
            );
        }

        return $handler->handle($request);
    }

    private function multifactorRequired(): bool
    {
        return WCF::getUser()->userID
            && WCF::getUser()->requiresMultifactor()
            && !WCF::getUser()->multifactorActive;
    }

    private function requestCanBypassMultifactor(ServerRequestInterface $request): bool
    {
        $controller = RequestHandler::getInstance()->getActiveRequest()->getClassName();
        if (\in_array($controller, self::ALLOWED_CONTROLLERS, true)) {
            return true;
        }

        if (RequestHandler::getInstance()->getActiveRequest()->isAvailableDuringOfflineMode()) {
            return true;
        }

        if (Helper::isAjaxRequest($request)) {
            return true;
        }

        return false;
    }
}
