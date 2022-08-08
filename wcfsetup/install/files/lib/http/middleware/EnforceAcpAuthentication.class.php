<?php

namespace wcf\http\middleware;

use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\acp\action\FullLogoutAction;
use wcf\acp\form\LoginForm;
use wcf\acp\form\MultifactorAuthenticationForm;
use wcf\acp\form\ReauthenticationForm;
use wcf\http\Helper;
use wcf\system\exception\AJAXException;
use wcf\system\exception\NamedUserException;
use wcf\system\request\LinkHandler;
use wcf\system\request\RequestHandler;
use wcf\system\user\multifactor\TMultifactorRequirementEnforcer;
use wcf\system\WCF;
use wcf\system\WCFACP;

/**
 * Checks all ACP requests for proper authentication.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package WoltLabSuite\Core\Http\Middleware
 * @since   6.0
 */
final class EnforceAcpAuthentication implements MiddlewareInterface
{
    use TMultifactorRequirementEnforcer;

    private const ALLOWED_CONTROLLERS = [
        LoginForm::class,
        ReauthenticationForm::class,
        FullLogoutAction::class,
        MultifactorAuthenticationForm::class,
    ];

    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        if (!RequestHandler::getInstance()->isACPRequest()) {
            return $handler->handle($request);
        }

        if (WCFACP::inRescueMode()) {
            return $handler->handle($request);
        }

        $controller = RequestHandler::getInstance()->getActiveRequest()->getClassName();
        if (\in_array($controller, self::ALLOWED_CONTROLLERS)) {
            return $handler->handle($request);
        }

        if (!WCF::getUser()->userID) {
            return $this->handleGuest($request);
        }

        if (!WCF::getSession()->getPermission('admin.general.canUseAcp')) {
            return $this->handleNoAcpPermission($request);
        }

        if (WCF::getSession()->needsReauthentication()) {
            return $this->handleReauthentication($request);
        }

        $this->enforceMultifactorAuthentication();

        // force debug mode if in ACP and authenticated
        WCFACP::overrideDebugMode();

        return $handler->handle($request);
    }

    private function handleGuest(ServerRequestInterface $request): ResponseInterface
    {
        if (Helper::isAjaxRequest($request)) {
            throw new AJAXException(
                WCF::getLanguage()->getDynamicVariable('wcf.ajax.error.sessionExpired'),
                AJAXException::SESSION_EXPIRED,
                ''
            );
        }

        return new RedirectResponse(
            LinkHandler::getInstance()->getControllerLink(
                LoginForm::class,
                [
                    'url' => (string)$request->getUri(),
                ]
            )
        );
    }

    private function handleNoAcpPermission(ServerRequestInterface $request): ResponseInterface
    {
        WCF::getTPL()->assign([
            '__isLogin' => true,
        ]);

        if (Helper::isAjaxRequest($request)) {
            throw new AJAXException(
                WCF::getLanguage()->getDynamicVariable('wcf.ajax.error.permissionDenied'),
                AJAXException::INSUFFICIENT_PERMISSIONS
            );
        } else {
            throw new NamedUserException(
                WCF::getLanguage()->getDynamicVariable('wcf.user.username.error.acpNotAuthorized')
            );
        }
    }

    private function handleReauthentication(ServerRequestInterface $request): ResponseInterface
    {
        if (Helper::isAjaxRequest($request)) {
            throw new AJAXException(
                WCF::getLanguage()->getDynamicVariable('wcf.user.reauthentication.explanation'),
                AJAXException::SESSION_EXPIRED
            );
        }

        return new RedirectResponse(
            LinkHandler::getInstance()->getControllerLink(
                ReauthenticationForm::class,
                [
                    'url' => (string)$request->getUri()
                ]
            )
        );
    }
}
