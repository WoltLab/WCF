<?php

namespace wcf\action;

use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use wcf\data\user\group\UserGroup;
use wcf\data\user\UserProfile;
use wcf\data\user\UserProfileList;
use wcf\http\Helper;
use wcf\system\cache\runtime\UserProfileRuntimeCache;
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
        $parameters = Helper::mapQueryParameters(
            $request->getQueryParams(),
            <<<'EOT'
                    array {
                        id: positive-int
                    }
                    EOT,
        );

        $userProfile = UserProfileRuntimeCache::getInstance()->getObject($parameters['id']);
        if ($userProfile) {
            WCF::getTPL()->assign('user', $userProfile);
        } else {
            WCF::getTPL()->assign('unknownUser', true);
        }

        return new HtmlResponse(
            WCF::getTPL()->fetch('userProfilePreview'),
        );
    }
}
