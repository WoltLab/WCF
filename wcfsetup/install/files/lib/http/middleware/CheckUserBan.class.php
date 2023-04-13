<?php

namespace wcf\http\middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\user\User;
use wcf\http\error\ErrorDetail;
use wcf\http\error\PermissionDeniedHandler;
use wcf\http\Helper;
use wcf\system\WCF;

/**
 * Checks whether the user is banned and deletes their sessions.
 *
 * @author  Tim Duesterhus
 * @copyright   2001-2022 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since   6.0
 */
final class CheckUserBan implements MiddlewareInterface
{
    /**
     * @inheritDoc
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = WCF::getUser();

        if ($this->isBanned($user)) {
            if (!Helper::isAjaxRequest($request)) {
                // Delete sessions only for non-AJAX requests to ensure
                // that the user was able to see the message properly
                WCF::getSession()->deleteUserSessionsExcept($user);
            }

            return (new PermissionDeniedHandler())->handle(
                ErrorDetail::fromMessage(WCF::getLanguage()->getDynamicVariable('wcf.user.error.isBanned'))
                    ->attachToRequest($request)
            );
        }

        return $handler->handle($request);
    }

    private function isBanned(User $user): bool
    {
        if (!$user->userID) {
            return false;
        }

        if ($user->hasOwnerAccess()) {
            return false;
        }

        return !!$user->banned;
    }
}
