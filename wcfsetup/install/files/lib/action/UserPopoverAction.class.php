<?php

namespace wcf\action;

use Laminas\Diactoros\Response\EmptyResponse;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\http\Helper;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Provides the popover content for a user.
 *
 * @author Alexander Ebert
 * @copyright 2001-2024 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.1
 */
final class UserPopoverAction implements RequestHandlerInterface
{
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        if (!WCF::getSession()->getPermission('user.profile.canViewUserProfile')) {
            throw new PermissionDeniedException();
        }

        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                    array {
                        id: positive-int
                    }
                    EOT,
        );

        $user = UserProfileRuntimeCache::getInstance()->getObject($parameters['id']);
        if (!$user) {
            return new EmptyResponse();
        }

        return new HtmlResponse(
            WCF::getTPL()->fetch('userCard', 'wcf', ['user' => $user]),
        );
    }
}
