<?php

namespace wcf\http\middleware;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\acp\action\FullLogoutAction;
use wcf\acp\form\LoginForm;
use wcf\acp\form\MultifactorAuthenticationForm;
use wcf\acp\form\ReauthenticationForm;
use wcf\action\AJAXInvokeAction;
use wcf\data\acp\session\access\log\ACPSessionAccessLogEditor;
use wcf\data\acp\session\log\ACPSessionLog;
use wcf\data\acp\session\log\ACPSessionLogEditor;
use wcf\http\error\ErrorDetail;
use wcf\http\error\PermissionDeniedHandler;
use wcf\http\Helper;
use wcf\system\request\LinkHandler;
use wcf\system\request\RequestHandler;
use wcf\system\WCF;
use wcf\system\WCFACP;
use wcf\util\HeaderUtil;
use wcf\util\UserUtil;

/**
 * Checks all ACP requests for proper authentication.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class EnforceAcpAuthentication implements MiddlewareInterface
{
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
        if (\in_array($controller, self::ALLOWED_CONTROLLERS, true)) {
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

        // force debug mode if in ACP and authenticated
        WCFACP::overrideDebugMode();

        if (!\defined("{$controller}::DO_NOT_LOG")) {
            $this->logRequest($request);
        }

        return $handler->handle($request);
    }

    private function handleGuest(ServerRequestInterface $request): ResponseInterface
    {
        if (Helper::isAjaxRequest($request)) {
            return (new PermissionDeniedHandler())->handle($request);
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
            return (new PermissionDeniedHandler())->handle($request);
        }

        return new HtmlResponse(
            HeaderUtil::parseOutputStream(WCF::getTPL()->fetchStream(
                'acpNotAuthorized',
                'wcf',
            )),
            403
        );
    }

    private function handleReauthentication(ServerRequestInterface $request): ResponseInterface
    {
        if (Helper::isAjaxRequest($request)) {
            return (new PermissionDeniedHandler())->handle(
                ErrorDetail::fromMessage(WCF::getLanguage()->getDynamicVariable('wcf.user.reauthentication.explanation'))
                    ->attachToRequest($request)
            );
        }

        return new RedirectResponse(
            LinkHandler::getInstance()->getControllerLink(
                ReauthenticationForm::class,
                [
                    'url' => (string)$request->getUri(),
                ]
            )
        );
    }

    private function logRequest(ServerRequestInterface $request): void
    {
        // try to find existing session log
        $sql = "SELECT  sessionLogID
                FROM    wcf1_acp_session_log
                WHERE   sessionID = ?
                    AND lastActivityTime > ?";
        $statement = WCF::getDB()->prepare($sql);
        $statement->execute([
            WCF::getSession()->sessionID,
            (TIME_NOW - 15 * 60),
        ]);
        $row = $statement->fetchArray();
        if (!empty($row['sessionLogID'])) {
            $sessionLogID = $row['sessionLogID'];

            $sessionLogEditor = new ACPSessionLogEditor(new ACPSessionLog(null, ['sessionLogID' => $sessionLogID]));
            $sessionLogEditor->update([
                'lastActivityTime' => TIME_NOW,
            ]);
        } else {
            // create new session log
            $sessionLog = ACPSessionLogEditor::create([
                'sessionID' => WCF::getSession()->sessionID,
                'userID' => WCF::getUser()->userID,
                'ipAddress' => UserUtil::getIpAddress(),
                'hostname' => @\gethostbyaddr(UserUtil::getIpAddress()),
                'userAgent' => \mb_substr(Helper::getUserAgent($request) ?? '', 0, 191),
                'time' => TIME_NOW,
                'lastActivityTime' => TIME_NOW,
            ]);
            $sessionLogID = $sessionLog->sessionLogID;
        }

        // Fetch request URI + request ID (if available).
        $requestURI = Helper::getPathAndQuery($request->getUri());
        if ($requestId = \wcf\getRequestId()) {
            $requestIdSuffix = ' (' . $requestId . ')';
            // Ensure that the request ID fits by truncating the URI.
            $requestURI = \substr($requestURI, 0, 255 - \strlen($requestIdSuffix)) . $requestIdSuffix;
        }

        // Get controller name + the AJAX action.
        $className = RequestHandler::getInstance()->getActiveRequest()->getClassName();
        if (\is_subclass_of($className, AJAXInvokeAction::class)) {
            $body = $request->getParsedBody();
            if (isset($body['className']) && isset($body['actionName'])) {
                $className .= \sprintf(
                    " (%s:%s)",
                    $body['className'],
                    $body['actionName']
                );
            }
        }

        // save access
        ACPSessionAccessLogEditor::create([
            'sessionLogID' => $sessionLogID,
            'ipAddress' => UserUtil::getIpAddress(),
            'time' => TIME_NOW,
            'requestURI' => \substr($requestURI, 0, 255),
            'requestMethod' => \substr($request->getMethod(), 0, 255),
            'className' => \substr($className, 0, 255),
        ]);
    }
}
