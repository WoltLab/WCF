<?php

namespace wcf\http\middleware;

use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\form\LoginForm;
use wcf\http\Helper;
use wcf\system\request\LinkHandler;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;
use wcf\util\HeaderUtil;

/**
 * Checks whether the 'force login' option is enabled and the request must be intercepted.
 *
 * @author      Marcel Werk
 * @copyright   2001-2024 WoltLab GmbH
 * @license     GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since       6.1
 */
final class CheckForForceLogin implements MiddlewareInterface
{
    private const STATUS_CODE = 403;

    private const ALLOWED_CONTROLLERS = [
        \wcf\form\EmailActivationForm::class,
        \wcf\form\EmailNewActivationCodeForm::class,
        \wcf\form\LoginForm::class,
        \wcf\form\LostPasswordForm::class,
        \wcf\form\NewPasswordForm::class,
        \wcf\form\RegisterActivationForm::class,
        \wcf\form\RegisterForm::class,
        \wcf\form\RegisterNewActivationCodeForm::class,
        \wcf\page\DisclaimerPage::class,
    ];

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (
            !$this->forceLoginEnabled()
            || RequestHandler::getInstance()->isACPRequest()
            || $this->userCanBypassForceLogin()
            || $this->requestCanBypassForceLogin()
        ) {
            return $handler->handle($request);
        }

        $preferredType = Helper::getPreferredContentType($request, [
            'application/json',
            'text/html',
        ]);

        return match ($preferredType) {
            'application/json' => HeaderUtil::withNoCacheHeaders(
                new JsonResponse(
                    [
                        'message' => WCF::getLanguage()->getDynamicVariable('wcf.user.login.forceLogin'),
                    ],
                    self::STATUS_CODE,
                    [],
                    \JSON_PRETTY_PRINT
                )
            ),
            'text/html' => new RedirectResponse(
                LinkHandler::getInstance()->getControllerLink(LoginForm::class)
            )
        };
    }

    private function forceLoginEnabled(): bool
    {
        return \defined('FORCE_LOGIN') && FORCE_LOGIN;
    }

    private function userCanBypassForceLogin(): bool
    {
        return WCF::getUser()->userID ? true : false;
    }

    private function requestCanBypassForceLogin(): bool
    {
        $controller = RequestHandler::getInstance()->getActiveRequest()->getClassName();
        if (\in_array($controller, self::ALLOWED_CONTROLLERS, true)) {
            return true;
        }

        if (RequestHandler::getInstance()->getActiveRequest()->isAvailableDuringOfflineMode()) {
            return true;
        }

        return false;
    }
}
