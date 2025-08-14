<?php

namespace wcf\system\endpoint\controller\core\users\notifications;

use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use wcf\system\endpoint\IController;
use wcf\system\endpoint\PostRequest;
use wcf\system\exception\PermissionDeniedException;
use wcf\system\WCF;

/**
 * Marks all moderation queues as read.
 *
 * @author Olaf Braun
 * @copyright 2001-2025 WoltLab GmbH
 * @license GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @since 6.3
 */
#[PostRequest('/core/users/notifications/mark-all-as-read')]
final class MarkAllUserNotificationsAsRead implements IController
{
    #[\Override]
    public function __invoke(ServerRequestInterface $request, array $variables): ResponseInterface
    {
        $this->assertUserIsLoggedIn();

        (new \wcf\command\user\notification\MarkAllUserNotificationsAsRead(WCF::getUser()->userID));

        return new JsonResponse([]);
    }

    private function assertUserIsLoggedIn(): void
    {
        if (!WCF::getUser()->userID) {
            throw new PermissionDeniedException();
        }
    }
}
